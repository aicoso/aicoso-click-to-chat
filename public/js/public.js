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

        function getSelectedQuantity() {
            const $qtyInput = $('form.cart input[name="quantity"]');
            return $qtyInput.length ? Math.max(1, parseInt($qtyInput.val(), 10) || 1) : 1;
        }

        function updateVariationUrl() {
            const quantity = getSelectedQuantity();
            $.ajax({
                url: ctc_chat_public.ajaxurl,
                type: 'POST',
                data: {
                    action: 'ctc_chat_get_variation_url',
                    product_id: parentProductId,
                    variations: collectVariationData(),
                    quantity: quantity,
                    nonce: ctc_chat_public.nonce
                },
                success: function(response) {
                    if (response.success && response.data.url) {
                        $productButton.attr('href', response.data.url);
                        if (response.data.order_total) {
                            $productButton.attr('data-ctc-cart-total', response.data.order_total);
                        }
                    }
                }
            });
        }

        $variationForm.on('found_variation', function(event, variation) {
            if (variation && variation.variation_id) {
                $productButton.attr('data-ctc-variation-id', variation.variation_id);
            }
            updateVariationUrl();
        });

        $variationForm.on('show_variation', function() {
            updateVariationUrl();
        });

        $variationForm.on('reset_data hide_variation', function() {
            $productButton.attr('href', originalUrl);
            $productButton.removeAttr('data-ctc-variation-id');
        });

        // Watch for quantity changes in the variation form
        $variationForm.on('change input', 'input[name="quantity"]', function() {
            updateVariationUrl();
        });
    }

    /**
     * Initialize quantity change watcher for simple products.
     */
    function initSimpleProductQuantityWatcher() {
        const $cartForm = $('form.cart:not(.variations_form)');
        const $productButton = $('.ctc-chat-whatsapp-button[data-ctc-button-type="product"], .ctc-chat-button-product');
        if (!$cartForm.length || !$productButton.length) {
            return;
        }

        const productId = $productButton.data('ctc-product-id');
        if (!productId) {
            return;
        }

        $cartForm.on('change input', 'input[name="quantity"]', function() {
            const qty = Math.max(1, parseInt($(this).val(), 10) || 1);
            $.ajax({
                url: ctc_chat_public.ajaxurl,
                type: 'POST',
                data: {
                    action: 'ctc_chat_get_variation_url',
                    product_id: productId,
                    variations: {},
                    quantity: qty,
                    nonce: ctc_chat_public.nonce
                },
                success: function(response) {
                    if (response.success && response.data.url) {
                        $productButton.attr('href', response.data.url);
                        if (response.data.order_total) {
                            $productButton.attr('data-ctc-cart-total', response.data.order_total);
                        }
                    }
                }
            });
        });
    }

    // Initialize when document is ready
    $(document).ready(function() {
        initClickToChat();
        initSimpleProductQuantityWatcher();
    });

})(jQuery);
