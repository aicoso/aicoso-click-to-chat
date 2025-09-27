<?php
/**
 * Admin class.
 *
 * This class handles all admin-related functionality.
 *
 * @package ClickToChat
 * @since 1.0.0
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
    die;
}

/**
 * Admin class.
 */
class CTC_Admin {

    /**
     * Plugin settings
     *
     * @var array
     */
    private $settings;

    /**
     * Flag to track if settings page has been rendered
     *
     * @var bool
     */
    private static $settings_page_rendered = false;

    /**
     * Constructor
     */
    public function __construct() {
        $this->settings = get_option( 'ctc_settings', array() );
        
        // Initialize hooks
        $this->init_hooks();
    }

    /**
     * Initialize hooks
     */
    private function init_hooks() {
        // Add admin menu
        add_action( 'admin_menu', array( $this, 'add_admin_menu' ) );
        
        // Register admin assets
        add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_assets' ) );
        
        // Add plugin action links
        add_filter( 'plugin_action_links_' . CTC_PLUGIN_BASENAME, array( $this, 'add_action_links' ) );
        
        // Add meta box to product edit screen
        add_action( 'add_meta_boxes', array( $this, 'add_product_meta_boxes' ) );
        
        // Save product meta
        add_action( 'save_post_product', array( $this, 'save_product_meta' ), 10, 2 );
    }

    /**
     * Add admin menu
     */
    public function add_admin_menu() {
        add_menu_page(
            esc_html__( 'Click to Chat', 'click-to-chat' ),
            esc_html__( 'Click to Chat', 'click-to-chat' ),
            'manage_options',
            'click-to-chat',
            array( $this, 'render_settings_page' ),
            'dashicons-whatsapp', // Custom dashicon for WhatsApp
            58 // Position after WooCommerce
        );
        
        // Add submenus
        add_submenu_page(
            'click-to-chat',
            esc_html__( 'Settings', 'click-to-chat' ),
            esc_html__( 'Settings', 'click-to-chat' ),
            'manage_options',
            'click-to-chat',
            array( $this, 'render_settings_page' )
        );
        
        add_submenu_page(
            'click-to-chat',
            esc_html__( 'WhatsApp Numbers', 'click-to-chat' ),
            esc_html__( 'WhatsApp Numbers', 'click-to-chat' ),
            'manage_options',
            'click-to-chat-numbers',
            array( $this, 'render_numbers_page' )
        );
        
        add_submenu_page(
            'click-to-chat',
            esc_html__( 'Message Templates', 'click-to-chat' ),
            esc_html__( 'Message Templates', 'click-to-chat' ),
            'manage_options',
            'click-to-chat-templates',
            array( $this, 'render_templates_page' )
        );
        
        add_submenu_page(
            'click-to-chat',
            esc_html__( 'Shortcode Generator', 'click-to-chat' ),
            esc_html__( 'Shortcode Generator', 'click-to-chat' ),
            'manage_options',
            'click-to-chat-shortcodes',
            array( $this, 'render_shortcode_page' )
        );
    }

