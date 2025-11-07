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
	private $ctc_chat_settings;

	/**
	 * WhatsApp Link Generator instance
	 *
	 * @var CTC_Chat_WhatsApp_Link_Generator
	 */
	private $ctc_chat_link_generator;

	/**
	 * Track if buttons have been displayed
	 *
	 * @var array
	 */
	private static $ctc_chat_buttons_displayed = array();

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->ctc_chat_settings = get_option( 'ctc_chat_settings', array() );
		$this->ctc_chat_link_generator = new CTC_Chat_WhatsApp_Link_Generator();
		$this->ctc_chat_init_hooks();
	}

	/**
	 * Initialize hooks
	 */
	private function ctc_chat_init_hooks() {
		// Prevent multiple hook registrations.
		static $ctc_chat_hooks_registered = false;

		if ( $ctc_chat_hooks_registered ) {
			return;
		}

		// Check if plugin is enabled.
		$ctc_chat_plugin_enabled = isset( $this->ctc_chat_settings['ctc_chat_plugin_enabled'] ) ? $this->ctc_chat_settings['ctc_chat_plugin_enabled'] : true;
		if ( ! $ctc_chat_plugin_enabled ) {
			return; // Exit early if plugin is disabled.
		}

		// Always try to add cart and checkout buttons using multiple hooks for compatibility.
		add_action( 'woocommerce_before_cart', array( $this, 'ctc_chat_maybe_display_cart_button' ) );
		add_action( 'woocommerce_after_cart', array( $this, 'ctc_chat_maybe_display_cart_button' ) );
		add_action( 'woocommerce_before_checkout_form', array( $this, 'ctc_chat_maybe_display_checkout_button' ), 5 );
		add_action( 'woocommerce_after_checkout_form', array( $this, 'ctc_chat_maybe_display_checkout_button' ) );

		// Single product hooks.
		if ( isset( $this->ctc_chat_settings['ctc_chat_single_product']['ctc_chat_enabled'] ) && $this->ctc_chat_settings['ctc_chat_single_product']['ctc_chat_enabled'] ) {
			$this->ctc_chat_add_single_product_hooks();
		}

		// Shop page hooks.
		if ( isset( $this->ctc_chat_settings['ctc_chat_shop_page']['ctc_chat_enabled'] ) && $this->ctc_chat_settings['ctc_chat_shop_page']['ctc_chat_enabled'] ) {
			$this->ctc_chat_add_shop_page_hooks();
		}

		// Cart page hooks.
		if ( isset( $this->ctc_chat_settings['ctc_chat_cart_page']['ctc_chat_enabled'] ) && $this->ctc_chat_settings['ctc_chat_cart_page']['ctc_chat_enabled'] ) {
			$this->ctc_chat_add_cart_page_hooks();
		}

		// Checkout page hooks.
		if ( isset( $this->ctc_chat_settings['ctc_chat_checkout_page']['ctc_chat_enabled'] ) && $this->ctc_chat_settings['ctc_chat_checkout_page']['ctc_chat_enabled'] ) {
			$this->ctc_chat_add_checkout_page_hooks();
		}

		// Thank you page hooks.
		if ( isset( $this->ctc_chat_settings['ctc_chat_thankyou_page']['ctc_chat_enabled'] ) && $this->ctc_chat_settings['ctc_chat_thankyou_page']['ctc_chat_enabled'] ) {
			add_action( 'woocommerce_thankyou', array( $this, 'ctc_chat_display_thankyou_button' ) );
		}

		// Floating button.
		if ( isset( $this->ctc_chat_settings['ctc_chat_floating_button']['ctc_chat_enabled'] ) && $this->ctc_chat_settings['ctc_chat_floating_button']['ctc_chat_enabled'] ) {
			add_action( 'wp_footer', array( $this, 'ctc_chat_display_floating_button' ) );
		}

		$ctc_chat_hooks_registered = true;
	}

	/**
	 * Add hooks for single product pages
	 */
	private function ctc_chat_add_single_product_hooks() {
		$ctc_chat_position = isset( $this->ctc_chat_settings['ctc_chat_single_product']['ctc_chat_position'] ) ?
					$this->ctc_chat_settings['ctc_chat_single_product']['ctc_chat_position'] : 'after_add_to_cart';

		switch ( $ctc_chat_position ) {
			case 'after_add_to_cart':
			case 'below_add_to_cart':
				// Check if add to cart is hidden (catalog mode).
				$ctc_chat_hide_add_to_cart = isset( $this->ctc_chat_settings['ctc_chat_advanced']['ctc_chat_hide_add_to_cart'] ) && $this->ctc_chat_settings['ctc_chat_advanced']['ctc_chat_hide_add_to_cart'];
				$ctc_chat_catalog_mode = isset( $this->ctc_chat_settings['ctc_chat_advanced']['ctc_chat_catalog_mode'] ) && $this->ctc_chat_settings['ctc_chat_advanced']['ctc_chat_catalog_mode'];

				if ( $ctc_chat_hide_add_to_cart || $ctc_chat_catalog_mode ) {
					// If add to cart is hidden, hook to product summary instead (after price).
					add_action( 'woocommerce_single_product_summary', array( $this, 'ctc_chat_display_single_product_button' ), 31 );
				} else {
					// Normal case: after add to cart form.
					add_action( 'woocommerce_after_add_to_cart_form', array( $this, 'ctc_chat_display_single_product_button' ) );
				}
				break;

			case 'before_add_to_cart':
				add_action( 'woocommerce_before_add_to_cart_button', array( $this, 'ctc_chat_display_single_product_button' ) );
				break;

			case 'after_price':
				// Hook to woocommerce_single_product_summary with priority 11 (after price which is at 10).
				add_action( 'woocommerce_single_product_summary', array( $this, 'ctc_chat_display_single_product_button' ), 11 );
				break;

			case 'before_title':
				add_action( 'woocommerce_before_single_product_summary', array( $this, 'ctc_chat_display_single_product_button' ), 5 );
				break;

			case 'after_short_description':
				add_action( 'woocommerce_single_product_summary', array( $this, 'ctc_chat_display_single_product_button' ), 25 );
				break;
		}
	}

	/**
	 * Add hooks for cart page
	 */
	private function ctc_chat_add_cart_page_hooks() {
		$ctc_chat_position = isset( $this->ctc_chat_settings['ctc_chat_cart_page']['ctc_chat_position'] ) ?
					$this->ctc_chat_settings['ctc_chat_cart_page']['ctc_chat_position'] : 'after_cart_table';

		switch ( $ctc_chat_position ) {
			case 'after_cart_table':
				add_action( 'woocommerce_after_cart_table', array( $this, 'ctc_chat_display_cart_button' ) );
				break;

			case 'before_cart_table':
				add_action( 'woocommerce_before_cart_table', array( $this, 'ctc_chat_display_cart_button' ) );
				break;

			case 'proceed_to_checkout':
				add_action( 'woocommerce_proceed_to_checkout', array( $this, 'ctc_chat_display_cart_button' ), 25 );
				break;

			case 'after_cart_totals':
				add_action( 'woocommerce_after_cart_totals', array( $this, 'ctc_chat_display_cart_button' ) );
				break;

			case 'cart_actions':
				add_action( 'woocommerce_cart_actions', array( $this, 'ctc_chat_display_cart_button' ) );
				break;
		}
	}

	/**
	 * Add hooks for checkout page
	 */
	private function ctc_chat_add_checkout_page_hooks() {
		$ctc_chat_position = isset( $this->ctc_chat_settings['ctc_chat_checkout_page']['ctc_chat_position'] ) ?
					$this->ctc_chat_settings['ctc_chat_checkout_page']['ctc_chat_position'] : 'after_payment';

		switch ( $ctc_chat_position ) {
			case 'after_payment':
				add_action( 'woocommerce_review_order_after_payment', array( $this, 'ctc_chat_display_checkout_button' ) );
				break;

			case 'before_payment':
				add_action( 'woocommerce_review_order_before_payment', array( $this, 'ctc_chat_display_checkout_button' ) );
				break;

			case 'after_order_review':
				add_action( 'woocommerce_checkout_after_order_review', array( $this, 'ctc_chat_display_checkout_button' ) );
				break;

			case 'before_order_review':
				add_action( 'woocommerce_checkout_before_order_review', array( $this, 'ctc_chat_display_checkout_button' ), 5 );
				break;

			case 'after_submit':
				add_action( 'woocommerce_review_order_after_submit', array( $this, 'ctc_chat_display_checkout_button' ) );
				break;
		}
	}

	/**
	 * Add hooks for shop pages
	 */
	private function ctc_chat_add_shop_page_hooks() {
		$ctc_chat_position = isset( $this->ctc_chat_settings['ctc_chat_shop_page']['ctc_chat_position'] ) ?
					$this->ctc_chat_settings['ctc_chat_shop_page']['ctc_chat_position'] : 'after_add_to_cart';

		switch ( $ctc_chat_position ) {
			case 'after_add_to_cart':
				add_action( 'woocommerce_after_shop_loop_item', array( $this, 'ctc_chat_display_shop_button' ), 15 );
				break;

			case 'before_add_to_cart':
				add_action( 'woocommerce_after_shop_loop_item', array( $this, 'ctc_chat_display_shop_button' ), 9 );
				break;

			case 'after_price':
				// Hook to woocommerce_after_shop_loop_item_title with priority 11 (after price which is at 10).
				add_action( 'woocommerce_after_shop_loop_item_title', array( $this, 'ctc_chat_display_shop_button' ), 11 );
				break;

			case 'before_title':
				add_action( 'woocommerce_before_shop_loop_item_title', array( $this, 'ctc_chat_display_shop_button' ), 15 );
				break;
		}
	}

	/**
	 * Display WhatsApp button on single product pages
	 */
	public function ctc_chat_display_single_product_button() {
		global $product;

		// Check if button has already been displayed for this product.
		if ( isset( self::$ctc_chat_buttons_displayed[ 'product_' . $product->get_id() ] ) ) {
			return;
		}

		if ( ! $product ) {
			return;
		}

		// Check if the product should be excluded.
		if ( $this->ctc_chat_is_excluded( $product->get_id() ) ) {
			return;
		}

		// Get the WhatsApp URL with error handling.
		try {
			$ctc_chat_whatsapp_url = $this->ctc_chat_link_generator->ctc_chat_get_product_url( $product->get_id() );
		} catch ( Exception $e ) {
			return;
		}

		if ( empty( $ctc_chat_whatsapp_url ) ) {
			return;
		}

		// Get button position for wrapper class.
		$ctc_chat_position = isset( $this->ctc_chat_settings['ctc_chat_single_product']['ctc_chat_position'] ) ?
					$this->ctc_chat_settings['ctc_chat_single_product']['ctc_chat_position'] : 'below_add_to_cart';
		$ctc_chat_position_class = 'ctc-chat-position-' . str_replace( '_', '-', $ctc_chat_position );

		// Check if catalog mode is active.
		$ctc_chat_catalog_mode = isset( $this->ctc_chat_settings['ctc_chat_advanced']['ctc_chat_catalog_mode'] ) && $this->ctc_chat_settings['ctc_chat_advanced']['ctc_chat_catalog_mode'];
		$ctc_chat_hide_add_to_cart = isset( $this->ctc_chat_settings['ctc_chat_advanced']['ctc_chat_hide_add_to_cart'] ) && $this->ctc_chat_settings['ctc_chat_advanced']['ctc_chat_hide_add_to_cart'];

		if ( $ctc_chat_catalog_mode || $ctc_chat_hide_add_to_cart ) {
			$ctc_chat_position_class .= ' ctc-chat-catalog-mode-button';
		}

		// Wrap button with position-specific container.
		echo '<div class="' . esc_attr( $ctc_chat_position_class ) . '">';
		$this->ctc_chat_render_button( $ctc_chat_whatsapp_url, 'product' );
		echo '</div>';

		// Mark this button as displayed.
		self::$ctc_chat_buttons_displayed[ 'product_' . $product->get_id() ] = true;
	}

	/**
	 * Display WhatsApp button on shop pages
	 */
	public function ctc_chat_display_shop_button() {
		global $product;

		// Check if button has already been displayed for this product.
		if ( isset( self::$ctc_chat_buttons_displayed[ 'shop_' . $product->get_id() ] ) ) {
			return;
		}

		if ( ! $product ) {
			return;
		}

		// Check if the shop page itself is excluded (without product ID).
		if ( $this->ctc_chat_is_excluded() ) {
			return;
		}

		// Check if the specific product should be excluded.
		if ( $this->ctc_chat_is_excluded( $product->get_id() ) ) {
			return;
		}

		// Get the WhatsApp URL (product-specific on shop pages).
		$ctc_chat_whatsapp_url = $this->ctc_chat_link_generator->ctc_chat_get_product_url( $product->get_id() );

		if ( empty( $ctc_chat_whatsapp_url ) ) {
			return;
		}

		// Display the button.
		$this->ctc_chat_render_button( $ctc_chat_whatsapp_url, 'shop' );

		// Mark this button as displayed.
		self::$ctc_chat_buttons_displayed[ 'shop_' . $product->get_id() ] = true;
	}

	/**
	 * Maybe display cart button based on settings
	 */
	public function ctc_chat_maybe_display_cart_button() {
		// Check if plugin is enabled.
		$ctc_chat_plugin_enabled = isset( $this->ctc_chat_settings['ctc_chat_plugin_enabled'] ) ? $this->ctc_chat_settings['ctc_chat_plugin_enabled'] : true;
		if ( ! $ctc_chat_plugin_enabled ) {
			return;
		}

		// Check if cart buttons are enabled.
		if ( ! isset( $this->ctc_chat_settings['ctc_chat_cart_page']['ctc_chat_enabled'] ) || ! $this->ctc_chat_settings['ctc_chat_cart_page']['ctc_chat_enabled'] ) {
			return;
		}

		// Only display on cart page.
		if ( ! is_cart() ) {
			return;
		}

		$this->ctc_chat_display_cart_button();
	}

	/**
	 * Maybe display checkout button based on settings
	 */
	public function ctc_chat_maybe_display_checkout_button() {
		// Check if plugin is enabled.
		$ctc_chat_plugin_enabled = isset( $this->ctc_chat_settings['ctc_chat_plugin_enabled'] ) ? $this->ctc_chat_settings['ctc_chat_plugin_enabled'] : true;
		if ( ! $ctc_chat_plugin_enabled ) {
			return;
		}

		// Check if checkout buttons are enabled.
		if ( ! isset( $this->ctc_chat_settings['ctc_chat_checkout_page']['ctc_chat_enabled'] ) || ! $this->ctc_chat_settings['ctc_chat_checkout_page']['ctc_chat_enabled'] ) {
			return;
		}

		// Only display on checkout page.
		if ( ! is_checkout() ) {
			return;
		}

		$this->ctc_chat_display_checkout_button();
	}

	/**
	 * Display WhatsApp button on cart page
	 */
	public function ctc_chat_display_cart_button() {
		// Check if button has already been displayed for cart.
		if ( isset( self::$ctc_chat_buttons_displayed['cart'] ) ) {
			return;
		}

		// Check if WooCommerce is available.
		if ( ! function_exists( 'WC' ) || ! WC() ) {
			return;
		}

		// Check if the page should be excluded.
		if ( $this->ctc_chat_is_excluded() ) {
			return;
		}

		// Get the WhatsApp URL with error handling.
		try {
			$ctc_chat_whatsapp_url = $this->ctc_chat_link_generator->ctc_chat_get_cart_url();
		} catch ( Exception $e ) {
			return;
		}

		// If no URL, try to create a basic one with just the number.
		if ( empty( $ctc_chat_whatsapp_url ) ) {
			$ctc_chat_whatsapp_number = $this->ctc_chat_link_generator->ctc_chat_get_whatsapp_number();
			if ( ! empty( $ctc_chat_whatsapp_number ) ) {
				$ctc_chat_whatsapp_number = preg_replace( '/[^0-9]/', '', $ctc_chat_whatsapp_number );
				$ctc_chat_default_message = esc_html__( 'Hello! I need help with my cart on your website.', 'aicoso-click-to-chat' );
				$ctc_chat_whatsapp_url = 'https://wa.me/' . $ctc_chat_whatsapp_number . '?text=' . rawurlencode( $ctc_chat_default_message );
			}
		}

		if ( empty( $ctc_chat_whatsapp_url ) ) {
			return;
		}

		// Display the button with proper wrapper.
		echo '<div class="ctc-chat-cart-button-container">';
		$this->ctc_chat_render_button( $ctc_chat_whatsapp_url, 'cart' );
		echo '</div>';

		// Mark this button as displayed.
		self::$ctc_chat_buttons_displayed['cart'] = true;
	}

	/**
	 * Display WhatsApp button on checkout page
	 */
	public function ctc_chat_display_checkout_button() {
		// Check if button has already been displayed for checkout.
		if ( isset( self::$ctc_chat_buttons_displayed['checkout'] ) ) {
			return;
		}

		// Check if WooCommerce is available.
		if ( ! function_exists( 'WC' ) || ! WC() ) {
			return;
		}

		// Check if the page should be excluded.
		if ( $this->ctc_chat_is_excluded() ) {
			return;
		}

		// Get the WhatsApp URL with error handling.
		try {
			$ctc_chat_whatsapp_url = $this->ctc_chat_link_generator->ctc_chat_get_checkout_url();
		} catch ( Exception $e ) {
			return;
		}

		// If no URL, try to create a basic one with just the number.
		if ( empty( $ctc_chat_whatsapp_url ) ) {
			$ctc_chat_whatsapp_number = $this->ctc_chat_link_generator->ctc_chat_get_whatsapp_number();
			if ( ! empty( $ctc_chat_whatsapp_number ) ) {
				$ctc_chat_whatsapp_number = preg_replace( '/[^0-9]/', '', $ctc_chat_whatsapp_number );
				$ctc_chat_default_message = esc_html__( 'Hello! I need help with my checkout on your website.', 'aicoso-click-to-chat' );
				$ctc_chat_whatsapp_url = 'https://wa.me/' . $ctc_chat_whatsapp_number . '?text=' . rawurlencode( $ctc_chat_default_message );
			}
		}

		if ( empty( $ctc_chat_whatsapp_url ) ) {
			return;
		}

		// Display the button with proper wrapper.
		echo '<div class="ctc-chat-checkout-button-container">';
		$this->ctc_chat_render_button( $ctc_chat_whatsapp_url, 'checkout' );
		echo '</div>';

		// Mark this button as displayed.
		self::$ctc_chat_buttons_displayed['checkout'] = true;
	}

	/**
	 * Display WhatsApp button on thank you page
	 *
	 * @param int $ctc_chat_order_id The order ID.
	 */
	public function ctc_chat_display_thankyou_button( $ctc_chat_order_id ) {
		// Check if button has already been displayed for this order.
		if ( isset( self::$ctc_chat_buttons_displayed[ 'thankyou_' . $ctc_chat_order_id ] ) ) {
			return;
		}

		// Check if the page should be excluded.
		if ( $this->ctc_chat_is_excluded() ) {
			return;
		}

		// Get the WhatsApp URL.
		$ctc_chat_whatsapp_url = $this->ctc_chat_link_generator->ctc_chat_get_thankyou_url( $ctc_chat_order_id );

		if ( empty( $ctc_chat_whatsapp_url ) ) {
			return;
		}

		// Display the button.
		echo '<div class="ctc-chat-thankyou-button-container">';
		$this->ctc_chat_render_button( $ctc_chat_whatsapp_url, 'thankyou' );
		echo '</div>';

		// Mark this button as displayed.
		self::$ctc_chat_buttons_displayed[ 'thankyou_' . $ctc_chat_order_id ] = true;
	}

	/**
	 * Display floating WhatsApp button
	 */
	public function ctc_chat_display_floating_button() {
		// Check if button has already been displayed.
		if ( isset( self::$ctc_chat_buttons_displayed['floating'] ) ) {
			return;
		}

		// Check if the page should be excluded.
		if ( $this->ctc_chat_is_excluded() ) {
			return;
		}

		// Get the WhatsApp URL.
		$ctc_chat_whatsapp_url = $this->ctc_chat_link_generator->ctc_chat_get_floating_url();

		if ( empty( $ctc_chat_whatsapp_url ) ) {
			return;
		}

		// Get the position.
		$ctc_chat_position = isset( $this->ctc_chat_settings['ctc_chat_floating_button']['ctc_chat_position'] ) ?
				   $this->ctc_chat_settings['ctc_chat_floating_button']['ctc_chat_position'] : 'bottom_right';

		// Define position classes.
		$ctc_chat_position_class = 'ctc-chat-floating-' . $ctc_chat_position;

		// Display the button.
		echo '<div class="ctc-chat-floating-button-container ' . esc_attr( $ctc_chat_position_class ) . '">';
		$this->ctc_chat_render_button( $ctc_chat_whatsapp_url, 'floating' );
		echo '</div>';

		// Mark this button as displayed.
		self::$ctc_chat_buttons_displayed['floating'] = true;
	}

	/**
	 * Render the WhatsApp button
	 *
	 * @param string $ctc_chat_url  The WhatsApp URL.
	 * @param string $ctc_chat_type The button type (for CSS classes).
	 */
	private function ctc_chat_render_button( $ctc_chat_url, $ctc_chat_type = 'default' ) {
		// Get button settings.
		$ctc_chat_button_text = isset( $this->ctc_chat_settings['ctc_chat_button_settings']['ctc_chat_text'] ) ?
					  $this->ctc_chat_settings['ctc_chat_button_settings']['ctc_chat_text'] : esc_html__( 'Order via WhatsApp', 'aicoso-click-to-chat' );

		$ctc_chat_show_icon = isset( $this->ctc_chat_settings['ctc_chat_button_settings']['ctc_chat_icon'] ) ?
					$this->ctc_chat_settings['ctc_chat_button_settings']['ctc_chat_icon'] : true;

		$ctc_chat_bg_color = isset( $this->ctc_chat_settings['ctc_chat_button_settings']['ctc_chat_bg_color'] ) ?
				   $this->ctc_chat_settings['ctc_chat_button_settings']['ctc_chat_bg_color'] : '#25D366';

		$ctc_chat_text_color = isset( $this->ctc_chat_settings['ctc_chat_button_settings']['ctc_chat_text_color'] ) ?
					 $this->ctc_chat_settings['ctc_chat_button_settings']['ctc_chat_text_color'] : '#ffffff';

		// Inline styles.
		$ctc_chat_button_style = 'background-color: ' . esc_attr( $ctc_chat_bg_color ) . '; color: ' . esc_attr( $ctc_chat_text_color ) . ';';

		// Button classes.
		$ctc_chat_button_classes = array(
			'ctc-chat-whatsapp-button',
			'ctc-chat-button-' . $ctc_chat_type,
		);

		if ( $ctc_chat_show_icon ) {
			$ctc_chat_button_classes[] = 'ctc-chat-button-with-icon';
		}

		$ctc_chat_class_attr = implode( ' ', $ctc_chat_button_classes );

		// Output the button HTML.
		?>
		<a href="<?php echo esc_url( $ctc_chat_url ); ?>" class="<?php echo esc_attr( $ctc_chat_class_attr ); ?>" style="<?php echo esc_attr( $ctc_chat_button_style ); ?>" target="_blank" rel="noopener">
			<?php if ( $ctc_chat_show_icon ) : ?>
				<span class="ctc-chat-whatsapp-icon">
					<svg viewBox="0 0 24 24" width="24" height="24">
						<path fill="currentColor" d="M17.498 14.382c-.301-.15-1.767-.867-2.04-.966-.273-.101-.473-.15-.673.15-.197.295-.771.964-.944 1.162-.175.195-.349.21-.646.075-.3-.15-1.263-.465-2.403-1.485-.888-.795-1.484-1.77-1.66-2.07-.174-.3-.019-.465.13-.615.136-.135.301-.345.451-.523.146-.181.194-.301.297-.496.1-.21.049-.375-.025-.524-.075-.15-.672-1.62-.922-2.206-.24-.584-.487-.51-.672-.51-.172-.015-.371-.015-.571-.015-.2 0-.523.074-.797.359-.273.3-1.045 1.02-1.045 2.475s1.07 2.865 1.219 3.075c.149.195 2.105 3.195 5.1 4.485.714.3 1.27.48 1.704.629.714.227 1.365.195 1.88.121.574-.091 1.767-.721 2.016-1.426.255-.705.255-1.29.18-1.425-.074-.135-.27-.21-.57-.345m-5.446 7.443h-.016c-1.77 0-3.524-.48-5.055-1.38l-.36-.214-3.75.975 1.005-3.645-.239-.375c-.99-1.576-1.516-3.391-1.516-5.26 0-5.445 4.455-9.885 9.942-9.885 2.654 0 5.145 1.035 7.021 2.91 1.875 1.859 2.909 4.35 2.909 6.99-.004 5.444-4.46 9.885-9.935 9.885M20.52 3.449C18.24 1.245 15.24 0 12.045 0 5.463 0 .104 5.334.101 11.893c0 2.096.549 4.14 1.595 5.945L0 24l6.335-1.652c1.746.943 3.71 1.444 5.71 1.447h.006c6.585 0 11.946-5.336 11.949-11.896 0-3.176-1.24-6.165-3.495-8.411"/>
					</svg>
				</span>
			<?php endif; ?>
			<span class="ctc-chat-button-text"><?php echo esc_html( $ctc_chat_button_text ); ?></span>
		</a>
		<?php
	}

	/**
	 * Check if the current page or product should be excluded
	 *
	 * @param int $ctc_chat_product_id Optional product ID.
	 * @return bool True if should be excluded, false otherwise.
	 */
	private function ctc_chat_is_excluded( $ctc_chat_product_id = null ) {
		// If exclusions not set or empty, nothing is excluded.
		if ( empty( $this->ctc_chat_settings['ctc_chat_exclusions'] ) ) {
			return false;
		}

		// Check specific product exclusions.
		if ( $ctc_chat_product_id && isset( $this->ctc_chat_settings['ctc_chat_exclusions']['ctc_chat_products'] ) && ! empty( $this->ctc_chat_settings['ctc_chat_exclusions']['ctc_chat_products'] ) ) {
			if ( in_array( $ctc_chat_product_id, $this->ctc_chat_settings['ctc_chat_exclusions']['ctc_chat_products'], true ) ) {
				return true;
			}
		}

		// Check product category exclusions.
		if ( $ctc_chat_product_id && isset( $this->ctc_chat_settings['ctc_chat_exclusions']['ctc_chat_categories'] ) && ! empty( $this->ctc_chat_settings['ctc_chat_exclusions']['ctc_chat_categories'] ) ) {
			$ctc_chat_product_categories = wp_get_post_terms( $ctc_chat_product_id, 'product_cat', array( 'fields' => 'ids' ) );

			if ( is_array( $ctc_chat_product_categories ) ) {
				$ctc_chat_excluded_categories = $this->ctc_chat_settings['ctc_chat_exclusions']['ctc_chat_categories'];

				// Check if product is in any excluded category.
				if ( array_intersect( $ctc_chat_product_categories, $ctc_chat_excluded_categories ) ) {
					return true;
				}
			}
		}

		// Check product tag exclusions.
		if ( $ctc_chat_product_id && isset( $this->ctc_chat_settings['ctc_chat_exclusions']['ctc_chat_tags'] ) && ! empty( $this->ctc_chat_settings['ctc_chat_exclusions']['ctc_chat_tags'] ) ) {
			$ctc_chat_product_tags = wp_get_post_terms( $ctc_chat_product_id, 'product_tag', array( 'fields' => 'ids' ) );

			if ( is_array( $ctc_chat_product_tags ) ) {
				$ctc_chat_excluded_tags = $this->ctc_chat_settings['ctc_chat_exclusions']['ctc_chat_tags'];

				// Check if product has any excluded tag.
				if ( array_intersect( $ctc_chat_product_tags, $ctc_chat_excluded_tags ) ) {
					return true;
				}
			}
		}

		// Get current page/post ID.
		$ctc_chat_current_id = get_the_ID();

		// Special handling for WooCommerce Shop page.
		if ( is_shop() ) {
			$ctc_chat_shop_page_id = wc_get_page_id( 'shop' );
			if ( $ctc_chat_shop_page_id && isset( $this->ctc_chat_settings['ctc_chat_exclusions']['ctc_chat_pages'] ) && ! empty( $this->ctc_chat_settings['ctc_chat_exclusions']['ctc_chat_pages'] ) ) {
				if ( in_array( $ctc_chat_shop_page_id, $this->ctc_chat_settings['ctc_chat_exclusions']['ctc_chat_pages'], true ) ) {
					return true;
				}
			}
		}

		if ( ! $ctc_chat_current_id ) {
			return false;
		}

		// Check page exclusions.
		if ( is_page() && isset( $this->ctc_chat_settings['ctc_chat_exclusions']['ctc_chat_pages'] ) && ! empty( $this->ctc_chat_settings['ctc_chat_exclusions']['ctc_chat_pages'] ) ) {
			if ( in_array( $ctc_chat_current_id, $this->ctc_chat_settings['ctc_chat_exclusions']['ctc_chat_pages'], true ) ) {
				return true;
			}
		}

		// Check post exclusions.
		if ( is_single() && ! is_product() && isset( $this->ctc_chat_settings['ctc_chat_exclusions']['ctc_chat_posts'] ) && ! empty( $this->ctc_chat_settings['ctc_chat_exclusions']['ctc_chat_posts'] ) ) {
			if ( in_array( $ctc_chat_current_id, $this->ctc_chat_settings['ctc_chat_exclusions']['ctc_chat_posts'], true ) ) {
				return true;
			}
		}

		return false;
	}
}
