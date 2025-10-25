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
 * @package ClickToChat
 */
class Click_To_Chat {

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
	 * Include required files
	 */
	private function includes() {
		// Admin.
		require_once CTC_PLUGIN_DIR . 'admin/class-ctc-admin.php';
		require_once CTC_PLUGIN_DIR . 'admin/class-ctc-settings.php';

		// Core functionality.
		require_once CTC_PLUGIN_DIR . 'includes/class-ctc-whatsapp-link-generator.php';
		require_once CTC_PLUGIN_DIR . 'includes/class-ctc-button-display.php';
		require_once CTC_PLUGIN_DIR . 'includes/class-ctc-shortcodes.php';

		// Public facing.
		require_once CTC_PLUGIN_DIR . 'public/class-ctc-public.php';
	}

	/**
	 * Initialize hooks
	 */
	private function init_hooks() {
		// Initialize classes.
		new CTC_Admin();
		new CTC_Settings();
		new CTC_Button_Display();
		new CTC_Shortcodes();
		new CTC_Public();
	}
}
