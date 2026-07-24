<?php
/**
 * Analytics helper functions.
 *
 * @package ClickToChat
 * @since 1.0.2
 */

if ( ! defined( 'ABSPATH' ) ) {
	die;
}

/**
 * Check analytics admin capability.
 *
 * @return bool
 */
function ctc_chat_analytics_capability() {
	if ( current_user_can( 'manage_woocommerce' ) ) {
		return true;
	}

	return current_user_can( 'manage_options' );
}

/**
 * Get clicks table name.
 *
 * @return string
 */
function ctc_chat_get_clicks_table_name() {
	global $wpdb;

	return $wpdb->prefix . 'ctc_chat_clicks';
}

/**
 * Whether first-party click tracking is enabled.
 *
 * @return bool
 */
function ctc_chat_analytics_is_enabled() {
	$settings  = get_option( 'ctc_chat_settings', array() );
	$plugin_on = ! isset( $settings['plugin_enabled'] ) || $settings['plugin_enabled'];

	if ( ! $plugin_on ) {
		return false;
	}

	if ( empty( $settings['analytics']['enabled'] ) && array_key_exists( 'analytics', $settings ) ) {
		return false;
	}

	return ! isset( $settings['analytics']['enabled'] ) || $settings['analytics']['enabled'];
}

/**
 * Allowed button types for tracking.
 *
 * @return string[]
 */
function ctc_chat_get_allowed_button_types() {
	return array( 'product', 'shop', 'cart', 'checkout', 'thankyou', 'floating', 'shortcode' );
}

/**
 * Allowed template types for tracking.
 *
 * @return string[]
 */
function ctc_chat_get_allowed_template_types() {
	return array( 'single_product', 'variations', 'shop', 'cart_checkout', 'thank_you', 'floating', 'custom' );
}

/**
 * Human label for button type.
 *
 * @param string $button_type Button type key.
 * @return string
 */
function ctc_chat_get_button_type_label( $button_type ) {
	$labels = array(
		'product'   => __( 'Product', 'aicoso-click-to-chat' ),
		'shop'      => __( 'Shop', 'aicoso-click-to-chat' ),
		'cart'      => __( 'Cart', 'aicoso-click-to-chat' ),
		'checkout'  => __( 'Checkout', 'aicoso-click-to-chat' ),
		'thankyou'  => __( 'Thank you', 'aicoso-click-to-chat' ),
		'floating'  => __( 'Floating', 'aicoso-click-to-chat' ),
		'shortcode' => __( 'Shortcode', 'aicoso-click-to-chat' ),
	);

	return isset( $labels[ $button_type ] ) ? $labels[ $button_type ] : ucfirst( $button_type );
}

/**
 * Mask a phone number for display.
 *
 * @param string $number Raw number.
 * @return string
 */
function ctc_chat_mask_phone_number( $number ) {
	$digits = preg_replace( '/[^0-9+]/', '', (string) $number );

	if ( strlen( $digits ) < 4 ) {
		return '****';
	}

	return substr( $digits, 0, 3 ) . ' ***** ' . substr( $digits, -4 );
}

/**
 * Normalize configured WhatsApp number identities and default selection.
 *
 * Existing positive IDs are preserved. Missing or duplicate IDs receive the
 * next unused ID so an identifier is never shared by two number records. If
 * multiple records are marked default, only the last one remains default.
 *
 * @param array $numbers Number settings records.
 * @return array
 */
function ctc_chat_normalize_number_record_ids( $numbers ) {
	if ( ! is_array( $numbers ) ) {
		return array();
	}

	$normalized = array();
	$used_ids   = array();
	$next_id    = 1;
	$default_id = 0;

	foreach ( $numbers as $number_data ) {
		if ( ! is_array( $number_data ) ) {
			continue;
		}

		$number_id = isset( $number_data['id'] ) ? absint( $number_data['id'] ) : 0;
		if ( ! $number_id || isset( $used_ids[ $number_id ] ) ) {
			while ( isset( $used_ids[ $next_id ] ) ) {
				++$next_id;
			}
			$number_id = $next_id;
		}

		$number_data['id']       = $number_id;
		$used_ids[ $number_id ] = true;
		$next_id                 = max( $next_id, $number_id + 1 );
		if ( ! empty( $number_data['is_default'] ) ) {
			$default_id = $number_id;
		}
		$normalized[] = $number_data;
	}

	if ( $default_id ) {
		foreach ( $normalized as &$number_data ) {
			$number_data['is_default'] = $default_id === absint( $number_data['id'] );
		}
		unset( $number_data );
	}

	return $normalized;
}
/**
 * Resolve number ID from settings by phone digits.
 *
 * @param string $phone Phone number.
 * @return int
 */
function ctc_chat_resolve_number_id_by_phone( $phone ) {
	$settings = get_option( 'ctc_chat_settings', array() );

	if ( empty( $settings['whatsapp_numbers'] ) || empty( $phone ) ) {
		return 0;
	}

	$needle  = preg_replace( '/[^0-9]/', '', $phone );
	$numbers = ctc_chat_normalize_number_record_ids( $settings['whatsapp_numbers'] );

	foreach ( $numbers as $number_data ) {
		$haystack = preg_replace( '/[^0-9]/', '', $number_data['number'] ?? '' );
		if ( $haystack && $haystack === $needle ) {
			return absint( $number_data['id'] );
		}
	}

	return 0;
}

/**
 * Get number label from settings.
 *
 * @param int $number_id Number ID.
 * @return string
 */
