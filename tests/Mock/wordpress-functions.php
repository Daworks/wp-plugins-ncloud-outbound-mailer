<?php
/**
 * WordPress Mock Functions for Testing
 *
 * @package NcloudMailer\Tests
 */

// Define ABSPATH if not defined.
if ( ! defined( 'ABSPATH' ) ) {
    define( 'ABSPATH', '/tmp/wordpress/' );
}

// Mock options storage.
global $wp_mock_options;
$wp_mock_options = array();

// Mock transients storage.
global $wp_mock_transients;
$wp_mock_transients = array();

// Mock filters storage.
global $wp_mock_filters;
$wp_mock_filters = array();

// Mock actions storage.
global $wp_mock_actions;
$wp_mock_actions = array();

/**
 * Mock get_option function.
 */
if ( ! function_exists( 'get_option' ) ) {
    function get_option( $option, $default = false ) {
        global $wp_mock_options;
        return $wp_mock_options[ $option ] ?? $default;
    }
}

/**
 * Mock update_option function.
 */
if ( ! function_exists( 'update_option' ) ) {
    function update_option( $option, $value ) {
        global $wp_mock_options;
        $wp_mock_options[ $option ] = $value;
        return true;
    }
}

/**
 * Mock add_option function.
 */
if ( ! function_exists( 'add_option' ) ) {
    function add_option( $option, $value = '' ) {
        global $wp_mock_options;
        if ( ! isset( $wp_mock_options[ $option ] ) ) {
            $wp_mock_options[ $option ] = $value;
        }
        return true;
    }
}

/**
 * Mock delete_option function.
 */
if ( ! function_exists( 'delete_option' ) ) {
    function delete_option( $option ) {
        global $wp_mock_options;
        unset( $wp_mock_options[ $option ] );
        return true;
    }
}

/**
 * Mock get_transient function.
 */
if ( ! function_exists( 'get_transient' ) ) {
    function get_transient( $transient ) {
        global $wp_mock_transients;
        return $wp_mock_transients[ $transient ] ?? false;
    }
}

/**
 * Mock set_transient function.
 */
if ( ! function_exists( 'set_transient' ) ) {
    function set_transient( $transient, $value, $expiration = 0 ) {
        global $wp_mock_transients;
        $wp_mock_transients[ $transient ] = $value;
        return true;
    }
}

/**
 * Mock delete_transient function.
 */
if ( ! function_exists( 'delete_transient' ) ) {
    function delete_transient( $transient ) {
        global $wp_mock_transients;
        unset( $wp_mock_transients[ $transient ] );
        return true;
    }
}

/**
 * Mock wp_parse_args function.
 */
if ( ! function_exists( 'wp_parse_args' ) ) {
    function wp_parse_args( $args, $defaults = array() ) {
        if ( is_object( $args ) ) {
            $parsed_args = get_object_vars( $args );
        } elseif ( is_array( $args ) ) {
            $parsed_args = $args;
        } else {
            parse_str( $args, $parsed_args );
        }
        return array_merge( $defaults, $parsed_args );
    }
}

/**
 * Mock wp_json_encode function.
 */
if ( ! function_exists( 'wp_json_encode' ) ) {
    function wp_json_encode( $data, $options = 0, $depth = 512 ) {
        return json_encode( $data, $options, $depth );
    }
}

/**
 * Mock apply_filters function.
 */
if ( ! function_exists( 'apply_filters' ) ) {
    function apply_filters( $tag, $value, ...$args ) {
        global $wp_mock_filters;
        if ( isset( $wp_mock_filters[ $tag ] ) ) {
            foreach ( $wp_mock_filters[ $tag ] as $callback ) {
                $value = call_user_func_array( $callback, array_merge( array( $value ), $args ) );
            }
        }
        return $value;
    }
}

/**
 * Mock add_filter function.
 */
if ( ! function_exists( 'add_filter' ) ) {
    function add_filter( $tag, $callback, $priority = 10, $accepted_args = 1 ) {
        global $wp_mock_filters;
        if ( ! isset( $wp_mock_filters[ $tag ] ) ) {
            $wp_mock_filters[ $tag ] = array();
        }
        $wp_mock_filters[ $tag ][] = $callback;
        return true;
    }
}

/**
 * Mock do_action function.
 */
if ( ! function_exists( 'do_action' ) ) {
    function do_action( $tag, ...$args ) {
        global $wp_mock_actions;
        if ( isset( $wp_mock_actions[ $tag ] ) ) {
            foreach ( $wp_mock_actions[ $tag ] as $callback ) {
                call_user_func_array( $callback, $args );
            }
        }
    }
}

/**
 * Mock add_action function.
 */
if ( ! function_exists( 'add_action' ) ) {
    function add_action( $tag, $callback, $priority = 10, $accepted_args = 1 ) {
        global $wp_mock_actions;
        if ( ! isset( $wp_mock_actions[ $tag ] ) ) {
            $wp_mock_actions[ $tag ] = array();
        }
        $wp_mock_actions[ $tag ][] = $callback;
        return true;
    }
}

/**
 * Mock is_email function.
 */
if ( ! function_exists( 'is_email' ) ) {
    function is_email( $email ) {
        return filter_var( $email, FILTER_VALIDATE_EMAIL ) !== false;
    }
}

/**
 * Mock sanitize_email function.
 */
if ( ! function_exists( 'sanitize_email' ) ) {
    function sanitize_email( $email ) {
        return filter_var( $email, FILTER_SANITIZE_EMAIL );
    }
}

/**
 * Mock sanitize_text_field function.
 */
if ( ! function_exists( 'sanitize_text_field' ) ) {
    function sanitize_text_field( $str ) {
        return trim( strip_tags( $str ) );
    }
}

/**
 * Mock current_time function.
 */
if ( ! function_exists( 'current_time' ) ) {
    function current_time( $type, $gmt = 0 ) {
        if ( 'mysql' === $type ) {
            return gmdate( 'Y-m-d H:i:s' );
        }
        return time();
    }
}

/**
 * Mock __ function (translation).
 */
if ( ! function_exists( '__' ) ) {
    function __( $text, $domain = 'default' ) {
        return $text;
    }
}

/**
 * Mock esc_html__ function.
 */
if ( ! function_exists( 'esc_html__' ) ) {
    function esc_html__( $text, $domain = 'default' ) {
        return htmlspecialchars( $text, ENT_QUOTES, 'UTF-8' );
    }
}

/**
 * Mock WP_Error class.
 */
if ( ! class_exists( 'WP_Error' ) ) {
    class WP_Error {
        private $code;
        private $message;
        private $data;

        public function __construct( $code = '', $message = '', $data = '' ) {
            $this->code    = $code;
            $this->message = $message;
            $this->data    = $data;
        }

        public function get_error_code() {
            return $this->code;
        }

        public function get_error_message() {
            return $this->message;
        }

        public function get_error_data() {
            return $this->data;
        }
    }
}

/**
 * Mock is_wp_error function.
 */
if ( ! function_exists( 'is_wp_error' ) ) {
    function is_wp_error( $thing ) {
        return $thing instanceof WP_Error;
    }
}

/**
 * Helper function to reset mock state.
 */
function reset_wp_mock_state() {
    global $wp_mock_options, $wp_mock_transients, $wp_mock_filters, $wp_mock_actions;
    $wp_mock_options    = array();
    $wp_mock_transients = array();
    $wp_mock_filters    = array();
    $wp_mock_actions    = array();
}

/**
 * Helper function to set mock options.
 */
function set_mock_option( $option, $value ) {
    global $wp_mock_options;
    $wp_mock_options[ $option ] = $value;
}
