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
 *
 * @since 1.0.0
 * @package CTC_Chat
 */
class CTC_Chat_Public {

	/**
	 * Plugin settings
	 *
	 * @since 1.0.0
	 * @var array
	 */
	private $ctc_chat_settings;

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->ctc_chat_settings = get_option( 'ctc_chat_settings', array() );

		// Initialize hooks.
		$this->ctc_chat_init_hooks();
	}

	/**
	 * Initialize hooks
	 */
	private function ctc_chat_init_hooks() {
		// Handle advanced options for hiding WooCommerce buttons FIRST (before other hooks).
		add_action( 'init', array( $this, 'ctc_chat_handle_advanced_options' ), 999 );

		// Enqueue scripts and styles.
		add_action( 'wp_enqueue_scripts', array( $this, 'ctc_chat_enqueue_assets' ) );

		// Add JavaScript for variable products.
		add_action( 'woocommerce_after_single_product', array( $this, 'ctc_chat_add_variable_product_script' ) );

		// Enqueue block-specific scripts for cart and checkout.
		add_action( 'wp_enqueue_scripts', array( $this, 'ctc_chat_enqueue_block_scripts' ) );

		// AJAX handlers for variation URLs.
		add_action( 'wp_ajax_ctc_chat_get_variation_url', array( $this, 'ctc_chat_ajax_get_variation_url' ) );
		add_action( 'wp_ajax_nopriv_ctc_chat_get_variation_url', array( $this, 'ctc_chat_ajax_get_variation_url' ) );
	}

	/**
	 * Enqueue public scripts and styles
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function ctc_chat_enqueue_assets() {
		// Only enqueue assets when needed.
		if ( ! $this->ctc_chat_should_load_assets() ) {
			return;
		}

		// Register and enqueue CSS.
		wp_enqueue_style(
			'ctc-chat-public-styles',
			CTC_CHAT_PLUGIN_URL . 'public/css/public.css',
			array(),
			CTC_CHAT_VERSION
		);

		// Enqueue additional layout fixes CSS with higher priority.
		wp_enqueue_style(
			'ctc-chat-layout-fixes',
			CTC_CHAT_PLUGIN_URL . 'public/css/button-layout-fixes.css',
			array( 'ctc-chat-public-styles' ),
			CTC_CHAT_VERSION
		);

		// Register and enqueue JavaScript.
		wp_enqueue_script(
			'ctc-chat-public-script',
			CTC_CHAT_PLUGIN_URL . 'public/js/public.js',
			array( 'jquery' ),
			CTC_CHAT_VERSION,
			true
		);

		// Localize script with data.
		$ctc_chat_localize_data = array(
			'ajaxurl' => admin_url( 'admin-ajax.php' ),
			'nonce'   => wp_create_nonce( 'ctc_chat_public_nonce' ),
		);

		wp_localize_script( 'ctc-chat-public-script', 'ctc_chat_public', $ctc_chat_localize_data );
	}

	/**
	 * Check if assets should be loaded
	 *
	 * @return bool True if assets should be loaded, false otherwise.
	 */
	private function ctc_chat_should_load_assets() {
		// Always load if floating button is enabled.
		if ( isset( $this->ctc_chat_settings['ctc_chat_floating_button']['ctc_chat_enabled'] ) && $this->ctc_chat_settings['ctc_chat_floating_button']['ctc_chat_enabled'] ) {
			return true;
		}

		// Load on shop pages if enabled.
		if ( ( is_shop() || is_product_category() || is_product_tag() ) &&
			 isset( $this->ctc_chat_settings['ctc_chat_shop_page']['ctc_chat_enabled'] ) &&
			 $this->ctc_chat_settings['ctc_chat_shop_page']['ctc_chat_enabled'] ) {
			return true;
		}

		// Load on single product pages if enabled.
		if ( is_product() &&
			 isset( $this->ctc_chat_settings['ctc_chat_single_product']['ctc_chat_enabled'] ) &&
			 $this->ctc_chat_settings['ctc_chat_single_product']['ctc_chat_enabled'] ) {
			return true;
		}

		// Load on cart page if enabled.
		if ( is_cart() &&
			 isset( $this->ctc_chat_settings['ctc_chat_cart_page']['ctc_chat_enabled'] ) &&
			 $this->ctc_chat_settings['ctc_chat_cart_page']['ctc_chat_enabled'] ) {
			return true;
		}

		// Load on checkout page if enabled.
		if ( is_checkout() && ! is_wc_endpoint_url( 'order-received' ) &&
			 isset( $this->ctc_chat_settings['ctc_chat_checkout_page']['ctc_chat_enabled'] ) &&
			 $this->ctc_chat_settings['ctc_chat_checkout_page']['ctc_chat_enabled'] ) {
			return true;
		}

		// Load on thank you page if enabled.
		if ( is_wc_endpoint_url( 'order-received' ) &&
			 isset( $this->ctc_chat_settings['ctc_chat_thankyou_page']['ctc_chat_enabled'] ) &&
			 $this->ctc_chat_settings['ctc_chat_thankyou_page']['ctc_chat_enabled'] ) {
			return true;
		}

		// Check if any shortcodes are used.
		global $post;
		if ( $post && has_shortcode( $post->post_content, 'ctc_chat_button' ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Add JavaScript for variable products
	 */
	public function ctc_chat_add_variable_product_script() {
		global $product;

		// Only add for variable products.
		if ( ! is_product() || ! $product || ! $product->is_type( 'variable' ) ) {
			return;
		}

		// Only add if single product button is enabled.
		if ( ! isset( $this->ctc_chat_settings['ctc_chat_single_product']['ctc_chat_enabled'] ) || ! $this->ctc_chat_settings['ctc_chat_single_product']['ctc_chat_enabled'] ) {
			return;
		}

		// Prepare the WhatsApp URL base with error handling.
		try {
			$ctc_chat_link_generator = new CTC_Chat_WhatsApp_Link_Generator();
			$ctc_chat_whatsapp_number = $ctc_chat_link_generator->ctc_chat_get_whatsapp_number( $product->get_id() );
		} catch ( Exception $e ) {
			return;
		}

		// If no number, return.
		if ( empty( $ctc_chat_whatsapp_number ) ) {
			return;
		}

		// Format the WhatsApp number.
		$ctc_chat_whatsapp_number = preg_replace( '/[^0-9]/', '', $ctc_chat_whatsapp_number );

		// Get the message template.
		$ctc_chat_message_template = isset( $this->ctc_chat_settings['ctc_chat_message_templates']['ctc_chat_variations'] ) ?
						   $this->ctc_chat_settings['ctc_chat_message_templates']['ctc_chat_variations'] : '';

		// Check for product-specific custom message.
		$ctc_chat_custom_message = get_post_meta( $product->get_id(), '_ctc_chat_custom_message', true );
		if ( ! empty( $ctc_chat_custom_message ) ) {
			$ctc_chat_message_template = $ctc_chat_custom_message;
		}

		// Prepare the JavaScript.
		$ctc_chat_script = "
			(function($) {
				'use strict';

				$(document).ready(function() {
					// Get references to elements
					var \$variationForm = $('.variations_form');
					var \$whatsappButton = $('.ctc-whatsapp-button');

					// If either element is missing, return
					if (!\$variationForm.length || !\$whatsappButton.length) {
						return;
					}

					// Listen for variation changes
					\$variationForm.on('show_variation', function(event, variation) {
						// Store the selected variation details
						var variationDetails = [];
						$('.variations select').each(function() {
							var \$select = $(this);
							var attributeName = \$select.data('attribute_name') || \$select.attr('name');
							var attributeValue = \$select.val();

							if (attributeValue) {
								// Get the attribute label
								var label = attributeName.replace('attribute_', '');
								label = label.replace('pa_', '');
								label = label.replace(/-/g, ' ');
								label = label.charAt(0).toUpperCase() + label.slice(1);

								// Get the attribute value label
								var valueLabel = '';
								var \$selectedOption = \$select.find('option:selected');

								if (\$selectedOption.length) {
									valueLabel = \$selectedOption.text();
								} else {
									valueLabel = attributeValue;
								}

								variationDetails.push(label + ': ' + valueLabel);
							}
						});

						// Create message with variation details
						var baseUrl = 'https://wa.me/" . esc_js( $ctc_chat_whatsapp_number ) . "?text=';
						var message = '" . esc_js( $ctc_chat_message_template ) . "';

						// Replace placeholders in message
						message = message.replace('{product_name}', '" . esc_js( $product->get_name() ) . "');
						message = message.replace('{variation_details}', variationDetails.join(', '));
						message = message.replace('{variation_price}', variation.display_price.toFixed(2));
						message = message.replace('{product_url}', '" . esc_js( get_permalink( $product->get_id() ) ) . "');

						// Update the WhatsApp button URL
						\$whatsappButton.attr('href', baseUrl + encodeURIComponent(message));
					});
				});
			})(jQuery);
		";

		wp_add_inline_script( 'ctc-chat-public-script', $ctc_chat_script );
	}

	/**
	 * Enqueue scripts for WooCommerce block-based cart and checkout
	 */
	public function ctc_chat_enqueue_block_scripts() {
		// Only load on cart or checkout pages.
		if ( ! is_cart() && ! is_checkout() ) {
			return;
		}

		// Check if plugin is enabled globally.
		$ctc_chat_plugin_enabled = isset( $this->ctc_chat_settings['ctc_chat_plugin_enabled'] ) ? $this->ctc_chat_settings['ctc_chat_plugin_enabled'] : true;
		if ( ! $ctc_chat_plugin_enabled ) {
			return;
		}

		// Check if cart or checkout is enabled.
		$ctc_chat_cart_enabled = isset( $this->ctc_chat_settings['ctc_chat_cart_page']['ctc_chat_enabled'] ) && $this->ctc_chat_settings['ctc_chat_cart_page']['ctc_chat_enabled'];
		$ctc_chat_checkout_enabled = isset( $this->ctc_chat_settings['ctc_chat_checkout_page']['ctc_chat_enabled'] ) && $this->ctc_chat_settings['ctc_chat_checkout_page']['ctc_chat_enabled'];

		if ( ! $ctc_chat_cart_enabled && ! $ctc_chat_checkout_enabled ) {
			return;
		}

		// Check if current page is excluded.
		if ( $this->ctc_chat_is_page_excluded() ) {
			return;
		}

		// Enqueue the block support script.
		wp_enqueue_script(
			'ctc-chat-cart-checkout-blocks',
			CTC_CHAT_PLUGIN_URL . 'public/js/cart-checkout-blocks.js',
			array(),
			CTC_CHAT_VERSION,
			true
		);

		// Get WhatsApp URL with error handling.
		$ctc_chat_whatsapp_url = '';

		try {
			$ctc_chat_link_generator = new CTC_Chat_WhatsApp_Link_Generator();

			if ( is_cart() ) {
				$ctc_chat_whatsapp_url = $ctc_chat_link_generator->ctc_chat_get_cart_url();
			} elseif ( is_checkout() ) {
				$ctc_chat_whatsapp_url = $ctc_chat_link_generator->ctc_chat_get_checkout_url(); // Uses checkout URL.
			}
		} catch ( Exception $e ) {
			$ctc_chat_whatsapp_url = '';
		}

		// If no URL generated, create a basic one.
		if ( empty( $ctc_chat_whatsapp_url ) ) {
			$ctc_chat_whatsapp_number = isset( $this->ctc_chat_settings['ctc_chat_whatsapp_numbers'][0]['ctc_chat_number'] ) ?
							  $this->ctc_chat_settings['ctc_chat_whatsapp_numbers'][0]['ctc_chat_number'] : '';
			if ( ! empty( $ctc_chat_whatsapp_number ) ) {
				$ctc_chat_whatsapp_number = preg_replace( '/[^0-9]/', '', $ctc_chat_whatsapp_number );
				$ctc_chat_default_message = __( 'Hello! I need help with my order.', 'aicoso-click-to-chat' );
				$ctc_chat_whatsapp_url = 'https://wa.me/' . $ctc_chat_whatsapp_number . '?text=' . rawurlencode( $ctc_chat_default_message );
			}
		}

		// Localize script with parameters.
		wp_localize_script(
			'ctc-chat-cart-checkout-blocks',
			'ctc_chat_block_params',
			array(
				'cart_enabled'      => $ctc_chat_cart_enabled ? '1' : '0',
				'checkout_enabled'  => $ctc_chat_checkout_enabled ? '1' : '0',
				'cart_position'     => isset( $this->ctc_chat_settings['ctc_chat_cart_page']['ctc_chat_position'] ) ?
					$this->ctc_chat_settings['ctc_chat_cart_page']['ctc_chat_position'] : 'after_cart_table',
				'checkout_position' => isset( $this->ctc_chat_settings['ctc_chat_checkout_page']['ctc_chat_position'] ) ?
					$this->ctc_chat_settings['ctc_chat_checkout_page']['ctc_chat_position'] : 'after_payment',
				'button_text'       => isset( $this->ctc_chat_settings['ctc_chat_button_settings']['ctc_chat_text'] ) ?
					$this->ctc_chat_settings['ctc_chat_button_settings']['ctc_chat_text'] : __( 'Order via WhatsApp', 'aicoso-click-to-chat' ),
				'bg_color'          => isset( $this->ctc_chat_settings['ctc_chat_button_settings']['ctc_chat_bg_color'] ) ?
					$this->ctc_chat_settings['ctc_chat_button_settings']['ctc_chat_bg_color'] : '#25D366',
				'text_color'        => isset( $this->ctc_chat_settings['ctc_chat_button_settings']['ctc_chat_text_color'] ) ?
					$this->ctc_chat_settings['ctc_chat_button_settings']['ctc_chat_text_color'] : '#ffffff',
				'show_icon'         => isset( $this->ctc_chat_settings['ctc_chat_button_settings']['ctc_chat_icon'] ) && $this->ctc_chat_settings['ctc_chat_button_settings']['ctc_chat_icon'] ? '1' : '0',
				'whatsapp_url'      => $ctc_chat_whatsapp_url,
			)
		);
	}

	/**
	 * Handle advanced options for hiding WooCommerce buttons
	 */
	public function ctc_chat_handle_advanced_options() {
		// Check if plugin is enabled.
		$ctc_chat_plugin_enabled = isset( $this->ctc_chat_settings['ctc_chat_plugin_enabled'] ) ? $this->ctc_chat_settings['ctc_chat_plugin_enabled'] : true;
		if ( ! $ctc_chat_plugin_enabled ) {
			return;
		}

		// Check if any advanced options are enabled.
		if ( ! isset( $this->ctc_chat_settings['ctc_chat_advanced'] ) ) {
			return;
		}

		$ctc_chat_advanced = $this->ctc_chat_settings['ctc_chat_advanced'];

		// Check for catalog mode first (overrides individual settings).
		if ( isset( $ctc_chat_advanced['ctc_chat_catalog_mode'] ) && $ctc_chat_advanced['ctc_chat_catalog_mode'] ) {
			$this->ctc_chat_hide_all_purchase_buttons();
			return;
		}

		// Hide Add to Cart buttons.
		if ( isset( $ctc_chat_advanced['ctc_chat_hide_add_to_cart'] ) && $ctc_chat_advanced['ctc_chat_hide_add_to_cart'] ) {
			$this->ctc_chat_hide_add_to_cart_buttons();
		}

		// Hide Proceed to Checkout button.
		if ( isset( $ctc_chat_advanced['ctc_chat_hide_proceed_checkout'] ) && $ctc_chat_advanced['ctc_chat_hide_proceed_checkout'] ) {
			$this->ctc_chat_hide_proceed_checkout_button();
		}

		// Hide Place Order button.
		if ( isset( $ctc_chat_advanced['ctc_chat_hide_place_order'] ) && $ctc_chat_advanced['ctc_chat_hide_place_order'] ) {
			$this->ctc_chat_hide_place_order_button();
		}
	}

	/**
	 * Hide all purchase buttons (catalog mode)
	 */
	private function ctc_chat_hide_all_purchase_buttons() {
		$this->ctc_chat_hide_add_to_cart_buttons();
		$this->ctc_chat_hide_proceed_checkout_button();
		$this->ctc_chat_hide_place_order_button();
	}

	/**
	 * Hide Add to Cart buttons
	 */
	private function ctc_chat_hide_add_to_cart_buttons() {
		// Remove add to cart buttons globally first.
		remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_add_to_cart', 30 );
		remove_action( 'woocommerce_after_shop_loop_item', 'woocommerce_template_loop_add_to_cart', 10 );

		// Add CSS to hide any remaining add to cart buttons.
		add_action( 'wp_enqueue_scripts', array( $this, 'ctc_chat_hide_add_to_cart_css' ) );
	}

	/**
	 * Hide Proceed to Checkout button
	 */
	private function ctc_chat_hide_proceed_checkout_button() {
		// Remove proceed to checkout button.
		remove_action( 'woocommerce_proceed_to_checkout', 'woocommerce_button_proceed_to_checkout', 20 );

		// Add CSS to hide the button on all pages.
		add_action( 'wp_enqueue_scripts', array( $this, 'ctc_chat_hide_proceed_checkout_css' ) );
	}

	/**
	 * Hide Place Order button
	 */
	private function ctc_chat_hide_place_order_button() {
		// Add CSS to hide the place order button on all pages.
		add_action( 'wp_enqueue_scripts', array( $this, 'ctc_chat_hide_place_order_css' ) );

		// Optionally prevent form submission.
		add_action( 'wp_enqueue_scripts', array( $this, 'ctc_chat_disable_checkout_form_submission' ) );
	}

	/**
	 * Add CSS to hide Add to Cart buttons
	 */
	public function ctc_chat_hide_add_to_cart_css() {
		$ctc_chat_css = '
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
		';

		wp_add_inline_style( 'ctc-chat-public-styles', $ctc_chat_css );
	}

	/**
	 * Add CSS to hide Proceed to Checkout button
	 */
	public function ctc_chat_hide_proceed_checkout_css() {
		$ctc_chat_css = '
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
		';

		wp_add_inline_style( 'ctc-chat-public-styles', $ctc_chat_css );
	}

	/**
	 * Add CSS to hide Place Order button
	 */
	public function ctc_chat_hide_place_order_css() {
		$ctc_chat_css = '
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
		';

		wp_add_inline_style( 'ctc-chat-public-styles', $ctc_chat_css );
	}

	/**
	 * Disable checkout form submission
	 */
	public function ctc_chat_disable_checkout_form_submission() {
		if ( is_checkout() ) {
			$ctc_chat_script = "
				jQuery(document).ready(function($) {
					// Disable form submission.
					$('form.checkout').on('submit', function(e) {
						e.preventDefault();
						return false;
					});
				});
			";

			wp_add_inline_script( 'ctc-chat-public-script', $ctc_chat_script );
		}
	}

	/**
	 * Check if the current page is excluded
	 *
	 * @return bool True if page is excluded, false otherwise
	 */
	private function ctc_chat_is_page_excluded() {
		// If exclusions not set or empty, nothing is excluded.
		if ( empty( $this->ctc_chat_settings['exclusions'] ) ) {
			return false;
		}

		// Get current page ID.
		$current_id = get_the_ID();

		// Special handling for WooCommerce Cart page.
		if ( is_cart() ) {
			$cart_page_id = wc_get_page_id( 'cart' );
			if ( $cart_page_id && isset( $this->ctc_chat_settings['exclusions']['pages'] ) && ! empty( $this->ctc_chat_settings['exclusions']['pages'] ) ) {
				if ( in_array( $cart_page_id, $this->ctc_chat_settings['exclusions']['pages'], true ) ) {
					return true;
				}
			}
		}

		// Special handling for WooCommerce Checkout page.
		if ( is_checkout() ) {
			$checkout_page_id = wc_get_page_id( 'checkout' );
			if ( $checkout_page_id && isset( $this->ctc_chat_settings['exclusions']['pages'] ) && ! empty( $this->ctc_chat_settings['exclusions']['pages'] ) ) {
				if ( in_array( $checkout_page_id, $this->ctc_chat_settings['exclusions']['pages'], true ) ) {
					return true;
				}
			}
		}

		// Check general page exclusions if we have a current ID.
		if ( $current_id && isset( $this->ctc_chat_settings['exclusions']['pages'] ) && ! empty( $this->ctc_chat_settings['exclusions']['pages'] ) ) {
			if ( in_array( $current_id, $this->ctc_chat_settings['exclusions']['pages'], true ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * AJAX handler to get variation-specific WhatsApp URL
	 */
	public function ctc_chat_ajax_get_variation_url() {
		// Check nonce.
		if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'ctc_chat_public_nonce' ) ) {
			wp_send_json_error( array( 'message' => __( 'Security check failed.', 'aicoso-click-to-chat' ) ) );
		}

		// Get product ID and variations.
		$ctc_chat_product_id = isset( $_POST['product_id'] ) ? intval( $_POST['product_id'] ) : 0;
		$ctc_chat_variations = isset( $_POST['variations'] ) ? array_map( 'sanitize_text_field', wp_unslash( $_POST['variations'] ) ) : array();

		if ( ! $ctc_chat_product_id ) {
			wp_send_json_error( array( 'message' => __( 'Invalid product ID.', 'aicoso-click-to-chat' ) ) );
		}

		// Initialize link generator.
		$ctc_chat_link_generator = new CTC_Chat_WhatsApp_Link_Generator();

		// Generate WhatsApp URL with variations.
		$ctc_chat_whatsapp_url = $ctc_chat_link_generator->get_product_url( $ctc_chat_product_id, $ctc_chat_variations );

		if ( empty( $ctc_chat_whatsapp_url ) ) {
			wp_send_json_error( array( 'message' => __( 'Could not generate WhatsApp URL.', 'aicoso-click-to-chat' ) ) );
		}

		wp_send_json_success( array( 'url' => $ctc_chat_whatsapp_url ) );
	}
}

// Initialize the class.
new CTC_Chat_Public();