    /**
     * Enqueue admin assets
     *
     * @param string $hook Current admin page hook.
     */
    public function enqueue_admin_assets($hook) {
        // Only enqueue on plugin admin pages
        if (strpos($hook, 'click-to-chat') === false) {
            return;
        }
        
        // CSS
        wp_enqueue_style('wp-color-picker');
        
        // Enqueue Select2 from local files
        wp_enqueue_style(
            'select2-css',
            CTC_PLUGIN_URL . 'admin/lib/select2/select2.min.css',
            array(),
            '4.0.13'
        );
        
        wp_enqueue_style(
            'ctc-admin-styles',
            CTC_PLUGIN_URL . 'admin/css/admin.css',
            array('wp-color-picker', 'select2-css'),
            CTC_VERSION
        );
        
        // JavaScript
        // Enqueue Select2 from local files
        wp_enqueue_script(
            'select2-js',
            CTC_PLUGIN_URL . 'admin/lib/select2/select2.min.js',
            array('jquery'),
            '4.0.13',
            true
        );
        
        wp_enqueue_script('wp-color-picker');
        wp_enqueue_script('jquery-ui-sortable');
        
        wp_enqueue_script(
            'ctc-admin-js',
            CTC_PLUGIN_URL . 'admin/js/admin.js',
            array('jquery', 'wp-color-picker', 'select2-js', 'jquery-ui-sortable'),
            CTC_VERSION,
            true
        );
        
        // Pass variables to JavaScript
        wp_localize_script(
            'ctc-admin-js',
            'ctc_admin',
            array(
                'ajaxurl' => admin_url('admin-ajax.php'),
                'ajax_url' => admin_url('admin-ajax.php'),
                'nonce'    => wp_create_nonce('ctc_admin_nonce'),
                'delete_number_confirm' => esc_html__('Are you sure you want to delete this WhatsApp number?', 'click-to-chat'),
                'duplicate_name_error' => esc_html__('This name is already being used. Please choose a different name.', 'click-to-chat'),
                'loading_text' => esc_html__('Loading...', 'click-to-chat'),
                'preview_text' => esc_html__('Preview', 'click-to-chat'),
                'default_button_text' => esc_html__('Chat with us', 'click-to-chat'),
                'copy_success' => esc_html__('Shortcode copied to clipboard!', 'click-to-chat'),
                'copy_error' => esc_html__('Failed to copy shortcode. Please select and copy manually.', 'click-to-chat'),
                'select_products_text' => esc_html__('Select products...', 'click-to-chat'),
                'select_categories_text' => esc_html__('Select categories...', 'click-to-chat'),
                'select_pages_text' => esc_html__('Select pages...', 'click-to-chat'),
                'i18n'     => array(
                    'confirm_delete'   => esc_html__('Are you sure you want to delete this WhatsApp number?', 'click-to-chat'),
                    'number_required'  => esc_html__('WhatsApp number is required.', 'click-to-chat'),
                    'name_required'    => esc_html__('Name is required.', 'click-to-chat'),
                    'select_products'  => esc_html__('Select products...', 'click-to-chat'),
                    'select_categories'=> esc_html__('Select categories...', 'click-to-chat'),
                    'select_pages'     => esc_html__('Select pages...', 'click-to-chat'),
                ),
            )
        );
    }

    /**
     * Add action links to the plugins page
     *
     * @param array $links Plugin action links.
     * @return array Modified action links.
     */
    public function add_action_links( $links ) {
        $plugin_links = array(
            '<a href="' . admin_url( 'admin.php?page=click-to-chat' ) . '">' . esc_html__( 'Settings', 'click-to-chat' ) . '</a>',
        );
        
        return array_merge( $plugin_links, $links );
    }

    /**
     * Render the main settings page
     */
    public function render_settings_page() {
        // Check permissions
        if ( ! current_user_can( 'manage_options' ) ) {
            return;
        }
        
        // Prevent duplicate rendering
        if ( self::$settings_page_rendered ) {
            return;
        }
        
        // Mark as rendered
        self::$settings_page_rendered = true;
        
        // Handle form submission
        if ( isset( $_POST['ctc_save_settings'] ) && check_admin_referer( 'ctc_settings_nonce', 'ctc_settings_nonce' ) ) {
            $this->save_settings();
        }
        
        // Include the view file
        include CTC_PLUGIN_DIR . 'admin/views/settings-page.php';
    }

    /**
     * Render the WhatsApp numbers page
     */
    public function render_numbers_page() {
        // Check permissions
        if ( ! current_user_can( 'manage_options' ) ) {
            return;
        }
        
        // Handle form submission
        if ( isset( $_POST['ctc_save_numbers'] ) && check_admin_referer( 'ctc_numbers_nonce', 'ctc_numbers_nonce' ) ) {
            $this->save_numbers();
        }
        
        // Include the view file
        include CTC_PLUGIN_DIR . 'admin/views/numbers-page.php';
    }

    /**
     * Render the message templates page
     */
    public function render_templates_page() {
        // Check permissions
        if ( ! current_user_can( 'manage_options' ) ) {
            return;
        }
        
        // Handle form submission
        if ( isset( $_POST['ctc_save_templates'] ) && check_admin_referer( 'ctc_templates_nonce', 'ctc_templates_nonce' ) ) {
            $this->save_templates();
        }
        
        // Include the view file
        include CTC_PLUGIN_DIR . 'admin/views/templates-page.php';
    }

