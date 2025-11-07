<?php
/**
 * Shortcodes class.
 *
 * This class handles the registration and rendering of
 * shortcodes provided by the plugin.
 *
 * @package ClickToChat
 * @since 1.0.0
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Shortcodes class.
 */
class CTC_Chat_Shortcodes {

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
	 * Constructor
	 */
	public function __construct() {
		$this->ctc_chat_settings = get_option( 'ctc_chat_settings', array() );
		$this->ctc_chat_link_generator = new CTC_Chat_WhatsApp_Link_Generator();

		// Register shortcodes.
		$this->ctc_chat_register_shortcodes();
	}

	/**
	 * Register shortcodes
	 */
	private function ctc_chat_register_shortcodes() {
		add_shortcode( 'ctc_chat_button', array( $this, 'ctc_chat_whatsapp_button_shortcode' ) );
	}

	/**
	 * WhatsApp button shortcode
	 *
	 * @param array $atts Shortcode attributes.
	 * @return string Shortcode output.
	 */
	public function ctc_chat_whatsapp_button_shortcode( $atts ) {
		// Check if plugin is enabled.
		$ctc_chat_plugin_enabled = isset( $this->ctc_chat_settings['ctc_chat_plugin_enabled'] ) ? $this->ctc_chat_settings['ctc_chat_plugin_enabled'] : true;
		if ( ! $ctc_chat_plugin_enabled ) {
			return ''; // Return empty string if plugin is disabled.
		}

		// Extract and merge attributes with defaults.
		$ctc_chat_atts = shortcode_atts(
			array(
				'product_id'    => 0,                // Specific product ID.
				'current'       => 'yes',            // Use current product.
				'text'          => '',               // Button text override.
				'bg_color'      => '',               // Background color override.
				'text_color'    => '',               // Text color override.
				'icon'          => 'yes',            // Show WhatsApp icon.
				'show_number'   => '',               // Specific WhatsApp number ID to use.
				'message'       => '',               // Custom message template.
				'css_class'     => '',               // Additional CSS classes.
				'size'          => 'normal',         // Button size (small, normal, large).
				'align'         => 'center',         // Button alignment (left, center, right).
				'type'          => 'product',        // Button type (product, shop, cart, floating).
			),
			$atts,
			'ctc_chat_button'
		);

		// Initialize output.
		$ctc_chat_output = '';

		// Determine the product ID to use.
		$ctc_chat_product_id = 0;

		// First check if a specific product_id was provided in the shortcode.
		if ( ! empty( $ctc_chat_atts['product_id'] ) && is_numeric( $ctc_chat_atts['product_id'] ) ) {
			// Use specified product ID if provided.
			$ctc_chat_product_id = absint( $ctc_chat_atts['product_id'] );
		} elseif ( 'yes' === $ctc_chat_atts['current'] && function_exists( 'is_product' ) && is_product() ) {
			// Use current product if requested and we're on a product page.
			global $product;

			if ( $product && method_exists( $product, 'get_id' ) ) {
				$ctc_chat_product_id = $product->get_id();
			}
		}

		// For non-product types, we don't need a product ID.
		// Only require product_id for product type when not on a product page.

		// Get the appropriate WhatsApp URL based on the shortcode type.
		$ctc_chat_whatsapp_url = '';

		switch ( $ctc_chat_atts['type'] ) {
			case 'product':
				// For product type, if no product_id and not on product page, use floating URL as fallback.
				if ( ! $ctc_chat_product_id ) {
					$ctc_chat_whatsapp_url = $this->ctc_chat_link_generator->ctc_chat_get_floating_url();
				} else {
					$ctc_chat_whatsapp_url = $this->ctc_chat_link_generator->ctc_chat_get_product_url( $ctc_chat_product_id );
				}
				break;

			case 'shop':
				// Get the current category if available.
				$ctc_chat_category_id = null;

				if ( function_exists( 'is_product_category' ) && is_product_category() ) {
					$ctc_chat_category = get_queried_object();
					if ( $ctc_chat_category && isset( $ctc_chat_category->term_id ) ) {
						$ctc_chat_category_id = $ctc_chat_category->term_id;
					}
				}

				$ctc_chat_whatsapp_url = $this->ctc_chat_link_generator->ctc_chat_get_shop_url( $ctc_chat_category_id );
				break;

			case 'cart':
				$ctc_chat_whatsapp_url = $this->ctc_chat_link_generator->ctc_chat_get_cart_url();
				break;

			case 'floating':
				$ctc_chat_whatsapp_url = $this->ctc_chat_link_generator->ctc_chat_get_floating_url();
				break;
		}

		// If we couldn't generate a WhatsApp URL, return empty string.
		if ( empty( $ctc_chat_whatsapp_url ) ) {
			// Log the error for debugging
			if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
				error_log( 'CTC Chat Shortcode: Could not generate WhatsApp URL. Type: ' . $ctc_chat_atts['type'] . ', Product ID: ' . $ctc_chat_product_id );
			}
			return '';
		}

		// Override message template if provided.
		if ( ! empty( $ctc_chat_atts['message'] ) ) {
			// Extract WhatsApp number from URL.
			$ctc_chat_url_parts = wp_parse_url( $ctc_chat_whatsapp_url );
			$ctc_chat_path_parts = explode( '/', $ctc_chat_url_parts['path'] );
			$ctc_chat_whatsapp_number = end( $ctc_chat_path_parts );

			// Generate new URL with custom message.
			$ctc_chat_whatsapp_url = 'https://wa.me/' . $ctc_chat_whatsapp_number . '?text=' . rawurlencode( $ctc_chat_atts['message'] );
		}

