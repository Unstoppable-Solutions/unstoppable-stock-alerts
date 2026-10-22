/**
 * Unstoppable Stock Alerts - Frontend JavaScript
 *
 * Handles AJAX subscription forms with no page reload
 *
 * @package Unstoppable_Stock_Alerts
 * @version 1.0.0
 */

(function($) {
    'use strict';

    /**
     * Stock Alert Form Handler
     */
    var USSA_Frontend = {
        
        /**
         * Initialize
         */
        init: function() {
            this.bindEvents();
            this.initVariationHandling();
        },

        /**
         * Bind events
         */
        bindEvents: function() {
            // Subscribe form submission
            $(document).on('submit', '.ussa-subscribe-form', this.handleSubscribe);
            
            // Unsubscribe button
            $(document).on('click', '.ussa-unsubscribe-btn', this.handleUnsubscribe);
            
            // Email input validation
            $(document).on('input', '.ussa-email-input', this.validateEmail);
            
            // Clear error on focus
            $(document).on('focus', '.ussa-email-input', function() {
                $(this).removeClass('ussa-error');
            });
        },

        /**
         * Initialize variation product handling
         */
        initVariationHandling: function() {
            var self = this;
            
            // Listen for variation changes
            $(document).on('found_variation', '.variations_form', function(event, variation) {
                self.handleVariationChange($(this), variation);
            });
            
            // Listen for variation reset
            $(document).on('reset_data', '.variations_form', function() {
                self.handleVariationReset($(this));
            });
        },

        /**
         * Handle variation change
         */
        handleVariationChange: function($form, variation) {
            var $alertForm = $form.find('.ussa-stock-alert-form');
            
            // Update variation ID
            $alertForm.attr('data-variation-id', variation.variation_id);
            $alertForm.find('input[name="ussa_variation_id"]').val(variation.variation_id);
            
            // Show/hide based on stock status
            if (!variation.is_in_stock) {
                $alertForm.addClass('ussa-visible').show();
            } else {
                $alertForm.removeClass('ussa-visible').hide();
            }
            
            // Reset form state
            this.resetFormState($alertForm);
        },

        /**
         * Handle variation reset
         */
        handleVariationReset: function($form) {
            var $alertForm = $form.find('.ussa-stock-alert-form');
            $alertForm.removeClass('ussa-visible').hide();
            this.resetFormState($alertForm);
        },

        /**
         * Reset form state
         */
        resetFormState: function($alertForm) {
            var $subscribeForm = $alertForm.find('.ussa-subscribe-form');
            var $messages = $alertForm.find('.ussa-form-messages');
            
            $subscribeForm.show();
            $messages.hide();
            $messages.find('.ussa-message').hide().text('');
            $alertForm.find('.ussa-email-input').removeClass('ussa-error');
        },

        /**
         * Handle subscribe form submission
         */
        handleSubscribe: function(e) {
            e.preventDefault();
            
            var $form = $(this);
            var $container = $form.closest('.ussa-stock-alert-form');
            var $submitBtn = $form.find('.ussa-submit-btn');
            var $btnText = $submitBtn.find('.ussa-btn-text');
            var $btnLoading = $submitBtn.find('.ussa-btn-loading');
            var $messages = $form.find('.ussa-form-messages');
            var $successMsg = $messages.find('.ussa-message-success');
            var $errorMsg = $messages.find('.ussa-message-error');
            var $emailInput = $form.find('.ussa-email-input');
            
            // Validate email
            var email = $emailInput.val().trim();
            if (!USSA_Frontend.isValidEmail(email)) {
                $emailInput.addClass('ussa-error');
                $errorMsg.text(ussa_params.i18n.invalid_email).show();
                $messages.show();
                return;
            }
            
            // Hide previous messages
            $messages.hide();
            $successMsg.hide();
            $errorMsg.hide();
            
            // Show loading state
            $submitBtn.prop('disabled', true);
            $btnText.hide();
            $btnLoading.show();
            
            // Get form data
            var data = {
                action: 'ussa_subscribe',
                nonce: $form.find('input[name="ussa_nonce"]').val(),
                email: email,
                product_id: $container.data('product-id'),
                variation_id: $container.data('variation-id') || 0
            };
            
            // Make AJAX request
            $.ajax({
                url: ussa_params.ajax_url,
                type: 'POST',
                data: data,
                success: function(response) {
                    if (response.success) {
                        // Show success message
                        $successMsg.text(response.data.message).show();
                        $messages.show();
                        
                        // Hide form after success
                        $form.slideUp(300);
                        
                        // Update container state if needed
                        if (response.data.already_subscribed) {
                            $container.addClass('ussa-already-subscribed');
                        }
                        
                        // Trigger custom event
                        $(document).trigger('ussa_subscribed', [response.data]);
                        
                    } else {
                        // Show error message
                        $errorMsg.text(response.data.message || ussa_params.i18n.error).show();
                        $messages.show();
                    }
                },
                error: function(xhr, status, error) {
                    // Show error message
                    $errorMsg.text(ussa_params.i18n.error).show();
                    $messages.show();
                    console.error('USSA Error:', error);
                },
                complete: function() {
                    // Reset button state
                    $submitBtn.prop('disabled', false);
                    $btnText.show();
                    $btnLoading.hide();
                }
            });
        },

        /**
         * Handle unsubscribe button click
         */
        handleUnsubscribe: function(e) {
            e.preventDefault();
            
            var $btn = $(this);
            var $container = $btn.closest('.ussa-stock-alert-form');
            var $subscribedMsg = $container.find('.ussa-subscribed-message');
            
            // Confirm unsubscribe
            if (!confirm(ussa_params.i18n.confirm_unsubscribe)) {
                return;
            }
            
            // Show loading state
            $btn.prop('disabled', true).text(ussa_params.i18n.unsubscribing);
            
            // Get data
            var data = {
                action: 'ussa_unsubscribe',
                nonce: ussa_params.nonce,
                product_id: $container.data('product-id'),
                variation_id: $container.data('variation-id') || 0
            };
            
            // Make AJAX request
            $.ajax({
                url: ussa_params.ajax_url,
                type: 'POST',
                data: data,
                success: function(response) {
                    if (response.success) {
                        // Reload to show subscribe form
                        location.reload();
                    } else {
                        alert(response.data.message || ussa_params.i18n.error);
                        $btn.prop('disabled', false).text(ussa_params.i18n.unsubscribe);
                    }
                },
                error: function() {
                    alert(ussa_params.i18n.error);
                    $btn.prop('disabled', false).text(ussa_params.i18n.unsubscribe);
                }
            });
        },

        /**
         * Validate email input on change
         */
        validateEmail: function() {
            var $input = $(this);
            var email = $input.val().trim();
            
            if (email && !USSA_Frontend.isValidEmail(email)) {
                $input.addClass('ussa-error');
            } else {
                $input.removeClass('ussa-error');
            }
        },

        /**
         * Check if email is valid
         */
        isValidEmail: function(email) {
            var regex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            return regex.test(email);
        }
    };

    /**
     * Initialize on document ready
     */
    $(document).ready(function() {
        USSA_Frontend.init();
    });

})(jQuery);
