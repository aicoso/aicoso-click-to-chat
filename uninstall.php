<?php
/**
 * Uninstall Click to Chat.
 *
 * @package ClickToChat
 * @since 1.0.0
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

delete_option( 'ctc_chat_settings' );
delete_option( 'ctc_chat_db_version' );

global $wpdb;

// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
$wpdb->query( "DELETE FROM {$wpdb->postmeta} WHERE meta_key LIKE '_ctc_chat_%'" );

require_once plugin_dir_path( __FILE__ ) . 'includes/class-ctc-chat-analytics-helpers.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/class-ctc-chat-install.php';

CTC_Chat_Install::drop_tables();
wp_clear_scheduled_hook( 'ctc_chat_prune_click_events' );

wp_cache_flush();
