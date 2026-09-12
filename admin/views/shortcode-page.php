<?php
/**
 * Improved Shortcode Generator Page
 *
 * @package ClickToChat
 * @since 1.0.0
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- View variables are scoped to the including renderer.

// Get plugin settings.
$settings = get_option( 'ctc_chat_settings', array() );

// Get button settings for defaults.
$button_settings = isset( $settings['button_settings'] ) ? $settings['button_settings'] : array();
$default_text = isset( $button_settings['text'] ) ? $button_settings['text'] : esc_html__( 'Order via WhatsApp', 'aicoso-click-to-chat' );
$default_bg_color = isset( $button_settings['bg_color'] ) ? $button_settings['bg_color'] : '#25D366';
$default_text_color = isset( $button_settings['text_color'] ) ? $button_settings['text_color'] : '#ffffff';

// Get WhatsApp numbers.
$whatsapp_numbers = isset( $settings['whatsapp_numbers'] ) ? $settings['whatsapp_numbers'] : array();
?>

<!-- Main Builder -->
<div class="ctc-chat-builder-wrapper ctc-settings-form">
		<!-- Left Side: Configuration -->
		<div class="ctc-chat-config-panel">
			<h2 class="ctc-chat-panel-title"><span class="dashicons <?php echo esc_attr( $this->get_settings_icon( 'shortcode_panel' ) ); ?>" aria-hidden="true"></span> <?php esc_html_e( 'Button Configuration', 'aicoso-click-to-chat' ); ?></h2>
			<div class="ctc-chat-config-panel__body">
			<!-- Button Type Selection -->
			<div class="ctc-chat-field-section">
				<h3><?php esc_html_e( 'Button Type', 'aicoso-click-to-chat' ); ?></h3>
				<div class="ctc-chat-button-types">
					<label class="ctc-chat-type-card">
						<input type="radio" name="button_type" value="product" class="ctc-chat-shortcode-param" data-param="type" checked>
						<div class="ctc-chat-type-card-inner">
							<span class="ctc-chat-type-icon dashicons <?php echo esc_attr( $this->get_settings_icon( 'shortcode_type_product' ) ); ?>"></span>
							<span class="ctc-chat-type-label"><?php esc_html_e( 'Product', 'aicoso-click-to-chat' ); ?></span>
							<small><?php esc_html_e( 'For product pages', 'aicoso-click-to-chat' ); ?></small>
						</div>
					</label>

					<label class="ctc-chat-type-card">
						<input type="radio" name="button_type" value="cart" class="ctc-chat-shortcode-param" data-param="type">
						<div class="ctc-chat-type-card-inner">
							<span class="ctc-chat-type-icon dashicons <?php echo esc_attr( $this->get_settings_icon( 'shortcode_type_cart' ) ); ?>"></span>
							<span class="ctc-chat-type-label"><?php esc_html_e( 'Cart', 'aicoso-click-to-chat' ); ?></span>
							<small><?php esc_html_e( 'Include cart items', 'aicoso-click-to-chat' ); ?></small>
						</div>
					</label>

					<label class="ctc-chat-type-card">
						<input type="radio" name="button_type" value="shop" class="ctc-chat-shortcode-param" data-param="type">
						<div class="ctc-chat-type-card-inner">
							<span class="ctc-chat-type-icon dashicons <?php echo esc_attr( $this->get_settings_icon( 'shortcode_type_shop' ) ); ?>"></span>
							<span class="ctc-chat-type-label"><?php esc_html_e( 'Shop', 'aicoso-click-to-chat' ); ?></span>
							<small><?php esc_html_e( 'Shop/category pages', 'aicoso-click-to-chat' ); ?></small>
						</div>
					</label>

					<label class="ctc-chat-type-card">
						<input type="radio" name="button_type" value="floating" class="ctc-chat-shortcode-param" data-param="type">
						<div class="ctc-chat-type-card-inner">
							<span class="ctc-chat-type-icon dashicons <?php echo esc_attr( $this->get_settings_icon( 'shortcode_type_general' ) ); ?>"></span>
							<span class="ctc-chat-type-label"><?php esc_html_e( 'General', 'aicoso-click-to-chat' ); ?></span>
							<small><?php esc_html_e( 'Simple contact', 'aicoso-click-to-chat' ); ?></small>
						</div>
					</label>
				</div>
			</div>

			<!-- Product Selection (for product type) -->
			<div class="ctc-chat-field-section ctc-chat-product-options ctc-chat-visible">
				<h3><?php esc_html_e( 'Product Selection', 'aicoso-click-to-chat' ); ?></h3>
				<div class="ctc-chat-field-group">
					<label class="ctc-chat-radio-option">
						<input type="radio" name="product_source" value="current" class="ctc-chat-shortcode-param" data-param="current" checked>
						<span><?php esc_html_e( 'Use current product (auto-detect)', 'aicoso-click-to-chat' ); ?></span>
					</label>
					<label class="ctc-chat-radio-option">
						<input type="radio" name="product_source" value="specific">
						<span><?php esc_html_e( 'Specify product ID', 'aicoso-click-to-chat' ); ?></span>
					</label>
					<div class="ctc-chat-product-id-field ctc-chat-hidden">
						<input type="number" id="ctc_chat_product_id" class="ctc-chat-shortcode-param" data-param="product_id" placeholder="<?php esc_attr_e( 'Enter product ID', 'aicoso-click-to-chat' ); ?>">
					</div>
				</div>
			</div>

			<!-- Appearance -->
			<div class="ctc-chat-field-section">
				<h3><?php esc_html_e( 'Appearance', 'aicoso-click-to-chat' ); ?></h3>

				<div class="ctc-chat-field-group">
					<label for="ctc_chat_button_text"><?php esc_html_e( 'Button Text', 'aicoso-click-to-chat' ); ?></label>
					<input type="text" id="ctc_chat_button_text" class="ctc-chat-shortcode-param" data-param="text" placeholder="<?php echo esc_attr( $default_text ); ?>">
				</div>

				<div class="ctc-chat-color-fields">
					<div class="ctc-chat-field-group">
						<label><?php esc_html_e( 'Background', 'aicoso-click-to-chat' ); ?></label>
						<input type="text" id="ctc_chat_bg_color" class="ctc-chat-color-field ctc-chat-shortcode-param" data-param="bg_color" value="<?php echo esc_attr( $default_bg_color ); ?>">
					</div>
					<div class="ctc-chat-field-group">
						<label><?php esc_html_e( 'Text Color', 'aicoso-click-to-chat' ); ?></label>
						<input type="text" id="ctc_chat_text_color" class="ctc-chat-color-field ctc-chat-shortcode-param" data-param="text_color" value="<?php echo esc_attr( $default_text_color ); ?>">
					</div>
				</div>

				<div class="ctc-chat-size-align-fields">
					<div class="ctc-chat-field-group">
						<label><?php esc_html_e( 'Size', 'aicoso-click-to-chat' ); ?></label>
						<select class="ctc-chat-shortcode-param" data-param="size">
							<option value="small"><?php esc_html_e( 'Small', 'aicoso-click-to-chat' ); ?></option>
							<option value="normal" selected><?php esc_html_e( 'Normal', 'aicoso-click-to-chat' ); ?></option>
							<option value="large"><?php esc_html_e( 'Large', 'aicoso-click-to-chat' ); ?></option>
						</select>
					</div>
					<div class="ctc-chat-field-group">
						<label><?php esc_html_e( 'Alignment', 'aicoso-click-to-chat' ); ?></label>
						<select class="ctc-chat-shortcode-param" data-param="align">
							<option value="left"><?php esc_html_e( 'Left', 'aicoso-click-to-chat' ); ?></option>
							<option value="center" selected><?php esc_html_e( 'Center', 'aicoso-click-to-chat' ); ?></option>
							<option value="right"><?php esc_html_e( 'Right', 'aicoso-click-to-chat' ); ?></option>
						</select>
					</div>
				</div>

				<div class="ctc-chat-field-group">
					<label class="ctc-chat-checkbox-option">
						<input type="checkbox" class="ctc-chat-shortcode-param" data-param="icon" checked>
						<span><?php esc_html_e( 'Show WhatsApp icon', 'aicoso-click-to-chat' ); ?></span>
					</label>
				</div>
			</div>

			<!-- Advanced Options -->
			<div class="ctc-chat-field-section ctc-chat-advanced">
				<h3 class="ctc-chat-collapsible">
					<span class="dashicons dashicons-arrow-right-alt2"></span>
					<?php esc_html_e( 'Advanced Options', 'aicoso-click-to-chat' ); ?>
				</h3>
				<div class="ctc-chat-advanced-content ctc-chat-hidden">
					<?php if ( ! empty( $whatsapp_numbers ) ) : ?>
					<div class="ctc-chat-field-group">
						<label><?php esc_html_e( 'Specific Number', 'aicoso-click-to-chat' ); ?></label>
						<select class="ctc-chat-shortcode-param" data-param="show_number">
							<option value=""><?php esc_html_e( 'Use default', 'aicoso-click-to-chat' ); ?></option>
							<?php foreach ( $whatsapp_numbers as $number ) : ?>
								<option value="<?php echo esc_attr( $number['id'] ); ?>">
									<?php echo esc_html( $number['name'] . ' (' . $number['number'] . ')' ); ?>
								</option>
							<?php endforeach; ?>
						</select>
					</div>
					<?php endif; ?>

					<div class="ctc-chat-field-group">
						<label><?php esc_html_e( 'Custom Message', 'aicoso-click-to-chat' ); ?></label>
						<textarea class="ctc-chat-shortcode-param" data-param="message" rows="3" placeholder="<?php esc_attr_e( 'Optional custom message...', 'aicoso-click-to-chat' ); ?>"></textarea>
					</div>

					<div class="ctc-chat-field-group">
						<label><?php esc_html_e( 'CSS Class', 'aicoso-click-to-chat' ); ?></label>
						<input type="text" class="ctc-chat-shortcode-param" data-param="css_class" placeholder="<?php esc_attr_e( 'custom-class', 'aicoso-click-to-chat' ); ?>">
					</div>
				</div>
			</div>
		</div>
		</div>

		<!-- Right Side: Preview & Output -->
		<div class="ctc-chat-preview-panel">
			<h2 class="ctc-chat-panel-title"><span class="dashicons <?php echo esc_attr( $this->get_settings_icon( 'preview' ) ); ?>" aria-hidden="true"></span> <?php esc_html_e( 'Preview & Output', 'aicoso-click-to-chat' ); ?></h2>
			<div class="ctc-chat-preview-panel__body">
			<!-- Live Preview -->
			<div class="ctc-chat-preview-section">
				<h3><span class="dashicons <?php echo esc_attr( $this->get_settings_icon( 'preview' ) ); ?>"></span> <?php esc_html_e( 'Live Preview', 'aicoso-click-to-chat' ); ?></h3>
				<div class="ctc-chat-preview-area">
					<div id="ctc-chat-button-preview">
						<!-- Preview will be generated here -->
					</div>
				</div>
			</div>

			<!-- Generated Shortcode -->
			<div class="ctc-chat-shortcode-section">
				<h3><span class="dashicons <?php echo esc_attr( $this->get_settings_icon( 'shortcode_output' ) ); ?>"></span> <?php esc_html_e( 'Your Shortcode', 'aicoso-click-to-chat' ); ?></h3>
				<div class="ctc-chat-shortcode-box">
					<code id="ctc-chat-generated-shortcode">[ctc_chat_button]</code>
					<button type="button" class="button button-secondary" id="ctc-chat-copy-shortcode">
						<span class="dashicons <?php echo esc_attr( $this->get_settings_icon( 'shortcode_copy' ) ); ?>" aria-hidden="true"></span>
						<span class="ctc-chat-copy-text"><?php esc_html_e( 'Copy', 'aicoso-click-to-chat' ); ?></span>
					</button>
				</div>
				<div class="ctc-chat-copy-success ctc-chat-hidden">
					<span class="dashicons dashicons-yes"></span> <?php esc_html_e( 'Copied to clipboard!', 'aicoso-click-to-chat' ); ?>
				</div>
			</div>

			<!-- Quick Examples -->
			<div class="ctc-chat-examples-section">
				<h3><span class="dashicons <?php echo esc_attr( $this->get_settings_icon( 'shortcode_examples' ) ); ?>" aria-hidden="true"></span> <?php esc_html_e( 'Quick Examples', 'aicoso-click-to-chat' ); ?></h3>
				<div class="ctc-chat-example-list">
					<div class="ctc-chat-example">
						<strong><?php esc_html_e( 'Basic button:', 'aicoso-click-to-chat' ); ?></strong>
						<code>[ctc_chat_button]</code>
					</div>
					<div class="ctc-chat-example">
						<strong><?php esc_html_e( 'Specific product:', 'aicoso-click-to-chat' ); ?></strong>
						<code>[ctc_chat_button product_id="123"]</code>
					</div>
					<div class="ctc-chat-example">
						<strong><?php esc_html_e( 'Cart button:', 'aicoso-click-to-chat' ); ?></strong>
						<code>[ctc_chat_button type="cart"]</code>
					</div>
					<div class="ctc-chat-example">
						<strong><?php esc_html_e( 'Custom text:', 'aicoso-click-to-chat' ); ?></strong>
						<code>[ctc_chat_button text="Contact Us"]</code>
					</div>
				</div>
			</div>

			<!-- Help Tips -->
			<div class="ctc-chat-help-section">
				<h3><span class="dashicons <?php echo esc_attr( $this->get_settings_icon( 'shortcode_usage' ) ); ?>"></span> <?php esc_html_e( 'Where to Use', 'aicoso-click-to-chat' ); ?></h3>
				<ul class="ctc-chat-help-list">
					<li><span class="dashicons dashicons-yes"></span> <?php esc_html_e( 'In any WordPress post or page content', 'aicoso-click-to-chat' ); ?></li>
					<li><span class="dashicons dashicons-yes"></span> <?php esc_html_e( 'In text widgets', 'aicoso-click-to-chat' ); ?></li>
					<li><span class="dashicons dashicons-yes"></span> <?php esc_html_e( 'In page builders (Elementor, Gutenberg, etc.)', 'aicoso-click-to-chat' ); ?></li>
					<li><span class="dashicons dashicons-yes"></span> <?php esc_html_e( 'In product descriptions', 'aicoso-click-to-chat' ); ?></li>
					<li><span class="dashicons dashicons-yes"></span> <?php esc_html_e( 'Multiple buttons on the same page', 'aicoso-click-to-chat' ); ?></li>
				</ul>
			</div>
			</div>
		</div>
	</div>
