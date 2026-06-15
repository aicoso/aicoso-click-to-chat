<?php
/**
 * Unified settings hub view.
 *
 * @package ClickToChat
 * @since 1.0.0
 */

if ( ! defined( 'WPINC' ) ) {
	die;
}

$current_section = $this->get_current_settings_section();
$current_tab     = $this->get_current_settings_tab();
?>

<div class="ctc-settings-hub ctc-settings-workspace">
	<?php if ( 'general' === $current_section ) : ?>
		<div class="ctc-settings-split">
			<nav class="ctc-settings-split__nav" aria-label="<?php esc_attr_e( 'Configuration sections', 'aicoso-click-to-chat' ); ?>">
				<?php $this->render_settings_split_nav( $current_tab ); ?>
			</nav>
			<div class="ctc-settings-split__content">
				<?php include CTC_CHAT_PLUGIN_DIR . 'admin/views/settings-page.php'; ?>
			</div>
		</div>
	<?php else : ?>
		<div class="ctc-settings-workspace__content">
			<?php
			if ( 'numbers' === $current_section ) {
				include CTC_CHAT_PLUGIN_DIR . 'admin/views/numbers-page.php';
			} elseif ( 'templates' === $current_section ) {
				include CTC_CHAT_PLUGIN_DIR . 'admin/views/templates-page.php';
			} elseif ( 'shortcodes' === $current_section ) {
				include CTC_CHAT_PLUGIN_DIR . 'admin/views/shortcode-page.php';
			}
			?>
		</div>
	<?php endif; ?>
</div>
