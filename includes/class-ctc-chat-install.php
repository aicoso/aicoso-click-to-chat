<?php
/**
 * Plugin install and database upgrades.
 *
 * @package ClickToChat
 * @since 1.0.2
 */

if ( ! defined( 'ABSPATH' ) ) {
	die;
}

/**
 * Install routines for Click to Chat.
 */
class CTC_Chat_Install {

	const DB_VERSION = '1.0.0';

	/**
	 * Bootstrap install hooks.
	 */
	public static function init() {
		add_action( 'plugins_loaded', array( __CLASS__, 'maybe_upgrade' ), 5 );
		add_action( 'ctc_chat_prune_click_events', array( __CLASS__, 'prune_click_events' ) );
	}

	/**
	 * Run on plugin activation.
	 */
	public static function activate() {
		self::create_tables();
		self::schedule_events();
		update_option( 'ctc_chat_db_version', self::DB_VERSION );
	}

	/**
	 * Run on plugin deactivation.
	 */
	public static function deactivate() {
		wp_clear_scheduled_hook( 'ctc_chat_prune_click_events' );
	}

	/**
	 * Maybe upgrade database schema.
	 */
	public static function maybe_upgrade() {
		CTC_Chat_Settings_Migrator::migrate();
		$installed = get_option( 'ctc_chat_db_version', '' );

		if ( self::DB_VERSION === $installed ) {
			self::schedule_events();
			return;
		}

		self::create_tables();
		self::schedule_events();
		update_option( 'ctc_chat_db_version', self::DB_VERSION );
	}

	/**
	 * Create custom tables.
	 */
	public static function create_tables() {
		global $wpdb;

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';

		$table_name      = ctc_chat_get_clicks_table_name();
		$charset_collate = $wpdb->get_charset_collate();

		$sql = "CREATE TABLE {$table_name} (
			id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
			clicked_at DATETIME NOT NULL,
			button_type VARCHAR(20) NOT NULL,
			template_type VARCHAR(30) DEFAULT NULL,
			number_id BIGINT(20) UNSIGNED DEFAULT NULL,
			product_id BIGINT(20) UNSIGNED DEFAULT NULL,
			variation_id BIGINT(20) UNSIGNED DEFAULT NULL,
			order_id BIGINT(20) UNSIGNED DEFAULT NULL,
			cart_item_count SMALLINT(5) UNSIGNED DEFAULT NULL,
			cart_total DECIMAL(14,4) DEFAULT NULL,
			cart_currency VARCHAR(10) DEFAULT NULL,
			page_url TEXT DEFAULT NULL,
			page_path VARCHAR(255) DEFAULT NULL,
			referrer_url TEXT DEFAULT NULL,
			device_type VARCHAR(20) DEFAULT NULL,
			visitor_key VARCHAR(64) NOT NULL,
			ip_hash VARCHAR(64) DEFAULT NULL,
			user_agent_hash VARCHAR(64) DEFAULT NULL,
			is_unique TINYINT(1) NOT NULL DEFAULT 0,
			is_bot TINYINT(1) NOT NULL DEFAULT 0,
			PRIMARY KEY  (id),
			KEY clicked_at (clicked_at),
			KEY button_type_clicked_at (button_type, clicked_at),
			KEY product_clicked_at (product_id, clicked_at),
			KEY number_clicked_at (number_id, clicked_at),
			KEY order_id (order_id),
			KEY visitor_key_clicked_at (visitor_key, clicked_at),
			KEY page_path_clicked_at (page_path, clicked_at)
		) {$charset_collate};";

		dbDelta( $sql );
	}

	/**
	 * Schedule cron events.
	 */
	public static function schedule_events() {
		if ( ! wp_next_scheduled( 'ctc_chat_prune_click_events' ) ) {
			wp_schedule_event( time(), 'daily', 'ctc_chat_prune_click_events' );
		}
	}

	/**
	 * Delete old click rows based on retention setting.
	 */
	public static function prune_click_events() {
		global $wpdb;

		$settings       = get_option( 'ctc_chat_settings', array() );
		$retention_days = isset( $settings['analytics']['retention_days'] ) ? absint( $settings['analytics']['retention_days'] ) : 365;
		$retention_days = max( 30, min( 730, $retention_days ) );

		$table = ctc_chat_get_clicks_table_name();
		$cutoff = gmdate( 'Y-m-d H:i:s', time() - ( $retention_days * DAY_IN_SECONDS ) );

		// phpcs:disable WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, PluginCheck.Security.DirectDB.UnescapedDBParameter -- Plugin-owned table identifier.
		$wpdb->query(
			$wpdb->prepare(
				"DELETE FROM {$table} WHERE clicked_at < %s LIMIT 1000",
				$cutoff
			)
		);
		// phpcs:enable
	}

	/**
	 * Drop plugin tables on uninstall.
	 */
	public static function drop_tables() {
		global $wpdb;

		$table = ctc_chat_get_clicks_table_name();
		// phpcs:disable WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, PluginCheck.Security.DirectDB.UnescapedDBParameter -- Plugin-owned table identifier.
		$wpdb->query( "DROP TABLE IF EXISTS {$table}" );
		// phpcs:enable
	}
}
