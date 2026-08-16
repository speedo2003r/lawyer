<?php
/**
 * Plugin Installer & Database Schema Manager.
 *
 * @package WPMultilingual
 */

namespace WPMultilingual;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Installer
 */
class Installer {

	/**
	 * Database version
	 */
	const DB_VERSION = '1.0.0';

	/**
	 * Option keys
	 */
	const OPTION_DB_VERSION = 'wpm_db_version';
	const OPTION_SETTINGS   = 'wpm_settings';

	/**
	 * Run plugin activation routine.
	 */
	public static function activate() {
		self::create_tables();
		self::set_default_options();

		// Set a transient so rewrite rules can be flushed safely on next load.
		set_transient( 'wpm_flush_rewrite_rules', 1, 60 );

		update_option( self::OPTION_DB_VERSION, self::DB_VERSION );
	}

	/**
	 * Run schema update routine if DB version changed.
	 */
	public static function maybe_update() {
		if ( get_option( self::OPTION_DB_VERSION ) !== self::DB_VERSION ) {
			self::create_tables();
			update_option( self::OPTION_DB_VERSION, self::DB_VERSION );
		}
	}

	/**
	 * Run plugin deactivation routine.
	 */
	public static function deactivate() {
		// Flush rewrite rules on deactivation without modifying user content.
		flush_rewrite_rules();
	}

	/**
	 * Create or update custom database tables using dbDelta.
	 */
	public static function create_tables() {
		global $wpdb;

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';

		$charset_collate = $wpdb->get_charset_collate();

		$table_languages          = $wpdb->prefix . 'wpm_languages';
		$table_translation_groups = $wpdb->prefix . 'wpm_translation_groups';
		$table_translations       = $wpdb->prefix . 'wpm_translations';

		// Note: dbDelta requires two spaces after PRIMARY KEY and exact spacing rules.
		$sql_languages = "CREATE TABLE {$table_languages} (
			id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			code varchar(10) NOT NULL,
			locale varchar(20) NOT NULL,
			name varchar(100) NOT NULL,
			native_name varchar(100) NOT NULL,
			direction varchar(3) NOT NULL DEFAULT 'ltr',
			flag varchar(10) DEFAULT '' NOT NULL,
			url_code varchar(10) NOT NULL,
			is_default tinyint(1) NOT NULL DEFAULT 0,
			is_enabled tinyint(1) NOT NULL DEFAULT 1,
			ordering int(11) NOT NULL DEFAULT 0,
			created_at datetime NOT NULL,
			PRIMARY KEY  (id),
			UNIQUE KEY code (code),
			KEY url_code (url_code),
			KEY is_default (is_default),
			KEY ordering (ordering)
		) {$charset_collate};";

		$sql_groups = "CREATE TABLE {$table_translation_groups} (
			id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			object_type varchar(20) NOT NULL,
			created_at datetime NOT NULL,
			PRIMARY KEY  (id),
			KEY object_type (object_type)
		) {$charset_collate};";

		$sql_translations = "CREATE TABLE {$table_translations} (
			id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			group_id bigint(20) unsigned NOT NULL,
			language_id bigint(20) unsigned NOT NULL,
			object_id bigint(20) unsigned NOT NULL,
			object_type varchar(20) NOT NULL,
			status varchar(30) NOT NULL DEFAULT 'translated',
			created_at datetime NOT NULL,
			updated_at datetime NOT NULL,
			PRIMARY KEY  (id),
			UNIQUE KEY group_language (group_id, language_id),
			KEY group_id (group_id),
			KEY language_id (language_id),
			KEY object_id (object_id),
			KEY object_type (object_type),
			KEY status (status)
		) {$charset_collate};";

		dbDelta( $sql_languages );
		dbDelta( $sql_groups );
		dbDelta( $sql_translations );
	}

	/**
	 * Initialize default plugin options if not already set.
	 */
	public static function set_default_options() {
		$default_settings = [
			'url_mode'                   => 'mode_a', // mode_a: /en/post/, mode_b: /post/ for default
			'hide_default_language_url'  => 0,
			'detect_browser_language'    => 1,
			'cookie_name'                => 'wpm_language',
			'cookie_enabled'             => 1,
			'sync_featured_image'        => 1,
			'sync_taxonomies'            => 1,
			'sync_post_meta'             => 0,
			'translatable_post_types'    => [ 'post', 'page' ],
			'translatable_taxonomies'    => [ 'category', 'post_tag' ],
		];

		$existing = get_option( self::OPTION_SETTINGS, false );
		if ( false === $existing ) {
			add_option( self::OPTION_SETTINGS, $default_settings );
		}
	}
}
