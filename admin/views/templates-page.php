<?php
/**
 * Message templates page view.
 *
 * @package ClickToChat
 * @since 1.0.0
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

// Include settings class.
require_once plugin_dir_path( __DIR__ ) . 'class-ctc-chat-settings.php';

// Get plugin settings.
$ctc_chat_settings = get_option( 'ctc_chat_settings', array() );

// Get message templates.
$ctc_chat_message_templates = isset( $ctc_chat_settings['ctc_chat_message_templates'] ) ? $ctc_chat_settings['ctc_chat_message_templates'] : array();

// Initialize templates if not set.
if ( empty( $ctc_chat_message_templates ) ) {
	$ctc_chat_message_templates = array(
		'ctc_chat_single_product' => "Hello! I'm interested in the product: *{product_name}*\nPrice: {price}\nURL: {product_url}\n\nDo you have this item in stock? I'd like to get more information.",
		'ctc_chat_cart_checkout'  => "Hello! I'd like to complete my purchase of:\n{cart_items_list}\n---------------------\nSubtotal: {cart_subtotal}\nTax: {tax_amount}\nShipping: {shipping_method} - {shipping_cost}\nTotal: {cart_total}\n\nI have a few questions before finalizing my order.",
		'ctc_chat_thank_you'      => "Hello! I've just placed order #{order_number} on {order_date}.\nMy order includes:\n{ordered_items_list}\n---------------------\nApplied Coupon: {coupon_code}\nTotal: {order_total}\n\nI'd like to confirm when this will be shipped.",
		'ctc_chat_floating'       => 'Hello! I was browsing your website at {current_page_url} and have a question.',
		'ctc_chat_variations'     => "Hello! I'm interested in the product: *{product_name}*\nSelected options: {variation_details}\nPrice: {variation_price}\nURL: {product_url}\n\nIs this combination available for immediate shipping?",
	);
}

// Get settings helper for template placeholders.
$ctc_chat_settings_helper = new CTC_Chat_Settings();
?>

<div class="wrap ctc-chat-admin-container">
	<div class="ctc-chat-admin-header">
		<span class="ctc-chat-admin-logo dashicons dashicons-whatsapp"></span>
		<h1 class="ctc-chat-admin-heading"><?php esc_html_e( 'Message Templates', 'aicoso-click-to-chat' ); ?></h1>
	</div>

	<!-- Instructions Banner -->
	<div class="ctc-chat-instructions-banner">
		<h2><span class="dashicons dashicons-text"></span> <?php esc_html_e( 'Customize Your WhatsApp Messages', 'aicoso-click-to-chat' ); ?></h2>
		<p><?php esc_html_e( 'Configure message templates for different contexts. Use placeholders to automatically include product details, cart information, and order data.', 'aicoso-click-to-chat' ); ?></p>
		<div class="ctc-chat-quick-steps">
			<div class="ctc-chat-step">
				<span class="ctc-chat-step-number">1</span>
				<span><?php esc_html_e( 'Choose a template below', 'aicoso-click-to-chat' ); ?></span>
			</div>
			<div class="ctc-chat-step">
				<span class="ctc-chat-step-number">2</span>
				<span><?php esc_html_e( 'Click placeholders to insert them', 'aicoso-click-to-chat' ); ?></span>
			</div>
			<div class="ctc-chat-step">
				<span class="ctc-chat-step-number">3</span>
				<span><?php esc_html_e( 'Preview and save your changes', 'aicoso-click-to-chat' ); ?></span>
			</div>
		</div>
	</div>

	<?php
	// Check if plugin is disabled or no numbers configured.
	$ctc_chat_plugin_enabled = isset( $ctc_chat_settings['ctc_chat_plugin_enabled'] ) ? $ctc_chat_settings['ctc_chat_plugin_enabled'] : true;

	// Check if there are any numbers with actual phone numbers configured.
	$ctc_chat_has_valid_numbers = false;
	if ( isset( $ctc_chat_settings['ctc_chat_whatsapp_numbers'] ) && ! empty( $ctc_chat_settings['ctc_chat_whatsapp_numbers'] ) ) {
		foreach ( $ctc_chat_settings['ctc_chat_whatsapp_numbers'] as $ctc_chat_number ) {
			if ( ! empty( $ctc_chat_number['ctc_chat_number'] ) ) {
				$ctc_chat_has_valid_numbers = true;
				break;
			}
		}
	}

	if ( ! $ctc_chat_plugin_enabled || ! $ctc_chat_has_valid_numbers ) :
		?>
		<div class="ctc-chat-admin-warning-box">
		<?php if ( ! $ctc_chat_plugin_enabled ) : ?>
		<div class="ctc-chat-warning-item">
			<span class="ctc-chat-warning-icon">⚠️</span>
			<div class="ctc-chat-warning-content">
				<strong><?php esc_html_e( 'Warning:', 'aicoso-click-to-chat' ); ?></strong>
				<?php
				printf(
					/* translators: %s: Link to Settings page */
					esc_html__( 'The plugin is currently disabled. WhatsApp buttons will not appear on your website. %s to activate the plugin.', 'aicoso-click-to-chat' ),
					'<a href="' . esc_url( admin_url( 'admin.php?page=click-to-chat' ) ) . '">' . esc_html__( 'Go to Settings', 'aicoso-click-to-chat' ) . '</a>'
				);
				?>
			</div>
		</div>
		<?php endif; ?>

		<?php if ( ! $ctc_chat_has_valid_numbers ) : ?>
		<div class="ctc-chat-warning-item">
			<span class="ctc-chat-warning-icon">⚠️</span>
			<div class="ctc-chat-warning-content">
				<strong><?php esc_html_e( 'Warning:', 'aicoso-click-to-chat' ); ?></strong>
				<?php
				printf(
					/* translators: %s: Link to Numbers page */
					esc_html__( 'No WhatsApp number configured! You need to add at least one WhatsApp number for the buttons to work. %s', 'aicoso-click-to-chat' ),
					'<a href="' . esc_url( admin_url( 'admin.php?page=click-to-chat-numbers' ) ) . '">' . esc_html__( 'Add a number now →', 'aicoso-click-to-chat' ) . '</a>'
				);
				?>
			</div>
		</div>
		<?php endif; ?>
	</div>
	<?php endif; ?>

	<form method="post" action="">
		<?php wp_nonce_field( 'ctc_chat_templates_nonce', 'ctc_chat_templates_nonce' ); ?>

		<div class="ctc-chat-templates-wrapper">
			<!-- Single Product Template -->
			<div class="ctc-chat-template-card">
				<div class="ctc-chat-template-header">
					<span class="ctc-chat-template-icon dashicons dashicons-products"></span>
					<h3 class="ctc-chat-template-title"><?php esc_html_e( 'Single Product Template', 'aicoso-click-to-chat' ); ?></h3>
				</div>
				<div class="ctc-chat-template-body">
					<p class="ctc-chat-template-description"><?php esc_html_e( 'This template is used for inquiries about a specific product.', 'aicoso-click-to-chat' ); ?></p>

					<div class="ctc-chat-placeholders-section">
						<label class="ctc-chat-field-label"><?php esc_html_e( 'Available Placeholders:', 'aicoso-click-to-chat' ); ?></label>
						<div class="ctc-chat-placeholders-grid">
							<?php
							$ctc_chat_placeholders = $ctc_chat_settings_helper->ctc_chat_get_template_placeholders( 'ctc_chat_single_product' );
							foreach ( $ctc_chat_placeholders as $ctc_chat_placeholder => $ctc_chat_description ) {
								echo '<span class="ctc-chat-placeholder-tag" data-placeholder="' . esc_attr( $ctc_chat_placeholder ) . '" title="' . esc_attr( $ctc_chat_description ) . '">' . esc_html( $ctc_chat_placeholder ) . '</span>';
							}
							?>
						</div>
					</div>

					<div class="ctc-chat-field-group">
						<label class="ctc-chat-field-label"><?php esc_html_e( 'Message Template', 'aicoso-click-to-chat' ); ?></label>
						<textarea name="ctc_chat_templates[ctc_chat_single_product]" class="ctc-chat-template-textarea" rows="8"><?php echo esc_textarea( html_entity_decode( $ctc_chat_message_templates['ctc_chat_single_product'] ?? '', ENT_QUOTES | ENT_HTML5, 'UTF-8' ) ); ?></textarea>
					</div>

					<div class="ctc-chat-template-actions">
						<button type="button" class="ctc-chat-preview-btn" data-template-type="ctc_chat_single_product">
							<span class="dashicons dashicons-visibility"></span>
							<?php esc_html_e( 'Preview', 'aicoso-click-to-chat' ); ?>
						</button>
					</div>

					<div class="ctc-chat-template-preview ctc-chat-hidden"></div>
				</div>
			</div>

			<!-- Product Variations Template -->
			<div class="ctc-chat-template-card">
				<div class="ctc-chat-template-header">
					<span class="ctc-chat-template-icon dashicons dashicons-admin-settings"></span>
					<h3 class="ctc-chat-template-title"><?php esc_html_e( 'Product Variations Template', 'aicoso-click-to-chat' ); ?></h3>
				</div>
				<div class="ctc-chat-template-body">
					<p class="ctc-chat-template-description"><?php esc_html_e( 'This template is used for variable products when specific variations are selected.', 'aicoso-click-to-chat' ); ?></p>

					<div class="ctc-chat-placeholders-section">
						<label class="ctc-chat-field-label"><?php esc_html_e( 'Available Placeholders:', 'aicoso-click-to-chat' ); ?></label>
						<div class="ctc-chat-placeholders-grid">
							<?php
							$ctc_chat_placeholders = $ctc_chat_settings_helper->ctc_chat_get_template_placeholders( 'ctc_chat_variations' );
							foreach ( $ctc_chat_placeholders as $ctc_chat_placeholder => $ctc_chat_description ) {
								echo '<span class="ctc-chat-placeholder-tag" data-placeholder="' . esc_attr( $ctc_chat_placeholder ) . '" title="' . esc_attr( $ctc_chat_description ) . '">' . esc_html( $ctc_chat_placeholder ) . '</span>';
							}
							?>
						</div>
					</div>

					<div class="ctc-chat-field-group">
						<label class="ctc-chat-field-label"><?php esc_html_e( 'Message Template', 'aicoso-click-to-chat' ); ?></label>
						<textarea name="ctc_chat_templates[ctc_chat_variations]" class="ctc-chat-template-textarea" rows="8"><?php echo esc_textarea( html_entity_decode( $ctc_chat_message_templates['ctc_chat_variations'] ?? '', ENT_QUOTES | ENT_HTML5, 'UTF-8' ) ); ?></textarea>
					</div>

					<div class="ctc-chat-template-actions">
						<button type="button" class="ctc-chat-preview-btn" data-template-type="ctc_chat_variations">
							<span class="dashicons dashicons-visibility"></span>
							<?php esc_html_e( 'Preview', 'aicoso-click-to-chat' ); ?>
						</button>
					</div>

					<div class="ctc-chat-template-preview ctc-chat-hidden"></div>
				</div>
			</div>

			<!-- Cart & Checkout Template -->
			<div class="ctc-chat-template-card">
				<div class="ctc-chat-template-header">
					<span class="ctc-chat-template-icon dashicons dashicons-cart"></span>
					<h3 class="ctc-chat-template-title"><?php esc_html_e( 'Cart & Checkout Template', 'aicoso-click-to-chat' ); ?></h3>
				</div>
				<div class="ctc-chat-template-body">
					<p class="ctc-chat-template-description"><?php esc_html_e( 'This template is used for inquiries from the cart or checkout pages.', 'aicoso-click-to-chat' ); ?></p>

					<div class="ctc-chat-placeholders-section">
						<label class="ctc-chat-field-label"><?php esc_html_e( 'Available Placeholders:', 'aicoso-click-to-chat' ); ?></label>
						<div class="ctc-chat-placeholders-grid">
							<?php
							$ctc_chat_placeholders = $ctc_chat_settings_helper->ctc_chat_get_template_placeholders( 'ctc_chat_cart_checkout' );
							foreach ( $ctc_chat_placeholders as $ctc_chat_placeholder => $ctc_chat_description ) {
								echo '<span class="ctc-chat-placeholder-tag" data-placeholder="' . esc_attr( $ctc_chat_placeholder ) . '" title="' . esc_attr( $ctc_chat_description ) . '">' . esc_html( $ctc_chat_placeholder ) . '</span>';
							}
							?>
						</div>
					</div>

					<div class="ctc-chat-field-group">
						<label class="ctc-chat-field-label"><?php esc_html_e( 'Message Template', 'aicoso-click-to-chat' ); ?></label>
						<textarea name="ctc_chat_templates[ctc_chat_cart_checkout]" class="ctc-chat-template-textarea" rows="8"><?php echo esc_textarea( html_entity_decode( $ctc_chat_message_templates['ctc_chat_cart_checkout'] ?? '', ENT_QUOTES | ENT_HTML5, 'UTF-8' ) ); ?></textarea>
					</div>

					<div class="ctc-chat-template-actions">
						<button type="button" class="ctc-chat-preview-btn" data-template-type="ctc_chat_cart_checkout">
							<span class="dashicons dashicons-visibility"></span>
							<?php esc_html_e( 'Preview', 'aicoso-click-to-chat' ); ?>
						</button>
					</div>

					<div class="ctc-chat-template-preview ctc-chat-hidden"></div>
				</div>
			</div>

			<!-- Thank You Page Template -->
			<div class="ctc-chat-template-card">
				<div class="ctc-chat-template-header">
					<span class="ctc-chat-template-icon dashicons dashicons-yes-alt"></span>
					<h3 class="ctc-chat-template-title"><?php esc_html_e( 'Thank You Page Template', 'aicoso-click-to-chat' ); ?></h3>
				</div>
				<div class="ctc-chat-template-body">
					<p class="ctc-chat-template-description"><?php esc_html_e( 'This template is used for inquiries from the order confirmation/thank you page.', 'aicoso-click-to-chat' ); ?></p>

					<div class="ctc-chat-placeholders-section">
						<label class="ctc-chat-field-label"><?php esc_html_e( 'Available Placeholders:', 'aicoso-click-to-chat' ); ?></label>
						<div class="ctc-chat-placeholders-grid">
							<?php
							$ctc_chat_placeholders = $ctc_chat_settings_helper->ctc_chat_get_template_placeholders( 'ctc_chat_thank_you' );
							foreach ( $ctc_chat_placeholders as $ctc_chat_placeholder => $ctc_chat_description ) {
								echo '<span class="ctc-chat-placeholder-tag" data-placeholder="' . esc_attr( $ctc_chat_placeholder ) . '" title="' . esc_attr( $ctc_chat_description ) . '">' . esc_html( $ctc_chat_placeholder ) . '</span>';
							}
							?>
						</div>
					</div>

					<div class="ctc-chat-field-group">
						<label class="ctc-chat-field-label"><?php esc_html_e( 'Message Template', 'aicoso-click-to-chat' ); ?></label>
						<textarea name="ctc_chat_templates[ctc_chat_thank_you]" class="ctc-chat-template-textarea" rows="8"><?php echo esc_textarea( html_entity_decode( $ctc_chat_message_templates['ctc_chat_thank_you'] ?? '', ENT_QUOTES | ENT_HTML5, 'UTF-8' ) ); ?></textarea>
					</div>

					<div class="ctc-chat-template-actions">
						<button type="button" class="ctc-chat-preview-btn" data-template-type="ctc_chat_thank_you">
							<span class="dashicons dashicons-visibility"></span>
							<?php esc_html_e( 'Preview', 'aicoso-click-to-chat' ); ?>
						</button>
					</div>

					<div class="ctc-chat-template-preview ctc-chat-hidden"></div>
				</div>
			</div>

			<!-- Floating Button Template -->
			<div class="ctc-chat-template-card">
				<div class="ctc-chat-template-header">
					<span class="ctc-chat-template-icon dashicons dashicons-format-chat"></span>
					<h3 class="ctc-chat-template-title"><?php esc_html_e( 'Floating Button Template', 'aicoso-click-to-chat' ); ?></h3>
				</div>
				<div class="ctc-chat-template-body">
					<p class="ctc-chat-template-description"><?php esc_html_e( 'This template is used for the floating WhatsApp button.', 'aicoso-click-to-chat' ); ?></p>

					<div class="ctc-chat-placeholders-section">
						<label class="ctc-chat-field-label"><?php esc_html_e( 'Available Placeholders:', 'aicoso-click-to-chat' ); ?></label>
						<div class="ctc-chat-placeholders-grid">
							<?php
							$ctc_chat_placeholders = $ctc_chat_settings_helper->ctc_chat_get_template_placeholders( 'ctc_chat_floating' );
							foreach ( $ctc_chat_placeholders as $ctc_chat_placeholder => $ctc_chat_description ) {
								echo '<span class="ctc-chat-placeholder-tag" data-placeholder="' . esc_attr( $ctc_chat_placeholder ) . '" title="' . esc_attr( $ctc_chat_description ) . '">' . esc_html( $ctc_chat_placeholder ) . '</span>';
							}
							?>
						</div>
					</div>

					<div class="ctc-chat-field-group">
						<label class="ctc-chat-field-label"><?php esc_html_e( 'Message Template', 'aicoso-click-to-chat' ); ?></label>
						<textarea name="ctc_chat_templates[ctc_chat_floating]" class="ctc-chat-template-textarea" rows="8"><?php echo esc_textarea( html_entity_decode( $ctc_chat_message_templates['ctc_chat_floating'] ?? '', ENT_QUOTES | ENT_HTML5, 'UTF-8' ) ); ?></textarea>
					</div>

					<div class="ctc-chat-template-actions">
						<button type="button" class="ctc-chat-preview-btn" data-template-type="ctc_chat_floating">
							<span class="dashicons dashicons-visibility"></span>
							<?php esc_html_e( 'Preview', 'aicoso-click-to-chat' ); ?>
						</button>
					</div>

					<div class="ctc-chat-template-preview ctc-chat-hidden"></div>
				</div>
			</div>
		</div>

		<div class="ctc-chat-save-section">
			<button type="submit" name="ctc_chat_save_templates" class="ctc-chat-save-btn">
				<span class="dashicons dashicons-saved"></span>
				<?php esc_html_e( 'Save All Templates', 'aicoso-click-to-chat' ); ?>
			</button>
			<p class="ctc-chat-save-note"><?php esc_html_e( 'Your changes will be applied to all WhatsApp messages', 'aicoso-click-to-chat' ); ?></p>
		</div>
	</form>
</div>