    /**
     * Render the shortcode generator page
     */
    public function render_shortcode_page() {
        // Check permissions
        if ( ! current_user_can( 'manage_options' ) ) {
            return;
        }
        
        // Include the view file
        include CTC_PLUGIN_DIR . 'admin/views/shortcode-page.php';
    }

    /**
     * Save general settings
     */
    private function save_settings() {
        // Get existing settings
        $settings = get_option( 'ctc_settings', array() );

        // Save plugin enabled status
        $settings['plugin_enabled'] = isset( $_POST['ctc_plugin_enabled'] ) ? true : false;

        // Sanitize and update button settings
        if ( isset( $_POST['ctc_button'] ) && is_array( $_POST['ctc_button'] ) ) {
            $button_settings = array(
                'text'       => sanitize_text_field( $_POST['ctc_button']['text'] ?? '' ),
                'icon'       => isset( $_POST['ctc_button']['icon'] ) ? true : false,
                'bg_color'   => sanitize_hex_color( $_POST['ctc_button']['bg_color'] ?? '#25D366' ),
                'text_color' => sanitize_hex_color( $_POST['ctc_button']['text_color'] ?? '#ffffff' ),
                'custom_css' => sanitize_textarea_field( $_POST['ctc_button']['custom_css'] ?? '' ),
            );
            
            $settings['button_settings'] = $button_settings;
        }
        
        // Sanitize and update single product settings
        if ( isset( $_POST['ctc_single_product'] ) && is_array( $_POST['ctc_single_product'] ) ) {
            $single_product = array(
                'enabled'  => isset( $_POST['ctc_single_product']['enabled'] ) ? true : false,
                'position' => sanitize_text_field( $_POST['ctc_single_product']['position'] ?? 'after_add_to_cart' ),
            );
            
            $settings['single_product'] = $single_product;
        }
        
        // Sanitize and update shop page settings
        if ( isset( $_POST['ctc_shop_page'] ) && is_array( $_POST['ctc_shop_page'] ) ) {
            $shop_page = array(
                'enabled'  => isset( $_POST['ctc_shop_page']['enabled'] ) ? true : false,
                'position' => sanitize_text_field( $_POST['ctc_shop_page']['position'] ?? 'after_add_to_cart' ),
            );
            
            $settings['shop_page'] = $shop_page;
        }
        
        // Sanitize and update cart page settings
        $settings['cart_page'] = array(
            'enabled' => isset( $_POST['ctc_cart_page']['enabled'] ) ? true : false,
            'position' => isset( $_POST['ctc_cart_page']['position'] ) ? sanitize_text_field( $_POST['ctc_cart_page']['position'] ) : 'after_cart_table',
        );

        // Sanitize and update checkout page settings
        $settings['checkout_page'] = array(
            'enabled' => isset( $_POST['ctc_checkout_page']['enabled'] ) ? true : false,
            'position' => isset( $_POST['ctc_checkout_page']['position'] ) ? sanitize_text_field( $_POST['ctc_checkout_page']['position'] ) : 'after_payment',
        );
        
        // Sanitize and update thank you page settings
        $settings['thankyou_page'] = array(
            'enabled' => isset( $_POST['ctc_thankyou_page']['enabled'] ) ? true : false,
        );
        
        // Sanitize and update floating button settings
        if ( isset( $_POST['ctc_floating_button'] ) && is_array( $_POST['ctc_floating_button'] ) ) {
            $floating_button = array(
                'enabled'  => isset( $_POST['ctc_floating_button']['enabled'] ) ? true : false,
                'position' => sanitize_text_field( $_POST['ctc_floating_button']['position'] ?? 'bottom_right' ),
            );
            
            $settings['floating_button'] = $floating_button;
        }
        
        // Sanitize and update exclusions
        // Always reset exclusions to ensure removed items are cleared
        $exclusions = array(
            'pages'      => array(),
            'posts'      => array(),
            'categories' => array(),
            'tags'       => array(),
            'products'   => array(),
        );

        if ( isset( $_POST['ctc_exclusions'] ) && is_array( $_POST['ctc_exclusions'] ) ) {
            // Pages
            if ( isset( $_POST['ctc_exclusions']['pages'] ) && is_array( $_POST['ctc_exclusions']['pages'] ) ) {
                foreach ( $_POST['ctc_exclusions']['pages'] as $page_id ) {
                    $exclusions['pages'][] = absint( $page_id );
                }
            }

            // Posts
            if ( isset( $_POST['ctc_exclusions']['posts'] ) && is_array( $_POST['ctc_exclusions']['posts'] ) ) {
                foreach ( $_POST['ctc_exclusions']['posts'] as $post_id ) {
                    $exclusions['posts'][] = absint( $post_id );
                }
            }

            // Categories
            if ( isset( $_POST['ctc_exclusions']['categories'] ) && is_array( $_POST['ctc_exclusions']['categories'] ) ) {
                foreach ( $_POST['ctc_exclusions']['categories'] as $category_id ) {
                    $exclusions['categories'][] = absint( $category_id );
                }
            }

            // Tags
            if ( isset( $_POST['ctc_exclusions']['tags'] ) && is_array( $_POST['ctc_exclusions']['tags'] ) ) {
                foreach ( $_POST['ctc_exclusions']['tags'] as $tag_id ) {
                    $exclusions['tags'][] = absint( $tag_id );
                }
            }

            // Products
            if ( isset( $_POST['ctc_exclusions']['products'] ) && is_array( $_POST['ctc_exclusions']['products'] ) ) {
                foreach ( $_POST['ctc_exclusions']['products'] as $product_id ) {
                    $exclusions['products'][] = absint( $product_id );
                }
            }
        }

        $settings['exclusions'] = $exclusions;

        // Sanitize and update advanced settings
        $catalog_mode = isset( $_POST['ctc_advanced']['catalog_mode'] ) ? true : false;

        // If catalog mode is enabled, force all hide options to be true
        if ($catalog_mode) {
            $advanced = array(
                'hide_add_to_cart' => true,
                'hide_proceed_checkout' => true,
                'hide_place_order' => true,
                'catalog_mode' => true,
            );
        } else {
            // Otherwise, check individual options
            $advanced = array(
                'hide_add_to_cart' => isset( $_POST['ctc_advanced']['hide_add_to_cart'] ) ? true : false,
                'hide_proceed_checkout' => isset( $_POST['ctc_advanced']['hide_proceed_checkout'] ) ? true : false,
                'hide_place_order' => isset( $_POST['ctc_advanced']['hide_place_order'] ) ? true : false,
                'catalog_mode' => false,
            );
        }

        $settings['advanced'] = $advanced;

        // Update settings
        update_option( 'ctc_settings', $settings );
        
        // Add success message
        add_settings_error(
            'ctc_settings',
            'ctc_settings_updated',
            esc_html__( 'Settings saved successfully.', 'click-to-chat' ),
            'updated'
        );
    }

