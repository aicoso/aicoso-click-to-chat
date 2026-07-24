/**
 * Click tracking for WhatsApp buttons.
 */
(function () {
    'use strict';

    function readData(el, key, fallback) {
        var value = el.getAttribute(key);
        if (value === null || value === '') {
            return fallback;
        }
        return value;
    }

    function buildPayload(button) {
        return {
            action: 'ctc_chat_track_click',
            nonce: window.ctc_chat_tracking.nonce,
            button_type: readData(button, 'data-ctc-button-type', 'unknown'),
            template_type: readData(button, 'data-ctc-template-type', ''),
            number_id: readData(button, 'data-ctc-number-id', '0'),
            product_id: readData(button, 'data-ctc-product-id', '0'),
            variation_id: readData(button, 'data-ctc-variation-id', '0'),
            order_id: readData(button, 'data-ctc-order-id', '0'),
            cart_item_count: readData(button, 'data-ctc-cart-count', '0'),
            cart_total: readData(button, 'data-ctc-cart-total', '0'),
            cart_currency: readData(button, 'data-ctc-cart-currency', ''),
            whatsapp_url: button.href || '',
            page_url: window.location.href,
            referrer_url: document.referrer || ''
        };
    }

    function sendPayload(payload) {
        var url = window.ctc_chat_tracking.ajaxurl;
        var body = new URLSearchParams(payload);

        if (navigator.sendBeacon) {
            var blob = new Blob([body.toString()], { type: 'application/x-www-form-urlencoded' });
            navigator.sendBeacon(url, blob);
            return;
        }

        if (window.fetch) {
            fetch(url, {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: body.toString(),
                keepalive: true,
                credentials: 'same-origin'
            });
        }
    }

    function passthroughAnalytics(button) {
        var buttonType = readData(button, 'data-ctc-button-type', 'unknown');

        if (typeof window.ga !== 'undefined') {
            window.ga('send', {
                hitType: 'event',
                eventCategory: 'Click to Chat',
                eventAction: 'click',
                eventLabel: buttonType
            });
        }

        if (typeof window.gtag !== 'undefined') {
            window.gtag('event', 'whatsapp_click', {
                button_type: buttonType,
                page_url: window.location.href
            });
        }
    }

    function onButtonClick(event) {
        if (!window.ctc_chat_tracking || !window.ctc_chat_tracking.enabled) {
            return;
        }

        var button = event.target.closest('.ctc-chat-whatsapp-button');
        if (!button) {
            return;
        }

        sendPayload(buildPayload(button));
        passthroughAnalytics(button);
    }

    // WooCommerce Blocks can replace Cart and Checkout markup at runtime.
    // Delegate the listener so both initial and dynamically inserted buttons are tracked.
    document.addEventListener('click', onButtonClick);
})();
