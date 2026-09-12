<?php
/**
 * Self-restoring scale benchmark for the dashboard number filter.
 *
 * Run only against a disposable local WordPress installation.
 *
 * @package ClickToChat
 */

if ( ! defined( 'ABSPATH' ) ) {
	die;
}

global $wpdb;

$table             = ctc_chat_get_clicks_table_name();
$prefix            = 'ctc-dnf-perf-' . wp_generate_uuid4();
$original_settings = get_option( 'ctc_chat_settings', false );
$start_date        = '2097-01-01';
$end_date          = '2097-01-30';
$number_id         = 1001;
$row_target        = 100000;
$batch_size        = 500;
$result            = array();

try {
	$settings                        = is_array( $original_settings ) ? $original_settings : ctc_chat_get_default_settings();
	$settings['whatsapp_numbers']   = array();
	for ( $index = 1; $index <= 100; ++$index ) {
		$settings['whatsapp_numbers'][] = array(
			'id'          => 1000 + $index,
			'name'        => 'Performance ' . $index,
			'number'      => '+1555' . str_pad( (string) $index, 7, '0', STR_PAD_LEFT ),
			'is_default'  => 1 === $index,
			'assignments' => array(),
		);
	}
	update_option( 'ctc_chat_settings', $settings );

	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
	$wpdb->query( 'START TRANSACTION' );
	for ( $offset = 0; $offset < $row_target; $offset += $batch_size ) {
		$values       = array();
		$placeholders = array();
		$batch_end    = min( $row_target, $offset + $batch_size );

		for ( $row = $offset; $row < $batch_end; ++$row ) {
			$day          = ( $row % 30 ) + 1;
			$scope_number = 0 === $row % 50 ? 0 : 1001 + ( $row % 100 );
			$types        = array( 'product', 'shop', 'cart', 'checkout', 'thankyou' );
			$type         = $types[ $row % count( $types ) ];
			$product_id   = in_array( $type, array( 'product', 'shop' ), true ) ? 5000 + ( $row % 50 ) : null;

			$placeholders[] = '(%s,%s,%d,%d,%f,%s,%s,%s,%d,%d)';
			array_push(
				$values,
				sprintf( '2097-01-%02d %02d:%02d:%02d', $day, $row % 24, $row % 60, $row % 60 ),
				$type,
				$scope_number,
				$product_id,
				(float) ( $row % 1000 ),
				'USD',
				0 === $row % 2 ? 'desktop' : 'mobile',
				$prefix . '-' . $row,
				$row % 2,
				0
			);
		}

		// phpcs:disable WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQL.NotPrepared
		$inserted = $wpdb->query(
			$wpdb->prepare(
				"INSERT INTO {$table}
				(clicked_at, button_type, number_id, product_id, cart_total, cart_currency, device_type, visitor_key, is_unique, is_bot)
				VALUES " . implode( ',', $placeholders ),
				...$values
			)
		);
		// phpcs:enable

		if ( false === $inserted ) {
			throw new RuntimeException( 'Performance fixture insert failed at offset ' . $offset . '.' );
		}
	}
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
	$wpdb->query( 'COMMIT' );

	$range   = ctc_chat_analytics_parse_range( $start_date, $end_date );
	$explain = $wpdb->get_row(
		$wpdb->prepare(
			"EXPLAIN SELECT clicked_at, is_unique FROM {$table}
			WHERE clicked_at BETWEEN %s AND %s AND number_id = %d
			ORDER BY clicked_at ASC",
			$range['start'],
			$range['end'],
			$number_id
		),
		ARRAY_A
	);

	$analytics = new CTC_Chat_Analytics();
	$scopes    = array(
		'all'          => array(),
		'exact'        => array(
			'number_mode' => 'number',
			'number_id'   => $number_id,
		),
		'unattributed' => array( 'number_mode' => 'unattributed' ),
	);
	$scope_results = array();
	$passed        = 'number_clicked_at' === ( $explain['key'] ?? '' );

	foreach ( $scopes as $scope_name => $filter ) {
		$durations = array();
		for ( $run = 0; $run < 21; ++$run ) {
			$started = microtime( true );
			$analytics->get_kpis( $start_date, $end_date, true, $filter );
			$analytics->get_trend( $start_date, $end_date, 'day', $filter );
			$analytics->get_funnel( $start_date, $end_date, $filter );
			$analytics->get_top_products( $start_date, $end_date, 5, $filter );
			$analytics->get_top_numbers( $start_date, $end_date, 5, $filter );
			$elapsed = microtime( true ) - $started;

			if ( $run > 0 ) {
				$durations[] = round( $elapsed, 6 );
			}
		}

		$within_target = count(
			array_filter(
				$durations,
				static function ( $duration ) {
					return $duration < 2;
				}
			)
		);
		$sorted        = $durations;
		sort( $sorted );
		$scope_passed = $within_target >= 19;
		$passed       = $passed && $scope_passed;

		$scope_results[ $scope_name ] = array(
			'refresh_seconds'    => $durations,
			'within_two_seconds' => $within_target,
			'p95_seconds'        => $sorted[18] ?? null,
			'passed'             => $scope_passed,
		);
	}

	$result = array(
		'configured_numbers' => 100,
		'fixture_rows'       => $row_target,
		'explain_key'        => $explain['key'] ?? null,
		'explain_rows'       => isset( $explain['rows'] ) ? (int) $explain['rows'] : null,
		'scopes'             => $scope_results,
		'passed'             => $passed,
	);
} finally {
	// phpcs:disable WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
	$wpdb->query(
		$wpdb->prepare(
			"DELETE FROM {$table} WHERE visitor_key LIKE %s",
			$wpdb->esc_like( $prefix ) . '%'
		)
	);
	// phpcs:enable

	if ( false === $original_settings ) {
		delete_option( 'ctc_chat_settings' );
	} else {
		update_option( 'ctc_chat_settings', $original_settings );
	}
}

echo wp_json_encode( $result, JSON_PRETTY_PRINT ) . "\n";