    /**
     * Save WhatsApp numbers
     */
    private function save_numbers() {
        // Get existing settings
        $settings = get_option( 'ctc_settings', array() );
        
        // Initialize empty array for WhatsApp numbers
        $whatsapp_numbers = array();
        
        // Process deleted numbers
        $deleted_numbers = array();
        if ( isset( $_POST['ctc_deleted_numbers'] ) && ! empty( $_POST['ctc_deleted_numbers'] ) ) {
            $deleted_numbers = array_map( 'absint', explode( ',', sanitize_text_field( $_POST['ctc_deleted_numbers'] ) ) );
        }
        
        // Check for duplicate names
        $number_names = array();
        $has_duplicate = false;
        
        // Process submitted numbers
        if ( isset( $_POST['ctc_numbers'] ) && is_array( $_POST['ctc_numbers'] ) ) {
            foreach ( $_POST['ctc_numbers'] as $number_data ) {
                // Get the number ID
                $number_id = absint( $number_data['id'] ?? 0 );
                
                // Skip this number if it was deleted
                if ( in_array( $number_id, $deleted_numbers ) ) {
                    continue;
                }
                
                // Check for duplicate names
                $name = sanitize_text_field( $number_data['name'] ?? '' );
                if ( in_array( $name, $number_names ) ) {
                    // We found a duplicate name
                    $has_duplicate = true;
                    continue; // Skip this number
                }
                
                // Add name to our tracking array
                if ( !empty( $name ) ) {
                    $number_names[] = $name;
                }
                
                $assignments = array(
                    'products'   => array(),
                    'categories' => array(),
                    'pages'      => array(),
                );
                
                // Process product assignments
                if ( isset( $number_data['assignments']['products'] ) && is_array( $number_data['assignments']['products'] ) ) {
                    foreach ( $number_data['assignments']['products'] as $product_id ) {
                        $assignments['products'][] = absint( $product_id );
                    }
                }
                
                // Process category assignments
                if ( isset( $number_data['assignments']['categories'] ) && is_array( $number_data['assignments']['categories'] ) ) {
                    foreach ( $number_data['assignments']['categories'] as $category_id ) {
                        $assignments['categories'][] = absint( $category_id );
                    }
                }
                
                // Process page assignments
                if ( isset( $number_data['assignments']['pages'] ) && is_array( $number_data['assignments']['pages'] ) ) {
                    foreach ( $number_data['assignments']['pages'] as $page_id ) {
                        $assignments['pages'][] = absint( $page_id );
                    }
                }
                
                // Add sanitized number data to the array
                $whatsapp_numbers[] = array(
                    'id'          => absint( $number_data['id'] ?? 0 ),
                    'name'        => sanitize_text_field( $number_data['name'] ?? '' ),
                    'number'      => sanitize_text_field( $number_data['number'] ?? '' ),
                    'description' => sanitize_textarea_field( $number_data['description'] ?? '' ),
                    'is_default'  => ! empty( $number_data['is_default'] ),
                    'assignments' => $assignments,
                );
            }
        }
        
        // Update WhatsApp numbers in settings
        $settings['whatsapp_numbers'] = $whatsapp_numbers;
        
        // Update settings
        update_option( 'ctc_settings', $settings );
        
        // Add message based on whether we found duplicates
        if ( $has_duplicate ) {
            add_settings_error(
                'ctc_numbers',
                'ctc_numbers_duplicates',
                esc_html__( 'Some WhatsApp numbers were not saved because they had duplicate names. Each WhatsApp number must have a unique name.', 'click-to-chat' ),
                'error'
            );
        } else {
            add_settings_error(
                'ctc_numbers',
                'ctc_numbers_updated',
                esc_html__( 'WhatsApp numbers saved successfully.', 'click-to-chat' ),
                'updated'
            );
        }
    }

