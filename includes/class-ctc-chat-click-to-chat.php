<?php
/**
 * The core plugin class.
 *
 * This is the main class that orchestrates all plugin functionality.
 * It loads admin, public, and core components.
 *
 * @package ClickToChat
 * @since 1.0.0
 */

// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	die;
}

/**
 * The core plugin class
 *
 * @since 1.0.0
 * @package CTC_Chat
 */
class CTC_Chat_Click_To_Chat {

	/**
	 * Instance of this class
	 *
	 * @since 1.0.0
	 * @var object
	 */
	protected static $instance = null;

	/**
	 * Initialize the plugin
	 */
	public function __construct() {
		// Initialize plugin components if WooCommerce is active.
		if ( ctc_chat_check_woocommerce() ) {
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
	 * Include required files
	 */
	private function includes() {
		// Admin.
		require_once CTC_CHAT_PLUGIN_DIR . 'admin/class-ctc-chat-admin.php';
		require_once CTC_CHAT_PLUGIN_DIR . 'admin/class-ctc-chat-settings.php';

		// Core functionality.
		require_once CTC_CHAT_PLUGIN_DIR . 'includes/class-ctc-chat-whatsapp-link-generator.php';
		require_once CTC_CHAT_PLUGIN_DIR . 'includes/class-ctc-chat-button-display.php';
		require_once CTC_CHAT_PLUGIN_DIR . 'includes/class-ctc-chat-shortcodes.php';

		// Public facing.
		require_once CTC_CHAT_PLUGIN_DIR . 'public/class-ctc-chat-public.php';
	}

	/**
	 * Initialize hooks
	 */
	private function init_hooks() {
		// Initialize classes.
		new CTC_Chat_Admin();
		new CTC_Chat_Settings();
		new CTC_Chat_Button_Display();
		new CTC_Chat_Shortcodes();
		new CTC_Chat_Public();
	}
}
