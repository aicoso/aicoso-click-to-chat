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
	private $settings;

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->settings = get_option( 'ctc_chat_settings', array() );

		// Initialize hooks.
		$this->init_hooks();
	}

	/**
	 * Initialize hooks
	 */
	private function init_hooks() {
		add_action( 'init', array( $this, 'maybe_set_visitor_cookie' ), 1 );

		// Handle advanced options for hiding WooCommerce buttons FIRST (before other hooks).
		add_action( 'init', array( $this, 'handle_advanced_options' ), 999 );

		// Enqueue scripts and styles.
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_assets' ) );

		// Enqueue block-specific scripts for cart and checkout.
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_block_scripts' ) );

		// AJAX handlers for variation URLs.
		add_action( 'wp_ajax_ctc_chat_get_variation_url', array( $this, 'ajax_get_variation_url' ) );
		add_action( 'wp_ajax_nopriv_ctc_chat_get_variation_url', array( $this, 'ajax_get_variation_url' ) );

		// AJAX handlers for back-in-stock alert URLs.
		add_action( 'wp_ajax_ctc_chat_get_stock_url', array( $this, 'ajax_get_stock_url' ) );
		add_action( 'wp_ajax_nopriv_ctc_chat_get_stock_url', array( $this, 'ajax_get_stock_url' ) );

		// Output Custom CSS from Advanced settings.
		add_action( 'wp_head', array( $this, 'output_custom_css' ), 99 );

		// Render desktop QR modal in footer if enabled.
		add_action( 'wp_footer', array( $this, 'render_qr_modal' ) );

		// Render GDPR Privacy Consent modal in footer if enabled.
		add_action( 'wp_footer', array( $this, 'render_privacy_prompt' ) );
	}

	/**
	 * Enqueue public scripts and styles
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function enqueue_assets() {
		// Only enqueue assets when needed.
		if ( ! $this->should_load_assets() ) {
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

		$script_deps = array( 'jquery' );
		$qr_settings = isset( $this->settings['qr_modal'] ) ? $this->settings['qr_modal'] : array();
		$qr_enabled  = ! isset( $qr_settings['enabled'] ) ? true : ! empty( $qr_settings['enabled'] );

		// Desktop QR code generator script.
		if ( $qr_enabled ) {
			wp_enqueue_script(
				'ctc-chat-qrcode',
				CTC_CHAT_PLUGIN_URL . 'public/js/qrcode.min.js',
				array(),
				CTC_CHAT_VERSION,
				true
			);
			$script_deps[] = 'ctc-chat-qrcode';
		}

		// Register and enqueue JavaScript.
		wp_enqueue_script(
			'ctc-chat-public-script',
			CTC_CHAT_PLUGIN_URL . 'public/js/public.js',
			$script_deps,
			CTC_CHAT_VERSION,
			true
		);

		// Localize script with data.
		$localize_data = array(
			'ajaxurl' => admin_url( 'admin-ajax.php' ),
			'nonce'   => wp_create_nonce( 'ctc_chat_public_nonce' ),
		);

		// Check if abandonment nudge is active on cart or checkout.
		$nudge_settings = isset( $this->settings['cart_checkout_nudge'] ) ? $this->settings['cart_checkout_nudge'] : array();
		if ( ! empty( $nudge_settings['enabled'] ) && ( is_cart() || ( is_checkout() && ! is_wc_endpoint_url( 'order-received' ) ) ) ) {
			require_once CTC_CHAT_PLUGIN_DIR . 'includes/class-ctc-chat-whatsapp-link-generator.php';
			$link_generator = new CTC_Chat_WhatsApp_Link_Generator();
			$nudge_url      = $link_generator->get_cart_url();

			if ( empty( $nudge_url ) ) {
				$nudge_url = $link_generator->get_floating_url();
			}
			if ( empty( $nudge_url ) ) {
				$wa_num = $link_generator->get_whatsapp_number();
				if ( ! empty( $wa_num ) ) {
					$nudge_url = 'https://wa.me/' . preg_replace( '/[^0-9]/', '', $wa_num );
				}
			}

			$cart_total = '';
			if ( function_exists( 'WC' ) && WC()->cart && method_exists( WC()->cart, 'get_total' ) && ! WC()->cart->is_empty() ) {
				$cart_total = wp_strip_all_tags( wc_price( WC()->cart->get_total( 'edit' ) ) );
			}

			if ( ! empty( $nudge_url ) ) {
				$localize_data['nudge'] = array(
					'enabled'     => true,
					'trigger'     => isset( $nudge_settings['trigger'] ) ? $nudge_settings['trigger'] : 'both',
					'delay'       => isset( $nudge_settings['delay'] ) ? max( 3, absint( $nudge_settings['delay'] ) ) : 20,
					'frequency'   => isset( $nudge_settings['frequency'] ) ? $nudge_settings['frequency'] : 'reappear',
					'title'       => ! empty( $nudge_settings['title'] ) ? esc_html( $nudge_settings['title'] ) : esc_html__( 'Need help with your order?', 'aicoso-click-to-chat' ),
					'message'     => ! empty( $nudge_settings['message'] ) ? esc_html( $nudge_settings['message'] ) : esc_html__( 'Have questions about payment, shipping, or need assistance? Chat with us on WhatsApp!', 'aicoso-click-to-chat' ),
					'button_text' => ! empty( $nudge_settings['button_text'] ) ? esc_html( $nudge_settings['button_text'] ) : esc_html__( 'Chat with Support 💬', 'aicoso-click-to-chat' ),
					'cart_total'  => $cart_total,
					'url'         => $nudge_url,
				);
			}
		}

		// Back in stock notification settings.
		$stock_settings = isset( $this->settings['back_in_stock'] ) ? $this->settings['back_in_stock'] : array();
		$localize_data['stock'] = array(
			'enabled'     => ! empty( $stock_settings['enabled'] ),
			'button_text' => ! empty( $stock_settings['button_text'] ) ? esc_html( $stock_settings['button_text'] ) : esc_html__( 'Notify Me on WhatsApp 🔔', 'aicoso-click-to-chat' ),
			'bg_color'    => ! empty( $stock_settings['bg_color'] ) ? sanitize_hex_color( $stock_settings['bg_color'] ) : '#ff9800',
			'text_color'  => ! empty( $stock_settings['text_color'] ) ? sanitize_hex_color( $stock_settings['text_color'] ) : '#ffffff',
		);

		// Desktop QR modal settings.
		$localize_data['qr_modal'] = array(
			'enabled'       => $qr_enabled,
			'title'         => ! empty( $qr_settings['title'] ) ? esc_html( $qr_settings['title'] ) : esc_html__( 'Scan to Chat on WhatsApp', 'aicoso-click-to-chat' ),
			'description'   => ! empty( $qr_settings['description'] ) ? esc_html( $qr_settings['description'] ) : esc_html__( 'Point your phone camera or WhatsApp QR scanner at this code to start chatting instantly.', 'aicoso-click-to-chat' ),
			'show_web_link' => ! isset( $qr_settings['show_web_link'] ) || ! empty( $qr_settings['show_web_link'] ),
			'web_link_text' => esc_html__( 'Or continue with WhatsApp Web on this computer →', 'aicoso-click-to-chat' ),
		);

		// GDPR & Privacy compliance settings.
		$privacy_settings = isset( $this->settings['privacy_compliance'] ) ? $this->settings['privacy_compliance'] : array();
		$privacy_enabled  = ! empty( $privacy_settings['enabled'] );
		$policy_url       = ! empty( $privacy_settings['custom_policy_url'] ) ? esc_url( $privacy_settings['custom_policy_url'] ) : '';
		if ( empty( $policy_url ) && function_exists( 'get_privacy_policy_url' ) ) {
			$policy_url = esc_url( get_privacy_policy_url() );
		}
		$link_text        = ! empty( $privacy_settings['link_text'] ) ? esc_html( $privacy_settings['link_text'] ) : esc_html__( 'Privacy Policy', 'aicoso-click-to-chat' );
		$policy_link_html = $policy_url ? '<a href="' . $policy_url . '" target="_blank" rel="noopener noreferrer" class="ctc-privacy-link">' . $link_text . '</a>' : $link_text;
		$notice_template  = ! empty( $privacy_settings['notice_text'] ) ? $privacy_settings['notice_text'] : esc_html__( 'By chatting with us on WhatsApp, you agree to our {privacy_policy_link} and consent to communication regarding your inquiry.', 'aicoso-click-to-chat' );
		$notice_html      = str_replace( '{privacy_policy_link}', $policy_link_html, esc_html( $notice_template ) );

		$localize_data['privacy'] = array(
			'enabled'       => $privacy_enabled,
			'consent_mode'  => $privacy_settings['consent_mode'] ?? 'prompt',
			'notice_html'   => $notice_html,
			'policy_url'    => $policy_url,
			'agree_button'  => ! empty( $privacy_settings['agree_button'] ) ? esc_html( $privacy_settings['agree_button'] ) : esc_html__( 'Accept & Chat', 'aicoso-click-to-chat' ),
			'cancel_button' => ! empty( $privacy_settings['cancel_button'] ) ? esc_html( $privacy_settings['cancel_button'] ) : esc_html__( 'Cancel', 'aicoso-click-to-chat' ),
		);

		wp_localize_script( 'ctc-chat-public-script', 'ctc_chat_public', $localize_data );

		if ( ctc_chat_analytics_is_enabled() ) {
			wp_enqueue_script(
				'ctc-chat-tracking',
				CTC_CHAT_PLUGIN_URL . 'public/js/tracking.js',
				array(),
				CTC_CHAT_VERSION,
				true
			);

			wp_localize_script(
				'ctc-chat-tracking',
				'ctc_chat_tracking',
				array(
					'ajaxurl' => admin_url( 'admin-ajax.php' ),
					'nonce'   => wp_create_nonce( 'ctc_chat_track_click' ),
					'enabled' => true,
				)
			);
		}
	}

	/**
	 * Set anonymous visitor cookie for analytics dedupe.
	 */
	public function maybe_set_visitor_cookie() {
		if ( is_admin() || ! ctc_chat_analytics_is_enabled() ) {
			return;
		}

		if ( ! empty( $_COOKIE['ctc_vid'] ) && preg_match( '/^[a-f0-9]{32}$/', sanitize_text_field( wp_unslash( $_COOKIE['ctc_vid'] ) ) ) ) {
			return;
		}

		$settings = get_option( 'ctc_chat_settings', array() );
		$ttl_days = isset( $settings['analytics']['visitor_cookie_ttl'] ) ? absint( $settings['analytics']['visitor_cookie_ttl'] ) : 30;
		$ttl_days = max( 1, min( 365, $ttl_days ) );

		$cookie_value = bin2hex( random_bytes( 16 ) );
		$secure       = is_ssl();

		setcookie(
			'ctc_vid',
			$cookie_value,
			array(
				'expires'  => time() + ( $ttl_days * DAY_IN_SECONDS ),
				'path'     => COOKIEPATH ? COOKIEPATH : '/',
				'domain'   => COOKIE_DOMAIN,
				'secure'   => $secure,
				'httponly' => true,
				'samesite' => 'Lax',
			)
		);

		$_COOKIE['ctc_vid'] = $cookie_value;
	}

	/**
	 * Check if assets should be loaded
	 *
	 * @return bool True if assets should be loaded, false otherwise.
	 */
	private function should_load_assets() {
		// Always load if floating button is enabled.
		if ( isset( $this->settings['floating_button']['enabled'] ) && $this->settings['floating_button']['enabled'] ) {
			return true;
		}

		// Load on shop pages if enabled.
		if ( ( is_shop() || is_product_category() || is_product_tag() ) &&
			 isset( $this->settings['shop_page']['enabled'] ) &&
			 $this->settings['shop_page']['enabled'] ) {
			return true;
		}

		// Load on single product pages if enabled or if back in stock alert is enabled.
		if ( is_product() &&
			 ( ( isset( $this->settings['single_product']['enabled'] ) && $this->settings['single_product']['enabled'] ) ||
			   ! empty( $this->settings['back_in_stock']['enabled'] ) ) ) {
			return true;
		}

		// Load on cart page if enabled.
		if ( is_cart() &&
			 isset( $this->settings['cart_page']['enabled'] ) &&
			 $this->settings['cart_page']['enabled'] ) {
			return true;
		}

		// Load on checkout page if enabled.
		if ( is_checkout() && ! is_wc_endpoint_url( 'order-received' ) &&
			 isset( $this->settings['checkout_page']['enabled'] ) &&
			 $this->settings['checkout_page']['enabled'] ) {
			return true;
		}

		// Load on thank you page if enabled.
		if ( is_wc_endpoint_url( 'order-received' ) &&
			 ! empty( $this->settings['thankyou_page']['enabled'] ) ) {
			return true;
		}

		// Load on My Account pages if order tracking is enabled.
		if ( function_exists( 'is_account_page' ) && is_account_page() ) {
			if ( ! empty( $this->settings['thankyou_page']['my_account_orders'] ) ||
				 ! empty( $this->settings['thankyou_page']['my_account_view_order'] ) ) {
				return true;
			}
		}

		// Load on cart or checkout if abandonment nudge is enabled.
		if ( ( is_cart() || ( is_checkout() && ! is_wc_endpoint_url( 'order-received' ) ) ) &&
			 ! empty( $this->settings['cart_checkout_nudge']['enabled'] ) ) {
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
	 * Enqueue scripts for WooCommerce block-based cart and checkout
	 */
	public function enqueue_block_scripts() {
		// Only load on cart or checkout pages.
		if ( ! is_cart() && ! is_checkout() ) {
			return;
		}

		// Check if plugin is enabled globally.
		$plugin_enabled = isset( $this->settings['plugin_enabled'] ) ? $this->settings['plugin_enabled'] : true;
		if ( ! $plugin_enabled ) {
			return;
		}

		// Check if cart or checkout is enabled.
		$cart_enabled = isset( $this->settings['cart_page']['enabled'] ) && $this->settings['cart_page']['enabled'];
		$checkout_enabled = isset( $this->settings['checkout_page']['enabled'] ) && $this->settings['checkout_page']['enabled'];

		if ( ! $cart_enabled && ! $checkout_enabled ) {
			return;
		}

		// Check if current page is excluded.
		if ( $this->is_page_excluded() ) {
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

		// Get WhatsApp URL.
		$link_generator = new CTC_Chat_WhatsApp_Link_Generator();
		$whatsapp_url = '';

		if ( is_cart() ) {
			$whatsapp_url = $link_generator->get_cart_url();
		} elseif ( is_checkout() ) {
			$whatsapp_url = $link_generator->get_cart_url(); // Uses same URL as cart.
		}

		// If no URL generated, create a basic one.
		if ( empty( $whatsapp_url ) ) {
			$whatsapp_number = isset( $this->settings['whatsapp_numbers'][0]['number'] ) ?
							  $this->settings['whatsapp_numbers'][0]['number'] : '';
			if ( ! empty( $whatsapp_number ) ) {
				$whatsapp_number = preg_replace( '/[^0-9]/', '', $whatsapp_number );
				$default_message = __( 'Hello! I need help with my order.', 'aicoso-click-to-chat' );
				$whatsapp_url = 'https://wa.me/' . $whatsapp_number . '?text=' . rawurlencode( $default_message );
			}
		}

		$cart_context = ctc_chat_get_cart_tracking_context();

		// Localize script with parameters.
		wp_localize_script(
			'ctc-chat-cart-checkout-blocks',
			'ctc_chat_block_params',
			array(
				'cart_enabled'      => $cart_enabled ? '1' : '0',
				'checkout_enabled'  => $checkout_enabled ? '1' : '0',
				'cart_position'     => isset( $this->settings['cart_page']['position'] ) ?
					$this->settings['cart_page']['position'] : 'after_cart_table',
				'checkout_position' => isset( $this->settings['checkout_page']['position'] ) ?
					$this->settings['checkout_page']['position'] : 'after_payment',
				'button_text'       => isset( $this->settings['button_settings']['text'] ) ?
					$this->settings['button_settings']['text'] : __( 'Order via WhatsApp', 'aicoso-click-to-chat' ),
				'bg_color'          => isset( $this->settings['button_settings']['bg_color'] ) ?
					$this->settings['button_settings']['bg_color'] : '#25D366',
				'text_color'        => isset( $this->settings['button_settings']['text_color'] ) ?
					$this->settings['button_settings']['text_color'] : '#ffffff',
				'show_icon'         => isset( $this->settings['button_settings']['icon'] ) && $this->settings['button_settings']['icon'] ? '1' : '0',
				'whatsapp_url'      => $whatsapp_url,
				'template_type'     => 'cart_checkout',
				'number_id'         => $link_generator->get_number_id(),
				'cart_item_count'   => isset( $cart_context['cart_item_count'] ) ? absint( $cart_context['cart_item_count'] ) : 0,
				'cart_total'        => isset( $cart_context['cart_total'] ) ? (float) $cart_context['cart_total'] : 0,
				'cart_currency'     => isset( $cart_context['cart_currency'] ) ? $cart_context['cart_currency'] : '',
			)
		);
	}

	/**
	 * Handle advanced options for hiding WooCommerce buttons
	 */
	public function handle_advanced_options() {
		// Check if plugin is enabled.
		$plugin_enabled = isset( $this->settings['plugin_enabled'] ) ? $this->settings['plugin_enabled'] : true;
		if ( ! $plugin_enabled ) {
			return;
		}

		// Check if any advanced options are enabled.
		if ( ! isset( $this->settings['advanced'] ) ) {
			return;
		}

		$advanced = $this->settings['advanced'];

		// Check for catalog mode first (overrides individual settings).
		if ( isset( $advanced['catalog_mode'] ) && $advanced['catalog_mode'] ) {
			$this->hide_all_purchase_buttons();
			return;
		}

		// Hide Add to Cart buttons.
		if ( isset( $advanced['hide_add_to_cart'] ) && $advanced['hide_add_to_cart'] ) {
			$this->hide_add_to_cart_buttons();
		}

		// Hide Proceed to Checkout button.
		if ( isset( $advanced['hide_proceed_checkout'] ) && $advanced['hide_proceed_checkout'] ) {
			$this->hide_proceed_checkout_button();
		}

		// Hide Place Order button.
		if ( isset( $advanced['hide_place_order'] ) && $advanced['hide_place_order'] ) {
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
		// Remove add to cart buttons globally first.
		remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_add_to_cart', 30 );
		remove_action( 'woocommerce_after_shop_loop_item', 'woocommerce_template_loop_add_to_cart', 10 );

		// Add CSS to hide any remaining add to cart buttons.
		add_action( 'wp_enqueue_scripts', array( $this, 'hide_add_to_cart_css' ) );
	}

	/**
	 * Hide Proceed to Checkout button
	 */
	private function hide_proceed_checkout_button() {
		// Remove proceed to checkout button.
		remove_action( 'woocommerce_proceed_to_checkout', 'woocommerce_button_proceed_to_checkout', 20 );

		// Add CSS to hide the button on all pages.
		add_action( 'wp_enqueue_scripts', array( $this, 'hide_proceed_checkout_css' ) );
	}

	/**
	 * Hide Place Order button
	 */
	private function hide_place_order_button() {
		// Add CSS to hide the place order button on all pages.
		add_action( 'wp_enqueue_scripts', array( $this, 'hide_place_order_css' ) );

		// Optionally prevent form submission.
		add_action( 'wp_enqueue_scripts', array( $this, 'disable_checkout_form_submission' ) );
	}

	/**
	 * Add CSS to hide Add to Cart buttons
	 */
	public function hide_add_to_cart_css() {
		$css = '
			/* Hide WooCommerce Add to Cart buttons only, NOT WhatsApp buttons */
			.single_add_to_cart_button:not(.ctc-chat-whatsapp-button),
			.add_to_cart_button:not(.ctc-chat-whatsapp-button),
			.product_type_simple.add_to_cart_button,
			.product_type_variable.add_to_cart_button,
			.product_type_grouped.add_to_cart_button,
			.product_type_external.add_to_cart_button,
			.ajax_add_to_cart,
			form.cart button.single_add_to_cart_button:not(.ctc-chat-whatsapp-button) {
				display: none !important;
			}

			/* Ensure WhatsApp buttons are visible */
			.ctc-chat-whatsapp-button,
			a.ctc-chat-whatsapp-button {
				display: inline-flex !important;
			}
		';

		wp_add_inline_style( 'ctc-chat-public-styles', $css );
	}

	/**
	 * Add CSS to hide Proceed to Checkout button
	 */
	public function hide_proceed_checkout_css() {
		$css = '
			/* Hide WooCommerce checkout button only, NOT WhatsApp buttons */
			.wc-proceed-to-checkout a.checkout-button:not(.ctc-chat-whatsapp-button),
			.wc-proceed-to-checkout .checkout-button:not(.ctc-chat-whatsapp-button),
			.wp-block-woocommerce-proceed-to-checkout-block {
				display: none !important;
			}

			/* Ensure WhatsApp buttons are visible */
			.ctc-chat-whatsapp-button,
			a.ctc-chat-whatsapp-button {
				display: inline-flex !important;
			}
		';

		wp_add_inline_style( 'ctc-chat-public-styles', $css );
	}

	/**
	 * Add CSS to hide Place Order button
	 */
	public function hide_place_order_css() {
		$css = '
			/* Hide WooCommerce place order button only, NOT WhatsApp buttons */
			#place_order:not(.ctc-chat-whatsapp-button),
			.woocommerce-checkout-payment button#place_order:not(.ctc-chat-whatsapp-button),
			.wp-block-woocommerce-checkout-actions-block button:not(.ctc-chat-whatsapp-button) {
				display: none !important;
			}

			/* Ensure WhatsApp buttons are visible */
			.ctc-chat-whatsapp-button,
			a.ctc-chat-whatsapp-button {
				display: inline-flex !important;
			}
		';

		wp_add_inline_style( 'ctc-chat-public-styles', $css );
	}

	/**
	 * Disable checkout form submission
	 */
	public function disable_checkout_form_submission() {
		if ( is_checkout() ) {
			$script = "
				jQuery(document).ready(function($) {
					// Disable form submission.
					$('form.checkout').on('submit', function(e) {
						e.preventDefault();
						return false;
					});
				});
			";

			wp_add_inline_script( 'ctc-chat-public-script', $script );
		}
	}

	/**
	 * Check if the current page is excluded
	 *
	 * @return bool True if page is excluded, false otherwise
	 */
	private function is_page_excluded() {
		// If exclusions not set or empty, nothing is excluded.
		if ( empty( $this->settings['exclusions'] ) ) {
			return false;
		}

		// Get current page ID.
		$current_id = get_the_ID();

		// Special handling for WooCommerce Cart page.
		if ( is_cart() ) {
			$cart_page_id = wc_get_page_id( 'cart' );
			if ( $cart_page_id && isset( $this->settings['exclusions']['pages'] ) && ! empty( $this->settings['exclusions']['pages'] ) ) {
				if ( in_array( $cart_page_id, $this->settings['exclusions']['pages'], true ) ) {
					return true;
				}
			}
		}

		// Special handling for WooCommerce Checkout page.
		if ( is_checkout() ) {
			$checkout_page_id = wc_get_page_id( 'checkout' );
			if ( $checkout_page_id && isset( $this->settings['exclusions']['pages'] ) && ! empty( $this->settings['exclusions']['pages'] ) ) {
				if ( in_array( $checkout_page_id, $this->settings['exclusions']['pages'], true ) ) {
					return true;
				}
			}
		}

		// Check general page exclusions if we have a current ID.
		if ( $current_id && isset( $this->settings['exclusions']['pages'] ) && ! empty( $this->settings['exclusions']['pages'] ) ) {
			if ( in_array( $current_id, $this->settings['exclusions']['pages'], true ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * AJAX handler to get variation-specific WhatsApp URL
	 */
	public function ajax_get_variation_url() {
		// Check nonce.
		if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'ctc_chat_public_nonce' ) ) {
			wp_send_json_error( array( 'message' => __( 'Security check failed.', 'aicoso-click-to-chat' ) ) );
		}

		// Get product ID, variations, and quantity.
		$product_id = isset( $_POST['product_id'] ) ? intval( $_POST['product_id'] ) : 0;
		$variations = isset( $_POST['variations'] ) ? array_map( 'sanitize_text_field', wp_unslash( $_POST['variations'] ) ) : array();
		$quantity   = isset( $_POST['quantity'] ) ? max( 1, intval( $_POST['quantity'] ) ) : 1;

		if ( ! $product_id ) {
			wp_send_json_error( array( 'message' => __( 'Invalid product ID.', 'aicoso-click-to-chat' ) ) );
		}

		// Initialize link generator.
		$link_generator = new CTC_Chat_WhatsApp_Link_Generator();

		// Generate WhatsApp URL with variations and quantity.
		$whatsapp_url = $link_generator->get_product_url( $product_id, $variations, $quantity );

		if ( empty( $whatsapp_url ) ) {
			wp_send_json_error( array( 'message' => __( 'Could not generate WhatsApp URL.', 'aicoso-click-to-chat' ) ) );
		}

		$product     = function_exists( 'wc_get_product' ) ? wc_get_product( $product_id ) : null;
		$unit_price  = ( $product && method_exists( $product, 'get_price' ) ) ? (float) $product->get_price() : 0;
		$order_total = $unit_price * $quantity;

		wp_send_json_success(
			array(
				'url'         => $whatsapp_url,
				'quantity'    => $quantity,
				'order_total' => $order_total,
			)
		);
	}

	/**
	 * AJAX handler to get back-in-stock alert WhatsApp URL for out-of-stock variations.
	 */
	public function ajax_get_stock_url() {
		if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'ctc_chat_public_nonce' ) ) {
			wp_send_json_error( array( 'message' => __( 'Security check failed.', 'aicoso-click-to-chat' ) ) );
		}

		$product_id = isset( $_POST['product_id'] ) ? intval( $_POST['product_id'] ) : 0;
		$variations = isset( $_POST['variations'] ) ? array_map( 'sanitize_text_field', wp_unslash( $_POST['variations'] ) ) : array();

		if ( ! $product_id ) {
			wp_send_json_error( array( 'message' => __( 'Invalid product ID.', 'aicoso-click-to-chat' ) ) );
		}

		require_once CTC_CHAT_PLUGIN_DIR . 'includes/class-ctc-chat-whatsapp-link-generator.php';
		$link_generator = new CTC_Chat_WhatsApp_Link_Generator();
		$whatsapp_url   = $link_generator->get_back_in_stock_url( $product_id, $variations );

		if ( empty( $whatsapp_url ) ) {
			wp_send_json_error( array( 'message' => __( 'Could not generate WhatsApp URL.', 'aicoso-click-to-chat' ) ) );
		}

		wp_send_json_success( array( 'url' => $whatsapp_url ) );
	}

	/**
	 * Output custom CSS from Advanced settings in the document head.
	 */
	public function output_custom_css() {
		$plugin_enabled = isset( $this->settings['plugin_enabled'] ) ? $this->settings['plugin_enabled'] : true;
		if ( ! $plugin_enabled ) {
			return;
		}

		if ( ! empty( $this->settings['advanced']['custom_css'] ) ) {
			$custom_css = trim( $this->settings['advanced']['custom_css'] );
			if ( '' !== $custom_css ) {
				echo "\n<!-- AICOSO Click to Chat Custom CSS -->\n";
				echo "<style id=\"ctc-chat-custom-css\">\n" . wp_strip_all_tags( $custom_css ) . "\n</style>\n";
			}
		}
	}

	/**
	 * Render the desktop "Scan QR Code to Chat" modal markup in the footer.
	 *
	 * @since 1.2.0
	 */
	public function render_qr_modal() {
		$qr_settings = isset( $this->settings['qr_modal'] ) ? $this->settings['qr_modal'] : array();
		$qr_enabled  = ! isset( $qr_settings['enabled'] ) ? true : ! empty( $qr_settings['enabled'] );
		if ( ! $qr_enabled ) {
			return;
		}

		$title         = ! empty( $qr_settings['title'] ) ? $qr_settings['title'] : esc_html__( 'Scan to Chat on WhatsApp', 'aicoso-click-to-chat' );
		$show_web_link = ! isset( $qr_settings['show_web_link'] ) || ! empty( $qr_settings['show_web_link'] );
		?>
		<div id="ctc-chat-qr-popover" class="ctc-chat-qr-popover ctc-qr-floating-docked" aria-hidden="true" role="dialog" aria-labelledby="ctc-chat-qr-title">
			<div class="ctc-chat-qr-card">
				<button type="button" class="ctc-chat-qr-close" aria-label="<?php esc_attr_e( 'Close', 'aicoso-click-to-chat' ); ?>">&times;</button>
				<div class="ctc-chat-qr-header">
					<div class="ctc-chat-qr-icon-wrap">
						<svg viewBox="0 0 24 24" width="20" height="20" fill="currentColor"><path d="M17.498 14.382c-.301-.15-1.767-.867-2.04-.966-.273-.101-.473-.15-.673.15-.197.295-.771.964-.944 1.162-.175.195-.349.21-.646.075-.3-.15-1.263-.465-2.403-1.485-.888-.795-1.484-1.77-1.66-2.07-.174-.3-.019-.465.13-.615.136-.135.301-.345.451-.523.146-.181.194-.301.297-.496.1-.21.049-.375-.025-.524-.075-.15-.672-1.62-.922-2.206-.24-.584-.487-.51-.672-.51-.172-.015-.371-.015-.571-.015-.2 0-.523.074-.797.359-.273.3-1.045 1.02-1.045 2.475s1.07 2.865 1.219 3.075c.149.195 2.105 3.195 5.1 4.485.714.3 1.27.48 1.704.629.714.227 1.365.195 1.88.121.574-.091 1.767-.721 2.016-1.426.255-.705.255-1.29.18-1.425-.074-.135-.27-.21-.57-.345m-5.446 7.443h-.016c-1.77 0-3.524-.48-5.055-1.38l-.36-.214-3.75.975 1.005-3.645-.239-.375c-.99-1.576-1.516-3.391-1.516-5.26 0-5.445 4.455-9.885 9.942-9.885 2.654 0 5.145 1.035 7.021 2.91 1.875 1.859 2.909 4.35 2.909 6.99-.004 5.444-4.46 9.885-9.935 9.885M20.52 3.449C18.24 1.245 15.24 0 12.045 0 5.463 0 .104 5.334.101 11.893c0 2.096.549 4.14 1.595 5.945L0 24l6.335-1.652c1.746.943 3.71 1.444 5.71 1.447h.006c6.585 0 11.946-5.336 11.949-11.896 0-3.176-1.24-6.165-3.495-8.411"/></svg>
					</div>
					<div class="ctc-chat-qr-header-text">
						<h4 id="ctc-chat-qr-title" class="ctc-chat-qr-heading"><?php echo esc_html( $title ); ?></h4>
						<p class="ctc-chat-qr-subtext"><?php esc_html_e( 'Scan with phone camera or WhatsApp', 'aicoso-click-to-chat' ); ?></p>
					</div>
				</div>
				<div class="ctc-chat-qr-code-wrapper">
					<div id="ctc-chat-qr-canvas-target" class="ctc-chat-qr-canvas-target"></div>
				</div>
				<?php if ( $show_web_link ) : ?>
				<div class="ctc-chat-qr-footer">
					<a href="#" id="ctc-chat-qr-web-action" class="ctc-chat-qr-web-btn" target="_blank" rel="noopener noreferrer">
						<?php esc_html_e( 'Or continue with WhatsApp Web', 'aicoso-click-to-chat' ); ?> &rarr;
					</a>
				</div>
				<?php endif; ?>
			</div>
		</div>
		<?php
	}

	/**
	 * Render GDPR Pre-Chat Privacy Consent prompt modal in footer.
	 *
	 * @since 1.2.1
	 */
	public function render_privacy_prompt() {
		$privacy = isset( $this->settings['privacy_compliance'] ) ? $this->settings['privacy_compliance'] : array();
		if ( empty( $privacy['enabled'] ) || ( $privacy['consent_mode'] ?? 'prompt' ) !== 'prompt' ) {
			return;
		}

		$policy_url = ! empty( $privacy['custom_policy_url'] ) ? esc_url( $privacy['custom_policy_url'] ) : '';
		if ( empty( $policy_url ) && function_exists( 'get_privacy_policy_url' ) ) {
			$policy_url = esc_url( get_privacy_policy_url() );
		}
		$link_text        = ! empty( $privacy['link_text'] ) ? esc_html( $privacy['link_text'] ) : esc_html__( 'Privacy Policy', 'aicoso-click-to-chat' );
		$policy_link_html = $policy_url ? '<a href="' . $policy_url . '" target="_blank" rel="noopener noreferrer" class="ctc-privacy-link">' . $link_text . '</a>' : $link_text;
		$notice_template  = ! empty( $privacy['notice_text'] ) ? $privacy['notice_text'] : esc_html__( 'By chatting with us on WhatsApp, you agree to our {privacy_policy_link} and consent to communication regarding your inquiry.', 'aicoso-click-to-chat' );
		$notice_html      = str_replace( '{privacy_policy_link}', $policy_link_html, esc_html( $notice_template ) );
		$agree_btn        = ! empty( $privacy['agree_button'] ) ? esc_html( $privacy['agree_button'] ) : esc_html__( 'Accept & Chat', 'aicoso-click-to-chat' );
		$cancel_btn       = ! empty( $privacy['cancel_button'] ) ? esc_html( $privacy['cancel_button'] ) : esc_html__( 'Cancel', 'aicoso-click-to-chat' );
		?>
		<div id="ctc-chat-privacy-prompt" class="ctc-chat-privacy-prompt" aria-hidden="true" role="dialog" aria-labelledby="ctc-chat-privacy-title">
			<div class="ctc-chat-privacy-backdrop"></div>
			<div class="ctc-chat-privacy-card">
				<div class="ctc-chat-privacy-icon-wrap">
					<svg viewBox="0 0 24 24" width="28" height="28" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
				</div>
				<h4 id="ctc-chat-privacy-title" class="ctc-chat-privacy-title"><?php esc_html_e( 'Privacy & Consent', 'aicoso-click-to-chat' ); ?></h4>
				<div class="ctc-chat-privacy-body">
					<?php echo wp_kses_post( $notice_html ); ?>
				</div>
				<div class="ctc-chat-privacy-actions">
					<button type="button" class="ctc-chat-privacy-btn ctc-chat-privacy-cancel" id="ctc-chat-privacy-cancel"><?php echo esc_html( $cancel_btn ); ?></button>
					<button type="button" class="ctc-chat-privacy-btn ctc-chat-privacy-agree" id="ctc-chat-privacy-agree"><?php echo esc_html( $agree_btn ); ?></button>
				</div>
			</div>
		</div>
		<?php
	}
}