function ctc_chat_get_number_label( $number_id ) {
	$number_id = absint( $number_id );
	$settings  = get_option( 'ctc_chat_settings', array() );

	if ( ! $number_id || empty( $settings['whatsapp_numbers'] ) ) {
		return __( 'Unknown', 'aicoso-click-to-chat' );
	}

	$numbers = ctc_chat_normalize_number_record_ids( $settings['whatsapp_numbers'] );
	foreach ( $numbers as $number_data ) {
		if ( absint( $number_data['id'] ) === $number_id ) {
			return ! empty( $number_data['name'] ) ? $number_data['name'] : __( 'WhatsApp', 'aicoso-click-to-chat' );
		}
	}

	return __( 'Unknown', 'aicoso-click-to-chat' );
}

/**
 * Get masked number label for reports.
 *
 * @param int $number_id Number ID.
 * @return string
 */
function ctc_chat_get_number_display( $number_id ) {
	$number_id = absint( $number_id );
	$settings  = get_option( 'ctc_chat_settings', array() );
	$label     = ctc_chat_get_number_label( $number_id );
	$masked    = '';

	if ( $number_id && ! empty( $settings['whatsapp_numbers'] ) ) {
		$numbers = ctc_chat_normalize_number_record_ids( $settings['whatsapp_numbers'] );
		foreach ( $numbers as $number_data ) {
			if ( absint( $number_data['id'] ) === $number_id ) {
				$masked = ctc_chat_mask_phone_number( $number_data['number'] ?? '' );
				break;
			}
		}
	}

	if ( $masked ) {
		return $label . ' (' . $masked . ')';
	}

	return $label;
}

/**
 * Detect device type from user agent.
 *
 * @param string $user_agent User agent string.
 * @return string
 */
function ctc_chat_detect_device_type( $user_agent = '' ) {
	if ( function_exists( 'wp_is_mobile' ) && wp_is_mobile() ) {
		if ( preg_match( '/tablet|ipad|playbook|silk/i', $user_agent ) ) {
			return 'tablet';
		}
		return 'mobile';
	}

	if ( preg_match( '/tablet|ipad|playbook|silk/i', $user_agent ) ) {
		return 'tablet';
	}

	if ( preg_match( '/mobile|android|iphone|ipod|blackberry|phone/i', $user_agent ) ) {
		return 'mobile';
	}

	return 'desktop';
}

/**
 * Build visitor key from cookie value.
 *
 * @param string $cookie_value Cookie value.
 * @return string
 */
function ctc_chat_build_visitor_key( $cookie_value ) {
	return hash( 'sha256', $cookie_value . '|' . home_url( '/' ) );
}

/**
 * Parse inclusive admin date range into UTC MySQL datetimes.
 *
 * @param string $start_date Y-m-d.
 * @param string $end_date   Y-m-d.
 * @return array{start:string,end:string,timezone:string}
 */
function ctc_chat_analytics_parse_range( $start_date, $end_date ) {
	$timezone = wp_timezone();

	try {
		$start = new DateTimeImmutable( $start_date . ' 00:00:00', $timezone );
		$end   = new DateTimeImmutable( $end_date . ' 23:59:59', $timezone );
	} catch ( Exception $e ) {
		$start = new DateTimeImmutable( 'today', $timezone );
		$end   = new DateTimeImmutable( 'today 23:59:59', $timezone );
	}

	$start_utc = $start->setTimezone( new DateTimeZone( 'UTC' ) );
	$end_utc   = $end->setTimezone( new DateTimeZone( 'UTC' ) );

	return array(
		'start'    => $start_utc->format( 'Y-m-d H:i:s' ),
		'end'      => $end_utc->format( 'Y-m-d H:i:s' ),
		'timezone' => $timezone->getName(),
	);
}

/**
 * Build comparison range preceding the primary range.
 *
 * @param string $start_date Y-m-d.
 * @param string $end_date   Y-m-d.
 * @return array{start:string,end:string}
 */
function ctc_chat_analytics_compare_range( $start_date, $end_date ) {
	$timezone = wp_timezone();

	try {
		$start = new DateTimeImmutable( $start_date, $timezone );
		$end   = new DateTimeImmutable( $end_date, $timezone );
	} catch ( Exception $e ) {
		$start = new DateTimeImmutable( 'today', $timezone );
		$end   = new DateTimeImmutable( 'today', $timezone );
	}

	$days = (int) $start->diff( $end )->days + 1;

	$compare_end   = $start->modify( '-1 day' );
	$compare_start = $compare_end->modify( '-' . ( $days - 1 ) . ' days' );

	return array(
		'start' => $compare_start->format( 'Y-m-d' ),
		'end'   => $compare_end->format( 'Y-m-d' ),
	);
}

/**
 * Format metric delta for display.
 *
 * @param float|null $current  Current value.
 * @param float|null $previous Previous value.
 * @return array{delta_pct:float|null,label:string}
 */
function ctc_chat_analytics_format_delta( $current, $previous ) {
	$current  = (float) $current;
	$previous = (float) $previous;

	if ( $previous <= 0 ) {
		return array(
			'delta_pct' => null,
			'label'     => $current > 0 ? __( 'New', 'aicoso-click-to-chat' ) : '—',
		);
	}

	$delta = ( ( $current - $previous ) / $previous ) * 100;

	return array(
		'delta_pct' => round( $delta, 1 ),
		'label'     => ( $delta >= 0 ? '+' : '' ) . round( $delta, 1 ) . '%',
	);
}

/**
 * Get cart tracking context.
 *
 * @return array
 */
function ctc_chat_get_cart_tracking_context() {
	if ( ! function_exists( 'WC' ) || ! WC()->cart ) {
		return array();
	}

	return array(
		'cart_item_count' => WC()->cart->get_cart_contents_count(),
		'cart_total'      => (float) WC()->cart->get_total( 'edit' ),
		'cart_currency'   => get_woocommerce_currency(),
	);
}
