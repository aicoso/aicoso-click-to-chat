<?php
/**
 * Public class.
 *
 * This class handles all public-facing functionality.
 *
 * @package ClickToChat
 * @since 1.0.0
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
    die;
}

/**
 * Public class.
 */
class CTC_Public {

    /**
     * Plugin settings
     *
     * @var array
     */
    private $settings;

    /**
     * Constructor
     */
    public function __construct() {
        $this->settings = get_option( 'ctc_settings', array() );
        
        // Initialize hooks
        $this->init_hooks();
    }

    /**
     * Initialize hooks
     */
    private function init_hooks() {
        // Handle advanced options for hiding WooCommerce buttons FIRST (before other hooks)
        add_action( 'init', array( $this, 'handle_advanced_options' ), 999 );

        // Enqueue scripts and styles
        add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_assets' ) );

        // Add custom inline CSS
        add_action( 'wp_head', array( $this, 'add_custom_css' ) );

        // Add JavaScript for variable products
        add_action( 'woocommerce_after_single_product', array( $this, 'add_variable_product_script' ) );

        // Enqueue block-specific scripts for cart and checkout
        add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_block_scripts' ) );

        // AJAX handlers for variation URLs
        add_action( 'wp_ajax_ctc_get_variation_url', array( $this, 'ajax_get_variation_url' ) );
        add_action( 'wp_ajax_nopriv_ctc_get_variation_url', array( $this, 'ajax_get_variation_url' ) );
    }

    /**
     * Enqueue public scripts and styles
     */
    public function enqueue_assets() {
        // Only enqueue assets when needed
        if ( ! $this->should_load_assets() ) {
            return;
        }
        
        // Register and enqueue CSS
        wp_enqueue_style(
            'ctc-public-styles',
            CTC_PLUGIN_URL . 'public/css/public.css',
            array(),
            CTC_VERSION
        );

        // Enqueue additional layout fixes CSS with higher priority
        wp_enqueue_style(
            'ctc-layout-fixes',
            CTC_PLUGIN_URL . 'public/css/button-layout-fixes.css',
            array('ctc-public-styles'),
            CTC_VERSION
        );
        
        // Register and enqueue JavaScript
        wp_enqueue_script(
            'ctc-public-script',
            CTC_PLUGIN_URL . 'public/js/public.js',
            array( 'jquery' ),
            CTC_VERSION,
            true
        );
        
        // Localize script with data
        $localize_data = array(
            'ajaxurl' => admin_url( 'admin-ajax.php' ),
            'nonce'   => wp_create_nonce( 'ctc_public_nonce' ),
        );
        
        wp_localize_script( 'ctc-public-script', 'ctc_public', $localize_data );
    }

    /**
     * Check if assets should be loaded
     * 
     * @return bool True if assets should be loaded, false otherwise.
     */
    private function should_load_assets() {
        // Always load if floating button is enabled
        if ( isset( $this->settings['floating_button']['enabled'] ) && $this->settings['floating_button']['enabled'] ) {
            return true;
        }
        
        // Load on shop pages if enabled
        if ( ( is_shop() || is_product_category() || is_product_tag() ) && 
             isset( $this->settings['shop_page']['enabled'] ) && 
             $this->settings['shop_page']['enabled'] ) {
            return true;
        }
        
        // Load on single product pages if enabled
        if ( is_product() && 
             isset( $this->settings['single_product']['enabled'] ) && 
             $this->settings['single_product']['enabled'] ) {
            return true;
        }
        
        // Load on cart page if enabled
        if ( is_cart() && 
             isset( $this->settings['cart_page']['enabled'] ) && 
             $this->settings['cart_page']['enabled'] ) {
            return true;
        }
        
        // Load on checkout page if enabled
        if ( is_checkout() && ! is_wc_endpoint_url( 'order-received' ) && 
             isset( $this->settings['checkout_page']['enabled'] ) && 
             $this->settings['checkout_page']['enabled'] ) {
            return true;
        }
        
        // Load on thank you page if enabled
        if ( is_wc_endpoint_url( 'order-received' ) && 
             isset( $this->settings['thankyou_page']['enabled'] ) && 
             $this->settings['thankyou_page']['enabled'] ) {
            return true;
        }
        
        // Check if any shortcodes are used
        global $post;
        if ( $post && (has_shortcode( $post->post_content, 'whatsapp_button' ) || has_shortcode( $post->post_content, 'ctc_button' )) ) {
            return true;
        }
        
        return false;
    }