    /**
     * Save message templates
     */
    private function save_templates() {
        // Get existing settings
        $settings = get_option( 'ctc_settings', array() );
        
        // Process submitted templates
        if ( isset( $_POST['ctc_templates'] ) && is_array( $_POST['ctc_templates'] ) ) {
            $message_templates = array(
                'single_product' => sanitize_textarea_field( wp_unslash( $_POST['ctc_templates']['single_product'] ?? '' ) ),
                'shop_page'      => sanitize_textarea_field( wp_unslash( $_POST['ctc_templates']['shop_page'] ?? '' ) ),
                'cart_checkout'  => sanitize_textarea_field( wp_unslash( $_POST['ctc_templates']['cart_checkout'] ?? '' ) ),
                'thank_you'      => sanitize_textarea_field( wp_unslash( $_POST['ctc_templates']['thank_you'] ?? '' ) ),
                'floating'       => sanitize_textarea_field( wp_unslash( $_POST['ctc_templates']['floating'] ?? '' ) ),
                'variations'     => sanitize_textarea_field( wp_unslash( $_POST['ctc_templates']['variations'] ?? '' ) ),
            );
            
            // Update message templates in settings
            $settings['message_templates'] = $message_templates;
            
            // Update settings
            update_option( 'ctc_settings', $settings );
            
            // Add success message
            add_settings_error(
                'ctc_templates',
                'ctc_templates_updated',
                esc_html__( 'Message templates saved successfully.', 'click-to-chat' ),
                'updated'
            );
        }
    }

    /**
     * Add meta boxes to product edit screen
     */
    public function add_product_meta_boxes() {
        add_meta_box(
            'ctc_product_settings',
            esc_html__( 'WhatsApp Shopping Settings', 'click-to-chat' ),
            array( $this, 'render_product_meta_box' ),
            'product',
            'side',
            'default'
        );
    }

