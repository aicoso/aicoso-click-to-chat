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

        var button = event.currentTarget;
        sendPayload(buildPayload(button));
        passthroughAnalytics(button);
    }

    function bindButtons() {
        var buttons = document.querySelectorAll('.ctc-chat-whatsapp-button');
        buttons.forEach(function (button) {
            if (button.dataset.ctcTrackingBound === '1') {
                return;
            }
            button.dataset.ctcTrackingBound = '1';
            button.addEventListener('click', onButtonClick);
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', bindButtons);
    } else {
        bindButtons();
    }
})();