    /**
     * Add custom CSS to the head
     */
    public function add_custom_css() {
        // Only add custom CSS when needed
        if ( ! $this->should_load_assets() ) {
            return;
        }
        
        // Get custom CSS from settings
        $custom_css = isset( $this->settings['button_settings']['custom_css'] ) ? 
                     $this->settings['button_settings']['custom_css'] : '';
        
        // If no custom CSS, return
        if ( empty( $custom_css ) ) {
            return;
        }
        
        // Output the custom CSS
        echo '<style type="text/css">' . "\n";
        echo esc_html( $custom_css ) . "\n";
        echo '</style>' . "\n";
    }

    /**
     * Add JavaScript for variable products
     */
    public function add_variable_product_script() {
        global $product;
        
        // Only add for variable products
        if ( ! is_product() || ! $product || ! $product->is_type( 'variable' ) ) {
            return;
        }
        
        // Only add if single product button is enabled
        if ( ! isset( $this->settings['single_product']['enabled'] ) || ! $this->settings['single_product']['enabled'] ) {
            return;
        }
        
        // Prepare the WhatsApp URL base
        $link_generator = new CTC_WhatsApp_Link_Generator();
        $whatsapp_number = $link_generator->get_whatsapp_number( $product->get_id() );
        
        // If no number, return
        if ( empty( $whatsapp_number ) ) {
            return;
        }
        
        // Format the WhatsApp number
        $whatsapp_number = preg_replace( '/[^0-9]/', '', $whatsapp_number );
        
        // Get the message template
        $message_template = isset( $this->settings['message_templates']['variations'] ) ? 
                           $this->settings['message_templates']['variations'] : '';
        
        // Check for product-specific custom message
        $custom_message = get_post_meta( $product->get_id(), '_ctc_custom_message', true );
        if ( ! empty( $custom_message ) ) {
            $message_template = $custom_message;
        }
        
        // Prepare the JavaScript
        ?>
        <script type="text/javascript">
            (function($) {
                'use strict';
                
                $(document).ready(function() {
                    // Get references to elements
                    var $variationForm = $('.variations_form');
                    var $whatsappButton = $('.ctc-whatsapp-button');
                    
                    // If either element is missing, return
                    if (!$variationForm.length || !$whatsappButton.length) {
                        return;
                    }
                    
                    // Listen for variation changes
                    $variationForm.on('show_variation', function(event, variation) {
                        // Store the selected variation details
                        var variationDetails = [];
                        $('.variations select').each(function() {
                            var $select = $(this);
                            var attributeName = $select.data('attribute_name') || $select.attr('name');
                            var attributeValue = $select.val();
                            
                            if (attributeValue) {
                                // Get the attribute label
                                var label = attributeName.replace('attribute_', '');
                                label = label.replace('pa_', '');
                                label = label.replace(/-/g, ' ');
                                label = label.charAt(0).toUpperCase() + label.slice(1);
                                
                                // Get the attribute value label
                                var valueLabel = '';
                                var $selectedOption = $select.find('option:selected');
                                
                                if ($selectedOption.length) {
                                    valueLabel = $selectedOption.text();
                                } else {
                                    valueLabel = attributeValue;
                                }
                                
                                variationDetails.push(label + ': ' + valueLabel);
                            }
                        });
                        
                        // Create message with variation details
                        var baseUrl = 'https://wa.me/<?php echo esc_js( $whatsapp_number ); ?>?text=';
                        var message = '<?php echo esc_js( $message_template ); ?>';
                        
                        // Replace placeholders in message
                        message = message.replace('{product_name}', '<?php echo esc_js( $product->get_name() ); ?>');
                        message = message.replace('{variation_details}', variationDetails.join(', '));
                        message = message.replace('{variation_price}', variation.display_price.toFixed(2));
                        message = message.replace('{product_url}', '<?php echo esc_js( get_permalink( $product->get_id() ) ); ?>');
                        
                        // Update the WhatsApp button URL
                        $whatsappButton.attr('href', baseUrl + encodeURIComponent(message));
                    });
                });
            })(jQuery);
        </script>
        <?php
    }

