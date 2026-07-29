<?php
/**
 * WP-CLI-compatible dashboard WhatsApp number filter fixture matrix.
 *
 * Run only against a disposable local WordPress installation.
 *
 * @package ClickToChat
 */

if ( ! defined( 'ABSPATH' ) ) {
	die;
}

global $wpdb;

$ctc_dnf_table          = ctc_chat_get_clicks_table_name();
$ctc_dnf_prefix         = 'ctc-dnf-' . wp_generate_uuid4();
$ctc_dnf_failures       = array();
$ctc_dnf_original_user  = get_current_user_id();
$ctc_dnf_settings       = get_option( 'ctc_chat_settings', false );
$ctc_dnf_settings_hash  = hash( 'sha256', serialize( $ctc_dnf_settings ) );
$ctc_dnf_current_start  = '2098-01-08';
$ctc_dnf_current_end    = '2098-01-14';
$ctc_dnf_previous_start = '2098-01-01';
$ctc_dnf_previous_end   = '2098-01-07';
$ctc_dnf_future_start   = '2099-01-01';
$ctc_dnf_future_end      = '2099-01-07';
$ctc_dnf_last_die_status = null;

$ctc_dnf_assert = static function ( $condition, $message ) use ( &$ctc_dnf_failures ) {
	if ( ! $condition ) {
		$ctc_dnf_failures[] = $message;
	}
};

$ctc_dnf_non_fixture_fingerprint = static function () use ( $wpdb, $ctc_dnf_table, $ctc_dnf_prefix ) {
	// phpcs:disable WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
	$rows = $wpdb->get_results(
		$wpdb->prepare(
			"SELECT * FROM {$ctc_dnf_table} WHERE visitor_key NOT LIKE %s ORDER BY id ASC",
			$wpdb->esc_like( $ctc_dnf_prefix ) . '%'
		),
		ARRAY_A
	);
	// phpcs:enable

	return array(
		'count' => count( (array) $rows ),
		'hash'  => hash( 'sha256', serialize( $rows ) ),
	);
};

$ctc_dnf_before = $ctc_dnf_non_fixture_fingerprint();

$ctc_dnf_datetime = static function ( $date, $hour ) {
	$range = ctc_chat_analytics_parse_range( $date, $date );

	return gmdate( 'Y-m-d H:i:s', strtotime( $range['start'] ) + ( HOUR_IN_SECONDS * $hour ) );
};

$ctc_dnf_insert = static function ( $row ) use ( $wpdb, $ctc_dnf_table, $ctc_dnf_prefix, $ctc_dnf_assert ) {
	static $sequence = 0;

	++$sequence;
	$row = wp_parse_args(
		$row,
		array(
			'clicked_at'      => gmdate( 'Y-m-d H:i:s' ),
			'button_type'     => 'product',
			'template_type'   => 'single_product',
			'number_id'       => null,
			'product_id'      => null,
			'variation_id'    => null,
			'order_id'        => null,
			'cart_item_count' => null,
			'cart_total'      => null,
			'cart_currency'   => 'USD',
			'page_url'        => 'https://example.test/product/',
			'page_path'       => '/product/',
			'referrer_url'    => '',
			'device_type'     => 'desktop',
			'ip_hash'         => null,
			'user_agent_hash' => null,
			'is_unique'       => 0,
			'is_bot'          => 0,
		)
	);
	$row['visitor_key'] = $ctc_dnf_prefix . '-' . str_pad( (string) $sequence, 3, '0', STR_PAD_LEFT );

	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
	$inserted = $wpdb->insert( $ctc_dnf_table, $row );
	$ctc_dnf_assert( false !== $inserted, 'Could not insert fixture click row ' . $sequence . '.' );
};

$ctc_dnf_sum_series = static function ( $payload ) {
	return array_sum( wp_list_pluck( $payload['series'], 'clicks' ) );
};

$ctc_dnf_sum_stages = static function ( $payload ) {
	return array_sum( wp_list_pluck( $payload['stages'], 'count' ) );
};

