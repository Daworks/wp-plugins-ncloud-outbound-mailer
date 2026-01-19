<?php
/**
 * Mail Handler Class Tests
 *
 * @package NcloudMailer\Tests
 */

namespace NcloudMailer\Tests\Unit;

use NcloudMailer\Mail_Handler;
use NcloudMailer\API\Client;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

/**
 * Test case for Mail_Handler class.
 */
class MailHandlerTest extends TestCase {

    /**
     * Mock API client.
     *
     * @var Client
     */
    private $client;

    /**
     * Mail handler instance.
     *
     * @var Mail_Handler
     */
    private $handler;

    /**
     * Set up before each test.
     */
    protected function setUp(): void {
        parent::setUp();
        reset_wp_mock_state();

        // Create a real client with test settings.
        set_mock_option( 'ncloud_mailer_settings', array(
            'enabled'        => false, // Disabled by default for most tests.
            'access_key'     => 'test_key',
            'secret_key'     => 'test_secret',
            'sender_address' => 'noreply@example.com',
            'sender_name'    => 'Test Sender',
            'region'         => 'KR',
        ) );

        $this->client  = new Client();
        $this->handler = new Mail_Handler( $this->client );
    }

    /**
     * Test handler initializes correctly.
     */
    public function test_handler_initializes(): void {
        $this->assertInstanceOf( Mail_Handler::class, $this->handler );
    }

    /**
     * Test intercept_wp_mail returns null when already handled.
     */
    public function test_intercept_returns_null_when_already_handled(): void {
        $result = $this->handler->intercept_wp_mail( true, array() );

        $this->assertTrue( $result );
    }

    /**
     * Test intercept_wp_mail returns null when disabled.
     */
    public function test_intercept_returns_null_when_disabled(): void {
        $result = $this->handler->intercept_wp_mail( null, array(
            'to'      => 'test@example.com',
            'subject' => 'Test',
            'message' => 'Test message',
        ) );

        // Should return null to fall back to default wp_mail.
        $this->assertNull( $result );
    }

    /**
     * Test parse_wp_mail_args parses simple email correctly.
     */
    public function test_parse_wp_mail_args_simple(): void {
        $reflection = new ReflectionClass( $this->handler );
        $method     = $reflection->getMethod( 'parse_wp_mail_args' );
        $method->setAccessible( true );

        $args = array(
            'to'      => 'recipient@example.com',
            'subject' => 'Test Subject',
            'message' => 'Test message body',
            'headers' => array(),
        );

        $result = $method->invoke( $this->handler, $args );

        $this->assertEquals( array( 'recipient@example.com' ), $result['to'] );
        $this->assertEquals( 'Test Subject', $result['subject'] );
        $this->assertEquals( 'Test message body', $result['message'] );
    }

    /**
     * Test parse_wp_mail_args parses multiple recipients.
     */
    public function test_parse_wp_mail_args_multiple_recipients(): void {
        $reflection = new ReflectionClass( $this->handler );
        $method     = $reflection->getMethod( 'parse_wp_mail_args' );
        $method->setAccessible( true );

        $args = array(
            'to'      => 'user1@example.com, user2@example.com, user3@example.com',
            'subject' => 'Test',
            'message' => 'Test',
            'headers' => array(),
        );

        $result = $method->invoke( $this->handler, $args );

        $this->assertCount( 3, $result['to'] );
        $this->assertEquals( 'user1@example.com', $result['to'][0] );
        $this->assertEquals( 'user2@example.com', $result['to'][1] );
        $this->assertEquals( 'user3@example.com', $result['to'][2] );
    }

    /**
     * Test parse_wp_mail_args parses array of recipients.
     */
    public function test_parse_wp_mail_args_array_recipients(): void {
        $reflection = new ReflectionClass( $this->handler );
        $method     = $reflection->getMethod( 'parse_wp_mail_args' );
        $method->setAccessible( true );

        $args = array(
            'to'      => array( 'user1@example.com', 'user2@example.com' ),
            'subject' => 'Test',
            'message' => 'Test',
            'headers' => array(),
        );

        $result = $method->invoke( $this->handler, $args );

        $this->assertCount( 2, $result['to'] );
    }

    /**
     * Test parse_headers parses From header.
     */
    public function test_parse_headers_from(): void {
        $reflection = new ReflectionClass( $this->handler );
        $method     = $reflection->getMethod( 'parse_headers' );
        $method->setAccessible( true );

        $headers = array( 'From: sender@example.com' );
        $result  = $method->invoke( $this->handler, $headers );

        $this->assertEquals( 'sender@example.com', $result['from'] );
    }

    /**
     * Test parse_headers parses From header with name.
     */
    public function test_parse_headers_from_with_name(): void {
        $reflection = new ReflectionClass( $this->handler );
        $method     = $reflection->getMethod( 'parse_headers' );
        $method->setAccessible( true );

        $headers = array( 'From: John Doe <john@example.com>' );
        $result  = $method->invoke( $this->handler, $headers );

        $this->assertEquals( 'john@example.com', $result['from'] );
        $this->assertEquals( 'John Doe', $result['from_name'] );
    }