		// Get button settings (using shortcode attributes as overrides if provided).
		$ctc_chat_button_text = ! empty( $ctc_chat_atts['text'] ) ? $ctc_chat_atts['text'] :
						  ( isset( $this->ctc_chat_settings['ctc_chat_button_settings']['ctc_chat_text'] ) ?
							$this->ctc_chat_settings['ctc_chat_button_settings']['ctc_chat_text'] :
							esc_html__( 'Order via WhatsApp', 'aicoso-click-to-chat' ) );

		$ctc_chat_show_icon = 'yes' === $ctc_chat_atts['icon'];

		$ctc_chat_bg_color = ! empty( $ctc_chat_atts['bg_color'] ) ? $ctc_chat_atts['bg_color'] :
					   ( isset( $this->ctc_chat_settings['ctc_chat_button_settings']['ctc_chat_bg_color'] ) ?
						 $this->ctc_chat_settings['ctc_chat_button_settings']['ctc_chat_bg_color'] : '#25D366' );

		$ctc_chat_text_color = ! empty( $ctc_chat_atts['text_color'] ) ? $ctc_chat_atts['text_color'] :
						 ( isset( $this->ctc_chat_settings['ctc_chat_button_settings']['ctc_chat_text_color'] ) ?
						   $this->ctc_chat_settings['ctc_chat_button_settings']['ctc_chat_text_color'] : '#ffffff' );

		// Inline styles.
		$ctc_chat_button_style = 'background-color: ' . esc_attr( $ctc_chat_bg_color ) . '; color: ' . esc_attr( $ctc_chat_text_color ) . ';';

		// Button classes.
		$ctc_chat_button_classes = array(
			'ctc_chat_whatsapp_button',
			'ctc_chat_button_' . $ctc_chat_atts['type'],
			'ctc_chat_button_size_' . $ctc_chat_atts['size'],
		);

		if ( $ctc_chat_show_icon ) {
			$ctc_chat_button_classes[] = 'ctc_chat_button_with_icon';
		}

		// Add custom CSS classes if provided.
		if ( ! empty( $ctc_chat_atts['css_class'] ) ) {
			$ctc_chat_button_classes[] = esc_attr( $ctc_chat_atts['css_class'] );
		}

		$ctc_chat_class_attr = implode( ' ', $ctc_chat_button_classes );

		// Build container based on alignment.
		$ctc_chat_container_class = 'ctc_chat_shortcode_container ctc_chat_align_' . $ctc_chat_atts['align'];

		// Build output HTML.
		$ctc_chat_output .= '<div class="' . esc_attr( $ctc_chat_container_class ) . '">';
		$ctc_chat_output .= '<a href="' . esc_url( $ctc_chat_whatsapp_url ) . '" class="' . esc_attr( $ctc_chat_class_attr ) . '" ';
		$ctc_chat_output .= 'style="' . esc_attr( $ctc_chat_button_style ) . '" target="_blank" rel="noopener">';

		if ( $ctc_chat_show_icon ) {
			$ctc_chat_output .= '<span class="ctc_chat_whatsapp_icon">';
			$ctc_chat_output .= '<svg viewBox="0 0 24 24" width="24" height="24">';
			$ctc_chat_output .= '<path fill="currentColor" d="M17.498 14.382c-.301-.15-1.767-.867-2.04-.966-.273-.101-.473-.15-.673.15-.197.295-.771.964-.944 1.162-.175.195-.349.21-.646.075-.3-.15-1.263-.465-2.403-1.485-.888-.795-1.484-1.77-1.66-2.07-.174-.3-.019-.465.13-.615.136-.135.301-.345.451-.523.146-.181.194-.301.297-.496.1-.21.049-.375-.025-.524-.075-.15-.672-1.62-.922-2.206-.24-.584-.487-.51-.672-.51-.172-.015-.371-.015-.571-.015-.2 0-.523.074-.797.359-.273.3-1.045 1.02-1.045 2.475s1.07 2.865 1.219 3.075c.149.195 2.105 3.195 5.1 4.485.714.3 1.27.48 1.704.629.714.227 1.365.195 1.88.121.574-.091 1.767-.721 2.016-1.426.255-.705.255-1.29.18-1.425-.074-.135-.27-.21-.57-.345m-5.446 7.443h-.016c-1.77 0-3.524-.48-5.055-1.38l-.36-.214-3.75.975 1.005-3.645-.239-.375c-.99-1.576-1.516-3.391-1.516-5.26 0-5.445 4.455-9.885 9.942-9.885 2.654 0 5.145 1.035 7.021 2.91 1.875 1.859 2.909 4.35 2.909 6.99-.004 5.444-4.46 9.885-9.935 9.885M20.52 3.449C18.24 1.245 15.24 0 12.045 0 5.463 0 .104 5.334.101 11.893c0 2.096.549 4.14 1.595 5.945L0 24l6.335-1.652c1.746.943 3.71 1.444 5.71 1.447h.006c6.585 0 11.946-5.336 11.949-11.896 0-3.176-1.24-6.165-3.495-8.411"/>';
			$ctc_chat_output .= '</svg>';
			$ctc_chat_output .= '</span>';
		}

		$ctc_chat_output .= '<span class="ctc_chat_button_text">' . esc_html( $ctc_chat_button_text ) . '</span>';
		$ctc_chat_output .= '</a>';
		$ctc_chat_output .= '</div>';

		return $ctc_chat_output;
	}
}

// Initialize the class.
new CTC_Chat_Shortcodes();