    /**
     * Enqueue scripts for WooCommerce block-based cart and checkout
     */
    public function enqueue_block_scripts() {
        // Only load on cart or checkout pages
        if (!is_cart() && !is_checkout()) {
            return;
        }

        // Check if plugin is enabled globally
        $plugin_enabled = isset($this->settings['plugin_enabled']) ? $this->settings['plugin_enabled'] : true;
        if (!$plugin_enabled) {
            return;
        }

        // Check if cart or checkout is enabled
        $cart_enabled = isset($this->settings['cart_page']['enabled']) && $this->settings['cart_page']['enabled'];
        $checkout_enabled = isset($this->settings['checkout_page']['enabled']) && $this->settings['checkout_page']['enabled'];

        if (!$cart_enabled && !$checkout_enabled) {
            return;
        }

        // Check if current page is excluded
        if ($this->is_page_excluded()) {
            return;
        }

        // Enqueue the block support script
        wp_enqueue_script(
            'ctc-cart-checkout-blocks',
            CTC_PLUGIN_URL . 'public/js/cart-checkout-blocks.js',
            array(),
            CTC_VERSION,
            true
        );

        // Get WhatsApp URL
        $link_generator = new CTC_WhatsApp_Link_Generator();
        $whatsapp_url = '';

        if (is_cart()) {
            $whatsapp_url = $link_generator->get_cart_url();
        } elseif (is_checkout()) {
            $whatsapp_url = $link_generator->get_cart_url(); // Uses same URL as cart
        }

        // If no URL generated, create a basic one
        if (empty($whatsapp_url)) {
            $whatsapp_number = isset($this->settings['whatsapp_numbers'][0]['number']) ?
                              $this->settings['whatsapp_numbers'][0]['number'] : '';
            if (!empty($whatsapp_number)) {
                $whatsapp_number = preg_replace('/[^0-9]/', '', $whatsapp_number);
                $default_message = __('Hello! I need help with my order.', 'click-to-chat');
                $whatsapp_url = 'https://wa.me/' . $whatsapp_number . '?text=' . urlencode($default_message);
            }
        }

        // Localize script with parameters
        wp_localize_script('ctc-cart-checkout-blocks', 'ctc_block_params', array(
            'cart_enabled' => $cart_enabled ? '1' : '0',
            'checkout_enabled' => $checkout_enabled ? '1' : '0',
            'cart_position' => isset($this->settings['cart_page']['position']) ?
                              $this->settings['cart_page']['position'] : 'after_cart_table',
            'checkout_position' => isset($this->settings['checkout_page']['position']) ?
                                  $this->settings['checkout_page']['position'] : 'after_payment',
            'button_text' => isset($this->settings['button_settings']['text']) ?
                            $this->settings['button_settings']['text'] : __('Order via WhatsApp', 'click-to-chat'),
            'bg_color' => isset($this->settings['button_settings']['bg_color']) ?
                         $this->settings['button_settings']['bg_color'] : '#25D366',
            'text_color' => isset($this->settings['button_settings']['text_color']) ?
                           $this->settings['button_settings']['text_color'] : '#ffffff',
            'show_icon' => isset($this->settings['button_settings']['icon']) && $this->settings['button_settings']['icon'] ? '1' : '0',
            'whatsapp_url' => $whatsapp_url,
        ));
    }

