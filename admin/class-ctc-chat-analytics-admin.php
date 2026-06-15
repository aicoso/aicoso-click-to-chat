<?php
/**
 * Admin analytics AJAX handlers.
 *
 * @package ClickToChat
 * @since 1.0.2
 */

if ( ! defined( 'ABSPATH' ) ) {
	die;
}

/**
 * Analytics admin endpoints.
 */
class CTC_Chat_Analytics_Admin {

	/**
	 * Analytics service.
	 *
	 * @var CTC_Chat_Analytics
	 */
	private $analytics;

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->analytics = new CTC_Chat_Analytics();

		add_action( 'wp_ajax_ctc_analytics_kpis', array( $this, 'ajax_kpis' ) );
		add_action( 'wp_ajax_ctc_analytics_trend', array( $this, 'ajax_trend' ) );
		add_action( 'wp_ajax_ctc_analytics_funnel', array( $this, 'ajax_funnel' ) );
		add_action( 'wp_ajax_ctc_analytics_top_products', array( $this, 'ajax_top_products' ) );
		add_action( 'wp_ajax_ctc_analytics_top_numbers', array( $this, 'ajax_top_numbers' ) );
		add_action( 'wp_ajax_ctc_analytics_report_clicks', array( $this, 'ajax_report_clicks' ) );
		add_action( 'wp_ajax_ctc_analytics_report_aggregate', array( $this, 'ajax_report_aggregate' ) );
		add_action( 'wp_ajax_ctc_analytics_export_csv', array( $this, 'ajax_export_csv' ) );
	}

	/**
	 * Verify admin analytics request.
	 *
	 * @return array{start_date:string,end_date:string,compare:bool}
	 */
	private function verify_request() {
		if ( ! ctc_chat_analytics_capability() ) {
			wp_send_json_error( array( 'message' => __( 'Permission denied.', 'aicoso-click-to-chat' ) ), 403 );
		}

		check_ajax_referer( 'ctc_chat_admin_nonce', 'nonce' );

		$start_date = isset( $_POST['start_date'] ) ? sanitize_text_field( wp_unslash( $_POST['start_date'] ) ) : '';
		$end_date   = isset( $_POST['end_date'] ) ? sanitize_text_field( wp_unslash( $_POST['end_date'] ) ) : '';

		if ( ! $start_date || ! $end_date ) {
			$end_date   = wp_date( 'Y-m-d' );
			$start_date = wp_date( 'Y-m-d', strtotime( '-29 days' ) );
		}

		if ( ! preg_match( '/^\d{4}-\d{2}-\d{2}$/', $start_date ) || ! preg_match( '/^\d{4}-\d{2}-\d{2}$/', $end_date ) ) {
			wp_send_json_error( array( 'message' => __( 'Invalid date range.', 'aicoso-click-to-chat' ) ), 400 );
		}

		if ( strtotime( $end_date ) < strtotime( $start_date ) ) {
			wp_send_json_error( array( 'message' => __( 'End date must be after start date.', 'aicoso-click-to-chat' ) ), 400 );
		}

		$days = (int) floor( ( strtotime( $end_date ) - strtotime( $start_date ) ) / DAY_IN_SECONDS ) + 1;
		if ( $days > 366 ) {
			wp_send_json_error( array( 'message' => __( 'Date range cannot exceed 366 days.', 'aicoso-click-to-chat' ) ), 400 );
		}

		$compare = ! isset( $_POST['compare'] ) || filter_var( wp_unslash( $_POST['compare'] ), FILTER_VALIDATE_BOOLEAN );

		return compact( 'start_date', 'end_date', 'compare' );
	}

	/**
	 * KPI endpoint.
	 */
	public function ajax_kpis() {
		$request = $this->verify_request();
		$data    = $this->analytics->get_kpis( $request['start_date'], $request['end_date'], $request['compare'] );
		wp_send_json_success( $data );
	}

	/**
	 * Trend endpoint.
	 */
	public function ajax_trend() {
		$request  = $this->verify_request();
		$grouping = isset( $_POST['grouping'] ) ? sanitize_key( wp_unslash( $_POST['grouping'] ) ) : 'day';
		if ( ! in_array( $grouping, array( 'day', 'week', 'month' ), true ) ) {
			$grouping = 'day';
		}
		$data = $this->analytics->get_trend( $request['start_date'], $request['end_date'], $grouping );
		wp_send_json_success( $data );
	}

	/**
	 * Funnel endpoint.
	 */
	public function ajax_funnel() {
		$request = $this->verify_request();
		$data    = $this->analytics->get_funnel( $request['start_date'], $request['end_date'] );
		wp_send_json_success( $data );
	}

	/**
	 * Top products endpoint.
	 */
	public function ajax_top_products() {
		$request = $this->verify_request();
		$data    = $this->analytics->get_top_products( $request['start_date'], $request['end_date'] );
		wp_send_json_success( $data );
	}

	/**
	 * Top numbers endpoint.
	 */
	public function ajax_top_numbers() {
		$request = $this->verify_request();
		$data    = $this->analytics->get_top_numbers( $request['start_date'], $request['end_date'] );
		wp_send_json_success( $data );
	}

	/**
	 * Click log endpoint.
	 */
	public function ajax_report_clicks() {
		$request = $this->verify_request();
		$page    = isset( $_POST['page'] ) ? absint( $_POST['page'] ) : 1;
		$per_page = isset( $_POST['per_page'] ) ? absint( $_POST['per_page'] ) : 20;
		$filters = $this->parse_filters();

		$data = $this->analytics->get_click_log( $request['start_date'], $request['end_date'], $filters, $page, $per_page );
		wp_send_json_success( $data );
	}

	/**
	 * Aggregate report endpoint.
	 */
	public function ajax_report_aggregate() {
		$request = $this->verify_request();
		$report  = isset( $_POST['report'] ) ? sanitize_key( wp_unslash( $_POST['report'] ) ) : 'placements';
		$page    = isset( $_POST['page'] ) ? absint( $_POST['page'] ) : 1;
		$per_page = isset( $_POST['per_page'] ) ? absint( $_POST['per_page'] ) : 20;

		$data = $this->analytics->get_aggregate_report( $report, $request['start_date'], $request['end_date'], $page, $per_page );
		wp_send_json_success( $data );
	}

	/**
	 * CSV export endpoint.
	 */
	public function ajax_export_csv() {
		if ( ! ctc_chat_analytics_capability() ) {
			wp_die( esc_html__( 'Permission denied.', 'aicoso-click-to-chat' ), 403 );
		}

		check_ajax_referer( 'ctc_chat_admin_nonce', 'nonce' );

		$start_date = isset( $_REQUEST['start_date'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['start_date'] ) ) : wp_date( 'Y-m-d', strtotime( '-29 days' ) );
		$end_date   = isset( $_REQUEST['end_date'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['end_date'] ) ) : wp_date( 'Y-m-d' );
		$filters    = $this->parse_filters_from_request();

		$rows = $this->analytics->get_export_rows( $start_date, $end_date, $filters );

		$filename = 'ctc-click-log-' . $start_date . '-to-' . $end_date . '.csv';

		nocache_headers();
		header( 'Content-Type: text/csv; charset=utf-8' );
		header( 'Content-Disposition: attachment; filename=' . $filename );

		$output = fopen( 'php://output', 'w' );
		fputcsv(
			$output,
			array(
				__( 'Date', 'aicoso-click-to-chat' ),
				__( 'Placement', 'aicoso-click-to-chat' ),
				__( 'Product', 'aicoso-click-to-chat' ),
				__( 'Order', 'aicoso-click-to-chat' ),
				__( 'Cart total', 'aicoso-click-to-chat' ),
				__( 'Number', 'aicoso-click-to-chat' ),
				__( 'Device', 'aicoso-click-to-chat' ),
				__( 'Page URL', 'aicoso-click-to-chat' ),
			)
		);

		foreach ( $rows as $row ) {
			fputcsv(
				$output,
				array(
					$row['clicked_at'],
					$row['button_label'],
					$row['product_name'],
					$row['order_number'],
					$row['cart_total_formatted'],
					$row['number_label'],
					$row['device_type'],
					$row['page_url'],
				)
			);
		}

		fclose( $output );
		exit;
	}

	/**
	 * Parse filters from POST.
	 *
	 * @return array
	 */
	private function parse_filters() {
		return $this->parse_filters_from_request();
	}

	/**
	 * Parse filters from request superglobal.
	 *
	 * @return array
	 */
	private function parse_filters_from_request() {
		$filters = array();

		if ( ! empty( $_REQUEST['button_type'] ) ) {
			$types = (array) wp_unslash( $_REQUEST['button_type'] );
			$filters['button_types'] = array_map( 'sanitize_key', $types );
		}

		if ( ! empty( $_REQUEST['number_id'] ) ) {
			$filters['number_id'] = absint( $_REQUEST['number_id'] );
		}

		if ( ! empty( $_REQUEST['product_id'] ) ) {
			$filters['product_id'] = absint( $_REQUEST['product_id'] );
		}

		if ( ! empty( $_REQUEST['device_type'] ) ) {
			$filters['device_type'] = sanitize_key( wp_unslash( $_REQUEST['device_type'] ) );
		}

		if ( ! empty( $_REQUEST['search'] ) ) {
			$filters['search'] = sanitize_text_field( wp_unslash( $_REQUEST['search'] ) );
		}

		return $filters;
	}
}
