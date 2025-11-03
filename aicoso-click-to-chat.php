<?php
/**
 * Click to Chat - WooCommerce WhatsApp Integration
 *
 * Enables direct WhatsApp ordering functionality for WooCommerce stores by adding
 * customizable WhatsApp buttons throughout the customer journey.
 *
 * @package           ClickToChat
 * @author            AICOSO
 * @copyright         2023-2025 AICOSO
 * @license           GPL-2.0-or-later
 *
 * @wordpress-plugin
 * Plugin Name:       AICOSO - Click to Chat
 * Plugin URI:        https://wordpress.org/plugins/aicoso-click-to-chat/
 * Description:       Enable customers to order products directly through WhatsApp with a single click. Add WhatsApp buttons to product pages, shop pages, cart, and checkout.
 * Version:           1.0.0
 * Requires at least: 6.2
 * Tested up to:      6.5
 * Requires PHP:      7.4
 * Author:            AICOSO
 * Author URI:        https://aicoso.com/
 * Text Domain:       aicoso-click-to-chat
 * Domain Path:       /languages
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * WC requires at least: 8.2
 * WC tested up to:      8.5
 */

// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	die;
}

// Define plugin constants.
define( 'CTC_CHAT_VERSION', '1.0.0' );
define( 'CTC_CHAT_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'CTC_CHAT_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'CTC_CHAT_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );

// Declare HPOS compatibility.
add_action(
	'before_woocommerce_init',
	function () {
		if ( class_exists( '\Automattic\WooCommerce\Utilities\FeaturesUtil' ) ) {
			\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', __FILE__, true );
		}
	}
);

/**
 * Check if WooCommerce is active
 *
 * @return bool True if WooCommerce is active, false otherwise.
 */
function ctc_chat_check_woocommerce() {
	if ( ! class_exists( 'WooCommerce' ) ) {
		add_action( 'admin_notices', 'ctc_chat_woocommerce_missing_notice' );
		return false;
	}
	return true;
}

/**
 * Admin notice for missing WooCommerce
 */
function ctc_chat_woocommerce_missing_notice() {
	?>
	<div class="error">
		<p><?php esc_html_e( 'Click to Chat requires WooCommerce to be installed and active.', 'aicoso-click-to-chat' ); ?></p>
	</div>
	<?php
}

// Load the core plugin class.
require_once CTC_CHAT_PLUGIN_DIR . 'includes/class-ctc-chat-click-to-chat.php';

/**
 * Main function to instantiate the plugin
 *
 * @return CTC_Chat_Click_To_Chat
 */
function ctc_chat_plugin() {
	return CTC_Chat_Click_To_Chat::get_instance();
}

// Initialize the plugin.
add_action( 'plugins_loaded', 'ctc_chat_plugin', 10 );

/**
 * Register activation hook
 */
function ctc_chat_activate() {
	// Add default settings.
	$default_settings = array(
		'plugin_enabled'    => true,
		'whatsapp_numbers'  => array(),
		'button_settings'   => array(
			'text'       => esc_html__( 'Order via WhatsApp', 'aicoso-click-to-chat' ),
			'icon'       => true,
			'bg_color'   => '#25D366',
			'text_color' => '#ffffff',
		),
		'single_product'    => array(
			'enabled'  => true,
			'position' => 'after_add_to_cart',
		),
		'shop_page'         => array(
			'enabled'  => false,
			'position' => 'after_add_to_cart',
		),
		'cart_page'         => array(
			'enabled'  => false,
			'position' => 'after_cart_table',
		),
		'checkout_page'     => array(
			'enabled'  => false,
			'position' => 'after_payment',
		),
		'thankyou_page'     => array(
			'enabled' => false,
		),
		'floating_button'   => array(
			'enabled'  => false,
			'position' => 'bottom_right',
		),
		'message_templates' => array(
			'single_product' => esc_html__( "Hello! I'm interested in the product: *{product_name}*\nPrice: {price}\nURL: {product_url}\n\nDo you have this item in stock? I'd like to get more information.", 'aicoso-click-to-chat' ),
			'cart_checkout'  => esc_html__( "Hello! I'd like to complete my purchase of:\n{cart_items_list}\n---------------------\nSubtotal: {cart_subtotal}\nTax: {tax_amount}\nShipping: {shipping_method} - {shipping_cost}\nTotal: {cart_total}\n\nI have a few questions before finalizing my order.", 'aicoso-click-to-chat' ),
			'thank_you'      => esc_html__( "Hello! I've just placed order #{order_number} on {order_date}.\nMy order includes:\n{ordered_items_list}\n---------------------\nApplied Coupon: {coupon_code}\nTotal: {order_total}\n\nI'd like to confirm when this will be shipped.", 'aicoso-click-to-chat' ),
			'floating'       => esc_html__( 'Hello! I was browsing your website at {current_page_url} and have a question.', 'aicoso-click-to-chat' ),
			'variations'     => esc_html__( "Hello! I'm interested in the product: *{product_name}*\nSelected options: {variation_details}\nPrice: {variation_price}\nURL: {product_url}\n\nIs this combination available for immediate shipping?", 'aicoso-click-to-chat' ),
		),
		'exclusions'        => array(
			'pages'      => array(),
			'posts'      => array(),
			'categories' => array(),
			'tags'       => array(),
		),
	);

	update_option( 'ctc_chat_settings', $default_settings );

	// Clean up any existing custom CSS data (removed in version 1.0.1)
	ctc_chat_cleanup_custom_css();
}
register_activation_hook( __FILE__, 'ctc_chat_activate' );

/**
 * Clean up custom CSS data from existing installations
 */
function ctc_chat_cleanup_custom_css() {
	$settings = get_option( 'ctc_chat_settings', array() );

	// Remove custom_css from button_settings if it exists
	if ( isset( $settings['button_settings']['custom_css'] ) ) {
		unset( $settings['button_settings']['custom_css'] );
		update_option( 'ctc_chat_settings', $settings );
	}
}

// Run cleanup on plugin load for existing installations
add_action( 'plugins_loaded', function() {
	if ( is_admin() ) {
		ctc_chat_cleanup_custom_css();
	}
});

/**
 * Register deactivation hook
 */
function ctc_chat_deactivate() {
	// Nothing to do here yet.
}
register_deactivation_hook( __FILE__, 'ctc_chat_deactivate' );
