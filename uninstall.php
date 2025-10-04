<?php
/**
 * Uninstall Click to Chat.
 *
 * This file runs when the plugin is uninstalled.
 * It cleans up all plugin data from the database.
 *
 * @package ClickToChat
 * @since 1.0.0
 */

// If uninstall is not called from WordPress, exit.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

// Delete plugin options.
delete_option( 'ctc_settings' );

// Delete product meta data from all products.
global $wpdb;

// Delete the plugin-specific post meta for all products.
// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Uninstall routine requires direct database access to clean up all plugin data.
$wpdb->query( "DELETE FROM {$wpdb->postmeta} WHERE meta_key LIKE '_ctc_%'" );

// Clear any cached data.
wp_cache_flush();
