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

// Include settings class
require_once plugin_dir_path( dirname( __FILE__ ) ) . 'class-settings.php';

// Get plugin settings
$settings = get_option( 'ctc_settings', array() );

// Get message templates
$message_templates = isset( $settings['message_templates'] ) ? $settings['message_templates'] : array();

// Initialize templates if not set
if ( empty( $message_templates ) ) {
    $message_templates = array(
        'single_product' => "Hello! I'm interested in the product: *{product_name}*\nPrice: {price}\nURL: {product_url}\n\nDo you have this item in stock? I'd like to get more information.",
        'cart_checkout'  => "Hello! I'd like to complete my purchase of:\n{cart_items_list}\n---------------------\nSubtotal: {cart_subtotal}\nTax: {tax_amount}\nShipping: {shipping_method} - {shipping_cost}\nTotal: {cart_total}\n\nI have a few questions before finalizing my order.",
        'thank_you'      => "Hello! I've just placed order #{order_number} on {order_date}.\nMy order includes:\n{ordered_items_list}\n---------------------\nApplied Coupon: {coupon_code}\nTotal: {order_total}\n\nI'd like to confirm when this will be shipped.",
        'floating'       => "Hello! I was browsing your website at {current_page_url} and have a question.",
        'variations'     => "Hello! I'm interested in the product: *{product_name}*\nSelected options: {variation_details}\nPrice: {variation_price}\nURL: {product_url}\n\nIs this combination available for immediate shipping?",
    );
}

// Get settings helper for template placeholders
$settings_helper = new CTC_Settings();
?>

