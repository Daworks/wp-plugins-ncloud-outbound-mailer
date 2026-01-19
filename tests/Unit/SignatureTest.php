<?php
/**
 * Signature Class Tests
 *
 * @package NcloudMailer\Tests
 */

namespace NcloudMailer\Tests\Unit;

use NcloudMailer\API\Signature;
use PHPUnit\Framework\TestCase;

/**
 * Test case for Signature class.
 */
class SignatureTest extends TestCase {

    /**
     * Test signature generation with known values.
     *
     * This test verifies that our signature generation matches
     * the expected output based on Ncloud's documentation.
     */
    public function test_generate_signature_format(): void {
        $method     = 'POST';
        $uri        = '/api/v1/mails';
        $timestamp  = '1521787414578';
        $access_key = 'test_access_key';
        $secret_key = 'test_secret_key';

        $signature = Signature::generate(
            $method,
            $uri,
            $timestamp,
            $access_key,
            $secret_key
        );

        // Verify signature is base64 encoded.
        $this->assertNotFalse( base64_decode( $signature, true ) );

        // Verify signature is not empty.
        $this->assertNotEmpty( $signature );

        // Verify signature length is consistent (base64 of SHA256 = 44 chars).
        $this->assertEquals( 44, strlen( $signature ) );
    }

    /**
     * Test that same inputs produce same signature.
     */
    public function test_signature_is_deterministic(): void {
        $method     = 'POST';
        $uri        = '/api/v1/mails';
        $timestamp  = '1234567890123';
        $access_key = 'my_access_key';
        $secret_key = 'my_secret_key';

        $signature1 = Signature::generate( $method, $uri, $timestamp, $access_key, $secret_key );
        $signature2 = Signature::generate( $method, $uri, $timestamp, $access_key, $secret_key );

        $this->assertEquals( $signature1, $signature2 );
    }

    /**
     * Test that different inputs produce different signatures.
     */
    public function test_different_inputs_produce_different_signatures(): void {
        $uri        = '/api/v1/mails';
        $timestamp  = '1234567890123';
        $access_key = 'my_access_key';
        $secret_key = 'my_secret_key';

        $signature_post = Signature::generate( 'POST', $uri, $timestamp, $access_key, $secret_key );
        $signature_get  = Signature::generate( 'GET', $uri, $timestamp, $access_key, $secret_key );

        $this->assertNotEquals( $signature_post, $signature_get );
    }

    /**
     * Test signature changes with different timestamps.
     */
    public function test_signature_changes_with_timestamp(): void {
        $method     = 'POST';
        $uri        = '/api/v1/mails';
        $access_key = 'my_access_key';
        $secret_key = 'my_secret_key';

        $signature1 = Signature::generate( $method, $uri, '1234567890123', $access_key, $secret_key );
        $signature2 = Signature::generate( $method, $uri, '1234567890124', $access_key, $secret_key );

        $this->assertNotEquals( $signature1, $signature2 );
    }

    /**
     * Test signature changes with different secret keys.
     */
    public function test_signature_changes_with_secret_key(): void {
        $method     = 'POST';
        $uri        = '/api/v1/mails';
        $timestamp  = '1234567890123';
        $access_key = 'my_access_key';

        $signature1 = Signature::generate( $method, $uri, $timestamp, $access_key, 'secret_key_1' );
        $signature2 = Signature::generate( $method, $uri, $timestamp, $access_key, 'secret_key_2' );

        $this->assertNotEquals( $signature1, $signature2 );
    }

    /**
     * Test get_timestamp returns valid millisecond timestamp.
     */
    public function test_get_timestamp_returns_milliseconds(): void {
        $timestamp = Signature::get_timestamp();

        // Should be a numeric string.
        $this->assertIsString( $timestamp );
        $this->assertMatchesRegularExpression( '/^\d+$/', $timestamp );

        // Should be 13 digits (milliseconds since epoch).
        $this->assertEquals( 13, strlen( $timestamp ) );

        // Should be close to current time.
        $current_ms = round( microtime( true ) * 1000 );
        $diff       = abs( (float) $timestamp - $current_ms );

        // Allow 1 second difference.
        $this->assertLessThan( 1000, $diff );
    }

    /**
     * Test signature message format matches Ncloud specification.
     */
    public function test_signature_message_format(): void {
        // According to Ncloud docs, message format is:
        // {METHOD} {URI}\n{TIMESTAMP}\n{ACCESS_KEY}
        $method     = 'POST';
        $uri        = '/api/v1/mails';
        $timestamp  = '1521787414578';
        $access_key = 'test_access_key';
        $secret_key = 'test_secret_key';

        // Build expected message.
        $expected_message = "POST /api/v1/mails\n1521787414578\ntest_access_key";

        // Generate expected signature manually.
        $expected_signature = base64_encode(
            hash_hmac( 'sha256', $expected_message, $secret_key, true )
        );

        $actual_signature = Signature::generate(
            $method,
            $uri,
            $timestamp,
            $access_key,
            $secret_key
        );

        $this->assertEquals( $expected_signature, $actual_signature );
    }

    /**
     * Test signature with GET method.
     */
    public function test_signature_with_get_method(): void {
        $signature = Signature::generate(
            'GET',
            '/api/v1/mails',
            '1234567890123',
            'access_key',
            'secret_key'
        );

        $this->assertNotEmpty( $signature );
        $this->assertEquals( 44, strlen( $signature ) );
    }

    /**
     * Test signature with special characters in keys.
     */
    public function test_signature_with_special_characters(): void {
        $signature = Signature::generate(
            'POST',
            '/api/v1/mails',
            '1234567890123',
            'access+key/test=',
            'secret+key/test='
        );

        $this->assertNotEmpty( $signature );
        $this->assertEquals( 44, strlen( $signature ) );
    }
}