$ctc_dnf_capture_ajax = static function ( $callback, $post ) use ( &$ctc_dnf_last_die_status ) {
	$previous_post    = $_POST;
	$previous_request = $_REQUEST;
	$output                  = '';
	$ctc_dnf_last_die_status = null;
	$status_handler          = static function ( $status_header, $status_code ) use ( &$ctc_dnf_last_die_status ) {
		$ctc_dnf_last_die_status = (int) $status_code;

		return $status_header;
	};
	$die_handler             = static function () use ( &$ctc_dnf_last_die_status ) {
		return static function ( $message, $title, $args ) use ( &$ctc_dnf_last_die_status ) {
			if ( null === $ctc_dnf_last_die_status && is_array( $args ) && isset( $args['response'] ) ) {
				$ctc_dnf_last_die_status = (int) $args['response'];
			} elseif ( null === $ctc_dnf_last_die_status && is_numeric( $title ) ) {
				$ctc_dnf_last_die_status = (int) $title;
			}
			throw new RuntimeException( 'ctc-dnf-wp-die' );
		};
	};

	$_POST    = $post;
	$_REQUEST = $post;
	add_filter( 'status_header', $status_handler, 10, 2 );
	add_filter( 'wp_die_ajax_handler', $die_handler );

	ob_start();
	try {
		call_user_func( $callback );
	} catch ( RuntimeException $exception ) {
		if ( 'ctc-dnf-wp-die' !== $exception->getMessage() ) {
			throw $exception;
		}
	} finally {
		$output   = ob_get_clean();
		$_POST    = $previous_post;
		$_REQUEST = $previous_request;
		remove_filter( 'status_header', $status_handler, 10 );
		remove_filter( 'wp_die_ajax_handler', $die_handler );
	}

	return json_decode( $output, true );
};

$ctc_dnf_restore = static function () use (
	$wpdb,
	$ctc_dnf_table,
	$ctc_dnf_prefix,
	$ctc_dnf_settings,
	$ctc_dnf_original_user
) {
	// phpcs:disable WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
	$wpdb->query(
		$wpdb->prepare(
			"DELETE FROM {$ctc_dnf_table} WHERE visitor_key LIKE %s",
			$wpdb->esc_like( $ctc_dnf_prefix ) . '%'
		)
	);
	// phpcs:enable

	if ( false === $ctc_dnf_settings ) {
		delete_option( 'ctc_chat_settings' );
	} else {
		update_option( 'ctc_chat_settings', $ctc_dnf_settings );
	}

	wp_set_current_user( $ctc_dnf_original_user );
};

