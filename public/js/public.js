/**
 * Click to Chat - Public JavaScript
 *
 * This file contains all the JavaScript for the public-facing aspects of the plugin.
 */

(function ($) {
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
        setTimeout(function () {
            $floatingButton.css({
                'transition': 'all 0.3s ease',
                'transform': 'scale(1)',
                'opacity': '1'
            });
        }, 500);

        // Handle scroll behavior
        $(window).on('scroll', function () {
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
            function () {
                $(this).css('transform', 'scale(1.1)');
            },
            function () {
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
        const originalText = $productButton.find('.ctc-chat-button-text').text();
        const originalBg = $productButton.css('background-color');
        const originalColor = $productButton.css('color');
        const parentProductId = $('input[name="product_id"]').val();

        if (!parentProductId) {
            return;
        }

        function collectVariationData() {
            const variationData = {};

            $variationForm.find('.variations select, .variations input[type="radio"]:checked').each(function () {
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

        function setOutOfStockState() {
            if (!window.ctc_chat_public || !ctc_chat_public.stock || !ctc_chat_public.stock.enabled) {
                return;
            }

            $productButton.addClass('ctc-chat-button-stock');
            $productButton.find('.ctc-chat-button-text').text(ctc_chat_public.stock.button_text);
            if (ctc_chat_public.stock.bg_color) {
                $productButton.css('background-color', ctc_chat_public.stock.bg_color);
            }
            if (ctc_chat_public.stock.text_color) {
                $productButton.css('color', ctc_chat_public.stock.text_color);
            }

            $.ajax({
                url: ctc_chat_public.ajaxurl,
                type: 'POST',
                data: {
                    action: 'ctc_chat_get_stock_url',
                    product_id: parentProductId,
                    variations: collectVariationData(),
                    nonce: ctc_chat_public.nonce
                },
                success: function (response) {
                    if (response.success && response.data.url) {
                        $productButton.attr('href', response.data.url);
                    }
                }
            });
        }

        function restoreInStockState() {
            $productButton.removeClass('ctc-chat-button-stock');
            if (originalText) {
                $productButton.find('.ctc-chat-button-text').text(originalText);
            }
            if (originalBg) {
                $productButton.css('background-color', originalBg);
            }
            if (originalColor) {
                $productButton.css('color', originalColor);
            }
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
                success: function (response) {
                    if (response.success && response.data.url) {
                        $productButton.attr('href', response.data.url);
                        if (response.data.order_total) {
                            $productButton.attr('data-ctc-cart-total', response.data.order_total);
                        }
                    }
                }
            });
        }

        $variationForm.on('found_variation', function (event, variation) {
            if (variation && variation.variation_id) {
                $productButton.attr('data-ctc-variation-id', variation.variation_id);
            }
            if (variation && !variation.is_in_stock && window.ctc_chat_public && ctc_chat_public.stock && ctc_chat_public.stock.enabled) {
                setOutOfStockState();
            } else {
                restoreInStockState();
                updateVariationUrl();
            }
        });

        $variationForm.on('show_variation', function () {
            updateVariationUrl();
        });

        $variationForm.on('reset_data hide_variation', function () {
            restoreInStockState();
            $productButton.attr('href', originalUrl);
            $productButton.removeAttr('data-ctc-variation-id');
        });

        // Watch for quantity changes in the variation form
        $variationForm.on('change input', 'input[name="quantity"]', function () {
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

        $cartForm.on('change input', 'input[name="quantity"]', function () {
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
                success: function (response) {
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
        const isOncePerSession = (config.frequency === 'once_per_session');

        // Check if user dismissed it in this session (only enforced if once_per_session)
        if (isOncePerSession) {
            try {
                if (sessionStorage.getItem(storageKey) === '1') {
                    return;
                }
            } catch (e) { }
        }

        let nudgeVisible = false;
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

            if ($('.ctc-chat-floating-button-container').length) {
                $nudge.addClass('ctc-nudge-has-floating');
            }

            // Handle dismiss
            $nudge.on('click', '.ctc-nudge-close', function (e) {
                e.preventDefault();
                hideNudge();
                if (isOncePerSession) {
                    try {
                        sessionStorage.setItem(storageKey, '1');
                    } catch (err) { }
                }
            });

            // Handle CTA click
            $nudge.on('click', '.ctc-nudge-btn', function () {
                hideNudge();
                try {
                    sessionStorage.setItem(storageKey, '1');
                } catch (err) { }
            });

            return $nudge;
        }

        function showNudge() {
            if (nudgeVisible) {
                return;
            }
            if (isOncePerSession) {
                try {
                    if (sessionStorage.getItem(storageKey) === '1') {
                        return;
                    }
                } catch (e) { }
            }

            nudgeVisible = true;
            if (inactivityTimer) {
                clearTimeout(inactivityTimer);
                inactivityTimer = null;
            }

            const $element = renderNudge();
            setTimeout(function () {
                $element.addClass('ctc-nudge-show');
            }, 50);
        }

        function hideNudge() {
            nudgeVisible = false;
            if ($nudge) {
                $nudge.removeClass('ctc-nudge-show');
            }
            // If re-appearance is allowed and inactivity trigger is active, restart inactivity timer
            if (!isOncePerSession && (triggerType === 'inactivity' || triggerType === 'both')) {
                resetInactivityTimer();
            }
        }

        const triggerType = config.trigger || 'both';
        const delayMs = Math.max(3, parseInt(config.delay, 10) || 20) * 1000;

        function resetInactivityTimer() {
            if (nudgeVisible) {
                return;
            }
            if (inactivityTimer) {
                clearTimeout(inactivityTimer);
            }
            inactivityTimer = setTimeout(function () {
                showNudge();
            }, delayMs);
        }

        // 1. Inactivity trigger
        if (triggerType === 'inactivity' || triggerType === 'both') {
            resetInactivityTimer();
            $(document).on('mousemove keydown scroll touchstart', function () {
                if (!nudgeVisible) {
                    resetInactivityTimer();
                }
            });
        }

        // 2. Exit intent trigger
        if (triggerType === 'exit_intent' || triggerType === 'both') {
            $(document).on('mouseleave', function (e) {
                if (e.clientY <= 0 && !nudgeVisible) {
                    showNudge();
                }
            });
        }
    }

    /**
     * Initialize order tracking actions (My Account orders table)
     */
    function initOrderTrackingActions() {
        // Ensure only one tracking card exists in the DOM if multiple hooks fired
        if ($('.ctc-chat-order-tracking-card').length > 1) {
            $('.ctc-chat-order-tracking-card:gt(0)').remove();
        }

        // Ensure My Account order tracking action links open safely in a new tab
        $(document).on('click', 'a.ctc_track_whatsapp', function () {
            $(this).attr('target', '_blank').attr('rel', 'noopener noreferrer');
        });
    }

    /**
     * Initialize Coupon Engine floating teaser chip
     */
    function initCouponTeaser() {
        const $teaser = $('.ctc-chat-coupon-teaser');
        if (!$teaser.length) {
            return;
        }

        // Check if user previously dismissed in this session
        if (sessionStorage.getItem('ctc_coupon_dismissed') === '1') {
            $teaser.hide();
            return;
        }

        // Animate entrance after 800ms
        setTimeout(function () {
            $teaser.addClass('ctc-coupon-visible');
        }, 800);

        // Dismiss button handler
        $teaser.on('click', '.ctc-chat-coupon-close', function (e) {
            e.preventDefault();
            e.stopPropagation();
            $teaser.removeClass('ctc-coupon-visible').fadeOut(250);
            sessionStorage.setItem('ctc_coupon_dismissed', '1');
        });
    }

    /**
     * Initialize Desktop "Scan QR Code to Chat" button-side popover
     */
    function initDesktopQrPopover() {
        if (typeof ctc_chat_public === 'undefined' || !ctc_chat_public.qr_modal || !ctc_chat_public.qr_modal.enabled) {
            return;
        }

        const $popover = $('#ctc-chat-qr-popover');
        if (!$popover.length) {
            return;
        }

        function closeQrPopover() {
            $popover.removeClass('ctc-qr-popover-open').attr('aria-hidden', 'true');
            $(document).off('click.ctcQrPopover');
        }

        function isMobileClient() {
            const ua = (navigator.userAgent || navigator.vendor || window.opera || '').toLowerCase();
            const isMobileUA = /android|webos|iphone|ipad|ipod|blackberry|iemobile|opera mini/i.test(ua);
            return isMobileUA && window.innerWidth <= 768;
        }

        // Close on close button click
        $popover.on('click', '.ctc-chat-qr-close', function (e) {
            e.preventDefault();
            closeQrPopover();
        });

        // Close on Escape key
        $(document).on('keydown', function (e) {
            if ((e.key === 'Escape' || e.keyCode === 27) && $popover.hasClass('ctc-qr-popover-open')) {
                closeQrPopover();
            }
        });

        // Close popover shortly after clicking WhatsApp Web action
        $popover.on('click', '#ctc-chat-qr-web-action', function () {
            setTimeout(closeQrPopover, 400);
        });

        // Intercept WhatsApp clicks on desktop devices
        $(document).on('click', 'a[href*="wa.me"], a[href*="whatsapp.com"], a[href*="api.whatsapp.com"], a[href*="web.whatsapp.com"], .ctc-chat-whatsapp-button, .ctc-chat-button, .ctc-chat-order-btn, .ctc-chat-nudge-btn, .ctc-chat-coupon-link, .ctc-chat-product-coupon-badge a, a.ctc_track_whatsapp', function (e) {
            // Never intercept clicks inside the QR popover itself
            if ($(this).closest('#ctc-chat-qr-popover').length) {
                return;
            }

            // Do not intercept close buttons or disabled buttons
            if ($(this).hasClass('ctc-chat-coupon-close') || $(this).hasClass('ctc-nudge-close') || $(this).is('.disabled, [disabled]')) {
                return;
            }

            // Only pass through on actual mobile devices with <= 768px width
            if (isMobileClient()) {
                return;
            }

            // Extract target WhatsApp URL
            let targetUrl = $(this).attr('href');
            if (!targetUrl || targetUrl === '#' || targetUrl.indexOf('javascript:') === 0) {
                targetUrl = $(this).find('a[href]').attr('href') || $(this).closest('a[href]').attr('href') || $(this).data('url') || $(this).data('href');
            }

            if (!targetUrl || (targetUrl.indexOf('wa.me') === -1 && targetUrl.indexOf('whatsapp.com') === -1)) {
                return;
            }

            // Intercept standard navigation on desktop
            e.preventDefault();
            e.stopPropagation();

            // Populate canvas target with high-resolution offline SVG QR code
            const qrTarget = document.getElementById('ctc-chat-qr-canvas-target');
            if (qrTarget) {
                qrTarget.innerHTML = '';
                let rendered = false;
                try {
                    const qrGen = (typeof window.qrcode === 'function') ? window.qrcode : (typeof qrcode === 'function' ? qrcode : null);
                    if (qrGen) {
                        const qr = qrGen(0, 'M');
                        qr.addData(targetUrl);
                        qr.make();
                        qrTarget.innerHTML = qr.createSvgTag({ cellSize: 3, margin: 0 });
                        rendered = true;
                    }
                } catch (err) {
                    console.warn('CTC QR SVG generation warning:', err);
                }

                // Bulletproof image fallback if SVG generation had any constraint
                if (!rendered || !qrTarget.hasChildNodes()) {
                    qrTarget.innerHTML = '<img src="https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=' + encodeURIComponent(targetUrl) + '" alt="WhatsApp QR Code" width="145" height="145" style="display:block;margin:0 auto;max-width:100%;height:auto;" />';
                }
            }

            // Update WhatsApp Web fallback link
            const $webLink = $('#ctc-chat-qr-web-action');
            if ($webLink.length) {
                $webLink.attr('href', targetUrl);
            }

            // Position popover card neatly near the clicked button
            const $clickedBtn = $(this);
            const isFloating = $clickedBtn.closest('.ctc-chat-floating-button-container').length > 0;

            if (isFloating) {
                $popover.removeClass('ctc-qr-inline-docked').addClass('ctc-qr-floating-docked');
                $popover.css({ top: '', left: '', right: '', bottom: '' });
            } else {
                $popover.removeClass('ctc-qr-floating-docked').addClass('ctc-qr-inline-docked');
                const rect = this.getBoundingClientRect();
                const popoverWidth = 260;
                const popoverHeight = 285;
                let top = rect.top + window.scrollY - popoverHeight - 12;
                let left = rect.left + window.scrollX + (rect.width / 2) - (popoverWidth / 2);

                if (rect.top - popoverHeight < 15) {
                    top = rect.bottom + window.scrollY + 12;
                }
                if (left < 15) left = 15;
                if (left + popoverWidth > window.innerWidth - 15) {
                    left = window.innerWidth - popoverWidth - 15;
                }

                $popover.css({
                    top: top + 'px',
                    left: left + 'px',
                    bottom: 'auto',
                    right: 'auto'
                });
            }

            // Display popover
            $popover.addClass('ctc-qr-popover-open').attr('aria-hidden', 'false');

            // Attach outside click listener to dismiss without a blocking screen backdrop
            setTimeout(function () {
                $(document).off('click.ctcQrPopover').on('click.ctcQrPopover', function (evt) {
                    if (!$(evt.target).closest('#ctc-chat-qr-popover, a[href*="wa.me"], a[href*="whatsapp.com"], .ctc-chat-whatsapp-button').length) {
                        closeQrPopover();
                    }
                });
            }, 60);
        });
    }

    /**
     * Initialize GDPR & Privacy Compliance Consent Mode
     */
    function initPrivacyConsent() {
        const privacy = (typeof ctc_chat_public !== 'undefined' && ctc_chat_public.privacy) ? ctc_chat_public.privacy : null;
        if (!privacy || !privacy.enabled) {
            return;
        }

        const $prompt = $('#ctc-chat-privacy-prompt');
        let pendingAction = null;

        function hasConsented() {
            try {
                return localStorage.getItem('ctc_chat_gdpr_consented') === '1';
            } catch (e) {
                return false;
            }
        }

        function setConsented() {
            try {
                localStorage.setItem('ctc_chat_gdpr_consented', '1');
            } catch (e) {
                // Ignore localStorage errors
            }
        }

        function openPrompt(callback) {
            pendingAction = callback;
            $prompt.addClass('ctc-privacy-prompt-open').attr('aria-hidden', 'false');
            $('body').addClass('ctc-privacy-modal-active');
        }

        function closePrompt() {
            $prompt.removeClass('ctc-privacy-prompt-open').attr('aria-hidden', 'true');
            $('body').removeClass('ctc-privacy-modal-active');
            pendingAction = null;
        }

        // Agree button click
        $('#ctc-chat-privacy-agree').on('click', function (e) {
            e.preventDefault();
            setConsented();
            const action = pendingAction;
            closePrompt();
            if (typeof action === 'function') {
                action();
            }
        });

        // Cancel button and backdrop click
        $('#ctc-chat-privacy-cancel, .ctc-chat-privacy-backdrop').on('click', function (e) {
            e.preventDefault();
            closePrompt();
        });

        // Close on Escape key
        $(document).on('keydown', function (e) {
            if (e.key === 'Escape' && $prompt.hasClass('ctc-privacy-prompt-open')) {
                closePrompt();
            }
        });

        // Intercept clicks before consent if consent_mode is 'prompt'
        if (privacy.consent_mode === 'prompt') {
            $(document).on('click', 'a[href*="wa.me"], a[href*="whatsapp.com"], a[href*="api.whatsapp.com"], a[href*="web.whatsapp.com"], .ctc-chat-whatsapp-button, .ctc-chat-button, .ctc-chat-order-btn, .ctc-chat-nudge-btn, .ctc-chat-coupon-link, .ctc-chat-product-coupon-badge a, a.ctc_track_whatsapp', function (e) {
                // Do not intercept actions inside privacy prompt or QR popover
                if ($(this).closest('#ctc-chat-privacy-prompt, #ctc-chat-qr-popover').length) {
                    return;
                }

                // Do not intercept close or disabled buttons
                if ($(this).hasClass('ctc-chat-coupon-close') || $(this).hasClass('ctc-nudge-close') || $(this).is('.disabled, [disabled]')) {
                    return;
                }

                if (hasConsented()) {
                    return; // Consented, pass through
                }

                // Intercept and open prompt
                e.preventDefault();
                e.stopPropagation();
                e.stopImmediatePropagation();

                const $target = $(this);
                openPrompt(function () {
                    const rawEl = $target[0];
                    if (rawEl && typeof rawEl.click === 'function') {
                        rawEl.click();
                    } else {
                        $target.trigger('click');
                    }
                });
            });
        }

        // Ensure inline notices for dynamic elements if consent_mode is 'inline_notice'
        if (privacy.consent_mode === 'inline_notice' && privacy.notice_html) {
            function ensureInlineNotices() {
                $('.ctc-chat-whatsapp-button:not(.ctc-chat-floating-button-container .ctc-chat-whatsapp-button)').each(function () {
                    const $btn = $(this);
                    if (!$btn.next('.ctc-chat-inline-privacy-notice').length && !$btn.parent().find('.ctc-chat-inline-privacy-notice').length) {
                        $btn.after('<div class="ctc-chat-inline-privacy-notice"><span class="ctc-chat-privacy-lock">🔒</span> ' + privacy.notice_html + '</div>');
                    }
                });
            }
            ensureInlineNotices();
            $(document).on('found_variation reset_data', ensureInlineNotices);
        }
    }

    // Initialize when document is ready
    $(document).ready(function () {
        initPrivacyConsent();
        initClickToChat();
        initSimpleProductQuantityWatcher();
        initAbandonmentNudge();
        initOrderTrackingActions();
        initCouponTeaser();
        initDesktopQrPopover();
    });

})(jQuery);
