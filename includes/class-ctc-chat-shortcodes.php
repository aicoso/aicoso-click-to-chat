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
	private $settings;

	/**
	 * WhatsApp Link Generator instance
	 *
	 * @var CTC_Chat_WhatsApp_Link_Generator
	 */
	private $link_generator;

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->settings = get_option( 'ctc_chat_settings', array() );
		$this->link_generator = new CTC_Chat_WhatsApp_Link_Generator();

		// Register shortcodes.
		$this->register_shortcodes();
	}

	/**
	 * Register shortcodes
	 */
	private function register_shortcodes() {
		add_shortcode( 'ctc_chat_button', array( $this, 'whatsapp_button_shortcode' ) );
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
			'ctc_chat_button'
		);

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

		// For non-product types, we don't need a product ID.
		// Only require product_id for product type when not on a product page.

		if ( $product_id && $this->link_generator->is_product_button_hidden( $product_id ) ) {
			return '';
		}

		// Get the appropriate WhatsApp URL based on the shortcode type.
		$whatsapp_url = '';

		switch ( $atts['type'] ) {
			case 'product':
				// For product type, if no product_id and not on product page, use floating URL as fallback.
				if ( ! $product_id ) {
					$whatsapp_url = $this->link_generator->get_floating_url();
				} else {
					$whatsapp_url = $this->link_generator->get_product_url( $product_id );
				}
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

		$container_class = 'ctc-shortcode-container';

		$button_type   = 'shortcode';
		$template_type = 'custom';
		if ( ! empty( $atts['message'] ) ) {
			$template_type = 'custom';
		} else {
			switch ( $atts['type'] ) {
				case 'shop':
					$template_type = 'shop';
					break;
				case 'cart':
					$template_type = 'cart_checkout';
					break;
				case 'floating':
					$template_type = 'floating';
					break;
				default:
					$template_type = $product_id && function_exists( 'wc_get_product' ) && wc_get_product( $product_id ) && wc_get_product( $product_id )->is_type( 'variable' ) ? 'variations' : 'single_product';
			}
		}

		$number_id = 0;
		if ( ! empty( $atts['show_number'] ) ) {
			$number_id = absint( $atts['show_number'] );
		} else {
			$number_id = $this->link_generator->get_number_id( $product_id ? $product_id : null );
		}

		$context = array_merge(
			ctc_chat_get_cart_tracking_context(),
			array(
				'template_type' => $template_type,
				'number_id'     => $number_id,
				'product_id'    => $product_id,
			)
		);

		ob_start();
		echo '<div class="' . esc_attr( $container_class ) . '">';
		CTC_Chat_Button_Renderer::render(
			$whatsapp_url,
			$button_type,
			array(
				'text'          => $button_text,
				'show_icon'     => $show_icon,
				'bg_color'      => $bg_color,
				'text_color'    => $text_color,
				'extra_classes' => array_filter( array( 'ctc-whatsapp-button', 'ctc-button-' . $atts['type'], 'ctc-button-size-' . $atts['size'], $atts['css_class'] ) ),
				'context'       => $context,
			)
		);
		echo '</div>';
		return ob_get_clean();
	}
}
