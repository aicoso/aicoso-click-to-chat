<?php
/**
 * Button Display class.
 *
 * This class handles the placement and display of WhatsApp buttons
 * at various locations throughout the site.
 *
 * @package ClickToChat
 * @since 1.0.0
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Button Display class.
 */
class CTC_Chat_Button_Display {

	/**
	 * Plugin settings
	 *
	 * @var array
	 */
	private $settings;

	/**
	 * WhatsApp Link Generator instance
	 *
	 * @var CTC_Chat_WhatsApp_Link_Generator
	 */
	private $link_generator;

	/**
	 * Track if buttons have been displayed
	 *
	 * @var array
	 */
	private static $buttons_displayed = array();

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->settings = get_option( 'ctc_chat_settings', array() );
		$this->link_generator = new CTC_Chat_WhatsApp_Link_Generator();
		$this->init_hooks();
	}

	/**
	 * Initialize hooks
	 */
	private function init_hooks() {
		// Prevent multiple hook registrations.
		static $hooks_registered = false;

		if ( $hooks_registered ) {
			return;
		}

		// Check if plugin is enabled.
		$plugin_enabled = isset( $this->settings['plugin_enabled'] ) ? $this->settings['plugin_enabled'] : true;
		if ( ! $plugin_enabled ) {
			return; // Exit early if plugin is disabled.
		}

		// Always try to add cart and checkout buttons using multiple hooks for compatibility.
		add_action( 'woocommerce_before_cart', array( $this, 'maybe_display_cart_button' ) );
		add_action( 'woocommerce_after_cart', array( $this, 'maybe_display_cart_button' ) );
		add_action( 'woocommerce_before_checkout_form', array( $this, 'maybe_display_checkout_button' ), 5 );
		add_action( 'woocommerce_after_checkout_form', array( $this, 'maybe_display_checkout_button' ) );

		// Single product hooks.
		if ( isset( $this->settings['single_product']['enabled'] ) && $this->settings['single_product']['enabled'] ) {
			$this->add_single_product_hooks();
		}

		// Shop page hooks.
		if ( isset( $this->settings['shop_page']['enabled'] ) && $this->settings['shop_page']['enabled'] ) {
			$this->add_shop_page_hooks();
		}

		// Cart page hooks.
		if ( isset( $this->settings['cart_page']['enabled'] ) && $this->settings['cart_page']['enabled'] ) {
			$this->add_cart_page_hooks();
		}

		// Checkout page hooks.
		if ( isset( $this->settings['checkout_page']['enabled'] ) && $this->settings['checkout_page']['enabled'] ) {
			$this->add_checkout_page_hooks();
		}

		// Thank you page hooks.
		if ( isset( $this->settings['thankyou_page']['enabled'] ) && $this->settings['thankyou_page']['enabled'] ) {
			add_action( 'woocommerce_thankyou', array( $this, 'display_thankyou_button' ) );
		}

		// Floating button.
		if ( isset( $this->settings['floating_button']['enabled'] ) && $this->settings['floating_button']['enabled'] ) {
			add_action( 'wp_footer', array( $this, 'display_floating_button' ) );
		}

		$hooks_registered = true;
	}

	/**
	 * Add hooks for single product pages
	 */
	private function add_single_product_hooks() {
		$position = isset( $this->settings['single_product']['position'] ) ?
					$this->settings['single_product']['position'] : 'after_add_to_cart';

		switch ( $position ) {
			case 'after_add_to_cart':
			case 'below_add_to_cart':
				// Check if add to cart is hidden (catalog mode).
				$hide_add_to_cart = isset( $this->settings['advanced']['hide_add_to_cart'] ) && $this->settings['advanced']['hide_add_to_cart'];
				$catalog_mode = isset( $this->settings['advanced']['catalog_mode'] ) && $this->settings['advanced']['catalog_mode'];

				if ( $hide_add_to_cart || $catalog_mode ) {
					// If add to cart is hidden, hook to product summary instead (after price).
					add_action( 'woocommerce_single_product_summary', array( $this, 'display_single_product_button' ), 31 );
				} else {
					// Normal case: after add to cart form.
					add_action( 'woocommerce_after_add_to_cart_form', array( $this, 'display_single_product_button' ) );
				}
				break;

			case 'before_add_to_cart':
				add_action( 'woocommerce_before_add_to_cart_button', array( $this, 'display_single_product_button' ) );
				break;

			case 'after_price':
				// Hook to woocommerce_single_product_summary with priority 11 (after price which is at 10).
				add_action( 'woocommerce_single_product_summary', array( $this, 'display_single_product_button' ), 11 );
				break;

			case 'before_title':
				add_action( 'woocommerce_before_single_product_summary', array( $this, 'display_single_product_button' ), 5 );
				break;

			case 'after_short_description':
				add_action( 'woocommerce_single_product_summary', array( $this, 'display_single_product_button' ), 25 );
				break;
		}
	}

	/**
	 * Add hooks for cart page
	 */
	private function add_cart_page_hooks() {
		$position = isset( $this->settings['cart_page']['position'] ) ?
					$this->settings['cart_page']['position'] : 'after_cart_table';

		switch ( $position ) {
			case 'after_cart_table':
				add_action( 'woocommerce_after_cart_table', array( $this, 'display_cart_button' ) );
				break;

			case 'before_cart_table':
				add_action( 'woocommerce_before_cart_table', array( $this, 'display_cart_button' ) );
				break;

			case 'proceed_to_checkout':
				add_action( 'woocommerce_proceed_to_checkout', array( $this, 'display_cart_button' ), 25 );
				break;

			case 'after_cart_totals':
				add_action( 'woocommerce_after_cart_totals', array( $this, 'display_cart_button' ) );
				break;

			case 'cart_actions':
				add_action( 'woocommerce_cart_actions', array( $this, 'display_cart_button' ) );
				break;
		}
	}

	/**
	 * Add hooks for checkout page
	 */
	private function add_checkout_page_hooks() {
		$position = isset( $this->settings['checkout_page']['position'] ) ?
					$this->settings['checkout_page']['position'] : 'after_payment';

		switch ( $position ) {
			case 'after_payment':
				add_action( 'woocommerce_review_order_after_payment', array( $this, 'display_checkout_button' ) );
				break;

			case 'before_payment':
				add_action( 'woocommerce_review_order_before_payment', array( $this, 'display_checkout_button' ) );
				break;

			case 'after_order_review':
				add_action( 'woocommerce_checkout_after_order_review', array( $this, 'display_checkout_button' ) );
				break;

			case 'before_order_review':
				add_action( 'woocommerce_checkout_before_order_review', array( $this, 'display_checkout_button' ), 5 );
				break;

			case 'after_submit':
				add_action( 'woocommerce_review_order_after_submit', array( $this, 'display_checkout_button' ) );
				break;
		}
	}

	/**
	 * Add hooks for shop pages
	 */
	private function add_shop_page_hooks() {
		$position = isset( $this->settings['shop_page']['position'] ) ?
					$this->settings['shop_page']['position'] : 'after_add_to_cart';

		switch ( $position ) {
			case 'after_add_to_cart':
				add_action( 'woocommerce_after_shop_loop_item', array( $this, 'display_shop_button' ), 15 );
				break;

			case 'before_add_to_cart':
				add_action( 'woocommerce_after_shop_loop_item', array( $this, 'display_shop_button' ), 9 );
				break;

			case 'after_price':
				// Hook to woocommerce_after_shop_loop_item_title with priority 11 (after price which is at 10).
				add_action( 'woocommerce_after_shop_loop_item_title', array( $this, 'display_shop_button' ), 11 );
				break;

			case 'before_title':
				add_action( 'woocommerce_before_shop_loop_item_title', array( $this, 'display_shop_button' ), 15 );
				break;
		}
	}

	/**
	 * Display WhatsApp button on single product pages
	 */
	public function display_single_product_button() {
		global $product;

		// Check if button has already been displayed for this product.
		if ( isset( self::$buttons_displayed[ 'product_' . $product->get_id() ] ) ) {
			return;
		}

		if ( ! $product ) {
			return;
		}

		// Respect per-product hide setting.
		if ( $this->link_generator->is_product_button_hidden( $product->get_id() ) ) {
			return;
		}

		// Check if the product should be excluded.
		if ( $this->is_excluded( $product->get_id() ) ) {
			return;
		}

		// Get the WhatsApp URL.
		$whatsapp_url = $this->link_generator->get_product_url( $product->get_id() );

		if ( empty( $whatsapp_url ) ) {
			return;
		}

		// Get button position for wrapper class.
		$position = isset( $this->settings['single_product']['position'] ) ?
					$this->settings['single_product']['position'] : 'after_add_to_cart';
		$position_class = 'ctc-chat-position-' . str_replace( '_', '-', $position );

		// Check if catalog mode is active.
		$catalog_mode = isset( $this->settings['advanced']['catalog_mode'] ) && $this->settings['advanced']['catalog_mode'];
		$hide_add_to_cart = isset( $this->settings['advanced']['hide_add_to_cart'] ) && $this->settings['advanced']['hide_add_to_cart'];

		if ( $catalog_mode || $hide_add_to_cart ) {
			$position_class .= ' ctc-chat-catalog-mode-button';
		}

		// Wrap button with position-specific container.
		echo '<div class="' . esc_attr( $position_class ) . '">';
		$this->render_button(
			$whatsapp_url,
			'product',
			$this->build_product_context( $product )
		);
		echo '</div>';

		// Mark this button as displayed.
		self::$buttons_displayed[ 'product_' . $product->get_id() ] = true;
	}

	/**
	 * Display WhatsApp button on shop pages
	 */
	public function display_shop_button() {
		global $product;

		// Check if button has already been displayed for this product.
		if ( isset( self::$buttons_displayed[ 'shop_' . $product->get_id() ] ) ) {
			return;
		}

		if ( ! $product ) {
			return;
		}

		// Respect per-product hide setting.
		if ( $this->link_generator->is_product_button_hidden( $product->get_id() ) ) {
			return;
		}

		// Check if the shop page itself is excluded (without product ID).
		if ( $this->is_excluded() ) {
			return;
		}

		// Check if the specific product should be excluded.
		if ( $this->is_excluded( $product->get_id() ) ) {
			return;
		}

		// Get the WhatsApp URL (product-specific on shop pages).
		$whatsapp_url = $this->link_generator->get_product_url( $product->get_id() );

		if ( empty( $whatsapp_url ) ) {
			return;
		}

		// Display the button.
		$this->render_button(
			$whatsapp_url,
			'shop',
			$this->build_product_context( $product, 'shop' )
		);

		// Mark this button as displayed.
		self::$buttons_displayed[ 'shop_' . $product->get_id() ] = true;
	}

	/**
	 * Maybe display cart button based on settings
	 */
	public function maybe_display_cart_button() {
		// Check if plugin is enabled.
		$plugin_enabled = isset( $this->settings['plugin_enabled'] ) ? $this->settings['plugin_enabled'] : true;
		if ( ! $plugin_enabled ) {
			return;
		}

		// Check if cart buttons are enabled.
		if ( ! isset( $this->settings['cart_page']['enabled'] ) || ! $this->settings['cart_page']['enabled'] ) {
			return;
		}

		// Only display on cart page.
		if ( ! is_cart() ) {
			return;
		}

		$this->display_cart_button();
	}

	/**
	 * Maybe display checkout button based on settings
	 */
	public function maybe_display_checkout_button() {
		// Check if plugin is enabled.
		$plugin_enabled = isset( $this->settings['plugin_enabled'] ) ? $this->settings['plugin_enabled'] : true;
		if ( ! $plugin_enabled ) {
			return;
		}

		// Check if checkout buttons are enabled.
		if ( ! isset( $this->settings['checkout_page']['enabled'] ) || ! $this->settings['checkout_page']['enabled'] ) {
			return;
		}

		// Only display on checkout page.
		if ( ! is_checkout() ) {
			return;
		}

		$this->display_checkout_button();
	}

	/**
	 * Display WhatsApp button on cart page
	 */
	public function display_cart_button() {
		// Check if button has already been displayed for cart.
		if ( isset( self::$buttons_displayed['cart'] ) ) {
			return;
		}

		// Check if the page should be excluded.
		if ( $this->is_excluded() ) {
			return;
		}

		// Get the WhatsApp URL.
		$whatsapp_url = $this->link_generator->get_cart_url();

		// If no URL, try to create a basic one with just the number.
		if ( empty( $whatsapp_url ) ) {
			$whatsapp_number = $this->link_generator->get_whatsapp_number();
			if ( ! empty( $whatsapp_number ) ) {
				$whatsapp_number = preg_replace( '/[^0-9]/', '', $whatsapp_number );
				$default_message = esc_html__( 'Hello! I need help with my cart on your website.', 'aicoso-click-to-chat' );
				$whatsapp_url = 'https://wa.me/' . $whatsapp_number . '?text=' . rawurlencode( $default_message );
			}
		}

		if ( empty( $whatsapp_url ) ) {
			return;
		}

		// Display the button with proper wrapper.
		echo '<div class="ctc-chat-cart-button-container">';
		$this->render_button(
			$whatsapp_url,
			'cart',
			$this->build_cart_context()
		);
		echo '</div>';

		// Mark this button as displayed.
		self::$buttons_displayed['cart'] = true;
	}

	/**
	 * Display WhatsApp button on checkout page
	 */
	public function display_checkout_button() {
		// Check if button has already been displayed for checkout.
		if ( isset( self::$buttons_displayed['checkout'] ) ) {
			return;
		}

		// Check if the page should be excluded.
		if ( $this->is_excluded() ) {
			return;
		}

		// Get the WhatsApp URL.
		$whatsapp_url = $this->link_generator->get_cart_url();

		// If no URL, try to create a basic one with just the number.
		if ( empty( $whatsapp_url ) ) {
			$whatsapp_number = $this->link_generator->get_whatsapp_number();
			if ( ! empty( $whatsapp_number ) ) {
				$whatsapp_number = preg_replace( '/[^0-9]/', '', $whatsapp_number );
				$default_message = esc_html__( 'Hello! I need help with my checkout on your website.', 'aicoso-click-to-chat' );
				$whatsapp_url = 'https://wa.me/' . $whatsapp_number . '?text=' . rawurlencode( $default_message );
			}
		}

		if ( empty( $whatsapp_url ) ) {
			return;
		}

		// Display the button with proper wrapper.
		echo '<div class="ctc-chat-checkout-button-container">';
		$this->render_button(
			$whatsapp_url,
			'checkout',
			$this->build_cart_context()
		);
		echo '</div>';

		// Mark this button as displayed.
		self::$buttons_displayed['checkout'] = true;
	}

	/**
	 * Display WhatsApp button on thank you page
	 *
	 * @param int $order_id The order ID.
	 */
	public function display_thankyou_button( $order_id ) {
		// Check if button has already been displayed for this order.
		if ( isset( self::$buttons_displayed[ 'thankyou_' . $order_id ] ) ) {
			return;
		}

		// Check if the page should be excluded.
		if ( $this->is_excluded() ) {
			return;
		}

		// Get the WhatsApp URL.
		$whatsapp_url = $this->link_generator->get_thankyou_url( $order_id );

		if ( empty( $whatsapp_url ) ) {
			return;
		}

		// Display the button.
		echo '<div class="ctc-chat-thankyou-button-container">';
		$this->render_button(
			$whatsapp_url,
			'thankyou',
			$this->build_thankyou_context( $order_id )
		);
		echo '</div>';

		// Mark this button as displayed.
		self::$buttons_displayed[ 'thankyou_' . $order_id ] = true;
	}

	/**
	 * Display floating WhatsApp button
	 */
	public function display_floating_button() {
		// Check if button has already been displayed.
		if ( isset( self::$buttons_displayed['floating'] ) ) {
			return;
		}

		// Check if the page should be excluded.
		if ( $this->is_excluded() ) {
			return;
		}

		// Get the WhatsApp URL.
		$whatsapp_url = $this->link_generator->get_floating_url();

		if ( empty( $whatsapp_url ) ) {
			return;
		}

		// Get the position.
		$position = isset( $this->settings['floating_button']['position'] ) ?
				   $this->settings['floating_button']['position'] : 'bottom_right';

		// Define position classes.
		$position_class = 'ctc-chat-floating-' . $position;

		// Display the button.
		echo '<div class="ctc-chat-floating-button-container ' . esc_attr( $position_class ) . '">';
		$this->render_button(
			$whatsapp_url,
			'floating',
			$this->build_floating_context()
		);
		echo '</div>';

		// Mark this button as displayed.
		self::$buttons_displayed['floating'] = true;
	}

	/**
	 * Render the WhatsApp button.
	 *
	 * @param string $url     The WhatsApp URL.
	 * @param string $type    The button type (for CSS classes).
	 * @param array  $context Tracking context.
	 */
	private function render_button( $url, $type = 'default', $context = array() ) {
		CTC_Chat_Button_Renderer::render(
			$url,
			$type,
			array(
				'context' => $context,
			)
		);
	}

	/**
	 * Build product tracking context.
	 *
	 * @param WC_Product $product       Product object.
	 * @param string     $template_type Optional template override.
	 * @return array
	 */
	private function build_product_context( $product, $template_type = '' ) {
		$product_id = $product->get_id();
		$page_id    = null;
		$category_id = null;

		if ( function_exists( 'is_shop' ) && is_shop() ) {
			$page_id = wc_get_page_id( 'shop' );
		} elseif ( function_exists( 'is_product_category' ) && is_product_category() ) {
			$category = get_queried_object();
			if ( $category && isset( $category->term_id ) ) {
				$category_id = $category->term_id;
			}
		} elseif ( is_page() ) {
			$page_id = get_the_ID();
		}

		if ( ! $template_type ) {
			if ( get_post_meta( $product_id, '_ctc_chat_custom_message', true ) ) {
				$template_type = 'custom';
			} elseif ( $product->is_type( 'variable' ) ) {
				$template_type = 'variations';
			} else {
				$template_type = 'single_product';
			}
		}

		return array(
			'template_type' => $template_type,
			'number_id'     => $this->link_generator->get_number_id( $product_id, $category_id, $page_id ),
			'product_id'    => $product_id,
		);
	}

	/**
	 * Build cart/checkout tracking context.
	 *
	 * @return array
	 */
	private function build_cart_context() {
		$context = ctc_chat_get_cart_tracking_context();

		return array_merge(
			$context,
			array(
				'template_type' => 'cart_checkout',
				'number_id'     => $this->link_generator->get_number_id(),
			)
		);
	}

	/**
	 * Build thank-you tracking context.
	 *
	 * @param int $order_id Order ID.
	 * @return array
	 */
	private function build_thankyou_context( $order_id ) {
		$context = array(
			'template_type' => 'thank_you',
			'order_id'      => absint( $order_id ),
			'number_id'     => $this->link_generator->get_number_id(),
		);

		if ( function_exists( 'wc_get_order' ) ) {
			$order = wc_get_order( $order_id );
			if ( $order ) {
				$context['cart_total']    = (float) $order->get_total();
				$context['cart_currency'] = $order->get_currency();
			}
		}

		return $context;
	}

	/**
	 * Build floating button tracking context.
	 *
	 * @return array
	 */
	private function build_floating_context() {
		$page_id = is_page() ? get_the_ID() : null;

		return array(
			'template_type' => 'floating',
			'number_id'     => $this->link_generator->get_number_id( null, null, $page_id ),
		);
	}

	/**
	 * Check if the current page or product should be excluded
	 *
	 * @param int $product_id Optional product ID.
	 * @return bool True if should be excluded, false otherwise.
	 */
	private function is_excluded( $product_id = null ) {
		// If exclusions not set or empty, nothing is excluded.
		if ( empty( $this->settings['exclusions'] ) ) {
			return false;
		}

		// Check specific product exclusions.
		if ( $product_id && isset( $this->settings['exclusions']['products'] ) && ! empty( $this->settings['exclusions']['products'] ) ) {
			if ( in_array( $product_id, $this->settings['exclusions']['products'], true ) ) {
				return true;
			}
		}

		// Check product category exclusions.
		if ( $product_id && isset( $this->settings['exclusions']['categories'] ) && ! empty( $this->settings['exclusions']['categories'] ) ) {
			$product_categories = wp_get_post_terms( $product_id, 'product_cat', array( 'fields' => 'ids' ) );

			if ( is_array( $product_categories ) ) {
				$excluded_categories = $this->settings['exclusions']['categories'];

				// Check if product is in any excluded category.
				if ( array_intersect( $product_categories, $excluded_categories ) ) {
					return true;
				}
			}
		}

		// Check product tag exclusions.
		if ( $product_id && isset( $this->settings['exclusions']['tags'] ) && ! empty( $this->settings['exclusions']['tags'] ) ) {
			$product_tags = wp_get_post_terms( $product_id, 'product_tag', array( 'fields' => 'ids' ) );

			if ( is_array( $product_tags ) ) {
				$excluded_tags = $this->settings['exclusions']['tags'];

				// Check if product has any excluded tag.
				if ( array_intersect( $product_tags, $excluded_tags ) ) {
					return true;
				}
			}
		}

		// Get current page/post ID.
		$current_id = get_the_ID();

		// Special handling for WooCommerce Shop page.
		if ( is_shop() ) {
			$shop_page_id = wc_get_page_id( 'shop' );
			if ( $shop_page_id && isset( $this->settings['exclusions']['pages'] ) && ! empty( $this->settings['exclusions']['pages'] ) ) {
				if ( in_array( $shop_page_id, $this->settings['exclusions']['pages'], true ) ) {
					return true;
				}
			}
		}

		if ( ! $current_id ) {
			return false;
		}

		// Check page exclusions.
		if ( is_page() && isset( $this->settings['exclusions']['pages'] ) && ! empty( $this->settings['exclusions']['pages'] ) ) {
			if ( in_array( $current_id, $this->settings['exclusions']['pages'], true ) ) {
				return true;
			}
		}

		// Check post exclusions.
		if ( is_single() && ! is_product() && isset( $this->settings['exclusions']['posts'] ) && ! empty( $this->settings['exclusions']['posts'] ) ) {
			if ( in_array( $current_id, $this->settings['exclusions']['posts'], true ) ) {
				return true;
			}
		}

		return false;
	}
}
