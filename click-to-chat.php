<?php
/**
 * Plugin Name: Click to Chat
 * Plugin URI: https://aicoso.com/
 * Description: Enable customers to order products directly through WhatsApp with a single click. Add WhatsApp buttons to product pages, shop pages, cart, and checkout.
 * Version: 1.0.0
 * Author: AICOSO
 * Author URI: https://aicoso.com/
 * Text Domain: click-to-chat
 * Domain Path: /languages
 * Requires at least: 6.2
 * Tested up to: 6.5
 * Requires PHP: 7.4
 * WC requires at least: 8.2
 * WC tested up to: 8.5
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 *
 * @package ClickToChat
 */

// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
    die;
}

// Define plugin constants
define( 'CTC_VERSION', '1.0.0' );
define( 'CTC_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'CTC_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'CTC_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );

// Declare HPOS compatibility
add_action('before_woocommerce_init', function() {
    if ( class_exists( '\Automattic\WooCommerce\Utilities\FeaturesUtil' ) ) {
        \Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', __FILE__, true );
    }
});

/**
 * Check if WooCommerce is active
 */
function ctc_check_woocommerce() {
    if ( ! class_exists( 'WooCommerce' ) ) {
        add_action( 'admin_notices', 'ctc_woocommerce_missing_notice' );
        return false;
    }
    return true;
}

/**
 * Admin notice for missing WooCommerce
 */
function ctc_woocommerce_missing_notice() {
    ?>
    <div class="error">
        <p><?php esc_html_e( 'Click to Chat requires WooCommerce to be installed and active.', 'click-to-chat' ); ?></p>
    </div>
    <?php
}

/**
 * The core plugin class
 */
class Click_To_Chat {

    /**
     * Instance of this class
     *
     * @var object
     */
    protected static $instance = null;

    /**
     * Initialize the plugin
     */
    public function __construct() {
        // Load plugin textdomain
        add_action( 'plugins_loaded', array( $this, 'load_textdomain' ) );

        // Initialize plugin components if WooCommerce is active
        if ( ctc_check_woocommerce() ) {
            $this->includes();
            $this->init_hooks();
        }
    }

    /**
     * Get a single instance of this class
     *
     * @return object
     */
    public static function get_instance() {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Load plugin textdomain
     */
    public function load_textdomain() {
        load_plugin_textdomain(
            'click-to-chat',
            false,
            dirname( plugin_basename( __FILE__ ) ) . '/languages/'
        );
    }

    /**
     * Include required files
     */
    private function includes() {
        // Admin
        require_once CTC_PLUGIN_DIR . 'admin/class-admin.php';
        require_once CTC_PLUGIN_DIR . 'admin/class-settings.php';

        // Core functionality
        require_once CTC_PLUGIN_DIR . 'includes/class-whatsapp-link-generator.php';
        require_once CTC_PLUGIN_DIR . 'includes/class-button-display.php';
        require_once CTC_PLUGIN_DIR . 'includes/class-shortcodes.php';

        // Public facing
        require_once CTC_PLUGIN_DIR . 'public/class-public.php';
    }

    /**
     * Initialize hooks
     */
    private function init_hooks() {
        // Initialize classes
        new CTC_Admin();
        new CTC_Settings();
        new CTC_Button_Display();
        new CTC_Shortcodes();
        new CTC_Public();
    }
}

/**
 * Main function to instantiate the plugin
 */
function ctc_plugin() {
    return Click_To_Chat::get_instance();
}

// Initialize the plugin
add_action( 'plugins_loaded', 'ctc_plugin', 10 );

/**
 * Register activation hook
 */
function ctc_activate() {
    // Add default settings
    $default_settings = array(
        'whatsapp_numbers' => array(
            array(
                'id'          => 1,
                'name'        => esc_html__( 'Default Number', 'click-to-chat' ),
                'number'      => '',
                'description' => '',
                'assignments' => array()
            )
        ),
        'button_settings' => array(
            'text'        => esc_html__( 'Order via WhatsApp', 'click-to-chat' ),
            'icon'        => true,
            'bg_color'    => '#25D366',
            'text_color'  => '#ffffff',
            'custom_css'  => ''
        ),
        'single_product' => array(
            'enabled'     => true,
            'position'    => 'after_add_to_cart'
        ),
        'shop_page' => array(
            'enabled'     => false,
            'position'    => 'after_add_to_cart'
        ),
        'cart_page' => array(
            'enabled'     => false,
            'position'    => 'after_cart_table'
        ),
        'checkout_page' => array(
            'enabled'     => false,
            'position'    => 'after_payment'
        ),
        'thankyou_page' => array(
            'enabled'     => false
        ),
        'floating_button' => array(
            'enabled'     => false,
            'position'    => 'bottom_right'
        ),
        'message_templates' => array(
            'single_product' => esc_html__( "Hello! I'm interested in the product: *{product_name}*\nPrice: {price}\nURL: {product_url}\n\nDo you have this item in stock? I'd like to get more information.", 'click-to-chat' ),
            'cart_checkout'  => esc_html__( "Hello! I'd like to complete my purchase of:\n{cart_items_list}\n---------------------\nSubtotal: {cart_subtotal}\nTax: {tax_amount}\nShipping: {shipping_method} - {shipping_cost}\nTotal: {cart_total}\n\nI have a few questions before finalizing my order.", 'click-to-chat' ),
            'thank_you'      => esc_html__( "Hello! I've just placed order #{order_number} on {order_date}.\nMy order includes:\n{ordered_items_list}\n---------------------\nApplied Coupon: {coupon_code}\nTotal: {order_total}\n\nI'd like to confirm when this will be shipped.", 'click-to-chat' ),
            'floating'       => esc_html__( "Hello! I was browsing your website at {current_page_url} and have a question.", 'click-to-chat' ),
            'variations'     => esc_html__( "Hello! I'm interested in the product: *{product_name}*\nSelected options: {variation_details}\nPrice: {variation_price}\nURL: {product_url}\n\nIs this combination available for immediate shipping?", 'click-to-chat' )
        ),
        'exclusions' => array(
            'pages'     => array(),
            'posts'     => array(),
            'categories' => array(),
            'tags'      => array()
        )
    );

    update_option( 'ctc_settings', $default_settings );
}
register_activation_hook( __FILE__, 'ctc_activate' );

/**
 * Register deactivation hook
 */
function ctc_deactivate() {
    // Nothing to do here yet
}
register_deactivation_hook( __FILE__, 'ctc_deactivate' );