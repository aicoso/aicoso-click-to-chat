<?php
/**
 * Storefront click tracking.
 *
 * @package ClickToChat
 * @since 1.0.2
 */

if ( ! defined( 'ABSPATH' ) ) {
	die;
}

/**
 * Click tracker.
 */
class CTC_Chat_Tracker {

	/**
	 * Register hooks.
	 */
	public function __construct() {
		add_action( 'wp_ajax_ctc_chat_track_click', array( $this, 'handle_track_click' ) );
		add_action( 'wp_ajax_nopriv_ctc_chat_track_click', array( $this, 'handle_track_click' ) );
	}

	/**
	 * Handle AJAX click tracking.
	 */
	public function handle_track_click() {
		if ( ! ctc_chat_analytics_is_enabled() ) {
			wp_send_json_success(
				array(
					'recorded' => false,
					'reason'   => 'analytics_disabled',
				)
			);
		}

		if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'ctc_chat_track_click' ) ) {
			wp_send_json_error( array( 'message' => __( 'Invalid nonce.', 'aicoso-click-to-chat' ) ), 403 );
		}

		$button_type = isset( $_POST['button_type'] ) ? sanitize_key( wp_unslash( $_POST['button_type'] ) ) : '';

		if ( ! in_array( $button_type, ctc_chat_get_allowed_button_types(), true ) ) {
			wp_send_json_error( array( 'message' => __( 'Invalid button type.', 'aicoso-click-to-chat' ) ), 400 );
		}

		$user_agent = isset( $_SERVER['HTTP_USER_AGENT'] ) ? sanitize_textarea_field( wp_unslash( $_SERVER['HTTP_USER_AGENT'] ) ) : '';

		if ( $this->is_bot( $user_agent ) ) {
			wp_send_json_success(
				array(
					'recorded' => false,
					'reason'   => 'bots_excluded',
				)
			);
		}

		$visitor_key = $this->get_visitor_key();

		if ( $this->is_rate_limited( $visitor_key ) ) {
			wp_send_json_success(
				array(
					'recorded' => false,
					'reason'   => 'rate_limited',
				)
			);
		}

		$template_type = isset( $_POST['template_type'] ) ? sanitize_key( wp_unslash( $_POST['template_type'] ) ) : '';
		if ( $template_type && ! in_array( $template_type, ctc_chat_get_allowed_template_types(), true ) ) {
			$template_type = '';
		}

		$product_id   = isset( $_POST['product_id'] ) ? absint( $_POST['product_id'] ) : 0;
		$variation_id = isset( $_POST['variation_id'] ) ? absint( $_POST['variation_id'] ) : 0;
		$order_id     = isset( $_POST['order_id'] ) ? absint( $_POST['order_id'] ) : 0;
		$number_id    = isset( $_POST['number_id'] ) ? absint( $_POST['number_id'] ) : 0;

		if ( ! $number_id && ! empty( $_POST['whatsapp_url'] ) ) {
			$whatsapp_url = esc_url_raw( wp_unslash( $_POST['whatsapp_url'] ) );
			$path         = (string) wp_parse_url( $whatsapp_url, PHP_URL_PATH );
			$phone        = preg_replace( '/[^0-9]/', '', $path );
			$number_id    = ctc_chat_resolve_number_id_by_phone( $phone );
		}

		if ( $product_id && 'product' !== get_post_type( $product_id ) ) {
			$product_id = 0;
		}

		if ( $order_id && ( ! function_exists( 'wc_get_order' ) || ! wc_get_order( $order_id ) ) ) {
			$order_id = 0;
		}

		$page_url     = isset( $_POST['page_url'] ) ? esc_url_raw( wp_unslash( $_POST['page_url'] ) ) : '';
		$referrer_url = isset( $_POST['referrer_url'] ) ? esc_url_raw( wp_unslash( $_POST['referrer_url'] ) ) : '';
		$page_path    = '';

		if ( $page_url ) {
			$parsed    = wp_parse_url( $page_url );
			$page_path = isset( $parsed['path'] ) ? substr( sanitize_text_field( $parsed['path'] ), 0, 255 ) : '';
		}

		$cart_item_count = isset( $_POST['cart_item_count'] ) ? absint( $_POST['cart_item_count'] ) : 0;
		$cart_total      = isset( $_POST['cart_total'] ) ? floatval( wp_unslash( $_POST['cart_total'] ) ) : 0;
		$cart_currency   = isset( $_POST['cart_currency'] ) ? sanitize_text_field( wp_unslash( $_POST['cart_currency'] ) ) : '';

		if ( ! $cart_currency && function_exists( 'get_woocommerce_currency' ) ) {
			$cart_currency = get_woocommerce_currency();
		}

		$settings = get_option( 'ctc_chat_settings', array() );
		$dedupe_hours = isset( $settings['analytics']['dedupe_hours'] ) ? absint( $settings['analytics']['dedupe_hours'] ) : 24;
		$is_unique    = $this->calculate_is_unique( $visitor_key, $button_type, $product_id, $order_id, $dedupe_hours );

		$ip_hash = null;
		if ( ! isset( $settings['analytics']['track_ip'] ) || $settings['analytics']['track_ip'] ) {
			$ip = $this->get_client_ip();
			if ( $ip ) {
				$ip_hash = hash_hmac( 'sha256', $ip, wp_salt( 'auth' ) );
			}
		}

		$user_agent_hash = $user_agent ? hash( 'sha256', $user_agent ) : null;

		global $wpdb;

		$table = ctc_chat_get_clicks_table_name();

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
		$inserted = $wpdb->insert(
			$table,
			array(
				'clicked_at'      => current_time( 'mysql', true ),
				'button_type'     => $button_type,
				'template_type'   => $template_type ? $template_type : null,
				'number_id'       => $number_id ? $number_id : null,
				'product_id'      => $product_id ? $product_id : null,
				'variation_id'    => $variation_id ? $variation_id : null,
				'order_id'        => $order_id ? $order_id : null,
				'cart_item_count' => $cart_item_count ? $cart_item_count : null,
				'cart_total'      => $cart_total > 0 ? $cart_total : null,
				'cart_currency'   => $cart_currency ? $cart_currency : null,
				'page_url'        => $page_url,
				'page_path'       => $page_path ? $page_path : null,
				'referrer_url'    => $referrer_url ? $referrer_url : null,
				'device_type'     => ctc_chat_detect_device_type( $user_agent ),
				'visitor_key'     => $visitor_key,
				'ip_hash'         => $ip_hash,
				'user_agent_hash' => $user_agent_hash,
				'is_unique'       => $is_unique ? 1 : 0,
				'is_bot'          => 0,
			),
			array(
				'%s',
				'%s',
				'%s',
				'%d',
				'%d',
				'%d',
				'%d',
				'%d',
				'%f',
				'%s',
				'%s',
				'%s',
				'%s',
				'%s',
				'%s',
				'%s',
				'%s',
				'%d',
				'%d',
			)
		);

		if ( false === $inserted ) {
			wp_send_json_error( array( 'message' => __( 'Could not record click.', 'aicoso-click-to-chat' ) ), 500 );
		}

		wp_send_json_success(
			array(
				'recorded' => true,
				'click_id' => (int) $wpdb->insert_id,
			)
		);
	}

	/**
	 * Determine if user agent is a bot.
	 *
	 * @param string $user_agent User agent.
	 * @return bool
	 */
	private function is_bot( $user_agent ) {
		$settings = get_option( 'ctc_chat_settings', array() );

		if ( isset( $settings['analytics']['exclude_bots'] ) && ! $settings['analytics']['exclude_bots'] ) {
			return false;
		}

		if ( function_exists( 'wp_is_bot' ) && wp_is_bot( $user_agent ) ) {
			return true;
		}

		return (bool) apply_filters( 'ctc_chat_is_bot_user_agent', false, $user_agent );
	}

	/**
	 * Get visitor key from cookie.
	 *
	 * @return string
	 */
	private function get_visitor_key() {
		$cookie = isset( $_COOKIE['ctc_vid'] ) ? sanitize_text_field( wp_unslash( $_COOKIE['ctc_vid'] ) ) : '';

		if ( ! preg_match( '/^[a-f0-9]{32}$/', $cookie ) ) {
			$cookie = bin2hex( random_bytes( 16 ) );
		}

		return ctc_chat_build_visitor_key( $cookie );
	}

	/**
	 * Rate limit check.
	 *
	 * @param string $visitor_key Visitor key.
	 * @return bool
	 */
	private function is_rate_limited( $visitor_key ) {
		global $wpdb;

		$table = ctc_chat_get_clicks_table_name();
		$since = gmdate( 'Y-m-d H:i:s', time() - MINUTE_IN_SECONDS );

		// phpcs:disable WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$count = (int) $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$table} WHERE visitor_key = %s AND clicked_at >= %s",
				$visitor_key,
				$since
			)
		);
		// phpcs:enable

		return $count >= 30;
	}

	/**
	 * Calculate uniqueness flag.
	 *
	 * @param string $visitor_key  Visitor key.
	 * @param string $button_type  Button type.
	 * @param int    $product_id   Product ID.
	 * @param int    $order_id     Order ID.
	 * @param int    $dedupe_hours Dedupe window.
	 * @return bool
	 */
	private function calculate_is_unique( $visitor_key, $button_type, $product_id, $order_id, $dedupe_hours ) {
		global $wpdb;

		$table = ctc_chat_get_clicks_table_name();
		$since = gmdate( 'Y-m-d H:i:s', time() - ( $dedupe_hours * HOUR_IN_SECONDS ) );

		// phpcs:disable WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$existing = (int) $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$table}
				WHERE visitor_key = %s
				AND button_type = %s
				AND COALESCE(product_id, 0) = %d
				AND COALESCE(order_id, 0) = %d
				AND clicked_at >= %s",
				$visitor_key,
				$button_type,
				$product_id,
				$order_id,
				$since
			)
		);
		// phpcs:enable

		return 0 === $existing;
	}

	/**
	 * Get client IP address.
	 *
	 * @return string
	 */
	private function get_client_ip() {
		$ip = '';

		if ( ! empty( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) {
			$parts = explode( ',', sanitize_text_field( wp_unslash( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) );
			$ip    = trim( $parts[0] );
		} elseif ( ! empty( $_SERVER['REMOTE_ADDR'] ) ) {
			$ip = sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) );
		}

		return filter_var( $ip, FILTER_VALIDATE_IP ) ? $ip : '';
	}
}