    /**
     * Test parse_headers parses CC header.
     */
    public function test_parse_headers_cc(): void {
        $reflection = new ReflectionClass( $this->handler );
        $method     = $reflection->getMethod( 'parse_headers' );
        $method->setAccessible( true );

        $headers = array( 'Cc: cc1@example.com, cc2@example.com' );
        $result  = $method->invoke( $this->handler, $headers );

        $this->assertCount( 2, $result['cc'] );
        $this->assertEquals( 'cc1@example.com', $result['cc'][0] );
        $this->assertEquals( 'cc2@example.com', $result['cc'][1] );
    }

    /**
     * Test parse_headers parses BCC header.
     */
    public function test_parse_headers_bcc(): void {
        $reflection = new ReflectionClass( $this->handler );
        $method     = $reflection->getMethod( 'parse_headers' );
        $method->setAccessible( true );

        $headers = array( 'Bcc: bcc@example.com' );
        $result  = $method->invoke( $this->handler, $headers );

        $this->assertCount( 1, $result['bcc'] );
        $this->assertEquals( 'bcc@example.com', $result['bcc'][0] );
    }

    /**
     * Test parse_headers parses Reply-To header.
     */
    public function test_parse_headers_reply_to(): void {
        $reflection = new ReflectionClass( $this->handler );
        $method     = $reflection->getMethod( 'parse_headers' );
        $method->setAccessible( true );

        $headers = array( 'Reply-To: reply@example.com' );
        $result  = $method->invoke( $this->handler, $headers );

        $this->assertEquals( 'reply@example.com', $result['reply_to'] );
    }

    /**
     * Test parse_headers parses Content-Type header.
     */
    public function test_parse_headers_content_type(): void {
        $reflection = new ReflectionClass( $this->handler );
        $method     = $reflection->getMethod( 'parse_headers' );
        $method->setAccessible( true );

        $headers = array( 'Content-Type: text/html; charset=UTF-8' );
        $result  = $method->invoke( $this->handler, $headers );

        $this->assertEquals( 'text/html', $result['content_type'] );
    }

    /**
     * Test parse_headers handles string headers.
     */
    public function test_parse_wp_mail_args_string_headers(): void {
        $reflection = new ReflectionClass( $this->handler );
        $method     = $reflection->getMethod( 'parse_wp_mail_args' );
        $method->setAccessible( true );

        $args = array(
            'to'      => 'test@example.com',
            'subject' => 'Test',
            'message' => 'Test',
            'headers' => "From: sender@example.com\r\nCc: cc@example.com",
        );

        $result = $method->invoke( $this->handler, $args );

        $this->assertEquals( 'sender@example.com', $result['from'] );
        $this->assertCount( 1, $result['cc'] );
    }

    /**
     * Test parse_headers handles multiple headers.
     */
    public function test_parse_headers_multiple(): void {
        $reflection = new ReflectionClass( $this->handler );
        $method     = $reflection->getMethod( 'parse_headers' );
        $method->setAccessible( true );

        $headers = array(
            'From: Test <test@example.com>',
            'Cc: cc1@example.com',
            'Cc: cc2@example.com',
            'Bcc: bcc@example.com',
            'Content-Type: text/html',
        );

        $result = $method->invoke( $this->handler, $headers );

        $this->assertEquals( 'test@example.com', $result['from'] );
        $this->assertEquals( 'Test', $result['from_name'] );
        $this->assertCount( 2, $result['cc'] );
        $this->assertCount( 1, $result['bcc'] );
        $this->assertEquals( 'text/html', $result['content_type'] );
    }

    /**
     * Test parse_headers ignores empty lines.
     */
    public function test_parse_headers_ignores_empty(): void {
        $reflection = new ReflectionClass( $this->handler );
        $method     = $reflection->getMethod( 'parse_headers' );
        $method->setAccessible( true );

        $headers = array(
            '',
            'From: test@example.com',
            '   ',
            'Cc: cc@example.com',
        );

        $result = $method->invoke( $this->handler, $headers );

        $this->assertEquals( 'test@example.com', $result['from'] );
        $this->assertCount( 1, $result['cc'] );
    }

    /**
     * Test parse_from_header with quoted name.
     */
    public function test_parse_from_header_quoted_name(): void {
        $reflection = new ReflectionClass( $this->handler );
        $method     = $reflection->getMethod( 'parse_from_header' );
        $method->setAccessible( true );

        $from   = '"John Doe" <john@example.com>';
        $result = $method->invoke( $this->handler, $from );

        $this->assertEquals( 'john@example.com', $result['from'] );
        $this->assertEquals( 'John Doe', $result['from_name'] );
    }

    /**
     * Test parse_from_header with email only.
     */
    public function test_parse_from_header_email_only(): void {
        $reflection = new ReflectionClass( $this->handler );
        $method     = $reflection->getMethod( 'parse_from_header' );
        $method->setAccessible( true );

        $from   = 'john@example.com';
        $result = $method->invoke( $this->handler, $from );

        $this->assertEquals( 'john@example.com', $result['from'] );
        $this->assertEquals( '', $result['from_name'] );
    }

    /**
     * Test handler adds filter hook.
     */
    public function test_handler_adds_filter(): void {
        global $wp_mock_filters;

        // Create new handler to trigger init_hooks.
        new Mail_Handler( $this->client );

        $this->assertArrayHasKey( 'pre_wp_mail', $wp_mock_filters );
    }
}
