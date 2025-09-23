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
        // Enqueue scripts and styles
        add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_assets' ) );

        // Add custom inline CSS
        add_action( 'wp_head', array( $this, 'add_custom_css' ) );

        // Add JavaScript for variable products
        add_action( 'woocommerce_after_single_product', array( $this, 'add_variable_product_script' ) );

        // Enqueue block-specific scripts for cart and checkout
        add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_block_scripts' ) );
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

        // Check if cart or checkout is enabled
        $cart_enabled = isset($this->settings['cart_page']['enabled']) && $this->settings['cart_page']['enabled'];
        $checkout_enabled = isset($this->settings['checkout_page']['enabled']) && $this->settings['checkout_page']['enabled'];

        if (!$cart_enabled && !$checkout_enabled) {
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
}

// Initialize the class
new CTC_Public();