<?php
/**
 * WhatsApp numbers management page view.
 *
 * @package ClickToChat
 * @since 1.0.0
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

// Get plugin settings.
$settings = get_option( 'ctc_chat_settings', array() );

// Get WhatsApp numbers.
$whatsapp_numbers = isset( $settings['whatsapp_numbers'] ) ? ctc_chat_normalize_number_record_ids( $settings['whatsapp_numbers'] ) : array();

// Get next number ID.
$next_id = 1;
if ( ! empty( $whatsapp_numbers ) ) {
	$ids = array_column( $whatsapp_numbers, 'id' );
	$next_id = max( $ids ) + 1;
}
?>

<div class="ctc-settings-inline-notices">
	<?php $this->render_settings_messages( 'ctc_numbers' ); ?>
</div>

<div class="ctc-settings-toolbar">
	<button type="button" id="ctc-chat-add-number" class="button button-secondary">
		<span class="dashicons dashicons-plus-alt" aria-hidden="true"></span>
		<?php esc_html_e( 'Add New WhatsApp Number', 'aicoso-click-to-chat' ); ?>
	</button>
</div>

<form method="post" action="" class="ctc-settings-form">
		<?php wp_nonce_field( 'ctc_chat_numbers_nonce', 'ctc_chat_numbers_nonce' ); ?>
		<input type="hidden" id="ctc-chat-next-number-id" name="ctc_chat_next_number_id" value="<?php echo esc_attr( $next_id ); ?>" />
		<input type="hidden" id="ctc-chat-deleted-numbers" name="ctc_chat_deleted_numbers" value="" />

		<div class="ctc-chat-numbers-wrapper" id="ctc-chat-numbers-container">
			<?php if ( empty( $whatsapp_numbers ) ) : ?>
				<div class="ctc-chat-number-card" data-id="1">
					<div class="ctc-chat-number-header">
						<div class="ctc-chat-number-title-section">
							<span class="ctc-chat-number-icon dashicons <?php echo esc_attr( $this->get_settings_icon( 'number' ) ); ?>"></span>
							<h3 class="ctc-chat-number-title"><?php esc_html_e( 'Default Number', 'aicoso-click-to-chat' ); ?></h3>
						</div>
						<div class="ctc-chat-number-actions">
							<button type="button" class="button button-link ctc-chat-number-toggle" title="<?php esc_attr_e( 'Expand/Collapse', 'aicoso-click-to-chat' ); ?>" aria-expanded="true" aria-label="<?php esc_attr_e( 'Expand/Collapse', 'aicoso-click-to-chat' ); ?>">
								<span class="dashicons dashicons-arrow-up-alt2" aria-hidden="true"></span>
							</button>
							<button type="button" class="button button-link button-link-delete ctc-chat-number-delete" title="<?php esc_attr_e( 'Delete Number', 'aicoso-click-to-chat' ); ?>" aria-label="<?php esc_attr_e( 'Delete Number', 'aicoso-click-to-chat' ); ?>">
								<span class="dashicons dashicons-trash" aria-hidden="true"></span>
							</button>
						</div>
					</div>

					<div class="ctc-chat-number-body">
						<input type="hidden" name="ctc_chat_numbers[0][id]" value="1" />

						<div class="ctc-chat-fields-grid">
							<div class="ctc-chat-field-group">
								<label class="ctc-chat-field-label" for="ctc_numbers_name_1"><?php esc_html_e( 'Display Name', 'aicoso-click-to-chat' ); ?></label>
								<input type="text" name="ctc_numbers[0][name]" id="ctc_numbers_name_1" value="<?php esc_attr_e( 'Default Number', 'aicoso-click-to-chat' ); ?>" class="ctc-chat-input ctc-chat-number-name" required />
								<span class="ctc-chat-field-help"><?php esc_html_e( 'A name to identify this number (for admin use only)', 'aicoso-click-to-chat' ); ?></span>
							</div>

							<div class="ctc-chat-field-group">
								<label class="ctc-chat-field-label" for="ctc_numbers_1"><?php esc_html_e( 'WhatsApp Number', 'aicoso-click-to-chat' ); ?></label>
								<input type="text" name="ctc_numbers[0][number]" id="ctc_numbers_1" value="" class="ctc-chat-input" placeholder="+1234567890" required />
								<span class="ctc-chat-field-help"><?php esc_html_e( 'Include country code (e.g., +1 for USA)', 'aicoso-click-to-chat' ); ?></span>
							</div>
						</div>

						<div class="ctc-chat-field-group ctc-chat-full-width">
							<label class="ctc-chat-field-label" for="ctc_numbers_description_1"><?php esc_html_e( 'Description', 'aicoso-click-to-chat' ); ?></label>
							<textarea name="ctc_numbers[0][description]" id="ctc_numbers_description_1" rows="3" class="ctc-chat-textarea"></textarea>
							<span class="ctc-chat-field-help"><?php esc_html_e( 'Optional notes about this number or the person/department it belongs to', 'aicoso-click-to-chat' ); ?></span>
						</div>

						<!-- Default Number Checkbox -->
						<div class="ctc-chat-default-section">
							<label class="ctc-chat-checkbox-option">
								<input type="checkbox" name="ctc_numbers[0][is_default]" id="ctc_numbers_default_1" value="1" class="ctc-chat-default-checkbox" />
								<span><?php esc_html_e( 'Use as Default Number', 'aicoso-click-to-chat' ); ?></span>
							</label>
							<span class="ctc-chat-field-help"><?php esc_html_e( 'This number will be used as fallback for pages/products without specific assignments', 'aicoso-click-to-chat' ); ?></span>
						</div>

						<!-- Assignments Section -->
						<div class="ctc-chat-assignments-section">
								<h4 class="ctc-chat-section-title">
								<span class="dashicons <?php echo esc_attr( $this->get_settings_icon( 'assignments' ) ); ?>"></span>
								<?php esc_html_e( 'Number Assignments', 'aicoso-click-to-chat' ); ?>
							</h4>

							<div class="ctc-chat-assignments-grid">
								<div class="ctc-chat-assignment-group">
									<label class="ctc-chat-field-label">
										<span class="ctc-chat-assignment-icon dashicons <?php echo esc_attr( $this->get_settings_icon( 'assign_products' ) ); ?>"></span>
										<?php esc_html_e( 'Products', 'aicoso-click-to-chat' ); ?>
									</label>
										<select name="ctc_numbers[0][assignments][products][]" class="ctc-chat-product-select" multiple="multiple" data-placeholder="<?php esc_attr_e( 'Select products...', 'aicoso-click-to-chat' ); ?>">
									</select>
								</div>

								<div class="ctc-chat-assignment-group">
									<label class="ctc-chat-field-label">
										<span class="ctc-chat-assignment-icon dashicons <?php echo esc_attr( $this->get_settings_icon( 'assign_categories' ) ); ?>"></span>
											<?php esc_html_e( 'Categories', 'aicoso-click-to-chat' ); ?>
									</label>
										<select name="ctc_numbers[0][assignments][categories][]" class="ctc-chat-category-select" multiple="multiple" data-placeholder="<?php esc_attr_e( 'Select categories...', 'aicoso-click-to-chat' ); ?>">
									</select>
								</div>

								<div class="ctc-chat-assignment-group">
									<label class="ctc-chat-field-label">
										<span class="ctc-chat-assignment-icon dashicons <?php echo esc_attr( $this->get_settings_icon( 'assign_pages' ) ); ?>"></span>
											<?php esc_html_e( 'Pages', 'aicoso-click-to-chat' ); ?>
									</label>
										<select name="ctc_numbers[0][assignments][pages][]" class="ctc-chat-page-select" multiple="multiple" data-placeholder="<?php esc_attr_e( 'Select pages...', 'aicoso-click-to-chat' ); ?>">
									</select>
								</div>
							</div>
						</div>
					</div>
				</div>
			<?php else : ?>
				<?php foreach ( $whatsapp_numbers as $index => $number ) : ?>
					<div class="ctc-chat-number-card" data-id="<?php echo esc_attr( $number['id'] ); ?>">
						<div class="ctc-chat-number-header">
							<div class="ctc-chat-number-title-section">
								<span class="ctc-chat-number-icon dashicons <?php echo esc_attr( $this->get_settings_icon( 'number' ) ); ?>"></span>
								<h3 class="ctc-chat-number-title"><?php echo esc_html( $number['name'] ); ?></h3>
							</div>
							<div class="ctc-chat-number-actions">
								<button type="button" class="button button-link ctc-chat-number-toggle" title="<?php esc_attr_e( 'Expand/Collapse', 'aicoso-click-to-chat' ); ?>" aria-expanded="true" aria-label="<?php esc_attr_e( 'Expand/Collapse', 'aicoso-click-to-chat' ); ?>">
									<span class="dashicons dashicons-arrow-up-alt2" aria-hidden="true"></span>
								</button>
								<button type="button" class="button button-link button-link-delete ctc-chat-number-delete" title="<?php esc_attr_e( 'Delete Number', 'aicoso-click-to-chat' ); ?>" aria-label="<?php esc_attr_e( 'Delete Number', 'aicoso-click-to-chat' ); ?>">
									<span class="dashicons dashicons-trash" aria-hidden="true"></span>
								</button>
							</div>
						</div>

						<div class="ctc-chat-number-body">
							<input type="hidden" name="ctc_chat_numbers[<?php echo esc_attr( $index ); ?>][id]" value="<?php echo esc_attr( $number['id'] ); ?>" />

							<div class="ctc-chat-fields-grid">
								<div class="ctc-chat-field-group">
									<label class="ctc-chat-field-label" for="ctc_numbers_name_<?php echo esc_attr( $number['id'] ); ?>"><?php esc_html_e( 'Display Name', 'aicoso-click-to-chat' ); ?></label>
									<input type="text" name="ctc_numbers[<?php echo esc_attr( $index ); ?>][name]" id="ctc_numbers_name_<?php echo esc_attr( $number['id'] ); ?>" value="<?php echo esc_attr( $number['name'] ); ?>" class="ctc-chat-input ctc-chat-number-name" required />
									<span class="ctc-chat-field-help"><?php esc_html_e( 'A name to identify this number (for admin use only)', 'aicoso-click-to-chat' ); ?></span>
								</div>

								<div class="ctc-chat-field-group">
									<label class="ctc-chat-field-label" for="ctc_numbers_<?php echo esc_attr( $number['id'] ); ?>"><?php esc_html_e( 'WhatsApp Number', 'aicoso-click-to-chat' ); ?></label>
									<input type="text" name="ctc_numbers[<?php echo esc_attr( $index ); ?>][number]" id="ctc_numbers_<?php echo esc_attr( $number['id'] ); ?>" value="<?php echo esc_attr( $number['number'] ); ?>" class="ctc-chat-input" placeholder="+1234567890" required />
									<span class="ctc-chat-field-help"><?php esc_html_e( 'Include country code (e.g., +1 for USA)', 'aicoso-click-to-chat' ); ?></span>
								</div>
							</div>

							<div class="ctc-chat-field-group ctc-chat-full-width">
								<label class="ctc-chat-field-label" for="ctc_numbers_description_<?php echo esc_attr( $number['id'] ); ?>"><?php esc_html_e( 'Description', 'aicoso-click-to-chat' ); ?></label>
								<textarea name="ctc_numbers[<?php echo esc_attr( $index ); ?>][description]" id="ctc_numbers_description_<?php echo esc_attr( $number['id'] ); ?>" rows="3" class="ctc-chat-textarea"><?php echo esc_textarea( isset( $number['description'] ) ? $number['description'] : '' ); ?></textarea>
								<span class="ctc-chat-field-help"><?php esc_html_e( 'Optional notes about this number or the person/department it belongs to', 'aicoso-click-to-chat' ); ?></span>
							</div>

							<!-- Default Number Checkbox -->
							<div class="ctc-chat-default-section">
								<label class="ctc-chat-checkbox-option">
									<input type="checkbox" name="ctc_numbers[<?php echo esc_attr( $index ); ?>][is_default]" id="ctc_numbers_default_<?php echo esc_attr( $number['id'] ); ?>" value="1" <?php checked( ! empty( $number['is_default'] ) ); ?> class="ctc-chat-default-checkbox" />
									<span><?php esc_html_e( 'Use as Default Number', 'aicoso-click-to-chat' ); ?></span>
								</label>
								<span class="ctc-chat-field-help"><?php esc_html_e( 'This number will be used as fallback for pages/products without specific assignments', 'aicoso-click-to-chat' ); ?></span>
							</div>

							<!-- Assignments Section -->
							<div class="ctc-chat-assignments-section">
								<h4 class="ctc-chat-section-title">
									<span class="dashicons <?php echo esc_attr( $this->get_settings_icon( 'assignments' ) ); ?>"></span>
									<?php esc_html_e( 'Number Assignments', 'aicoso-click-to-chat' ); ?>
								</h4>

								<div class="ctc-chat-assignments-grid">
									<div class="ctc-chat-field-group">
										<label class="ctc-chat-field-label">
											<span class="dashicons <?php echo esc_attr( $this->get_settings_icon( 'assign_products' ) ); ?>"></span>
											<?php esc_html_e( 'Products', 'aicoso-click-to-chat' ); ?>
										</label>
											<select name="ctc_numbers[<?php echo esc_attr( $index ); ?>][assignments][products][]" class="ctc-chat-product-select" multiple="multiple" data-placeholder="<?php esc_attr_e( 'Select products...', 'aicoso-click-to-chat' ); ?>">
										<?php
										// Show selected products.
										if ( isset( $number['assignments']['products'] ) && is_array( $number['assignments']['products'] ) ) {
											foreach ( $number['assignments']['products'] as $product_id ) {
												$product = wc_get_product( $product_id );
												if ( $product ) {
													echo '<option value="' . esc_attr( $product_id ) . '" selected>' . esc_html( $product->get_name() ) . '</option>';
												}
											}
										}
										?>
										</select>
									</div>

									<div class="ctc-chat-field-group">
										<label class="ctc-chat-field-label">
											<span class="dashicons <?php echo esc_attr( $this->get_settings_icon( 'assign_categories' ) ); ?>"></span>
											<?php esc_html_e( 'Categories', 'aicoso-click-to-chat' ); ?>
										</label>
											<select name="ctc_numbers[<?php echo esc_attr( $index ); ?>][assignments][categories][]" class="ctc-chat-category-select" multiple="multiple" data-placeholder="<?php esc_attr_e( 'Select categories...', 'aicoso-click-to-chat' ); ?>">
										<?php
										// Show selected categories.
										if ( isset( $number['assignments']['categories'] ) && is_array( $number['assignments']['categories'] ) ) {
											foreach ( $number['assignments']['categories'] as $term_id ) {
												$category_term = get_term( $term_id, 'product_cat' );
												if ( $category_term && ! is_wp_error( $category_term ) ) {
													echo '<option value="' . esc_attr( $category_term->term_id ) . '" selected>' . esc_html( $category_term->name ) . '</option>';
												}
											}
										}
										?>
										</select>
									</div>

									<div class="ctc-chat-field-group">
										<label class="ctc-chat-field-label">
											<span class="dashicons <?php echo esc_attr( $this->get_settings_icon( 'assign_pages' ) ); ?>"></span>
											<?php esc_html_e( 'Pages', 'aicoso-click-to-chat' ); ?>
										</label>
											<select name="ctc_numbers[<?php echo esc_attr( $index ); ?>][assignments][pages][]" class="ctc-chat-page-select" multiple="multiple" data-placeholder="<?php esc_attr_e( 'Select pages...', 'aicoso-click-to-chat' ); ?>">
										<?php
										// Show selected pages.
										if ( isset( $number['assignments']['pages'] ) && is_array( $number['assignments']['pages'] ) ) {
											foreach ( $number['assignments']['pages'] as $page_id ) {
												$page_title = get_the_title( $page_id );
												if ( $page_title ) {
													echo '<option value="' . esc_attr( $page_id ) . '" selected>' . esc_html( $page_title ) . '</option>';
												}
											}
										}
										?>
										</select>
									</div>
								</div>
							</div>
						</div>
					</div>
				<?php endforeach; ?>
			<?php endif; ?>
		</div>

		<?php $this->render_settings_footer( 'ctc_chat_save_numbers', esc_html__( 'Save All Numbers', 'aicoso-click-to-chat' ) ); ?>
	</form>

	<!-- Number Item Template (for JavaScript) -->
	<script type="text/html" id="tmpl-ctc-chat-number-template">
		<div class="ctc-chat-number-card" data-id="{{ data.id }}">
			<div class="ctc-chat-number-header">
				<div class="ctc-chat-number-title-section">
					<span class="ctc-chat-number-icon dashicons <?php echo esc_attr( $this->get_settings_icon( 'number' ) ); ?>"></span>
					<h3 class="ctc-chat-number-title"><?php esc_html_e( 'New WhatsApp Number', 'aicoso-click-to-chat' ); ?></h3>
				</div>
				<div class="ctc-chat-number-actions">
					<button type="button" class="button button-link ctc-chat-number-toggle" title="<?php esc_attr_e( 'Expand/Collapse', 'aicoso-click-to-chat' ); ?>" aria-expanded="true" aria-label="<?php esc_attr_e( 'Expand/Collapse', 'aicoso-click-to-chat' ); ?>">
						<span class="dashicons dashicons-arrow-up-alt2" aria-hidden="true"></span>
					</button>
					<button type="button" class="button button-link button-link-delete ctc-chat-number-delete" title="<?php esc_attr_e( 'Delete Number', 'aicoso-click-to-chat' ); ?>" aria-label="<?php esc_attr_e( 'Delete Number', 'aicoso-click-to-chat' ); ?>">
						<span class="dashicons dashicons-trash" aria-hidden="true"></span>
					</button>
				</div>
			</div>

			<div class="ctc-chat-number-body">
				<input type="hidden" name="ctc_numbers[{{ data.index }}][id]" value="{{ data.id }}" />

				<div class="ctc-chat-fields-grid">
					<div class="ctc-chat-field-group">
						<label class="ctc-chat-field-label" for="ctc_numbers_name_{{ data.id }}"><?php esc_html_e( 'Display Name', 'aicoso-click-to-chat' ); ?></label>
						<input type="text" name="ctc_numbers[{{ data.index }}][name]" id="ctc_numbers_name_{{ data.id }}" value="" class="ctc-chat-input ctc-chat-number-name" required />
						<span class="ctc-chat-field-help"><?php esc_html_e( 'A name to identify this number (for admin use only)', 'aicoso-click-to-chat' ); ?></span>
					</div>

					<div class="ctc-chat-field-group">
						<label class="ctc-chat-field-label" for="ctc_numbers_{{ data.id }}"><?php esc_html_e( 'WhatsApp Number', 'aicoso-click-to-chat' ); ?></label>
						<input type="text" name="ctc_numbers[{{ data.index }}][number]" id="ctc_numbers_{{ data.id }}" value="" class="ctc-chat-input" placeholder="+1234567890" required />
						<span class="ctc-chat-field-help"><?php esc_html_e( 'Include country code (e.g., +1 for USA)', 'aicoso-click-to-chat' ); ?></span>
					</div>
				</div>

				<div class="ctc-chat-field-group ctc-chat-full-width">
					<label class="ctc-chat-field-label" for="ctc_numbers_description_{{ data.id }}"><?php esc_html_e( 'Description', 'aicoso-click-to-chat' ); ?></label>
					<textarea name="ctc_numbers[{{ data.index }}][description]" id="ctc_numbers_description_{{ data.id }}" rows="3" class="ctc-chat-textarea"></textarea>
					<span class="ctc-chat-field-help"><?php esc_html_e( 'Optional notes about this number or the person/department it belongs to', 'aicoso-click-to-chat' ); ?></span>
				</div>

				<!-- Default Number Checkbox -->
				<div class="ctc-chat-default-section">
					<label class="ctc-chat-checkbox-option">
						<input type="checkbox" name="ctc_numbers[{{ data.index }}][is_default]" id="ctc_numbers_default_{{ data.id }}" value="1" class="ctc-chat-default-checkbox" />
						<span><?php esc_html_e( 'Use as Default Number', 'aicoso-click-to-chat' ); ?></span>
					</label>
					<span class="ctc-chat-field-help"><?php esc_html_e( 'This number will be used as fallback for pages/products without specific assignments', 'aicoso-click-to-chat' ); ?></span>
				</div>

				<!-- Assignments Section -->
				<div class="ctc-chat-assignments-section">
					<h4 class="ctc-chat-section-title">
						<span class="dashicons <?php echo esc_attr( $this->get_settings_icon( 'assignments' ) ); ?>"></span>
						<?php esc_html_e( 'Number Assignments', 'aicoso-click-to-chat' ); ?>
					</h4>

					<div class="ctc-chat-assignments-grid">
						<div class="ctc-chat-field-group">
							<label class="ctc-chat-field-label">
								<span class="dashicons <?php echo esc_attr( $this->get_settings_icon( 'assign_products' ) ); ?>"></span>
								<?php esc_html_e( 'Products', 'aicoso-click-to-chat' ); ?>
							</label>
							<select name="ctc_numbers[{{ data.index }}][assignments][products][]" class="ctc-chat-product-select" multiple="multiple" data-placeholder="<?php esc_attr_e( 'Select products...', 'aicoso-click-to-chat' ); ?>"></select>
						</div>

						<div class="ctc-chat-field-group">
							<label class="ctc-chat-field-label">
								<span class="dashicons <?php echo esc_attr( $this->get_settings_icon( 'assign_categories' ) ); ?>"></span>
								<?php esc_html_e( 'Categories', 'aicoso-click-to-chat' ); ?>
							</label>
							<select name="ctc_numbers[{{ data.index }}][assignments][categories][]" class="ctc-chat-category-select" multiple="multiple" data-placeholder="<?php esc_attr_e( 'Select categories...', 'aicoso-click-to-chat' ); ?>"></select>
						</div>

						<div class="ctc-chat-field-group">
							<label class="ctc-chat-field-label">
								<span class="dashicons <?php echo esc_attr( $this->get_settings_icon( 'assign_pages' ) ); ?>"></span>
								<?php esc_html_e( 'Pages', 'aicoso-click-to-chat' ); ?>
							</label>
							<select name="ctc_numbers[{{ data.index }}][assignments][pages][]" class="ctc-chat-page-select" multiple="multiple" data-placeholder="<?php esc_attr_e( 'Select pages...', 'aicoso-click-to-chat' ); ?>"></select>
						</div>
					</div>
				</div>
			</div>
		</div>
	</script>