    /**
     * Handle advanced options for hiding WooCommerce buttons
     */
    public function handle_advanced_options() {
        // Check if plugin is enabled
        $plugin_enabled = isset($this->settings['plugin_enabled']) ? $this->settings['plugin_enabled'] : true;
        if (!$plugin_enabled) {
            return;
        }

        // Check if any advanced options are enabled
        if (!isset($this->settings['advanced'])) {
            return;
        }

        $advanced = $this->settings['advanced'];

        // Check for catalog mode first (overrides individual settings)
        if (isset($advanced['catalog_mode']) && $advanced['catalog_mode']) {
            $this->hide_all_purchase_buttons();
            return;
        }

        // Hide Add to Cart buttons
        if (isset($advanced['hide_add_to_cart']) && $advanced['hide_add_to_cart']) {
            $this->hide_add_to_cart_buttons();
        }

        // Hide Proceed to Checkout button
        if (isset($advanced['hide_proceed_checkout']) && $advanced['hide_proceed_checkout']) {
            $this->hide_proceed_checkout_button();
        }

        // Hide Place Order button
        if (isset($advanced['hide_place_order']) && $advanced['hide_place_order']) {
            $this->hide_place_order_button();
        }
    }

    /**
     * Hide all purchase buttons (catalog mode)
     */
    private function hide_all_purchase_buttons() {
        $this->hide_add_to_cart_buttons();
        $this->hide_proceed_checkout_button();
        $this->hide_place_order_button();
    }

    /**
     * Hide Add to Cart buttons
     */
    private function hide_add_to_cart_buttons() {
        // Remove add to cart buttons globally first
        remove_action('woocommerce_single_product_summary', 'woocommerce_template_single_add_to_cart', 30);
        remove_action('woocommerce_after_shop_loop_item', 'woocommerce_template_loop_add_to_cart', 10);

        // Add CSS to hide any remaining add to cart buttons
        add_action('wp_head', array($this, 'hide_add_to_cart_css'));

    }

    /**
     * Hide Proceed to Checkout button
     */
    private function hide_proceed_checkout_button() {
        // Remove proceed to checkout button
        remove_action('woocommerce_proceed_to_checkout', 'woocommerce_button_proceed_to_checkout', 20);

        // Add CSS to hide the button on all pages
        add_action('wp_head', array($this, 'hide_proceed_checkout_css'));
    }

    /**
     * Hide Place Order button
     */
    private function hide_place_order_button() {
        // Add CSS to hide the place order button on all pages
        add_action('wp_head', array($this, 'hide_place_order_css'));

        // Optionally prevent form submission
        add_action('wp_footer', array($this, 'disable_checkout_form_submission'));
    }


    /**
     * Add CSS to hide Add to Cart buttons
     */
    public function hide_add_to_cart_css() {
        ?>
        <style type="text/css">
            /* Hide WooCommerce Add to Cart buttons only, NOT WhatsApp buttons */
            .single_add_to_cart_button:not(.ctc-whatsapp-button),
            .add_to_cart_button:not(.ctc-whatsapp-button),
            .product_type_simple.add_to_cart_button,
            .product_type_variable.add_to_cart_button,
            .product_type_grouped.add_to_cart_button,
            .product_type_external.add_to_cart_button,
            .ajax_add_to_cart,
            form.cart button.single_add_to_cart_button:not(.ctc-whatsapp-button) {
                display: none !important;
            }

            /* Ensure WhatsApp buttons are visible */
            .ctc-whatsapp-button,
            a.ctc-whatsapp-button {
                display: inline-flex !important;
            }
        </style>
        <?php
    }

    /**
     * Add CSS to hide Proceed to Checkout button
     */
    public function hide_proceed_checkout_css() {
        ?>
        <style type="text/css">
            /* Hide WooCommerce checkout button only, NOT WhatsApp buttons */
            .wc-proceed-to-checkout a.checkout-button:not(.ctc-whatsapp-button),
            .wc-proceed-to-checkout .checkout-button:not(.ctc-whatsapp-button),
            .wp-block-woocommerce-proceed-to-checkout-block {
                display: none !important;
            }

            /* Ensure WhatsApp buttons are visible */
            .ctc-whatsapp-button,
            a.ctc-whatsapp-button {
                display: inline-flex !important;
            }
        </style>
        <?php
    }

