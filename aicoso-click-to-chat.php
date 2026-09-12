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
 * Version:           1.2.1
 * Requires at least: 6.2
 * Tested up to:      7.0
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
define( 'CTC_CHAT_VERSION', '1.2.1' );
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
			'enabled'               => false,
			'my_account_orders'     => false,
			'my_account_view_order' => false,
			'button_text'           => esc_html__( 'Track My Order on WhatsApp 🚚', 'aicoso-click-to-chat' ),
		),
		'floating_button'   => array(
			'enabled'  => false,
			'position' => 'bottom_right',
		),
		'cart_checkout_nudge' => array(
			'enabled'     => false,
			'trigger'     => 'both',
			'delay'       => 20,
			'frequency'   => 'reappear',
			'title'       => esc_html__( 'Need help with your order?', 'aicoso-click-to-chat' ),
			'message'     => esc_html__( 'Have questions about payment, shipping, or need assistance? Chat with us on WhatsApp!', 'aicoso-click-to-chat' ),
			'button_text' => esc_html__( 'Chat with Support 💬', 'aicoso-click-to-chat' ),
		),
		'back_in_stock'       => array(
			'enabled'     => false,
			'button_text' => esc_html__( 'Notify Me on WhatsApp 🔔', 'aicoso-click-to-chat' ),
			'bg_color'    => '#ff9800',
			'text_color'  => '#ffffff',
			'message'     => esc_html__( "Hello! I noticed that *{product_name}* (SKU: {product_sku}) is currently out of stock.\n\nPlease notify me via WhatsApp as soon as it is back in stock!\nLink: {product_url}", 'aicoso-click-to-chat' ),
		),
		'coupon_engine'       => array(
			'enabled'          => false,
			'coupon_code'      => '',
			'custom_discount'  => '',
			'badge_text'       => esc_html__( '🎁 Chat to get 10% OFF!', 'aicoso-click-to-chat' ),
			'badge_bg'         => '#e11d48',
			'badge_color'      => '#ffffff',
			'show_on_floating' => true,
			'show_on_product'  => true,
			'message'          => esc_html__( "🎁 *Special Discount Claim*\n\nHello! I'd like to claim my discount coupon: *{coupon_code}* ({discount_amount})\n\n*Product:* {product_name}\n*Page:* {current_page_url}\n\nCan you please assist me with applying this discount to my order? Thank you!", 'aicoso-click-to-chat' ),
		),
		'message_templates' => array(
			'single_product' => esc_html__( "Hello! I'm interested in the product: *{product_name}*\nPrice: {price}\nURL: {product_url}\n\nDo you have this item in stock? I'd like to get more information.", 'aicoso-click-to-chat' ),
			'shop'           => esc_html__( "Hello! I'm browsing your products at {current_page_url} and have a question.", 'aicoso-click-to-chat' ),
			'cart_checkout'  => esc_html__( "Hello! I'd like to complete my purchase of:\n{cart_items_list}\n---------------------\nSubtotal: {cart_subtotal}\nTax: {tax_amount}\nShipping: {shipping_method} - {shipping_cost}\nTotal: {cart_total}\n\nI have a few questions before finalizing my order.", 'aicoso-click-to-chat' ),
			'thank_you'      => esc_html__( "Hello! I'd like to check the status of my order #{order_number} (placed on {order_date}).\nCustomer: {customer_name}\nCurrent Status: {order_status}\n\nItems:\n{ordered_items_list}\nTotal: {order_total}\n\nCould you please provide a tracking update? Thank you!", 'aicoso-click-to-chat' ),
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
			'custom_css'            => '',
		),
		'qr_modal'          => array(
			'enabled'       => true,
			'title'         => esc_html__( 'Scan to Chat on WhatsApp', 'aicoso-click-to-chat' ),
			'description'   => esc_html__( 'Point your phone camera or WhatsApp QR scanner at this code to start chatting instantly.', 'aicoso-click-to-chat' ),
			'show_web_link' => true,
		),
		'privacy_compliance' => array(
			'enabled'           => false,
			'consent_mode'      => 'prompt',
			'notice_text'       => esc_html__( 'By chatting with us on WhatsApp, you agree to our {privacy_policy_link} and consent to communication regarding your inquiry.', 'aicoso-click-to-chat' ),
			'link_text'         => esc_html__( 'Privacy Policy', 'aicoso-click-to-chat' ),
			'custom_policy_url' => '',
			'anonymize_ip'      => true,
			'agree_button'      => esc_html__( 'Accept & Chat', 'aicoso-click-to-chat' ),
			'cancel_button'     => esc_html__( 'Cancel', 'aicoso-click-to-chat' ),
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
