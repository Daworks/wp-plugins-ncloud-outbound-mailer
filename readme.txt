=== Ncloud Outbound Mailer ===
Contributors: designarete
Donate link: https://daworks.io
Tags: email, smtp, ncloud, naver cloud, mail
Requires at least: 5.6
Tested up to: 6.9
Requires PHP: 7.4
Stable tag: 1.0.1
License: MIT
License URI: https://opensource.org/licenses/MIT

Send WordPress emails through Ncloud Cloud Outbound Mailer API.

Developed by [Design Arete](https://daworks.io) - Professional WordPress Development.

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
3. Register and verify your sending domain (see Domain Setup below)
4. Go to Settings > Ncloud Mailer in WordPress admin
5. Enter your API credentials and sender information
6. Enable the mailer and test with the test email feature

= Domain Setup =

Before sending emails, you must register and verify your domain in Ncloud Console.

**Step 1: Register Domain**

1. Go to [Ncloud Console](https://console.ncloud.com/) > Cloud Outbound Mailer > Domain Management
2. Click "+ 도메인 등록" (Add Domain)
3. Enter your domain name (e.g., example.com)

**Step 2: Domain Verification Token**

Add a TXT record to verify domain ownership:

1. In Domain Management, click "보기" (View) next to "인증 토큰" (Verification Token)
2. Copy the verification token value
3. Add a TXT record to your DNS:
   * Host: @ (or your domain)
   * Type: TXT
   * Value: (paste the verification token)
4. Click "새로 고침" (Refresh) to verify

**Step 3: SPF Record**

SPF (Sender Policy Framework) authorizes Ncloud to send emails on your behalf:

1. Click "보기" (View) next to "SPF 레코드"
2. Copy the SPF record value
3. Add a TXT record to your DNS:
   * Host: @
   * Type: TXT
   * Value: `v=spf1 include:_spfblocka.ncloud.com ~all`
4. Click "사용" (Enable) to activate SPF

**Step 4: DKIM Record**

DKIM (DomainKeys Identified Mail) adds a digital signature to your emails:

1. Click "보기" (View) next to "DKIM"
2. Copy the DKIM record value
3. Add a TXT record to your DNS:
   * Host: (provided selector, e.g., `ncloud._domainkey`)
   * Type: TXT
   * Value: (paste the DKIM public key)
4. Click "사용" (Enable) to activate DKIM

**Step 5: DMARC Record (Recommended)**

DMARC provides instructions for handling authentication failures:

1. Add a TXT record to your DNS:
   * Host: `_dmarc`
   * Type: TXT
   * Value: `v=DMARC1; p=none; rua=mailto:dmarc@yourdomain.com`
2. After verification, consider changing policy to `p=quarantine` or `p=reject`

**DNS Record Summary**

| Type | Host | Value |
| --- | --- | --- |
| TXT | @ | (Verification Token) |
| TXT | @ | v=spf1 include:_spfblocka.ncloud.com ~all |
| TXT | ncloud._domainkey | (DKIM Public Key) |
| TXT | _dmarc | v=DMARC1; p=none; rua=mailto:you@domain.com |

Note: DNS propagation may take up to 24-48 hours. The verification status will show "인증 완료" (Verified) when complete.

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

= 1.0.1 =
* Add Korean (ko_KR) translation
* Add load_plugin_textdomain for i18n support
* Update translation strings in POT file

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

= Debugging =

**Email Logs**

The plugin stores the last 100 email logs in a WordPress transient (`ncloud_mailer_logs`). You can retrieve logs programmatically:

`$logs = get_transient( 'ncloud_mailer_logs' );
foreach ( $logs as $log ) {
    echo $log['time'] . ' - ' . $log['status'] . ' - ' . $log['subject'];
}`

Each log entry contains:
* `time` - Timestamp of the email
* `status` - 'success' or 'error'
* `to` - Recipient email addresses
* `subject` - Email subject
* `request_id` - Ncloud request ID (success only)
* `code` - Error code (error only)
* `message` - Error message (error only)

**WordPress Debug Log**

When `WP_DEBUG` is enabled, errors are also logged to `wp-content/debug.log`:

`// In wp-config.php
define( 'WP_DEBUG', true );
define( 'WP_DEBUG_LOG', true );`

Log format: `[Ncloud Mailer Error] {code}: {message} (To: {recipients}, Subject: {subject})`

**Disabling Logs**

To disable logging entirely:

`add_filter( 'ncloud_mailer_enable_logging', '__return_false' );`
