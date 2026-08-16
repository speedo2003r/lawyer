<?php
/**
 * Language Manager.
 *
 * @package WPMultilingual
 */

namespace WPMultilingual;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class LanguageManager
 */
class LanguageManager {

	/**
	 * Current language code for this request.
	 *
	 * @var string|null
	 */
	private $current_language = null;

	/**
	 * Singleton instance.
	 *
	 * @var LanguageManager|null
	 */
	private static $instance = null;

	/**
	 * Get singleton instance.
	 *
	 * @return LanguageManager
	 */
	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Constructor.
	 */
	private function __construct() {}

	/**
	 * Retrieve all configured languages.
	 *
	 * @param array $args Filter arguments (e.g., ['enabled_only' => true]).
	 * @return array Array of language objects.
	 */
	public function get_languages( $args = [] ) {
		global $wpdb;

		$defaults = [
			'enabled_only' => false,
			'orderby'      => 'ordering',
			'order'        => 'ASC',
		];
		$args = wp_parse_args( $args, $defaults );

		$cache_key = 'languages_' . ( $args['enabled_only'] ? 'enabled' : 'all' );
		$found     = false;
		$cached    = Cache::get( $cache_key, $found );
		if ( $found && is_array( $cached ) ) {
			return $cached;
		}

		$table = $wpdb->prefix . 'wpm_languages';

		// Verify table exists before querying
		if ( $wpdb->get_var( $wpdb->prepare( "SHOW TABLES LIKE %s", $table ) ) !== $table ) {
			return [];
		}

		$where = '';
		if ( ! empty( $args['enabled_only'] ) ) {
			$where = 'WHERE is_enabled = 1';
		}

		$orderby = in_array( strtolower( $args['orderby'] ), [ 'id', 'code', 'name', 'ordering' ], true ) ? $args['orderby'] : 'ordering';
		$order   = strtoupper( $args['order'] ) === 'DESC' ? 'DESC' : 'ASC';

		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$results = $wpdb->get_results( "SELECT * FROM {$table} {$where} ORDER BY {$orderby} {$order}" );

		if ( ! is_array( $results ) ) {
			$results = [];
		}

		// Key results by language code
		$languages = [];
		foreach ( $results as $row ) {
			$languages[ $row->code ] = $row;
		}

		Cache::set( $cache_key, $languages, 86400 );

		return $languages;
	}

	/**
	 * Get only enabled languages.
	 *
	 * @return array
	 */
	public function get_enabled_languages() {
		return $this->get_languages( [ 'enabled_only' => true ] );
	}

	/**
	 * Get language by code or ID.
	 *
	 * @param string|int $identifier Language code or database ID.
	 * @return object|null
	 */
	public function get_language( $identifier ) {
		if ( empty( $identifier ) ) {
			return null;
		}

		$languages = $this->get_languages();

		// Check by code
		if ( is_string( $identifier ) && isset( $languages[ $identifier ] ) ) {
			return $languages[ $identifier ];
		}

		// Check by ID
		foreach ( $languages as $lang ) {
			if ( (int) $lang->id === (int) $identifier || $lang->code === (string) $identifier ) {
				return $lang;
			}
		}

		return null;
	}

	/**
	 * Get language by URL code.
	 *
	 * @param string $url_code
	 * @return object|null
	 */
	public function get_language_by_url_code( $url_code ) {
		if ( empty( $url_code ) ) {
			return null;
		}

		$languages = $this->get_languages();
		foreach ( $languages as $lang ) {
			if ( $lang->url_code === $url_code ) {
				return $lang;
			}
		}

		return null;
	}

	/**
	 * Get default language object.
	 *
	 * @return object|null
	 */
	public function get_default_language() {
		$found  = false;
		$cached = Cache::get( 'language_default', $found );
		if ( $found && is_object( $cached ) ) {
			return $cached;
		}

		$languages = $this->get_languages();
		foreach ( $languages as $lang ) {
			if ( (int) $lang->is_default === 1 ) {
				Cache::set( 'language_default', $lang, 86400 );
				return $lang;
			}
		}

		// Fallback to first available language if no default marked
		if ( ! empty( $languages ) ) {
			$first = reset( $languages );
			Cache::set( 'language_default', $first, 86400 );
			return $first;
		}

		return null;
	}

