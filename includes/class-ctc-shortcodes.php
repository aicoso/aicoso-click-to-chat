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
class CTC_Shortcodes {

	/**
	 * Plugin settings
	 *
	 * @var array
	 */
	private $settings;

	/**
	 * WhatsApp Link Generator instance
	 *
	 * @var CTC_WhatsApp_Link_Generator
	 */
	private $link_generator;

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->settings = get_option( 'ctc_settings', array() );
		$this->link_generator = new CTC_WhatsApp_Link_Generator();

		// Register shortcodes.
		$this->register_shortcodes();
	}

	/**
	 * Register shortcodes
	 */
	private function register_shortcodes() {
		add_shortcode( 'ctc_button', array( $this, 'whatsapp_button_shortcode' ) );
		// Keep old shortcode for backward compatibility.
		add_shortcode( 'whatsapp_button', array( $this, 'whatsapp_button_shortcode' ) );
	}

	/**
	 * WhatsApp button shortcode
	 *
	 * @param array $atts Shortcode attributes.
	 * @return string Shortcode output.
	 */
	public function whatsapp_button_shortcode( $atts ) {
		// Check if plugin is enabled.
		$plugin_enabled = isset( $this->settings['plugin_enabled'] ) ? $this->settings['plugin_enabled'] : true;
		if ( ! $plugin_enabled ) {
			return ''; // Return empty string if plugin is disabled.
		}

		// Extract and merge attributes with defaults.
		$atts = shortcode_atts(
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
			'whatsapp_button'
		);

		// Initialize output.
		$output = '';

		// Determine the product ID to use.
		$product_id = 0;

		// First check if a specific product_id was provided in the shortcode.
		if ( ! empty( $atts['product_id'] ) && is_numeric( $atts['product_id'] ) ) {
			// Use specified product ID if provided.
			$product_id = absint( $atts['product_id'] );
		} elseif ( 'yes' === $atts['current'] && is_product() ) {
			// Use current product if requested and we're on a product page.
			global $product;

			if ( $product ) {
				$product_id = $product->get_id();
			}
		}

		// If no valid product ID, try to get current page/cart info based on type.
		if ( ! $product_id && 'product' === $atts['type'] ) {
			// For product type shortcode when not on a product page and no product_id specified,
			// we cannot proceed as we need a valid product to generate the message.
			// Return a helpful comment for debugging (won't be visible in frontend).
			return '<!-- CTC: Product shortcode requires product_id attribute when not on a product page -->';
		}

		// Get the appropriate WhatsApp URL based on the shortcode type.
		$whatsapp_url = '';

		switch ( $atts['type'] ) {
			case 'product':
				$whatsapp_url = $this->link_generator->get_product_url( $product_id );
				break;

			case 'shop':
				// Get the current category if available.
				$category_id = null;

				if ( is_product_category() ) {
					$category = get_queried_object();
					if ( $category && isset( $category->term_id ) ) {
						$category_id = $category->term_id;
					}
				}

				$whatsapp_url = $this->link_generator->get_shop_url( $category_id );
				break;

			case 'cart':
				$whatsapp_url = $this->link_generator->get_cart_url();
				break;

			case 'floating':
				$whatsapp_url = $this->link_generator->get_floating_url();
				break;
		}

		// If we couldn't generate a WhatsApp URL, return empty string.
		if ( empty( $whatsapp_url ) ) {
			return '';
		}

		// Override message template if provided.
		if ( ! empty( $atts['message'] ) ) {
			// Extract WhatsApp number from URL.
			$url_parts = wp_parse_url( $whatsapp_url );
			$path_parts = explode( '/', $url_parts['path'] );
			$whatsapp_number = end( $path_parts );

			// Generate new URL with custom message.
			$whatsapp_url = 'https://wa.me/' . $whatsapp_number . '?text=' . rawurlencode( $atts['message'] );
		}

		// Get button settings (using shortcode attributes as overrides if provided).
		$button_text = ! empty( $atts['text'] ) ? $atts['text'] :
					  ( isset( $this->settings['button_settings']['text'] ) ?
						$this->settings['button_settings']['text'] :
						esc_html__( 'Order via WhatsApp', 'aicoso-click-to-chat' ) );

		$show_icon = 'yes' === $atts['icon'];

		$bg_color = ! empty( $atts['bg_color'] ) ? $atts['bg_color'] :
				   ( isset( $this->settings['button_settings']['bg_color'] ) ?
					 $this->settings['button_settings']['bg_color'] : '#25D366' );

		$text_color = ! empty( $atts['text_color'] ) ? $atts['text_color'] :
					 ( isset( $this->settings['button_settings']['text_color'] ) ?
					   $this->settings['button_settings']['text_color'] : '#ffffff' );

		// Inline styles.
		$button_style = 'background-color: ' . esc_attr( $bg_color ) . '; color: ' . esc_attr( $text_color ) . ';';

		// Button classes.
		$button_classes = array(
			'ctc-whatsapp-button',
			'ctc-button-' . $atts['type'],
			'ctc-button-size-' . $atts['size'],
			'ctc-button-align-' . $atts['align'],
		);

		if ( $show_icon ) {
			$button_classes[] = 'ctc-button-with-icon';
		}

		// Add custom CSS classes if provided.
		if ( ! empty( $atts['css_class'] ) ) {
			$button_classes[] = esc_attr( $atts['css_class'] );
		}

		$class_attr = implode( ' ', $button_classes );

		// Build container based on alignment.
		$container_class = 'ctc-shortcode-container ctc-align-' . $atts['align'];

		// Build output HTML.
		$output .= '<div class="' . esc_attr( $container_class ) . '">';
		$output .= '<a href="' . esc_url( $whatsapp_url ) . '" class="' . esc_attr( $class_attr ) . '" ';
		$output .= 'style="' . esc_attr( $button_style ) . '" target="_blank" rel="noopener">';

		if ( $show_icon ) {
			$output .= '<span class="ctc-whatsapp-icon">';
			$output .= '<svg viewBox="0 0 24 24" width="24" height="24">';
			$output .= '<path fill="currentColor" d="M17.498 14.382c-.301-.15-1.767-.867-2.04-.966-.273-.101-.473-.15-.673.15-.197.295-.771.964-.944 1.162-.175.195-.349.21-.646.075-.3-.15-1.263-.465-2.403-1.485-.888-.795-1.484-1.77-1.66-2.07-.174-.3-.019-.465.13-.615.136-.135.301-.345.451-.523.146-.181.194-.301.297-.496.1-.21.049-.375-.025-.524-.075-.15-.672-1.62-.922-2.206-.24-.584-.487-.51-.672-.51-.172-.015-.371-.015-.571-.015-.2 0-.523.074-.797.359-.273.3-1.045 1.02-1.045 2.475s1.07 2.865 1.219 3.075c.149.195 2.105 3.195 5.1 4.485.714.3 1.27.48 1.704.629.714.227 1.365.195 1.88.121.574-.091 1.767-.721 2.016-1.426.255-.705.255-1.29.18-1.425-.074-.135-.27-.21-.57-.345m-5.446 7.443h-.016c-1.77 0-3.524-.48-5.055-1.38l-.36-.214-3.75.975 1.005-3.645-.239-.375c-.99-1.576-1.516-3.391-1.516-5.26 0-5.445 4.455-9.885 9.942-9.885 2.654 0 5.145 1.035 7.021 2.91 1.875 1.859 2.909 4.35 2.909 6.99-.004 5.444-4.46 9.885-9.935 9.885M20.52 3.449C18.24 1.245 15.24 0 12.045 0 5.463 0 .104 5.334.101 11.893c0 2.096.549 4.14 1.595 5.945L0 24l6.335-1.652c1.746.943 3.71 1.444 5.71 1.447h.006c6.585 0 11.946-5.336 11.949-11.896 0-3.176-1.24-6.165-3.495-8.411"/>';
			$output .= '</svg>';
			$output .= '</span>';
		}

		$output .= '<span class="ctc-button-text">' . esc_html( $button_text ) . '</span>';
		$output .= '</a>';
		$output .= '</div>';

		return $output;
	}
}

// Initialize the class.
new CTC_Shortcodes();
