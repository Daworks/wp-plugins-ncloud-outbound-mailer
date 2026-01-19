<?php
/**
 * Ncloud API Signature Generator
 *
 * @package NcloudMailer
 */

namespace NcloudMailer\API;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Class Signature
 *
 * Generates HMAC-SHA256 signatures for Ncloud API authentication.
 */
class Signature {

    /**
     * Generate API signature.
     *
     * @param string $method     HTTP method (GET, POST, etc.).
     * @param string $uri        Request URI (e.g., /api/v1/mails).
     * @param string $timestamp  Timestamp in milliseconds.
     * @param string $access_key Ncloud access key.
     * @param string $secret_key Ncloud secret key.
     * @return string Base64 encoded HMAC-SHA256 signature.
     */
    public static function generate(
        string $method,
        string $uri,
        string $timestamp,
        string $access_key,
        string $secret_key
    ): string {
        $space    = ' ';
        $new_line = "\n";

        // Build message string.
        $message = $method . $space . $uri . $new_line . $timestamp . $new_line . $access_key;

        // Generate HMAC-SHA256 signature.
        $signature = base64_encode(
            hash_hmac( 'sha256', $message, $secret_key, true )
        );

        return $signature;
    }

    /**
     * Get current timestamp in milliseconds.
     *
     * @return string Timestamp in milliseconds.
     */
    public static function get_timestamp(): string {
        return (string) round( microtime( true ) * 1000 );
    }
}
