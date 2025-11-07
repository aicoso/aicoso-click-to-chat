/**
 * Click to Chat - Public JavaScript
 *
 * This file contains all the JavaScript for the public-facing aspects of the plugin.
 */

(function($) {
    'use strict';

    /**
     * Initialize the plugin's public functionality
     */
    function ctc_chat_init_click_to_chat() {
        // Handle floating button behavior
        ctc_chat_init_floating_button();

        // Handle variable product behavior
        ctc_chat_init_variable_product_handler();
    }

    /**
     * Initialize floating button behavior
     */
    function ctc_chat_init_floating_button() {
        const ctc_chat_floating_button = $('.ctc-chat-floating-button-container');

        if (!ctc_chat_floating_button.length) {
            return;
        }

        // Add some animation to the floating button
        ctc_chat_floating_button.css({
            'transform': 'scale(0)',
            'opacity': '0'
        });

        // Show the button with a slight delay for better page load appearance
        setTimeout(function() {
            ctc_chat_floating_button.css({
                'transition': 'all 0.3s ease',
                'transform': 'scale(1)',
                'opacity': '1'
            });
        }, 500);

        // Handle scroll behavior
        $(window).on('scroll', function() {
            const ctc_chat_scroll_top = $(window).scrollTop();

            // Show/hide button based on scroll position
            if (ctc_chat_scroll_top > 300) {
                if (!ctc_chat_floating_button.hasClass('ctc-chat-button-visible')) {
                    ctc_chat_floating_button.addClass('ctc-chat-button-visible');
                }
            } else {
                if (ctc_chat_floating_button.hasClass('ctc-chat-button-visible')) {
                    ctc_chat_floating_button.removeClass('ctc-chat-button-visible');
                }
            }
        });

        // Add hover effect to floating button
        ctc_chat_floating_button.hover(
            function() {
                $(this).css('transform', 'scale(1.1)');
            },
            function() {
                $(this).css('transform', 'scale(1)');
            }
        );
    }

    /**
     * Handle variable product changes
     */
    function ctc_chat_init_variable_product_handler() {
        // Check if we're on a single product page with variations
        if (!$('.variations_form').length) {
            return;
        }

        // Store the original URL and message template
        let ctc_chat_product_button = $('.ctc-chat-whatsapp-button.ctc-chat-button-product');
        if (!ctc_chat_product_button.length) {
            // Try alternative selector
            ctc_chat_product_button = $('.ctc-chat-button-product a');
            if (!ctc_chat_product_button.length) {
                return;
            }
        }

        const ctc_chat_original_url = ctc_chat_product_button.attr('href');

        // Listen for variation changes
        $('.variations_form').on('found_variation', function(event, variation) {
            // Get selected variation attributes
            const ctc_chat_variation_data = {};
            $('.variations select').each(function() {
                const name = $(this).attr('name');
                const value = $(this).val();
                if (name && value) {
                    ctc_chat_variation_data[name] = value;
                }
            });

            // Make AJAX request to get updated WhatsApp URL
            $.ajax({
                url: ctc_chat_public.ajaxurl,
                type: 'POST',
                data: {
                    action: 'ctc_chat_get_variation_url',
                    product_id: variation.variation_id || $('input[name="product_id"]').val(),
                    variations: ctc_chat_variation_data,
                    nonce: ctc_chat_public.nonce
                },
                success: function(response) {
                    if (response.success && response.data.url) {
                        ctc_chat_product_button.attr('href', response.data.url);
                    }
                }
            });
        });

        // Reset URL when variations are reset
        $('.variations_form').on('reset_data', function() {
            ctc_chat_product_button.attr('href', ctc_chat_original_url);
        });
    }

    /**
     * Handle button click tracking (optional)
     */
    function ctc_chat_track_button_clicks() {
        $('.ctc-chat-whatsapp-button').on('click', function(e) {
            // Get button data
            const ctc_chat_button = $(this);
            const ctc_chat_button_type = ctc_chat_button.hasClass('ctc-chat-button-product') ? 'product' :
                              (ctc_chat_button.hasClass('ctc-chat-button-shop') ? 'shop' :
                               (ctc_chat_button.hasClass('ctc-chat-button-cart') ? 'cart' :
                                (ctc_chat_button.hasClass('ctc-chat-button-checkout') ? 'checkout' :
                                 (ctc_chat_button.hasClass('ctc-chat-button-floating') ? 'floating' : 'unknown'))));

            // If WooCommerce analytics is active and we want to hook into it
            if (typeof wc_ga_pro !== 'undefined' && ctc_chat_button_type === 'product') {
                // WooCommerce Google Analytics Pro integration
                // You can add custom tracking code here if needed
            }

            // If Google Analytics is available (universal analytics)
            if (typeof ga !== 'undefined') {
                ga('send', {
                    hitType: 'event',
                    eventCategory: 'Click to Chat',
                    eventAction: 'click',
                    eventLabel: ctc_chat_button_type
                });
            }

            // If Google Analytics 4 is available
            if (typeof gtag !== 'undefined') {
                gtag('event', 'whatsapp_click', {
                    'button_type': ctc_chat_button_type,
                    'page_url': window.location.href
                });
            }
        });
    }

    // Initialize when document is ready
    $(document).ready(function() {
        ctc_chat_init_click_to_chat();

        // Initialize click tracking if supported
        if ($('.ctc-chat-whatsapp-button').length) {
            ctc_chat_track_button_clicks();
        }
    });

})(jQuery);
