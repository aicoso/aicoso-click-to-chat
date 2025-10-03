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

// Get plugin settings
$settings = get_option( 'ctc_settings', array() );

// Get WhatsApp numbers
$whatsapp_numbers = isset( $settings['whatsapp_numbers'] ) ? $settings['whatsapp_numbers'] : array();

// Get next number ID
$next_id = 1;
if ( ! empty( $whatsapp_numbers ) ) {
    $ids = array_column( $whatsapp_numbers, 'id' );
    $next_id = max( $ids ) + 1;
}
?>

<div class="wrap ctc-admin-container">
    <div class="ctc-admin-header">
        <span class="ctc-admin-logo dashicons dashicons-whatsapp"></span>
        <h1 class="ctc-admin-heading"><?php esc_html_e( 'WhatsApp Numbers', 'click-to-chat' ); ?></h1>
    </div>

    <!-- Instructions Banner -->
    <div class="ctc-instructions-banner">
        <h2><span class="dashicons dashicons-megaphone"></span> <?php esc_html_e( 'Manage Your WhatsApp Support Team', 'click-to-chat' ); ?></h2>
        <p><?php esc_html_e( 'Add multiple WhatsApp numbers and assign them to specific products, categories, or pages for targeted customer support.', 'click-to-chat' ); ?></p>
        <div class="ctc-quick-steps">
            <div class="ctc-step">
                <span class="ctc-step-number">1</span>
                <span><?php esc_html_e( 'Add WhatsApp numbers', 'click-to-chat' ); ?></span>
            </div>
            <div class="ctc-step">
                <span class="ctc-step-number">2</span>
                <span><?php esc_html_e( 'Assign to products/categories', 'click-to-chat' ); ?></span>
            </div>
            <div class="ctc-step">
                <span class="ctc-step-number">3</span>
                <span><?php esc_html_e( 'Set a default fallback number', 'click-to-chat' ); ?></span>
            </div>
        </div>
    </div>

    <div class="ctc-add-number-section">
        <button type="button" id="ctc-add-number" class="ctc-add-btn">
            <span class="dashicons dashicons-plus-alt"></span>
            <?php esc_html_e( 'Add New WhatsApp Number', 'click-to-chat' ); ?>
        </button>
    </div>
    
    <form method="post" action="">
        <?php wp_nonce_field( 'ctc_numbers_nonce', 'ctc_numbers_nonce' ); ?>
        <input type="hidden" id="ctc-next-number-id" name="ctc_next_number_id" value="<?php echo esc_attr( $next_id ); ?>" />
        <input type="hidden" id="ctc-deleted-numbers" name="ctc_deleted_numbers" value="" />
        
        <div class="ctc-numbers-wrapper" id="ctc-numbers-container">
            <?php if ( empty( $whatsapp_numbers ) ) : ?>
                <div class="ctc-number-card" data-id="1">
                    <div class="ctc-number-header">
                        <div class="ctc-number-title-section">
                            <span class="ctc-number-icon dashicons dashicons-phone"></span>
                            <h3 class="ctc-number-title"><?php esc_html_e( 'Default Number', 'click-to-chat' ); ?></h3>
                        </div>
                        <div class="ctc-number-actions">
                            <button type="button" class="ctc-number-toggle button-link" title="<?php esc_attr_e( 'Expand/Collapse', 'click-to-chat' ); ?>">
                                <span class="dashicons dashicons-arrow-up-alt2"></span>
                            </button>
                            <button type="button" class="ctc-number-delete button-link" title="<?php esc_attr_e( 'Delete Number', 'click-to-chat' ); ?>">
                                <span class="dashicons dashicons-trash"></span>
                            </button>
                        </div>
                    </div>
                    
                    <div class="ctc-number-body">
                        <input type="hidden" name="ctc_numbers[0][id]" value="1" />

                        <div class="ctc-fields-grid">
                            <div class="ctc-field-group">
                                <label class="ctc-field-label" for="ctc_numbers_name_1"><?php esc_html_e( 'Display Name', 'click-to-chat' ); ?></label>
                                <input type="text" name="ctc_numbers[0][name]" id="ctc_numbers_name_1" value="<?php esc_attr_e( 'Default Number', 'click-to-chat' ); ?>" class="ctc-input ctc-number-name" required />
                                <span class="ctc-field-help"><?php esc_html_e( 'A name to identify this number (for admin use only)', 'click-to-chat' ); ?></span>
                            </div>

                            <div class="ctc-field-group">
                                <label class="ctc-field-label" for="ctc_numbers_1"><?php esc_html_e( 'WhatsApp Number', 'click-to-chat' ); ?></label>
                                <input type="text" name="ctc_numbers[0][number]" id="ctc_numbers_1" value="" class="ctc-input" placeholder="+1234567890" required />
                                <span class="ctc-field-help"><?php esc_html_e( 'Include country code (e.g., +1 for USA)', 'click-to-chat' ); ?></span>
                            </div>
                        </div>
                        
                        <div class="ctc-field-group ctc-full-width">
                            <label class="ctc-field-label" for="ctc_numbers_description_1"><?php esc_html_e( 'Description', 'click-to-chat' ); ?></label>
                            <textarea name="ctc_numbers[0][description]" id="ctc_numbers_description_1" rows="3" class="ctc-textarea"></textarea>
                            <span class="ctc-field-help"><?php esc_html_e( 'Optional notes about this number or the person/department it belongs to', 'click-to-chat' ); ?></span>
                        </div>

                        <!-- Default Number Checkbox -->
                        <div class="ctc-default-section">
                            <label class="ctc-checkbox-option">
                                <input type="checkbox" name="ctc_numbers[0][is_default]" id="ctc_numbers_default_1" value="1" class="ctc-default-checkbox" />
                                <span><?php esc_html_e( 'Use as Default Number', 'click-to-chat' ); ?></span>
                            </label>
                            <span class="ctc-field-help"><?php esc_html_e( 'This number will be used as fallback for pages/products without specific assignments', 'click-to-chat' ); ?></span>
                        </div>

                        <!-- Assignments Section -->
                        <div class="ctc-assignments-section">
                            <h4 class="ctc-section-title">
                                <span class="dashicons dashicons-admin-links"></span>
                                <?php esc_html_e( 'Number Assignments', 'click-to-chat' ); ?>
                            </h4>
                            
                            <div class="ctc-assignments-grid">
                                <div class="ctc-assignment-group">
                                    <label class="ctc-field-label">
                                        <span class="ctc-assignment-icon dashicons dashicons-cart"></span>
                                        <?php esc_html_e( 'Products', 'click-to-chat' ); ?>
                                    </label>
                                    <select name="ctc_numbers[0][assignments][products][]" class="ctc-product-select" multiple="multiple" data-placeholder="<?php esc_attr_e( 'Select products...', 'click-to-chat' ); ?>">
                                    </select>
                                </div>

                                <div class="ctc-assignment-group">
                                    <label class="ctc-field-label">
                                        <span class="ctc-assignment-icon dashicons dashicons-category"></span>
                                        <?php esc_html_e( 'Categories', 'click-to-chat' ); ?>
                                    </label>
                                    <select name="ctc_numbers[0][assignments][categories][]" class="ctc-category-select" multiple="multiple" data-placeholder="<?php esc_attr_e( 'Select categories...', 'click-to-chat' ); ?>">
                                    </select>
                                </div>

                                <div class="ctc-assignment-group">
                                    <label class="ctc-field-label">
                                        <span class="ctc-assignment-icon dashicons dashicons-admin-page"></span>
                                        <?php esc_html_e( 'Pages', 'click-to-chat' ); ?>
                                    </label>
                                    <select name="ctc_numbers[0][assignments][pages][]" class="ctc-page-select" multiple="multiple" data-placeholder="<?php esc_attr_e( 'Select pages...', 'click-to-chat' ); ?>">
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php else : ?>
                <?php foreach ( $whatsapp_numbers as $index => $number ) : ?>
                    <div class="ctc-number-card" data-id="<?php echo esc_attr( $number['id'] ); ?>">
                        <div class="ctc-number-header">
                            <div class="ctc-number-title-section">
                                <span class="ctc-number-icon dashicons dashicons-phone"></span>
                                <h3 class="ctc-number-title"><?php echo esc_html( $number['name'] ); ?></h3>
                            </div>
                            <div class="ctc-number-actions">
                                <button type="button" class="ctc-number-toggle button-link" title="<?php esc_attr_e( 'Expand/Collapse', 'click-to-chat' ); ?>">
                                    <span class="dashicons dashicons-arrow-up-alt2"></span>
                                </button>
                                <button type="button" class="ctc-number-delete button-link" title="<?php esc_attr_e( 'Delete Number', 'click-to-chat' ); ?>">
                                    <span class="dashicons dashicons-trash"></span>
                                </button>
                            </div>
                        </div>
                        
                        <div class="ctc-number-body">
                            <input type="hidden" name="ctc_numbers[<?php echo esc_attr( $index ); ?>][id]" value="<?php echo esc_attr( $number['id'] ); ?>" />
                            
                            <div class="ctc-field-row">
                                <div class="ctc-field-col">
                                    <label for="ctc_numbers_name_<?php echo esc_attr( $number['id'] ); ?>"><?php esc_html_e( 'Name:', 'click-to-chat' ); ?></label>
                                    <input type="text" name="ctc_numbers[<?php echo esc_attr( $index ); ?>][name]" id="ctc_numbers_name_<?php echo esc_attr( $number['id'] ); ?>" value="<?php echo esc_attr( $number['name'] ); ?>" class="regular-text ctc-number-name" required />
                                    <span class="description"><?php esc_html_e( 'A name to identify this number (for admin use only).', 'click-to-chat' ); ?></span>
                                </div>
                                
                                <div class="ctc-field-col">
                                    <label for="ctc_numbers_<?php echo esc_attr( $number['id'] ); ?>"><?php esc_html_e( 'WhatsApp Number:', 'click-to-chat' ); ?></label>
                                    <input type="text" name="ctc_numbers[<?php echo esc_attr( $index ); ?>][number]" id="ctc_numbers_<?php echo esc_attr( $number['id'] ); ?>" value="<?php echo esc_attr( $number['number'] ); ?>" class="regular-text" placeholder="123456789012" required />
                                    <span class="description"><?php esc_html_e( 'Enter the WhatsApp number with country code, no spaces or special characters.', 'click-to-chat' ); ?></span>
                                </div>
                            </div>
                            
                            <p>
                                <label for="ctc_numbers_description_<?php echo esc_attr( $number['id'] ); ?>"><?php esc_html_e( 'Description:', 'click-to-chat' ); ?></label>
                                <textarea name="ctc_numbers[<?php echo esc_attr( $index ); ?>][description]" id="ctc_numbers_description_<?php echo esc_attr( $number['id'] ); ?>" rows="3" class="large-text"><?php echo esc_textarea( $number['description'] ); ?></textarea>
                                <span class="description"><?php esc_html_e( 'Optional description or notes about this number.', 'click-to-chat' ); ?></span>
                            </p>

                            <!-- Default Number Checkbox -->
                            <p>
                                <label for="ctc_numbers_default_<?php echo esc_attr( $number['id'] ); ?>">
                                    <input type="checkbox" name="ctc_numbers[<?php echo esc_attr( $index ); ?>][is_default]" id="ctc_numbers_default_<?php echo esc_attr( $number['id'] ); ?>" value="1" <?php checked( ! empty( $number['is_default'] ) ); ?> class="ctc-default-checkbox" />
                                    <?php esc_html_e( 'Use as Default Number', 'click-to-chat' ); ?>
                                </label>
                                <span class="description"><?php esc_html_e( 'This number will be used as fallback for pages/products without specific assignments.', 'click-to-chat' ); ?></span>
                            </p>

                            <!-- Assignments Section -->
                            <div class="ctc-assignments-section">
                                <h4 class="ctc-assignments-heading"><?php esc_html_e( 'Assign this WhatsApp Number to:', 'click-to-chat' ); ?></h4>
                                
                                <div class="ctc-assignment-type">
                                    <label class="ctc-assignment-label"><?php esc_html_e( 'Products:', 'click-to-chat' ); ?></label>
                                    <select name="ctc_numbers[<?php echo esc_attr( $index ); ?>][assignments][products][]" class="ctc-product-select" multiple="multiple" data-placeholder="<?php esc_attr_e( 'Select products...', 'click-to-chat' ); ?>">
                                        <?php
                                        // Show selected products
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
                                
                                <div class="ctc-assignment-type">
                                    <label class="ctc-assignment-label"><?php esc_html_e( 'Product Categories:', 'click-to-chat' ); ?></label>
                                    <select name="ctc_numbers[<?php echo esc_attr( $index ); ?>][assignments][categories][]" class="ctc-category-select" multiple="multiple" data-placeholder="<?php esc_attr_e( 'Select categories...', 'click-to-chat' ); ?>">
                                        <?php
                                        // Show selected categories
                                        if ( isset( $number['assignments']['categories'] ) && is_array( $number['assignments']['categories'] ) ) {
                                            foreach ( $number['assignments']['categories'] as $term_id ) {
                                                $term = get_term( $term_id, 'product_cat' );
                                                if ( $term && ! is_wp_error( $term ) ) {
                                                    echo '<option value="' . esc_attr( $term->term_id ) . '" selected>' . esc_html( $term->name ) . '</option>';
                                                }
                                            }
                                        }
                                        ?>
                                    </select>
                                </div>
                                
                                <div class="ctc-assignment-type">
                                    <label class="ctc-assignment-label"><?php esc_html_e( 'Pages:', 'click-to-chat' ); ?></label>
                                    <select name="ctc_numbers[<?php echo esc_attr( $index ); ?>][assignments][pages][]" class="ctc-page-select" multiple="multiple" data-placeholder="<?php esc_attr_e( 'Select pages...', 'click-to-chat' ); ?>">
                                        <?php
                                        // Show selected pages
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
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
        
        <div class="ctc-save-section">
            <button type="submit" name="ctc_save_numbers" class="ctc-save-btn">
                <span class="dashicons dashicons-saved"></span>
                <?php esc_html_e( 'Save All Numbers', 'click-to-chat' ); ?>
            </button>
            <p class="ctc-save-note"><?php esc_html_e( 'Your WhatsApp numbers and assignments will be saved', 'click-to-chat' ); ?></p>
        </div>
    </form>
    
    <!-- Number Item Template (for JavaScript) -->
    <script type="text/html" id="tmpl-ctc-number-template">
        <div class="ctc-number-card" data-id="{{ data.id }}">
            <div class="ctc-number-header">
                <div class="ctc-number-title-section">
                    <span class="ctc-number-icon dashicons dashicons-phone"></span>
                    <h3 class="ctc-number-title"><?php esc_html_e( 'New WhatsApp Number', 'click-to-chat' ); ?></h3>
                </div>
                <div class="ctc-number-actions">
                    <button type="button" class="ctc-number-toggle button-link" title="<?php esc_attr_e( 'Expand/Collapse', 'click-to-chat' ); ?>">
                        <span class="dashicons dashicons-arrow-up-alt2"></span>
                    </button>
                    <button type="button" class="ctc-number-delete button-link" title="<?php esc_attr_e( 'Delete Number', 'click-to-chat' ); ?>">
                        <span class="dashicons dashicons-trash"></span>
                    </button>
                </div>
            </div>
            
            <div class="ctc-number-body">
                <input type="hidden" name="ctc_numbers[{{ data.index }}][id]" value="{{ data.id }}" />
                
                <div class="ctc-field-row">
                    <div class="ctc-field-col">
                        <label for="ctc_numbers_name_{{ data.id }}"><?php esc_html_e( 'Name:', 'click-to-chat' ); ?></label>
                        <input type="text" name="ctc_numbers[{{ data.index }}][name]" id="ctc_numbers_name_{{ data.id }}" value="" class="regular-text ctc-number-name" required />
                        <span class="description"><?php esc_html_e( 'A name to identify this number (for admin use only).', 'click-to-chat' ); ?></span>
                    </div>
                    
                    <div class="ctc-field-col">
                        <label for="ctc_numbers_{{ data.id }}"><?php esc_html_e( 'WhatsApp Number:', 'click-to-chat' ); ?></label>
                        <input type="text" name="ctc_numbers[{{ data.index }}][number]" id="ctc_numbers_{{ data.id }}" value="" class="regular-text" placeholder="123456789012" required />
                        <span class="description"><?php esc_html_e( 'Enter the WhatsApp number with country code, no spaces or special characters.', 'click-to-chat' ); ?></span>
                    </div>
                </div>
                
                <p>
                    <label for="ctc_numbers_description_{{ data.id }}"><?php esc_html_e( 'Description:', 'click-to-chat' ); ?></label>
                    <textarea name="ctc_numbers[{{ data.index }}][description]" id="ctc_numbers_description_{{ data.id }}" rows="3" class="large-text"></textarea>
                    <span class="description"><?php esc_html_e( 'Optional description or notes about this number.', 'click-to-chat' ); ?></span>
                </p>
                
                <!-- Assignments Section -->
                <div class="ctc-assignments-section">
                    <h4 class="ctc-assignments-heading"><?php esc_html_e( 'Assign this WhatsApp Number to:', 'click-to-chat' ); ?></h4>
                    
                    <div class="ctc-assignment-type">
                        <label class="ctc-assignment-label"><?php esc_html_e( 'Products:', 'click-to-chat' ); ?></label>
                        <select name="ctc_numbers[{{ data.index }}][assignments][products][]" class="ctc-product-select" multiple="multiple" data-placeholder="<?php esc_attr_e( 'Select products...', 'click-to-chat' ); ?>"></select>
                    </div>
                    
                    <div class="ctc-assignment-type">
                        <label class="ctc-assignment-label"><?php esc_html_e( 'Product Categories:', 'click-to-chat' ); ?></label>
                        <select name="ctc_numbers[{{ data.index }}][assignments][categories][]" class="ctc-category-select" multiple="multiple" data-placeholder="<?php esc_attr_e( 'Select categories...', 'click-to-chat' ); ?>"></select>
                    </div>
                    
                    <div class="ctc-assignment-type">
                        <label class="ctc-assignment-label"><?php esc_html_e( 'Pages:', 'click-to-chat' ); ?></label>
                        <select name="ctc_numbers[{{ data.index }}][assignments][pages][]" class="ctc-page-select" multiple="multiple" data-placeholder="<?php esc_attr_e( 'Select pages...', 'click-to-chat' ); ?>"></select>
                    </div>
                </div>
            </div>
        </div>
    </script>
</div>
