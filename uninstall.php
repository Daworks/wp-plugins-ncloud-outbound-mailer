<?php
/**
 * Uninstall script for Ncloud Outbound Mailer
 *
 * This file is executed when the plugin is uninstalled.
 * It removes all plugin data from the database.
 *
 * @package NcloudMailer
 */

// Exit if not called by WordPress.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
    exit;
}

// Delete plugin options.
delete_option( 'ncloud_mailer_settings' );

// Delete transients.
delete_transient( 'ncloud_mailer_logs' );

// Clean up any scheduled events (for future use).
wp_clear_scheduled_hook( 'ncloud_mailer_cleanup' );
