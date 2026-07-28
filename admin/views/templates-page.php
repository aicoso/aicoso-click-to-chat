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
$settings = get_option( 'ctc_chat_settings', array() );

// Get message templates.
$message_templates = isset( $settings['message_templates'] ) ? $settings['message_templates'] : array();

// Initialize templates if not set.
if ( empty( $message_templates ) ) {
	$message_templates = array(
		'single_product' => "Hello! I'm interested in the product: *{product_name}*\nPrice: {price}\nURL: {product_url}\n\nDo you have this item in stock? I'd like to get more information.",
		'cart_checkout'  => "Hello! I'd like to complete my purchase of:\n{cart_items_list}\n---------------------\nSubtotal: {cart_subtotal}\nTax: {tax_amount}\nShipping: {shipping_method} - {shipping_cost}\nTotal: {cart_total}\n\nI have a few questions before finalizing my order.",
		'thank_you'      => "Hello! I've just placed order #{order_number} on {order_date}.\nMy order includes:\n{ordered_items_list}\n---------------------\nApplied Coupon: {coupon_code}\nTotal: {order_total}\n\nI'd like to confirm when this will be shipped.",
		'floating'       => 'Hello! I was browsing your website at {current_page_url} and have a question.',
		'variations'     => "Hello! I'm interested in the product: *{product_name}*\nSelected options: {variation_details}\nPrice: {variation_price}\nURL: {product_url}\n\nIs this combination available for immediate shipping?",
	);
}

// Get settings helper for template placeholders.
$settings_helper = new CTC_Chat_Settings();
?>

<div class="ctc-settings-inline-notices">
	<?php $this->render_settings_messages( 'ctc_templates' ); ?>
</div>

