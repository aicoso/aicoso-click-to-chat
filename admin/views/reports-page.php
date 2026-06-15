<?php
/**
 * Reports page view.
 *
 * @package ClickToChat
 * @since 1.0.2
 */

if ( ! defined( 'WPINC' ) ) {
	die;
}

$current_report = $this->get_current_report();
$default_end    = isset( $_GET['end_date'] ) ? sanitize_text_field( wp_unslash( $_GET['end_date'] ) ) : wp_date( 'Y-m-d' );
$default_start  = isset( $_GET['start_date'] ) ? sanitize_text_field( wp_unslash( $_GET['start_date'] ) ) : wp_date( 'Y-m-d', strtotime( '-29 days' ) );
$product_id     = isset( $_GET['product_id'] ) ? absint( $_GET['product_id'] ) : 0;
$number_id      = isset( $_GET['number_id'] ) ? absint( $_GET['number_id'] ) : 0;
?>

<div class="ctc-analytics" data-ctc-analytics="reports" data-report="<?php echo esc_attr( $current_report ); ?>" data-product-id="<?php echo esc_attr( (string) $product_id ); ?>" data-number-id="<?php echo esc_attr( (string) $number_id ); ?>">
	<div class="ctc-analytics-toolbar ctc-analytics-toolbar--reports">
		<div class="ctc-analytics-toolbar__filters">
			<label class="ctc-analytics-field">
				<span class="ctc-analytics-field__label"><?php esc_html_e( 'From', 'aicoso-click-to-chat' ); ?></span>
				<input type="date" id="ctc-reports-start" value="<?php echo esc_attr( $default_start ); ?>">
			</label>
			<label class="ctc-analytics-field">
				<span class="ctc-analytics-field__label"><?php esc_html_e( 'To', 'aicoso-click-to-chat' ); ?></span>
				<input type="date" id="ctc-reports-end" value="<?php echo esc_attr( $default_end ); ?>">
			</label>
			<?php if ( 'clicks' === $current_report ) : ?>
				<label class="ctc-analytics-field ctc-analytics-field--search">
					<span class="ctc-analytics-field__label"><?php esc_html_e( 'Search', 'aicoso-click-to-chat' ); ?></span>
					<input type="search" id="ctc-reports-search" placeholder="<?php esc_attr_e( 'Product, order, URL…', 'aicoso-click-to-chat' ); ?>">
				</label>
			<?php endif; ?>
		</div>
		<div class="ctc-analytics-toolbar__actions">
			<button type="button" class="button button-primary" id="ctc-reports-apply"><?php esc_html_e( 'Apply filters', 'aicoso-click-to-chat' ); ?></button>
			<?php if ( 'clicks' === $current_report ) : ?>
				<button type="button" class="button button-secondary" id="ctc-reports-export"><?php esc_html_e( 'Export CSV', 'aicoso-click-to-chat' ); ?></button>
			<?php endif; ?>
		</div>
	</div>

	<div id="ctc-reports-table" class="ctc-analytics-card" aria-live="polite">
		<div class="ctc-analytics-card__body ctc-analytics-loading"><?php esc_html_e( 'Loading report…', 'aicoso-click-to-chat' ); ?></div>
	</div>
</div>
