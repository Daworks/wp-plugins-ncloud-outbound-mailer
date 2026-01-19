=== Ncloud Outbound Mailer ===
Contributors: developer
Tags: email, smtp, ncloud, naver cloud, mail
Requires at least: 5.6
Tested up to: 6.4
Requires PHP: 7.4
Stable tag: 1.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Send WordPress emails through Ncloud Cloud Outbound Mailer API.

== Description ==

Ncloud Outbound Mailer allows you to send all WordPress emails through Ncloud Cloud Outbound Mailer API instead of the default PHP mail function.

= Features =

* Easy configuration through WordPress admin
* Support for multiple regions (Korea, Singapore, Japan)
* HTML and plain text email support
* CC and BCC support
* Email logging with last 100 entries
* Test connection and send test email functionality
* Compatible with popular plugins (Contact Form 7, WooCommerce, etc.)

= Requirements =

* WordPress 5.6 or higher
* PHP 7.4 or higher
* Ncloud Cloud Outbound Mailer subscription
* Ncloud API Access Key and Secret Key

= Setup =

1. Sign up for Ncloud Cloud Outbound Mailer service
2. Get your API Access Key and Secret Key from Ncloud Console
3. Go to Settings > Ncloud Mailer in WordPress admin
4. Enter your API credentials and sender information
5. Enable the mailer and test with the test email feature

== Installation ==

1. Upload the `ncloud-outbound-mailer` folder to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Go to Settings > Ncloud Mailer to configure the plugin

== Frequently Asked Questions ==

= Where do I get my API credentials? =

You can get your API Access Key and Secret Key from the [Ncloud Console](https://console.ncloud.com/).

= What regions are supported? =

The plugin supports Korea (KR), Singapore (SGN), and Japan (JPN) regions.

= Does this plugin work with WooCommerce? =

Yes, the plugin replaces the default WordPress wp_mail function, so it works with any plugin that uses wp_mail for sending emails.

= What happens if the API fails? =

By default, if the Ncloud API fails, the email will not be sent. You can use the `ncloud_mailer_fallback_on_error` filter to enable fallback to the default PHP mail function.

== Screenshots ==

1. Settings page - Configure API credentials and sender information
2. Test connection and send test email
3. Email logs showing recent send history

== Changelog ==

= 1.0.0 =
* Initial release
* Basic email sending through Ncloud API
* Admin settings page
* Connection test and test email features
* Email logging

== Upgrade Notice ==

= 1.0.0 =
Initial release.

== Developer Documentation ==

= Filters =

**ncloud_mailer_before_send**
Modify the mail data before sending.

`add_filter( 'ncloud_mailer_before_send', function( $body, $mail_data ) {
    // Modify $body array before sending
    return $body;
}, 10, 2 );`

**ncloud_mailer_fallback_on_error**
Enable fallback to default wp_mail on error.

`add_filter( 'ncloud_mailer_fallback_on_error', '__return_true' );`

**ncloud_mailer_enable_logging**
Disable email logging.

`add_filter( 'ncloud_mailer_enable_logging', '__return_false' );`

= Actions =

**ncloud_mailer_init**
Fires after the plugin is fully initialized.

**ncloud_mailer_after_send**
Fires after successful email sending.

**ncloud_mailer_error**
Fires when an error occurs during email sending.