<form method="post" action="" class="ctc-settings-form">
		<?php wp_nonce_field( 'ctc_chat_templates_nonce', 'ctc_chat_templates_nonce' ); ?>

		<div class="ctc-chat-templates-wrapper">
			<!-- Single Product Template -->
			<div class="ctc-chat-template-card">
				<div class="ctc-chat-template-header">
					<span class="ctc-chat-template-icon dashicons <?php echo esc_attr( $this->get_settings_icon( 'template_product' ) ); ?>"></span>
					<h3 class="ctc-chat-template-title"><?php esc_html_e( 'Single Product Template', 'aicoso-click-to-chat' ); ?></h3>
				</div>
				<div class="ctc-chat-template-body">
					<p class="ctc-chat-template-description"><?php esc_html_e( 'This template is used for inquiries about a specific product.', 'aicoso-click-to-chat' ); ?></p>

					<div class="ctc-chat-placeholders-section">
						<label class="ctc-chat-field-label"><?php esc_html_e( 'Available Placeholders:', 'aicoso-click-to-chat' ); ?></label>
						<div class="ctc-chat-placeholders-grid">
							<?php
							$placeholders = $settings_helper->get_template_placeholders( 'single_product' );
							foreach ( $placeholders as $placeholder => $description ) {
								echo '<span class="ctc-chat-placeholder-tag" data-placeholder="' . esc_attr( $placeholder ) . '" title="' . esc_attr( $description ) . '">' . esc_html( $placeholder ) . '</span>';
							}
							?>
						</div>
					</div>

					<div class="ctc-chat-field-group">
						<label class="ctc-chat-field-label"><?php esc_html_e( 'Message Template', 'aicoso-click-to-chat' ); ?></label>
						<textarea name="ctc_chat_templates[single_product]" class="ctc-chat-template-textarea" rows="8"><?php echo esc_textarea( html_entity_decode( $message_templates['single_product'] ?? '', ENT_QUOTES | ENT_HTML5, 'UTF-8' ) ); ?></textarea>
					</div>

					<div class="ctc-chat-template-actions">
						<button type="button" class="button button-secondary ctc-chat-preview-btn" data-template-type="single_product">
							<span class="dashicons <?php echo esc_attr( $this->get_settings_icon( 'preview' ) ); ?>"></span>
							<?php esc_html_e( 'Preview', 'aicoso-click-to-chat' ); ?>
						</button>
					</div>

					<div class="ctc-chat-template-preview ctc-chat-hidden"></div>
				</div>
			</div>

			<!-- Product Variations Template -->
			<div class="ctc-chat-template-card">
				<div class="ctc-chat-template-header">
					<span class="ctc-chat-template-icon dashicons <?php echo esc_attr( $this->get_settings_icon( 'template_variations' ) ); ?>"></span>
					<h3 class="ctc-chat-template-title"><?php esc_html_e( 'Product Variations Template', 'aicoso-click-to-chat' ); ?></h3>
				</div>
				<div class="ctc-chat-template-body">
					<p class="ctc-chat-template-description"><?php esc_html_e( 'This template is used for variable products when specific variations are selected.', 'aicoso-click-to-chat' ); ?></p>

					<div class="ctc-chat-placeholders-section">
						<label class="ctc-chat-field-label"><?php esc_html_e( 'Available Placeholders:', 'aicoso-click-to-chat' ); ?></label>
						<div class="ctc-chat-placeholders-grid">
							<?php
							$placeholders = $settings_helper->get_template_placeholders( 'variations' );
							foreach ( $placeholders as $placeholder => $description ) {
								echo '<span class="ctc-chat-placeholder-tag" data-placeholder="' . esc_attr( $placeholder ) . '" title="' . esc_attr( $description ) . '">' . esc_html( $placeholder ) . '</span>';
							}
							?>
						</div>
					</div>

					<div class="ctc-chat-field-group">
						<label class="ctc-chat-field-label"><?php esc_html_e( 'Message Template', 'aicoso-click-to-chat' ); ?></label>
						<textarea name="ctc_chat_templates[variations]" class="ctc-chat-template-textarea" rows="8"><?php echo esc_textarea( html_entity_decode( $message_templates['variations'] ?? '', ENT_QUOTES | ENT_HTML5, 'UTF-8' ) ); ?></textarea>
					</div>

					<div class="ctc-chat-template-actions">
						<button type="button" class="button button-secondary ctc-chat-preview-btn" data-template-type="variations">
							<span class="dashicons <?php echo esc_attr( $this->get_settings_icon( 'preview' ) ); ?>"></span>
							<?php esc_html_e( 'Preview', 'aicoso-click-to-chat' ); ?>
						</button>
					</div>

					<div class="ctc-chat-template-preview ctc-chat-hidden"></div>
				</div>
			</div>

			<!-- Cart & Checkout Template -->
			<div class="ctc-chat-template-card">
				<div class="ctc-chat-template-header">
					<span class="ctc-chat-template-icon dashicons <?php echo esc_attr( $this->get_settings_icon( 'template_cart' ) ); ?>"></span>
					<h3 class="ctc-chat-template-title"><?php esc_html_e( 'Cart & Checkout Template', 'aicoso-click-to-chat' ); ?></h3>
				</div>
				<div class="ctc-chat-template-body">
					<p class="ctc-chat-template-description"><?php esc_html_e( 'This template is used for inquiries from the cart or checkout pages.', 'aicoso-click-to-chat' ); ?></p>

					<div class="ctc-chat-placeholders-section">
						<label class="ctc-chat-field-label"><?php esc_html_e( 'Available Placeholders:', 'aicoso-click-to-chat' ); ?></label>
						<div class="ctc-chat-placeholders-grid">
							<?php
							$placeholders = $settings_helper->get_template_placeholders( 'cart_checkout' );
							foreach ( $placeholders as $placeholder => $description ) {
								echo '<span class="ctc-chat-placeholder-tag" data-placeholder="' . esc_attr( $placeholder ) . '" title="' . esc_attr( $description ) . '">' . esc_html( $placeholder ) . '</span>';
							}
							?>
						</div>
					</div>

					<div class="ctc-chat-field-group">
						<label class="ctc-chat-field-label"><?php esc_html_e( 'Message Template', 'aicoso-click-to-chat' ); ?></label>
						<textarea name="ctc_chat_templates[cart_checkout]" class="ctc-chat-template-textarea" rows="8"><?php echo esc_textarea( html_entity_decode( $message_templates['cart_checkout'] ?? '', ENT_QUOTES | ENT_HTML5, 'UTF-8' ) ); ?></textarea>
					</div>

					<div class="ctc-chat-template-actions">
						<button type="button" class="button button-secondary ctc-chat-preview-btn" data-template-type="cart_checkout">
							<span class="dashicons <?php echo esc_attr( $this->get_settings_icon( 'preview' ) ); ?>"></span>
							<?php esc_html_e( 'Preview', 'aicoso-click-to-chat' ); ?>
						</button>
					</div>

					<div class="ctc-chat-template-preview ctc-chat-hidden"></div>
				</div>
			</div>

			<!-- Thank You Page Template -->
			<div class="ctc-chat-template-card">
				<div class="ctc-chat-template-header">
					<span class="ctc-chat-template-icon dashicons <?php echo esc_attr( $this->get_settings_icon( 'template_thankyou' ) ); ?>"></span>
					<h3 class="ctc-chat-template-title"><?php esc_html_e( 'Thank You Page Template', 'aicoso-click-to-chat' ); ?></h3>
				</div>
				<div class="ctc-chat-template-body">
					<p class="ctc-chat-template-description"><?php esc_html_e( 'This template is used for inquiries from the order confirmation/thank you page.', 'aicoso-click-to-chat' ); ?></p>

					<div class="ctc-chat-placeholders-section">
						<label class="ctc-chat-field-label"><?php esc_html_e( 'Available Placeholders:', 'aicoso-click-to-chat' ); ?></label>
						<div class="ctc-chat-placeholders-grid">
							<?php
							$placeholders = $settings_helper->get_template_placeholders( 'thank_you' );
							foreach ( $placeholders as $placeholder => $description ) {
								echo '<span class="ctc-chat-placeholder-tag" data-placeholder="' . esc_attr( $placeholder ) . '" title="' . esc_attr( $description ) . '">' . esc_html( $placeholder ) . '</span>';
							}
							?>
						</div>
					</div>

					<div class="ctc-chat-field-group">
						<label class="ctc-chat-field-label"><?php esc_html_e( 'Message Template', 'aicoso-click-to-chat' ); ?></label>
						<textarea name="ctc_chat_templates[thank_you]" class="ctc-chat-template-textarea" rows="8"><?php echo esc_textarea( html_entity_decode( $message_templates['thank_you'] ?? '', ENT_QUOTES | ENT_HTML5, 'UTF-8' ) ); ?></textarea>
					</div>

					<div class="ctc-chat-template-actions">
						<button type="button" class="button button-secondary ctc-chat-preview-btn" data-template-type="thank_you">
							<span class="dashicons <?php echo esc_attr( $this->get_settings_icon( 'preview' ) ); ?>"></span>
							<?php esc_html_e( 'Preview', 'aicoso-click-to-chat' ); ?>
						</button>
					</div>

					<div class="ctc-chat-template-preview ctc-chat-hidden"></div>
				</div>
			</div>

			<!-- Floating Button Template -->
			<div class="ctc-chat-template-card">
				<div class="ctc-chat-template-header">
					<span class="ctc-chat-template-icon dashicons <?php echo esc_attr( $this->get_settings_icon( 'template_floating' ) ); ?>"></span>
					<h3 class="ctc-chat-template-title"><?php esc_html_e( 'Floating Button Template', 'aicoso-click-to-chat' ); ?></h3>
				</div>
				<div class="ctc-chat-template-body">
					<p class="ctc-chat-template-description"><?php esc_html_e( 'This template is used for the floating WhatsApp button.', 'aicoso-click-to-chat' ); ?></p>

					<div class="ctc-chat-placeholders-section">
						<label class="ctc-chat-field-label"><?php esc_html_e( 'Available Placeholders:', 'aicoso-click-to-chat' ); ?></label>
						<div class="ctc-chat-placeholders-grid">
							<?php
							$placeholders = $settings_helper->get_template_placeholders( 'floating' );
							foreach ( $placeholders as $placeholder => $description ) {
								echo '<span class="ctc-chat-placeholder-tag" data-placeholder="' . esc_attr( $placeholder ) . '" title="' . esc_attr( $description ) . '">' . esc_html( $placeholder ) . '</span>';
							}
							?>
						</div>
					</div>

					<div class="ctc-chat-field-group">
						<label class="ctc-chat-field-label"><?php esc_html_e( 'Message Template', 'aicoso-click-to-chat' ); ?></label>
						<textarea name="ctc_chat_templates[floating]" class="ctc-chat-template-textarea" rows="8"><?php echo esc_textarea( html_entity_decode( $message_templates['floating'] ?? '', ENT_QUOTES | ENT_HTML5, 'UTF-8' ) ); ?></textarea>
					</div>

					<div class="ctc-chat-template-actions">
						<button type="button" class="button button-secondary ctc-chat-preview-btn" data-template-type="floating">
							<span class="dashicons <?php echo esc_attr( $this->get_settings_icon( 'preview' ) ); ?>"></span>
							<?php esc_html_e( 'Preview', 'aicoso-click-to-chat' ); ?>
						</button>
					</div>

					<div class="ctc-chat-template-preview ctc-chat-hidden"></div>
				</div>
			</div>
		</div>

		<?php $this->render_settings_footer( 'ctc_chat_save_templates', esc_html__( 'Save All Templates', 'aicoso-click-to-chat' ) ); ?>
	</form>
