<?php
/**
 * Settings class.
 *
 * This class handles the plugin settings framework and API.
 *
 * @package ClickToChat
 * @since 1.0.0
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Settings class.
 */
class CTC_Chat_Settings {

	/**
	 * Plugin settings
	 *
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
		// Register AJAX handlers.
		add_action( 'wp_ajax_ctc_chat_ajax_get_setting', array( $this, 'ctc_chat_ajax_get_setting' ) );
		add_action( 'wp_ajax_ctc_chat_ajax_search_products', array( $this, 'ctc_chat_ajax_search_products' ) );
		add_action( 'wp_ajax_ctc_chat_ajax_search_categories', array( $this, 'ctc_chat_ajax_search_categories' ) );
		add_action( 'wp_ajax_ctc_chat_ajax_search_pages', array( $this, 'ctc_chat_ajax_search_pages' ) );
		add_action( 'wp_ajax_ctc_chat_ajax_search_posts', array( $this, 'ctc_chat_ajax_search_posts' ) );
		add_action( 'wp_ajax_ctc_chat_ajax_search_tags', array( $this, 'ctc_chat_ajax_search_tags' ) );
		add_action( 'wp_ajax_ctc_chat_ajax_preview_message', array( $this, 'ctc_chat_ajax_preview_message' ) );
	}

	/**
	 * Get a specific setting
	 *
	 * @param string $key     The setting key.
	 * @param mixed  $default The default value if setting doesn't exist.
	 * @return mixed The setting value or default.
	 */
	public function ctc_chat_get_setting( $key, $default = false ) {
		// Split the key by dots to access nested settings.
		$ctc_chat_keys = explode( '.', $key );
		$ctc_chat_value = $this->ctc_chat_settings;

		// Navigate through the settings array.
		foreach ( $ctc_chat_keys as $ctc_chat_nested_key ) {
			if ( ! isset( $ctc_chat_value[ $ctc_chat_nested_key ] ) ) {
				return $default;
			}

			$ctc_chat_value = $ctc_chat_value[ $ctc_chat_nested_key ];
		}

		return $ctc_chat_value;
	}

	/**
	 * AJAX handler for getting a setting
	 */
	public function ctc_chat_ajax_get_setting() {
		// Check nonce.
		if ( ! check_ajax_referer( 'ctc_chat_admin_nonce', 'nonce', false ) ) {
			wp_send_json_error(
				array(
					'message' => esc_html__( 'Security check failed.', 'aicoso-click-to-chat' ),
				)
			);
		}

		// Check if key is set.
		if ( empty( $_POST['key'] ) ) {
			wp_send_json_error(
				array(
					'message' => esc_html__( 'Setting key is required.', 'aicoso-click-to-chat' ),
				)
			);
		}

		// Get the setting.
		$ctc_chat_key = sanitize_text_field( wp_unslash( $_POST['key'] ) );
		$ctc_chat_default = isset( $_POST['default'] ) ? sanitize_text_field( wp_unslash( $_POST['default'] ) ) : false;

		$ctc_chat_value = $this->ctc_chat_get_setting( $ctc_chat_key, $ctc_chat_default );

		wp_send_json_success(
			array(
				'value' => $ctc_chat_value,
			)
		);
	}

	/**
	 * AJAX handler for searching products
	 */
	public function ctc_chat_ajax_search_products() {
		// Check nonce.
		if ( ! check_ajax_referer( 'ctc_chat_admin_nonce', 'nonce', false ) ) {
			wp_send_json_error(
				array(
					'message' => esc_html__( 'Security check failed.', 'aicoso-click-to-chat' ),
				)
			);
		}

		// Check permissions.
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error(
				array(
					'message' => esc_html__( 'You do not have permission to perform this action.', 'aicoso-click-to-chat' ),
				)
			);
		}

		// Get search term.
		$ctc_chat_term = isset( $_GET['term'] ) ? sanitize_text_field( wp_unslash( $_GET['term'] ) ) : '';

		// Search for products.
		$ctc_chat_args = array(
			'post_type'      => 'product',
			'post_status'    => 'publish',
			'posts_per_page' => 10,
		);

