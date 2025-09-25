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

// Get plugin settings
$settings = get_option( 'ctc_settings', array() );

// Get settings helper
$settings_helper = new CTC_Settings();

// Get position options
$product_positions = $settings_helper->get_product_position_options();
$shop_positions = $settings_helper->get_shop_position_options();
$floating_positions = $settings_helper->get_floating_position_options();
?>

<div class="wrap ctc-admin-container">
    <div class="ctc-admin-header">
        <span class="ctc-admin-logo dashicons dashicons-whatsapp"></span>
        <h1 class="ctc-admin-heading"><?php esc_html_e( 'Click to Chat', 'click-to-chat' ); ?></h1>
    </div>
    
    <h2 class="nav-tab-wrapper ctc-admin-tabs">
        <a href="#" class="nav-tab" data-tab="ctc-tab-general"><?php esc_html_e( 'General Settings', 'click-to-chat' ); ?></a>
        <a href="#" class="nav-tab" data-tab="ctc-tab-button"><?php esc_html_e( 'Button Settings', 'click-to-chat' ); ?></a>
        <a href="#" class="nav-tab" data-tab="ctc-tab-display"><?php esc_html_e( 'Display Settings', 'click-to-chat' ); ?></a>
        <a href="#" class="nav-tab" data-tab="ctc-tab-exclusions"><?php esc_html_e( 'Exclusions', 'click-to-chat' ); ?></a>
    </h2>
    
    <form method="post" action="">
        <?php wp_nonce_field( 'ctc_settings_nonce', 'ctc_settings_nonce' ); ?>
        
        <!-- General Settings Tab -->
        <div id="ctc-tab-general" class="ctc-admin-tab-content ctc-admin-content">
            <h2><?php esc_html_e( 'General Settings', 'click-to-chat' ); ?></h2>
            
            <p><?php esc_html_e( 'Configure the general settings for the Click to Chat plugin.', 'click-to-chat' ); ?></p>
            
            <table class="form-table ctc-form-table">
                <tr>
                    <th scope="row">
                        <label><?php esc_html_e( 'Plugin Status', 'click-to-chat' ); ?></label>
                    </th>
                    <td>
                        <fieldset>
                            <label for="ctc_plugin_enabled">
                                <input type="checkbox" name="ctc_plugin_enabled" id="ctc_plugin_enabled" value="1" <?php checked( isset( $settings['plugin_enabled'] ) ? $settings['plugin_enabled'] : true, true ); ?>>
                                <?php esc_html_e( 'Enable plugin functionality', 'click-to-chat' ); ?>
                            </label>
                        </fieldset>
                        <p class="description"><?php esc_html_e( 'When disabled, no WhatsApp buttons will be displayed.', 'click-to-chat' ); ?></p>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row">
                        <label><?php esc_html_e( 'WhatsApp Numbers', 'click-to-chat' ); ?></label>
                    </th>
                    <td>
                        <p>
                            <?php esc_html_e( 'Configure your WhatsApp numbers in the', 'click-to-chat' ); ?> 
                            <a href="<?php echo esc_url( admin_url( 'admin.php?page=click-to-chat-numbers' ) ); ?>"><?php esc_html_e( 'WhatsApp Numbers', 'click-to-chat' ); ?></a>
                            <?php esc_html_e( 'section.', 'click-to-chat' ); ?>
                        </p>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row">
                        <label><?php esc_html_e( 'Message Templates', 'click-to-chat' ); ?></label>
                    </th>
                    <td>
                        <p>
                            <?php esc_html_e( 'Configure your message templates in the', 'click-to-chat' ); ?> 
                            <a href="<?php echo esc_url( admin_url( 'admin.php?page=click-to-chat-templates' ) ); ?>"><?php esc_html_e( 'Message Templates', 'click-to-chat' ); ?></a>
                            <?php esc_html_e( 'section.', 'click-to-chat' ); ?>
                        </p>
                    </td>
                </tr>
            </table>
        </div>
        
        <!-- Button Settings Tab -->
        <div id="ctc-tab-button" class="ctc-admin-tab-content ctc-admin-content">
            <h2><?php esc_html_e( 'Button Settings', 'click-to-chat' ); ?></h2>
            
            <p><?php esc_html_e( 'Configure the appearance of the WhatsApp button.', 'click-to-chat' ); ?></p>
            
            <table class="form-table ctc-form-table">
                <tr>
                    <th scope="row">
                        <label for="ctc_button_text"><?php esc_html_e( 'Button Text', 'click-to-chat' ); ?></label>
                    </th>
                    <td>
                        <input type="text" name="ctc_button[text]" id="ctc_button_text" value="<?php echo esc_attr( isset( $settings['button_settings']['text'] ) ? $settings['button_settings']['text'] : esc_html__( 'Order via WhatsApp', 'click-to-chat' ) ); ?>" class="regular-text">
                        <p class="description"><?php esc_html_e( 'The text to display on the WhatsApp button.', 'click-to-chat' ); ?></p>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row">
                        <label for="ctc_button_icon"><?php esc_html_e( 'Display WhatsApp Icon', 'click-to-chat' ); ?></label>
                    </th>
                    <td>
                        <fieldset>
                            <label for="ctc_button_icon">
                                <input type="checkbox" name="ctc_button[icon]" id="ctc_button_icon" value="1" <?php checked( isset( $settings['button_settings']['icon'] ) ? $settings['button_settings']['icon'] : true ); ?>>
                                <?php esc_html_e( 'Show WhatsApp icon on the button', 'click-to-chat' ); ?>
                            </label>
                        </fieldset>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row">
                        <label for="ctc_button_bg_color"><?php esc_html_e( 'Button Background Color', 'click-to-chat' ); ?></label>
                    </th>
                    <td>
                        <input type="text" name="ctc_button[bg_color]" id="ctc_button_bg_color" value="<?php echo esc_attr( isset( $settings['button_settings']['bg_color'] ) ? $settings['button_settings']['bg_color'] : '#25D366' ); ?>" class="ctc-color-field">
                    </td>
                </tr>
                
                <tr>
                    <th scope="row">
                        <label for="ctc_button_text_color"><?php esc_html_e( 'Button Text Color', 'click-to-chat' ); ?></label>
                    </th>
                    <td>
                        <input type="text" name="ctc_button[text_color]" id="ctc_button_text_color" value="<?php echo esc_attr( isset( $settings['button_settings']['text_color'] ) ? $settings['button_settings']['text_color'] : '#ffffff' ); ?>" class="ctc-color-field">
                    </td>
                </tr>
                
                <tr>
                    <th scope="row">
                        <label for="ctc_button_custom_css"><?php esc_html_e( 'Custom CSS', 'click-to-chat' ); ?></label>
                    </th>
                    <td>
                        <textarea name="ctc_button[custom_css]" id="ctc_button_custom_css" rows="8" class="large-text code"><?php echo esc_textarea( isset( $settings['button_settings']['custom_css'] ) ? $settings['button_settings']['custom_css'] : '' ); ?></textarea>
                        <p class="description"><?php esc_html_e( 'Add custom CSS for further customization of the button appearance.', 'click-to-chat' ); ?></p>
                    </td>
                </tr>
            </table>
        </div>
        
        <!-- Display Settings Tab -->
        <div id="ctc-tab-display" class="ctc-admin-tab-content ctc-admin-content">
            <h2><?php esc_html_e( 'Display Settings', 'click-to-chat' ); ?></h2>
            
            <p><?php esc_html_e( 'Configure where the WhatsApp button should be displayed.', 'click-to-chat' ); ?></p>
            
            <h3><?php esc_html_e( 'Single Product Pages', 'click-to-chat' ); ?></h3>
            <table class="form-table ctc-form-table">
                <tr>
                    <th scope="row">
                        <label for="ctc_single_product_enabled"><?php esc_html_e( 'Display on Single Product Pages', 'click-to-chat' ); ?></label>
                    </th>
                    <td>
                        <fieldset>
                            <label for="ctc_single_product_enabled">
                                <input type="checkbox" name="ctc_single_product[enabled]" id="ctc_single_product_enabled" value="1" <?php checked( isset( $settings['single_product']['enabled'] ) ? $settings['single_product']['enabled'] : true ); ?>>
                                <?php esc_html_e( 'Show WhatsApp button on single product pages', 'click-to-chat' ); ?>
                            </label>
                        </fieldset>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row">
                        <label for="ctc_single_product_position"><?php esc_html_e( 'Button Position', 'click-to-chat' ); ?></label>
                    </th>
                    <td>
                        <select name="ctc_single_product[position]" id="ctc_single_product_position">
                            <?php foreach ( $product_positions as $value => $label ) : ?>
                                <option value="<?php echo esc_attr( $value ); ?>" <?php selected( isset( $settings['single_product']['position'] ) ? $settings['single_product']['position'] : 'after_add_to_cart', $value ); ?>><?php echo esc_html( $label ); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </td>
                </tr>
            </table>
            
            <h3><?php esc_html_e( 'Shop Pages', 'click-to-chat' ); ?></h3>
            <table class="form-table ctc-form-table">
                <tr>
                    <th scope="row">
                        <label for="ctc_shop_page_enabled"><?php esc_html_e( 'Display on Shop Pages', 'click-to-chat' ); ?></label>
                    </th>
                    <td>
                        <fieldset>
                            <label for="ctc_shop_page_enabled">
                                <input type="checkbox" name="ctc_shop_page[enabled]" id="ctc_shop_page_enabled" value="1" <?php checked( isset( $settings['shop_page']['enabled'] ) ? $settings['shop_page']['enabled'] : false ); ?>>
                                <?php esc_html_e( 'Show WhatsApp button on shop/archive pages', 'click-to-chat' ); ?>
                            </label>
                        </fieldset>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row">
                        <label for="ctc_shop_page_position"><?php esc_html_e( 'Button Position', 'click-to-chat' ); ?></label>
                    </th>
                    <td>
                        <select name="ctc_shop_page[position]" id="ctc_shop_page_position">
                            <?php foreach ( $shop_positions as $value => $label ) : ?>
                                <option value="<?php echo esc_attr( $value ); ?>" <?php selected( isset( $settings['shop_page']['position'] ) ? $settings['shop_page']['position'] : 'after_add_to_cart', $value ); ?>><?php echo esc_html( $label ); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </td>
                </tr>
            </table>
            
            <h3><?php esc_html_e( 'Cart & Checkout Pages', 'click-to-chat' ); ?></h3>
            <table class="form-table ctc-form-table">
                <tr>
                    <th scope="row">
                        <label for="ctc_cart_page_enabled"><?php esc_html_e( 'Display on Cart Page', 'click-to-chat' ); ?></label>
                    </th>
                    <td>
                        <fieldset>
                            <label for="ctc_cart_page_enabled">
                                <input type="checkbox" name="ctc_cart_page[enabled]" id="ctc_cart_page_enabled" value="1" <?php checked( isset( $settings['cart_page']['enabled'] ) ? $settings['cart_page']['enabled'] : false ); ?>>
                                <?php esc_html_e( 'Show WhatsApp button on the cart page', 'click-to-chat' ); ?>
                            </label>
                        </fieldset>
                    </td>
                </tr>

                <tr class="ctc-cart-position-row" <?php echo ( ! isset( $settings['cart_page']['enabled'] ) || ! $settings['cart_page']['enabled'] ) ? 'style="display:none;"' : ''; ?>>
                    <th scope="row">
                        <label for="ctc_cart_page_position"><?php esc_html_e( 'Cart Button Position', 'click-to-chat' ); ?></label>
                    </th>
                    <td>
                        <select name="ctc_cart_page[position]" id="ctc_cart_page_position">
                            <option value="after_cart_table" <?php selected( isset( $settings['cart_page']['position'] ) ? $settings['cart_page']['position'] : 'after_cart_table', 'after_cart_table' ); ?>><?php esc_html_e( 'After Cart Table', 'click-to-chat' ); ?></option>
                            <option value="before_cart_table" <?php selected( isset( $settings['cart_page']['position'] ) ? $settings['cart_page']['position'] : '', 'before_cart_table' ); ?>><?php esc_html_e( 'Before Cart Table', 'click-to-chat' ); ?></option>
                            <option value="proceed_to_checkout" <?php selected( isset( $settings['cart_page']['position'] ) ? $settings['cart_page']['position'] : '', 'proceed_to_checkout' ); ?>><?php esc_html_e( 'Next to Proceed to Checkout Button', 'click-to-chat' ); ?></option>
                            <option value="after_cart_totals" <?php selected( isset( $settings['cart_page']['position'] ) ? $settings['cart_page']['position'] : '', 'after_cart_totals' ); ?>><?php esc_html_e( 'After Cart Totals', 'click-to-chat' ); ?></option>
                            <option value="cart_actions" <?php selected( isset( $settings['cart_page']['position'] ) ? $settings['cart_page']['position'] : '', 'cart_actions' ); ?>><?php esc_html_e( 'In Cart Actions Area', 'click-to-chat' ); ?></option>
                        </select>
                        <p class="description"><?php esc_html_e( 'Choose where to display the WhatsApp button on the cart page', 'click-to-chat' ); ?></p>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row">
                        <label for="ctc_checkout_page_enabled"><?php esc_html_e( 'Display on Checkout Page', 'click-to-chat' ); ?></label>
                    </th>
                    <td>
                        <fieldset>
                            <label for="ctc_checkout_page_enabled">
                                <input type="checkbox" name="ctc_checkout_page[enabled]" id="ctc_checkout_page_enabled" value="1" <?php checked( isset( $settings['checkout_page']['enabled'] ) ? $settings['checkout_page']['enabled'] : false ); ?>>
                                <?php esc_html_e( 'Show WhatsApp button on the checkout page', 'click-to-chat' ); ?>
                            </label>
                        </fieldset>
                    </td>
                </tr>

                <tr class="ctc-checkout-position-row" <?php echo ( ! isset( $settings['checkout_page']['enabled'] ) || ! $settings['checkout_page']['enabled'] ) ? 'style="display:none;"' : ''; ?>>
                    <th scope="row">
                        <label for="ctc_checkout_page_position"><?php esc_html_e( 'Checkout Button Position', 'click-to-chat' ); ?></label>
                    </th>
                    <td>
                        <select name="ctc_checkout_page[position]" id="ctc_checkout_page_position">
                            <option value="after_payment" <?php selected( isset( $settings['checkout_page']['position'] ) ? $settings['checkout_page']['position'] : 'after_payment', 'after_payment' ); ?>><?php esc_html_e( 'After Payment Methods', 'click-to-chat' ); ?></option>
                            <option value="before_payment" <?php selected( isset( $settings['checkout_page']['position'] ) ? $settings['checkout_page']['position'] : '', 'before_payment' ); ?>><?php esc_html_e( 'Before Payment Methods', 'click-to-chat' ); ?></option>
                            <option value="after_order_review" <?php selected( isset( $settings['checkout_page']['position'] ) ? $settings['checkout_page']['position'] : '', 'after_order_review' ); ?>><?php esc_html_e( 'After Order Review', 'click-to-chat' ); ?></option>
                            <option value="before_order_review" <?php selected( isset( $settings['checkout_page']['position'] ) ? $settings['checkout_page']['position'] : '', 'before_order_review' ); ?>><?php esc_html_e( 'Before Order Review', 'click-to-chat' ); ?></option>
                            <option value="after_submit" <?php selected( isset( $settings['checkout_page']['position'] ) ? $settings['checkout_page']['position'] : '', 'after_submit' ); ?>><?php esc_html_e( 'After Place Order Button', 'click-to-chat' ); ?></option>
                        </select>
                        <p class="description"><?php esc_html_e( 'Choose where to display the WhatsApp button on the checkout page', 'click-to-chat' ); ?></p>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row">
                        <label for="ctc_thankyou_page_enabled"><?php esc_html_e( 'Display on Thank You Page', 'click-to-chat' ); ?></label>
                    </th>
                    <td>
                        <fieldset>
                            <label for="ctc_thankyou_page_enabled">
                                <input type="checkbox" name="ctc_thankyou_page[enabled]" id="ctc_thankyou_page_enabled" value="1" <?php checked( isset( $settings['thankyou_page']['enabled'] ) ? $settings['thankyou_page']['enabled'] : false ); ?>>
                                <?php esc_html_e( 'Show WhatsApp button on the thank you/order confirmation page', 'click-to-chat' ); ?>
                            </label>
                        </fieldset>
                    </td>
                </tr>
            </table>
            
            <h3><?php esc_html_e( 'Floating Button', 'click-to-chat' ); ?></h3>
            <table class="form-table ctc-form-table">
                <tr>
                    <th scope="row">
                        <label for="ctc_floating_button_enabled"><?php esc_html_e( 'Display Floating Button', 'click-to-chat' ); ?></label>
                    </th>
                    <td>
                        <fieldset>
                            <label for="ctc_floating_button_enabled">
                                <input type="checkbox" name="ctc_floating_button[enabled]" id="ctc_floating_button_enabled" value="1" <?php checked( isset( $settings['floating_button']['enabled'] ) ? $settings['floating_button']['enabled'] : false ); ?>>
                                <?php esc_html_e( 'Show a floating WhatsApp button on all pages', 'click-to-chat' ); ?>
                            </label>
                        </fieldset>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row">
                        <label for="ctc_floating_button_position"><?php esc_html_e( 'Button Position', 'click-to-chat' ); ?></label>
                    </th>
                    <td>
                        <select name="ctc_floating_button[position]" id="ctc_floating_button_position">
                            <?php foreach ( $floating_positions as $value => $label ) : ?>
                                <option value="<?php echo esc_attr( $value ); ?>" <?php selected( isset( $settings['floating_button']['position'] ) ? $settings['floating_button']['position'] : 'bottom_right', $value ); ?>><?php echo esc_html( $label ); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </td>
                </tr>
            </table>
        </div>
        
        <!-- Exclusions Tab -->
        <div id="ctc-tab-exclusions" class="ctc-admin-tab-content ctc-admin-content">
            <h2><?php esc_html_e( 'Exclusions', 'click-to-chat' ); ?></h2>
            
            <p><?php esc_html_e( 'Configure where the WhatsApp button should NOT be displayed.', 'click-to-chat' ); ?></p>
            
            <table class="form-table ctc-form-table">
                <tr>
                    <th scope="row">
                        <label for="ctc_exclude_pages"><?php esc_html_e( 'Exclude Pages', 'click-to-chat' ); ?></label>
                    </th>
                    <td>
                        <select name="ctc_exclusions[pages][]" id="ctc_exclude_pages" class="ctc-page-select" multiple="multiple" style="width: 100%;">
                            <?php
                            // Show selected pages
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
                        <p class="description"><?php esc_html_e( 'Select pages where the WhatsApp button should not appear.', 'click-to-chat' ); ?></p>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row">
                        <label for="ctc_exclude_posts"><?php esc_html_e( 'Exclude Posts', 'click-to-chat' ); ?></label>
                    </th>
                    <td>
                        <select name="ctc_exclusions[posts][]" id="ctc_exclude_posts" class="ctc-post-select" multiple="multiple" style="width: 100%;">
                            <?php
                            // Show selected posts
                            if ( isset( $settings['exclusions']['posts'] ) && is_array( $settings['exclusions']['posts'] ) ) {
                                foreach ( $settings['exclusions']['posts'] as $post_id ) {
                                    $post_title = get_the_title( $post_id );
                                    if ( $post_title ) {
                                        echo '<option value="' . esc_attr( $post_id ) . '" selected>' . esc_html( $post_title ) . '</option>';
                                    }
                                }
                            }
                            ?>
                        </select>
                        <p class="description"><?php esc_html_e( 'Select posts where the WhatsApp button should not appear.', 'click-to-chat' ); ?></p>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row">
                        <label for="ctc_exclude_categories"><?php esc_html_e( 'Exclude Product Categories', 'click-to-chat' ); ?></label>
                    </th>
                    <td>
                        <select name="ctc_exclusions[categories][]" id="ctc_exclude_categories" class="ctc-category-select" multiple="multiple" style="width: 100%;">
                            <?php
                            // Show selected categories
                            if ( isset( $settings['exclusions']['categories'] ) && is_array( $settings['exclusions']['categories'] ) ) {
                                foreach ( $settings['exclusions']['categories'] as $term_id ) {
                                    $term = get_term( $term_id, 'product_cat' );
                                    if ( $term && ! is_wp_error( $term ) ) {
                                        echo '<option value="' . esc_attr( $term->term_id ) . '" selected>' . esc_html( $term->name ) . '</option>';
                                    }
                                }
                            }
                            ?>
                        </select>
                        <p class="description"><?php esc_html_e( 'Select product categories where the WhatsApp button should not appear.', 'click-to-chat' ); ?></p>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row">
                        <label for="ctc_exclude_tags"><?php esc_html_e( 'Exclude Product Tags', 'click-to-chat' ); ?></label>
                    </th>
                    <td>
                        <select name="ctc_exclusions[tags][]" id="ctc_exclude_tags" class="ctc-tag-select" multiple="multiple" style="width: 100%;">
                            <?php
                            // Show selected tags
                            if ( isset( $settings['exclusions']['tags'] ) && is_array( $settings['exclusions']['tags'] ) ) {
                                foreach ( $settings['exclusions']['tags'] as $term_id ) {
                                    $term = get_term( $term_id, 'product_tag' );
                                    if ( $term && ! is_wp_error( $term ) ) {
                                        echo '<option value="' . esc_attr( $term->term_id ) . '" selected>' . esc_html( $term->name ) . '</option>';
                                    }
                                }
                            }
                            ?>
                        </select>
                        <p class="description"><?php esc_html_e( 'Select product tags where the WhatsApp button should not appear.', 'click-to-chat' ); ?></p>
                    </td>
                </tr>

                <tr>
                    <th scope="row">
                        <label for="ctc_exclude_products"><?php esc_html_e( 'Exclude Products', 'click-to-chat' ); ?></label>
                    </th>
                    <td>
                        <select name="ctc_exclusions[products][]" id="ctc_exclude_products" class="ctc-product-select" multiple="multiple" style="width: 100%;">
                            <?php
                            // Show selected products
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
                        <p class="description"><?php esc_html_e( 'Select specific products where the WhatsApp button should not appear.', 'click-to-chat' ); ?></p>
                    </td>
                </tr>
            </table>
        </div>
        
        <p class="submit">
            <input type="submit" name="ctc_save_settings" class="button button-primary" value="<?php esc_attr_e( 'Save Settings', 'click-to-chat' ); ?>">
        </p>
    </form>
</div>