	/**
	 * Get the current active language code for the request.
	 *
	 * @return string
	 */
	public function get_current_language() {
		if ( null !== $this->current_language ) {
			return apply_filters( 'wpm_current_language', $this->current_language );
		}

		$default = $this->get_default_language();
		$code    = $default ? $default->code : 'en';

		$this->current_language = $code;
		return apply_filters( 'wpm_current_language', $this->current_language );
	}

	/**
	 * Set the current active language code for the request.
	 *
	 * @param string $code
	 */
	public function set_current_language( $code ) {
		$old_lang = $this->current_language;
		$lang     = $this->get_language( $code );

		if ( $lang ) {
			$this->current_language = $lang->code;
			if ( $old_lang !== $this->current_language ) {
				do_action( 'wpm_language_switched', $this->current_language, $old_lang );
			}
		}
	}

	/**
	 * Check if a language code or current language is RTL.
	 *
	 * @param string|null $code
	 * @return bool
	 */
	public function is_rtl( $code = null ) {
		if ( null === $code ) {
			$code = $this->get_current_language();
		}

		$lang = $this->get_language( $code );
		if ( $lang && 'rtl' === strtolower( $lang->direction ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Check if given code is default language.
	 *
	 * @param string $code
	 * @return bool
	 */
	public function is_default_language( $code ) {
		$default = $this->get_default_language();
		return $default && $default->code === $code;
	}

	/**
	 * Add a new language.
	 *
	 * @param array $data Language data.
	 * @return int|\WP_Error Inserted language ID or WP_Error.
	 */
	public function add_language( $data ) {
		global $wpdb;

		$code        = sanitize_text_field( strtolower( $data['code'] ?? '' ) );
		$locale      = sanitize_text_field( $data['locale'] ?? '' );
		$name        = sanitize_text_field( $data['name'] ?? '' );
		$native_name = sanitize_text_field( $data['native_name'] ?? $name );
		$direction   = in_array( strtolower( $data['direction'] ?? '' ), [ 'ltr', 'rtl' ], true ) ? strtolower( $data['direction'] ) : 'ltr';
		$flag        = sanitize_text_field( $data['flag'] ?? '' );
		$url_code    = sanitize_text_field( strtolower( $data['url_code'] ?? $code ) );
		$is_default  = ! empty( $data['is_default'] ) ? 1 : 0;
		$is_enabled  = isset( $data['is_enabled'] ) ? (int) (bool) $data['is_enabled'] : 1;
		$ordering    = isset( $data['ordering'] ) ? (int) $data['ordering'] : 0;

		if ( empty( $code ) || empty( $name ) || empty( $locale ) ) {
			return new \WP_Error( 'wpm_invalid_language_data', __( 'Language code, name, and locale are required.', 'wp-multilingual' ) );
		}

		// Validate code pattern (alphanumeric, 2-10 chars)
		if ( ! preg_match( '/^[a-z]{2,10}(-[a-z]{2,10})?$/i', $code ) ) {
			return new \WP_Error( 'wpm_invalid_code_format', __( 'Invalid language code format.', 'wp-multilingual' ) );
		}

		$table = $wpdb->prefix . 'wpm_languages';

		// Check duplicate code
		$existing = $wpdb->get_var( $wpdb->prepare( "SELECT id FROM {$table} WHERE code = %s", $code ) );
		if ( $existing ) {
			return new \WP_Error( 'wpm_duplicate_code', __( 'A language with this code already exists.', 'wp-multilingual' ) );
		}

		// If marking as default, unset existing default
		if ( 1 === $is_default ) {
			$wpdb->update( $table, [ 'is_default' => 0 ], [ 'is_default' => 1 ] );
		} else {
			// If this is the very first language added, make it default automatically
			$count = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$table}" );
			if ( 0 === $count ) {
				$is_default = 1;
			}
		}

		$inserted = $wpdb->insert(
			$table,
			[
				'code'        => $code,
				'locale'      => $locale,
				'name'        => $name,
				'native_name' => $native_name,
				'direction'   => $direction,
				'flag'        => $flag,
				'url_code'    => $url_code,
				'is_default'  => $is_default,
				'is_enabled'  => $is_enabled,
				'ordering'    => $ordering,
				'created_at'  => current_time( 'mysql' ),
			],
			[ '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%d', '%d', '%d', '%s' ]
		);

		if ( false === $inserted ) {
			return new \WP_Error( 'wpm_db_insert_failed', __( 'Could not save language to database.', 'wp-multilingual' ) );
		}

		$lang_id = $wpdb->insert_id;
		Cache::invalidate_languages();

		// Flag for rewrite flush
		set_transient( 'wpm_flush_rewrite_rules', 1, 60 );

		do_action( 'wpm_language_created', $lang_id, $code );

		return $lang_id;
	}

	/**
	 * Update an existing language.
	 *
	 * @param int   $id Language database ID.
	 * @param array $data Updated fields.
	 * @return bool|\WP_Error
	 */
	public function update_language( $id, $data ) {
		global $wpdb;

		$id = absint( $id );
		if ( ! $id ) {
			return new \WP_Error( 'wpm_invalid_id', __( 'Invalid language ID.', 'wp-multilingual' ) );
		}

		$table = $wpdb->prefix . 'wpm_languages';
		$lang  = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$table} WHERE id = %d", $id ) );

		if ( ! $lang ) {
			return new \WP_Error( 'wpm_not_found', __( 'Language not found.', 'wp-multilingual' ) );
		}

		$fields = [];
		$format = [];

		if ( isset( $data['name'] ) ) {
			$fields['name'] = sanitize_text_field( $data['name'] );
			$format[]       = '%s';
		}
		if ( isset( $data['native_name'] ) ) {
			$fields['native_name'] = sanitize_text_field( $data['native_name'] );
			$format[]              = '%s';
		}
		if ( isset( $data['locale'] ) ) {
			$fields['locale'] = sanitize_text_field( $data['locale'] );
			$format[]         = '%s';
		}
		if ( isset( $data['direction'] ) ) {
			$fields['direction'] = in_array( strtolower( $data['direction'] ), [ 'ltr', 'rtl' ], true ) ? strtolower( $data['direction'] ) : 'ltr';
			$format[]            = '%s';
		}
		if ( isset( $data['flag'] ) ) {
			$fields['flag'] = sanitize_text_field( $data['flag'] );
			$format[]       = '%s';
		}
		if ( isset( $data['url_code'] ) ) {
			$fields['url_code'] = sanitize_text_field( strtolower( $data['url_code'] ) );
			$format[]           = '%s';
		}
		if ( isset( $data['is_enabled'] ) ) {
			$fields['is_enabled'] = ! empty( $data['is_enabled'] ) ? 1 : 0;
			$format[]             = '%d';
		}
		if ( isset( $data['ordering'] ) ) {
			$fields['ordering'] = (int) $data['ordering'];
			$format[]           = '%d';
		}
		if ( isset( $data['is_default'] ) && ! empty( $data['is_default'] ) ) {
			// Unset other defaults
			$wpdb->update( $table, [ 'is_default' => 0 ], [ 'is_default' => 1 ] );
			$fields['is_default'] = 1;
			$fields['is_enabled'] = 1; // Default must be enabled
			$format[]             = '%d';
		}

		if ( empty( $fields ) ) {
			return true;
		}

		$result = $wpdb->update( $table, $fields, [ 'id' => $id ], $format, [ '%d' ] );

		Cache::invalidate_languages();
		set_transient( 'wpm_flush_rewrite_rules', 1, 60 );

		do_action( 'wpm_language_updated', $id, $lang->code, $fields );

		return false !== $result;
	}

	/**
	 * Set a language as default.
	 *
	 * @param string|int $identifier Language code or ID.
	 * @return bool|\WP_Error
	 */
	public function set_default_language( $identifier ) {
		$lang = $this->get_language( $identifier );
		if ( ! $lang ) {
			return new \WP_Error( 'wpm_not_found', __( 'Language not found.', 'wp-multilingual' ) );
		}

		return $this->update_language( $lang->id, [ 'is_default' => 1 ] );
	}

	/**
	 * Delete a language.
	 *
	 * @param int $id Language database ID.
	 * @return bool|\WP_Error
	 */
	public function delete_language( $id ) {
		global $wpdb;

		$id   = absint( $id );
		$lang = $this->get_language( $id );

		if ( ! $lang ) {
			return new \WP_Error( 'wpm_not_found', __( 'Language not found.', 'wp-multilingual' ) );
		}

		if ( (int) $lang->is_default === 1 ) {
			return new \WP_Error( 'wpm_cannot_delete_default', __( 'Cannot delete the default language.', 'wp-multilingual' ) );
		}

		$table_lang  = $wpdb->prefix . 'wpm_languages';
		$table_trans = $wpdb->prefix . 'wpm_translations';

		// Clean up translations table references for this language
		$wpdb->delete( $table_trans, [ 'language_id' => $id ], [ '%d' ] );

		// Delete language
		$wpdb->delete( $table_lang, [ 'id' => $id ], [ '%d' ] );

		Cache::invalidate_languages();
		set_transient( 'wpm_flush_rewrite_rules', 1, 60 );

		do_action( 'wpm_language_deleted', $id, $lang->code );

		return true;
	}

	/**
	 * Return standard preset languages for easy setup in UI.
	 *
	 * @return array
	 */
	public static function get_preset_languages() {
		return [
			'en' => [
				'code'        => 'en',
				'locale'      => 'en_US',
				'name'        => 'English',
				'native_name' => 'English',
				'direction'   => 'ltr',
				'flag'        => '🇺🇸',
				'url_code'    => 'en',
			],
			'ar' => [
				'code'        => 'ar',
				'locale'      => 'ar',
				'name'        => 'Arabic',
				'native_name' => 'العربية',
				'direction'   => 'rtl',
				'flag'        => '🇸🇦',
				'url_code'    => 'ar',
			],
			'fr' => [
				'code'        => 'fr',
				'locale'      => 'fr_FR',
				'name'        => 'French',
				'native_name' => 'Français',
				'direction'   => 'ltr',
				'flag'        => '🇫🇷',
				'url_code'    => 'fr',
			],
			'de' => [
				'code'        => 'de',
				'locale'      => 'de_DE',
				'name'        => 'German',
				'native_name' => 'Deutsch',
				'direction'   => 'ltr',
				'flag'        => '🇩🇪',
				'url_code'    => 'de',
			],
			'es' => [
				'code'        => 'es',
				'locale'      => 'es_ES',
				'name'        => 'Spanish',
				'native_name' => 'Español',
				'direction'   => 'ltr',
				'flag'        => '🇪🇸',
				'url_code'    => 'es',
			],
			'it' => [
				'code'        => 'it',
				'locale'      => 'it_IT',
				'name'        => 'Italian',
				'native_name' => 'Italiano',
				'direction'   => 'ltr',
				'flag'        => '🇮🇹',
				'url_code'    => 'it',
			],
			'tr' => [
				'code'        => 'tr',
				'locale'      => 'tr_TR',
				'name'        => 'Turkish',
				'native_name' => 'Türkçe',
				'direction'   => 'ltr',
				'flag'        => '🇹🇷',
				'url_code'    => 'tr',
			],
			'fa' => [
				'code'        => 'fa',
				'locale'      => 'fa_IR',
				'name'        => 'Persian',
				'native_name' => 'فارسی',
				'direction'   => 'rtl',
				'flag'        => '🇮🇷',
				'url_code'    => 'fa',
			],
			'ur' => [
				'code'        => 'ur',
				'locale'      => 'ur',
				'name'        => 'Urdu',
				'native_name' => 'اردو',
				'direction'   => 'rtl',
				'flag'        => '🇵🇰',
				'url_code'    => 'ur',
			],
			'zh' => [
				'code'        => 'zh',
				'locale'      => 'zh_CN',
				'name'        => 'Chinese (Simplified)',
				'native_name' => '简体中文',
				'direction'   => 'ltr',
				'flag'        => '🇨🇳',
				'url_code'    => 'zh',
			],
			'ja' => [
				'code'        => 'ja',
				'locale'      => 'ja',
				'name'        => 'Japanese',
				'native_name' => '日本語',
				'direction'   => 'ltr',
				'flag'        => '🇯🇵',
				'url_code'    => 'ja',
			],
			'ru' => [
				'code'        => 'ru',
				'locale'      => 'ru_RU',
				'name'        => 'Russian',
				'native_name' => 'Русский',
				'direction'   => 'ltr',
				'flag'        => '🇷🇺',
				'url_code'    => 'ru',
			],
			'pt' => [
				'code'        => 'pt',
				'locale'      => 'pt_BR',
				'name'        => 'Portuguese (Brazil)',
				'native_name' => 'Português',
				'direction'   => 'ltr',
				'flag'        => '🇧🇷',
				'url_code'    => 'pt',
			],
			'nl' => [
				'code'        => 'nl',
				'locale'      => 'nl_NL',
				'name'        => 'Dutch',
				'native_name' => 'Nederlands',
				'direction'   => 'ltr',
				'flag'        => '🇳🇱',
				'url_code'    => 'nl',
			],
		];
	}
}
