<?php
/**
 * Shortcode generator page view.
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
        <h1 class="ctc-admin-heading"><?php esc_html_e( 'Shortcode Generator', 'click-to-chat' ); ?></h1>
    </div>
    
    <p><?php esc_html_e( 'Generate a shortcode to display a WhatsApp button anywhere in your content.', 'click-to-chat' ); ?></p>
    
    <div class="ctc-shortcode-generator">
        <div class="ctc-shortcode-form">
            <h3><?php esc_html_e( 'Configure Your Shortcode', 'click-to-chat' ); ?></h3>
            <?php wp_nonce_field( 'ctc_shortcode_nonce', 'ctc_shortcode_nonce' ); ?>
            
            <table class="form-table ctc-form-table">
                <tr>
                    <th scope="row">
                        <label for="ctc_shortcode_type"><?php esc_html_e( 'Button Type', 'click-to-chat' ); ?></label>
                    </th>
                    <td>
                        <select id="ctc_shortcode_type" class="ctc-shortcode-param" data-param="type">
                            <option value="product"><?php esc_html_e( 'Product Button', 'click-to-chat' ); ?></option>
                            <option value="shop"><?php esc_html_e( 'Shop/Category Button', 'click-to-chat' ); ?></option>
                            <option value="cart"><?php esc_html_e( 'Cart Button', 'click-to-chat' ); ?></option>
                            <option value="floating"><?php esc_html_e( 'General Purpose Button', 'click-to-chat' ); ?></option>
                        </select>
                        <p class="description"><?php esc_html_e( 'Select the type of WhatsApp button you want to display.', 'click-to-chat' ); ?></p>
                    </td>
                </tr>
                
                <tr class="ctc-product-params">
                    <th scope="row">
                        <label><?php esc_html_e( 'Product Source', 'click-to-chat' ); ?></label>
                    </th>
                    <td>
                        <fieldset>
                            <label>
                                <input type="radio" name="ctc_product_source" value="current" class="ctc-shortcode-param" data-param="current" data-value="yes" checked>
                                <?php esc_html_e( 'Use current product (when used on product pages)', 'click-to-chat' ); ?>
                            </label>
                            <br>
                            <label>
                                <input type="radio" name="ctc_product_source" value="specific" class="ctc-product-source-toggle">
                                <?php esc_html_e( 'Specify a product', 'click-to-chat' ); ?>
                            </label>
                            <div class="ctc-specific-product" style="display:none; margin-top:10px;">
                                <select class="ctc-product-select ctc-shortcode-param" data-param="product_id" style="width: 100%;" data-placeholder="<?php esc_attr_e( 'Select a product...', 'click-to-chat' ); ?>"></select>
                            </div>
                        </fieldset>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row">
                        <label for="ctc_shortcode_text"><?php esc_html_e( 'Button Text', 'click-to-chat' ); ?></label>
                    </th>
                    <td>
                        <input type="text" id="ctc_shortcode_text" class="regular-text ctc-shortcode-param" data-param="text" placeholder="<?php echo esc_attr( $default_text ); ?>">
                        <p class="description"><?php esc_html_e( 'Leave empty to use default button text from settings.', 'click-to-chat' ); ?></p>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row">
                        <label for="ctc_shortcode_icon"><?php esc_html_e( 'WhatsApp Icon', 'click-to-chat' ); ?></label>
                    </th>
                    <td>
                        <select id="ctc_shortcode_icon" class="ctc-shortcode-param" data-param="icon">
                            <option value="yes"><?php esc_html_e( 'Show icon', 'click-to-chat' ); ?></option>
                            <option value="no"><?php esc_html_e( 'Hide icon', 'click-to-chat' ); ?></option>
                        </select>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row">
                        <label for="ctc_shortcode_bg_color"><?php esc_html_e( 'Background Color', 'click-to-chat' ); ?></label>
                    </th>
                    <td>
                        <input type="text" id="ctc_shortcode_bg_color" class="ctc-color-field ctc-shortcode-param" data-param="bg_color" value="<?php echo esc_attr( $default_bg_color ); ?>">
                        <p class="description"><?php esc_html_e( 'Leave empty to use default color from settings.', 'click-to-chat' ); ?></p>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row">
                        <label for="ctc_shortcode_text_color"><?php esc_html_e( 'Text Color', 'click-to-chat' ); ?></label>
                    </th>
                    <td>
                        <input type="text" id="ctc_shortcode_text_color" class="ctc-color-field ctc-shortcode-param" data-param="text_color" value="<?php echo esc_attr( $default_text_color ); ?>">
                        <p class="description"><?php esc_html_e( 'Leave empty to use default color from settings.', 'click-to-chat' ); ?></p>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row">
                        <label for="ctc_shortcode_size"><?php esc_html_e( 'Button Size', 'click-to-chat' ); ?></label>
                    </th>
                    <td>
                        <select id="ctc_shortcode_size" class="ctc-shortcode-param" data-param="size">
                            <option value="small"><?php esc_html_e( 'Small', 'click-to-chat' ); ?></option>
                            <option value="normal" selected><?php esc_html_e( 'Normal', 'click-to-chat' ); ?></option>
                            <option value="large"><?php esc_html_e( 'Large', 'click-to-chat' ); ?></option>
                        </select>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row">
                        <label for="ctc_shortcode_align"><?php esc_html_e( 'Button Alignment', 'click-to-chat' ); ?></label>
                    </th>
                    <td>
                        <select id="ctc_shortcode_align" class="ctc-shortcode-param" data-param="align">
                            <option value="left"><?php esc_html_e( 'Left', 'click-to-chat' ); ?></option>
                            <option value="center" selected><?php esc_html_e( 'Center', 'click-to-chat' ); ?></option>
                            <option value="right"><?php esc_html_e( 'Right', 'click-to-chat' ); ?></option>
                        </select>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row">
                        <label for="ctc_shortcode_custom_class"><?php esc_html_e( 'Custom CSS Class', 'click-to-chat' ); ?></label>
                    </th>
                    <td>
                        <input type="text" id="ctc_shortcode_custom_class" class="regular-text ctc-shortcode-param" data-param="css_class">
                        <p class="description"><?php esc_html_e( 'Optional: Add custom CSS class to the button for additional styling.', 'click-to-chat' ); ?></p>
                    </td>
                </tr>
                
                <tr class="ctc-advanced-params">
                    <th scope="row">
                        <label for="ctc_shortcode_show_number"><?php esc_html_e( 'Specific WhatsApp Number', 'click-to-chat' ); ?></label>
                    </th>
                    <td>
                        <select id="ctc_shortcode_show_number" class="ctc-shortcode-param" data-param="show_number">
                            <option value=""><?php esc_html_e( 'Use default assignment rules', 'click-to-chat' ); ?></option>
                            <?php foreach ( $whatsapp_numbers as $number ) : ?>
                                <option value="<?php echo esc_attr( $number['id'] ); ?>"><?php echo esc_html( $number['name'] . ' (' . $number['number'] . ')' ); ?></option>
                            <?php endforeach; ?>
                        </select>
                        <p class="description"><?php esc_html_e( 'Optional: Force the button to use a specific WhatsApp number.', 'click-to-chat' ); ?></p>
                    </td>
                </tr>
                
                <tr class="ctc-advanced-params">
                    <th scope="row">
                        <label for="ctc_shortcode_message"><?php esc_html_e( 'Custom Message', 'click-to-chat' ); ?></label>
                    </th>
                    <td>
                        <textarea id="ctc_shortcode_message" class="large-text ctc-shortcode-param" data-param="message" rows="4"></textarea>
                        <p class="description"><?php esc_html_e( 'Optional: Custom message template. Leave empty to use default messages from settings.', 'click-to-chat' ); ?></p>
                    </td>
                </tr>
            </table>
        </div>
        
        <div class="ctc-shortcode-preview">
            <h3><?php esc_html_e( 'Your Shortcode', 'click-to-chat' ); ?></h3>
            
            <div class="ctc-shortcode-result">
                <pre class="ctc-shortcode-code">[whatsapp_button]</pre>
                <span class="ctc-copy-shortcode dashicons dashicons-clipboard" title="<?php esc_attr_e( 'Copy to clipboard', 'click-to-chat' ); ?>"></span>
            </div>
            
            <div class="ctc-shortcode-preview-container">
                <p class="ctc-shortcode-preview-label"><?php esc_html_e( 'Button Preview', 'click-to-chat' ); ?></p>
                <div class="ctc-shortcode-preview-content">
                    <!-- Live preview of the button will be displayed here by JavaScript -->
                </div>
            </div>
            
            <div class="ctc-shortcode-usage">
                <h4><?php esc_html_e( 'How to Use This Shortcode', 'click-to-chat' ); ?></h4>
                <p><?php esc_html_e( 'Copy the shortcode above and paste it into your post, page, or text widget content where you want the WhatsApp button to appear.', 'click-to-chat' ); ?></p>
                <p><strong><?php esc_html_e( 'Examples:', 'click-to-chat' ); ?></strong></p>
                <ul>
                    <li><?php esc_html_e( 'To display a button for the current product:', 'click-to-chat' ); ?> <code>[whatsapp_button]</code></li>
                    <li><?php esc_html_e( 'To display a button for a specific product:', 'click-to-chat' ); ?> <code>[whatsapp_button product_id="123" current="no"]</code></li>
                    <li><?php esc_html_e( 'To display a cart button with custom text:', 'click-to-chat' ); ?> <code>[whatsapp_button type="cart" text="Order Now via WhatsApp"]</code></li>
                </ul>
            </div>
        </div>
    </div>
</div>