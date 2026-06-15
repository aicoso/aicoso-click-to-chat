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

        const $variationForm = $('.variations_form');
        let $productButton = $variationForm.find('.ctc-chat-whatsapp-button.ctc-chat-button-product');

        if (!$productButton.length) {
            $productButton = $('.ctc-chat-whatsapp-button.ctc-chat-button-product').first();
        }

        if (!$productButton.length) {
            return;
        }

        const originalUrl = $productButton.attr('href');
        const parentProductId = $('input[name="product_id"]').val();

        if (!parentProductId) {
            return;
        }

        function collectVariationData() {
            const variationData = {};

            $variationForm.find('.variations select, .variations input[type="radio"]:checked').each(function() {
                const name = $(this).attr('name');
                const value = $(this).val();

                if (name && value) {
                    variationData[name] = value;
                }
            });

            return variationData;
        }

        function updateVariationUrl() {
            $.ajax({
                url: ctc_chat_public.ajaxurl,
                type: 'POST',
                data: {
                    action: 'ctc_chat_get_variation_url',
                    product_id: parentProductId,
                    variations: collectVariationData(),
                    nonce: ctc_chat_public.nonce
                },
                success: function(response) {
                    if (response.success && response.data.url) {
                        $productButton.attr('href', response.data.url);
                    }
                }
            });
        }

        $variationForm.on('found_variation show_variation', function() {
            updateVariationUrl();
        });

        $variationForm.on('reset_data hide_variation', function() {
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
