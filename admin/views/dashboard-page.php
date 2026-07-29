<?php
/**
 * Dashboard page view.
 *
 * @package ClickToChat
 * @since 1.0.2
 */

if ( ! defined( 'WPINC' ) ) {
	die;
}

$analytics_enabled  = ctc_chat_analytics_is_enabled();
$analytics_has_data = false;

if ( $analytics_enabled ) {
	$analytics          = new CTC_Chat_Analytics();
	$analytics_has_data = $analytics->has_click_data();
}

$default_end        = wp_date( 'Y-m-d' );
$default_start      = wp_date( 'Y-m-d', strtotime( '-29 days' ) );
$dashboard_settings = get_option( 'ctc_chat_settings', array() );
$dashboard_numbers  = ! empty( $dashboard_settings['whatsapp_numbers'] )
	? ctc_chat_normalize_number_record_ids( $dashboard_settings['whatsapp_numbers'] )
	: array();
?>

<div class="ctc-analytics" data-ctc-analytics="dashboard" data-enabled="<?php echo $analytics_enabled ? '1' : '0'; ?>">
	<?php if ( ! $analytics_enabled || ! $analytics_has_data ) : ?>
		<div class="ctc-dashboard-notices">
			<?php
			if ( ! $analytics_enabled ) {
				$this->render_admin_banner(
					sprintf(
						/* translators: %s: settings URL */
						__( 'WhatsApp click tracking is disabled. Enable it in %s to collect dashboard data.', 'aicoso-click-to-chat' ),
						'<a href="' . esc_url( admin_url( 'admin.php?page=click-to-chat-settings' ) ) . '">' . esc_html__( 'Settings → General', 'aicoso-click-to-chat' ) . '</a>'
					),
					'warning'
				);
			} elseif ( ! $analytics_has_data ) {
				$this->render_admin_banner(
					__( 'Analytics tracking has been enabled. Data will start appearing as visitors interact with your WhatsApp buttons. Historical data from before this update is not available.', 'aicoso-click-to-chat' )
				);
			}
			?>
		</div>
	<?php endif; ?>
	<div class="ctc-analytics-toolbar">
		<div class="ctc-analytics-toolbar__presets">
			<button type="button" class="button" data-range-preset="7" aria-pressed="false"><?php esc_html_e( 'Last 7 days', 'aicoso-click-to-chat' ); ?></button>
			<button type="button" class="button button-primary" data-range-preset="30" aria-pressed="true"><?php esc_html_e( 'Last 30 days', 'aicoso-click-to-chat' ); ?></button>
			<button type="button" class="button" data-range-preset="90" aria-pressed="false"><?php esc_html_e( 'Last 90 days', 'aicoso-click-to-chat' ); ?></button>
		</div>
		<div class="ctc-analytics-toolbar__custom">
			<label class="ctc-analytics-field ctc-analytics-field--number">
				<span class="ctc-analytics-field__label"><?php esc_html_e( 'WhatsApp number', 'aicoso-click-to-chat' ); ?></span>
				<select id="ctc-analytics-number">
					<option value="all" selected><?php esc_html_e( 'All numbers', 'aicoso-click-to-chat' ); ?></option>
					<?php foreach ( $dashboard_numbers as $dashboard_number ) : ?>
						<?php
						$number_id   = absint( $dashboard_number['id'] ?? 0 );
						$number_name = ! empty( $dashboard_number['name'] ) ? $dashboard_number['name'] : __( 'WhatsApp', 'aicoso-click-to-chat' );
						$number_mask = ctc_chat_mask_phone_number( $dashboard_number['number'] ?? '' );
						?>
						<?php if ( $number_id ) : ?>
							<option value="<?php echo esc_attr( (string) $number_id ); ?>"><?php echo esc_html( $number_name . ' (' . $number_mask . ')' ); ?></option>
						<?php endif; ?>
					<?php endforeach; ?>
					<option value="unattributed"><?php esc_html_e( 'Unattributed', 'aicoso-click-to-chat' ); ?></option>
				</select>
			</label>
			<label class="ctc-analytics-field">
				<span class="ctc-analytics-field__label"><?php esc_html_e( 'From', 'aicoso-click-to-chat' ); ?></span>
				<input type="date" id="ctc-analytics-start" value="<?php echo esc_attr( $default_start ); ?>">
			</label>
			<label class="ctc-analytics-field">
				<span class="ctc-analytics-field__label"><?php esc_html_e( 'To', 'aicoso-click-to-chat' ); ?></span>
				<input type="date" id="ctc-analytics-end" value="<?php echo esc_attr( $default_end ); ?>">
			</label>
			<label class="ctc-analytics-toolbar__compare ctc-analytics-field ctc-analytics-field--checkbox">
				<input type="checkbox" id="ctc-analytics-compare" checked>
				<span class="ctc-analytics-field__label"><?php esc_html_e( 'Compare to prior period', 'aicoso-click-to-chat' ); ?></span>
			</label>
			<div class="ctc-analytics-toolbar__actions">
				<button type="button" class="button button-secondary" id="ctc-analytics-apply"><?php esc_html_e( 'Apply', 'aicoso-click-to-chat' ); ?></button>
			</div>
		</div>
	</div>
	<div id="ctc-analytics-filter-notice" class="ctc-analytics-filter-notice" aria-live="polite" aria-atomic="true" hidden></div>

	<div class="ctc-analytics-kpis" id="ctc-analytics-kpis" aria-live="polite">
		<div class="ctc-analytics-loading"><?php esc_html_e( 'Loading metrics…', 'aicoso-click-to-chat' ); ?></div>
	</div>

	<div class="ctc-analytics-grid">
		<section class="ctc-analytics-card" id="ctc-analytics-trend" aria-live="polite">
			<header class="ctc-analytics-card__header">
				<h2><?php esc_html_e( 'Click trend', 'aicoso-click-to-chat' ); ?></h2>
			</header>
			<div class="ctc-analytics-card__body ctc-analytics-loading"><?php esc_html_e( 'Loading…', 'aicoso-click-to-chat' ); ?></div>
		</section>

		<section class="ctc-analytics-card" id="ctc-analytics-funnel" aria-live="polite">
			<header class="ctc-analytics-card__header">
				<h2><?php esc_html_e( 'WhatsApp intent funnel (clicks)', 'aicoso-click-to-chat' ); ?></h2>
			</header>
			<div class="ctc-analytics-card__body ctc-analytics-loading"><?php esc_html_e( 'Loading…', 'aicoso-click-to-chat' ); ?></div>
		</section>
	</div>

	<div class="ctc-analytics-grid ctc-analytics-grid--half">
		<section class="ctc-analytics-card" id="ctc-analytics-top-products" aria-live="polite">
			<header class="ctc-analytics-card__header">
				<h2><?php esc_html_e( 'Top products', 'aicoso-click-to-chat' ); ?></h2>
			</header>
			<div class="ctc-analytics-card__body ctc-analytics-loading"><?php esc_html_e( 'Loading…', 'aicoso-click-to-chat' ); ?></div>
		</section>

		<section class="ctc-analytics-card" id="ctc-analytics-top-numbers" aria-live="polite">
			<header class="ctc-analytics-card__header">
				<h2><?php esc_html_e( 'Top WhatsApp numbers', 'aicoso-click-to-chat' ); ?></h2>
			</header>
			<div class="ctc-analytics-card__body ctc-analytics-loading"><?php esc_html_e( 'Loading…', 'aicoso-click-to-chat' ); ?></div>
		</section>
	</div>

	<section class="ctc-analytics-card ctc-analytics-quick-actions">
		<header class="ctc-analytics-card__header">
			<h2><?php esc_html_e( 'Quick actions', 'aicoso-click-to-chat' ); ?></h2>
		</header>
		<div class="ctc-analytics-card__body">
			<a class="button button-secondary" href="<?php echo esc_url( admin_url( 'admin.php?page=click-to-chat-reports' ) ); ?>"><?php esc_html_e( 'View click log', 'aicoso-click-to-chat' ); ?></a>
			<a class="button button-secondary" href="<?php echo esc_url( $this->get_settings_hub_url( 'numbers' ) ); ?>"><?php esc_html_e( 'Manage numbers', 'aicoso-click-to-chat' ); ?></a>
			<a class="button button-secondary" href="<?php echo esc_url( $this->get_settings_hub_url( 'general', 'button' ) ); ?>"><?php esc_html_e( 'Button settings', 'aicoso-click-to-chat' ); ?></a>
		</div>
	</section>
</div>
