<?php
/**
 * PHPUnit Bootstrap
 *
 * @package NcloudMailer\Tests
 */

// Composer autoloader.
require_once dirname( __DIR__ ) . '/vendor/autoload.php';

// Load WordPress mock functions.
require_once __DIR__ . '/Mock/wordpress-functions.php';

// Load plugin classes manually for testing.
require_once dirname( __DIR__ ) . '/includes/API/class-signature.php';
require_once dirname( __DIR__ ) . '/includes/API/class-client.php';
require_once dirname( __DIR__ ) . '/includes/class-mail-handler.php';
