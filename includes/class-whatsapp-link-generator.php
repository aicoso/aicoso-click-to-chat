<?php
/**
 * WhatsApp link generator class.
 *
 * This class handles the generation of WhatsApp URLs with prefilled messages
 * for different contexts (single product, shop page, cart, etc.)
 *
 * @package ClickToChat
 * @since 1.0.0
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
    die;
}

/**
 * WhatsApp Link Generator class.
 */
class CTC_WhatsApp_Link_Generator {

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
    }

    /**
     * Get the appropriate WhatsApp number for the current context
     *
     * @param int $product_id Optional product ID.
     * @param int $category_id Optional category ID.
     * @param int $page_id Optional page ID.
     * @return string The WhatsApp number to use.
     */
    public function get_whatsapp_number( $product_id = null, $category_id = null, $page_id = null ) {
        // Return empty if no numbers configured
        if ( empty( $this->settings['whatsapp_numbers'] ) ) {
            return '';
        }

        // First, check for specific assignments based on context

        // Check for product-specific assignments
        if ( $product_id ) {
            $product_id = absint( $product_id );

            foreach ( $this->settings['whatsapp_numbers'] as $number_data ) {
                if ( ! empty( $number_data['assignments']['products'] ) &&
                     is_array( $number_data['assignments']['products'] ) ) {
                    // Convert all stored IDs to integers for comparison
                    $assigned_products = array_map( 'absint', $number_data['assignments']['products'] );
                    if ( in_array( $product_id, $assigned_products, true ) ) {
                        return $number_data['number'];
                    }
                }
            }

            // If no direct product assignment, check product's categories
            $terms = get_the_terms( $product_id, 'product_cat' );
            if ( $terms && ! is_wp_error( $terms ) ) {
                foreach ( $terms as $term ) {
                    $term_id = absint( $term->term_id );
                    foreach ( $this->settings['whatsapp_numbers'] as $number_data ) {
                        if ( ! empty( $number_data['assignments']['categories'] ) &&
                             is_array( $number_data['assignments']['categories'] ) ) {
                            // Convert all stored IDs to integers for comparison
                            $assigned_categories = array_map( 'absint', $number_data['assignments']['categories'] );
                            if ( in_array( $term_id, $assigned_categories, true ) ) {
                                return $number_data['number'];
                            }
                        }
                    }
                }
            }
        }

        // Check for category-specific assignments
        if ( $category_id ) {
            $category_id = absint( $category_id );

            foreach ( $this->settings['whatsapp_numbers'] as $number_data ) {
                if ( ! empty( $number_data['assignments']['categories'] ) &&
                     is_array( $number_data['assignments']['categories'] ) ) {
                    // Convert all stored IDs to integers for comparison
                    $assigned_categories = array_map( 'absint', $number_data['assignments']['categories'] );
                    if ( in_array( $category_id, $assigned_categories, true ) ) {
                        return $number_data['number'];
                    }
                }
            }
        }

        // Check for page-specific assignments
        if ( $page_id ) {
            $page_id = absint( $page_id );

            foreach ( $this->settings['whatsapp_numbers'] as $number_data ) {
                if ( ! empty( $number_data['assignments']['pages'] ) &&
                     is_array( $number_data['assignments']['pages'] ) ) {
                    // Convert all stored IDs to integers for comparison
                    $assigned_pages = array_map( 'absint', $number_data['assignments']['pages'] );
                    if ( in_array( $page_id, $assigned_pages, true ) ) {
                        return $number_data['number'];
                    }
                }
            }
        }

        // No specific assignment found, now look for a default/fallback number

        // Step 1: Check if any number is explicitly marked as default
        foreach ( $this->settings['whatsapp_numbers'] as $number_data ) {
            if ( ! empty( $number_data['is_default'] ) ) {
                return $number_data['number'];
            }
        }

        // Step 2: Look for a number with NO assignments (implicit default)
        foreach ( $this->settings['whatsapp_numbers'] as $number_data ) {
            $has_assignments = false;

            // Check if this number has any assignments
            if ( isset( $number_data['assignments'] ) && is_array( $number_data['assignments'] ) ) {
                if ( ! empty( $number_data['assignments']['products'] ) ||
                     ! empty( $number_data['assignments']['categories'] ) ||
                     ! empty( $number_data['assignments']['pages'] ) ) {
                    $has_assignments = true;
                }
            }

            // If this number has no assignments, use it as default
            if ( ! $has_assignments ) {
                return $number_data['number'];
            }
        }

        // Step 3: If all numbers have assignments and none match, return empty
        // This means no WhatsApp button should be shown on unassigned pages
        // unless a number is explicitly marked as default
        return '';
    }

    /**
     * Generate a WhatsApp URL for a product
     *
     * @param int    $product_id The product ID.
     * @param array  $variations The selected variations (optional).
     * @return string The generated WhatsApp URL.
     */
    public function get_product_url( $product_id, $variations = array() ) {
        // Check if WooCommerce is active and function exists
        if ( ! function_exists( 'wc_get_product' ) ) {
            return '';
        }
        
        $product = wc_get_product( $product_id );
        
        if ( ! $product || ! is_object( $product ) || ! $product instanceof WC_Product ) {
            return '';
        }

        // Get the WhatsApp number to use
        // When on shop/category pages, also check for page/category specific assignments
        $page_id = null;
        $category_id = null;

        // Check if we're on shop page
        if ( function_exists( 'is_shop' ) && is_shop() ) {
            $page_id = wc_get_page_id( 'shop' );
        }
        // Check if we're on a category page
        elseif ( function_exists( 'is_product_category' ) && is_product_category() ) {
            $category = get_queried_object();
            if ( $category && isset( $category->term_id ) ) {
                $category_id = $category->term_id;
            }
        }
        // Check if we're on any other page
        elseif ( is_page() ) {
            $page_id = get_the_ID();
        }

        // Get the appropriate number considering all contexts
        $whatsapp_number = $this->get_whatsapp_number( $product_id, $category_id, $page_id );

        if ( empty( $whatsapp_number ) ) {
            return '';
        }

        // Format the WhatsApp number (remove any non-numeric characters)
        $whatsapp_number = preg_replace( '/[^0-9]/', '', $whatsapp_number );
        
        // Prepare the message
        if ( empty( $variations ) ) {
            $message = $this->prepare_single_product_message( $product );
        } else {
            $message = $this->prepare_variation_message( $product, $variations );
        }
        
        // Build the WhatsApp URL
        return $this->build_whatsapp_url( $whatsapp_number, $message );
    }

    /**
     * Generate a WhatsApp URL for the shop page
     *
     * @param int $category_id The category ID if available.
     * @return string The generated WhatsApp URL.
     */
    public function get_shop_url( $category_id = null ) {
        // Get the WhatsApp number to use
        $whatsapp_number = $this->get_whatsapp_number( null, $category_id );
        
        if ( empty( $whatsapp_number ) ) {
            return '';
        }

        // Format the WhatsApp number
        $whatsapp_number = preg_replace( '/[^0-9]/', '', $whatsapp_number );

        // Get the message template
        $message_template = isset( $this->settings['message_templates']['shop_page'] ) ? 
                           $this->settings['message_templates']['shop_page'] : '';

        // Replace placeholders
        $message = $this->replace_shop_placeholders( $message_template, $category_id );

        // Build and return the WhatsApp URL
        return $this->build_whatsapp_url( $whatsapp_number, $message );
    }

    /**
     * Generate a WhatsApp URL for the cart/checkout
     *
     * @return string The generated WhatsApp URL.
     */
    public function get_cart_url() {
        // Check if WooCommerce is active
        if ( ! function_exists( 'WC' ) ) {
            return '';
        }
        
        // Check if cart is available
        if ( ! WC()->cart || ! is_object( WC()->cart ) ) {
            return '';
        }

        // Check if we're on a specific page (cart page might have a page ID)
        $page_id = null;
        if ( is_page() ) {
            $page_id = get_the_ID();
        }

        // Get the WhatsApp number (will check page assignments if on a page)
        $whatsapp_number = $this->get_whatsapp_number( null, null, $page_id );
        
        if ( empty( $whatsapp_number ) ) {
            return '';
        }

        // Format the WhatsApp number
        $whatsapp_number = preg_replace( '/[^0-9]/', '', $whatsapp_number );

        // Get the message template - using 'cart_checkout' as per the settings
        $message_template = isset( $this->settings['message_templates']['cart_checkout'] ) ?
                           $this->settings['message_templates']['cart_checkout'] : '';

        // Replace placeholders
        $message = $this->replace_cart_placeholders( $message_template );

        // Build and return the WhatsApp URL
        return $this->build_whatsapp_url( $whatsapp_number, $message );
    }

    /**
     * Generate a WhatsApp URL for the thank you page
     *
     * @param int $order_id The order ID.
     * @return string The generated WhatsApp URL.
     */
    public function get_thankyou_url( $order_id ) {
        // Check if WooCommerce is active
        if ( ! function_exists( 'wc_get_order' ) ) {
            return '';
        }
        
        // Get the order
        $order = wc_get_order( $order_id );
        
        // Check if order is valid
        if ( ! $order || ! is_object( $order ) || ! $order instanceof WC_Order ) {
            return '';
        }

        // Check if we're on a specific page (thank you page might have a page ID)
        $page_id = null;
        if ( is_page() ) {
            $page_id = get_the_ID();
        }

        // Get the WhatsApp number (will check page assignments if on a page)
        $whatsapp_number = $this->get_whatsapp_number( null, null, $page_id );
        
        if ( empty( $whatsapp_number ) ) {
            return '';
        }

        // Format the WhatsApp number
        $whatsapp_number = preg_replace( '/[^0-9]/', '', $whatsapp_number );

        // Get the message template
        $message_template = isset( $this->settings['message_templates']['thankyou'] ) ? 
                           $this->settings['message_templates']['thankyou'] : '';

        // Replace placeholders
        $message = $this->replace_order_placeholders( $message_template, $order );

        // Build and return the WhatsApp URL
        return $this->build_whatsapp_url( $whatsapp_number, $message );
    }

    /**
     * Generate a WhatsApp URL for the floating button
     *
     * @return string The generated WhatsApp URL.
     */
    public function get_floating_url() {
        // Get current context for number selection
        $page_id = null;
        $category_id = null;
        $product_id = null;

        // Check if we're on a product page
        if ( function_exists( 'is_product' ) && is_product() ) {
            global $product;
            if ( $product && is_object( $product ) ) {
                $product_id = $product->get_id();
            }
        }
        // Check if we're on shop page
        elseif ( function_exists( 'is_shop' ) && is_shop() ) {
            $page_id = wc_get_page_id( 'shop' );
        }
        // Check if we're on a category page
        elseif ( function_exists( 'is_product_category' ) && is_product_category() ) {
            $category = get_queried_object();
            if ( $category && isset( $category->term_id ) ) {
                $category_id = $category->term_id;
            }
        }
        // Check if we're on cart page
        elseif ( function_exists( 'is_cart' ) && is_cart() ) {
            $page_id = wc_get_page_id( 'cart' );
        }
        // Check if we're on checkout page
        elseif ( function_exists( 'is_checkout' ) && is_checkout() ) {
            $page_id = wc_get_page_id( 'checkout' );
        }
        // Check if we're on any other page
        elseif ( is_page() ) {
            $page_id = get_the_ID();
        }

        // Get the WhatsApp number considering all contexts
        $whatsapp_number = $this->get_whatsapp_number( $product_id, $category_id, $page_id );

        if ( empty( $whatsapp_number ) ) {
            return '';
        }

        // Format the WhatsApp number
        $whatsapp_number = preg_replace( '/[^0-9]/', '', $whatsapp_number );

        // Get the message template
        $message_template = isset( $this->settings['message_templates']['floating'] ) ? 
                           $this->settings['message_templates']['floating'] : '';

        // Replace placeholders
        $message = $this->replace_general_placeholders( $message_template );

        // Build and return the WhatsApp URL
        return $this->build_whatsapp_url( $whatsapp_number, $message );
    }

    /**
     * Build a WhatsApp URL with the provided number and message
     *
     * @param string $number  The WhatsApp number.
     * @param string $message The message to send.
     * @return string The complete WhatsApp URL.
     */
    private function build_whatsapp_url( $number, $message ) {
        // Decode HTML entities in the message
        $decoded_message = html_entity_decode($message, ENT_QUOTES | ENT_HTML5, 'UTF-8');
            
        // Make sure line breaks are preserved properly
        $decoded_message = str_replace("\n", "%0A", $decoded_message);
            
        // Build the WhatsApp URL
        return 'https://wa.me/' . $number . '?text=' . rawurlencode($decoded_message);
    }

    /**
     * Prepare message for a single product
     *
     * @param WC_Product $product The product object.
     * @return string The prepared message.
     */
    private function prepare_single_product_message( $product ) {
        // Check if product is valid
        if ( ! is_object( $product ) || ! $product instanceof WC_Product ) {
            return '';
        }
        
        // Get the message template
        $message_template = isset( $this->settings['message_templates']['product'] ) ? 
                           $this->settings['message_templates']['product'] : '';

        // Get product data safely
        $product_name = '';
        $product_price = 0;
        $product_url = '';
        
        if ( method_exists( $product, 'get_name' ) ) {
            $product_name = $product->get_name();
        }
        
        if ( method_exists( $product, 'get_price' ) ) {
            $product_price = $product->get_price();
        }
        
        if ( method_exists( $product, 'get_id' ) && function_exists( 'get_permalink' ) ) {
            $product_url = get_permalink( $product->get_id() );
        }

        // Replace placeholders
        $replacements = array(
            '{product_name}' => $product_name,
            '{price}'        => wp_strip_all_tags( wc_price( $product_price ) ),
            '{product_url}'  => $product_url,
        );
        
        foreach ( $replacements as $placeholder => $value ) {
            $message_template = str_replace( $placeholder, $value, $message_template );
        }
        
        return $message_template;
    }

    /**
     * Prepare message for a product with variations
     *
     * @param WC_Product $product    The product object.
     * @param array      $variations The selected variations.
     * @return string The prepared message.
     */
    private function prepare_variation_message( $product, $variations ) {
        // Check if product is valid
        if ( ! is_object( $product ) || ! $product instanceof WC_Product ) {
            return '';
        }
            
        // Check if variations is an array
        if ( ! is_array( $variations ) ) {
            $variations = array();
        }
            
        // Get the message template
        $message_template = isset( $this->settings['message_templates']['variations'] ) ? 
                           $this->settings['message_templates']['variations'] : '';

        // Get variation details safely
        $variation_details = '';
        if ( function_exists( 'wc_attribute_label' ) ) {
            foreach ( $variations as $attribute => $value ) {
                if ( is_string( $attribute ) && is_string( $value ) ) {
                    $attribute_label = wc_attribute_label( str_replace( 'attribute_', '', $attribute ), $product );
                    $variation_details .= $attribute_label . ': ' . $value . ', ';
                }
            }
            $variation_details = rtrim( $variation_details, ', ' );
        }

        // Get product data safely
        $product_name = '';
        $product_price = 0;
        $product_url = '';
        $variation_price = 0;
    
        if ( method_exists( $product, 'get_name' ) ) {
            $product_name = $product->get_name();
        }
        
        if ( method_exists( $product, 'get_price' ) ) {
            $product_price = $product->get_price();
            $variation_price = $product_price;
        }
        
        if ( method_exists( $product, 'get_id' ) && function_exists( 'get_permalink' ) ) {
            $product_url = get_permalink( $product->get_id() );
        }
        
        // Try to get the variation price if possible
        if ( method_exists( $product, 'is_type' ) && $product->is_type( 'variable' ) && function_exists( 'wc_get_product' ) ) {
            // Use the recommended method to find matching variation
            $variation_id = 0;
            
            // Find matching variation using WooCommerce's data store API
            if ( function_exists( 'WC' ) ) {
                try {
                    // Use the WC_Product_Data_Store_CPT approach which is the modern method
                    if ( class_exists( 'WC_Product_Data_Store_CPT' ) ) {
                        $data_store = new WC_Product_Data_Store_CPT();
                        $variation_id = $data_store->find_matching_product_variation( $product, $variations );
                    } elseif ( class_exists( 'WC_Data_Store' ) ) {
                        // Alternative approach using WC_Data_Store
                        $data_store = WC_Data_Store::load( 'product' );
                        if ( is_object( $data_store ) && method_exists( $data_store, 'find_matching_product_variation' ) ) {
                            $variation_id = $data_store->find_matching_product_variation( $product, $variations );
                        }
                    }
                } catch ( Exception $e ) {
                    // Log error or handle exception if needed
                    error_log( 'Click to Chat: Error finding variation - ' . $e->getMessage() );
                }
            }
            
            if ( $variation_id ) {
                $variation = wc_get_product( $variation_id );
                if ( $variation && is_object( $variation ) && method_exists( $variation, 'get_price' ) ) {
                    $variation_price = $variation->get_price();
                }
            }
        }

        // Replace placeholders
        $replacements = array(
            '{product_name}'      => $product_name,
            '{variation_details}' => $variation_details,
            '{variation_price}'   => wp_strip_all_tags( wc_price( $variation_price ) ),
            '{product_url}'       => $product_url,
        );

        foreach ( $replacements as $placeholder => $value ) {
            $message_template = str_replace( $placeholder, $value, $message_template );
        }

        return $message_template;
    }

    /**
     * Replace shop page placeholders in the message
     *
     * @param string $message_template The message template.
     * @param int    $category_id      The category ID if available.
     * @return string The message with replaced placeholders.
     */
    private function replace_shop_placeholders( $message_template, $category_id = null ) {
        $category_name = esc_html__( 'store', 'click-to-chat' );
        
        if ( $category_id ) {
            $term = get_term( $category_id, 'product_cat' );
            if ( $term && ! is_wp_error( $term ) ) {
                $category_name = $term->name;
            }
        }

        // Replace placeholders
        $replacements = array(
            '{category_name}'   => $category_name,
            '{current_page_url}' => esc_url( (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]" ),
        );

        foreach ( $replacements as $placeholder => $value ) {
            $message_template = str_replace( $placeholder, $value, $message_template );
        }

        return $message_template;
    }

    /**
     * Replace cart placeholders in the message
     *
     * @param string $message_template The message template.
     * @return string The message with replaced placeholders.
     */
    private function replace_cart_placeholders( $message_template ) {
        // Check if WooCommerce and cart are available
        if ( ! function_exists( 'WC' ) || ! WC()->cart || ! is_object( WC()->cart ) ) {
            return $message_template;
        }
        
        $cart_items_list = '';
        
        // Get cart items safely
        $cart_items = WC()->cart->get_cart();
        if ( is_array( $cart_items ) ) {
            foreach ( $cart_items as $cart_item ) {
                if ( isset( $cart_item['data'] ) && is_object( $cart_item['data'] ) && $cart_item['data'] instanceof WC_Product ) {
                    $product = $cart_item['data'];
                    
                    // Get product name
                    $item_name = '';
                    if ( method_exists( $product, 'get_name' ) ) {
                        $item_name = $product->get_name();
                    }
                    
                    // Get quantity
                    $item_quantity = isset( $cart_item['quantity'] ) ? absint( $cart_item['quantity'] ) : 1;
                    
                    // Get line total
                    $item_total = 0;
                    if ( isset( $cart_item['line_total'] ) ) {
                        $item_total = $cart_item['line_total'];
                    }
                    
                    // Add variation details if available
                    if ( isset( $cart_item['variation'] ) && is_array( $cart_item['variation'] ) ) {
                        $variation_details = array();
                        foreach ( $cart_item['variation'] as $attribute => $value ) {
                            if ( function_exists( 'wc_attribute_label' ) ) {
                                $attribute_label = wc_attribute_label( str_replace( 'attribute_', '', $attribute ), $product );
                                $variation_details[] = $attribute_label . ': ' . $value;
                            }
                        }
                        
                        if ( ! empty( $variation_details ) ) {
                            $item_name .= ' (' . implode( ', ', $variation_details ) . ')';
                        }
                    }
                    
                    $cart_items_list .= $item_name . ' x ' . $item_quantity . ' - ' . 
                                       wp_strip_all_tags( wc_price( $item_total ) ) . "\n";
                }
            }
        }
        
        // Get cart totals safely
        $cart_subtotal = 0;
        $shipping_method = esc_html__( 'Not calculated', 'click-to-chat' );
        $shipping_cost = '';
        $cart_total = 0;
        
        if ( method_exists( WC()->cart, 'get_subtotal' ) ) {
            $cart_subtotal = WC()->cart->get_subtotal();
        }
        
        // Try to get shipping info
        if ( function_exists( 'WC' ) && isset( WC()->session ) ) {
            $chosen_methods = WC()->session->get( 'chosen_shipping_methods' );
            $packages = WC()->shipping()->get_packages();
            
            if ( is_array( $chosen_methods ) && ! empty( $chosen_methods ) && is_array( $packages ) ) {
                foreach ( $packages as $i => $package ) {
                    if ( isset( $chosen_methods[ $i ] ) && isset( $package['rates'][ $chosen_methods[ $i ] ] ) ) {
                        $rate = $package['rates'][ $chosen_methods[ $i ] ];
                        $shipping_method = $rate->get_label();
                        $shipping_cost = wp_strip_all_tags( wc_price( $rate->get_cost() ) );
                        break;
                    }
                }
            }
        }
        
        if ( method_exists( WC()->cart, 'get_total' ) ) {
            $cart_total = WC()->cart->get_total( 'edit' );
        }

        // Replace placeholders
        $replacements = array(
            '{cart_items_list}' => $cart_items_list,
            '{cart_subtotal}'   => wp_strip_all_tags( wc_price( $cart_subtotal ) ),
            '{shipping_method}' => $shipping_method,
            '{shipping_cost}'   => $shipping_cost,
            '{cart_total}'      => wp_strip_all_tags( wc_price( $cart_total ) ),
        );

        foreach ( $replacements as $placeholder => $value ) {
            $message_template = str_replace( $placeholder, $value, $message_template );
        }

        return $message_template;
    }

    /**
     * Replace order placeholders in the message
     *
     * @param string   $message_template The message template.
     * @param WC_Order $order            The order object.
     * @return string The message with replaced placeholders.
     */
    private function replace_order_placeholders( $message_template, $order ) {
        // Check if order is valid
        if ( ! is_object( $order ) || ! $order instanceof WC_Order ) {
            return $message_template;
        }
        
        $ordered_items_list = '';
        
        // Safely get order items
        if ( method_exists( $order, 'get_items' ) ) {
            $items = $order->get_items();
            
            if ( is_array( $items ) || is_object( $items ) ) {
                foreach ( $items as $item ) {
                    if ( is_object( $item ) ) {
                        // Get item name safely
                        $item_name = '';
                        if ( method_exists( $item, 'get_name' ) ) {
                            $item_name = $item->get_name();
                        } elseif ( method_exists( $item, 'get_data' ) ) {
                            $data = $item->get_data();
                            if ( isset( $data['name'] ) ) {
                                $item_name = $data['name'];
                            }
                        }
                        
                        // Get quantity safely
                        $item_quantity = 1;
                        if ( method_exists( $item, 'get_quantity' ) ) {
                            $item_quantity = $item->get_quantity();
                        } elseif ( method_exists( $item, 'get_data' ) ) {
                            $data = $item->get_data();
                            if ( isset( $data['quantity'] ) ) {
                                $item_quantity = $data['quantity'];
                            }
                        }
                        
                        // Get item total safely
                        $item_total = 0;
                        try {
                            // First try to get data using get_data which is more commonly available
                            if ( method_exists( $item, 'get_data' ) ) {
                                $data = $item->get_data();
                                if ( isset( $data['total'] ) && is_numeric( $data['total'] ) ) {
                                    $item_total = $data['total'];
                                } elseif ( isset( $data['subtotal'] ) && is_numeric( $data['subtotal'] ) ) {
                                    $item_total = $data['subtotal'];
                                }
                            }
                            // Only if that fails, try the direct method as a fallback
                            elseif ( method_exists( $item, 'get_total' ) && is_callable([$item, 'get_total']) ) {
                                $item_total = $item->get_total();
                            }
                        } catch ( Exception $e ) {
                            // Silently handle any exceptions
                        }
                        
                        $ordered_items_list .= $item_name . ' x ' . $item_quantity . ' - ' . 
                                              wp_strip_all_tags( wc_price( $item_total ) ) . "\n";
                    }
                }
            }
        }

        // Get coupon info safely
        $coupon_code = esc_html__( 'None', 'click-to-chat' );
        try {
            if ( method_exists( $order, 'get_coupon_codes' ) ) {
                $coupons = $order->get_coupon_codes();
                if ( is_array( $coupons ) && ! empty( $coupons ) ) {
                    $coupon_code = implode( ', ', $coupons );
                }
            }
        } catch ( Exception $e ) {
            // Silently handle any exceptions
        }
        
        // Get order data safely
        $order_number = '';
        $order_date = '';
        $order_total = 0;
        
        try {
            if ( method_exists( $order, 'get_order_number' ) ) {
                $order_number = $order->get_order_number();
            }
        } catch ( Exception $e ) {
            // Silently handle any exceptions
        }
        
        try {
            if ( method_exists( $order, 'get_date_created' ) && function_exists( 'wc_format_datetime' ) ) {
                $date_created = $order->get_date_created();
                if ( $date_created ) {
                    $order_date = wc_format_datetime( $date_created );
                }
            }
        } catch ( Exception $e ) {
            // Silently handle any exceptions
        }
        
        // Safely get order total with multiple fallbacks
        try {
            // First try to get data using get_data which is more commonly available
            if ( method_exists( $order, 'get_data' ) ) {
                $data = $order->get_data();
                if ( is_array( $data ) && isset( $data['total'] ) ) {
                    $order_total = $data['total'];
                }
            } 
            // Only if that fails, try the direct method as a fallback
            elseif ( method_exists( $order, 'get_total' ) && is_callable([$order, 'get_total']) ) {
                $order_total = $order->get_total();
            }
            // Direct property access as last resort
            elseif ( is_object( $order ) && isset( $order->total ) ) {
                $order_total = $order->total;
            }
        } catch ( Exception $e ) {
            // Silently handle any exceptions
        }

        // Replace placeholders
        $replacements = array(
            '{order_number}'      => $order_number,
            '{order_date}'        => $order_date,
            '{ordered_items_list}' => $ordered_items_list,
            '{coupon_code}'       => $coupon_code,
            '{order_total}'       => wp_strip_all_tags( wc_price( $order_total ) ),
        );

        foreach ( $replacements as $placeholder => $value ) {
            $message_template = str_replace( $placeholder, $value, $message_template );
        }

        return $message_template;
    }

    /**
     * Replace general placeholders in the message
     *
     * @param string $message_template The message template.
     * @return string The message with replaced placeholders.
     */
    private function replace_general_placeholders( $message_template ) {
        // Replace placeholders
        $replacements = array(
            '{current_page_url}' => esc_url( (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]" ),
        );

        foreach ( $replacements as $placeholder => $value ) {
            $message_template = str_replace( $placeholder, $value, $message_template );
        }

        return $message_template;
    }
}

// Initialize the class
new CTC_WhatsApp_Link_Generator();