<div class="wrap ctc-admin-container">
    <div class="ctc-admin-header">
        <span class="ctc-admin-logo dashicons dashicons-whatsapp"></span>
        <h1 class="ctc-admin-heading"><?php esc_html_e( 'Message Templates', 'click-to-chat' ); ?></h1>
    </div>

    <!-- Instructions Banner -->
    <div class="ctc-instructions-banner">
        <h2>📝 <?php esc_html_e( 'Customize Your WhatsApp Messages', 'click-to-chat' ); ?></h2>
        <p><?php esc_html_e( 'Configure message templates for different contexts. Use placeholders to automatically include product details, cart information, and order data.', 'click-to-chat' ); ?></p>
        <div class="ctc-quick-steps">
            <div class="ctc-step">
                <span class="ctc-step-number">1</span>
                <span><?php esc_html_e( 'Choose a template below', 'click-to-chat' ); ?></span>
            </div>
            <div class="ctc-step">
                <span class="ctc-step-number">2</span>
                <span><?php esc_html_e( 'Click placeholders to insert them', 'click-to-chat' ); ?></span>
            </div>
            <div class="ctc-step">
                <span class="ctc-step-number">3</span>
                <span><?php esc_html_e( 'Preview and save your changes', 'click-to-chat' ); ?></span>
            </div>
        </div>
    </div>
    
    <form method="post" action="">
        <?php wp_nonce_field( 'ctc_templates_nonce', 'ctc_templates_nonce' ); ?>
        
        <div class="ctc-templates-wrapper">
            <!-- Single Product Template -->
            <div class="ctc-template-card">
                <div class="ctc-template-header">
                    <span class="ctc-template-icon">📦</span>
                    <h3 class="ctc-template-title"><?php esc_html_e( 'Single Product Template', 'click-to-chat' ); ?></h3>
                </div>
                <div class="ctc-template-body">
                    <p class="ctc-template-description"><?php esc_html_e( 'This template is used for inquiries about a specific product.', 'click-to-chat' ); ?></p>
                    
                    <div class="ctc-placeholders-section">
                        <label class="ctc-field-label"><?php esc_html_e( 'Available Placeholders:', 'click-to-chat' ); ?></label>
                        <div class="ctc-placeholders-grid">
                            <?php
                            $placeholders = $settings_helper->get_template_placeholders( 'single_product' );
                            foreach ( $placeholders as $placeholder => $description ) {
                                echo '<span class="ctc-placeholder-tag" data-placeholder="' . esc_attr( $placeholder ) . '" title="' . esc_attr( $description ) . '">' . esc_html( $placeholder ) . '</span>';
                            }
                            ?>
                        </div>
                    </div>
                    
                    <div class="ctc-field-group">
                        <label class="ctc-field-label"><?php esc_html_e( 'Message Template', 'click-to-chat' ); ?></label>
                        <textarea name="ctc_templates[single_product]" class="ctc-template-textarea" rows="8"><?php echo esc_textarea( html_entity_decode( $message_templates['single_product'] ?? '', ENT_QUOTES | ENT_HTML5, 'UTF-8' ) ); ?></textarea>
                    </div>

                    <div class="ctc-template-actions">
                        <button type="button" class="ctc-preview-btn" data-template-type="single_product">
                            <span class="dashicons dashicons-visibility"></span>
                            <?php esc_html_e( 'Preview', 'click-to-chat' ); ?>
                        </button>
                    </div>

                    <div class="ctc-template-preview ctc-hidden"></div>
                </div>
            </div>
            
            <!-- Product Variations Template -->
            <div class="ctc-template-card">
                <div class="ctc-template-header">
                    <span class="ctc-template-icon">🎨</span>
                    <h3 class="ctc-template-title"><?php esc_html_e( 'Product Variations Template', 'click-to-chat' ); ?></h3>
                </div>
                <div class="ctc-template-body">
                    <p class="ctc-template-description"><?php esc_html_e( 'This template is used for variable products when specific variations are selected.', 'click-to-chat' ); ?></p>
                    
                    <div class="ctc-placeholders-section">
                        <label class="ctc-field-label"><?php esc_html_e( 'Available Placeholders:', 'click-to-chat' ); ?></label>
                        <div class="ctc-placeholders-grid">
                            <?php
                            $placeholders = $settings_helper->get_template_placeholders( 'variations' );
                            foreach ( $placeholders as $placeholder => $description ) {
                                echo '<span class="ctc-placeholder-tag" data-placeholder="' . esc_attr( $placeholder ) . '" title="' . esc_attr( $description ) . '">' . esc_html( $placeholder ) . '</span>';
                            }
                            ?>
                        </div>
                    </div>

                    <div class="ctc-field-group">
                        <label class="ctc-field-label"><?php esc_html_e( 'Message Template', 'click-to-chat' ); ?></label>
                        <textarea name="ctc_templates[variations]" class="ctc-template-textarea" rows="8"><?php echo esc_textarea( html_entity_decode( $message_templates['variations'] ?? '', ENT_QUOTES | ENT_HTML5, 'UTF-8' ) ); ?></textarea>
                    </div>

                    <div class="ctc-template-actions">
                        <button type="button" class="ctc-preview-btn" data-template-type="variations">
                            <span class="dashicons dashicons-visibility"></span>
                            <?php esc_html_e( 'Preview', 'click-to-chat' ); ?>
                        </button>
                    </div>

                    <div class="ctc-template-preview ctc-hidden"></div>
                </div>
            </div>

            <!-- Cart & Checkout Template -->
            <div class="ctc-template-card">
                <div class="ctc-template-header">
                    <span class="ctc-template-icon">🛒</span>
                    <h3 class="ctc-template-title"><?php esc_html_e( 'Cart & Checkout Template', 'click-to-chat' ); ?></h3>
                </div>
                <div class="ctc-template-body">
                    <p class="ctc-template-description"><?php esc_html_e( 'This template is used for inquiries from the cart or checkout pages.', 'click-to-chat' ); ?></p>
                    
                    <div class="ctc-placeholders-section">
                        <label class="ctc-field-label"><?php esc_html_e( 'Available Placeholders:', 'click-to-chat' ); ?></label>
                        <div class="ctc-placeholders-grid">
                            <?php
                            $placeholders = $settings_helper->get_template_placeholders( 'cart_checkout' );
                            foreach ( $placeholders as $placeholder => $description ) {
                                echo '<span class="ctc-placeholder-tag" data-placeholder="' . esc_attr( $placeholder ) . '" title="' . esc_attr( $description ) . '">' . esc_html( $placeholder ) . '</span>';
                            }
                            ?>
                        </div>
                    </div>

                    <div class="ctc-field-group">
                        <label class="ctc-field-label"><?php esc_html_e( 'Message Template', 'click-to-chat' ); ?></label>
                        <textarea name="ctc_templates[cart_checkout]" class="ctc-template-textarea" rows="8"><?php echo esc_textarea( html_entity_decode( $message_templates['cart_checkout'] ?? '', ENT_QUOTES | ENT_HTML5, 'UTF-8' ) ); ?></textarea>
                    </div>

                    <div class="ctc-template-actions">
                        <button type="button" class="ctc-preview-btn" data-template-type="cart_checkout">
                            <span class="dashicons dashicons-visibility"></span>
                            <?php esc_html_e( 'Preview', 'click-to-chat' ); ?>
                        </button>
                    </div>

                    <div class="ctc-template-preview ctc-hidden"></div>
                </div>
            </div>
            
            <!-- Thank You Page Template -->
            <div class="ctc-template-card">
                <div class="ctc-template-header">
                    <span class="ctc-template-icon">🎆</span>
                    <h3 class="ctc-template-title"><?php esc_html_e( 'Thank You Page Template', 'click-to-chat' ); ?></h3>
                </div>
                <div class="ctc-template-body">
                    <p class="ctc-template-description"><?php esc_html_e( 'This template is used for inquiries from the order confirmation/thank you page.', 'click-to-chat' ); ?></p>
                    
                    <div class="ctc-placeholders-section">
                        <label class="ctc-field-label"><?php esc_html_e( 'Available Placeholders:', 'click-to-chat' ); ?></label>
                        <div class="ctc-placeholders-grid">
                            <?php
                            $placeholders = $settings_helper->get_template_placeholders( 'thank_you' );
                            foreach ( $placeholders as $placeholder => $description ) {
                                echo '<span class="ctc-placeholder-tag" data-placeholder="' . esc_attr( $placeholder ) . '" title="' . esc_attr( $description ) . '">' . esc_html( $placeholder ) . '</span>';
                            }
                            ?>
                        </div>
                    </div>

                    <div class="ctc-field-group">
                        <label class="ctc-field-label"><?php esc_html_e( 'Message Template', 'click-to-chat' ); ?></label>
                        <textarea name="ctc_templates[thank_you]" class="ctc-template-textarea" rows="8"><?php echo esc_textarea( html_entity_decode( $message_templates['thank_you'] ?? '', ENT_QUOTES | ENT_HTML5, 'UTF-8' ) ); ?></textarea>
                    </div>

                    <div class="ctc-template-actions">
                        <button type="button" class="ctc-preview-btn" data-template-type="thank_you">
                            <span class="dashicons dashicons-visibility"></span>
                            <?php esc_html_e( 'Preview', 'click-to-chat' ); ?>
                        </button>
                    </div>

                    <div class="ctc-template-preview ctc-hidden"></div>
                </div>
            </div>
            
            <!-- Floating Button Template -->
            <div class="ctc-template-card">
                <div class="ctc-template-header">
                    <span class="ctc-template-icon">💬</span>
                    <h3 class="ctc-template-title"><?php esc_html_e( 'Floating Button Template', 'click-to-chat' ); ?></h3>
                </div>
                <div class="ctc-template-body">
                    <p class="ctc-template-description"><?php esc_html_e( 'This template is used for the floating WhatsApp button.', 'click-to-chat' ); ?></p>
                    
                    <div class="ctc-placeholders-section">
                        <label class="ctc-field-label"><?php esc_html_e( 'Available Placeholders:', 'click-to-chat' ); ?></label>
                        <div class="ctc-placeholders-grid">
                            <?php
                            $placeholders = $settings_helper->get_template_placeholders( 'floating' );
                            foreach ( $placeholders as $placeholder => $description ) {
                                echo '<span class="ctc-placeholder-tag" data-placeholder="' . esc_attr( $placeholder ) . '" title="' . esc_attr( $description ) . '">' . esc_html( $placeholder ) . '</span>';
                            }
                            ?>
                        </div>
                    </div>

                    <div class="ctc-field-group">
                        <label class="ctc-field-label"><?php esc_html_e( 'Message Template', 'click-to-chat' ); ?></label>
                        <textarea name="ctc_templates[floating]" class="ctc-template-textarea" rows="8"><?php echo esc_textarea( html_entity_decode( $message_templates['floating'] ?? '', ENT_QUOTES | ENT_HTML5, 'UTF-8' ) ); ?></textarea>
                    </div>

                    <div class="ctc-template-actions">
                        <button type="button" class="ctc-preview-btn" data-template-type="floating">
                            <span class="dashicons dashicons-visibility"></span>
                            <?php esc_html_e( 'Preview', 'click-to-chat' ); ?>
                        </button>
                    </div>

                    <div class="ctc-template-preview ctc-hidden"></div>
                </div>
            </div>
        </div>

        <div class="ctc-save-section">
            <button type="submit" name="ctc_save_templates" class="ctc-save-btn">
                <span class="dashicons dashicons-saved"></span>
                <?php esc_html_e( 'Save All Templates', 'click-to-chat' ); ?>
            </button>
            <p class="ctc-save-note"><?php esc_html_e( 'Your changes will be applied to all WhatsApp messages', 'click-to-chat' ); ?></p>
        </div>
    </form>
</div>
