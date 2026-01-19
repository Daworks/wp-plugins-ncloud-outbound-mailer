/**
 * Ncloud Mailer Admin JavaScript
 *
 * @package NcloudMailer
 */

(function($) {
    'use strict';

    var NcloudMailerAdmin = {
        /**
         * Initialize the admin functionality.
         */
        init: function() {
            this.bindEvents();
        },

        /**
         * Bind event handlers.
         */
        bindEvents: function() {
            $('#ncloud-test-connection').on('click', this.testConnection);
            $('#ncloud-send-test-email').on('click', this.sendTestEmail);
        },

        /**
         * Test API connection.
         *
         * @param {Event} e Click event.
         */
        testConnection: function(e) {
            e.preventDefault();

            var $button = $(this);
            var $result = $('#ncloud-connection-result');
            var originalText = $button.text();

            // Disable button and show loading state.
            $button.prop('disabled', true).text(ncloudMailer.i18n.testing);
            $result.removeClass('success error loading').addClass('loading').text(ncloudMailer.i18n.testing);

            $.ajax({
                url: ncloudMailer.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'ncloud_mailer_test_connection',
                    nonce: ncloudMailer.nonce
                },
                success: function(response) {
                    if (response.success) {
                        $result.removeClass('loading').addClass('success').text(response.data);
                    } else {
                        $result.removeClass('loading').addClass('error').text(ncloudMailer.i18n.error + ' ' + response.data);
                    }
                },
                error: function(xhr, status, error) {
                    $result.removeClass('loading').addClass('error').text(ncloudMailer.i18n.error + ' ' + error);
                },
                complete: function() {
                    $button.prop('disabled', false).text(originalText);
                }
            });
        },

        /**
         * Send test email.
         *
         * @param {Event} e Click event.
         */
        sendTestEmail: function(e) {
            e.preventDefault();

            var $button = $(this);
            var $emailInput = $('#ncloud-test-email-address');
            var $result = $('#ncloud-test-email-result');
            var email = $emailInput.val().trim();
            var originalText = $button.text();

            // Validate email.
            if (!email) {
                $result.removeClass('success loading').addClass('error').text(ncloudMailer.i18n.enterEmail);
                $emailInput.focus();
                return;
            }

            // Basic email validation.
            if (!NcloudMailerAdmin.isValidEmail(email)) {
                $result.removeClass('success loading').addClass('error').text(ncloudMailer.i18n.enterEmail);
                $emailInput.focus();
                return;
            }

            // Disable button and show loading state.
            $button.prop('disabled', true).text(ncloudMailer.i18n.sending);
            $result.removeClass('success error loading').addClass('loading').text(ncloudMailer.i18n.sending);

            $.ajax({
                url: ncloudMailer.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'ncloud_mailer_send_test_email',
                    nonce: ncloudMailer.nonce,
                    email: email
                },
                success: function(response) {
                    if (response.success) {
                        $result.removeClass('loading').addClass('success').text(response.data);
                    } else {
                        $result.removeClass('loading').addClass('error').text(ncloudMailer.i18n.error + ' ' + response.data);
                    }
                },
                error: function(xhr, status, error) {
                    $result.removeClass('loading').addClass('error').text(ncloudMailer.i18n.error + ' ' + error);
                },
                complete: function() {
                    $button.prop('disabled', false).text(originalText);
                }
            });
        },

        /**
         * Basic email validation.
         *
         * @param {string} email Email address to validate.
         * @return {boolean} True if valid.
         */
        isValidEmail: function(email) {
            var re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            return re.test(email);
        }
    };

    // Initialize on document ready.
    $(document).ready(function() {
        NcloudMailerAdmin.init();
    });

})(jQuery);
