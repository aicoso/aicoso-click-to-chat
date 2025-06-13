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

        // Handle variable product behavior (handled in PHP for better SEO)
    }

    /**
     * Initialize floating button behavior
     */
    function initFloatingButton() {
        const $floatingButton = $('.ctc-floating-button-container');
        
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
                if (!$floatingButton.hasClass('ctc-button-visible')) {
                    $floatingButton.addClass('ctc-button-visible');
                }
            } else {
                if ($floatingButton.hasClass('ctc-button-visible')) {
                    $floatingButton.removeClass('ctc-button-visible');
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
     * Handle button click tracking (optional)
     */
    function trackButtonClicks() {
        $('.ctc-whatsapp-button').on('click', function(e) {
            // Get button data
            const $button = $(this);
            const buttonType = $button.hasClass('ctc-button-product') ? 'product' : 
                              ($button.hasClass('ctc-button-shop') ? 'shop' : 
                               ($button.hasClass('ctc-button-cart') ? 'cart' : 
                                ($button.hasClass('ctc-button-checkout') ? 'checkout' : 
                                 ($button.hasClass('ctc-button-floating') ? 'floating' : 'unknown'))));

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
        if ($('.ctc-whatsapp-button').length) {
            trackButtonClicks();
        }
    });

})(jQuery);