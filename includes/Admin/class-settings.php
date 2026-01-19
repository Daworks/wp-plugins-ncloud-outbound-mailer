<?php
/**
 * Admin Settings Page
 *
 * @package NcloudMailer
 */

namespace NcloudMailer\Admin;

use NcloudMailer\API\Client;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Class Settings
 *
 * Handles the admin settings page for Ncloud Mailer.
 */
class Settings {

    /**
     * API Client instance.
     *
     * @var Client
     */
    private $api_client;

    /**
     * Option name for settings.
     */
    private const OPTION_NAME = 'ncloud_mailer_settings';

    /**
     * Settings page slug.
     */
    private const PAGE_SLUG = 'ncloud-mailer';

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
        add_action( 'admin_menu', array( $this, 'add_admin_menu' ) );
        add_action( 'admin_init', array( $this, 'register_settings' ) );
        add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_assets' ) );

        // AJAX handlers.
        add_action( 'wp_ajax_ncloud_mailer_test_connection', array( $this, 'ajax_test_connection' ) );
        add_action( 'wp_ajax_ncloud_mailer_send_test_email', array( $this, 'ajax_send_test_email' ) );
    }

    /**
     * Add admin menu.
     */
    public function add_admin_menu(): void {
        add_options_page(
            __( 'Ncloud Mailer Settings', 'ncloud-outbound-mailer' ),
            __( 'Ncloud Mailer', 'ncloud-outbound-mailer' ),
            'manage_options',
            self::PAGE_SLUG,
            array( $this, 'render_settings_page' )
        );
    }

    /**
     * Register settings.
     */
    public function register_settings(): void {
        register_setting(
            'ncloud_mailer_settings_group',
            self::OPTION_NAME,
            array(
                'type'              => 'array',
                'sanitize_callback' => array( $this, 'sanitize_settings' ),
                'default'           => array(),
            )
        );

        // General Settings Section.
        add_settings_section(
            'ncloud_mailer_general',
            __( 'API Configuration', 'ncloud-outbound-mailer' ),
            array( $this, 'render_section_description' ),
            self::PAGE_SLUG
        );

        // Enable/Disable.
        add_settings_field(
            'enabled',
            __( 'Enable Ncloud Mailer', 'ncloud-outbound-mailer' ),
            array( $this, 'render_checkbox_field' ),
            self::PAGE_SLUG,
            'ncloud_mailer_general',
            array(
                'label_for'   => 'enabled',
                'description' => __( 'Enable sending emails through Ncloud Cloud Outbound Mailer.', 'ncloud-outbound-mailer' ),
            )
        );

        // Region.
        add_settings_field(
            'region',
            __( 'Region', 'ncloud-outbound-mailer' ),
            array( $this, 'render_select_field' ),
            self::PAGE_SLUG,
            'ncloud_mailer_general',
            array(
                'label_for' => 'region',
                'options'   => array(
                    'KR'  => __( 'Korea (KR)', 'ncloud-outbound-mailer' ),
                    'SGN' => __( 'Singapore (SGN)', 'ncloud-outbound-mailer' ),
                    'JPN' => __( 'Japan (JPN)', 'ncloud-outbound-mailer' ),
                ),
            )
        );

        // Access Key.
        add_settings_field(
            'access_key',
            __( 'Access Key', 'ncloud-outbound-mailer' ),
            array( $this, 'render_text_field' ),
            self::PAGE_SLUG,
            'ncloud_mailer_general',
            array(
                'label_for'   => 'access_key',
                'type'        => 'text',
                'description' => __( 'Your Ncloud API Access Key.', 'ncloud-outbound-mailer' ),
            )
        );

        // Secret Key.
        add_settings_field(
            'secret_key',
            __( 'Secret Key', 'ncloud-outbound-mailer' ),
            array( $this, 'render_password_field' ),
            self::PAGE_SLUG,
            'ncloud_mailer_general',
            array(
                'label_for'   => 'secret_key',
                'description' => __( 'Your Ncloud API Secret Key.', 'ncloud-outbound-mailer' ),
            )
        );

        // Sender Settings Section.
        add_settings_section(
            'ncloud_mailer_sender',
            __( 'Sender Settings', 'ncloud-outbound-mailer' ),
            null,
            self::PAGE_SLUG
        );

        // Sender Address.
        add_settings_field(
            'sender_address',
            __( 'Sender Email Address', 'ncloud-outbound-mailer' ),
            array( $this, 'render_text_field' ),
            self::PAGE_SLUG,
            'ncloud_mailer_sender',
            array(
                'label_for'   => 'sender_address',
                'type'        => 'email',
                'description' => __( 'The email address that will appear as the sender.', 'ncloud-outbound-mailer' ),
            )
        );

        // Sender Name.
        add_settings_field(
            'sender_name',
            __( 'Sender Name', 'ncloud-outbound-mailer' ),
            array( $this, 'render_text_field' ),
            self::PAGE_SLUG,
            'ncloud_mailer_sender',
            array(
                'label_for'   => 'sender_name',
                'type'        => 'text',
                'description' => __( 'The name that will appear as the sender.', 'ncloud-outbound-mailer' ),
            )
        );
    }

    /**
     * Enqueue admin assets.
     *
     * @param string $hook Current admin page.
     */
    public function enqueue_assets( string $hook ): void {
        if ( 'settings_page_' . self::PAGE_SLUG !== $hook ) {
            return;
        }

        wp_enqueue_style(
            'ncloud-mailer-admin',
            NCLOUD_MAILER_PLUGIN_URL . 'admin/css/admin.css',
            array(),
            NCLOUD_MAILER_VERSION
        );

        wp_enqueue_script(
            'ncloud-mailer-admin',
            NCLOUD_MAILER_PLUGIN_URL . 'admin/js/admin.js',
            array( 'jquery' ),
            NCLOUD_MAILER_VERSION,
            true
        );

        wp_localize_script(
            'ncloud-mailer-admin',
            'ncloudMailer',
            array(
                'ajaxUrl' => admin_url( 'admin-ajax.php' ),
                'nonce'   => wp_create_nonce( 'ncloud_mailer_admin' ),
                'i18n'    => array(
                    'testing'       => __( 'Testing...', 'ncloud-outbound-mailer' ),
                    'sending'       => __( 'Sending...', 'ncloud-outbound-mailer' ),
                    'success'       => __( 'Success!', 'ncloud-outbound-mailer' ),
                    'error'         => __( 'Error:', 'ncloud-outbound-mailer' ),
                    'testEmail'     => __( 'Test email sent successfully!', 'ncloud-outbound-mailer' ),
                    'enterEmail'    => __( 'Please enter a test email address.', 'ncloud-outbound-mailer' ),
                ),
            )
        );
    }

    /**
     * Render the settings page.
     */
    public function render_settings_page(): void {
        if ( ! current_user_can( 'manage_options' ) ) {
            return;
        }

        // Check if settings were saved.
        if ( isset( $_GET['settings-updated'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
            add_settings_error(
                'ncloud_mailer_messages',
                'ncloud_mailer_message',
                __( 'Settings Saved', 'ncloud-outbound-mailer' ),
                'updated'
            );
        }

        ?>
        <div class="wrap ncloud-mailer-wrap">
            <h1><?php echo esc_html( get_admin_page_title() ); ?></h1>

            <?php settings_errors( 'ncloud_mailer_messages' ); ?>

            <form action="options.php" method="post">
                <?php
                settings_fields( 'ncloud_mailer_settings_group' );
                do_settings_sections( self::PAGE_SLUG );
                submit_button( __( 'Save Settings', 'ncloud-outbound-mailer' ) );
                ?>
            </form>

            <hr>

            <h2><?php esc_html_e( 'Test Connection & Email', 'ncloud-outbound-mailer' ); ?></h2>

            <table class="form-table ncloud-mailer-test-section">
                <tr>
                    <th scope="row"><?php esc_html_e( 'API Connection Test', 'ncloud-outbound-mailer' ); ?></th>
                    <td>
                        <button type="button" class="button" id="ncloud-test-connection">
                            <?php esc_html_e( 'Test Connection', 'ncloud-outbound-mailer' ); ?>
                        </button>
                        <span id="ncloud-connection-result" class="ncloud-result"></span>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><?php esc_html_e( 'Send Test Email', 'ncloud-outbound-mailer' ); ?></th>
                    <td>
                        <input type="email"
                               id="ncloud-test-email-address"
                               class="regular-text"
                               placeholder="<?php esc_attr_e( 'Enter test email address', 'ncloud-outbound-mailer' ); ?>">
                        <button type="button" class="button" id="ncloud-send-test-email">
                            <?php esc_html_e( 'Send Test Email', 'ncloud-outbound-mailer' ); ?>
                        </button>
                        <span id="ncloud-test-email-result" class="ncloud-result"></span>
                    </td>
                </tr>
            </table>

            <?php $this->render_logs_section(); ?>
        </div>
        <?php
    }

    /**
     * Render logs section.
     */
    private function render_logs_section(): void {
        $logs = get_transient( 'ncloud_mailer_logs' ) ?: array();
        $logs = array_reverse( $logs ); // Show newest first.
        ?>
        <hr>
        <h2><?php esc_html_e( 'Recent Email Logs', 'ncloud-outbound-mailer' ); ?></h2>

        <?php if ( empty( $logs ) ) : ?>
            <p><?php esc_html_e( 'No email logs yet.', 'ncloud-outbound-mailer' ); ?></p>
        <?php else : ?>
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th><?php esc_html_e( 'Time', 'ncloud-outbound-mailer' ); ?></th>
                        <th><?php esc_html_e( 'Status', 'ncloud-outbound-mailer' ); ?></th>
                        <th><?php esc_html_e( 'To', 'ncloud-outbound-mailer' ); ?></th>
                        <th><?php esc_html_e( 'Subject', 'ncloud-outbound-mailer' ); ?></th>
                        <th><?php esc_html_e( 'Details', 'ncloud-outbound-mailer' ); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ( array_slice( $logs, 0, 20 ) as $log ) : ?>
                        <tr>
                            <td><?php echo esc_html( $log['time'] ?? '' ); ?></td>
                            <td>
                                <?php if ( 'success' === ( $log['status'] ?? '' ) ) : ?>
                                    <span class="ncloud-status-success">✓ <?php esc_html_e( 'Sent', 'ncloud-outbound-mailer' ); ?></span>
                                <?php else : ?>
                                    <span class="ncloud-status-error">✗ <?php esc_html_e( 'Failed', 'ncloud-outbound-mailer' ); ?></span>
                                <?php endif; ?>
                            </td>
                            <td><?php echo esc_html( implode( ', ', (array) ( $log['to'] ?? array() ) ) ); ?></td>
                            <td><?php echo esc_html( $log['subject'] ?? '' ); ?></td>
                            <td>
                                <?php if ( ! empty( $log['request_id'] ) ) : ?>
                                    <?php echo esc_html( $log['request_id'] ); ?>
                                <?php elseif ( ! empty( $log['message'] ) ) : ?>
                                    <?php echo esc_html( $log['message'] ); ?>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
        <?php
    }

    /**
     * Render section description.
     */
    public function render_section_description(): void {
        echo '<p>' . esc_html__( 'Enter your Ncloud Cloud Outbound Mailer API credentials below.', 'ncloud-outbound-mailer' ) . '</p>';
        echo '<p><a href="https://console.ncloud.com/mc/solution/outboundMailer" target="_blank">';
        echo esc_html__( 'Get your API credentials from Ncloud Console', 'ncloud-outbound-mailer' );
        echo '</a></p>';
    }

    /**
     * Render checkbox field.
     *
     * @param array $args Field arguments.
     */
    public function render_checkbox_field( array $args ): void {
        $options = get_option( self::OPTION_NAME, array() );
        $value   = $options[ $args['label_for'] ] ?? false;
        ?>
        <label>
            <input type="checkbox"
                   id="<?php echo esc_attr( $args['label_for'] ); ?>"
                   name="<?php echo esc_attr( self::OPTION_NAME . '[' . $args['label_for'] . ']' ); ?>"
                   value="1"
                   <?php checked( $value, true ); ?>>
            <?php if ( ! empty( $args['description'] ) ) : ?>
                <?php echo esc_html( $args['description'] ); ?>
            <?php endif; ?>
        </label>
        <?php
    }

    /**
     * Render select field.
     *
     * @param array $args Field arguments.
     */
    public function render_select_field( array $args ): void {
        $options = get_option( self::OPTION_NAME, array() );
        $value   = $options[ $args['label_for'] ] ?? 'KR';
        ?>
        <select id="<?php echo esc_attr( $args['label_for'] ); ?>"
                name="<?php echo esc_attr( self::OPTION_NAME . '[' . $args['label_for'] . ']' ); ?>">
            <?php foreach ( $args['options'] as $key => $label ) : ?>
                <option value="<?php echo esc_attr( $key ); ?>" <?php selected( $value, $key ); ?>>
                    <?php echo esc_html( $label ); ?>
                </option>
            <?php endforeach; ?>
        </select>
        <?php
    }

    /**
     * Render text field.
     *
     * @param array $args Field arguments.
     */
    public function render_text_field( array $args ): void {
        $options = get_option( self::OPTION_NAME, array() );
        $value   = $options[ $args['label_for'] ] ?? '';
        $type    = $args['type'] ?? 'text';
        ?>
        <input type="<?php echo esc_attr( $type ); ?>"
               id="<?php echo esc_attr( $args['label_for'] ); ?>"
               name="<?php echo esc_attr( self::OPTION_NAME . '[' . $args['label_for'] . ']' ); ?>"
               value="<?php echo esc_attr( $value ); ?>"
               class="regular-text">
        <?php if ( ! empty( $args['description'] ) ) : ?>
            <p class="description"><?php echo esc_html( $args['description'] ); ?></p>
        <?php endif; ?>
        <?php
    }

    /**
     * Render password field.
     *
     * @param array $args Field arguments.
     */
    public function render_password_field( array $args ): void {
        $options = get_option( self::OPTION_NAME, array() );
        $value   = $options[ $args['label_for'] ] ?? '';
        ?>
        <input type="password"
               id="<?php echo esc_attr( $args['label_for'] ); ?>"
               name="<?php echo esc_attr( self::OPTION_NAME . '[' . $args['label_for'] . ']' ); ?>"
               value="<?php echo esc_attr( $value ); ?>"
               class="regular-text"
               autocomplete="new-password">
        <?php if ( ! empty( $args['description'] ) ) : ?>
            <p class="description"><?php echo esc_html( $args['description'] ); ?></p>
        <?php endif; ?>
        <?php
    }

    /**
     * Sanitize settings.
     *
     * @param array $input Raw input.
     * @return array Sanitized input.
     */
    public function sanitize_settings( array $input ): array {
        $sanitized = array();

        $sanitized['enabled']        = ! empty( $input['enabled'] );
        $sanitized['region']         = sanitize_text_field( $input['region'] ?? 'KR' );
        $sanitized['access_key']     = sanitize_text_field( $input['access_key'] ?? '' );
        $sanitized['secret_key']     = sanitize_text_field( $input['secret_key'] ?? '' );
        $sanitized['sender_address'] = sanitize_email( $input['sender_address'] ?? '' );
        $sanitized['sender_name']    = sanitize_text_field( $input['sender_name'] ?? '' );

        // Validate region.
        $valid_regions = array( 'KR', 'SGN', 'JPN' );
        if ( ! in_array( $sanitized['region'], $valid_regions, true ) ) {
            $sanitized['region'] = 'KR';
        }

        // Validate sender email.
        if ( ! empty( $sanitized['sender_address'] ) && ! is_email( $sanitized['sender_address'] ) ) {
            add_settings_error(
                'ncloud_mailer_messages',
                'invalid_email',
                __( 'Please enter a valid sender email address.', 'ncloud-outbound-mailer' ),
                'error'
            );
        }

        return $sanitized;
    }

    /**
     * AJAX handler for testing connection.
     */
    public function ajax_test_connection(): void {
        check_ajax_referer( 'ncloud_mailer_admin', 'nonce' );

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( __( 'Permission denied.', 'ncloud-outbound-mailer' ) );
        }

        $this->api_client->refresh_settings();
        $result = $this->api_client->test_connection();

        if ( is_wp_error( $result ) ) {
            wp_send_json_error( $result->get_error_message() );
        }

        wp_send_json_success( $result['message'] );
    }

    /**
     * AJAX handler for sending test email.
     */
    public function ajax_send_test_email(): void {
        check_ajax_referer( 'ncloud_mailer_admin', 'nonce' );

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( __( 'Permission denied.', 'ncloud-outbound-mailer' ) );
        }

        $to = isset( $_POST['email'] ) ? sanitize_email( wp_unslash( $_POST['email'] ) ) : '';

        if ( ! is_email( $to ) ) {
            wp_send_json_error( __( 'Please enter a valid email address.', 'ncloud-outbound-mailer' ) );
        }

        $this->api_client->refresh_settings();

        // Prepare test email.
        $subject = sprintf(
            /* translators: %s: Site name */
            __( '[Test] Ncloud Mailer Test from %s', 'ncloud-outbound-mailer' ),
            get_bloginfo( 'name' )
        );

        $message = sprintf(
            /* translators: 1: Site name, 2: Current time */
            __( "This is a test email from %1\$s.\n\nSent at: %2\$s\n\nIf you received this email, your Ncloud Mailer configuration is working correctly!", 'ncloud-outbound-mailer' ),
            get_bloginfo( 'name' ),
            current_time( 'mysql' )
        );

        // Use wp_mail which will be intercepted by our handler.
        $result = wp_mail( $to, $subject, $message );

        if ( $result ) {
            wp_send_json_success( __( 'Test email sent successfully!', 'ncloud-outbound-mailer' ) );
        } else {
            wp_send_json_error( __( 'Failed to send test email. Check the logs for details.', 'ncloud-outbound-mailer' ) );
        }
    }
}
