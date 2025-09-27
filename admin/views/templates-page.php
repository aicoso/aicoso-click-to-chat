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

                    <div class="ctc-template-preview" style="display: none;"></div>
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

                    <div class="ctc-template-preview" style="display: none;"></div>
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

                    <div class="ctc-template-preview" style="display: none;"></div>
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

                    <div class="ctc-template-preview" style="display: none;"></div>
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

                    <div class="ctc-template-preview" style="display: none;"></div>
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

<style>
/* Modern Template Page Styles */
.ctc-admin-container {
    max-width: 1200px;
    margin: 20px auto;
}

.ctc-instructions-banner {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 25px;
    border-radius: 8px;
    margin: 20px 0;
}

.ctc-instructions-banner h2 {
    color: white;
    margin: 0 0 10px 0;
    font-size: 20px;
}

.ctc-instructions-banner p {
    color: rgba(255,255,255,0.95);
    margin: 0 0 15px 0;
}

.ctc-quick-steps {
    display: flex;
    gap: 30px;
    margin-top: 15px;
}

.ctc-step {
    display: flex;
    align-items: center;
    gap: 10px;
}

.ctc-step-number {
    background: rgba(255,255,255,0.2);
    color: white;
    width: 25px;
    height: 25px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
    font-size: 12px;
}

.ctc-templates-wrapper {
    display: grid;
    gap: 20px;
    margin: 20px 0;
}

.ctc-template-card {
    background: white;
    border: 1px solid #e0e0e0;
    border-radius: 8px;
    overflow: hidden;
    transition: box-shadow 0.2s;
}

.ctc-template-card:hover {
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

.ctc-template-header {
    background: linear-gradient(135deg, #f5f7fa 0%, #e9ecef 100%);
    padding: 15px 20px;
    border-bottom: 1px solid #dee2e6;
    display: flex;
    align-items: center;
    gap: 12px;
}

.ctc-template-icon {
    font-size: 24px;
}

.ctc-template-title {
    margin: 0;
    font-size: 16px;
    font-weight: 600;
    color: #333;
}

.ctc-template-body {
    padding: 20px;
}

.ctc-template-description {
    color: #666;
    margin: 0 0 20px 0;
    font-size: 13px;
}

.ctc-placeholders-section {
    margin-bottom: 20px;
}

.ctc-field-label {
    display: block;
    margin-bottom: 8px;
    font-weight: 500;
    font-size: 13px;
    color: #333;
}

.ctc-placeholders-grid {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
}

.ctc-placeholder-tag {
    background: #e8f5e9;
    color: #2e7d32;
    padding: 4px 10px;
    border-radius: 15px;
    font-size: 12px;
    font-family: monospace;
    cursor: pointer;
    transition: all 0.2s;
    border: 1px solid #c8e6c9;
}

.ctc-placeholder-tag:hover {
    background: #25D366;
    color: white;
    border-color: #25D366;
    transform: translateY(-1px);
}

.ctc-field-group {
    margin-bottom: 20px;
}

.ctc-template-textarea {
    width: 100%;
    padding: 12px;
    border: 1px solid #ddd;
    border-radius: 6px;
    font-family: monospace;
    font-size: 13px;
    line-height: 1.5;
    resize: vertical;
    transition: border-color 0.2s;
}

.ctc-template-textarea:focus {
    outline: none;
    border-color: #25D366;
    box-shadow: 0 0 0 3px rgba(37, 211, 102, 0.1);
}

.ctc-template-actions {
    display: flex;
    gap: 10px;
}

.ctc-preview-btn {
    background: #f8f9fa;
    color: #333;
    border: 1px solid #dee2e6;
    padding: 8px 16px;
    border-radius: 4px;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 6px;
    transition: all 0.2s;
    font-size: 13px;
}

.ctc-preview-btn:hover {
    background: #e9ecef;
    border-color: #adb5bd;
}

.ctc-preview-btn .dashicons {
    font-size: 16px;
    width: 16px;
    height: 16px;
}

.ctc-template-preview {
    margin-top: 15px;
    padding: 15px;
    background: #f8f9fa;
    border-radius: 6px;
    border: 1px solid #dee2e6;
    white-space: pre-wrap;
    font-family: monospace;
    font-size: 12px;
    line-height: 1.5;
}

.ctc-template-preview.error {
    background: #ffebee;
    border-color: #ffcdd2;
    color: #c62828;
}

.ctc-save-section {
    margin: 40px 0 20px;
    text-align: center;
    padding: 30px;
    background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%);
    border-radius: 8px;
    border: 1px solid #e0e0e0;
}

.ctc-save-btn {
    background: #25D366;
    color: white;
    border: none;
    padding: 12px 35px;
    font-size: 15px;
    font-weight: 600;
    border-radius: 25px;
    cursor: pointer;
    transition: all 0.3s;
    box-shadow: 0 2px 8px rgba(37, 211, 102, 0.3);
    display: inline-flex;
    align-items: center;
    gap: 8px;
}

.ctc-save-btn .dashicons {
    font-size: 18px;
    width: 18px;
    height: 18px;
    line-height: 18px;
}

.ctc-save-btn:hover {
    background: #20bd5a;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(37, 211, 102, 0.4);
}

.ctc-save-btn:active {
    transform: translateY(0);
}

.ctc-save-note {
    margin: 12px 0 0;
    color: #666;
    font-size: 13px;
}

/* Responsive Design */
@media (max-width: 768px) {
    .ctc-quick-steps {
        flex-direction: column;
        gap: 10px;
    }

    .ctc-placeholders-grid {
        flex-direction: column;
    }

    .ctc-template-actions {
        flex-direction: column;
    }
}
</style>