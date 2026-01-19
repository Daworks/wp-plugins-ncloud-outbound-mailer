<?php
/**
 * API Client Class Tests
 *
 * @package NcloudMailer\Tests
 */

namespace NcloudMailer\Tests\Unit;

use NcloudMailer\API\Client;
use PHPUnit\Framework\TestCase;

/**
 * Test case for Client class.
 */
class ClientTest extends TestCase {

    /**
     * Set up before each test.
     */
    protected function setUp(): void {
        parent::setUp();
        reset_wp_mock_state();
    }

    /**
     * Test client initialization with default settings.
     */
    public function test_client_initializes_with_defaults(): void {
        $client = new Client();

        $this->assertInstanceOf( Client::class, $client );
    }

    /**
     * Test is_enabled returns false when not configured.
     */
    public function test_is_enabled_returns_false_when_not_configured(): void {
        $client = new Client();

        $this->assertFalse( $client->is_enabled() );
    }

    /**
     * Test is_enabled returns false when only partially configured.
     */
    public function test_is_enabled_returns_false_when_partially_configured(): void {
        set_mock_option( 'ncloud_mailer_settings', array(
            'enabled'    => true,
            'access_key' => 'test_key',
            // Missing secret_key and sender_address.
        ) );

        $client = new Client();

        $this->assertFalse( $client->is_enabled() );
    }

    /**
     * Test is_enabled returns true when fully configured.
     */
    public function test_is_enabled_returns_true_when_fully_configured(): void {
        set_mock_option( 'ncloud_mailer_settings', array(
            'enabled'        => true,
            'access_key'     => 'test_access_key',
            'secret_key'     => 'test_secret_key',
            'sender_address' => 'test@example.com',
        ) );

        $client = new Client();

        $this->assertTrue( $client->is_enabled() );
    }

    /**
     * Test get_endpoint returns correct URL for KR region.
     */
    public function test_get_endpoint_returns_kr_endpoint(): void {
        set_mock_option( 'ncloud_mailer_settings', array(
            'region' => 'KR',
        ) );

        $client = new Client();

        $this->assertEquals(
            'https://mail.apigw.ntruss.com/api/v1',
            $client->get_endpoint()
        );
    }

    /**
     * Test get_endpoint returns correct URL for SGN region.
     */
    public function test_get_endpoint_returns_sgn_endpoint(): void {
        set_mock_option( 'ncloud_mailer_settings', array(
            'region' => 'SGN',
        ) );

        $client = new Client();

        $this->assertEquals(
            'https://mail.apigw.ntruss.com/api/v1-sgn',
            $client->get_endpoint()
        );
    }

    /**
     * Test get_endpoint returns correct URL for JPN region.
     */
    public function test_get_endpoint_returns_jpn_endpoint(): void {
        set_mock_option( 'ncloud_mailer_settings', array(
            'region' => 'JPN',
        ) );

        $client = new Client();

        $this->assertEquals(
            'https://mail.apigw.ntruss.com/api/v1-jpn',
            $client->get_endpoint()
        );
    }

    /**
     * Test get_endpoint defaults to KR for unknown region.
     */
    public function test_get_endpoint_defaults_to_kr(): void {
        set_mock_option( 'ncloud_mailer_settings', array(
            'region' => 'UNKNOWN',
        ) );

        $client = new Client();

        $this->assertEquals(
            'https://mail.apigw.ntruss.com/api/v1',
            $client->get_endpoint()
        );
    }

    /**
     * Test get_sender_address returns configured address.
     */
    public function test_get_sender_address(): void {
        set_mock_option( 'ncloud_mailer_settings', array(
            'sender_address' => 'noreply@example.com',
        ) );

        $client = new Client();

        $this->assertEquals( 'noreply@example.com', $client->get_sender_address() );
    }

    /**
     * Test get_sender_name returns configured name.
     */
    public function test_get_sender_name(): void {
        set_mock_option( 'ncloud_mailer_settings', array(
            'sender_name' => 'Test Sender',
        ) );

        $client = new Client();

        $this->assertEquals( 'Test Sender', $client->get_sender_name() );
    }

    /**
     * Test send_mail returns error when not configured.
     */
    public function test_send_mail_returns_error_when_not_configured(): void {
        $client = new Client();

        $result = $client->send_mail( array(
            'to'      => array( 'test@example.com' ),
            'subject' => 'Test',
            'message' => 'Test message',
        ) );

        $this->assertInstanceOf( \WP_Error::class, $result );
        $this->assertEquals( 'ncloud_mailer_not_configured', $result->get_error_code() );
    }

    /**
     * Test refresh_settings updates client settings.
     */
    public function test_refresh_settings(): void {
        // Initial empty settings.
        $client = new Client();
        $this->assertFalse( $client->is_enabled() );

        // Update settings.
        set_mock_option( 'ncloud_mailer_settings', array(
            'enabled'        => true,
            'access_key'     => 'test_access_key',
            'secret_key'     => 'test_secret_key',
            'sender_address' => 'test@example.com',
        ) );

        // Refresh and verify.
        $client->refresh_settings();
        $this->assertTrue( $client->is_enabled() );
    }

    /**
     * Test client handles empty sender address gracefully.
     */
    public function test_get_sender_address_returns_empty_when_not_set(): void {
        $client = new Client();

        $this->assertEquals( '', $client->get_sender_address() );
    }

    /**
     * Test client handles empty sender name gracefully.
     */
    public function test_get_sender_name_returns_empty_when_not_set(): void {
        $client = new Client();

        $this->assertEquals( '', $client->get_sender_name() );
    }
}