    /**
     * Add CSS to hide Place Order button
     */
    public function hide_place_order_css() {
        ?>
        <style type="text/css">
            /* Hide WooCommerce place order button only, NOT WhatsApp buttons */
            #place_order:not(.ctc-whatsapp-button),
            .woocommerce-checkout-payment button#place_order:not(.ctc-whatsapp-button),
            .wp-block-woocommerce-checkout-actions-block button:not(.ctc-whatsapp-button) {
                display: none !important;
            }

            /* Ensure WhatsApp buttons are visible */
            .ctc-whatsapp-button,
            a.ctc-whatsapp-button {
                display: inline-flex !important;
            }
        </style>
        <?php
    }

    /**
     * Disable checkout form submission
     */
    public function disable_checkout_form_submission() {
        if (is_checkout()) {
            ?>
            <script type="text/javascript">
                jQuery(document).ready(function($) {
                    // Disable form submission
                    $('form.checkout').on('submit', function(e) {
                        e.preventDefault();
                        return false;
                    });
                });
            </script>
            <?php
        }
    }


    /**
     * Check if the current page is excluded
     *
     * @return bool True if page is excluded, false otherwise
     */
    private function is_page_excluded() {
        // If exclusions not set or empty, nothing is excluded
        if (empty($this->settings['exclusions'])) {
            return false;
        }

        // Get current page ID
        $current_id = get_the_ID();

        // Special handling for WooCommerce Cart page
        if (is_cart()) {
            $cart_page_id = wc_get_page_id('cart');
            if ($cart_page_id && isset($this->settings['exclusions']['pages']) && !empty($this->settings['exclusions']['pages'])) {
                if (in_array($cart_page_id, $this->settings['exclusions']['pages'], true)) {
                    return true;
                }
            }
        }

        // Special handling for WooCommerce Checkout page
        if (is_checkout()) {
            $checkout_page_id = wc_get_page_id('checkout');
            if ($checkout_page_id && isset($this->settings['exclusions']['pages']) && !empty($this->settings['exclusions']['pages'])) {
                if (in_array($checkout_page_id, $this->settings['exclusions']['pages'], true)) {
                    return true;
                }
            }
        }

        // Check general page exclusions if we have a current ID
        if ($current_id && isset($this->settings['exclusions']['pages']) && !empty($this->settings['exclusions']['pages'])) {
            if (in_array($current_id, $this->settings['exclusions']['pages'], true)) {
                return true;
            }
        }

        return false;
    }

    /**
     * AJAX handler to get variation-specific WhatsApp URL
     */
    public function ajax_get_variation_url() {
        // Check nonce
        if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'ctc_public_nonce' ) ) {
            wp_send_json_error( array( 'message' => __( 'Security check failed.', 'click-to-chat' ) ) );
        }

        // Get product ID and variations
        $product_id = isset( $_POST['product_id'] ) ? intval( $_POST['product_id'] ) : 0;
        $variations = isset( $_POST['variations'] ) ? $_POST['variations'] : array();

        if ( ! $product_id ) {
            wp_send_json_error( array( 'message' => __( 'Invalid product ID.', 'click-to-chat' ) ) );
        }

        // Initialize link generator
        $link_generator = new CTC_WhatsApp_Link_Generator();

        // Generate WhatsApp URL with variations
        $whatsapp_url = $link_generator->get_product_url( $product_id, $variations );

        if ( empty( $whatsapp_url ) ) {
            wp_send_json_error( array( 'message' => __( 'Could not generate WhatsApp URL.', 'click-to-chat' ) ) );
        }

        wp_send_json_success( array( 'url' => $whatsapp_url ) );
    }
}

// Initialize the class
new CTC_Public();