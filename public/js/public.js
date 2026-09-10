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

    /**
     * Initialize Cart & Checkout Abandonment Nudge
     */
    function initAbandonmentNudge() {
        if (!window.ctc_chat_public || !ctc_chat_public.nudge || !ctc_chat_public.nudge.enabled) {
            return;
        }

        const config = ctc_chat_public.nudge;
        const storageKey = 'ctc_nudge_dismissed';

        // Check if user dismissed it in this session
        try {
            if (sessionStorage.getItem(storageKey) === '1') {
                return;
            }
        } catch (e) {}

        let nudgeShown = false;
        let inactivityTimer = null;
        let $nudge = null;

        function renderNudge() {
            if ($('#ctc-abandonment-nudge').length) {
                return $('#ctc-abandonment-nudge');
            }

            const badgeHtml = config.cart_total
                ? `<span class="ctc-nudge-badge">${config.cart_total}</span>`
                : '';

            const nudgeHtml = `
                <div id="ctc-abandonment-nudge" class="ctc-abandonment-nudge" role="dialog" aria-live="polite" aria-label="${config.title}">
                    <button type="button" class="ctc-nudge-close" aria-label="Close">&times;</button>
                    <div class="ctc-nudge-header">
                        <div class="ctc-nudge-avatar-wrap">
                            <div class="ctc-nudge-avatar">
                                <svg viewBox="0 0 24 24"><path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981zm11.387-5.464c-.074-.124-.272-.198-.57-.347-.297-.149-1.758-.868-2.031-.967-.272-.099-.47-.149-.669.149-.198.297-.768.967-.941 1.165-.173.198-.347.223-.644.074-.297-.149-1.255-.462-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.521.151-.172.2-.296.3-.495.099-.198.05-.372-.025-.521-.075-.148-.669-1.611-.916-2.206-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372s-1.04 1.016-1.04 2.479 1.065 2.876 1.213 3.074c.149.198 2.095 3.2 5.076 4.487.709.306 1.263.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.695.248-1.29.173-1.414z"/></svg>
                            </div>
                            <span class="ctc-nudge-online-dot"></span>
                        </div>
                        <div class="ctc-nudge-header-text">
                            <h4 class="ctc-nudge-title">${config.title}</h4>
                            ${badgeHtml}
                        </div>
                    </div>
                    <div class="ctc-nudge-body">
                        <p class="ctc-nudge-message">${config.message}</p>
                    </div>
                    <div class="ctc-nudge-footer">
                        <a href="${config.url}" target="_blank" rel="noopener noreferrer" class="ctc-nudge-btn ctc-chat-button" data-ctc-button-type="nudge">
                            <svg class="ctc-nudge-wa-icon" viewBox="0 0 24 24" width="18" height="18" fill="currentColor"><path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981zm11.387-5.464c-.074-.124-.272-.198-.57-.347-.297-.149-1.758-.868-2.031-.967-.272-.099-.47-.149-.669.149-.198.297-.768.967-.941 1.165-.173.198-.347.223-.644.074-.297-.149-1.255-.462-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.521.151-.172.2-.296.3-.495.099-.198.05-.372-.025-.521-.075-.148-.669-1.611-.916-2.206-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372s-1.04 1.016-1.04 2.479 1.065 2.876 1.213 3.074c.149.198 2.095 3.2 5.076 4.487.709.306 1.263.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.695.248-1.29.173-1.414z"/></svg>
                            <span>${config.button_text}</span>
                        </a>
                    </div>
                </div>
            `;

            $('body').append(nudgeHtml);
            $nudge = $('#ctc-abandonment-nudge');

            // Handle dismiss
            $nudge.on('click', '.ctc-nudge-close', function(e) {
                e.preventDefault();
                $nudge.removeClass('ctc-nudge-show');
                try {
                    sessionStorage.setItem(storageKey, '1');
                } catch (err) {}
            });

            // Handle CTA click
            $nudge.on('click', '.ctc-nudge-btn', function() {
                try {
                    sessionStorage.setItem(storageKey, '1');
                } catch (err) {}
            });

            return $nudge;
        }

        function triggerNudge() {
            if (nudgeShown) {
                return;
            }
            try {
                if (sessionStorage.getItem(storageKey) === '1') {
                    return;
                }
            } catch (e) {}

            nudgeShown = true;
            if (inactivityTimer) {
                clearTimeout(inactivityTimer);
            }

            const $element = renderNudge();
            setTimeout(function() {
                $element.addClass('ctc-nudge-show');
            }, 50);
        }

        const triggerType = config.trigger || 'both';

        // 1. Inactivity trigger
        if (triggerType === 'inactivity' || triggerType === 'both') {
            const delayMs = Math.max(3, parseInt(config.delay, 10) || 20) * 1000;

            function resetInactivityTimer() {
                if (nudgeShown) {
                    return;
                }
                if (inactivityTimer) {
                    clearTimeout(inactivityTimer);
                }
                inactivityTimer = setTimeout(triggerNudge, delayMs);
            }

            resetInactivityTimer();
            $(document).on('mousemove keydown scroll touchstart', resetInactivityTimer);
        }

        // 2. Exit intent trigger
        if (triggerType === 'exit_intent' || triggerType === 'both') {
            $(document).on('mouseleave', function(e) {
                if (e.clientY <= 0 && !nudgeShown) {
                    triggerNudge();
                }
            });
        }
    }

    // Initialize when document is ready
    $(document).ready(function() {
        initClickToChat();
        initSimpleProductQuantityWatcher();
        initAbandonmentNudge();
    });

})(jQuery);
