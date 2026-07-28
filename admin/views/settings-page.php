<?php
/**
 * Settings page view.
 *
 * @package ClickToChat
 * @since 1.0.0
 */

if ( ! defined( 'WPINC' ) ) {
	die;
}

$settings         = get_option( 'ctc_chat_settings', array() );
$settings_helper  = new CTC_Chat_Settings();
$product_positions  = $settings_helper->get_product_position_options();
$shop_positions     = $settings_helper->get_shop_position_options();
$floating_positions = $settings_helper->get_floating_position_options();
$current_tab        = $this->get_current_settings_tab();
?>

<div class="ctc-settings-inline-notices">
	<?php
	$message = get_transient( 'ctc_chat_settings_message' );
	if ( 'success' === $message ) {
		delete_transient( 'ctc_chat_settings_message' );
		$this->render_admin_banner( __( 'Settings saved successfully.', 'aicoso-click-to-chat' ), 'success', true );
	}
	$this->render_settings_messages( 'ctc_chat_settings' );
	?>
</div>

<form method="post" action="" class="ctc-settings-form">
	<?php wp_nonce_field( 'ctc_chat_settings_nonce', 'ctc_chat_settings_nonce' ); ?>
	<input type="hidden" name="ctc_chat_current_tab" id="ctc_chat_current_tab" value="<?php echo esc_attr( $current_tab ); ?>">

	<div id="ctc-chat-settings-general" class="ctc-chat-settings-panel<?php echo 'general' === $current_tab ? ' active' : ''; ?>">
		<?php
		$this->render_settings_card_open(
			esc_html__( 'Plugin Status', 'aicoso-click-to-chat' ),
			array(
				'description' => esc_html__( 'Control whether Click to Chat is active across your store.', 'aicoso-click-to-chat' ),
				'icon'        => $this->get_settings_icon( 'config_general' ),
			)
		);
		?>
		<table class="form-table" role="presentation">
			<tr>
				<th scope="row"><?php esc_html_e( 'Enable plugin', 'aicoso-click-to-chat' ); ?></th>
				<td>
					<label for="ctc_chat_plugin_enabled">
						<input type="checkbox" name="ctc_chat_plugin_enabled" id="ctc_chat_plugin_enabled" value="1" <?php checked( isset( $settings['plugin_enabled'] ) ? $settings['plugin_enabled'] : true, true ); ?>>
						<?php esc_html_e( 'Enable Click to Chat plugin', 'aicoso-click-to-chat' ); ?>
					</label>
					<p class="description"><?php esc_html_e( 'When disabled, no WhatsApp buttons will be displayed on your website.', 'aicoso-click-to-chat' ); ?></p>
				</td>
			</tr>
		</table>
		<?php $this->render_settings_card_close(); ?>

		<?php
		$analytics = isset( $settings['analytics'] ) && is_array( $settings['analytics'] ) ? $settings['analytics'] : array();
		$this->render_settings_card_open(
			esc_html__( 'Click Analytics', 'aicoso-click-to-chat' ),
			array(
				'description' => esc_html__( 'First-party WhatsApp click tracking for Dashboard and Reports.', 'aicoso-click-to-chat' ),
				'icon'        => 'dashicons-chart-bar',
			)
		);
		?>
		<table class="form-table" role="presentation">
			<tr>
				<th scope="row"><?php esc_html_e( 'Enable tracking', 'aicoso-click-to-chat' ); ?></th>
				<td>
					<label for="ctc_chat_analytics_enabled">
						<input type="checkbox" name="ctc_chat_analytics[enabled]" id="ctc_chat_analytics_enabled" value="1" <?php checked( ! isset( $analytics['enabled'] ) || $analytics['enabled'] ); ?>>
						<?php esc_html_e( 'Record WhatsApp button clicks', 'aicoso-click-to-chat' ); ?>
					</label>
				</td>
			</tr>
			<tr>
				<th scope="row"><label for="ctc_chat_analytics_retention"><?php esc_html_e( 'Data retention (days)', 'aicoso-click-to-chat' ); ?></label></th>
				<td>
					<input type="number" min="30" max="730" step="1" name="ctc_chat_analytics[retention_days]" id="ctc_chat_analytics_retention" value="<?php echo esc_attr( isset( $analytics['retention_days'] ) ? absint( $analytics['retention_days'] ) : 365 ); ?>" class="small-text">
					<p class="description"><?php esc_html_e( 'Click events older than this are deleted automatically.', 'aicoso-click-to-chat' ); ?></p>
				</td>
			</tr>
			<tr>
				<th scope="row"><?php esc_html_e( 'Privacy', 'aicoso-click-to-chat' ); ?></th>
				<td>
					<label for="ctc_chat_analytics_track_ip">
						<input type="checkbox" name="ctc_chat_analytics[track_ip]" id="ctc_chat_analytics_track_ip" value="1" <?php checked( ! isset( $analytics['track_ip'] ) || $analytics['track_ip'] ); ?>>
						<?php esc_html_e( 'Store hashed IP address with each click', 'aicoso-click-to-chat' ); ?>
					</label>
					<br>
					<label for="ctc_chat_analytics_exclude_bots">
						<input type="checkbox" name="ctc_chat_analytics[exclude_bots]" id="ctc_chat_analytics_exclude_bots" value="1" <?php checked( ! isset( $analytics['exclude_bots'] ) || $analytics['exclude_bots'] ); ?>>
						<?php esc_html_e( 'Exclude known bots from tracking', 'aicoso-click-to-chat' ); ?>
					</label>
				</td>
			</tr>
		</table>
		<?php $this->render_settings_card_close(); ?>
	</div>

	<div id="ctc-chat-settings-button" class="ctc-chat-settings-panel<?php echo 'button' === $current_tab ? ' active' : ''; ?>">
		<?php
		$this->render_settings_card_open(
			esc_html__( 'Button Appearance', 'aicoso-click-to-chat' ),
			array(
				'description' => esc_html__( 'Customize the label, icon, and colors for WhatsApp buttons.', 'aicoso-click-to-chat' ),
				'icon'        => $this->get_settings_icon( 'config_button' ),
			)
		);
		?>
		<table class="form-table" role="presentation">
			<tr>
				<th scope="row">
					<label for="ctc_chat_button_text"><?php esc_html_e( 'Button Text', 'aicoso-click-to-chat' ); ?></label>
				</th>
				<td>
					<input type="text" name="ctc_chat_button[text]" id="ctc_chat_button_text" value="<?php echo esc_attr( isset( $settings['button_settings']['text'] ) ? $settings['button_settings']['text'] : esc_html__( 'Order via WhatsApp', 'aicoso-click-to-chat' ) ); ?>" class="regular-text">
					<p class="description"><?php esc_html_e( 'The text to display on the WhatsApp button.', 'aicoso-click-to-chat' ); ?></p>
				</td>
			</tr>
			<tr>
				<th scope="row"><?php esc_html_e( 'WhatsApp icon', 'aicoso-click-to-chat' ); ?></th>
				<td>
					<label for="ctc_chat_button_icon">
						<input type="checkbox" name="ctc_chat_button[icon]" id="ctc_chat_button_icon" value="1" <?php checked( isset( $settings['button_settings']['icon'] ) ? $settings['button_settings']['icon'] : true ); ?>>
						<?php esc_html_e( 'Show WhatsApp icon on the button', 'aicoso-click-to-chat' ); ?>
					</label>
				</td>
			</tr>
			<tr>
				<th scope="row">
					<label for="ctc_chat_button_bg_color"><?php esc_html_e( 'Background Color', 'aicoso-click-to-chat' ); ?></label>
				</th>
				<td>
					<input type="text" name="ctc_chat_button[bg_color]" id="ctc_chat_button_bg_color" value="<?php echo esc_attr( isset( $settings['button_settings']['bg_color'] ) ? $settings['button_settings']['bg_color'] : '#25D366' ); ?>" class="ctc-chat-color-field">
				</td>
			</tr>
			<tr>
				<th scope="row">
					<label for="ctc_chat_button_text_color"><?php esc_html_e( 'Text Color', 'aicoso-click-to-chat' ); ?></label>
				</th>
				<td>
					<input type="text" name="ctc_chat_button[text_color]" id="ctc_chat_button_text_color" value="<?php echo esc_attr( isset( $settings['button_settings']['text_color'] ) ? $settings['button_settings']['text_color'] : '#ffffff' ); ?>" class="ctc-chat-color-field">
				</td>
			</tr>
		</table>
		<?php $this->render_settings_card_close(); ?>
	</div>

	<div id="ctc-chat-settings-display" class="ctc-chat-settings-panel<?php echo 'display' === $current_tab ? ' active' : ''; ?>">
		<?php
		$this->render_settings_card_open(
			esc_html__( 'Single Product Pages', 'aicoso-click-to-chat' ),
			array(
				'description' => esc_html__( 'Show a WhatsApp button on individual product detail pages.', 'aicoso-click-to-chat' ),
				'icon'        => 'dashicons-products',
			)
		);
		?>
		<table class="form-table" role="presentation">
			<tr>
				<th scope="row"><?php esc_html_e( 'Show button', 'aicoso-click-to-chat' ); ?></th>
				<td>
					<label for="ctc_chat_single_product_enabled">
						<input type="checkbox" name="ctc_chat_single_product[enabled]" id="ctc_chat_single_product_enabled" value="1" <?php checked( isset( $settings['single_product']['enabled'] ) ? $settings['single_product']['enabled'] : true ); ?>>
						<?php esc_html_e( 'Show WhatsApp button on single product pages', 'aicoso-click-to-chat' ); ?>
					</label>
				</td>
			</tr>
			<tr class="ctc-chat-single-position-row">
				<th scope="row">
					<label for="ctc_chat_single_product_position"><?php esc_html_e( 'Button Position', 'aicoso-click-to-chat' ); ?></label>
				</th>
				<td>
					<select name="ctc_chat_single_product[position]" id="ctc_chat_single_product_position">
						<?php foreach ( $product_positions as $value => $label ) : ?>
							<option value="<?php echo esc_attr( $value ); ?>" <?php selected( isset( $settings['single_product']['position'] ) ? $settings['single_product']['position'] : 'after_add_to_cart', $value ); ?>><?php echo esc_html( $label ); ?></option>
						<?php endforeach; ?>
					</select>
				</td>
			</tr>
		</table>
		<?php $this->render_settings_card_close(); ?>

		<?php
		$this->render_settings_card_open(
			esc_html__( 'Shop Pages', 'aicoso-click-to-chat' ),
			array(
				'description' => esc_html__( 'Display buttons on shop, category, and archive listings.', 'aicoso-click-to-chat' ),
				'icon'        => 'dashicons-store',
			)
		);
		?>
		<table class="form-table" role="presentation">
			<tr>
				<th scope="row"><?php esc_html_e( 'Show button', 'aicoso-click-to-chat' ); ?></th>
				<td>
					<label for="ctc_shop_page_enabled">
						<input type="checkbox" name="ctc_chat_shop_page[enabled]" id="ctc_shop_page_enabled" value="1" <?php checked( isset( $settings['shop_page']['enabled'] ) ? $settings['shop_page']['enabled'] : false ); ?>>
						<?php esc_html_e( 'Show WhatsApp button on shop/archive pages', 'aicoso-click-to-chat' ); ?>
					</label>
				</td>
			</tr>
			<tr class="ctc-chat-shop-position-row<?php echo ( ! isset( $settings['shop_page']['enabled'] ) || ! $settings['shop_page']['enabled'] ) ? ' ctc-chat-hidden' : ''; ?>">
				<th scope="row">
					<label for="ctc_chat_shop_page_position"><?php esc_html_e( 'Button Position', 'aicoso-click-to-chat' ); ?></label>
				</th>
				<td>
					<select name="ctc_chat_shop_page[position]" id="ctc_chat_shop_page_position">
						<?php foreach ( $shop_positions as $value => $label ) : ?>
							<option value="<?php echo esc_attr( $value ); ?>" <?php selected( isset( $settings['shop_page']['position'] ) ? $settings['shop_page']['position'] : 'after_add_to_cart', $value ); ?>><?php echo esc_html( $label ); ?></option>
						<?php endforeach; ?>
					</select>
				</td>
			</tr>
		</table>
		<?php $this->render_settings_card_close(); ?>

		<?php
		$this->render_settings_card_open(
			esc_html__( 'Cart & Checkout Pages', 'aicoso-click-to-chat' ),
			array(
				'description' => esc_html__( 'Let shoppers continue the conversation during checkout.', 'aicoso-click-to-chat' ),
				'icon'        => 'dashicons-cart',
			)
		);
		?>
		<table class="form-table" role="presentation">
			<tr>
				<th scope="row"><?php esc_html_e( 'Cart page', 'aicoso-click-to-chat' ); ?></th>
				<td>
					<label for="ctc_chat_cart_page_enabled">
						<input type="checkbox" name="ctc_chat_cart_page[enabled]" id="ctc_chat_cart_page_enabled" value="1" <?php checked( isset( $settings['cart_page']['enabled'] ) ? $settings['cart_page']['enabled'] : false ); ?>>
						<?php esc_html_e( 'Show WhatsApp button on the cart page', 'aicoso-click-to-chat' ); ?>
					</label>
				</td>
			</tr>
			<tr class="ctc-chat-cart-position-row<?php echo ( ! isset( $settings['cart_page']['enabled'] ) || ! $settings['cart_page']['enabled'] ) ? ' ctc-chat-hidden' : ''; ?>">
				<th scope="row">
					<label for="ctc_chat_cart_page_position"><?php esc_html_e( 'Cart Button Position', 'aicoso-click-to-chat' ); ?></label>
				</th>
				<td>
					<select name="ctc_chat_cart_page[position]" id="ctc_chat_cart_page_position">
						<option value="after_cart_table" <?php selected( isset( $settings['cart_page']['position'] ) ? $settings['cart_page']['position'] : 'after_cart_table', 'after_cart_table' ); ?>><?php esc_html_e( 'After Cart Table', 'aicoso-click-to-chat' ); ?></option>
						<option value="before_cart_table" <?php selected( isset( $settings['cart_page']['position'] ) ? $settings['cart_page']['position'] : '', 'before_cart_table' ); ?>><?php esc_html_e( 'Before Cart Table', 'aicoso-click-to-chat' ); ?></option>
						<option value="proceed_to_checkout" <?php selected( isset( $settings['cart_page']['position'] ) ? $settings['cart_page']['position'] : '', 'proceed_to_checkout' ); ?>><?php esc_html_e( 'Next to Proceed to Checkout Button', 'aicoso-click-to-chat' ); ?></option>
						<option value="after_cart_totals" <?php selected( isset( $settings['cart_page']['position'] ) ? $settings['cart_page']['position'] : '', 'after_cart_totals' ); ?>><?php esc_html_e( 'After Cart Totals', 'aicoso-click-to-chat' ); ?></option>
						<option value="cart_actions" <?php selected( isset( $settings['cart_page']['position'] ) ? $settings['cart_page']['position'] : '', 'cart_actions' ); ?>><?php esc_html_e( 'In Cart Actions Area', 'aicoso-click-to-chat' ); ?></option>
					</select>
				</td>
			</tr>
			<tr>
				<th scope="row"><?php esc_html_e( 'Checkout page', 'aicoso-click-to-chat' ); ?></th>
				<td>
					<label for="ctc_chat_checkout_page_enabled">
						<input type="checkbox" name="ctc_chat_checkout_page[enabled]" id="ctc_chat_checkout_page_enabled" value="1" <?php checked( isset( $settings['checkout_page']['enabled'] ) ? $settings['checkout_page']['enabled'] : false ); ?>>
						<?php esc_html_e( 'Show WhatsApp button on the checkout page', 'aicoso-click-to-chat' ); ?>
					</label>
				</td>
			</tr>
			<tr class="ctc-chat-checkout-position-row<?php echo ( ! isset( $settings['checkout_page']['enabled'] ) || ! $settings['checkout_page']['enabled'] ) ? ' ctc-chat-hidden' : ''; ?>">
				<th scope="row">
					<label for="ctc_chat_checkout_page_position"><?php esc_html_e( 'Checkout Button Position', 'aicoso-click-to-chat' ); ?></label>
				</th>
				<td>
					<select name="ctc_chat_checkout_page[position]" id="ctc_chat_checkout_page_position">
						<option value="after_payment" <?php selected( isset( $settings['checkout_page']['position'] ) ? $settings['checkout_page']['position'] : 'after_payment', 'after_payment' ); ?>><?php esc_html_e( 'After Payment Methods', 'aicoso-click-to-chat' ); ?></option>
						<option value="before_payment" <?php selected( isset( $settings['checkout_page']['position'] ) ? $settings['checkout_page']['position'] : '', 'before_payment' ); ?>><?php esc_html_e( 'Before Payment Methods', 'aicoso-click-to-chat' ); ?></option>
						<option value="after_order_review" <?php selected( isset( $settings['checkout_page']['position'] ) ? $settings['checkout_page']['position'] : '', 'after_order_review' ); ?>><?php esc_html_e( 'After Order Review', 'aicoso-click-to-chat' ); ?></option>
						<option value="before_order_review" <?php selected( isset( $settings['checkout_page']['position'] ) ? $settings['checkout_page']['position'] : '', 'before_order_review' ); ?>><?php esc_html_e( 'Before Order Review', 'aicoso-click-to-chat' ); ?></option>
						<option value="after_submit" <?php selected( isset( $settings['checkout_page']['position'] ) ? $settings['checkout_page']['position'] : '', 'after_submit' ); ?>><?php esc_html_e( 'After Place Order Button', 'aicoso-click-to-chat' ); ?></option>
					</select>
				</td>
			</tr>
			<tr>
				<th scope="row"><?php esc_html_e( 'Thank you page', 'aicoso-click-to-chat' ); ?></th>
				<td>
					<label for="ctc_chat_thankyou_page_enabled">
						<input type="checkbox" name="ctc_chat_thankyou_page[enabled]" id="ctc_chat_thankyou_page_enabled" value="1" <?php checked( isset( $settings['thankyou_page']['enabled'] ) ? $settings['thankyou_page']['enabled'] : false ); ?>>
						<?php esc_html_e( 'Show WhatsApp button on the thank you/order confirmation page', 'aicoso-click-to-chat' ); ?>
					</label>
				</td>
			</tr>
		</table>
		<?php $this->render_settings_card_close(); ?>

		<?php
		$this->render_settings_card_open(
			esc_html__( 'Floating Button', 'aicoso-click-to-chat' ),
			array(
				'description' => esc_html__( 'Keep a persistent WhatsApp entry point visible on every page.', 'aicoso-click-to-chat' ),
				'icon'        => $this->get_settings_icon( 'template_floating' ),
			)
		);
		?>
		<table class="form-table" role="presentation">
			<tr>
				<th scope="row"><?php esc_html_e( 'Show button', 'aicoso-click-to-chat' ); ?></th>
				<td>
					<label for="ctc_chat_floating_button_enabled">
						<input type="checkbox" name="ctc_chat_floating_button[enabled]" id="ctc_chat_floating_button_enabled" value="1" <?php checked( isset( $settings['floating_button']['enabled'] ) ? $settings['floating_button']['enabled'] : false ); ?>>
						<?php esc_html_e( 'Show a floating WhatsApp button on all pages', 'aicoso-click-to-chat' ); ?>
					</label>
				</td>
			</tr>
			<tr>
				<th scope="row">
					<label for="ctc_chat_floating_button_position"><?php esc_html_e( 'Button Position', 'aicoso-click-to-chat' ); ?></label>
				</th>
				<td>
					<select name="ctc_chat_floating_button[position]" id="ctc_chat_floating_button_position">
						<?php foreach ( $floating_positions as $value => $label ) : ?>
							<option value="<?php echo esc_attr( $value ); ?>" <?php selected( isset( $settings['floating_button']['position'] ) ? $settings['floating_button']['position'] : 'bottom_right', $value ); ?>><?php echo esc_html( $label ); ?></option>
						<?php endforeach; ?>
					</select>
				</td>
			</tr>
		</table>
		<?php $this->render_settings_card_close(); ?>

		<?php
		$this->render_settings_card_open(
			esc_html__( 'Advanced Options', 'aicoso-click-to-chat' ),
			array(
				'description' => esc_html__( 'Catalog mode and WooCommerce purchase button overrides.', 'aicoso-click-to-chat' ),
				'icon'        => 'dashicons-shield',
				'class'       => 'ctc-settings-card--warning',
			)
		);
		?>
		<div class="notice notice-warning inline">
			<p><?php esc_html_e( 'Warning: The options below will hide WooCommerce purchase buttons. Only enable if you want to use WhatsApp as the primary contact method for orders.', 'aicoso-click-to-chat' ); ?></p>
		</div>
		<table class="form-table" role="presentation">
			<tr>
				<th scope="row"><?php esc_html_e( 'Hide Add to Cart', 'aicoso-click-to-chat' ); ?></th>
				<td>
					<label for="ctc_chat_hide_add_to_cart">
						<input type="checkbox" name="ctc_chat_advanced[hide_add_to_cart]" id="ctc_chat_hide_add_to_cart" value="1" <?php checked( isset( $settings['advanced']['hide_add_to_cart'] ) ? $settings['advanced']['hide_add_to_cart'] : false ); ?>>
						<?php esc_html_e( 'Hide "Add to Cart" buttons', 'aicoso-click-to-chat' ); ?>
					</label>
					<p class="description"><?php esc_html_e( 'Hides Add to Cart buttons on shop and product pages', 'aicoso-click-to-chat' ); ?></p>
				</td>
			</tr>
			<tr>
				<th scope="row"><?php esc_html_e( 'Hide Proceed to Checkout', 'aicoso-click-to-chat' ); ?></th>
				<td>
					<label for="ctc_chat_hide_proceed_checkout">
						<input type="checkbox" name="ctc_chat_advanced[hide_proceed_checkout]" id="ctc_chat_hide_proceed_checkout" value="1" <?php checked( isset( $settings['advanced']['hide_proceed_checkout'] ) ? $settings['advanced']['hide_proceed_checkout'] : false ); ?>>
						<?php esc_html_e( 'Hide "Proceed to Checkout" button', 'aicoso-click-to-chat' ); ?>
					</label>
					<p class="description"><?php esc_html_e( 'Hides the Proceed to Checkout button on the cart page', 'aicoso-click-to-chat' ); ?></p>
				</td>
			</tr>
			<tr>
				<th scope="row"><?php esc_html_e( 'Hide Place Order', 'aicoso-click-to-chat' ); ?></th>
				<td>
					<label for="ctc_chat_hide_place_order">
						<input type="checkbox" name="ctc_chat_advanced[hide_place_order]" id="ctc_chat_hide_place_order" value="1" <?php checked( isset( $settings['advanced']['hide_place_order'] ) ? $settings['advanced']['hide_place_order'] : false ); ?>>
						<?php esc_html_e( 'Hide "Place Order" button', 'aicoso-click-to-chat' ); ?>
					</label>
					<p class="description"><?php esc_html_e( 'Hides the Place Order button on the checkout page', 'aicoso-click-to-chat' ); ?></p>
				</td>
			</tr>
			<tr>
				<th scope="row"><?php esc_html_e( 'Catalog mode', 'aicoso-click-to-chat' ); ?></th>
				<td>
					<label for="ctc_chat_catalog_mode">
						<input type="checkbox" name="ctc_chat_advanced[catalog_mode]" id="ctc_chat_catalog_mode" value="1" <?php checked( isset( $settings['advanced']['catalog_mode'] ) ? $settings['advanced']['catalog_mode'] : false ); ?>>
						<?php esc_html_e( 'Enable catalog mode (hides all purchase buttons)', 'aicoso-click-to-chat' ); ?>
					</label>
					<p class="description"><?php esc_html_e( 'Turns your store into a catalog where customers must contact via WhatsApp to purchase', 'aicoso-click-to-chat' ); ?></p>
				</td>
			</tr>
		</table>
		<?php $this->render_settings_card_close(); ?>
	</div>

	<div id="ctc-chat-settings-exclusions" class="ctc-chat-settings-panel<?php echo 'exclusions' === $current_tab ? ' active' : ''; ?>">
		<?php
		$this->render_settings_card_open(
			esc_html__( 'Page & Post Exclusions', 'aicoso-click-to-chat' ),
			array(
				'description' => esc_html__( 'Hide WhatsApp buttons on selected pages and posts.', 'aicoso-click-to-chat' ),
				'icon'        => 'dashicons-admin-page',
			)
		);
		?>
		<table class="form-table" role="presentation">
			<tr>
				<th scope="row">
					<label for="ctc_chat_exclude_pages"><?php esc_html_e( 'Exclude Pages', 'aicoso-click-to-chat' ); ?></label>
				</th>
				<td>
					<select name="ctc_chat_exclusions[pages][]" id="ctc_chat_exclude_pages" class="ctc-chat-page-select" multiple="multiple">
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
					<p class="description"><?php esc_html_e( 'Select pages where the WhatsApp button should not appear.', 'aicoso-click-to-chat' ); ?></p>
				</td>
			</tr>
			<tr>
				<th scope="row">
					<label for="ctc_chat_exclude_posts"><?php esc_html_e( 'Exclude Posts', 'aicoso-click-to-chat' ); ?></label>
				</th>
				<td>
					<select name="ctc_chat_exclusions[posts][]" id="ctc_chat_exclude_posts" class="ctc-chat-post-select" multiple="multiple">
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
					<p class="description"><?php esc_html_e( 'Select posts where the WhatsApp button should not appear.', 'aicoso-click-to-chat' ); ?></p>
				</td>
			</tr>
		</table>
		<?php $this->render_settings_card_close(); ?>

		<?php
		$this->render_settings_card_open(
			esc_html__( 'Product Exclusions', 'aicoso-click-to-chat' ),
			array(
				'description' => esc_html__( 'Exclude products, categories, or tags from showing WhatsApp buttons.', 'aicoso-click-to-chat' ),
				'icon'        => $this->get_settings_icon( 'config_exclusions' ),
			)
		);
		?>
		<table class="form-table" role="presentation">
			<tr>
				<th scope="row">
					<label for="ctc_chat_exclude_categories"><?php esc_html_e( 'Exclude Product Categories', 'aicoso-click-to-chat' ); ?></label>
				</th>
				<td>
					<select name="ctc_chat_exclusions[categories][]" id="ctc_chat_exclude_categories" class="ctc-chat-category-select" multiple="multiple">
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
					<p class="description"><?php esc_html_e( 'Select product categories where the WhatsApp button should not appear.', 'aicoso-click-to-chat' ); ?></p>
				</td>
			</tr>
			<tr>
				<th scope="row">
					<label for="ctc_chat_exclude_tags"><?php esc_html_e( 'Exclude Product Tags', 'aicoso-click-to-chat' ); ?></label>
				</th>
				<td>
					<select name="ctc_chat_exclusions[tags][]" id="ctc_chat_exclude_tags" class="ctc-chat-tag-select" multiple="multiple">
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
					<p class="description"><?php esc_html_e( 'Select product tags where the WhatsApp button should not appear.', 'aicoso-click-to-chat' ); ?></p>
				</td>
			</tr>
			<tr>
				<th scope="row">
					<label for="ctc_chat_exclude_products"><?php esc_html_e( 'Exclude Products', 'aicoso-click-to-chat' ); ?></label>
				</th>
				<td>
					<select name="ctc_chat_exclusions[products][]" id="ctc_chat_exclude_products" class="ctc-chat-product-select" multiple="multiple">
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
					<p class="description"><?php esc_html_e( 'Select specific products where the WhatsApp button should not appear.', 'aicoso-click-to-chat' ); ?></p>
				</td>
			</tr>
		</table>
		<?php $this->render_settings_card_close(); ?>
	</div>

	<?php $this->render_settings_footer( 'ctc_chat_save_settings', esc_html__( 'Save Settings', 'aicoso-click-to-chat' ) ); ?>
</form>
