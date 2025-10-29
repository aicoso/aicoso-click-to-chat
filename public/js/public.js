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
    function initClickToChat() {
        // Handle floating button behavior
        initFloatingButton();

        // Handle variable product behavior
        initVariableProductHandler();
    }

    /**
     * Initialize floating button behavior
     */
    function initFloatingButton() {
        const $floatingButton = $('.ctc-chat-floating-button-container');
        
        if (!$floatingButton.length) {
            return;
        }

        // Add some animation to the floating button
        $floatingButton.css({
            'transform': 'scale(0)',
            'opacity': '0'
        });

        // Show the button with a slight delay for better page load appearance
        setTimeout(function() {
            $floatingButton.css({
                'transition': 'all 0.3s ease',
                'transform': 'scale(1)',
                'opacity': '1'
            });
        }, 500);

        // Handle scroll behavior
        $(window).on('scroll', function() {
            const scrollTop = $(window).scrollTop();
            
            // Show/hide button based on scroll position
            if (scrollTop > 300) {
                if (!$floatingButton.hasClass('ctc-chat-button-visible')) {
                    $floatingButton.addClass('ctc-chat-button-visible');
                }
            } else {
                if ($floatingButton.hasClass('ctc-chat-button-visible')) {
                    $floatingButton.removeClass('ctc-chat-button-visible');
                }
            }
        });

        // Add hover effect to floating button
        $floatingButton.hover(
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
    function initVariableProductHandler() {
        // Check if we're on a single product page with variations
        if (!$('.variations_form').length) {
            return;
        }

        // Store the original URL and message template
        let $productButton = $('.ctc-chat-whatsapp-button.ctc-chat-button-product');
        if (!$productButton.length) {
            // Try alternative selector
            $productButton = $('.ctc-chat-button-product a');
            if (!$productButton.length) {
                return;
            }
        }

        const originalUrl = $productButton.attr('href');

        // Listen for variation changes
        $('.variations_form').on('found_variation', function(event, variation) {
            // Get selected variation attributes
            const variationData = {};
            $('.variations select').each(function() {
                const name = $(this).attr('name');
                const value = $(this).val();
                if (name && value) {
                    variationData[name] = value;
                }
            });

            // Make AJAX request to get updated WhatsApp URL
            $.ajax({
                url: ctc_chat_public.ajaxurl,
                type: 'POST',
                data: {
                    action: 'ctc_get_variation_url',
                    product_id: variation.variation_id || $('input[name="product_id"]').val(),
                    variations: variationData,
                    nonce: ctc_chat_public.nonce
                },
                success: function(response) {
                    if (response.success && response.data.url) {
                        $productButton.attr('href', response.data.url);
                    }
                }
            });
        });

        // Reset URL when variations are reset
        $('.variations_form').on('reset_data', function() {
            $productButton.attr('href', originalUrl);
        });
    }

    /**
     * Handle button click tracking (optional)
     */
    function trackButtonClicks() {
        $('.ctc-chat-whatsapp-button').on('click', function(e) {
            // Get button data
            const $button = $(this);
            const buttonType = $button.hasClass('ctc-chat-button-product') ? 'product' : 
                              ($button.hasClass('ctc-chat-button-shop') ? 'shop' : 
                               ($button.hasClass('ctc-chat-button-cart') ? 'cart' : 
                                ($button.hasClass('ctc-chat-button-checkout') ? 'checkout' : 
                                 ($button.hasClass('ctc-chat-button-floating') ? 'floating' : 'unknown'))));

            // If WooCommerce analytics is active and we want to hook into it
            if (typeof wc_ga_pro !== 'undefined' && buttonType === 'product') {
                // WooCommerce Google Analytics Pro integration
                // You can add custom tracking code here if needed
            }

            // If Google Analytics is available (universal analytics)
            if (typeof ga !== 'undefined') {
                ga('send', {
                    hitType: 'event',
                    eventCategory: 'Click to Chat',
                    eventAction: 'click',
                    eventLabel: buttonType
                });
            }

            // If Google Analytics 4 is available
            if (typeof gtag !== 'undefined') {
                gtag('event', 'whatsapp_click', {
                    'button_type': buttonType,
                    'page_url': window.location.href
                });
            }
        });
    }

    // Initialize when document is ready
    $(document).ready(function() {
        initClickToChat();
        
        // Initialize click tracking if supported
        if ($('.ctc-chat-whatsapp-button').length) {
            trackButtonClicks();
        }
    });

})(jQuery);
