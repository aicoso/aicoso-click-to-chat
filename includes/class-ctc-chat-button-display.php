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
					$this->settings['single_product']['position'] : 'below_add_to_cart';
		$position_class = 'ctc-chat-position-' . str_replace( '_', '-', $position );

		// Check if catalog mode is active.
		$catalog_mode = isset( $this->settings['advanced']['catalog_mode'] ) && $this->settings['advanced']['catalog_mode'];
		$hide_add_to_cart = isset( $this->settings['advanced']['hide_add_to_cart'] ) && $this->settings['advanced']['hide_add_to_cart'];

		if ( $catalog_mode || $hide_add_to_cart ) {
			$position_class .= ' ctc-chat-catalog-mode-button';
		}

		// Wrap button with position-specific container.
		echo '<div class="' . esc_attr( $position_class ) . '">';
		$this->render_button( $whatsapp_url, 'product' );
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
		$this->render_button( $whatsapp_url, 'shop' );

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
		$this->render_button( $whatsapp_url, 'cart' );
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
		$this->render_button( $whatsapp_url, 'checkout' );
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
		$this->render_button( $whatsapp_url, 'thankyou' );
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
		$this->render_button( $whatsapp_url, 'floating' );
		echo '</div>';

		// Mark this button as displayed.
		self::$buttons_displayed['floating'] = true;
	}

	/**
	 * Render the WhatsApp button
	 *
	 * @param string $url  The WhatsApp URL.
	 * @param string $type The button type (for CSS classes).
	 */
	private function render_button( $url, $type = 'default' ) {
		// Get button settings.
		$button_text = isset( $this->settings['button_settings']['text'] ) ?
					  $this->settings['button_settings']['text'] : esc_html__( 'Order via WhatsApp', 'aicoso-click-to-chat' );

		$show_icon = isset( $this->settings['button_settings']['icon'] ) ?
					$this->settings['button_settings']['icon'] : true;

		$bg_color = isset( $this->settings['button_settings']['bg_color'] ) ?
				   $this->settings['button_settings']['bg_color'] : '#25D366';

		$text_color = isset( $this->settings['button_settings']['text_color'] ) ?
					 $this->settings['button_settings']['text_color'] : '#ffffff';

		// Inline styles.
		$button_style = 'background-color: ' . esc_attr( $bg_color ) . '; color: ' . esc_attr( $text_color ) . ';';

		// Button classes.
		$button_classes = array(
			'ctc-chat-whatsapp-button',
			'ctc-chat-button-' . $type,
		);

		if ( $show_icon ) {
			$button_classes[] = 'ctc-chat-button-with-icon';
		}

		$class_attr = implode( ' ', $button_classes );

		// Output the button HTML.
		?>
		<a href="<?php echo esc_url( $url ); ?>" class="<?php echo esc_attr( $class_attr ); ?>" style="<?php echo esc_attr( $button_style ); ?>" target="_blank" rel="noopener">
			<?php if ( $show_icon ) : ?>
				<span class="ctc-chat-whatsapp-icon">
					<svg viewBox="0 0 24 24" width="24" height="24">
						<path fill="currentColor" d="M17.498 14.382c-.301-.15-1.767-.867-2.04-.966-.273-.101-.473-.15-.673.15-.197.295-.771.964-.944 1.162-.175.195-.349.21-.646.075-.3-.15-1.263-.465-2.403-1.485-.888-.795-1.484-1.77-1.66-2.07-.174-.3-.019-.465.13-.615.136-.135.301-.345.451-.523.146-.181.194-.301.297-.496.1-.21.049-.375-.025-.524-.075-.15-.672-1.62-.922-2.206-.24-.584-.487-.51-.672-.51-.172-.015-.371-.015-.571-.015-.2 0-.523.074-.797.359-.273.3-1.045 1.02-1.045 2.475s1.07 2.865 1.219 3.075c.149.195 2.105 3.195 5.1 4.485.714.3 1.27.48 1.704.629.714.227 1.365.195 1.88.121.574-.091 1.767-.721 2.016-1.426.255-.705.255-1.29.18-1.425-.074-.135-.27-.21-.57-.345m-5.446 7.443h-.016c-1.77 0-3.524-.48-5.055-1.38l-.36-.214-3.75.975 1.005-3.645-.239-.375c-.99-1.576-1.516-3.391-1.516-5.26 0-5.445 4.455-9.885 9.942-9.885 2.654 0 5.145 1.035 7.021 2.91 1.875 1.859 2.909 4.35 2.909 6.99-.004 5.444-4.46 9.885-9.935 9.885M20.52 3.449C18.24 1.245 15.24 0 12.045 0 5.463 0 .104 5.334.101 11.893c0 2.096.549 4.14 1.595 5.945L0 24l6.335-1.652c1.746.943 3.71 1.444 5.71 1.447h.006c6.585 0 11.946-5.336 11.949-11.896 0-3.176-1.24-6.165-3.495-8.411"/>
					</svg>
				</span>
			<?php endif; ?>
			<span class="ctc-chat-button-text"><?php echo esc_html( $button_text ); ?></span>
		</a>
		<?php
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
