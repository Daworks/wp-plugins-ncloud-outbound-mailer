<?php
/**
 * Ncloud API Client
 *
 * @package NcloudMailer
 */

namespace NcloudMailer\API;

use WP_Error;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Class Client
 *
 * Handles communication with Ncloud Cloud Outbound Mailer API.
 */
class Client {

    /**
     * API endpoints by region.
     */
    private const ENDPOINTS = array(
        'KR'  => 'https://mail.apigw.ntruss.com/api/v1',
        'SGN' => 'https://mail.apigw.ntruss.com/api/v1-sgn',
        'JPN' => 'https://mail.apigw.ntruss.com/api/v1-jpn',
    );

    /**
     * API settings.
     *
     * @var array
     */
    private $settings;

    /**
     * Constructor.
     */
    public function __construct() {
        $this->settings = $this->get_settings();
    }

    /**
     * Get plugin settings.
     *
     * @return array Plugin settings.
     */
    private function get_settings(): array {
        $defaults = array(
            'access_key'     => '',
            'secret_key'     => '',
            'sender_address' => '',
            'sender_name'    => '',
            'region'         => 'KR',
            'enabled'        => false,
        );

        $settings = get_option( 'ncloud_mailer_settings', array() );
        return wp_parse_args( $settings, $defaults );
    }

    /**
     * Refresh settings from database.
     *
     * @return void
     */
    public function refresh_settings(): void {
        $this->settings = $this->get_settings();
    }

    /**
     * Check if the mailer is enabled and configured.
     *
     * @return bool True if enabled and configured.
     */
    public function is_enabled(): bool {
        return ! empty( $this->settings['enabled'] )
            && ! empty( $this->settings['access_key'] )
            && ! empty( $this->settings['secret_key'] )
            && ! empty( $this->settings['sender_address'] );
    }

    /**
     * Get the API endpoint for the configured region.
     *
     * @return string API endpoint URL.
     */
    public function get_endpoint(): string {
        $region = $this->settings['region'] ?? 'KR';
        return self::ENDPOINTS[ $region ] ?? self::ENDPOINTS['KR'];
    }

    /**
     * Get sender address.
     *
     * @return string Sender email address.
     */
    public function get_sender_address(): string {
        return $this->settings['sender_address'] ?? '';
    }

    /**
     * Get sender name.
     *
     * @return string Sender name.
     */
    public function get_sender_name(): string {
        return $this->settings['sender_name'] ?? '';
    }

    /**
     * Send email via Ncloud API.
     *
     * @param array $mail_data Email data.
     * @return array|WP_Error Response data or WP_Error on failure.
     */
    public function send_mail( array $mail_data ) {
        if ( ! $this->is_enabled() ) {
            return new WP_Error(
                'ncloud_mailer_not_configured',
                __( 'Ncloud Mailer is not properly configured.', 'ncloud-outbound-mailer' )
            );
        }

        $uri       = '/api/v1/mails';
        $timestamp = Signature::get_timestamp();
        $signature = Signature::generate(
            'POST',
            $uri,
            $timestamp,
            $this->settings['access_key'],
            $this->settings['secret_key']
        );

        $headers = array(
            'Content-Type'             => 'application/json',
            'x-ncp-apigw-timestamp'    => $timestamp,
            'x-ncp-iam-access-key'     => $this->settings['access_key'],
            'x-ncp-apigw-signature-v2' => $signature,
        );

        $body = $this->prepare_mail_body( $mail_data );

        /**
         * Filter the mail body before sending.
         *
         * @param array $body      The mail body data.
         * @param array $mail_data Original mail data.
         */
        $body = apply_filters( 'ncloud_mailer_before_send', $body, $mail_data );

        $response = wp_remote_post(
            $this->get_endpoint() . '/mails',
            array(
                'headers' => $headers,
                'body'    => wp_json_encode( $body ),
                'timeout' => 30,
            )
        );

        if ( is_wp_error( $response ) ) {
            /**
             * Fires when an error occurs during mail sending.
             *
             * @param WP_Error $response  The error object.
             * @param array    $mail_data Original mail data.
             */
            do_action( 'ncloud_mailer_error', $response, $mail_data );
            return $response;
        }

        $response_code = wp_remote_retrieve_response_code( $response );
        $response_body = wp_remote_retrieve_body( $response );
        $result        = json_decode( $response_body, true );

        if ( 201 !== $response_code && 200 !== $response_code ) {
            $error_message = $result['errorMessage'] ?? __( 'Unknown error occurred.', 'ncloud-outbound-mailer' );
            $error_code    = $result['error']['errorCode'] ?? 'ncloud_api_error';

            $error = new WP_Error( $error_code, $error_message, $result );

            /** This action is documented above. */
            do_action( 'ncloud_mailer_error', $error, $mail_data );

            return $error;
        }

        /**
         * Fires after successful mail sending.
         *
         * @param array $result    API response data.
         * @param array $mail_data Original mail data.
         */
        do_action( 'ncloud_mailer_after_send', $result, $mail_data );

        return $result;
    }

