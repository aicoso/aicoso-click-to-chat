<?php
/**
 * WhatsApp link generator class.
 *
 * This class handles the generation of WhatsApp URLs with prefilled messages
 * for different contexts (single product, shop page, cart, etc.).
 *
 * @package ClickToChat
 * @since 1.0.0
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

// phpcs:disable Generic.CodeAnalysis.EmptyStatement.DetectedCatch -- Empty catch blocks are intentional for graceful degradation.

/**
 * WhatsApp Link Generator class.
 */
class CTC_Chat_WhatsApp_Link_Generator {

	/**
	 * Plugin settings.
	 *
	 * @var array
	 */
	private $ctc_chat_settings;

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->ctc_chat_settings = get_option( 'ctc_chat_settings', array() );
	}

	/**
	 * Get the appropriate WhatsApp number for the current context.
	 *
	 * @param int $product_id Optional product ID.
	 * @param int $category_id Optional category ID.
	 * @param int $page_id Optional page ID.
	 * @return string The WhatsApp number to use.
	 */
	public function ctc_chat_get_whatsapp_number( $product_id = null, $category_id = null, $page_id = null ) {
		// Return empty if no numbers configured.
		if ( empty( $this->ctc_chat_settings['ctc_chat_whatsapp_numbers'] ) ) {
			return '';
		}

		// First, check for specific assignments based on context.

		// Check for product-specific assignments.
		if ( $product_id ) {
			$product_id = absint( $product_id );

			foreach ( $this->ctc_chat_settings['ctc_chat_whatsapp_numbers'] as $ctc_chat_number_data ) {
				if ( ! empty( $ctc_chat_number_data['ctc_chat_assignments']['ctc_chat_products'] ) &&
					 is_array( $ctc_chat_number_data['ctc_chat_assignments']['ctc_chat_products'] ) ) {
					// Convert all stored IDs to integers for comparison.
					$ctc_chat_assigned_products = array_map( 'absint', $ctc_chat_number_data['ctc_chat_assignments']['ctc_chat_products'] );
					if ( in_array( $product_id, $ctc_chat_assigned_products, true ) ) {
						return $ctc_chat_number_data['ctc_chat_number'];
					}
				}
			}

			// If no direct product assignment, check product's categories.
			$ctc_chat_terms = get_the_terms( $product_id, 'product_cat' );
			if ( $ctc_chat_terms && ! is_wp_error( $ctc_chat_terms ) ) {
				foreach ( $ctc_chat_terms as $term ) {
					$ctc_chat_term_id = absint( $term->term_id );
					foreach ( $this->ctc_chat_settings['ctc_chat_whatsapp_numbers'] as $ctc_chat_number_data ) {
						if ( ! empty( $ctc_chat_number_data['ctc_chat_assignments']['ctc_chat_categories'] ) &&
							 is_array( $ctc_chat_number_data['ctc_chat_assignments']['ctc_chat_categories'] ) ) {
							// Convert all stored IDs to integers for comparison.
							$ctc_chat_assigned_categories = array_map( 'absint', $ctc_chat_number_data['ctc_chat_assignments']['ctc_chat_categories'] );
							if ( in_array( $ctc_chat_term_id, $ctc_chat_assigned_categories, true ) ) {
								return $ctc_chat_number_data['ctc_chat_number'];
							}
						}
					}
				}
			}
		}

		// Check for category-specific assignments.
		if ( $category_id ) {
			$category_id = absint( $category_id );

			foreach ( $this->ctc_chat_settings['ctc_chat_whatsapp_numbers'] as $ctc_chat_number_data ) {
				if ( ! empty( $ctc_chat_number_data['ctc_chat_assignments']['ctc_chat_categories'] ) &&
					 is_array( $ctc_chat_number_data['ctc_chat_assignments']['ctc_chat_categories'] ) ) {
					// Convert all stored IDs to integers for comparison.
					$ctc_chat_assigned_categories = array_map( 'absint', $ctc_chat_number_data['ctc_chat_assignments']['ctc_chat_categories'] );
					if ( in_array( $category_id, $ctc_chat_assigned_categories, true ) ) {
						return $ctc_chat_number_data['ctc_chat_number'];
					}
				}
			}
		}

		// Check for page-specific assignments.
		if ( $page_id ) {
			$page_id = absint( $page_id );

			foreach ( $this->ctc_chat_settings['ctc_chat_whatsapp_numbers'] as $ctc_chat_number_data ) {
				if ( ! empty( $ctc_chat_number_data['ctc_chat_assignments']['ctc_chat_pages'] ) &&
					 is_array( $ctc_chat_number_data['ctc_chat_assignments']['ctc_chat_pages'] ) ) {
					// Convert all stored IDs to integers for comparison.
					$ctc_chat_assigned_pages = array_map( 'absint', $ctc_chat_number_data['ctc_chat_assignments']['ctc_chat_pages'] );
					if ( in_array( $page_id, $ctc_chat_assigned_pages, true ) ) {
						return $ctc_chat_number_data['ctc_chat_number'];
					}
				}
			}
		}

		// No specific assignment found, now look for a default/fallback number.

		// Step 1: Check if any number is explicitly marked as default.
		foreach ( $this->ctc_chat_settings['ctc_chat_whatsapp_numbers'] as $ctc_chat_number_data ) {
			if ( ! empty( $ctc_chat_number_data['ctc_chat_is_default'] ) ) {
				return $ctc_chat_number_data['ctc_chat_number'];
			}
		}

		// Step 2: Look for a number with NO assignments (implicit default).
		foreach ( $this->ctc_chat_settings['ctc_chat_whatsapp_numbers'] as $ctc_chat_number_data ) {
			$ctc_chat_has_assignments = false;

			// Check if this number has any assignments.
			if ( isset( $ctc_chat_number_data['ctc_chat_assignments'] ) && is_array( $ctc_chat_number_data['ctc_chat_assignments'] ) ) {
				if ( ! empty( $ctc_chat_number_data['ctc_chat_assignments']['ctc_chat_products'] ) ||
					 ! empty( $ctc_chat_number_data['ctc_chat_assignments']['ctc_chat_categories'] ) ||
					 ! empty( $ctc_chat_number_data['ctc_chat_assignments']['ctc_chat_pages'] ) ) {
					$ctc_chat_has_assignments = true;
				}
			}

			// If this number has no assignments, use it as default.
			if ( ! $ctc_chat_has_assignments ) {
				return $ctc_chat_number_data['ctc_chat_number'];
			}
		}

		// Step 3: If all numbers have assignments and none match, return empty.
		// This means no WhatsApp button should be shown on unassigned pages.
		// unless a number is explicitly marked as default.
		return '';
	}

	/**
	 * Generate a WhatsApp URL for a product.
	 *
	 * @param int   $product_id  The product ID.
	 * @param array $variations  The selected variations (optional).
	 * @return string The generated WhatsApp URL.
	 */
	public function ctc_chat_get_product_url( $product_id, $variations = array() ) {
		// Check if WooCommerce is active and function exists.
		if ( ! function_exists( 'wc_get_product' ) ) {
			return '';
		}

		$ctc_chat_product = wc_get_product( $product_id );

		if ( ! $ctc_chat_product || ! is_object( $ctc_chat_product ) || ! $ctc_chat_product instanceof WC_Product ) {
			return '';
		}

		// Get the WhatsApp number to use.
		// When on shop/category pages, also check for page/category specific assignments.
		$ctc_chat_page_id = null;
		$ctc_chat_category_id = null;

		// Check if we're on shop page.
		if ( function_exists( 'is_shop' ) && is_shop() ) {
			$ctc_chat_page_id = wc_get_page_id( 'shop' );
		} elseif ( function_exists( 'is_product_category' ) && is_product_category() ) {
			// Check if we're on a category page.
			$ctc_chat_category = get_queried_object();
			if ( $ctc_chat_category && isset( $ctc_chat_category->term_id ) ) {
				$ctc_chat_category_id = $ctc_chat_category->term_id;
			}
		} elseif ( is_page() ) {
			// Check if we're on any other page.
			$ctc_chat_page_id = get_the_ID();
		}

		// Get the appropriate number considering all contexts.
		$ctc_chat_whatsapp_number = $this->ctc_chat_get_whatsapp_number( $product_id, $ctc_chat_category_id, $ctc_chat_page_id );

		if ( empty( $ctc_chat_whatsapp_number ) ) {
			return '';
		}

		// Format the WhatsApp number (remove any non-numeric characters).
		$ctc_chat_whatsapp_number = preg_replace( '/[^0-9]/', '', $ctc_chat_whatsapp_number );

		// Prepare the message.
		if ( empty( $variations ) ) {
			$ctc_chat_message = $this->ctc_chat_prepare_single_product_message( $ctc_chat_product );
		} else {
			$ctc_chat_message = $this->ctc_chat_prepare_variation_message( $ctc_chat_product, $variations );
		}

		// Build the WhatsApp URL.
		return $this->ctc_chat_build_whatsapp_url( $ctc_chat_whatsapp_number, $ctc_chat_message );
	}

	/**
	 * Generate a WhatsApp URL for the shop/category pages.
	 *
	 * @param int|null $category_id Optional category ID.
	 * @return string The generated WhatsApp URL.
	 */
	public function ctc_chat_get_shop_url( $category_id = null ) {
		// Get the current page ID if we're on shop page.
		$ctc_chat_page_id = null;
		if ( function_exists( 'is_shop' ) && is_shop() ) {
			$ctc_chat_page_id = wc_get_page_id( 'shop' );
		} elseif ( is_page() ) {
			$ctc_chat_page_id = get_the_ID();
		}

		// Get the WhatsApp number.
		$ctc_chat_whatsapp_number = $this->ctc_chat_get_whatsapp_number( null, $category_id, $ctc_chat_page_id );

		if ( empty( $ctc_chat_whatsapp_number ) ) {
			return '';
		}

		// Format the WhatsApp number.
		$ctc_chat_whatsapp_number = preg_replace( '/[^0-9]/', '', $ctc_chat_whatsapp_number );

		// Get the message template. for shop page.
		$ctc_chat_message_template = isset( $this->ctc_chat_settings['ctc_chat_message_templates']['ctc_chat_shop'] ) ?
						   $this->ctc_chat_settings['ctc_chat_message_templates']['ctc_chat_shop'] :
						   'Hi, I\'m interested in your products.';

		// Replace placeholders. if any.
		$ctc_chat_message = $ctc_chat_message_template;

		// Replace {current_page_url} placeholder.
		if ( strpos( $ctc_chat_message, '{current_page_url}' ) !== false ) {
			$ctc_chat_current_url = ( isset( $_SERVER['HTTPS'] ) && 'on' === $_SERVER['HTTPS'] ? 'https' : 'http' ) . '://';
			$ctc_chat_current_url .= isset( $_SERVER['HTTP_HOST'] ) ? sanitize_text_field( wp_unslash( $_SERVER['HTTP_HOST'] ) ) : '';
			$ctc_chat_current_url .= isset( $_SERVER['REQUEST_URI'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REQUEST_URI'] ) ) : '';
			$ctc_chat_message = str_replace( '{current_page_url}', $ctc_chat_current_url, $ctc_chat_message );
		}

		// Replace {category_name} placeholder if we have a category.
		if ( $category_id && strpos( $ctc_chat_message, '{category_name}' ) !== false ) {
			$ctc_chat_category = get_term( $category_id, 'product_cat' );
			if ( $ctc_chat_category && ! is_wp_error( $ctc_chat_category ) ) {
				$ctc_chat_message = str_replace( '{category_name}', $ctc_chat_category->name, $ctc_chat_message );
			}
		}

		// Build and return the WhatsApp URL.
		return $this->ctc_chat_build_whatsapp_url( $ctc_chat_whatsapp_number, $ctc_chat_message );
	}

	/**
	 * Generate a WhatsApp URL for the cart/checkout.
	 *
	 * @return string The generated WhatsApp URL.
	 */
	public function ctc_chat_get_cart_url() {
		// Check if WooCommerce is active.
		if ( ! function_exists( 'WC' ) ) {
			return '';
		}

		// Check if WC() is available.
		try {
			$wc_instance = WC();
			if ( ! $wc_instance ) {
				return '';
			}
		} catch ( Exception $e ) {
			return '';
		}

		// Check if cart is available.
		if ( ! $wc_instance->cart || ! is_object( $wc_instance->cart ) ) {
			return '';
		}

		// Check if we're on a specific page (cart page might have a page ID).
		$ctc_chat_page_id = null;
		if ( is_page() ) {
			$ctc_chat_page_id = get_the_ID();
		}

		// Get the WhatsApp number (will check page assignments if on a page).
		$ctc_chat_whatsapp_number = $this->ctc_chat_get_whatsapp_number( null, null, $ctc_chat_page_id );

		if ( empty( $ctc_chat_whatsapp_number ) ) {
			return '';
		}

		// Format the WhatsApp number.
		$ctc_chat_whatsapp_number = preg_replace( '/[^0-9]/', '', $ctc_chat_whatsapp_number );

		// Get the message template - using 'cart_checkout' as per the settings.
		$ctc_chat_message_template = isset( $this->ctc_chat_settings['ctc_chat_message_templates']['ctc_chat_cart_checkout'] ) ?
						   $this->ctc_chat_settings['ctc_chat_message_templates']['ctc_chat_cart_checkout'] : '';

		// If no template, create a default cart message.
		if ( empty( $ctc_chat_message_template ) ) {
			$ctc_chat_message_template = esc_html__( 'Hello! I need help with my cart on your website.', 'aicoso-click-to-chat' );
		}

		// Replace placeholders.
		$ctc_chat_message = $this->ctc_chat_replace_cart_placeholders( $ctc_chat_message_template );
		$ctc_chat_message = $this->ctc_chat_replace_general_placeholders( $ctc_chat_message );

		// Build and return the WhatsApp URL.
		return $this->ctc_chat_build_whatsapp_url( $ctc_chat_whatsapp_number, $ctc_chat_message );
	}

	/**
	 * Generate a WhatsApp URL for the thank you page.
	 *
	 * @param int $order_id The order ID.
	 * @return string The generated WhatsApp URL.
	 */
	public function ctc_chat_get_thankyou_url( $order_id ) {
		// Check if WooCommerce is active.
		if ( ! function_exists( 'wc_get_order' ) ) {
			return '';
		}

		// Get the order.
		$ctc_chat_order = wc_get_order( $order_id );

		// Check if order is valid.
		if ( ! $ctc_chat_order || ! is_object( $ctc_chat_order ) || ! $ctc_chat_order instanceof WC_Order ) {
			return '';
		}

		// Check if we're on a specific page (thank you page might have a page ID).
		$ctc_chat_page_id = null;
		if ( is_page() ) {
			$ctc_chat_page_id = get_the_ID();
		}

		// Get the WhatsApp number (will check page assignments if on a page).
		$ctc_chat_whatsapp_number = $this->ctc_chat_get_whatsapp_number( null, null, $ctc_chat_page_id );

		if ( empty( $ctc_chat_whatsapp_number ) ) {
			return '';
		}

		// Format the WhatsApp number.
		$ctc_chat_whatsapp_number = preg_replace( '/[^0-9]/', '', $ctc_chat_whatsapp_number );

		// Get the message template.
		$ctc_chat_message_template = isset( $this->ctc_chat_settings['ctc_chat_message_templates']['ctc_chat_thank_you'] ) ?
						   $this->ctc_chat_settings['ctc_chat_message_templates']['ctc_chat_thank_you'] : '';

		// Replace placeholders.
		$ctc_chat_message = $this->ctc_chat_replace_order_placeholders( $ctc_chat_message_template, $ctc_chat_order );

		// Build and return the WhatsApp URL.
		return $this->ctc_chat_build_whatsapp_url( $ctc_chat_whatsapp_number, $ctc_chat_message );
	}

	/**
	 * Generate a WhatsApp URL for the floating button.
	 *
	 * @return string The generated WhatsApp URL.
	 */
	public function ctc_chat_get_floating_url() {
		// Get current context for number selection.
		$ctc_chat_page_id = null;
		$ctc_chat_category_id = null;
		$ctc_chat_product_id = null;

		// Check if we're on a product page.
		if ( function_exists( 'is_product' ) && is_product() ) {
			global $product;
			if ( $product && is_object( $product ) ) {
				$ctc_chat_product_id = $product->get_id();
			}
			// Check if we're on shop page.
		} elseif ( function_exists( 'is_shop' ) && is_shop() ) {
			$ctc_chat_page_id = wc_get_page_id( 'shop' );
			// Check if we're on a category page.
		} elseif ( function_exists( 'is_product_category' ) && is_product_category() ) {
			$ctc_chat_category = get_queried_object();
			if ( $ctc_chat_category && isset( $ctc_chat_category->term_id ) ) {
				$ctc_chat_category_id = $ctc_chat_category->term_id;
			}
			// Check if we're on cart page.
		} elseif ( function_exists( 'is_cart' ) && is_cart() ) {
			$ctc_chat_page_id = wc_get_page_id( 'cart' );
			// Check if we're on checkout page.
		} elseif ( function_exists( 'is_checkout' ) && is_checkout() ) {
			$ctc_chat_page_id = wc_get_page_id( 'checkout' );
			// Check if we're on any other page.
		} elseif ( is_page() ) {
			$ctc_chat_page_id = get_the_ID();
		}

		// Get the WhatsApp number considering all contexts.
		$ctc_chat_whatsapp_number = $this->ctc_chat_get_whatsapp_number( $ctc_chat_product_id, $ctc_chat_category_id, $ctc_chat_page_id );

		if ( empty( $ctc_chat_whatsapp_number ) ) {
			return '';
		}

		// Format the WhatsApp number.
		$ctc_chat_whatsapp_number = preg_replace( '/[^0-9]/', '', $ctc_chat_whatsapp_number );

		// Get the message template.
		$ctc_chat_message_template = isset( $this->ctc_chat_settings['ctc_chat_message_templates']['ctc_chat_floating'] ) ?
						   $this->ctc_chat_settings['ctc_chat_message_templates']['ctc_chat_floating'] : '';

		// Replace placeholders.
		$ctc_chat_message = $this->ctc_chat_replace_general_placeholders( $ctc_chat_message_template );

		// Build and return the WhatsApp URL.
		return $this->ctc_chat_build_whatsapp_url( $ctc_chat_whatsapp_number, $ctc_chat_message );
	}

	/**
	 * Build a WhatsApp URL with the provided number and message.
	 *
	 * @param string $number  The WhatsApp number.
	 * @param string $message The message to send.
	 * @return string The complete WhatsApp URL.
	 */
	private function ctc_chat_build_whatsapp_url( $number, $message ) {
		// Decode HTML entities in the message.
		$ctc_chat_decoded_message = html_entity_decode( $message, ENT_QUOTES | ENT_HTML5, 'UTF-8' );

		// URL encode the message (rawurlencode will properly handle newlines as %0A).
		$ctc_chat_encoded_message = rawurlencode( $ctc_chat_decoded_message );

		// Build the WhatsApp URL.
		return 'https://wa.me/' . $number . '?text=' . $ctc_chat_encoded_message;
	}

	/**
	 * Prepare message for a single product.
	 *
	 * @param WC_Product $product The product object.
	 * @return string The prepared message.
	 */
	private function ctc_chat_prepare_single_product_message( $product ) {
		// Check if product is valid.
		if ( ! is_object( $product ) || ! $product instanceof WC_Product ) {
			return '';
		}

		// Always use single_product template for individual products.
		// This ensures consistency when clicking on product-specific WhatsApp buttons.
		$ctc_chat_template_key = 'ctc_chat_single_product';

		// Get the message template. with fallback.
		$ctc_chat_message_template = '';
		if ( isset( $this->ctc_chat_settings['ctc_chat_message_templates'][ $ctc_chat_template_key ] ) ) {
			$ctc_chat_message_template = $this->ctc_chat_settings['ctc_chat_message_templates'][ $ctc_chat_template_key ];
		}

		// If no template is set, use default template.
		if ( empty( $ctc_chat_message_template ) ) {
			$ctc_chat_message_template = "Hello! I'm interested in the product: *{product_name}*\nPrice: {price}\nURL: {product_url}\n\nDo you have this item in stock? I'd like to get more information.";
		}

		// Get product data safely.
		$ctc_chat_product_name = '';
		$ctc_chat_product_price = 0;
		$ctc_chat_product_url = '';

		if ( method_exists( $product, 'get_name' ) ) {
			$ctc_chat_product_name = $product->get_name();
		}

		if ( method_exists( $product, 'get_price' ) ) {
			$ctc_chat_product_price = $product->get_price();
		}

		if ( method_exists( $product, 'get_id' ) && function_exists( 'get_permalink' ) ) {
			$ctc_chat_product_url = get_permalink( $product->get_id() );
		}

		// Build replacements for single product template.
		$ctc_chat_replacements = array(
			'{product_name}' => $ctc_chat_product_name,
			'{price}'        => wp_strip_all_tags( wc_price( $ctc_chat_product_price ) ),
			'{product_url}'  => $ctc_chat_product_url,
		);

		foreach ( $ctc_chat_replacements as $ctc_chat_placeholder => $ctc_chat_value ) {
			$ctc_chat_message_template = str_replace( $ctc_chat_placeholder, $ctc_chat_value, $ctc_chat_message_template );
		}

		return $ctc_chat_message_template;
	}

	/**
	 * Prepare message for a product with variations.
	 *
	 * @param WC_Product $product    The product object.
	 * @param array      $variations The selected variations.
	 * @return string The prepared message.
	 */
	private function ctc_chat_prepare_variation_message( $product, $variations ) {
		// Check if product is valid.
		if ( ! is_object( $product ) || ! $product instanceof WC_Product ) {
			return '';
		}

		// Check if variations is an array.
		if ( ! is_array( $variations ) ) {
			$variations = array();
		}

		// Get the message template.
		$ctc_chat_message_template = isset( $this->ctc_chat_settings['ctc_chat_message_templates']['ctc_chat_variations'] ) ?
						   $this->ctc_chat_settings['ctc_chat_message_templates']['ctc_chat_variations'] : '';

		// Get variation details safely.
		$ctc_chat_variation_details = '';
		if ( function_exists( 'wc_attribute_label' ) ) {
			foreach ( $variations as $attribute => $value ) {
				if ( is_string( $attribute ) && is_string( $value ) ) {
					$attribute_label = wc_attribute_label( str_replace( 'attribute_', '', $attribute ), $product );
					$ctc_chat_variation_details .= $attribute_label . ': ' . $value . ', ';
				}
			}
			$ctc_chat_variation_details = rtrim( $ctc_chat_variation_details, ', ' );
		}

		// Get product data safely.
		$ctc_chat_product_name = '';
		$ctc_chat_product_price = 0;
		$ctc_chat_product_url = '';
		$ctc_chat_variation_price = 0;

		if ( method_exists( $product, 'get_name' ) ) {
			$ctc_chat_product_name = $product->get_name();
		}

		if ( method_exists( $product, 'get_price' ) ) {
			$ctc_chat_product_price = $product->get_price();
			$ctc_chat_variation_price = $ctc_chat_product_price;
		}

		if ( method_exists( $product, 'get_id' ) && function_exists( 'get_permalink' ) ) {
			$ctc_chat_product_url = get_permalink( $product->get_id() );
		}

		// Try to get the variation price if possible.
		if ( method_exists( $product, 'is_type' ) && $product->is_type( 'variable' ) && function_exists( 'wc_get_product' ) ) {
			// Use the recommended method to find matching variation.
			$ctc_chat_variation_id = 0;

			// Find matching variation using WooCommerce's data store API.
			if ( function_exists( 'WC' ) ) {
				try {
					// Use the WC_Product_Data_Store_CPT approach which is the modern method.
					if ( class_exists( 'WC_Product_Data_Store_CPT' ) ) {
						$ctc_chat_data_store = new WC_Product_Data_Store_CPT();
						$ctc_chat_variation_id = $ctc_chat_data_store->find_matching_product_variation( $product, $variations );
					} elseif ( class_exists( 'WC_Data_Store' ) ) {
						// Alternative approach using WC_Data_Store.
						$ctc_chat_data_store = WC_Data_Store::load( 'product' );
						if ( is_object( $ctc_chat_data_store ) && method_exists( $ctc_chat_data_store, 'find_matching_product_variation' ) ) {
							$ctc_chat_variation_id = $ctc_chat_data_store->find_matching_product_variation( $product, $variations );
						}
					}
				} catch ( Exception $e ) {
					// Silently fail - graceful degradation for variation detection.
				}
			}

			if ( $ctc_chat_variation_id ) {
				$ctc_chat_variation = wc_get_product( $ctc_chat_variation_id );
				if ( $ctc_chat_variation && is_object( $ctc_chat_variation ) && method_exists( $ctc_chat_variation, 'get_price' ) ) {
					$ctc_chat_variation_price = $ctc_chat_variation->get_price();
				}
			}
		}

		// Format price with proper currency symbol.
		if ( function_exists( 'wc_price' ) ) {
			$ctc_chat_formatted_price = html_entity_decode( wp_strip_all_tags( wc_price( $ctc_chat_variation_price ) ), ENT_QUOTES | ENT_HTML5, 'UTF-8' );
		} elseif ( function_exists( 'get_woocommerce_currency_symbol' ) ) {
			$ctc_chat_formatted_price = get_woocommerce_currency_symbol() . $ctc_chat_variation_price;
		} else {
			$ctc_chat_formatted_price = '$' . $ctc_chat_variation_price;
		}

		// Replace placeholders.
		$ctc_chat_replacements = array(
			'{product_name}'      => $ctc_chat_product_name,
			'{variation_details}' => $ctc_chat_variation_details,
			'{variation_price}'   => $ctc_chat_formatted_price,
			'{product_url}'       => $ctc_chat_product_url,
		);

		foreach ( $ctc_chat_replacements as $ctc_chat_placeholder => $ctc_chat_value ) {
			$ctc_chat_message_template = str_replace( $ctc_chat_placeholder, $ctc_chat_value, $ctc_chat_message_template );
		}

		return $ctc_chat_message_template;
	}


	/**
	 * Replace cart placeholders in the message.
	 *
	 * @param string $message_template The message template.
	 * @return string The message with replaced placeholders.
	 */
	private function ctc_chat_replace_cart_placeholders( $message_template ) {
		// Check if WooCommerce and cart are available.
		if ( ! function_exists( 'WC' ) || ! WC()->cart || ! is_object( WC()->cart ) ) {
			return $message_template;
		}

		$cart_items_list = '';

		// Get cart items safely.
		$cart_items = WC()->cart->get_cart();
		if ( is_array( $cart_items ) ) {
			foreach ( $cart_items as $cart_item ) {
				if ( isset( $cart_item['data'] ) && is_object( $cart_item['data'] ) && $cart_item['data'] instanceof WC_Product ) {
					$product = $cart_item['data'];

					// Get product name.
					$item_name = '';
					if ( method_exists( $product, 'get_name' ) ) {
						$item_name = $product->get_name();
					}

					// Get quantity.
					$item_quantity = isset( $cart_item['quantity'] ) ? absint( $cart_item['quantity'] ) : 1;

					// Get line total.
					$item_total = 0;
					if ( isset( $cart_item['line_total'] ) ) {
						$item_total = $cart_item['line_total'];
					}

					// Add variation details if available.
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

		// Get cart totals safely.
		$cart_subtotal = 0;
		$tax_amount = 0;
		$shipping_method = esc_html__( 'Not calculated', 'aicoso-click-to-chat' );
		$shipping_cost = '';
		$cart_total = 0;

		if ( method_exists( WC()->cart, 'get_subtotal' ) ) {
			$cart_subtotal = WC()->cart->get_subtotal();
		}

		// Get tax amount.
		if ( method_exists( WC()->cart, 'get_taxes_total' ) ) {
			$tax_amount = WC()->cart->get_taxes_total();
		}

		// Try to get shipping info.
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

		// Replace placeholders.
		$replacements = array(
			'{cart_items_list}' => $cart_items_list,
			'{cart_subtotal}'   => wp_strip_all_tags( wc_price( $cart_subtotal ) ),
			'{tax_amount}'      => wp_strip_all_tags( wc_price( $tax_amount ) ),
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
	 * Replace order placeholders in the message.
	 *
	 * @param string   $message_template The message template.
	 * @param WC_Order $order            The order object.
	 * @return string The message with replaced placeholders.
	 */
	private function ctc_chat_replace_order_placeholders( $message_template, $order ) {
		// Check if order is valid.
		if ( ! is_object( $order ) || ! $order instanceof WC_Order ) {
			return $message_template;
		}

		$ordered_items_list = '';

		// Safely get order items.
		if ( method_exists( $order, 'get_items' ) ) {
			$items = $order->get_items();

			if ( is_array( $items ) || is_object( $items ) ) {
				foreach ( $items as $item ) {
					if ( is_object( $item ) ) {
						// Get item name safely.
						$item_name = '';
						if ( method_exists( $item, 'get_name' ) ) {
							$item_name = $item->get_name();
						} elseif ( method_exists( $item, 'get_data' ) ) {
							$data = $item->get_data();
							if ( isset( $data['name'] ) ) {
								$item_name = $data['name'];
							}
						}

						// Get quantity safely.
						$item_quantity = 1;
						if ( method_exists( $item, 'get_quantity' ) ) {
							$item_quantity = $item->get_quantity();
						} elseif ( method_exists( $item, 'get_data' ) ) {
							$data = $item->get_data();
							if ( isset( $data['quantity'] ) ) {
								$item_quantity = $data['quantity'];
							}
						}

						// Get item total safely.
						$item_total = 0;
						try {
							// First try to get data using get_data which is more commonly available.
							if ( method_exists( $item, 'get_data' ) ) {
								$data = $item->get_data();
								if ( isset( $data['total'] ) && is_numeric( $data['total'] ) ) {
									$item_total = $data['total'];
								} elseif ( isset( $data['subtotal'] ) && is_numeric( $data['subtotal'] ) ) {
									$item_total = $data['subtotal'];
								}
								// Only if that fails, try the direct method as a fallback.
							} elseif ( method_exists( $item, 'get_total' ) && is_callable( array( $item, 'get_total' ) ) ) {
								$item_total = $item->get_total();
							}
						} catch ( Exception $e ) {
							// Silently handle any exceptions.
						}

						$ordered_items_list .= $item_name . ' x ' . $item_quantity . ' - ' .
											  wp_strip_all_tags( wc_price( $item_total ) ) . "\n";
					}
				}
			}
		}

		// Get coupon info safely.
		$coupon_code = esc_html__( 'None', 'aicoso-click-to-chat' );
		try {
			if ( method_exists( $order, 'get_coupon_codes' ) ) {
				$coupons = $order->get_coupon_codes();
				if ( is_array( $coupons ) && ! empty( $coupons ) ) {
					$coupon_code = implode( ', ', $coupons );
				}
			}
		} catch ( Exception $e ) {
			// Silently handle any exceptions.
		}

		// Get order data safely.
		$order_number = '';
		$order_date = '';
		$order_total = 0;

		try {
			if ( method_exists( $order, 'get_order_number' ) ) {
				$order_number = $order->get_order_number();
			}
		} catch ( Exception $e ) {
			// Silently handle any exceptions.
		}

		try {
			if ( method_exists( $order, 'get_date_created' ) && function_exists( 'wc_format_datetime' ) ) {
				$date_created = $order->get_date_created();
				if ( $date_created ) {
					$order_date = wc_format_datetime( $date_created );
				}
			}
		} catch ( Exception $e ) {
			// Silently handle any exceptions.
		}

		// Safely get order total with multiple fallbacks.
		try {
			// First try to get data using get_data which is more commonly available.
			if ( method_exists( $order, 'get_data' ) ) {
				$data = $order->get_data();
				if ( is_array( $data ) && isset( $data['total'] ) ) {
					$order_total = $data['total'];
				}
				// Only if that fails, try the direct method as a fallback.
			} elseif ( method_exists( $order, 'get_total' ) && is_callable( array( $order, 'get_total' ) ) ) {
				$order_total = $order->get_total();
				// Direct property access as last resort.
			} elseif ( is_object( $order ) && isset( $order->total ) ) {
				$order_total = $order->total;
			}
		} catch ( Exception $e ) {
			// Silently handle any exceptions.
		}

		// Replace placeholders.
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
	 * Replace general placeholders in the message.
	 *
	 * @param string $message_template The message template.
	 * @return string The message with replaced placeholders.
	 */
	private function ctc_chat_replace_general_placeholders( $message_template ) {
		// Replace placeholders.
		$ctc_chat_current_url = ( isset( $_SERVER['HTTPS'] ) && 'on' === $_SERVER['HTTPS'] ? 'https' : 'http' ) . '://';
		$ctc_chat_current_url .= isset( $_SERVER['HTTP_HOST'] ) ? sanitize_text_field( wp_unslash( $_SERVER['HTTP_HOST'] ) ) : '';
		$ctc_chat_current_url .= isset( $_SERVER['REQUEST_URI'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REQUEST_URI'] ) ) : '';

		$ctc_chat_replacements = array(
			'{current_page_url}' => esc_url( $ctc_chat_current_url ),
		);

		foreach ( $ctc_chat_replacements as $ctc_chat_placeholder => $ctc_chat_value ) {
			$message_template = str_replace( $ctc_chat_placeholder, $ctc_chat_value, $message_template );
		}

		return $message_template;
	}

	/**
	 * Generate a WhatsApp URL for the checkout page.
	 *
	 * @return string The generated WhatsApp URL.
	 */
	public function ctc_chat_get_checkout_url() {
		// Check if WooCommerce is active.
		if ( ! function_exists( 'WC' ) ) {
			return '';
		}

		// Check if cart is available (for checkout, we still need cart contents).
		if ( ! WC()->cart || ! is_object( WC()->cart ) ) {
			return '';
		}

		// Check if we're on a specific page (checkout page might have a page ID).
		$ctc_chat_page_id = null;
		if ( is_page() ) {
			$ctc_chat_page_id = get_the_ID();
		}

		// Get the WhatsApp number (will check page assignments if on a page).
		$ctc_chat_whatsapp_number = $this->ctc_chat_get_whatsapp_number( null, null, $ctc_chat_page_id );

		if ( empty( $ctc_chat_whatsapp_number ) ) {
			return '';
		}

		// Format the WhatsApp number.
		$ctc_chat_whatsapp_number = preg_replace( '/[^0-9]/', '', $ctc_chat_whatsapp_number );

		// Get the message template - using same template as cart for checkout.
		$ctc_chat_message_template = isset( $this->ctc_chat_settings['ctc_chat_message_templates']['ctc_chat_cart_checkout'] ) ?
						   $this->ctc_chat_settings['ctc_chat_message_templates']['ctc_chat_cart_checkout'] : '';

		// If no template, create a default checkout message.
		if ( empty( $ctc_chat_message_template ) ) {
			$ctc_chat_message_template = esc_html__( "Hello! I'd like to complete my purchase of:\n{cart_items_list}\n---------------------\nSubtotal: {cart_subtotal}\nTax: {tax_amount}\nShipping: {shipping_method} - {shipping_cost}\nTotal: {cart_total}\n\nI have a few questions before finalizing my order.", 'aicoso-click-to-chat' );
		}

		// Replace placeholders.
		$ctc_chat_message = $this->ctc_chat_replace_cart_placeholders( $ctc_chat_message_template );
		$ctc_chat_message = $this->ctc_chat_replace_general_placeholders( $ctc_chat_message );

		// Build and return the WhatsApp URL.
		return $this->ctc_chat_build_whatsapp_url( $ctc_chat_whatsapp_number, $ctc_chat_message );
	}
}

// Initialize the class.
new CTC_Chat_WhatsApp_Link_Generator();
