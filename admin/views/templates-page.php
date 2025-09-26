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
        'shop_page'      => "Hello! I'm browsing your {category_name} products and have a question.\nI was looking at: {current_page_url}\n\nCould you help me with more information about your products in this category?",
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
    
    <p><?php esc_html_e( 'Configure the message templates for different contexts. Use placeholders to include dynamic data in your messages.', 'click-to-chat' ); ?></p>
    
    <form method="post" action="">
        <?php wp_nonce_field( 'ctc_templates_nonce', 'ctc_templates_nonce' ); ?>
        
        <div class="ctc-templates-container">
            <!-- Single Product Template -->
            <div class="ctc-template-item">
                <h3 class="ctc-template-header"><?php esc_html_e( 'Single Product Template', 'click-to-chat' ); ?></h3>
                <div class="ctc-template-content">
                    <p class="description"><?php esc_html_e( 'This template is used for inquiries about a specific product.', 'click-to-chat' ); ?></p>
                    
                    <div class="ctc-placeholders">
                        <strong><?php esc_html_e( 'Available Placeholders:', 'click-to-chat' ); ?></strong><br>
                        <?php
                        $placeholders = $settings_helper->get_template_placeholders( 'single_product' );
                        foreach ( $placeholders as $placeholder => $description ) {
                            echo '<span class="ctc-placeholder-tag" data-placeholder="' . esc_attr( $placeholder ) . '">' . esc_html( $placeholder ) . '</span>';
                        }
                        ?>
                    </div>
                    
                    <textarea name="ctc_templates[single_product]" class="ctc-template-textarea" rows="8"><?php echo esc_textarea( html_entity_decode( $message_templates['single_product'] ?? '', ENT_QUOTES | ENT_HTML5, 'UTF-8' ) ); ?></textarea>
                    
                    <button type="button" class="button ctc-template-preview-button" data-template-type="single_product">
                        <?php esc_html_e( 'Preview', 'click-to-chat' ); ?>
                    </button>
                    
                    <div class="ctc-template-preview" style="display: none;"></div>
                </div>
            </div>
            
            <!-- Product Variations Template -->
            <div class="ctc-template-item">
                <h3 class="ctc-template-header"><?php esc_html_e( 'Product Variations Template', 'click-to-chat' ); ?></h3>
                <div class="ctc-template-content">
                    <p class="description"><?php esc_html_e( 'This template is used for variable products when specific variations are selected.', 'click-to-chat' ); ?></p>
                    
                    <div class="ctc-placeholders">
                        <strong><?php esc_html_e( 'Available Placeholders:', 'click-to-chat' ); ?></strong><br>
                        <?php
                        $placeholders = $settings_helper->get_template_placeholders( 'variations' );
                        foreach ( $placeholders as $placeholder => $description ) {
                            echo '<span class="ctc-placeholder-tag" data-placeholder="' . esc_attr( $placeholder ) . '">' . esc_html( $placeholder ) . '</span>';
                        }
                        ?>
                    </div>
                    
                    <textarea name="ctc_templates[variations]" class="ctc-template-textarea" rows="8"><?php echo esc_textarea( html_entity_decode( $message_templates['variations'] ?? '', ENT_QUOTES | ENT_HTML5, 'UTF-8' ) ); ?></textarea>
                    
                    <button type="button" class="button ctc-template-preview-button" data-template-type="variations">
                        <?php esc_html_e( 'Preview', 'click-to-chat' ); ?>
                    </button>
                    
                    <div class="ctc-template-preview" style="display: none;"></div>
                </div>
            </div>
            
            <!-- Shop Page Template -->
            <div class="ctc-template-item">
                <h3 class="ctc-template-header"><?php esc_html_e( 'Shop Page Template', 'click-to-chat' ); ?></h3>
                <div class="ctc-template-content">
                    <p class="description"><?php esc_html_e( 'This template is used for inquiries from shop/archive pages.', 'click-to-chat' ); ?></p>
                    
                    <div class="ctc-placeholders">
                        <strong><?php esc_html_e( 'Available Placeholders:', 'click-to-chat' ); ?></strong><br>
                        <?php
                        $placeholders = $settings_helper->get_template_placeholders( 'shop_page' );
                        foreach ( $placeholders as $placeholder => $description ) {
                            echo '<span class="ctc-placeholder-tag" data-placeholder="' . esc_attr( $placeholder ) . '">' . esc_html( $placeholder ) . '</span>';
                        }
                        ?>
                    </div>
                    
                    <textarea name="ctc_templates[shop_page]" class="ctc-template-textarea" rows="8"><?php echo esc_textarea( html_entity_decode( $message_templates['shop_page'] ?? '', ENT_QUOTES | ENT_HTML5, 'UTF-8' ) ); ?></textarea>
                    
                    <button type="button" class="button ctc-template-preview-button" data-template-type="shop_page">
                        <?php esc_html_e( 'Preview', 'click-to-chat' ); ?>
                    </button>
                    
                    <div class="ctc-template-preview" style="display: none;"></div>
                </div>
            </div>
            
            <!-- Cart & Checkout Template -->
            <div class="ctc-template-item">
                <h3 class="ctc-template-header"><?php esc_html_e( 'Cart & Checkout Template', 'click-to-chat' ); ?></h3>
                <div class="ctc-template-content">
                    <p class="description"><?php esc_html_e( 'This template is used for inquiries from the cart or checkout pages.', 'click-to-chat' ); ?></p>
                    
                    <div class="ctc-placeholders">
                        <strong><?php esc_html_e( 'Available Placeholders:', 'click-to-chat' ); ?></strong><br>
                        <?php
                        $placeholders = $settings_helper->get_template_placeholders( 'cart_checkout' );
                        foreach ( $placeholders as $placeholder => $description ) {
                            echo '<span class="ctc-placeholder-tag" data-placeholder="' . esc_attr( $placeholder ) . '">' . esc_html( $placeholder ) . '</span>';
                        }
                        ?>
                    </div>
                    
                    <textarea name="ctc_templates[cart_checkout]" class="ctc-template-textarea" rows="8"><?php echo esc_textarea( html_entity_decode( $message_templates['cart_checkout'] ?? '', ENT_QUOTES | ENT_HTML5, 'UTF-8' ) ); ?></textarea>
                    
                    <button type="button" class="button ctc-template-preview-button" data-template-type="cart_checkout">
                        <?php esc_html_e( 'Preview', 'click-to-chat' ); ?>
                    </button>
                    
                    <div class="ctc-template-preview" style="display: none;"></div>
                </div>
            </div>
            
            <!-- Thank You Page Template -->
            <div class="ctc-template-item">
                <h3 class="ctc-template-header"><?php esc_html_e( 'Thank You Page Template', 'click-to-chat' ); ?></h3>
                <div class="ctc-template-content">
                    <p class="description"><?php esc_html_e( 'This template is used for inquiries from the order confirmation/thank you page.', 'click-to-chat' ); ?></p>
                    
                    <div class="ctc-placeholders">
                        <strong><?php esc_html_e( 'Available Placeholders:', 'click-to-chat' ); ?></strong><br>
                        <?php
                        $placeholders = $settings_helper->get_template_placeholders( 'thank_you' );
                        foreach ( $placeholders as $placeholder => $description ) {
                            echo '<span class="ctc-placeholder-tag" data-placeholder="' . esc_attr( $placeholder ) . '">' . esc_html( $placeholder ) . '</span>';
                        }
                        ?>
                    </div>
                    
                    <textarea name="ctc_templates[thank_you]" class="ctc-template-textarea" rows="8"><?php echo esc_textarea( html_entity_decode( $message_templates['thank_you'] ?? '', ENT_QUOTES | ENT_HTML5, 'UTF-8' ) ); ?></textarea>
                    
                    <button type="button" class="button ctc-template-preview-button" data-template-type="thank_you">
                        <?php esc_html_e( 'Preview', 'click-to-chat' ); ?>
                    </button>
                    
                    <div class="ctc-template-preview" style="display: none;"></div>
                </div>
            </div>
            
            <!-- Floating Button Template -->
            <div class="ctc-template-item">
                <h3 class="ctc-template-header"><?php esc_html_e( 'Floating Button Template', 'click-to-chat' ); ?></h3>
                <div class="ctc-template-content">
                    <p class="description"><?php esc_html_e( 'This template is used for the floating WhatsApp button.', 'click-to-chat' ); ?></p>
                    
                    <div class="ctc-placeholders">
                        <strong><?php esc_html_e( 'Available Placeholders:', 'click-to-chat' ); ?></strong><br>
                        <?php
                        $placeholders = $settings_helper->get_template_placeholders( 'floating' );
                        foreach ( $placeholders as $placeholder => $description ) {
                            echo '<span class="ctc-placeholder-tag" data-placeholder="' . esc_attr( $placeholder ) . '">' . esc_html( $placeholder ) . '</span>';
                        }
                        ?>
                    </div>
                    
                    <textarea name="ctc_templates[floating]" class="ctc-template-textarea" rows="8"><?php echo esc_textarea( html_entity_decode( $message_templates['floating'] ?? '', ENT_QUOTES | ENT_HTML5, 'UTF-8' ) ); ?></textarea>
                    
                    <button type="button" class="button ctc-template-preview-button" data-template-type="floating">
                        <?php esc_html_e( 'Preview', 'click-to-chat' ); ?>
                    </button>
                    
                    <div class="ctc-template-preview" style="display: none;"></div>
                </div>
            </div>
        </div>
        
        <p class="submit">
            <input type="submit" name="ctc_save_templates" class="button button-primary" value="<?php esc_attr_e( 'Save Templates', 'click-to-chat' ); ?>">
        </p>
    </form>
</div>