    /**
     * Render meta box on product edit screen
     *
     * @param WP_Post $post Current post object.
     */
    public function render_product_meta_box( $post ) {
        // Add nonce for security
        wp_nonce_field( 'ctc_product_meta_nonce', 'ctc_product_meta_nonce' );
        
        // Get current values
        $hide_button = get_post_meta( $post->ID, '_ctc_hide_button', true );
        $custom_message = get_post_meta( $post->ID, '_ctc_custom_message', true );
        $assigned_number = get_post_meta( $post->ID, '_ctc_assigned_number', true );
        
        // Get available WhatsApp numbers
        $whatsapp_numbers = isset( $this->settings['whatsapp_numbers'] ) ? $this->settings['whatsapp_numbers'] : array();
        
        // Output the meta box HTML
        ?>
        <p>
            <label>
                <input type="checkbox" name="ctc_hide_button" value="1" <?php checked( $hide_button, '1' ); ?> />
                <?php esc_html_e( 'Hide WhatsApp button on this product', 'click-to-chat' ); ?>
            </label>
        </p>
        
        <p>
            <label for="ctc_assigned_number"><?php esc_html_e( 'Assign specific WhatsApp number:', 'click-to-chat' ); ?></label>
            <select name="ctc_assigned_number" id="ctc_assigned_number">
                <option value=""><?php esc_html_e( 'Default (based on rules)', 'click-to-chat' ); ?></option>
                <?php foreach ( $whatsapp_numbers as $number ) : ?>
                    <option value="<?php echo esc_attr( $number['id'] ); ?>" <?php selected( $assigned_number, $number['id'] ); ?>>
                        <?php echo esc_html( $number['name'] . ' (' . $number['number'] . ')' ); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </p>
        
        <p>
            <label for="ctc_custom_message"><?php esc_html_e( 'Custom message template (overrides default):', 'click-to-chat' ); ?></label>
            <textarea name="ctc_custom_message" id="ctc_custom_message" rows="4" class="widefat"><?php echo esc_textarea( $custom_message ); ?></textarea>
            <span class="description">
                <?php esc_html_e( 'Available placeholders: {product_name}, {price}, {product_url}', 'click-to-chat' ); ?>
            </span>
        </p>
        <?php
    }

    /**
     * Save product meta data
     *
     * @param int     $post_id The post ID.
     * @param WP_Post $post    The post object.
     */
    public function save_product_meta( $post_id, $post ) {
        // Check if nonce is valid
        if ( ! isset( $_POST['ctc_product_meta_nonce'] ) || ! wp_verify_nonce( $_POST['ctc_product_meta_nonce'], 'ctc_product_meta_nonce' ) ) {
            return;
        }
        
        // Check if user has permissions
        if ( ! current_user_can( 'edit_post', $post_id ) ) {
            return;
        }
        
        // Check if not an autosave
        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
            return;
        }
        
        // Check if not a revision
        if ( wp_is_post_revision( $post_id ) ) {
            return;
        }
        
        // Update hide button setting
        if ( isset( $_POST['ctc_hide_button'] ) ) {
            update_post_meta( $post_id, '_ctc_hide_button', '1' );
        } else {
            delete_post_meta( $post_id, '_ctc_hide_button' );
        }
        
        // Update assigned number
        if ( isset( $_POST['ctc_assigned_number'] ) && ! empty( $_POST['ctc_assigned_number'] ) ) {
            update_post_meta( $post_id, '_ctc_assigned_number', sanitize_text_field( $_POST['ctc_assigned_number'] ) );
        } else {
            delete_post_meta( $post_id, '_ctc_assigned_number' );
        }
        
        // Update custom message
        if ( isset( $_POST['ctc_custom_message'] ) && ! empty( $_POST['ctc_custom_message'] ) ) {
            update_post_meta( $post_id, '_ctc_custom_message', sanitize_textarea_field( $_POST['ctc_custom_message'] ) );
        } else {
            delete_post_meta( $post_id, '_ctc_custom_message' );
        }
    }
}
// Class is initialized in the main plugin file
