<?php
/**
 * Mail Handler - Intercepts wp_mail and sends via Ncloud API
 *
 * @package NcloudMailer
 */

namespace NcloudMailer;

use NcloudMailer\API\Client;
use WP_Error;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Class Mail_Handler
 *
 * Handles intercepting WordPress wp_mail() function and routing emails
 * through Ncloud Cloud Outbound Mailer API.
 */
class Mail_Handler {

    /**
     * API Client instance.
     *
     * @var Client
     */
    private $api_client;

    /**
     * Constructor.
     *
     * @param Client $api_client API Client instance.
     */
    public function __construct( Client $api_client ) {
        $this->api_client = $api_client;
        $this->init_hooks();
    }

    /**
     * Initialize hooks.
     */
    private function init_hooks(): void {
        // Intercept wp_mail - priority 999 to run after other plugins.
        add_filter( 'pre_wp_mail', array( $this, 'intercept_wp_mail' ), 999, 2 );
    }

    /**
     * Intercept wp_mail and send via Ncloud API.
     *
     * @param null|bool $return Short-circuit return value.
     * @param array     $atts   Array of the `wp_mail()` arguments.
     * @return null|bool Null to proceed with default, true/false for result.
     */
    public function intercept_wp_mail( $return, array $atts ) {
        // If already handled by another filter, skip.
        if ( null !== $return ) {
            return $return;
        }

        // Check if Ncloud Mailer is enabled.
        if ( ! $this->api_client->is_enabled() ) {
            return null; // Fall back to default wp_mail.
        }

        // Parse the wp_mail arguments.
        $mail_data = $this->parse_wp_mail_args( $atts );

        // Send via Ncloud API.
        $result = $this->api_client->send_mail( $mail_data );

        if ( is_wp_error( $result ) ) {
            // Log the error.
            $this->log_error( $result, $mail_data );

            /**
             * Filter whether to fall back to default wp_mail on error.
             *
             * @param bool     $fallback  Whether to fall back. Default false.
             * @param WP_Error $result    The error object.
             * @param array    $mail_data The mail data.
             */
            $fallback = apply_filters( 'ncloud_mailer_fallback_on_error', false, $result, $mail_data );

            if ( $fallback ) {
                return null; // Fall back to default wp_mail.
            }

            return false; // Return failure.
        }

        // Log success if logging is enabled.
        $this->log_success( $result, $mail_data );

        return true; // Mail sent successfully.
    }

    /**
     * Parse wp_mail arguments into our mail data format.
     *
     * @param array $atts wp_mail arguments.
     * @return array Parsed mail data.
     */
    private function parse_wp_mail_args( array $atts ): array {
        $to      = $atts['to'] ?? '';
        $subject = $atts['subject'] ?? '';
        $message = $atts['message'] ?? '';
        $headers = $atts['headers'] ?? array();

        // Normalize headers to array.
        if ( ! is_array( $headers ) ) {
            $headers = explode( "\n", str_replace( "\r\n", "\n", $headers ) );
        }

        // Parse headers.
        $parsed_headers = $this->parse_headers( $headers );

        // Normalize TO to array.
        if ( ! is_array( $to ) ) {
            $to = array_map( 'trim', explode( ',', $to ) );
        }

        // Build mail data.
        $mail_data = array(
            'to'        => $to,
            'subject'   => $subject,
            'message'   => $message,
            'from'      => $parsed_headers['from'] ?? '',
            'from_name' => $parsed_headers['from_name'] ?? '',
            'cc'        => $parsed_headers['cc'] ?? array(),
            'bcc'       => $parsed_headers['bcc'] ?? array(),
            'reply_to'  => $parsed_headers['reply_to'] ?? '',
        );

        // Check content type for HTML.
        if ( ! empty( $parsed_headers['content_type'] ) ) {
            $mail_data['content_type'] = $parsed_headers['content_type'];
        }

        return $mail_data;
    }

