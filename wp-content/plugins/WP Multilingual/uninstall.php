<?php
/**
 * Fired when the plugin is uninstalled.
 *
 * @package WPMultilingual
 */

// If uninstall not called from WordPress, exit.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

global $wpdb;

// 1. Delete custom database tables
$table_languages          = $wpdb->prefix . 'wpm_languages';
$table_translation_groups = $wpdb->prefix . 'wpm_translation_groups';
$table_translations       = $wpdb->prefix . 'wpm_translations';

// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
$wpdb->query( "DROP TABLE IF EXISTS {$table_translations}" );
// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
$wpdb->query( "DROP TABLE IF EXISTS {$table_translation_groups}" );
// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
$wpdb->query( "DROP TABLE IF EXISTS {$table_languages}" );

// 2. Delete plugin options & transients
delete_option( 'wpm_db_version' );
delete_option( 'wpm_settings' );
delete_transient( 'wpm_flush_rewrite_rules' );

// 3. Clean up postmeta
$wpdb->delete( $wpdb->postmeta, [ 'meta_key' => '_wpm_language' ] );
$wpdb->delete( $wpdb->postmeta, [ 'meta_key' => '_wpm_group_id' ] );
$wpdb->delete( $wpdb->postmeta, [ 'meta_key' => '_wpm_translation_status' ] );

// 4. Clean up termmeta
$wpdb->delete( $wpdb->termmeta, [ 'meta_key' => '_wpm_language' ] );
$wpdb->delete( $wpdb->termmeta, [ 'meta_key' => '_wpm_group_id' ] );
$wpdb->delete( $wpdb->termmeta, [ 'meta_key' => '_wpm_translation_status' ] );

// 5. Clear caches
wp_cache_flush();
flush_rewrite_rules();