try {
	$settings                       = is_array( $ctc_dnf_settings ) ? $ctc_dnf_settings : ctc_chat_get_default_settings();
	$settings['analytics']['enabled'] = true;
	$settings['whatsapp_numbers']  = array(
		array(
			'id'          => 101,
			'name'        => 'Fixture Sales',
			'number'      => '+15550000101',
			'is_default'  => true,
			'assignments' => array(),
		),
		array(
			'id'          => 202,
			'name'        => 'Fixture Support',
			'number'      => '+15550000202',
			'is_default'  => false,
			'assignments' => array(),
		),
	);
	update_option( 'ctc_chat_settings', $settings );

	$current_rows = array(
		array( 101, 'product', 910101, 0, 'desktop', 1 ),
		array( 101, 'cart', null, 100, 'mobile', 0 ),
		array( 101, 'checkout', null, 200, 'mobile', 1 ),
		array( 202, 'shop', 910202, 0, 'desktop', 1 ),
		array( 202, 'thankyou', null, 300, 'unknown', 0 ),
		array( 303, 'cart', null, 400, 'desktop', 1 ),
		array( null, 'checkout', null, 500, 'mobile', 1 ),
		array( 0, 'product', 910000, 0, 'tablet', 0 ),
	);

	foreach ( $current_rows as $index => $values ) {
		$ctc_dnf_insert(
			array(
				'clicked_at'  => $ctc_dnf_datetime( $ctc_dnf_current_start, $index + 1 ),
				'number_id'   => $values[0],
				'button_type' => $values[1],
				'product_id'  => $values[2],
				'cart_total'  => $values[3],
				'device_type' => $values[4],
				'is_unique'   => $values[5],
				'page_path'   => '/' . $values[1] . '/',
				'page_url'    => 'https://example.test/' . $values[1] . '/',
			)
		);
	}

	$previous_rows = array(
		array( 101, 'shop', 910101, 0, 'desktop', 1 ),
		array( 101, 'cart', null, 50, 'mobile', 0 ),
		array( 202, 'checkout', null, 75, 'mobile', 1 ),
		array( null, 'product', 910000, 0, 'unknown', 1 ),
		array( 0, 'thankyou', null, 0, 'desktop', 0 ),
		array( 303, 'product', 910303, 0, 'desktop', 1 ),
	);

	foreach ( $previous_rows as $index => $values ) {
		$ctc_dnf_insert(
			array(
				'clicked_at'  => $ctc_dnf_datetime( $ctc_dnf_previous_start, $index + 1 ),
				'number_id'   => $values[0],
				'button_type' => $values[1],
				'product_id'  => $values[2],
				'cart_total'  => $values[3],
				'device_type' => $values[4],
				'is_unique'   => $values[5],
				'page_path'   => '/' . $values[1] . '/',
				'page_url'    => 'https://example.test/' . $values[1] . '/',
			)
		);
	}

	$analytics = new CTC_Chat_Analytics();
	$scopes    = array(
		'all'          => array(
			'filters' => array(),
			'clicks'  => 8,
			'prior'   => 6,
		),
		'number-101'   => array(
			'filters' => array(
				'number_mode' => 'number',
				'number_id'   => 101,
			),
			'clicks'  => 3,
			'prior'   => 2,
		),
		'number-202'   => array(
			'filters' => array(
				'number_mode' => 'number',
				'number_id'   => 202,
			),
			'clicks'  => 2,
			'prior'   => 1,
		),
		'unattributed' => array(
			'filters' => array( 'number_mode' => 'unattributed' ),
			'clicks'  => 2,
			'prior'   => 2,
		),
	);

	foreach ( $scopes as $scope => $expected ) {
		$kpis = $analytics->get_kpis(
			$ctc_dnf_current_start,
			$ctc_dnf_current_end,
			true,
			$expected['filters']
		);
		$ctc_dnf_assert( $expected['clicks'] === $kpis['total_clicks']['value'], $scope . ' KPI total mismatch.' );
		$ctc_dnf_assert( $expected['prior'] === $kpis['total_clicks']['compare_value'], $scope . ' prior KPI total mismatch.' );

		$trend = $analytics->get_trend( $ctc_dnf_current_start, $ctc_dnf_current_end, 'day', $expected['filters'] );
		$ctc_dnf_assert( $expected['clicks'] === $ctc_dnf_sum_series( $trend ), $scope . ' trend total mismatch.' );

		$funnel = $analytics->get_funnel( $ctc_dnf_current_start, $ctc_dnf_current_end, $expected['filters'] );
		$ctc_dnf_assert( $expected['clicks'] === $ctc_dnf_sum_stages( $funnel ), $scope . ' funnel total mismatch.' );

		$products = $analytics->get_top_products( $ctc_dnf_current_start, $ctc_dnf_current_end, 10, $expected['filters'] );
		if ( 'number-101' === $scope && ! empty( $products['items'] ) ) {
			$ctc_dnf_assert( array( 910101 ) === array_values( array_unique( wp_list_pluck( $products['items'], 'product_id' ) ) ), 'Number 101 product scope leaked.' );
		}
		if ( 'number-202' === $scope && ! empty( $products['items'] ) ) {
			$ctc_dnf_assert( array( 910202 ) === array_values( array_unique( wp_list_pluck( $products['items'], 'product_id' ) ) ), 'Number 202 product scope leaked.' );
		}

		$numbers = $analytics->get_top_numbers( $ctc_dnf_current_start, $ctc_dnf_current_end, 10, $expected['filters'] );
		if ( 'number-101' === $scope ) {
			$ctc_dnf_assert( array( 101 ) === wp_list_pluck( $numbers['items'], 'number_id' ), 'Top numbers did not isolate number 101.' );
		}
		if ( 'number-202' === $scope ) {
			$ctc_dnf_assert( array( 202 ) === wp_list_pluck( $numbers['items'], 'number_id' ), 'Top numbers did not isolate number 202.' );
		}
		if ( 'unattributed' === $scope ) {
			$ctc_dnf_assert( array( 0 ) === wp_list_pluck( $numbers['items'], 'number_id' ), 'Top numbers did not isolate Unattributed.' );
			$ctc_dnf_assert( '' === $numbers['items'][0]['report_url'], 'Unattributed exposed a misleading report deep link.' );
		}
	}

	$zero = $analytics->get_kpis( $ctc_dnf_future_start, $ctc_dnf_future_end, false, array( 'number_mode' => 'number', 'number_id' => 101 ) );
	$ctc_dnf_assert( 0 === $zero['total_clicks']['value'], 'Zero-result KPI did not stay zero.' );
	$ctc_dnf_assert( null === $zero['total_clicks']['compare_value'], 'Comparison-disabled KPI returned a prior value.' );
	$ctc_dnf_assert( 8 === $analytics->get_kpis( $ctc_dnf_current_start, $ctc_dnf_current_end, false )['total_clicks']['value'], 'Omitted-filter KPI did not preserve All.' );
	$ctc_dnf_assert( 8 === $ctc_dnf_sum_series( $analytics->get_trend( $ctc_dnf_current_start, $ctc_dnf_current_end ) ), 'Omitted-filter trend did not preserve All.' );
	$ctc_dnf_assert( 8 === $ctc_dnf_sum_stages( $analytics->get_funnel( $ctc_dnf_current_start, $ctc_dnf_current_end ) ), 'Omitted-filter funnel did not preserve All.' );
	$ctc_dnf_assert( 3 === count( $analytics->get_top_products( $ctc_dnf_current_start, $ctc_dnf_current_end, 10 )['items'] ), 'Omitted-filter top products did not preserve All.' );
	$ctc_dnf_assert( 4 === count( $analytics->get_top_numbers( $ctc_dnf_current_start, $ctc_dnf_current_end, 10 )['items'] ), 'Omitted-filter top numbers did not preserve All.' );
	$ctc_dnf_assert( 0 === $ctc_dnf_sum_series( $analytics->get_trend( $ctc_dnf_future_start, $ctc_dnf_future_end, 'day', array( 'number_mode' => 'number', 'number_id' => 101 ) ) ), 'Zero-result trend did not stay zero.' );
	$ctc_dnf_assert( 0 === $ctc_dnf_sum_stages( $analytics->get_funnel( $ctc_dnf_future_start, $ctc_dnf_future_end, array( 'number_mode' => 'number', 'number_id' => 101 ) ) ), 'Zero-result funnel did not stay zero.' );
	$ctc_dnf_assert( empty( $analytics->get_top_products( $ctc_dnf_future_start, $ctc_dnf_future_end, 10, array( 'number_mode' => 'number', 'number_id' => 101 ) )['items'] ), 'Zero-result top products were not empty.' );
	$ctc_dnf_assert( empty( $analytics->get_top_numbers( $ctc_dnf_future_start, $ctc_dnf_future_end, 10, array( 'number_mode' => 'number', 'number_id' => 101 ) )['items'] ), 'Zero-result top numbers were not empty.' );

	$admin_users = get_users(
		array(
			'role'   => 'administrator',
			'number' => 1,
			'fields' => 'ID',
		)
	);
	if ( ! empty( $admin_users ) ) {
		if ( ! defined( 'DOING_AJAX' ) ) {
			define( 'DOING_AJAX', true );
		}
		wp_set_current_user( (int) $admin_users[0] );
		$admin = new CTC_Chat_Analytics_Admin();
		$nonce = wp_create_nonce( 'ctc_chat_admin_nonce' );

		$actions = array(
			array( 'ajax_kpis', 'ctc_analytics_kpis' ),
			array( 'ajax_trend', 'ctc_analytics_trend' ),
			array( 'ajax_funnel', 'ctc_analytics_funnel' ),
			array( 'ajax_top_products', 'ctc_analytics_top_products' ),
			array( 'ajax_top_numbers', 'ctc_analytics_top_numbers' ),
		);

		foreach ( $actions as $action ) {
			$response = $ctc_dnf_capture_ajax(
				array( $admin, $action[0] ),
				array(
					'action'        => $action[1],
					'nonce'         => $nonce,
					'start_date'    => $ctc_dnf_current_start,
					'end_date'      => $ctc_dnf_current_end,
					'compare'       => 'false',
					'grouping'      => 'day',
					'number_filter' => '101',
				)
			);
			$ctc_dnf_assert( is_array( $response ) && ! empty( $response['success'] ), $action[1] . ' did not return success.' );
			$ctc_dnf_assert(
				isset( $response['data']['filter_context']['resolved'] ) && '101' === $response['data']['filter_context']['resolved'],
				$action[1] . ' did not return the exact filter context.'
			);
			$ctc_dnf_assert( false === strpos( wp_json_encode( $response ), '15550000101' ), $action[1] . ' exposed a full phone number.' );
		}

		foreach ( $actions as $action ) {
			$base_post = array(
				'action'     => $action[1],
				'nonce'      => $nonce,
				'start_date' => $ctc_dnf_current_start,
				'end_date'   => $ctc_dnf_current_end,
				'compare'    => 'false',
				'grouping'   => 'day',
			);
			$response  = $ctc_dnf_capture_ajax( array( $admin, $action[0] ), $base_post );
			$ctc_dnf_assert( is_array( $response ) && ! empty( $response['success'] ), $action[1] . ' omitted-filter request failed.' );
			$ctc_dnf_assert(
				isset( $response['data']['filter_context']['resolved'] ) && 'all' === $response['data']['filter_context']['resolved'],
				$action[1] . ' omitted filter did not resolve to All.'
			);

			$base_post['start_date']    = $ctc_dnf_future_start;
			$base_post['end_date']      = $ctc_dnf_future_end;
			$base_post['number_filter'] = '101';
			$response                   = $ctc_dnf_capture_ajax( array( $admin, $action[0] ), $base_post );
			$ctc_dnf_assert( is_array( $response ) && ! empty( $response['success'] ), $action[1] . ' zero-result request failed.' );
			if ( 'ajax_kpis' === $action[0] ) {
				$ctc_dnf_assert( 0 === $response['data']['total_clicks']['value'], 'Zero-result KPI AJAX total was not zero.' );
			} elseif ( 'ajax_trend' === $action[0] ) {
				$ctc_dnf_assert( 0 === $ctc_dnf_sum_series( $response['data'] ), 'Zero-result trend AJAX total was not zero.' );
			} elseif ( 'ajax_funnel' === $action[0] ) {
				$ctc_dnf_assert( 0 === $ctc_dnf_sum_stages( $response['data'] ), 'Zero-result funnel AJAX total was not zero.' );
			} else {
				$ctc_dnf_assert( empty( $response['data']['items'] ), $action[1] . ' zero-result items were not empty.' );
			}
		}

		$authorized_post = array(
			'action'        => 'ctc_analytics_kpis',
			'nonce'         => $nonce,
			'start_date'    => $ctc_dnf_current_start,
			'end_date'      => $ctc_dnf_current_end,
			'compare'       => 'false',
			'number_filter' => '101',
		);
		wp_set_current_user( 0 );
		$response = $ctc_dnf_capture_ajax( array( $admin, 'ajax_kpis' ), $authorized_post );
		$ctc_dnf_assert( is_array( $response ) && empty( $response['success'] ), 'Insufficient capability request was not rejected.' );
		$ctc_dnf_assert( 403 === $ctc_dnf_last_die_status, 'Insufficient capability request did not return HTTP 403.' );

		wp_set_current_user( (int) $admin_users[0] );
		$bad_nonce_post          = $authorized_post;
		$bad_nonce_post['nonce'] = 'invalid';
		$response                = $ctc_dnf_capture_ajax( array( $admin, 'ajax_kpis' ), $bad_nonce_post );
		$ctc_dnf_assert( 403 === $ctc_dnf_last_die_status, 'Bad nonce was not rejected with HTTP 403.' );

		unset( $bad_nonce_post['nonce'] );
		$response = $ctc_dnf_capture_ajax( array( $admin, 'ajax_kpis' ), $bad_nonce_post );
		$ctc_dnf_assert( 403 === $ctc_dnf_last_die_status, 'Missing nonce was not rejected with HTTP 403.' );

		$settings['whatsapp_numbers'][0]['name'] = '<script>alert("fixture")</script>';
		update_option( 'ctc_chat_settings', $settings );
		$page_admin = new CTC_Chat_Admin();
		ob_start();
		$page_admin->render_dashboard_page();
		$dashboard_markup = ob_get_clean();
		$ctc_dnf_assert( false === strpos( $dashboard_markup, '<script>alert("fixture")</script>' ), 'HTML-like number name was not escaped.' );
		$ctc_dnf_assert( false !== strpos( $dashboard_markup, '&lt;script&gt;alert(&quot;fixture&quot;)&lt;/script&gt;' ), 'Escaped HTML-like number name was not rendered.' );
		$ctc_dnf_assert( false === strpos( $dashboard_markup, '15550000101' ), 'Dashboard markup exposed a full phone number.' );
		$ctc_dnf_assert( false === strpos( $dashboard_markup, '15550000202' ), 'Dashboard markup exposed a second full phone number.' );
		$settings['whatsapp_numbers'][0]['name'] = 'Fixture Sales';
		update_option( 'ctc_chat_settings', $settings );

		$reflection = new ReflectionClass( $admin );
		$resolver   = $reflection->getMethod( 'resolve_number_filter' );
		$resolver->setAccessible( true );
		$invalid_values = array( '', '999', '303', '0', '-1', '7.0', array( '7' ), '7 OR 1=1' );
		foreach ( $invalid_values as $invalid ) {
			$_POST['number_filter'] = $invalid;
			$resolved               = $resolver->invoke( $admin );
			$ctc_dnf_assert( 'all' === $resolved['context']['resolved'], 'Invalid filter did not resolve to All.' );
			$ctc_dnf_assert( true === $resolved['context']['fell_back'], 'Invalid filter did not signal fallback.' );
		}
		unset( $_POST['number_filter'] );
		$omitted = $resolver->invoke( $admin );
		$ctc_dnf_assert( 'all' === $omitted['context']['resolved'] && false === $omitted['context']['fell_back'], 'Omitted filter did not resolve cleanly to All.' );

		$settings['whatsapp_numbers'][0]['name']       = 'Renamed Fixture Sales';
		$settings['whatsapp_numbers'][0]['is_default'] = false;
		$settings['whatsapp_numbers'][1]['is_default'] = true;
		update_option( 'ctc_chat_settings', $settings );
		$renamed = $analytics->get_kpis( $ctc_dnf_current_start, $ctc_dnf_current_end, true, array( 'number_mode' => 'number', 'number_id' => 101 ) );
		$ctc_dnf_assert( 3 === $renamed['total_clicks']['value'], 'Rename/default change altered current number 101 history.' );
		$ctc_dnf_assert( 2 === $renamed['total_clicks']['compare_value'], 'Rename/default change altered prior number 101 history.' );

		array_shift( $settings['whatsapp_numbers'] );
		update_option( 'ctc_chat_settings', $settings );
		$_POST['number_filter'] = '101';
		$removed                = $resolver->invoke( $admin );
		$ctc_dnf_assert( 'all' === $removed['context']['resolved'] && true === $removed['context']['fell_back'], 'Removed selected number did not fall back to All.' );
		unset( $_POST['number_filter'] );
		foreach ( $actions as $action ) {
			$response = $ctc_dnf_capture_ajax(
				array( $admin, $action[0] ),
				array(
					'action'        => $action[1],
					'nonce'         => $nonce,
					'start_date'    => $ctc_dnf_current_start,
					'end_date'      => $ctc_dnf_current_end,
					'compare'       => 'false',
					'grouping'      => 'day',
					'number_filter' => '101',
				)
			);
			$ctc_dnf_assert( is_array( $response ) && ! empty( $response['success'] ), $action[1] . ' stale-filter fallback request failed.' );
			$ctc_dnf_assert(
				'all' === $response['data']['filter_context']['resolved'] && true === $response['data']['filter_context']['fell_back'],
				$action[1] . ' did not revalidate a concurrently removed selection.'
			);
		}
	} else {
		$ctc_dnf_failures[] = 'No administrator user exists for AJAX fixture checks.';
	}

	$ctc_dnf_after = $ctc_dnf_non_fixture_fingerprint();
	$ctc_dnf_assert( $ctc_dnf_before === $ctc_dnf_after, 'Non-fixture analytics rows changed during filter verification.' );
} finally {
	$ctc_dnf_restore();
}

$ctc_dnf_restored_settings_hash = hash( 'sha256', serialize( get_option( 'ctc_chat_settings', false ) ) );
$ctc_dnf_assert( $ctc_dnf_settings_hash === $ctc_dnf_restored_settings_hash, 'Settings were not restored exactly.' );

if ( empty( $ctc_dnf_failures ) ) {
	echo "dashboard-number-filter-fixtures: PASS\n";
} else {
	echo "dashboard-number-filter-fixtures: FAIL\n";
	foreach ( $ctc_dnf_failures as $ctc_dnf_failure ) {
		echo '- ' . $ctc_dnf_failure . "\n";
	}
	if ( defined( 'WP_CLI' ) && WP_CLI ) {
		WP_CLI::error( 'Dashboard number filter fixture failures detected.' );
	}
}
