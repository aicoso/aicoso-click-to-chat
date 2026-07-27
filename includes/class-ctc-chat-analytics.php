<?php
/**
 * Analytics query layer.
 *
 * @package ClickToChat
 * @since 1.0.2
 */

if ( ! defined( 'ABSPATH' ) ) {
	die;
}

/**
 * Analytics queries.
 */
class CTC_Chat_Analytics {

	/**
	 * Determine whether any analytics clicks have been recorded.
	 *
	 * @return bool
	 */
	public function has_click_data() {
		global $wpdb;

		$table = ctc_chat_get_clicks_table_name();

		// phpcs:disable WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$has_data = $wpdb->get_var( "SELECT 1 FROM {$table} LIMIT 1" );
		// phpcs:enable

		return null !== $has_data;
	}

	/**
	 * Get KPI metrics for a range and comparison range.
	 *
	 * @param string $start_date Start date Y-m-d.
	 * @param string $end_date   End date Y-m-d.
	 * @param bool   $compare    Include comparison period.
	 * @return array
	 */
	public function get_kpis( $start_date, $end_date, $compare = true ) {
		$range         = ctc_chat_analytics_parse_range( $start_date, $end_date );
		$current       = $this->get_kpi_values( $range['start'], $range['end'] );
		$compare_range = ctc_chat_analytics_compare_range( $start_date, $end_date );
		$previous      = $compare ? $this->get_kpi_values(
			ctc_chat_analytics_parse_range( $compare_range['start'], $compare_range['end'] )['start'],
			ctc_chat_analytics_parse_range( $compare_range['start'], $compare_range['end'] )['end']
		) : array();

		$currency = function_exists( 'get_woocommerce_currency' ) ? get_woocommerce_currency() : '';

		$build = function ( $key, $format = 'number', $extra = array() ) use ( $current, $previous, $compare ) {
			if ( ! $compare ) {
				return array_merge(
					array(
						'value'         => $current[ $key ],
						'compare_value' => null,
						'delta_pct'     => null,
						'delta_label'   => '',
						'format'        => $format,
					),
					$extra
				);
			}

			$delta = ctc_chat_analytics_format_delta( $current[ $key ], $previous[ $key ] );
			$data  = array(
				'value'         => $current[ $key ],
				'compare_value' => $previous[ $key ],
				'delta_pct'     => $delta['delta_pct'],
				'delta_label'   => $delta['label'],
				'format'        => $format,
			);

			return array_merge( $data, $extra );
		};

		$top_placement = $current['top_placement_type'];
		$top_count     = $current['top_placement_count'];

		return array(
			'comparison_enabled' => (bool) $compare,
			'range'              => array(
				'start'         => $start_date,
				'end'           => $end_date,
				'compare_start' => $compare_range['start'],
				'compare_end'   => $compare_range['end'],
				'timezone'      => $range['timezone'],
			),
			'total_clicks'       => $build( 'total_clicks' ),
			'unique_clicks'      => $build( 'unique_clicks' ),
			'high_intent_clicks' => $build( 'high_intent_clicks' ),
			'cart_value_clicked' => $build( 'cart_value_clicked', 'currency', array( 'currency' => $currency ) ),
			'mobile_share'       => $build( 'mobile_share', 'percent' ),
			'top_placement'      => array(
				'value'         => $top_placement,
				'label'         => $top_placement ? ctc_chat_get_button_type_label( $top_placement ) : '',
				'count'         => $top_count,
				'compare_value' => $compare ? $previous['top_placement_type'] : null,
				'compare_label' => $compare && $previous['top_placement_type'] ? ctc_chat_get_button_type_label( $previous['top_placement_type'] ) : '',
				'compare_count' => $compare ? $previous['top_placement_count'] : null,
			),
		);
	}

