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
class CTC_Settings {

	/**
	 * Plugin settings
	 *
	 * @var array
	 */
	private $settings;

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->settings = get_option( 'ctc_settings', array() );

		// Initialize hooks.
		$this->init_hooks();
	}

	/**
	 * Initialize hooks
	 */
	private function init_hooks() {
		// Register AJAX handlers.
		add_action( 'wp_ajax_ctc_get_setting', array( $this, 'ajax_get_setting' ) );
		add_action( 'wp_ajax_ctc_search_products', array( $this, 'ajax_search_products' ) );
		add_action( 'wp_ajax_ctc_search_categories', array( $this, 'ajax_search_categories' ) );
		add_action( 'wp_ajax_ctc_search_pages', array( $this, 'ajax_search_pages' ) );
		add_action( 'wp_ajax_ctc_search_posts', array( $this, 'ajax_search_posts' ) );
		add_action( 'wp_ajax_ctc_search_tags', array( $this, 'ajax_search_tags' ) );
		add_action( 'wp_ajax_ctc_preview_message', array( $this, 'ajax_preview_message' ) );
	}

	/**
	 * Get a specific setting
	 *
	 * @param string $key     The setting key.
	 * @param mixed  $default The default value if setting doesn't exist.
	 * @return mixed The setting value or default.
	 */
	public function get_setting( $key, $default = false ) {
		// Split the key by dots to access nested settings.
		$keys = explode( '.', $key );
		$value = $this->settings;

		// Navigate through the settings array.
		foreach ( $keys as $nested_key ) {
			if ( ! isset( $value[ $nested_key ] ) ) {
				return $default;
			}

			$value = $value[ $nested_key ];
		}

		return $value;
	}

	/**
	 * AJAX handler for getting a setting
	 */
	public function ajax_get_setting() {
		// Check nonce.
		if ( ! check_ajax_referer( 'ctc_admin_nonce', 'nonce', false ) ) {
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
		$key = sanitize_text_field( wp_unslash( $_POST['key'] ) );
		$default = isset( $_POST['default'] ) ? sanitize_text_field( wp_unslash( $_POST['default'] ) ) : false;

		$value = $this->get_setting( $key, $default );

		wp_send_json_success(
			array(
				'value' => $value,
			)
		);
	}

	/**
	 * AJAX handler for searching products
	 */
	public function ajax_search_products() {
		// Check nonce.
		if ( ! check_ajax_referer( 'ctc_admin_nonce', 'nonce', false ) ) {
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
		$term = isset( $_GET['term'] ) ? sanitize_text_field( wp_unslash( $_GET['term'] ) ) : '';

		// Search for products.
		$args = array(
			'post_type'      => 'product',
			'post_status'    => 'publish',
			'posts_per_page' => 10,
		);

		if ( ! empty( $term ) ) {
			$args['s'] = $term;
		}

		$products = get_posts( $args );

		// Format the results.
		$results = array();
		foreach ( $products as $product ) {
			$results[] = array(
				'id'   => $product->ID,
				'text' => $product->post_title,
			);
		}

		wp_send_json_success(
			array(
				'results' => $results,
			)
		);
	}

	/**
	 * AJAX handler for searching categories
	 */
	public function ajax_search_categories() {
		// Check nonce.
		if ( ! check_ajax_referer( 'ctc_admin_nonce', 'nonce', false ) ) {
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
		$term = isset( $_GET['term'] ) ? sanitize_text_field( wp_unslash( $_GET['term'] ) ) : '';

		// Search for categories.
		$args = array(
			'taxonomy'   => 'product_cat',
			'hide_empty' => false,
			'number'     => 10,
		);

		if ( ! empty( $term ) ) {
			$args['name__like'] = $term;
		}

		$categories = get_terms( $args );

		// Format the results.
		$results = array();
		if ( ! is_wp_error( $categories ) ) {
			foreach ( $categories as $category ) {
				$results[] = array(
					'id'   => $category->term_id,
					'text' => $category->name,
				);
			}
		}

		wp_send_json_success(
			array(
				'results' => $results,
			)
		);
	}

	/**
	 * AJAX handler for searching pages
	 */
	public function ajax_search_pages() {
		// Check nonce.
		if ( ! check_ajax_referer( 'ctc_admin_nonce', 'nonce', false ) ) {
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
		$term = isset( $_GET['term'] ) ? sanitize_text_field( wp_unslash( $_GET['term'] ) ) : '';

		// Search for pages.
		$args = array(
			'post_type'      => 'page',
			'post_status'    => 'publish',
			'posts_per_page' => 10,
		);

		if ( ! empty( $term ) ) {
			$args['s'] = $term;
		}

		$pages = get_posts( $args );

		// Format the results.
		$results = array();
		foreach ( $pages as $page ) {
			$results[] = array(
				'id'   => $page->ID,
				'text' => $page->post_title,
			);
		}

		wp_send_json_success(
			array(
				'results' => $results,
			)
		);
	}

	/**
	 * AJAX handler for searching posts
	 */
	public function ajax_search_posts() {
		// Check nonce.
		if ( ! check_ajax_referer( 'ctc_admin_nonce', 'nonce', false ) ) {
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
		$term = isset( $_GET['term'] ) ? sanitize_text_field( wp_unslash( $_GET['term'] ) ) : '';

		// Search for posts.
		$args = array(
			'post_type'      => 'post',
			'post_status'    => 'publish',
			'posts_per_page' => 10,
		);

		if ( ! empty( $term ) ) {
			$args['s'] = $term;
		}

		$posts = get_posts( $args );

		// Format the results.
		$results = array();
		foreach ( $posts as $post ) {
			$results[] = array(
				'id'   => $post->ID,
				'text' => $post->post_title,
			);
		}

		wp_send_json_success(
			array(
				'results' => $results,
			)
		);
	}

	/**
	 * AJAX handler for searching product tags
	 */
	public function ajax_search_tags() {
		// Check nonce.
		if ( ! check_ajax_referer( 'ctc_admin_nonce', 'nonce', false ) ) {
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
		$term = isset( $_GET['term'] ) ? sanitize_text_field( wp_unslash( $_GET['term'] ) ) : '';

		// Search for product tags.
		$args = array(
			'taxonomy'   => 'product_tag',
			'hide_empty' => false,
			'number'     => 10,
		);

		if ( ! empty( $term ) ) {
			$args['name__like'] = $term;
		}

		$tags = get_terms( $args );

		// Format the results.
		$results = array();
		if ( ! is_wp_error( $tags ) ) {
			foreach ( $tags as $tag ) {
				$results[] = array(
					'id'   => $tag->term_id,
					'text' => $tag->name,
				);
			}
		}

		wp_send_json_success(
			array(
				'results' => $results,
			)
		);
	}

	/**
	 * AJAX handler for previewing message templates
	 */
	public function ajax_preview_message() {
		// Check nonce.
		if ( ! check_ajax_referer( 'ctc_admin_nonce', 'nonce', false ) ) {
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
		$template_type = isset( $_POST['template_type'] ) ? sanitize_text_field( wp_unslash( $_POST['template_type'] ) ) : '';
		// Use wp_unslash to handle slashes and stripslashes_deep for arrays.
		// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Sanitized on next line with wp_check_invalid_utf8.
		$template_content = isset( $_POST['template_content'] ) ? wp_unslash( $_POST['template_content'] ) : '';

		// Basic sanitization without encoding entities.
		$template_content = wp_check_invalid_utf8( $template_content );

		if ( empty( $template_type ) || empty( $template_content ) ) {
			wp_send_json_error(
				array(
					'message' => esc_html__( 'Template type and content are required.', 'aicoso-click-to-chat' ),
				)
			);
		}

		// No need to decode since we're not encoding in the first place.

		// Generate a preview with sample data based on template type.
		$preview = $this->generate_template_preview( $template_type, $template_content );

		wp_send_json_success(
			array(
				'preview' => $preview,
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
	private function generate_template_preview( $template_type, $template_content ) {
		// Define sample data based on template type.
		$replacements = array();

		switch ( $template_type ) {
			case 'single_product':
				// Get price and decode HTML entities.
				if ( function_exists( 'wc_price' ) ) {
					$price = html_entity_decode( wp_strip_all_tags( wc_price( 49.99 ) ), ENT_QUOTES | ENT_HTML5, 'UTF-8' );
				} elseif ( function_exists( 'get_woocommerce_currency_symbol' ) ) {
					// Fallback: get currency symbol directly.
					$price = get_woocommerce_currency_symbol() . '49.99';
				} else {
					// Default fallback.
					$price = '$49.99';
				}
				$replacements = array(
					'{product_name}' => 'Sample Product',
					'{price}'        => $price,
					'{product_url}'  => site_url( '/product/sample-product/' ),
				);
				break;

			case 'variations':
				// Get price and decode HTML entities.
				if ( function_exists( 'wc_price' ) ) {
					$variation_price = html_entity_decode( wp_strip_all_tags( wc_price( 59.99 ) ), ENT_QUOTES | ENT_HTML5, 'UTF-8' );
				} elseif ( function_exists( 'get_woocommerce_currency_symbol' ) ) {
					// Fallback: get currency symbol directly.
					$variation_price = get_woocommerce_currency_symbol() . '59.99';
				} else {
					// Default fallback.
					$variation_price = '$59.99';
				}
				$replacements = array(
					'{product_name}'      => 'Sample Variable Product',
					'{variation_details}' => 'Color: Blue, Size: Medium',
					'{variation_price}'   => $variation_price,
					'{product_url}'       => site_url( '/product/sample-variable-product/' ),
				);
				break;

			case 'cart_checkout':
				// Decode all price entities.
				if ( function_exists( 'wc_price' ) ) {
					$price1 = html_entity_decode( wp_strip_all_tags( wc_price( 99.98 ) ), ENT_QUOTES | ENT_HTML5, 'UTF-8' );
					$price2 = html_entity_decode( wp_strip_all_tags( wc_price( 29.99 ) ), ENT_QUOTES | ENT_HTML5, 'UTF-8' );
					$subtotal = html_entity_decode( wp_strip_all_tags( wc_price( 129.97 ) ), ENT_QUOTES | ENT_HTML5, 'UTF-8' );
					$tax = html_entity_decode( wp_strip_all_tags( wc_price( 10.40 ) ), ENT_QUOTES | ENT_HTML5, 'UTF-8' );
					$shipping = html_entity_decode( wp_strip_all_tags( wc_price( 5.00 ) ), ENT_QUOTES | ENT_HTML5, 'UTF-8' );
					$total = html_entity_decode( wp_strip_all_tags( wc_price( 145.37 ) ), ENT_QUOTES | ENT_HTML5, 'UTF-8' );
				} elseif ( function_exists( 'get_woocommerce_currency_symbol' ) ) {
					$symbol = get_woocommerce_currency_symbol();
					$price1 = $symbol . '99.98';
					$price2 = $symbol . '29.99';
					$subtotal = $symbol . '129.97';
					$tax = $symbol . '10.40';
					$shipping = $symbol . '5.00';
					$total = $symbol . '145.37';
				} else {
					$price1 = '$99.98';
					$price2 = '$29.99';
					$subtotal = '$129.97';
					$tax = '$10.40';
					$shipping = '$5.00';
					$total = '$145.37';
				}

				$replacements = array(
					'{cart_items_list}' => 'Sample Product x 2 - ' . $price1 . "\n" .
										 'Another Product x 1 - ' . $price2,
					'{cart_subtotal}'   => $subtotal,
					'{tax_amount}'      => $tax,
					'{shipping_method}' => 'Flat rate',
					'{shipping_cost}'   => $shipping,
					'{cart_total}'      => $total,
				);
				break;

			case 'thank_you':
				// Decode all price entities.
				if ( function_exists( 'wc_price' ) ) {
					$price1 = html_entity_decode( wp_strip_all_tags( wc_price( 99.98 ) ), ENT_QUOTES | ENT_HTML5, 'UTF-8' );
					$price2 = html_entity_decode( wp_strip_all_tags( wc_price( 29.99 ) ), ENT_QUOTES | ENT_HTML5, 'UTF-8' );
					$order_total = html_entity_decode( wp_strip_all_tags( wc_price( 134.97 ) ), ENT_QUOTES | ENT_HTML5, 'UTF-8' );
				} elseif ( function_exists( 'get_woocommerce_currency_symbol' ) ) {
					$symbol = get_woocommerce_currency_symbol();
					$price1 = $symbol . '99.98';
					$price2 = $symbol . '29.99';
					$order_total = $symbol . '134.97';
				} else {
					$price1 = '$99.98';
					$price2 = '$29.99';
					$order_total = '$134.97';
				}

				$date_format = function_exists( 'wc_date_format' ) ? wc_date_format() : get_option( 'date_format' );
				$replacements = array(
					'{order_number}'      => '12345',
					'{order_date}'        => date_i18n( $date_format, time() ),
					'{ordered_items_list}' => 'Sample Product x 2 - ' . $price1 . "\n" .
											'Another Product x 1 - ' . $price2,
					'{coupon_code}'       => 'SAMPLE10',
					'{order_total}'       => $order_total,
				);
				break;

			case 'floating':
				$replacements = array(
					'{current_page_url}' => site_url( '/sample-page/' ),
				);
				break;
		}

		// Replace placeholders with sample data.
		foreach ( $replacements as $placeholder => $value ) {
			$template_content = str_replace( $placeholder, $value, $template_content );
		}

		return $template_content;
	}

	/**
	 * Get available position options for product pages
	 *
	 * @return array The position options.
	 */
	public function get_product_position_options() {
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
	public function get_shop_position_options() {
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
	public function get_floating_position_options() {
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
	public function get_template_placeholders( $template_type ) {
		$placeholders = array();

		switch ( $template_type ) {
			case 'single_product':
				$placeholders = array(
					'{product_name}' => esc_html__( 'Product name', 'aicoso-click-to-chat' ),
					'{price}'        => esc_html__( 'Product price', 'aicoso-click-to-chat' ),
					'{product_url}'  => esc_html__( 'Product URL', 'aicoso-click-to-chat' ),
				);
				break;

			case 'variations':
				$placeholders = array(
					'{product_name}'      => esc_html__( 'Product name', 'aicoso-click-to-chat' ),
					'{variation_details}' => esc_html__( 'Selected variation details', 'aicoso-click-to-chat' ),
					'{variation_price}'   => esc_html__( 'Selected variation price', 'aicoso-click-to-chat' ),
					'{product_url}'       => esc_html__( 'Product URL', 'aicoso-click-to-chat' ),
				);
				break;

			case 'cart_checkout':
				$placeholders = array(
					'{cart_items_list}' => esc_html__( 'List of items in cart', 'aicoso-click-to-chat' ),
					'{cart_subtotal}'   => esc_html__( 'Cart subtotal', 'aicoso-click-to-chat' ),
					'{tax_amount}'      => esc_html__( 'Tax amount', 'aicoso-click-to-chat' ),
					'{shipping_method}' => esc_html__( 'Selected shipping method', 'aicoso-click-to-chat' ),
					'{shipping_cost}'   => esc_html__( 'Shipping cost', 'aicoso-click-to-chat' ),
					'{cart_total}'      => esc_html__( 'Cart total', 'aicoso-click-to-chat' ),
				);
				break;

			case 'thank_you':
				$placeholders = array(
					'{order_number}'      => esc_html__( 'Order number', 'aicoso-click-to-chat' ),
					'{order_date}'        => esc_html__( 'Order date', 'aicoso-click-to-chat' ),
					'{ordered_items_list}' => esc_html__( 'List of ordered items', 'aicoso-click-to-chat' ),
					'{coupon_code}'       => esc_html__( 'Applied coupon code', 'aicoso-click-to-chat' ),
					'{order_total}'       => esc_html__( 'Order total', 'aicoso-click-to-chat' ),
				);
				break;

			case 'floating':
				$placeholders = array(
					'{current_page_url}' => esc_html__( 'Current page URL', 'aicoso-click-to-chat' ),
				);
				break;
		}

		return $placeholders;
	}
}

// Initialize the class.
new CTC_Settings();
