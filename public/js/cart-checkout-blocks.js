/**
 * Click to Chat - Cart and Checkout Blocks Support
 *
 * This file adds WhatsApp buttons to WooCommerce block-based cart and checkout pages
 */

(function() {
    'use strict';

    // Wait for DOM to be ready
    function ctc_chat_ready(fn) {
        if (document.readyState !== 'loading') {
            fn();
        } else {
            document.addEventListener('DOMContentLoaded', fn);
        }
    }

    // Insert WhatsApp button for cart/checkout blocks
    function ctc_chat_insert_whatsapp_button() {
        // Check if we have the settings
        if (typeof ctc_chat_block_params === 'undefined') {
            return;
        }

        // Check if we're on cart or checkout page
        const ctc_chat_is_cart_page = document.querySelector('.wp-block-woocommerce-cart');
        const ctc_chat_is_checkout_page = document.querySelector('.wp-block-woocommerce-checkout');

        if (!ctc_chat_is_cart_page && !ctc_chat_is_checkout_page) {
            return;
        }

        // Determine which page we're on and check if enabled
        let ctc_chat_page_type = '';
        let ctc_chat_is_enabled = false;
        let ctc_chat_position = '';

        if (ctc_chat_is_cart_page && ctc_chat_block_params.cart_enabled === '1') {
            ctc_chat_page_type = 'cart';
            ctc_chat_is_enabled = true;
            ctc_chat_position = ctc_chat_block_params.cart_position || 'after_cart_table';
        } else if (ctc_chat_is_checkout_page && ctc_chat_block_params.checkout_enabled === '1') {
            ctc_chat_page_type = 'checkout';
            ctc_chat_is_enabled = true;
            ctc_chat_position = ctc_chat_block_params.checkout_position || 'after_payment';
        }

        if (!ctc_chat_is_enabled) {
            return;
        }

        // Create the button HTML
        const ctc_chat_button_html = ctc_chat_create_button_html(ctc_chat_page_type);

        // Insert the button based on position
        ctc_chat_insert_button_at_position(ctc_chat_button_html, ctc_chat_page_type, ctc_chat_position);
    }

    // Create the WhatsApp button HTML
    function ctc_chat_create_button_html(pageType) {
        const ctc_chat_button_text = ctc_chat_block_params.button_text || 'Order via WhatsApp';
        const ctc_chat_bg_color = ctc_chat_block_params.bg_color || '#25D366';
        const ctc_chat_text_color = ctc_chat_block_params.text_color || '#ffffff';
        const ctc_chat_show_icon = ctc_chat_block_params.show_icon === '1';
        const ctc_chat_whatsapp_url = ctc_chat_block_params.whatsapp_url || '#';

        let ctc_chat_html = `
            <div class="ctc-chat-${pageType}-button-container ctc-chat-block-button" style="margin: 20px 0; text-align: center;">
                <a href="${ctc_chat_whatsapp_url}"
                   class="ctc-chat-whatsapp-button ctc-chat-button-${pageType}"
                   style="display: inline-block; background-color: ${ctc_chat_bg_color}; color: ${ctc_chat_text_color}; padding: 12px 24px; text-decoration: none; border-radius: 5px; font-size: 16px; font-weight: 500;"
                   target="_blank" rel="noopener">
        `;

        if (ctc_chat_show_icon) {
            ctc_chat_html += `
                <span class="ctc-chat-whatsapp-icon" style="display: inline-block; margin-right: 8px; vertical-align: middle;">
                    <svg viewBox="0 0 24 24" width="20" height="20" style="vertical-align: middle; fill: currentColor;">
                        <path d="M17.498 14.382c-.301-.15-1.767-.867-2.04-.966-.273-.101-.473-.15-.673.15-.197.295-.771.964-.944 1.162-.175.195-.349.21-.646.075-.3-.15-1.263-.465-2.403-1.485-.888-.795-1.484-1.77-1.66-2.07-.174-.3-.019-.465.13-.615.136-.135.301-.345.451-.523.146-.181.194-.301.297-.496.1-.21.049-.375-.025-.524-.075-.15-.672-1.62-.922-2.206-.24-.584-.487-.51-.672-.51-.172-.015-.371-.015-.571-.015-.2 0-.523.074-.797.359-.273.3-1.045 1.02-1.045 2.475s1.07 2.865 1.219 3.075c.149.195 2.105 3.195 5.1 4.485.714.3 1.27.48 1.704.629.714.227 1.365.195 1.88.121.574-.091 1.767-.721 2.016-1.426.255-.705.255-1.29.18-1.425-.074-.135-.27-.21-.57-.345m-5.446 7.443h-.016c-1.77 0-3.524-.48-5.055-1.38l-.36-.214-3.75.975 1.005-3.645-.239-.375c-.99-1.576-1.516-3.391-1.516-5.26 0-5.445 4.455-9.885 9.942-9.885 2.654 0 5.145 1.035 7.021 2.91 1.875 1.859 2.909 4.35 2.909 6.99-.004 5.444-4.46 9.885-9.935 9.885M20.52 3.449C18.24 1.245 15.24 0 12.045 0 5.463 0 .104 5.334.101 11.893c0 2.096.549 4.14 1.595 5.945L0 24l6.335-1.652c1.746.943 3.71 1.444 5.71 1.447h.006c6.585 0 11.946-5.336 11.949-11.896 0-3.176-1.24-6.165-3.495-8.411"/>
                    </svg>
                </span>
            `;
        }

        ctc_chat_html += `
                    <span class="ctc-chat-button-text">${ctc_chat_button_text}</span>
                </a>
            </div>
        `;

        return ctc_chat_html;
    }

    // Insert button at the appropriate position
    function ctc_chat_insert_button_at_position(buttonHtml, pageType, position) {
        let ctc_chat_target_element = null;
        let ctc_chat_insert_method = 'after';

        if (pageType === 'cart') {
            // For cart block pages
            switch (position) {
                case 'before_cart_table':
                    ctc_chat_target_element = document.querySelector('.wp-block-woocommerce-cart');
                    ctc_chat_insert_method = 'prepend';
                    break;
                case 'proceed_to_checkout':
                    ctc_chat_target_element = document.querySelector('.wp-block-woocommerce-proceed-to-checkout-block');
                    ctc_chat_insert_method = 'before';
                    break;
                case 'after_cart_totals':
                    ctc_chat_target_element = document.querySelector('.wp-block-woocommerce-cart-totals-block');
                    ctc_chat_insert_method = 'after';
                    break;
                case 'after_cart_table':
                default:
                    // Try multiple selectors for better compatibility
                    ctc_chat_target_element = document.querySelector('.wp-block-woocommerce-cart-items-block') ||
                                   document.querySelector('.wp-block-woocommerce-cart');
                    ctc_chat_insert_method = 'after';
                    break;
            }
        } else if (pageType === 'checkout') {
            // For checkout block pages
            switch (position) {
                case 'before_payment':
                    ctc_chat_target_element = document.querySelector('.wp-block-woocommerce-checkout-payment-block');
                    ctc_chat_insert_method = 'before';
                    break;
                case 'after_order_review':
                    ctc_chat_target_element = document.querySelector('.wp-block-woocommerce-checkout-order-summary-block');
                    ctc_chat_insert_method = 'after';
                    break;
                case 'before_order_review':
                    ctc_chat_target_element = document.querySelector('.wp-block-woocommerce-checkout-order-summary-block');
                    ctc_chat_insert_method = 'before';
                    break;
                case 'after_submit':
                    ctc_chat_target_element = document.querySelector('.wp-block-woocommerce-checkout-actions-block');
                    ctc_chat_insert_method = 'after';
                    break;
                case 'after_payment':
                default:
                    ctc_chat_target_element = document.querySelector('.wp-block-woocommerce-checkout-payment-block') ||
                                   document.querySelector('.wp-block-woocommerce-checkout');
                    ctc_chat_insert_method = 'after';
                    break;
            }
        }

        // Insert the button if we found a target
        if (ctc_chat_target_element) {
            // Check if button already exists
            if (document.querySelector('.ctc-chat-block-button')) {
                return;
            }

            const ctc_chat_temp_div = document.createElement('div');
            ctc_chat_temp_div.innerHTML = buttonHtml;
            const ctc_chat_button_element = ctc_chat_temp_div.firstElementChild;

            switch (ctc_chat_insert_method) {
                case 'before':
                    ctc_chat_target_element.parentNode.insertBefore(ctc_chat_button_element, ctc_chat_target_element);
                    break;
                case 'after':
                    ctc_chat_target_element.parentNode.insertBefore(ctc_chat_button_element, ctc_chat_target_element.nextSibling);
                    break;
                case 'prepend':
                    ctc_chat_target_element.prepend(ctc_chat_button_element);
                    break;
                case 'append':
                    ctc_chat_target_element.appendChild(ctc_chat_button_element);
                    break;
            }
        }
    }

    // Initialize on page load
    ctc_chat_ready(function() {
        ctc_chat_insert_whatsapp_button();

        // Also check after a delay for dynamically loaded content
        setTimeout(ctc_chat_insert_whatsapp_button, 1000);

        // Listen for cart updates (for block-based cart)
        if (window.wp && window.wp.data) {
            const { subscribe } = window.wp.data;

            const ctc_chat_unsubscribe = subscribe(() => {
                // Re-insert button after cart updates
                setTimeout(ctc_chat_insert_whatsapp_button, 100);
            });
        }
    });

})();