	/**
	 * Raw KPI values for datetime range (UTC storage).
	 *
	 * @param string $start_utc Start datetime UTC.
	 * @param string $end_utc   End datetime UTC.
	 * @return array
	 */
	private function get_kpi_values( $start_utc, $end_utc ) {
		global $wpdb;

		$table = ctc_chat_get_clicks_table_name();

		// phpcs:disable WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$totals = $wpdb->get_row(
			$wpdb->prepare(
				"SELECT
					COUNT(*) AS total_clicks,
					SUM(CASE WHEN is_unique = 1 THEN 1 ELSE 0 END) AS unique_clicks,
					SUM(CASE WHEN button_type IN ('cart','checkout','thankyou') THEN 1 ELSE 0 END) AS high_intent_clicks,
					COALESCE(SUM(CASE WHEN cart_total > 0 THEN cart_total ELSE 0 END), 0) AS cart_value_clicked,
					SUM(CASE WHEN device_type = 'mobile' THEN 1 ELSE 0 END) AS mobile_clicks
				FROM {$table}
				WHERE clicked_at BETWEEN %s AND %s",
				$start_utc,
				$end_utc
			),
			ARRAY_A
		);

		$total_clicks = isset( $totals['total_clicks'] ) ? (int) $totals['total_clicks'] : 0;
		$mobile       = isset( $totals['mobile_clicks'] ) ? (int) $totals['mobile_clicks'] : 0;

		$top = $wpdb->get_row(
			$wpdb->prepare(
				"SELECT button_type, COUNT(*) AS click_count
				FROM {$table}
				WHERE clicked_at BETWEEN %s AND %s
				GROUP BY button_type
				ORDER BY click_count DESC
				LIMIT 1",
				$start_utc,
				$end_utc
			),
			ARRAY_A
		);