    /**
     * Parse email headers.
     *
     * @param array $headers Headers array.
     * @return array Parsed headers.
     */
    private function parse_headers( array $headers ): array {
        $parsed = array(
            'from'         => '',
            'from_name'    => '',
            'cc'           => array(),
            'bcc'          => array(),
            'reply_to'     => '',
            'content_type' => '',
        );

        foreach ( $headers as $header ) {
            $header = trim( $header );
            if ( empty( $header ) ) {
                continue;
            }

            // Split header into name and value.
            if ( strpos( $header, ':' ) === false ) {
                continue;
            }

            list( $name, $value ) = explode( ':', $header, 2 );
            $name  = strtolower( trim( $name ) );
            $value = trim( $value );

            switch ( $name ) {
                case 'from':
                    $parsed = array_merge( $parsed, $this->parse_from_header( $value ) );
                    break;

                case 'cc':
                    $parsed['cc'] = array_merge(
                        $parsed['cc'],
                        array_map( 'trim', explode( ',', $value ) )
                    );
                    break;

                case 'bcc':
                    $parsed['bcc'] = array_merge(
                        $parsed['bcc'],
                        array_map( 'trim', explode( ',', $value ) )
                    );
                    break;

                case 'reply-to':
                    $parsed['reply_to'] = $value;
                    break;

                case 'content-type':
                    // Extract content type (ignore charset and boundary).
                    if ( preg_match( '/^([^;]+)/', $value, $matches ) ) {
                        $parsed['content_type'] = trim( $matches[1] );
                    }
                    break;
            }
        }

        return $parsed;
    }

    /**
     * Parse From header.
     *
     * @param string $from From header value.
     * @return array Parsed from data.
     */
    private function parse_from_header( string $from ): array {
        $result = array(
            'from'      => '',
            'from_name' => '',
        );

        // Match "Name <email>" format.
        if ( preg_match( '/^(.+?)\s*<([^>]+)>$/', $from, $matches ) ) {
            $result['from_name'] = trim( $matches[1], ' "' );
            $result['from']      = trim( $matches[2] );
        } else {
            $result['from'] = trim( $from );
        }

        return $result;
    }

    /**
     * Log error for debugging.
     *
     * @param WP_Error $error     Error object.
     * @param array    $mail_data Mail data.
     */
    private function log_error( WP_Error $error, array $mail_data ): void {
        /**
         * Filter whether to enable error logging.
         *
         * @param bool $enabled Whether logging is enabled.
         */
        if ( ! apply_filters( 'ncloud_mailer_enable_logging', true ) ) {
            return;
        }

        $log_entry = array(
            'time'    => current_time( 'mysql' ),
            'status'  => 'error',
            'code'    => $error->get_error_code(),
            'message' => $error->get_error_message(),
            'to'      => $mail_data['to'] ?? array(),
            'subject' => $mail_data['subject'] ?? '',
        );

        // Store in transient for admin viewing (last 100 entries).
        $logs   = get_transient( 'ncloud_mailer_logs' ) ?: array();
        $logs[] = $log_entry;
        $logs   = array_slice( $logs, -100 ); // Keep last 100.
        set_transient( 'ncloud_mailer_logs', $logs, DAY_IN_SECONDS );

        // Also log to error log if WP_DEBUG is enabled.
        if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
            error_log(
                sprintf(
                    '[Ncloud Mailer Error] %s: %s (To: %s, Subject: %s)',
                    $error->get_error_code(),
                    $error->get_error_message(),
                    implode( ', ', (array) ( $mail_data['to'] ?? array() ) ),
                    $mail_data['subject'] ?? ''
                )
            );
        }
    }

    /**
     * Log successful send.
     *
     * @param array $result    API response.
     * @param array $mail_data Mail data.
     */
    private function log_success( array $result, array $mail_data ): void {
        /** This filter is documented above. */
        if ( ! apply_filters( 'ncloud_mailer_enable_logging', true ) ) {
            return;
        }

        $log_entry = array(
            'time'       => current_time( 'mysql' ),
            'status'     => 'success',
            'request_id' => $result['requestId'] ?? '',
            'to'         => $mail_data['to'] ?? array(),
            'subject'    => $mail_data['subject'] ?? '',
        );

        // Store in transient for admin viewing (last 100 entries).
        $logs   = get_transient( 'ncloud_mailer_logs' ) ?: array();
        $logs[] = $log_entry;
        $logs   = array_slice( $logs, -100 ); // Keep last 100.
        set_transient( 'ncloud_mailer_logs', $logs, DAY_IN_SECONDS );
    }
}
