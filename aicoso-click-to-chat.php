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
 * @license           GPL-3.0
 *
 * @wordpress-plugin
 * Plugin Name:       AICOSO Click to Chat
 * Plugin URI:        https://wordpress.org/plugins/aicoso-click-to-chat/
 * Description:       Enable customers to order products directly through WhatsApp with a single click. Add WhatsApp buttons to product pages, shop pages, cart, and checkout.
 * Version:           1.0.3
 * Requires at least: 6.2
 * Tested up to:      6.8
 * Requires PHP:      7.4
 * Author:            AICOSO
 * Author URI:        https://aicoso.com/
 * Text Domain:       aicoso-click-to-chat
 * Domain Path:       /languages
 * License:           GPL-3.0
 * License URI:       https://www.gnu.org/licenses/gpl-3.0.html
 * WC requires at least: 8.2
 * WC tested up to:      10.3.5
 */

// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	die;
}

// Define plugin constants.
define( 'CTC_CHAT_VERSION', '1.0.3' );
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

// Load analytics helpers early.
require_once CTC_CHAT_PLUGIN_DIR . 'includes/class-ctc-chat-analytics-helpers.php';
require_once CTC_CHAT_PLUGIN_DIR . 'includes/class-ctc-chat-settings-migrator.php';
require_once CTC_CHAT_PLUGIN_DIR . 'includes/class-ctc-chat-install.php';

CTC_Chat_Install::init();

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
 * Recursively merge default settings without overwriting saved values.
 *
 * @param array $defaults Default settings.
 * @param array $current  Current settings.
 * @return array
 */
function ctc_chat_merge_settings( $defaults, $current ) {
	foreach ( $defaults as $key => $value ) {
		if ( is_array( $value ) ) {
			$current_value = isset( $current[ $key ] ) && is_array( $current[ $key ] ) ? $current[ $key ] : array();
			$current[ $key ] = ctc_chat_merge_settings( $value, $current_value );
		} elseif ( ! array_key_exists( $key, $current ) ) {
			$current[ $key ] = $value;
		}
	}

	return $current;
}

/**
 * Get default plugin settings.
 *
 * @return array
 */
function ctc_chat_get_default_settings() {
	return array(
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
			'shop'           => esc_html__( "Hello! I'm browsing your products at {current_page_url} and have a question.", 'aicoso-click-to-chat' ),
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
			'products'   => array(),
		),
		'advanced'          => array(
			'hide_add_to_cart'      => false,
			'hide_proceed_checkout' => false,
			'hide_place_order'      => false,
			'catalog_mode'          => false,
		),
		'analytics'         => array(
			'enabled'            => true,
			'retention_days'     => 365,
			'track_ip'           => true,
			'dedupe_hours'       => 24,
			'exclude_bots'       => true,
			'visitor_cookie_ttl' => 30,
		),
	);
}

/**
 * Register activation hook
 */
function ctc_chat_activate() {
	require_once CTC_CHAT_PLUGIN_DIR . 'includes/class-ctc-chat-analytics-helpers.php';
	require_once CTC_CHAT_PLUGIN_DIR . 'includes/class-ctc-chat-settings-migrator.php';
	require_once CTC_CHAT_PLUGIN_DIR . 'includes/class-ctc-chat-install.php';
	CTC_Chat_Install::activate();
	CTC_Chat_Settings_Migrator::migrate();
}
register_activation_hook( __FILE__, 'ctc_chat_activate' );

/**
 * Register deactivation hook
 */
function ctc_chat_deactivate() {
	require_once CTC_CHAT_PLUGIN_DIR . 'includes/class-ctc-chat-install.php';
	CTC_Chat_Install::deactivate();
}
register_deactivation_hook( __FILE__, 'ctc_chat_deactivate' );