		return array(
			'total_clicks'         => $total_clicks,
			'unique_clicks'        => isset( $totals['unique_clicks'] ) ? (int) $totals['unique_clicks'] : 0,
			'high_intent_clicks'   => isset( $totals['high_intent_clicks'] ) ? (int) $totals['high_intent_clicks'] : 0,
			'cart_value_clicked'   => isset( $totals['cart_value_clicked'] ) ? (float) $totals['cart_value_clicked'] : 0,
			'mobile_share'         => $total_clicks > 0 ? round( ( $mobile / $total_clicks ) * 100, 1 ) : 0,
			'top_placement_type'   => $top['button_type'] ?? '',
			'top_placement_count'  => isset( $top['click_count'] ) ? (int) $top['click_count'] : 0,
		);
	}

	/**
	 * Trend series data.
	 *
	 * @param string $start_date Start date.
	 * @param string $end_date   End date.
	 * @param string $grouping   day|week|month.
	 * @return array
	 */
	public function get_trend( $start_date, $end_date, $grouping = 'day' ) {
		global $wpdb;

		$range  = ctc_chat_analytics_parse_range( $start_date, $end_date );
		$table  = ctc_chat_get_clicks_table_name();
		$tz     = wp_timezone();

		$rows = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT clicked_at, is_unique FROM {$table}
				WHERE clicked_at BETWEEN %s AND %s
				ORDER BY clicked_at ASC",
				$range['start'],
				$range['end']
			),
			ARRAY_A
		);

		$buckets = array();

		foreach ( (array) $rows as $row ) {
			try {
				$dt = new DateTimeImmutable( $row['clicked_at'], new DateTimeZone( 'UTC' ) );
				$dt = $dt->setTimezone( $tz );
			} catch ( Exception $e ) {
				continue;
			}

			if ( 'month' === $grouping ) {
				$key = $dt->format( 'Y-m' );
			} elseif ( 'week' === $grouping ) {
				$key = $dt->format( 'o-\WW' );
			} else {
				$key = $dt->format( 'Y-m-d' );
			}

			if ( ! isset( $buckets[ $key ] ) ) {
				$buckets[ $key ] = array(
					'date'          => $key,
					'clicks'        => 0,
					'unique_clicks' => 0,
				);
			}

			$buckets[ $key ]['clicks']++;
			if ( ! empty( $row['is_unique'] ) ) {
				$buckets[ $key ]['unique_clicks']++;
			}
		}

		return array(
			'range'    => array(
				'start'    => $start_date,
				'end'      => $end_date,
				'timezone' => $range['timezone'],
			),
			'grouping' => $grouping,
			'series'   => array_values( $buckets ),
		);
	}

	/**
	 * Funnel counts.
	 *
	 * @param string $start_date Start date.
	 * @param string $end_date   End date.
	 * @return array
	 */
	public function get_funnel( $start_date, $end_date ) {
		global $wpdb;

		$range = ctc_chat_analytics_parse_range( $start_date, $end_date );
		$table = ctc_chat_get_clicks_table_name();

		$rows = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT button_type, COUNT(*) AS click_count
				FROM {$table}
				WHERE clicked_at BETWEEN %s AND %s
				GROUP BY button_type",
				$range['start'],
				$range['end']
			),
			ARRAY_A
		);

		$counts = array();
		foreach ( (array) $rows as $row ) {
			$counts[ $row['button_type'] ] = (int) $row['click_count'];
		}

		$browsing = ( $counts['product'] ?? 0 ) + ( $counts['shop'] ?? 0 ) + ( $counts['floating'] ?? 0 ) + ( $counts['shortcode'] ?? 0 );

		return array(
			'stages' => array(
				array(
					'key'   => 'browsing',
					'label' => __( 'Product / Shop', 'aicoso-click-to-chat' ),
					'count' => $browsing,
				),
				array(
					'key'   => 'cart',
					'label' => __( 'Cart', 'aicoso-click-to-chat' ),
					'count' => $counts['cart'] ?? 0,
				),
				array(
					'key'   => 'checkout',
					'label' => __( 'Checkout', 'aicoso-click-to-chat' ),
					'count' => $counts['checkout'] ?? 0,
				),
				array(
					'key'   => 'thankyou',
					'label' => __( 'Thank you', 'aicoso-click-to-chat' ),
					'count' => $counts['thankyou'] ?? 0,
				),
			),
		);
	}

	/**
	 * Top products by clicks.
	 *
	 * @param string $start_date Start date.
	 * @param string $end_date   End date.
	 * @param int    $limit      Limit.
	 * @return array
	 */
	public function get_top_products( $start_date, $end_date, $limit = 5 ) {
		global $wpdb;

		$range = ctc_chat_analytics_parse_range( $start_date, $end_date );
		$table = ctc_chat_get_clicks_table_name();
		$limit = max( 1, min( 10, absint( $limit ) ) );

		$rows = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT product_id,
					COUNT(*) AS clicks,
					SUM(CASE WHEN is_unique = 1 THEN 1 ELSE 0 END) AS unique_clicks
				FROM {$table}
				WHERE clicked_at BETWEEN %s AND %s
				AND product_id IS NOT NULL AND product_id > 0
				GROUP BY product_id
				ORDER BY clicks DESC
				LIMIT %d",
				$range['start'],
				$range['end'],
				$limit
			),
			ARRAY_A
		);

		$items = array();

		foreach ( (array) $rows as $row ) {
			$product_id = (int) $row['product_id'];
			$title      = get_the_title( $product_id );
			$items[]    = array(
				'product_id'     => $product_id,
				'name'           => $title ? $title : __( '(deleted product)', 'aicoso-click-to-chat' ),
				'clicks'         => (int) $row['clicks'],
				'unique_clicks'  => (int) $row['unique_clicks'],
				'report_url'     => add_query_arg(
					array(
						'page'       => 'click-to-chat-reports',
						'report'     => 'clicks',
						'product_id' => $product_id,
						'start_date' => $start_date,
						'end_date'   => $end_date,
					),
					admin_url( 'admin.php' )
				),
			);
		}

		return array( 'items' => $items );
	}

	/**
	 * Top WhatsApp numbers by clicks.
	 *
	 * @param string $start_date Start date.
	 * @param string $end_date   End date.
	 * @param int    $limit      Limit.
	 * @return array
	 */
	public function get_top_numbers( $start_date, $end_date, $limit = 5 ) {
		global $wpdb;

		$range = ctc_chat_analytics_parse_range( $start_date, $end_date );
		$table = ctc_chat_get_clicks_table_name();
		$limit = max( 1, min( 10, absint( $limit ) ) );

		$rows = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT COALESCE(number_id, 0) AS number_id,
					COUNT(*) AS clicks,
					SUM(CASE WHEN button_type IN ('cart','checkout','thankyou') THEN 1 ELSE 0 END) AS high_intent_clicks
				FROM {$table}
				WHERE clicked_at BETWEEN %s AND %s
				GROUP BY COALESCE(number_id, 0)
				ORDER BY clicks DESC
				LIMIT %d",
				$range['start'],
				$range['end'],
				$limit
			),
			ARRAY_A
		);

		$items = array();

		foreach ( (array) $rows as $row ) {
			$number_id = (int) $row['number_id'];
			$items[]   = array(
				'number_id'           => $number_id,
				'label'               => $number_id ? ctc_chat_get_number_label( $number_id ) : __( 'Unattributed', 'aicoso-click-to-chat' ),
				'masked_number'       => $number_id ? ctc_chat_get_number_display( $number_id ) : __( 'Unattributed', 'aicoso-click-to-chat' ),
				'clicks'              => (int) $row['clicks'],
				'high_intent_clicks'  => (int) $row['high_intent_clicks'],
				'report_url'          => add_query_arg(
					array(
						'page'       => 'click-to-chat-reports',
						'report'     => 'clicks',
						'number_id'  => $number_id,
						'start_date' => $start_date,
						'end_date'   => $end_date,
					),
					admin_url( 'admin.php' )
				),
			);
		}

		return array( 'items' => $items );
	}

	/**
	 * Paginated click log.
	 *
	 * @param string $start_date Start date.
	 * @param string $end_date   End date.
	 * @param array  $filters    Filters.
	 * @param int    $page       Page.
	 * @param int    $per_page   Per page.
	 * @return array
	 */
	public function get_click_log( $start_date, $end_date, $filters = array(), $page = 1, $per_page = 20 ) {
		global $wpdb;

		$range    = ctc_chat_analytics_parse_range( $start_date, $end_date );
		$table    = ctc_chat_get_clicks_table_name();
		$page     = max( 1, absint( $page ) );
		$per_page = max( 1, min( 100, absint( $per_page ) ) );
		$offset   = ( $page - 1 ) * $per_page;

		list( $where_sql, $where_args ) = $this->build_filter_sql( $range['start'], $range['end'], $filters );

		$total = (int) $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$table} WHERE {$where_sql}",
				...$where_args
			)
		);

		$query_args   = $where_args;
		$query_args[] = $per_page;
		$query_args[] = $offset;

		$rows = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT * FROM {$table} WHERE {$where_sql} ORDER BY clicked_at DESC LIMIT %d OFFSET %d",
				...$query_args
			),
			ARRAY_A
		);

		return array(
			'rows'     => array_map( array( $this, 'format_click_row' ), (array) $rows ),
			'total'    => $total,
			'page'     => $page,
			'per_page' => $per_page,
		);
	}

	/**
	 * Aggregate report data.
	 *
	 * @param string $report     Report key.
	 * @param string $start_date Start date.
	 * @param string $end_date   End date.
	 * @param int    $page       Page.
	 * @param int    $per_page   Per page.
	 * @return array
	 */
	public function get_aggregate_report( $report, $start_date, $end_date, $page = 1, $per_page = 20 ) {
		global $wpdb;

		$range    = ctc_chat_analytics_parse_range( $start_date, $end_date );
		$table    = ctc_chat_get_clicks_table_name();
		$page     = max( 1, absint( $page ) );
		$per_page = max( 1, min( 100, absint( $per_page ) ) );
		$offset   = ( $page - 1 ) * $per_page;

		switch ( $report ) {
			case 'placements':
				$group_col = 'button_type';
				$where     = '1=1';
				break;
			case 'products':
				$group_col = 'product_id';
				$where     = 'product_id IS NOT NULL AND product_id > 0';
				break;
			case 'numbers':
				$group_col = 'COALESCE(number_id, 0)';
				$where     = '1=1';
				break;
			case 'pages':
				$group_col = 'page_path';
				$where     = "page_path IS NOT NULL AND page_path <> ''";
				break;
			default:
				return array(
					'rows'     => array(),
					'total'    => 0,
					'page'     => 1,
					'per_page' => $per_page,
				);
		}

		$total = (int) $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM (
					SELECT {$group_col}
					FROM {$table}
					WHERE clicked_at BETWEEN %s AND %s AND {$where}
					GROUP BY {$group_col}
				) AS grouped",
				$range['start'],
				$range['end']
			)
		);

		$rows = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT {$group_col} AS group_key,
					COUNT(*) AS clicks,
					SUM(CASE WHEN is_unique = 1 THEN 1 ELSE 0 END) AS unique_clicks,
					SUM(CASE WHEN button_type IN ('cart','checkout','thankyou') THEN 1 ELSE 0 END) AS high_intent
				FROM {$table}
				WHERE clicked_at BETWEEN %s AND %s AND {$where}
				GROUP BY {$group_col}
				ORDER BY clicks DESC
				LIMIT %d OFFSET %d",
				$range['start'],
				$range['end'],
				$per_page,
				$offset
			),
			ARRAY_A
		);

		$grand_total = (int) $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$table} WHERE clicked_at BETWEEN %s AND %s",
				$range['start'],
				$range['end']
			)
		);

		$formatted = array();

		foreach ( (array) $rows as $row ) {
			$clicks = (int) $row['clicks'];
			$item   = array(
				'clicks'         => $clicks,
				'unique_clicks'  => (int) $row['unique_clicks'],
				'high_intent'    => (int) $row['high_intent'],
				'share_pct'      => $grand_total > 0 ? round( ( $clicks / $grand_total ) * 100, 1 ) : 0,
			);

			switch ( $report ) {
				case 'placements':
					$item['placement']      = ctc_chat_get_button_type_label( $row['group_key'] );
					$item['placement_key']  = $row['group_key'];
					break;
				case 'products':
					$pid = (int) $row['group_key'];
					$title = get_the_title( $pid );
					$item['product_id'] = $pid;
					$item['product']    = $title ? $title : __( '(deleted product)', 'aicoso-click-to-chat' );
					break;
				case 'numbers':
					$nid = (int) $row['group_key'];
					$item['number_id'] = $nid;
					$item['number']    = $nid ? ctc_chat_get_number_display( $nid ) : __( 'Unattributed', 'aicoso-click-to-chat' );
					break;
				case 'pages':
					$item['page_path'] = $row['group_key'];
					break;
			}

			$formatted[] = $item;
		}

		return array(
			'rows'     => $formatted,
			'total'    => $total,
			'page'     => $page,
			'per_page' => $per_page,
		);
	}

	/**
	 * Export click log rows for CSV.
	 *
	 * @param string $start_date Start date.
	 * @param string $end_date   End date.
	 * @param array  $filters    Filters.
	 * @param int    $limit      Max rows.
	 * @return array
	 */
	public function get_export_rows( $start_date, $end_date, $filters = array(), $limit = 10000 ) {
		global $wpdb;

		$range = ctc_chat_analytics_parse_range( $start_date, $end_date );
		$table = ctc_chat_get_clicks_table_name();
		$limit = max( 1, min( 10000, absint( $limit ) ) );

		list( $where_sql, $where_args ) = $this->build_filter_sql( $range['start'], $range['end'], $filters );
		$query_args                     = $where_args;
		$query_args[]                   = $limit;

		$rows = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT * FROM {$table} WHERE {$where_sql} ORDER BY clicked_at DESC LIMIT %d",
				...$query_args
			),
			ARRAY_A
		);

		return array_map( array( $this, 'format_click_row' ), (array) $rows );
	}

	/**
	 * Build filter SQL fragment.
	 *
	 * @param string $start_utc Start UTC.
	 * @param string $end_utc   End UTC.
	 * @param array  $filters   Filters.
	 * @return array{0:string,1:array}
	 */
	private function build_filter_sql( $start_utc, $end_utc, $filters ) {
		global $wpdb;

		$clauses = array( 'clicked_at BETWEEN %s AND %s' );
		$args    = array( $start_utc, $end_utc );

		if ( ! empty( $filters['button_types'] ) && is_array( $filters['button_types'] ) ) {
			$types = array_values( array_intersect( $filters['button_types'], ctc_chat_get_allowed_button_types() ) );
			if ( $types ) {
				$placeholders = implode( ',', array_fill( 0, count( $types ), '%s' ) );
				$clauses[]    = "button_type IN ({$placeholders})";
				$args         = array_merge( $args, $types );
			}
		}

		if ( ! empty( $filters['number_id'] ) ) {
			$clauses[] = 'number_id = %d';
			$args[]    = absint( $filters['number_id'] );
		}

		if ( ! empty( $filters['product_id'] ) ) {
			$clauses[] = 'product_id = %d';
			$args[]    = absint( $filters['product_id'] );
		}

		if ( ! empty( $filters['device_type'] ) ) {
			$clauses[] = 'device_type = %s';
			$args[]    = sanitize_key( $filters['device_type'] );
		}

		if ( ! empty( $filters['search'] ) ) {
			$search = sanitize_text_field( $filters['search'] );
			$like   = '%' . $wpdb->esc_like( $search ) . '%';
			$clauses[] = '(page_url LIKE %s OR page_path LIKE %s)';
			$args[] = $like;
			$args[] = $like;

			if ( is_numeric( $search ) ) {
				$clauses[ count( $clauses ) - 1 ] .= ' OR product_id = %d OR order_id = %d';
				$args[] = absint( $search );
				$args[] = absint( $search );
			}
		}

		return array( implode( ' AND ', $clauses ), $args );
	}

	/**
	 * Format a click row for API output.
	 *
	 * @param array $row DB row.
	 * @return array
	 */
	private function format_click_row( $row ) {
		$timezone = wp_timezone();
		$clicked  = '';

		try {
			$dt      = new DateTimeImmutable( $row['clicked_at'], new DateTimeZone( 'UTC' ) );
			$clicked = $dt->setTimezone( $timezone )->format( 'Y-m-d H:i:s' );
		} catch ( Exception $e ) {
			$clicked = $row['clicked_at'];
		}

		$product_name = '';
		if ( ! empty( $row['product_id'] ) ) {
			$product_name = get_the_title( (int) $row['product_id'] );
		}

		$order_number = '';
		if ( ! empty( $row['order_id'] ) && function_exists( 'wc_get_order' ) ) {
			$order = wc_get_order( (int) $row['order_id'] );
			if ( $order ) {
				$order_number = $order->get_order_number();
			}
		}

		$cart_total_formatted = '';
		if ( ! empty( $row['cart_total'] ) && function_exists( 'wc_price' ) ) {
			$cart_total_formatted = wp_strip_all_tags( wc_price( $row['cart_total'], array( 'currency' => $row['cart_currency'] ) ) );
		}

		return array(
			'id'                   => (int) $row['id'],
			'clicked_at'           => $clicked,
			'button_type'          => $row['button_type'],
			'button_label'         => ctc_chat_get_button_type_label( $row['button_type'] ),
			'product_id'           => ! empty( $row['product_id'] ) ? (int) $row['product_id'] : null,
			'product_name'         => $product_name,
			'order_id'             => ! empty( $row['order_id'] ) ? (int) $row['order_id'] : null,
			'order_number'         => $order_number,
			'cart_total_formatted' => $cart_total_formatted,
			'number_label'         => ! empty( $row['number_id'] ) ? ctc_chat_get_number_display( (int) $row['number_id'] ) : '—',
			'device_type'          => ! empty( $row['device_type'] ) ? $row['device_type'] : 'unknown',
			'page_url'             => $row['page_url'],
		);
	}
}
