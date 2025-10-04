<?php
/**
 * Settings page view.
 *
 * @package ClickToChat
 * @since 1.0.0
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

// Get plugin settings.
$settings = get_option( 'ctc_settings', array() );

// Get settings helper.
$settings_helper = new CTC_Settings();

// Get position options.
$product_positions = $settings_helper->get_product_position_options();
$shop_positions = $settings_helper->get_shop_position_options();
$floating_positions = $settings_helper->get_floating_position_options();
?>

<div class="wrap ctc-admin-container">
	<div class="ctc-admin-header">
		<span class="ctc-admin-logo dashicons dashicons-whatsapp"></span>
		<h1 class="ctc-admin-heading"><?php esc_html_e( 'Plugin Settings', 'click-to-chat' ); ?></h1>
	</div>

	<!-- Instructions Banner - Same style as Templates page -->
	<div class="ctc-instructions-banner">
		<h2><span class="dashicons dashicons-admin-generic"></span> <?php esc_html_e( 'Configure Your WhatsApp Integration', 'click-to-chat' ); ?></h2>
		<p><?php esc_html_e( 'Customize how the WhatsApp button appears and behaves across your WooCommerce store.', 'click-to-chat' ); ?></p>
		<div class="ctc-quick-steps">
			<div class="ctc-step">
				<span class="ctc-step-number">1</span>
				<span><?php esc_html_e( 'General settings', 'click-to-chat' ); ?></span>
			</div>
			<div class="ctc-step">
				<span class="ctc-step-number">2</span>
				<span><?php esc_html_e( 'Button appearance', 'click-to-chat' ); ?></span>
			</div>
			<div class="ctc-step">
				<span class="ctc-step-number">3</span>
				<span><?php esc_html_e( 'Display locations', 'click-to-chat' ); ?></span>
			</div>
			<div class="ctc-step">
				<span class="ctc-step-number">4</span>
				<span><?php esc_html_e( 'Save your settings', 'click-to-chat' ); ?></span>
			</div>
		</div>
	</div>

	<?php
	// Check if plugin is disabled or no numbers configured.
	$plugin_enabled = isset( $settings['plugin_enabled'] ) ? $settings['plugin_enabled'] : true;

	// Check if there are any numbers with actual phone numbers configured.
	$has_valid_numbers = false;
	if ( isset( $settings['whatsapp_numbers'] ) && ! empty( $settings['whatsapp_numbers'] ) ) {
		foreach ( $settings['whatsapp_numbers'] as $number ) {
			if ( ! empty( $number['number'] ) ) {
				$has_valid_numbers = true;
				break;
			}
		}
	}

	if ( ! $plugin_enabled || ! $has_valid_numbers ) :
		?>
	<div class="ctc-admin-warning-box">
		<?php if ( ! $plugin_enabled ) : ?>
		<div class="ctc-warning-item">
			<span class="ctc-warning-icon">⚠️</span>
			<div class="ctc-warning-content">
				<strong><?php esc_html_e( 'Warning:', 'click-to-chat' ); ?></strong>
				<?php esc_html_e( 'The plugin is currently disabled. WhatsApp buttons will not appear on your website. Enable the plugin below to activate it.', 'click-to-chat' ); ?>
			</div>
		</div>
		<?php endif; ?>

		<?php if ( ! $has_valid_numbers ) : ?>
		<div class="ctc-warning-item">
			<span class="ctc-warning-icon">⚠️</span>
			<div class="ctc-warning-content">
				<strong><?php esc_html_e( 'Warning:', 'click-to-chat' ); ?></strong>
				<?php
				printf(
					/* translators: %s: Link to Numbers page */
					esc_html__( 'No WhatsApp number configured! You need to add at least one WhatsApp number for the buttons to work. %s', 'click-to-chat' ),
					'<a href="' . esc_url( admin_url( 'admin.php?page=click-to-chat-numbers' ) ) . '">' . esc_html__( 'Add a number now →', 'click-to-chat' ) . '</a>'
				);
				?>
			</div>
		</div>
		<?php endif; ?>
	</div>
	<?php endif; ?>

	<form method="post" action="">
		<?php wp_nonce_field( 'ctc_settings_nonce', 'ctc_settings_nonce' ); ?>

		<!-- Tab Navigation -->
		<div class="ctc-settings-nav">
			<button type="button" class="ctc-settings-nav-item active" data-tab="general">
				<span class="dashicons dashicons-admin-settings"></span>
				<?php esc_html_e( 'General', 'click-to-chat' ); ?>
			</button>
			<button type="button" class="ctc-settings-nav-item" data-tab="button">
				<span class="dashicons dashicons-button"></span>
				<?php esc_html_e( 'Button Style', 'click-to-chat' ); ?>
			</button>
			<button type="button" class="ctc-settings-nav-item" data-tab="display">
				<span class="dashicons dashicons-visibility"></span>
				<?php esc_html_e( 'Display', 'click-to-chat' ); ?>
			</button>
			<button type="button" class="ctc-settings-nav-item" data-tab="exclusions">
				<span class="dashicons dashicons-hidden"></span>
				<?php esc_html_e( 'Exclusions', 'click-to-chat' ); ?>
			</button>
		</div>

		<!-- General Settings Tab -->
		<div id="ctc-settings-general" class="ctc-settings-panel active">
			<div class="ctc-number-card">
				<div class="ctc-number-header">
					<div class="ctc-number-title-section">
						<span class="ctc-number-icon dashicons dashicons-admin-settings"></span>
						<h3 class="ctc-number-title"><?php esc_html_e( 'Plugin Status', 'click-to-chat' ); ?></h3>
					</div>
				</div>
				<div class="ctc-number-body">
					<div class="ctc-form-group">
						<label>
							<input type="checkbox" name="ctc_plugin_enabled" id="ctc_plugin_enabled" value="1" <?php checked( isset( $settings['plugin_enabled'] ) ? $settings['plugin_enabled'] : true, true ); ?>>
							<?php esc_html_e( 'Enable Click to Chat plugin', 'click-to-chat' ); ?>
						</label>
						<p class="ctc-field-description"><?php esc_html_e( 'When disabled, no WhatsApp buttons will be displayed on your website.', 'click-to-chat' ); ?></p>
					</div>
				</div>
			</div>

			<div class="ctc-number-card">
				<div class="ctc-number-header">
					<div class="ctc-number-title-section">
						<span class="ctc-number-icon dashicons dashicons-admin-links"></span>
						<h3 class="ctc-number-title"><?php esc_html_e( 'Quick Links', 'click-to-chat' ); ?></h3>
					</div>
				</div>
				<div class="ctc-number-body">
					<div class="ctc-button-group">
						<a href="<?php echo esc_url( admin_url( 'admin.php?page=click-to-chat-numbers' ) ); ?>" class="button button-secondary">
							<span class="dashicons dashicons-phone"></span>
							<?php esc_html_e( 'WhatsApp Numbers', 'click-to-chat' ); ?>
						</a>
						<a href="<?php echo esc_url( admin_url( 'admin.php?page=click-to-chat-templates' ) ); ?>" class="button button-secondary">
							<span class="dashicons dashicons-text"></span>
							<?php esc_html_e( 'Message Templates', 'click-to-chat' ); ?>
						</a>
					</div>
					<p class="ctc-field-description"><?php esc_html_e( 'Manage your WhatsApp numbers and message templates from these sections.', 'click-to-chat' ); ?></p>
				</div>
			</div>
		</div>

		<!-- Button Settings Tab -->
		<div id="ctc-settings-button" class="ctc-settings-panel">
			<div class="ctc-number-card">
				<div class="ctc-number-header">
					<div class="ctc-number-title-section">
						<span class="ctc-number-icon dashicons dashicons-button"></span>
						<h3 class="ctc-number-title"><?php esc_html_e( 'Button Appearance', 'click-to-chat' ); ?></h3>
					</div>
				</div>
				<div class="ctc-number-body">
					<div class="ctc-form-group">
						<label for="ctc_button_text"><?php esc_html_e( 'Button Text', 'click-to-chat' ); ?></label>
						<input type="text" name="ctc_button[text]" id="ctc_button_text" value="<?php echo esc_attr( isset( $settings['button_settings']['text'] ) ? $settings['button_settings']['text'] : esc_html__( 'Order via WhatsApp', 'click-to-chat' ) ); ?>" class="regular-text">
						<p class="ctc-field-description"><?php esc_html_e( 'The text to display on the WhatsApp button.', 'click-to-chat' ); ?></p>
					</div>

					<div class="ctc-form-group">
						<label>
							<input type="checkbox" name="ctc_button[icon]" id="ctc_button_icon" value="1" <?php checked( isset( $settings['button_settings']['icon'] ) ? $settings['button_settings']['icon'] : true ); ?>>
							<?php esc_html_e( 'Show WhatsApp icon on the button', 'click-to-chat' ); ?>
						</label>
					</div>

					<div class="ctc-form-group">
						<label for="ctc_button_bg_color"><?php esc_html_e( 'Background Color', 'click-to-chat' ); ?></label>
						<input type="text" name="ctc_button[bg_color]" id="ctc_button_bg_color" value="<?php echo esc_attr( isset( $settings['button_settings']['bg_color'] ) ? $settings['button_settings']['bg_color'] : '#25D366' ); ?>" class="ctc-color-field">
					</div>

					<div class="ctc-form-group">
						<label for="ctc_button_text_color"><?php esc_html_e( 'Text Color', 'click-to-chat' ); ?></label>
						<input type="text" name="ctc_button[text_color]" id="ctc_button_text_color" value="<?php echo esc_attr( isset( $settings['button_settings']['text_color'] ) ? $settings['button_settings']['text_color'] : '#ffffff' ); ?>" class="ctc-color-field">
					</div>

					<div class="ctc-form-group">
						<label for="ctc_button_custom_css"><?php esc_html_e( 'Custom CSS', 'click-to-chat' ); ?></label>
						<textarea name="ctc_button[custom_css]" id="ctc_button_custom_css" rows="6" class="large-text code"><?php echo esc_textarea( isset( $settings['button_settings']['custom_css'] ) ? $settings['button_settings']['custom_css'] : '' ); ?></textarea>
						<p class="ctc-field-description"><?php esc_html_e( 'Add custom CSS for advanced button styling.', 'click-to-chat' ); ?></p>
					</div>
				</div>
			</div>
		</div>

		<!-- Display Settings Tab -->
		<div id="ctc-settings-display" class="ctc-settings-panel">
			<!-- Single Product Pages -->
			<div class="ctc-number-card">
				<div class="ctc-number-header">
					<div class="ctc-number-title-section">
						<span class="ctc-number-icon dashicons dashicons-cart"></span>
						<h3 class="ctc-number-title"><?php esc_html_e( 'Single Product Pages', 'click-to-chat' ); ?></h3>
					</div>
				</div>
				<div class="ctc-number-body">
					<div class="ctc-form-group">
						<label>
							<input type="checkbox" name="ctc_single_product[enabled]" id="ctc_single_product_enabled" value="1" <?php checked( isset( $settings['single_product']['enabled'] ) ? $settings['single_product']['enabled'] : true ); ?>>
							<?php esc_html_e( 'Show WhatsApp button on single product pages', 'click-to-chat' ); ?>
						</label>
					</div>

					<div class="ctc-form-group">
						<label for="ctc_single_product_position"><?php esc_html_e( 'Button Position', 'click-to-chat' ); ?></label>
						<select name="ctc_single_product[position]" id="ctc_single_product_position" class="regular-text">
							<?php foreach ( $product_positions as $value => $label ) : ?>
								<option value="<?php echo esc_attr( $value ); ?>" <?php selected( isset( $settings['single_product']['position'] ) ? $settings['single_product']['position'] : 'after_add_to_cart', $value ); ?>><?php echo esc_html( $label ); ?></option>
							<?php endforeach; ?>
						</select>
					</div>
				</div>
			</div>

			<!-- Shop Pages -->
			<div class="ctc-number-card">
				<div class="ctc-number-header">
					<div class="ctc-number-title-section">
						<span class="ctc-number-icon dashicons dashicons-store"></span>
						<h3 class="ctc-number-title"><?php esc_html_e( 'Shop Pages', 'click-to-chat' ); ?></h3>
					</div>
				</div>
				<div class="ctc-number-body">
					<div class="ctc-form-group">
						<label>
							<input type="checkbox" name="ctc_shop_page[enabled]" id="ctc_shop_page_enabled" value="1" <?php checked( isset( $settings['shop_page']['enabled'] ) ? $settings['shop_page']['enabled'] : false ); ?>>
							<?php esc_html_e( 'Show WhatsApp button on shop/archive pages', 'click-to-chat' ); ?>
						</label>
					</div>

					<div class="ctc-form-group">
						<label for="ctc_shop_page_position"><?php esc_html_e( 'Button Position', 'click-to-chat' ); ?></label>
						<select name="ctc_shop_page[position]" id="ctc_shop_page_position" class="regular-text">
							<?php foreach ( $shop_positions as $value => $label ) : ?>
								<option value="<?php echo esc_attr( $value ); ?>" <?php selected( isset( $settings['shop_page']['position'] ) ? $settings['shop_page']['position'] : 'after_add_to_cart', $value ); ?>><?php echo esc_html( $label ); ?></option>
							<?php endforeach; ?>
						</select>
					</div>
				</div>
			</div>

			<!-- Cart & Checkout Pages -->
			<div class="ctc-number-card">
				<div class="ctc-number-header">
					<div class="ctc-number-title-section">
						<span class="ctc-number-icon dashicons dashicons-cart"></span>
						<h3 class="ctc-number-title"><?php esc_html_e( 'Cart & Checkout Pages', 'click-to-chat' ); ?></h3>
					</div>
				</div>
				<div class="ctc-number-body">
					<div class="ctc-form-group">
						<label>
							<input type="checkbox" name="ctc_cart_page[enabled]" id="ctc_cart_page_enabled" value="1" <?php checked( isset( $settings['cart_page']['enabled'] ) ? $settings['cart_page']['enabled'] : false ); ?>>
							<?php esc_html_e( 'Show WhatsApp button on the cart page', 'click-to-chat' ); ?>
						</label>
					</div>

					<div class="ctc-form-group ctc-cart-position-row<?php echo ( ! isset( $settings['cart_page']['enabled'] ) || ! $settings['cart_page']['enabled'] ) ? ' ctc-hidden' : ''; ?>">
						<label for="ctc_cart_page_position"><?php esc_html_e( 'Cart Button Position', 'click-to-chat' ); ?></label>
						<select name="ctc_cart_page[position]" id="ctc_cart_page_position" class="regular-text">
							<option value="after_cart_table" <?php selected( isset( $settings['cart_page']['position'] ) ? $settings['cart_page']['position'] : 'after_cart_table', 'after_cart_table' ); ?>><?php esc_html_e( 'After Cart Table', 'click-to-chat' ); ?></option>
							<option value="before_cart_table" <?php selected( isset( $settings['cart_page']['position'] ) ? $settings['cart_page']['position'] : '', 'before_cart_table' ); ?>><?php esc_html_e( 'Before Cart Table', 'click-to-chat' ); ?></option>
							<option value="proceed_to_checkout" <?php selected( isset( $settings['cart_page']['position'] ) ? $settings['cart_page']['position'] : '', 'proceed_to_checkout' ); ?>><?php esc_html_e( 'Next to Proceed to Checkout Button', 'click-to-chat' ); ?></option>
							<option value="after_cart_totals" <?php selected( isset( $settings['cart_page']['position'] ) ? $settings['cart_page']['position'] : '', 'after_cart_totals' ); ?>><?php esc_html_e( 'After Cart Totals', 'click-to-chat' ); ?></option>
							<option value="cart_actions" <?php selected( isset( $settings['cart_page']['position'] ) ? $settings['cart_page']['position'] : '', 'cart_actions' ); ?>><?php esc_html_e( 'In Cart Actions Area', 'click-to-chat' ); ?></option>
						</select>
					</div>

					<hr class="ctc-divider">

					<div class="ctc-form-group">
						<label>
							<input type="checkbox" name="ctc_checkout_page[enabled]" id="ctc_checkout_page_enabled" value="1" <?php checked( isset( $settings['checkout_page']['enabled'] ) ? $settings['checkout_page']['enabled'] : false ); ?>>
							<?php esc_html_e( 'Show WhatsApp button on the checkout page', 'click-to-chat' ); ?>
						</label>
					</div>

					<div class="ctc-form-group ctc-checkout-position-row<?php echo ( ! isset( $settings['checkout_page']['enabled'] ) || ! $settings['checkout_page']['enabled'] ) ? ' ctc-hidden' : ''; ?>">
						<label for="ctc_checkout_page_position"><?php esc_html_e( 'Checkout Button Position', 'click-to-chat' ); ?></label>
						<select name="ctc_checkout_page[position]" id="ctc_checkout_page_position" class="regular-text">
							<option value="after_payment" <?php selected( isset( $settings['checkout_page']['position'] ) ? $settings['checkout_page']['position'] : 'after_payment', 'after_payment' ); ?>><?php esc_html_e( 'After Payment Methods', 'click-to-chat' ); ?></option>
							<option value="before_payment" <?php selected( isset( $settings['checkout_page']['position'] ) ? $settings['checkout_page']['position'] : '', 'before_payment' ); ?>><?php esc_html_e( 'Before Payment Methods', 'click-to-chat' ); ?></option>
							<option value="after_order_review" <?php selected( isset( $settings['checkout_page']['position'] ) ? $settings['checkout_page']['position'] : '', 'after_order_review' ); ?>><?php esc_html_e( 'After Order Review', 'click-to-chat' ); ?></option>
							<option value="before_order_review" <?php selected( isset( $settings['checkout_page']['position'] ) ? $settings['checkout_page']['position'] : '', 'before_order_review' ); ?>><?php esc_html_e( 'Before Order Review', 'click-to-chat' ); ?></option>
							<option value="after_submit" <?php selected( isset( $settings['checkout_page']['position'] ) ? $settings['checkout_page']['position'] : '', 'after_submit' ); ?>><?php esc_html_e( 'After Place Order Button', 'click-to-chat' ); ?></option>
						</select>
					</div>

					<hr class="ctc-divider">

					<div class="ctc-form-group">
						<label>
							<input type="checkbox" name="ctc_thankyou_page[enabled]" id="ctc_thankyou_page_enabled" value="1" <?php checked( isset( $settings['thankyou_page']['enabled'] ) ? $settings['thankyou_page']['enabled'] : false ); ?>>
							<?php esc_html_e( 'Show WhatsApp button on the thank you/order confirmation page', 'click-to-chat' ); ?>
						</label>
					</div>
				</div>
			</div>

			<!-- Floating Button -->
			<div class="ctc-number-card">
				<div class="ctc-number-header">
					<div class="ctc-number-title-section">
						<span class="ctc-number-icon dashicons dashicons-admin-appearance"></span>
						<h3 class="ctc-number-title"><?php esc_html_e( 'Floating Button', 'click-to-chat' ); ?></h3>
					</div>
				</div>
				<div class="ctc-number-body">
					<div class="ctc-form-group">
						<label>
							<input type="checkbox" name="ctc_floating_button[enabled]" id="ctc_floating_button_enabled" value="1" <?php checked( isset( $settings['floating_button']['enabled'] ) ? $settings['floating_button']['enabled'] : false ); ?>>
							<?php esc_html_e( 'Show a floating WhatsApp button on all pages', 'click-to-chat' ); ?>
						</label>
					</div>

					<div class="ctc-form-group">
						<label for="ctc_floating_button_position"><?php esc_html_e( 'Button Position', 'click-to-chat' ); ?></label>
						<select name="ctc_floating_button[position]" id="ctc_floating_button_position" class="regular-text">
							<?php foreach ( $floating_positions as $value => $label ) : ?>
								<option value="<?php echo esc_attr( $value ); ?>" <?php selected( isset( $settings['floating_button']['position'] ) ? $settings['floating_button']['position'] : 'bottom_right', $value ); ?>><?php echo esc_html( $label ); ?></option>
							<?php endforeach; ?>
						</select>
					</div>
				</div>
			</div>

			<!-- Advanced Options -->
			<div class="ctc-number-card">
				<div class="ctc-number-header">
					<div class="ctc-number-title-section">
						<span class="ctc-number-icon dashicons dashicons-warning"></span>
						<h3 class="ctc-number-title"><?php esc_html_e( 'Advanced Options', 'click-to-chat' ); ?></h3>
					</div>
				</div>
				<div class="ctc-number-body">
					<div class="ctc-admin-notice ctc-admin-notice-warning">
						<p><?php esc_html_e( 'Warning: The options below will hide WooCommerce purchase buttons. Only enable if you want to use WhatsApp as the primary contact method for orders.', 'click-to-chat' ); ?></p>
					</div>

					<div class="ctc-form-group">
						<label>
							<input type="checkbox" name="ctc_advanced[hide_add_to_cart]" id="ctc_hide_add_to_cart" value="1" <?php checked( isset( $settings['advanced']['hide_add_to_cart'] ) ? $settings['advanced']['hide_add_to_cart'] : false ); ?>>
							<?php esc_html_e( 'Hide "Add to Cart" buttons', 'click-to-chat' ); ?>
						</label>
						<p class="ctc-field-description"><?php esc_html_e( 'Hides Add to Cart buttons on shop and product pages', 'click-to-chat' ); ?></p>
					</div>

					<div class="ctc-form-group">
						<label>
							<input type="checkbox" name="ctc_advanced[hide_proceed_checkout]" id="ctc_hide_proceed_checkout" value="1" <?php checked( isset( $settings['advanced']['hide_proceed_checkout'] ) ? $settings['advanced']['hide_proceed_checkout'] : false ); ?>>
							<?php esc_html_e( 'Hide "Proceed to Checkout" button', 'click-to-chat' ); ?>
						</label>
						<p class="ctc-field-description"><?php esc_html_e( 'Hides the Proceed to Checkout button on the cart page', 'click-to-chat' ); ?></p>
					</div>

					<div class="ctc-form-group">
						<label>
							<input type="checkbox" name="ctc_advanced[hide_place_order]" id="ctc_hide_place_order" value="1" <?php checked( isset( $settings['advanced']['hide_place_order'] ) ? $settings['advanced']['hide_place_order'] : false ); ?>>
							<?php esc_html_e( 'Hide "Place Order" button', 'click-to-chat' ); ?>
						</label>
						<p class="ctc-field-description"><?php esc_html_e( 'Hides the Place Order button on the checkout page', 'click-to-chat' ); ?></p>
					</div>

					<hr class="ctc-divider">

					<div class="ctc-form-group">
						<label>
							<input type="checkbox" name="ctc_advanced[catalog_mode]" id="ctc_catalog_mode" value="1" <?php checked( isset( $settings['advanced']['catalog_mode'] ) ? $settings['advanced']['catalog_mode'] : false ); ?>>
							<?php esc_html_e( 'Enable catalog mode (hides all purchase buttons)', 'click-to-chat' ); ?>
						</label>
						<p class="ctc-field-description"><?php esc_html_e( 'Turns your store into a catalog where customers must contact via WhatsApp to purchase', 'click-to-chat' ); ?></p>
					</div>
				</div>
			</div>
		</div>

		<!-- Exclusions Tab -->
		<div id="ctc-settings-exclusions" class="ctc-settings-panel">
			<div class="ctc-number-card">
				<div class="ctc-number-header">
					<div class="ctc-number-title-section">
						<span class="ctc-number-icon dashicons dashicons-no-alt"></span>
						<h3 class="ctc-number-title"><?php esc_html_e( 'Page & Post Exclusions', 'click-to-chat' ); ?></h3>
					</div>
				</div>
				<div class="ctc-number-body">
					<div class="ctc-form-group">
						<label for="ctc_exclude_pages"><?php esc_html_e( 'Exclude Pages', 'click-to-chat' ); ?></label>
						<select name="ctc_exclusions[pages][]" id="ctc_exclude_pages" class="ctc-page-select" multiple="multiple">
							<?php
							if ( isset( $settings['exclusions']['pages'] ) && is_array( $settings['exclusions']['pages'] ) ) {
								foreach ( $settings['exclusions']['pages'] as $page_id ) {
									$page_title = get_the_title( $page_id );
									if ( $page_title ) {
										echo '<option value="' . esc_attr( $page_id ) . '" selected>' . esc_html( $page_title ) . '</option>';
									}
								}
							}
							?>
						</select>
						<p class="ctc-field-description"><?php esc_html_e( 'Select pages where the WhatsApp button should not appear.', 'click-to-chat' ); ?></p>
					</div>

					<div class="ctc-form-group">
						<label for="ctc_exclude_posts"><?php esc_html_e( 'Exclude Posts', 'click-to-chat' ); ?></label>
						<select name="ctc_exclusions[posts][]" id="ctc_exclude_posts" class="ctc-post-select" multiple="multiple">
							<?php
							if ( isset( $settings['exclusions']['posts'] ) && is_array( $settings['exclusions']['posts'] ) ) {
								foreach ( $settings['exclusions']['posts'] as $excluded_post_id ) {
									$post_title = get_the_title( $excluded_post_id );
									if ( $post_title ) {
										echo '<option value="' . esc_attr( $excluded_post_id ) . '" selected>' . esc_html( $post_title ) . '</option>';
									}
								}
							}
							?>
						</select>
						<p class="ctc-field-description"><?php esc_html_e( 'Select posts where the WhatsApp button should not appear.', 'click-to-chat' ); ?></p>
					</div>
				</div>
			</div>

			<div class="ctc-number-card">
				<div class="ctc-number-header">
					<div class="ctc-number-title-section">
						<span class="ctc-number-icon dashicons dashicons-products"></span>
						<h3 class="ctc-number-title"><?php esc_html_e( 'Product Exclusions', 'click-to-chat' ); ?></h3>
					</div>
				</div>
				<div class="ctc-number-body">
					<div class="ctc-form-group">
						<label for="ctc_exclude_categories"><?php esc_html_e( 'Exclude Product Categories', 'click-to-chat' ); ?></label>
						<select name="ctc_exclusions[categories][]" id="ctc_exclude_categories" class="ctc-category-select" multiple="multiple">
							<?php
							if ( isset( $settings['exclusions']['categories'] ) && is_array( $settings['exclusions']['categories'] ) ) {
								foreach ( $settings['exclusions']['categories'] as $term_id ) {
									$category = get_term( $term_id, 'product_cat' );
									if ( $category && ! is_wp_error( $category ) ) {
										echo '<option value="' . esc_attr( $category->term_id ) . '" selected>' . esc_html( $category->name ) . '</option>';
									}
								}
							}
							?>
						</select>
						<p class="ctc-field-description"><?php esc_html_e( 'Select product categories where the WhatsApp button should not appear.', 'click-to-chat' ); ?></p>
					</div>

					<div class="ctc-form-group">
						<label for="ctc_exclude_tags"><?php esc_html_e( 'Exclude Product Tags', 'click-to-chat' ); ?></label>
						<select name="ctc_exclusions[tags][]" id="ctc_exclude_tags" class="ctc-tag-select" multiple="multiple">
							<?php
							if ( isset( $settings['exclusions']['tags'] ) && is_array( $settings['exclusions']['tags'] ) ) {
								foreach ( $settings['exclusions']['tags'] as $term_id ) {
									$product_tag = get_term( $term_id, 'product_tag' );
									if ( $product_tag && ! is_wp_error( $product_tag ) ) {
										echo '<option value="' . esc_attr( $product_tag->term_id ) . '" selected>' . esc_html( $product_tag->name ) . '</option>';
									}
								}
							}
							?>
						</select>
						<p class="ctc-field-description"><?php esc_html_e( 'Select product tags where the WhatsApp button should not appear.', 'click-to-chat' ); ?></p>
					</div>

					<div class="ctc-form-group">
						<label for="ctc_exclude_products"><?php esc_html_e( 'Exclude Products', 'click-to-chat' ); ?></label>
						<select name="ctc_exclusions[products][]" id="ctc_exclude_products" class="ctc-product-select" multiple="multiple">
							<?php
							if ( isset( $settings['exclusions']['products'] ) && is_array( $settings['exclusions']['products'] ) ) {
								foreach ( $settings['exclusions']['products'] as $product_id ) {
									$product_title = get_the_title( $product_id );
									if ( $product_title ) {
										echo '<option value="' . esc_attr( $product_id ) . '" selected>' . esc_html( $product_title ) . '</option>';
									}
								}
							}
							?>
						</select>
						<p class="ctc-field-description"><?php esc_html_e( 'Select specific products where the WhatsApp button should not appear.', 'click-to-chat' ); ?></p>
					</div>
				</div>
			</div>
		</div>

		<div class="ctc-save-section">
			<button type="submit" name="ctc_save_settings" class="ctc-btn ctc-btn-primary">
				<span class="dashicons dashicons-saved"></span>
				<?php esc_html_e( 'Save Settings', 'click-to-chat' ); ?>
			</button>
		</div>
	</form>
</div>
