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

$analytics_enabled = ctc_chat_analytics_is_enabled();
$default_end       = wp_date( 'Y-m-d' );
$default_start     = wp_date( 'Y-m-d', strtotime( '-29 days' ) );
?>

<div class="ctc-analytics" data-ctc-analytics="dashboard" data-enabled="<?php echo $analytics_enabled ? '1' : '0'; ?>">
	<?php if ( ! $analytics_enabled ) : ?>
		<div class="notice notice-warning">
			<p>
				<?php
				printf(
					/* translators: %s: settings URL */
					esc_html__( 'WhatsApp click tracking is disabled. Enable it in %s to collect dashboard data.', 'aicoso-click-to-chat' ),
					'<a href="' . esc_url( admin_url( 'admin.php?page=click-to-chat-settings' ) ) . '">' . esc_html__( 'Settings → General', 'aicoso-click-to-chat' ) . '</a>'
				);
				?>
			</p>
		</div>
	<?php endif; ?>

	<div class="ctc-analytics-toolbar">
		<div class="ctc-analytics-toolbar__presets">
			<button type="button" class="button" data-range-preset="7"><?php esc_html_e( 'Last 7 days', 'aicoso-click-to-chat' ); ?></button>
			<button type="button" class="button button-primary" data-range-preset="30"><?php esc_html_e( 'Last 30 days', 'aicoso-click-to-chat' ); ?></button>
			<button type="button" class="button" data-range-preset="90"><?php esc_html_e( 'Last 90 days', 'aicoso-click-to-chat' ); ?></button>
		</div>
		<div class="ctc-analytics-toolbar__custom">
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
			<a class="button button-secondary" href="<?php echo esc_url( admin_url( 'admin.php?page=click-to-chat-settings&section=numbers' ) ); ?>"><?php esc_html_e( 'Manage numbers', 'aicoso-click-to-chat' ); ?></a>
			<a class="button button-secondary" href="<?php echo esc_url( admin_url( 'admin.php?page=click-to-chat-settings' ) ); ?>"><?php esc_html_e( 'Button settings', 'aicoso-click-to-chat' ); ?></a>
		</div>
	</section>
</div>