    /**
     * Prepare mail body for API request.
     *
     * @param array $mail_data Email data.
     * @return array Formatted mail body.
     */
    private function prepare_mail_body( array $mail_data ): array {
        $body = array(
            'senderAddress' => $mail_data['from'] ?? $this->get_sender_address(),
            'senderName'    => $mail_data['from_name'] ?? $this->get_sender_name(),
            'title'         => $mail_data['subject'] ?? '',
            'body'          => $mail_data['message'] ?? '',
            'individual'    => true,
            'advertising'   => false,
            'recipients'    => $this->format_recipients( $mail_data ),
        );

        return $body;
    }

    /**
     * Format recipients for API request.
     *
     * @param array $mail_data Email data.
     * @return array Formatted recipients array.
     */
    private function format_recipients( array $mail_data ): array {
        $recipients = array();

        // Add TO recipients.
        if ( ! empty( $mail_data['to'] ) ) {
            foreach ( (array) $mail_data['to'] as $to ) {
                $recipients[] = $this->parse_recipient( $to, 'R' );
            }
        }

        // Add CC recipients.
        if ( ! empty( $mail_data['cc'] ) ) {
            foreach ( (array) $mail_data['cc'] as $cc ) {
                $recipients[] = $this->parse_recipient( $cc, 'C' );
            }
        }

        // Add BCC recipients.
        if ( ! empty( $mail_data['bcc'] ) ) {
            foreach ( (array) $mail_data['bcc'] as $bcc ) {
                $recipients[] = $this->parse_recipient( $bcc, 'B' );
            }
        }

        return $recipients;
    }

    /**
     * Parse a single recipient.
     *
     * @param string $recipient Recipient string (email or "Name <email>").
     * @param string $type      Recipient type (R=To, C=CC, B=BCC).
     * @return array Formatted recipient.
     */
    private function parse_recipient( string $recipient, string $type = 'R' ): array {
        $name    = '';
        $address = $recipient;

        // Parse "Name <email>" format.
        if ( preg_match( '/^(.+?)\s*<([^>]+)>$/', $recipient, $matches ) ) {
            $name    = trim( $matches[1] );
            $address = trim( $matches[2] );
        }

        return array(
            'address' => $address,
            'name'    => $name,
            'type'    => $type,
        );
    }

    /**
     * Test API connection.
     *
     * @return array|WP_Error Test result or error.
     */
    public function test_connection() {
        if ( empty( $this->settings['access_key'] ) || empty( $this->settings['secret_key'] ) ) {
            return new WP_Error(
                'missing_credentials',
                __( 'API credentials are not configured.', 'ncloud-outbound-mailer' )
            );
        }

        // Use a simple API call to test connection.
        // We'll try to get mail list (which requires valid credentials).
        $uri       = '/api/v1/mails';
        $timestamp = Signature::get_timestamp();
        $signature = Signature::generate(
            'GET',
            $uri,
            $timestamp,
            $this->settings['access_key'],
            $this->settings['secret_key']
        );

        $headers = array(
            'x-ncp-apigw-timestamp'    => $timestamp,
            'x-ncp-iam-access-key'     => $this->settings['access_key'],
            'x-ncp-apigw-signature-v2' => $signature,
        );

        $response = wp_remote_get(
            $this->get_endpoint() . '/mails?pageSize=1',
            array(
                'headers' => $headers,
                'timeout' => 15,
            )
        );

        if ( is_wp_error( $response ) ) {
            return $response;
        }

        $response_code = wp_remote_retrieve_response_code( $response );

        if ( 200 === $response_code ) {
            return array(
                'success' => true,
                'message' => __( 'API connection successful.', 'ncloud-outbound-mailer' ),
            );
        }

        $response_body = wp_remote_retrieve_body( $response );
        $result        = json_decode( $response_body, true );

        return new WP_Error(
            'api_error',
            $result['errorMessage'] ?? __( 'API connection failed.', 'ncloud-outbound-mailer' ),
            $result
        );
    }
}
