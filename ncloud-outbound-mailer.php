<?php
/**
 * Plugin Name: Ncloud Outbound Mailer
 * Plugin URI: https://wordpress.org/plugins/ncloud-outbound-mailer/
 * Description: Send WordPress emails through Ncloud Cloud Outbound Mailer API
 * Version: 1.0.2
 * Requires at least: 5.6
 * Requires PHP: 7.4
 * Author: Design Arete
 * Author URI: https://daworks.io
 * License: MIT
 * License URI: https://opensource.org/licenses/MIT
 * Text Domain: ncloud-outbound-mailer
 * Domain Path: /languages
 *
 * @package NcloudMailer
 * @copyright 2024 Design Arete
 */

namespace NcloudMailer;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Plugin constants.
define( 'NCLOUD_MAILER_VERSION', '1.0.2' );
define( 'NCLOUD_MAILER_PLUGIN_FILE', __FILE__ );
define( 'NCLOUD_MAILER_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'NCLOUD_MAILER_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'NCLOUD_MAILER_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );

// Autoloader.
if ( file_exists( NCLOUD_MAILER_PLUGIN_DIR . 'vendor/autoload.php' ) ) {
    require_once NCLOUD_MAILER_PLUGIN_DIR . 'vendor/autoload.php';
} else {
    // Manual autoloader for when composer autoload is not available.
    spl_autoload_register(
        function ( $class ) {
            $prefix   = 'NcloudMailer\\';
            $base_dir = NCLOUD_MAILER_PLUGIN_DIR . 'includes/';

            $len = strlen( $prefix );
            if ( strncmp( $prefix, $class, $len ) !== 0 ) {
                return;
            }

            $relative_class = substr( $class, $len );

            // Split namespace and class name.
            $parts      = explode( '\\', $relative_class );
            $class_name = array_pop( $parts );
            $subdir     = implode( '/', $parts );

            // Build the file path: subdirectory stays as-is, class name gets WordPress prefix.
            $file = $base_dir;
            if ( ! empty( $subdir ) ) {
                $file .= $subdir . '/';
            }
            $file .= 'class-' . strtolower( str_replace( '_', '-', $class_name ) ) . '.php';

            if ( file_exists( $file ) ) {
                require $file;
            }
        }
    );
}

/**
 * Main plugin class.
 */
final class Plugin {

    /**
     * Single instance of the class.
     *
     * @var Plugin|null
     */
    private static $instance = null;

    /**
     * API client instance.
     *
     * @var API\Client|null
     */
    private $api_client = null;

    /**
     * Mail handler instance.
     *
     * @var Mail_Handler|null
     */
    private $mail_handler = null;

    /**
     * Admin instance.
     *
     * @var Admin\Settings|null
     */
    private $admin = null;

    /**
     * Get single instance of the class.
     *
     * @return Plugin
     */
    public static function get_instance(): Plugin {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Constructor.
     */
    private function __construct() {
        $this->init_hooks();
    }

    /**
     * Initialize hooks.
     */
    private function init_hooks(): void {
        // Initialize plugin after plugins loaded.
        add_action( 'plugins_loaded', array( $this, 'init' ) );

        // Activation/Deactivation hooks.
        register_activation_hook( NCLOUD_MAILER_PLUGIN_FILE, array( $this, 'activate' ) );
        register_deactivation_hook( NCLOUD_MAILER_PLUGIN_FILE, array( $this, 'deactivate' ) );
    }

    /**
     * Initialize the plugin.
     */
    public function init(): void {
        // Load text domain for translations.
        load_plugin_textdomain(
            'ncloud-outbound-mailer',
            false,
            dirname( NCLOUD_MAILER_PLUGIN_BASENAME ) . '/languages'
        );

        // Initialize API client.
        $this->api_client = new API\Client();

        // Initialize mail handler.
        $this->mail_handler = new Mail_Handler( $this->api_client );

        // Initialize admin if in admin area.
        if ( is_admin() ) {
            $this->admin = new Admin\Settings( $this->api_client );
        }

        /**
         * Fires after the plugin is fully initialized.
         *
         * @param Plugin $plugin The plugin instance.
         */
        do_action( 'ncloud_mailer_init', $this );
    }

    /**
     * Plugin activation.
     */
    public function activate(): void {
        // Set default options.
        $default_options = array(
            'access_key'     => '',
            'secret_key'     => '',
            'sender_address' => '',
            'sender_name'    => get_bloginfo( 'name' ),
            'region'         => 'KR',
            'enabled'        => false,
        );

        if ( false === get_option( 'ncloud_mailer_settings' ) ) {
            add_option( 'ncloud_mailer_settings', $default_options );
        }

        // Flush rewrite rules.
        flush_rewrite_rules();
    }

    /**
     * Plugin deactivation.
     */
    public function deactivate(): void {
        // Flush rewrite rules.
        flush_rewrite_rules();
    }

    /**
     * Get the API client.
     *
     * @return API\Client|null
     */
    public function get_api_client(): ?API\Client {
        return $this->api_client;
    }

    /**
     * Get the mail handler.
     *
     * @return Mail_Handler|null
     */
    public function get_mail_handler(): ?Mail_Handler {
        return $this->mail_handler;
    }
}

/**
 * Get the plugin instance.
 *
 * @return Plugin
 */
function ncloud_mailer(): Plugin {
    return Plugin::get_instance();
}

// Initialize the plugin.
ncloud_mailer();
