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

// Get plugin settings
$settings = get_option( 'ctc_settings', array() );

// Get button settings for defaults
$button_settings = isset( $settings['button_settings'] ) ? $settings['button_settings'] : array();
$default_text = isset( $button_settings['text'] ) ? $button_settings['text'] : esc_html__( 'Order via WhatsApp', 'click-to-chat' );
$default_bg_color = isset( $button_settings['bg_color'] ) ? $button_settings['bg_color'] : '#25D366';
$default_text_color = isset( $button_settings['text_color'] ) ? $button_settings['text_color'] : '#ffffff';

// Get WhatsApp numbers
$whatsapp_numbers = isset( $settings['whatsapp_numbers'] ) ? $settings['whatsapp_numbers'] : array();
?>

<div class="wrap ctc-admin-container">
    <div class="ctc-admin-header">
        <span class="ctc-admin-logo dashicons dashicons-whatsapp"></span>
        <h1 class="ctc-admin-heading"><?php esc_html_e( 'WhatsApp Button Shortcode Builder', 'click-to-chat' ); ?></h1>
    </div>

    <!-- Quick Instructions -->
    <div class="ctc-instructions-banner">
        <h2>📚 <?php esc_html_e( 'How to Use Shortcodes', 'click-to-chat' ); ?></h2>
        <p><?php esc_html_e( 'Create custom WhatsApp buttons and place them anywhere on your site using shortcodes.', 'click-to-chat' ); ?></p>
        <div class="ctc-quick-steps">
            <div class="ctc-step">
                <span class="ctc-step-number">1</span>
                <span><?php esc_html_e( 'Configure your button below', 'click-to-chat' ); ?></span>
            </div>
            <div class="ctc-step">
                <span class="ctc-step-number">2</span>
                <span><?php esc_html_e( 'Copy the generated shortcode', 'click-to-chat' ); ?></span>
            </div>
            <div class="ctc-step">
                <span class="ctc-step-number">3</span>
                <span><?php esc_html_e( 'Paste it in any post, page, or widget', 'click-to-chat' ); ?></span>
            </div>
        </div>
    </div>
    
    <?php
    // Check if plugin is disabled or no numbers configured
    $plugin_enabled = isset( $settings['plugin_enabled'] ) ? $settings['plugin_enabled'] : true;
    $has_numbers = isset( $settings['whatsapp_numbers'] ) && ! empty( $settings['whatsapp_numbers'] );

    if ( ! $plugin_enabled || ! $has_numbers ) :
    ?>
    <div class="ctc-admin-warning-box">
        <?php if ( ! $plugin_enabled ) : ?>
        <div class="ctc-warning-item">
            <span class="ctc-warning-icon">⚠️</span>
            <div class="ctc-warning-content">
                <strong><?php esc_html_e( 'Warning:', 'click-to-chat' ); ?></strong>
                <?php
                printf(
                    /* translators: %s: Link to Settings page */
                    esc_html__( 'The plugin is currently disabled. WhatsApp buttons will not appear on your website. %s to activate the plugin.', 'click-to-chat' ),
                    '<a href="' . esc_url( admin_url( 'admin.php?page=click-to-chat' ) ) . '">' . esc_html__( 'Go to Settings', 'click-to-chat' ) . '</a>'
                );
                ?>
            </div>
        </div>
        <?php endif; ?>

        <?php if ( ! $has_numbers ) : ?>
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

    <!-- Main Builder -->
    <div class="ctc-builder-wrapper">
        <!-- Left Side: Configuration -->
        <div class="ctc-config-panel">
            <h2 class="ctc-panel-title">⚙️ <?php esc_html_e( 'Button Configuration', 'click-to-chat' ); ?></h2>

            <!-- Button Type Selection -->
            <div class="ctc-field-section">
                <h3><?php esc_html_e( 'Button Type', 'click-to-chat' ); ?></h3>
                <div class="ctc-button-types">
                    <label class="ctc-type-card">
                        <input type="radio" name="button_type" value="product" class="ctc-shortcode-param" data-param="type" checked>
                        <div class="ctc-type-card-inner">
                            <span class="ctc-type-icon">📦</span>
                            <span class="ctc-type-label"><?php esc_html_e( 'Product', 'click-to-chat' ); ?></span>
                            <small><?php esc_html_e( 'For product pages', 'click-to-chat' ); ?></small>
                        </div>
                    </label>

                    <label class="ctc-type-card">
                        <input type="radio" name="button_type" value="cart" class="ctc-shortcode-param" data-param="type">
                        <div class="ctc-type-card-inner">
                            <span class="ctc-type-icon">🛒</span>
                            <span class="ctc-type-label"><?php esc_html_e( 'Cart', 'click-to-chat' ); ?></span>
                            <small><?php esc_html_e( 'Include cart items', 'click-to-chat' ); ?></small>
                        </div>
                    </label>

                    <label class="ctc-type-card">
                        <input type="radio" name="button_type" value="shop" class="ctc-shortcode-param" data-param="type">
                        <div class="ctc-type-card-inner">
                            <span class="ctc-type-icon">🏪</span>
                            <span class="ctc-type-label"><?php esc_html_e( 'Shop', 'click-to-chat' ); ?></span>
                            <small><?php esc_html_e( 'Shop/category pages', 'click-to-chat' ); ?></small>
                        </div>
                    </label>

                    <label class="ctc-type-card">
                        <input type="radio" name="button_type" value="floating" class="ctc-shortcode-param" data-param="type">
                        <div class="ctc-type-card-inner">
                            <span class="ctc-type-icon">💬</span>
                            <span class="ctc-type-label"><?php esc_html_e( 'General', 'click-to-chat' ); ?></span>
                            <small><?php esc_html_e( 'Simple contact', 'click-to-chat' ); ?></small>
                        </div>
                    </label>
                </div>
            </div>

            <!-- Product Selection (for product type) -->
            <div class="ctc-field-section ctc-product-options ctc-visible">
                <h3><?php esc_html_e( 'Product Selection', 'click-to-chat' ); ?></h3>
                <div class="ctc-field-group">
                    <label class="ctc-radio-option">
                        <input type="radio" name="product_source" value="current" class="ctc-shortcode-param" data-param="current" checked>
                        <span><?php esc_html_e( 'Use current product (auto-detect)', 'click-to-chat' ); ?></span>
                    </label>
                    <label class="ctc-radio-option">
                        <input type="radio" name="product_source" value="specific">
                        <span><?php esc_html_e( 'Specify product ID', 'click-to-chat' ); ?></span>
                    </label>
                    <div class="ctc-product-id-field ctc-hidden">
                        <input type="number" id="ctc_product_id" class="ctc-shortcode-param" data-param="product_id" placeholder="<?php esc_attr_e( 'Enter product ID', 'click-to-chat' ); ?>">
                    </div>
                </div>
            </div>

            <!-- Appearance -->
            <div class="ctc-field-section">
                <h3><?php esc_html_e( 'Appearance', 'click-to-chat' ); ?></h3>

                <div class="ctc-field-group">
                    <label for="ctc_button_text"><?php esc_html_e( 'Button Text', 'click-to-chat' ); ?></label>
                    <input type="text" id="ctc_button_text" class="ctc-shortcode-param" data-param="text" placeholder="<?php echo esc_attr( $default_text ); ?>">
                </div>

                <div class="ctc-color-fields">
                    <div class="ctc-field-group">
                        <label><?php esc_html_e( 'Background', 'click-to-chat' ); ?></label>
                        <input type="text" id="ctc_bg_color" class="ctc-color-field ctc-shortcode-param" data-param="bg_color" value="<?php echo esc_attr( $default_bg_color ); ?>">
                    </div>
                    <div class="ctc-field-group">
                        <label><?php esc_html_e( 'Text Color', 'click-to-chat' ); ?></label>
                        <input type="text" id="ctc_text_color" class="ctc-color-field ctc-shortcode-param" data-param="text_color" value="<?php echo esc_attr( $default_text_color ); ?>">
                    </div>
                </div>

                <div class="ctc-size-align-fields">
                    <div class="ctc-field-group">
                        <label><?php esc_html_e( 'Size', 'click-to-chat' ); ?></label>
                        <select class="ctc-shortcode-param" data-param="size">
                            <option value="small"><?php esc_html_e( 'Small', 'click-to-chat' ); ?></option>
                            <option value="normal" selected><?php esc_html_e( 'Normal', 'click-to-chat' ); ?></option>
                            <option value="large"><?php esc_html_e( 'Large', 'click-to-chat' ); ?></option>
                        </select>
                    </div>
                    <div class="ctc-field-group">
                        <label><?php esc_html_e( 'Alignment', 'click-to-chat' ); ?></label>
                        <select class="ctc-shortcode-param" data-param="align">
                            <option value="left"><?php esc_html_e( 'Left', 'click-to-chat' ); ?></option>
                            <option value="center" selected><?php esc_html_e( 'Center', 'click-to-chat' ); ?></option>
                            <option value="right"><?php esc_html_e( 'Right', 'click-to-chat' ); ?></option>
                        </select>
                    </div>
                </div>

                <div class="ctc-field-group">
                    <label class="ctc-checkbox-option">
                        <input type="checkbox" class="ctc-shortcode-param" data-param="icon" checked>
                        <span><?php esc_html_e( 'Show WhatsApp icon', 'click-to-chat' ); ?></span>
                    </label>
                </div>
            </div>

            <!-- Advanced Options -->
            <div class="ctc-field-section ctc-advanced">
                <h3 class="ctc-collapsible">
                    <span class="dashicons dashicons-arrow-right-alt2"></span>
                    <?php esc_html_e( 'Advanced Options', 'click-to-chat' ); ?>
                </h3>
                <div class="ctc-advanced-content ctc-hidden">
                    <?php if ( ! empty( $whatsapp_numbers ) ) : ?>
                    <div class="ctc-field-group">
                        <label><?php esc_html_e( 'Specific Number', 'click-to-chat' ); ?></label>
                        <select class="ctc-shortcode-param" data-param="show_number">
                            <option value=""><?php esc_html_e( 'Use default', 'click-to-chat' ); ?></option>
                            <?php foreach ( $whatsapp_numbers as $number ) : ?>
                                <option value="<?php echo esc_attr( $number['id'] ); ?>">
                                    <?php echo esc_html( $number['name'] . ' (' . $number['number'] . ')' ); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <?php endif; ?>

                    <div class="ctc-field-group">
                        <label><?php esc_html_e( 'Custom Message', 'click-to-chat' ); ?></label>
                        <textarea class="ctc-shortcode-param" data-param="message" rows="3" placeholder="<?php esc_attr_e( 'Optional custom message...', 'click-to-chat' ); ?>"></textarea>
                    </div>

                    <div class="ctc-field-group">
                        <label><?php esc_html_e( 'CSS Class', 'click-to-chat' ); ?></label>
                        <input type="text" class="ctc-shortcode-param" data-param="css_class" placeholder="<?php esc_attr_e( 'custom-class', 'click-to-chat' ); ?>">
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Side: Preview & Output -->
        <div class="ctc-preview-panel">
            <!-- Live Preview -->
            <div class="ctc-preview-section">
                <h3>👁️ <?php esc_html_e( 'Live Preview', 'click-to-chat' ); ?></h3>
                <div class="ctc-preview-area">
                    <div id="ctc-button-preview">
                        <!-- Preview will be generated here -->
                    </div>
                </div>
            </div>

            <!-- Generated Shortcode -->
            <div class="ctc-shortcode-section">
                <h3>📋 <?php esc_html_e( 'Your Shortcode', 'click-to-chat' ); ?></h3>
                <div class="ctc-shortcode-box">
                    <code id="ctc-generated-shortcode">[ctc_button]</code>
                    <button type="button" class="ctc-copy-btn" id="ctc-copy-shortcode">
                        <span class="dashicons dashicons-clipboard"></span>
                        <span class="ctc-copy-text"><?php esc_html_e( 'Copy', 'click-to-chat' ); ?></span>
                    </button>
                </div>
                <div class="ctc-copy-success ctc-hidden">
                    ✅ <?php esc_html_e( 'Copied to clipboard!', 'click-to-chat' ); ?>
                </div>
            </div>

            <!-- Quick Examples -->
            <div class="ctc-examples-section">
                <h3>💡 <?php esc_html_e( 'Quick Examples', 'click-to-chat' ); ?></h3>
                <div class="ctc-example-list">
                    <div class="ctc-example">
                        <strong><?php esc_html_e( 'Basic button:', 'click-to-chat' ); ?></strong>
                        <code>[ctc_button]</code>
                    </div>
                    <div class="ctc-example">
                        <strong><?php esc_html_e( 'Specific product:', 'click-to-chat' ); ?></strong>
                        <code>[ctc_button product_id="123"]</code>
                    </div>
                    <div class="ctc-example">
                        <strong><?php esc_html_e( 'Cart button:', 'click-to-chat' ); ?></strong>
                        <code>[ctc_button type="cart"]</code>
                    </div>
                    <div class="ctc-example">
                        <strong><?php esc_html_e( 'Custom text:', 'click-to-chat' ); ?></strong>
                        <code>[ctc_button text="Contact Us"]</code>
                    </div>
                </div>
            </div>

            <!-- Help Tips -->
            <div class="ctc-help-section">
                <h3>❓ <?php esc_html_e( 'Where to Use', 'click-to-chat' ); ?></h3>
                <ul class="ctc-help-list">
                    <li>✅ <?php esc_html_e( 'In any WordPress post or page content', 'click-to-chat' ); ?></li>
                    <li>✅ <?php esc_html_e( 'In text widgets', 'click-to-chat' ); ?></li>
                    <li>✅ <?php esc_html_e( 'In page builders (Elementor, Gutenberg, etc.)', 'click-to-chat' ); ?></li>
                    <li>✅ <?php esc_html_e( 'In product descriptions', 'click-to-chat' ); ?></li>
                    <li>✅ <?php esc_html_e( 'Multiple buttons on the same page', 'click-to-chat' ); ?></li>
                </ul>
            </div>
        </div>
    </div>
</div>