		if ( ! empty( $ctc_chat_term ) ) {
			$ctc_chat_args['s'] = $ctc_chat_term;
		}

		$ctc_chat_products = get_posts( $ctc_chat_args );

		// Format the results.
		$ctc_chat_results = array();
		foreach ( $ctc_chat_products as $ctc_chat_product ) {
			$ctc_chat_results[] = array(
				'id'   => $ctc_chat_product->ID,
				'text' => $ctc_chat_product->post_title,
			);
		}

		wp_send_json_success(
			array(
				'results' => $ctc_chat_results,
			)
		);
	}

	/**
	 * AJAX handler for searching categories
	 */
	public function ctc_chat_ajax_search_categories() {
		// Check nonce.
		if ( ! check_ajax_referer( 'ctc_chat_admin_nonce', 'nonce', false ) ) {
			wp_send_json_error(
				array(
					'message' => esc_html__( 'Security check failed.', 'aicoso-click-to-chat' ),
				)
			);
		}

		// Check permissions.
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error(
				array(
					'message' => esc_html__( 'You do not have permission to perform this action.', 'aicoso-click-to-chat' ),
				)
			);
		}

		// Get search term.
		$ctc_chat_term = isset( $_GET['term'] ) ? sanitize_text_field( wp_unslash( $_GET['term'] ) ) : '';

		// Search for categories.
		$ctc_chat_args = array(
			'taxonomy'   => 'product_cat',
			'hide_empty' => false,
			'number'     => 10,
		);

		if ( ! empty( $ctc_chat_term ) ) {
			$ctc_chat_args['name__like'] = $ctc_chat_term;
		}

		$ctc_chat_categories = get_terms( $ctc_chat_args );

		// Format the results.
		$ctc_chat_results = array();
		if ( ! is_wp_error( $ctc_chat_categories ) ) {
			foreach ( $ctc_chat_categories as $ctc_chat_category ) {
				$ctc_chat_results[] = array(
					'id'   => $ctc_chat_category->term_id,
					'text' => $ctc_chat_category->name,
				);
			}
		}

		wp_send_json_success(
			array(
				'results' => $ctc_chat_results,
			)
		);
	}

	/**
	 * AJAX handler for searching pages
	 */
	public function ctc_chat_ajax_search_pages() {
		// Check nonce.
		if ( ! check_ajax_referer( 'ctc_chat_admin_nonce', 'nonce', false ) ) {
			wp_send_json_error(
				array(
					'message' => esc_html__( 'Security check failed.', 'aicoso-click-to-chat' ),
				)
			);
		}

		// Check permissions.
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error(
				array(
					'message' => esc_html__( 'You do not have permission to perform this action.', 'aicoso-click-to-chat' ),
				)
			);
		}

		// Get search term.
		$ctc_chat_term = isset( $_GET['term'] ) ? sanitize_text_field( wp_unslash( $_GET['term'] ) ) : '';

		// Search for pages.
		$ctc_chat_args = array(
			'post_type'      => 'page',
			'post_status'    => 'publish',
			'posts_per_page' => 10,
		);

		if ( ! empty( $ctc_chat_term ) ) {
			$ctc_chat_args['s'] = $ctc_chat_term;
		}

		$ctc_chat_pages = get_posts( $ctc_chat_args );

		// Format the results.
		$ctc_chat_results = array();
		foreach ( $ctc_chat_pages as $ctc_chat_page ) {
			$ctc_chat_results[] = array(
				'id'   => $ctc_chat_page->ID,
				'text' => $ctc_chat_page->post_title,
			);
		}

		wp_send_json_success(
			array(
				'results' => $ctc_chat_results,
			)
		);
	}

	/**
	 * AJAX handler for searching posts
	 */
	public function ctc_chat_ajax_search_posts() {
		// Check nonce.
		if ( ! check_ajax_referer( 'ctc_chat_admin_nonce', 'nonce', false ) ) {
			wp_send_json_error(
				array(
					'message' => esc_html__( 'Security check failed.', 'aicoso-click-to-chat' ),
				)
			);
		}

		// Check permissions.
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error(
				array(
					'message' => esc_html__( 'You do not have permission to perform this action.', 'aicoso-click-to-chat' ),
				)
			);
		}

		// Get search term.
		$ctc_chat_term = isset( $_GET['term'] ) ? sanitize_text_field( wp_unslash( $_GET['term'] ) ) : '';

		// Search for posts.
		$ctc_chat_args = array(
			'post_type'      => 'post',
			'post_status'    => 'publish',
			'posts_per_page' => 10,
		);

		if ( ! empty( $ctc_chat_term ) ) {
			$ctc_chat_args['s'] = $ctc_chat_term;
		}

		$ctc_chat_posts = get_posts( $ctc_chat_args );

		// Format the results.
		$ctc_chat_results = array();
		foreach ( $ctc_chat_posts as $ctc_chat_post ) {
			$ctc_chat_results[] = array(
				'id'   => $ctc_chat_post->ID,
				'text' => $ctc_chat_post->post_title,
			);
		}

		wp_send_json_success(
			array(
				'results' => $ctc_chat_results,
			)
		);
	}

	/**
	 * AJAX handler for searching product tags
	 */
	public function ctc_chat_ajax_search_tags() {
		// Check nonce.
		if ( ! check_ajax_referer( 'ctc_chat_admin_nonce', 'nonce', false ) ) {
			wp_send_json_error(
				array(
					'message' => esc_html__( 'Security check failed.', 'aicoso-click-to-chat' ),
				)
			);
		}

		// Check permissions.
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error(
				array(
					'message' => esc_html__( 'You do not have permission to perform this action.', 'aicoso-click-to-chat' ),
				)
			);
		}

		// Get search term.
		$ctc_chat_term = isset( $_GET['term'] ) ? sanitize_text_field( wp_unslash( $_GET['term'] ) ) : '';

		// Search for product tags.
		$ctc_chat_args = array(
			'taxonomy'   => 'product_tag',
			'hide_empty' => false,
			'number'     => 10,
		);

		if ( ! empty( $ctc_chat_term ) ) {
			$ctc_chat_args['name__like'] = $ctc_chat_term;
		}

		$ctc_chat_tags = get_terms( $ctc_chat_args );

		// Format the results.
		$ctc_chat_results = array();
		if ( ! is_wp_error( $ctc_chat_tags ) ) {
			foreach ( $ctc_chat_tags as $ctc_chat_tag ) {
				$ctc_chat_results[] = array(
					'id'   => $ctc_chat_tag->term_id,
					'text' => $ctc_chat_tag->name,
				);
			}
		}

		wp_send_json_success(
			array(
				'results' => $ctc_chat_results,
			)
		);
	}

	/**
	 * AJAX handler for previewing message templates
	 */
	public function ctc_chat_ajax_preview_message() {
		// Check nonce.
		if ( ! check_ajax_referer( 'ctc_chat_admin_nonce', 'nonce', false ) ) {
			wp_send_json_error(
				array(
					'message' => esc_html__( 'Security check failed.', 'aicoso-click-to-chat' ),
				)
			);
		}

		// Check permissions.
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error(
				array(
					'message' => esc_html__( 'You do not have permission to perform this action.', 'aicoso-click-to-chat' ),
				)
			);
		}

		// Get template type and content.
		$ctc_chat_template_type = isset( $_POST['template_type'] ) ? sanitize_text_field( wp_unslash( $_POST['template_type'] ) ) : '';
		// Use wp_unslash to handle slashes and properly sanitize template content.
		$ctc_chat_template_content = isset( $_POST['template_content'] ) ? wp_kses_post( wp_unslash( $_POST['template_content'] ) ) : '';

		if ( empty( $ctc_chat_template_type ) || empty( $ctc_chat_template_content ) ) {
			wp_send_json_error(
				array(
					'message' => esc_html__( 'Template type and content are required.', 'aicoso-click-to-chat' ),
				)
			);
		}

		// No need to decode since we're not encoding in the first place.

		// Generate a preview with sample data based on template type.
		$ctc_chat_preview = $this->ctc_chat_generate_template_preview( $ctc_chat_template_type, $ctc_chat_template_content );

		wp_send_json_success(
			array(
				'preview' => $ctc_chat_preview,
			)
		);
	}

	/**
	 * Generate a message template preview with sample data
	 *
	 * @param string $template_type    The template type.
	 * @param string $template_content The template content.
	 * @return string The preview with replaced placeholders.
	 */
	private function ctc_chat_generate_template_preview( $template_type, $template_content ) {
		// Define sample data based on template type.
		$ctc_chat_replacements = array();

		switch ( $template_type ) {
			case 'ctc_chat_single_product':
				// Get price and decode HTML entities.
				if ( function_exists( 'wc_price' ) ) {
					$ctc_chat_price = html_entity_decode( wp_strip_all_tags( wc_price( 49.99 ) ), ENT_QUOTES | ENT_HTML5, 'UTF-8' );
				} elseif ( function_exists( 'get_woocommerce_currency_symbol' ) ) {
					// Fallback: get currency symbol directly.
					$ctc_chat_price = get_woocommerce_currency_symbol() . '49.99';
				} else {
					// Default fallback.
					$ctc_chat_price = '$49.99';
				}
				$ctc_chat_replacements = array(
					'{product_name}' => 'Sample Product',
					'{price}'        => $ctc_chat_price,
					'{product_url}'  => site_url( '/product/sample-product/' ),
				);
				break;

			case 'ctc_chat_variations':
				// Get price and decode HTML entities.
				if ( function_exists( 'wc_price' ) ) {
					$ctc_chat_variation_price = html_entity_decode( wp_strip_all_tags( wc_price( 59.99 ) ), ENT_QUOTES | ENT_HTML5, 'UTF-8' );
				} elseif ( function_exists( 'get_woocommerce_currency_symbol' ) ) {
					// Fallback: get currency symbol directly.
					$ctc_chat_variation_price = get_woocommerce_currency_symbol() . '59.99';
				} else {
					// Default fallback.
					$ctc_chat_variation_price = '$59.99';
				}
				$ctc_chat_replacements = array(
					'{product_name}'      => 'Sample Variable Product',
					'{variation_details}' => 'Color: Blue, Size: Medium',
					'{variation_price}'   => $ctc_chat_variation_price,
					'{product_url}'       => site_url( '/product/sample-variable-product/' ),
				);
				break;

			case 'ctc_chat_cart_checkout':
				// Decode all price entities.
				if ( function_exists( 'wc_price' ) ) {
					$ctc_chat_price1 = html_entity_decode( wp_strip_all_tags( wc_price( 99.98 ) ), ENT_QUOTES | ENT_HTML5, 'UTF-8' );
					$ctc_chat_price2 = html_entity_decode( wp_strip_all_tags( wc_price( 29.99 ) ), ENT_QUOTES | ENT_HTML5, 'UTF-8' );
					$ctc_chat_subtotal = html_entity_decode( wp_strip_all_tags( wc_price( 129.97 ) ), ENT_QUOTES | ENT_HTML5, 'UTF-8' );
					$ctc_chat_tax = html_entity_decode( wp_strip_all_tags( wc_price( 10.40 ) ), ENT_QUOTES | ENT_HTML5, 'UTF-8' );
					$ctc_chat_shipping = html_entity_decode( wp_strip_all_tags( wc_price( 5.00 ) ), ENT_QUOTES | ENT_HTML5, 'UTF-8' );
					$ctc_chat_total = html_entity_decode( wp_strip_all_tags( wc_price( 145.37 ) ), ENT_QUOTES | ENT_HTML5, 'UTF-8' );
				} elseif ( function_exists( 'get_woocommerce_currency_symbol' ) ) {
					$ctc_chat_symbol = get_woocommerce_currency_symbol();
					$ctc_chat_price1 = $ctc_chat_symbol . '99.98';
					$ctc_chat_price2 = $ctc_chat_symbol . '29.99';
					$ctc_chat_subtotal = $ctc_chat_symbol . '129.97';
					$ctc_chat_tax = $ctc_chat_symbol . '10.40';
					$ctc_chat_shipping = $ctc_chat_symbol . '5.00';
					$ctc_chat_total = $ctc_chat_symbol . '145.37';
				} else {
					$ctc_chat_price1 = '$99.98';
					$ctc_chat_price2 = '$29.99';
					$ctc_chat_subtotal = '$129.97';
					$ctc_chat_tax = '$10.40';
					$ctc_chat_shipping = '$5.00';
					$ctc_chat_total = '$145.37';
				}

				$ctc_chat_cart_items_list = 'Sample Product x 2 - ' . $ctc_chat_price1 . "\n" .
										 'Another Product x 1 - ' . $ctc_chat_price2;

				$ctc_chat_replacements = array(
					'{cart_items_list}' => $ctc_chat_cart_items_list,
					'{cart_subtotal}'   => $ctc_chat_subtotal,
					'{tax_amount}'      => $ctc_chat_tax,
					'{shipping_method}' => 'Flat rate',
					'{shipping_cost}'   => $ctc_chat_shipping,
					'{cart_total}'      => $ctc_chat_total,
				);
				break;

			case 'ctc_chat_thank_you':
				// Decode all price entities.
				if ( function_exists( 'wc_price' ) ) {
					$ctc_chat_price1 = html_entity_decode( wp_strip_all_tags( wc_price( 99.98 ) ), ENT_QUOTES | ENT_HTML5, 'UTF-8' );
					$ctc_chat_price2 = html_entity_decode( wp_strip_all_tags( wc_price( 29.99 ) ), ENT_QUOTES | ENT_HTML5, 'UTF-8' );
					$ctc_chat_order_total = html_entity_decode( wp_strip_all_tags( wc_price( 134.97 ) ), ENT_QUOTES | ENT_HTML5, 'UTF-8' );
				} elseif ( function_exists( 'get_woocommerce_currency_symbol' ) ) {
					$ctc_chat_symbol = get_woocommerce_currency_symbol();
					$ctc_chat_price1 = $ctc_chat_symbol . '99.98';
					$ctc_chat_price2 = $ctc_chat_symbol . '29.99';
					$ctc_chat_order_total = $ctc_chat_symbol . '134.97';
				} else {
					$ctc_chat_price1 = '$99.98';
					$ctc_chat_price2 = '$29.99';
					$ctc_chat_order_total = '$134.97';
				}

				$ctc_chat_date_format = function_exists( 'wc_date_format' ) ? wc_date_format() : get_option( 'date_format' );
				$ctc_chat_ordered_items_list = 'Sample Product x 2 - ' . $ctc_chat_price1 . "\n" .
											'Another Product x 1 - ' . $ctc_chat_price2;

				$ctc_chat_replacements = array(
					'{order_number}'      => '12345',
					'{order_date}'        => date_i18n( $ctc_chat_date_format, time() ),
					'{ordered_items_list}' => $ctc_chat_ordered_items_list,
					'{coupon_code}'       => 'SAMPLE10',
					'{order_total}'       => $ctc_chat_order_total,
				);
				break;

			case 'ctc_chat_floating':
				$ctc_chat_replacements = array(
					'{current_page_url}' => site_url( '/sample-page/' ),
				);
				break;
		}

		// Replace placeholders with sample data.
		foreach ( $ctc_chat_replacements as $ctc_chat_placeholder => $ctc_chat_value ) {
			$template_content = str_replace( $ctc_chat_placeholder, $ctc_chat_value, $template_content );
		}

		return $template_content;
	}

	/**
	 * Get available position options for product pages
	 *
	 * @return array The position options.
	 */
	public function ctc_chat_get_product_position_options() {
		return array(
			'after_add_to_cart'     => esc_html__( 'Below add to cart button', 'aicoso-click-to-chat' ),
			'before_add_to_cart'    => esc_html__( 'Above add to cart button', 'aicoso-click-to-chat' ),
			'after_price'           => esc_html__( 'Below price', 'aicoso-click-to-chat' ),
			'before_title'          => esc_html__( 'Above title', 'aicoso-click-to-chat' ),
			'after_short_description' => esc_html__( 'Below short description', 'aicoso-click-to-chat' ),
		);
	}

	/**
	 * Get available position options for shop pages
	 *
	 * @return array The position options.
	 */
	public function ctc_chat_get_shop_position_options() {
		return array(
			'after_add_to_cart'  => esc_html__( 'Below add to cart button', 'aicoso-click-to-chat' ),
			'before_add_to_cart' => esc_html__( 'Above add to cart button', 'aicoso-click-to-chat' ),
			'after_price'        => esc_html__( 'Below price', 'aicoso-click-to-chat' ),
			'before_title'       => esc_html__( 'Above title', 'aicoso-click-to-chat' ),
		);
	}

	/**
	 * Get available position options for floating button
	 *
	 * @return array The position options.
	 */
	public function ctc_chat_get_floating_position_options() {
		return array(
			'bottom_right' => esc_html__( 'Bottom Right', 'aicoso-click-to-chat' ),
			'bottom_left'  => esc_html__( 'Bottom Left', 'aicoso-click-to-chat' ),
			'top_right'    => esc_html__( 'Top Right', 'aicoso-click-to-chat' ),
			'top_left'     => esc_html__( 'Top Left', 'aicoso-click-to-chat' ),
		);
	}

	/**
	 * Get available message template placeholders
	 *
	 * @param string $template_type The template type.
	 * @return array The available placeholders.
	 */
	public function ctc_chat_get_template_placeholders( $template_type ) {
		$ctc_chat_placeholders = array();

		switch ( $template_type ) {
			case 'ctc_chat_single_product':
				$ctc_chat_placeholders = array(
					'{product_name}' => esc_html__( 'Product name', 'aicoso-click-to-chat' ),
					'{price}'        => esc_html__( 'Product price', 'aicoso-click-to-chat' ),
					'{product_url}'  => esc_html__( 'Product URL', 'aicoso-click-to-chat' ),
				);
				break;

			case 'ctc_chat_variations':
				$ctc_chat_placeholders = array(
					'{product_name}'      => esc_html__( 'Product name', 'aicoso-click-to-chat' ),
					'{variation_details}' => esc_html__( 'Selected variation details', 'aicoso-click-to-chat' ),
					'{variation_price}'   => esc_html__( 'Selected variation price', 'aicoso-click-to-chat' ),
					'{product_url}'       => esc_html__( 'Product URL', 'aicoso-click-to-chat' ),
				);
				break;

			case 'ctc_chat_cart_checkout':
				$ctc_chat_placeholders = array(
					'{cart_items_list}' => esc_html__( 'List of items in cart', 'aicoso-click-to-chat' ),
					'{cart_subtotal}'   => esc_html__( 'Cart subtotal', 'aicoso-click-to-chat' ),
					'{tax_amount}'      => esc_html__( 'Tax amount', 'aicoso-click-to-chat' ),
					'{shipping_method}' => esc_html__( 'Selected shipping method', 'aicoso-click-to-chat' ),
					'{shipping_cost}'   => esc_html__( 'Shipping cost', 'aicoso-click-to-chat' ),
					'{cart_total}'      => esc_html__( 'Cart total', 'aicoso-click-to-chat' ),
				);
				break;

			case 'ctc_chat_thank_you':
				$ctc_chat_placeholders = array(
					'{order_number}'      => esc_html__( 'Order number', 'aicoso-click-to-chat' ),
					'{order_date}'        => esc_html__( 'Order date', 'aicoso-click-to-chat' ),
					'{ordered_items_list}' => esc_html__( 'List of ordered items', 'aicoso-click-to-chat' ),
					'{coupon_code}'       => esc_html__( 'Applied coupon code', 'aicoso-click-to-chat' ),
					'{order_total}'       => esc_html__( 'Order total', 'aicoso-click-to-chat' ),
				);
				break;

			case 'ctc_chat_floating':
				$ctc_chat_placeholders = array(
					'{current_page_url}' => esc_html__( 'Current page URL', 'aicoso-click-to-chat' ),
				);
				break;
		}

		return $ctc_chat_placeholders;
	}
}

// Initialize the class.
new CTC_Chat_Settings();
