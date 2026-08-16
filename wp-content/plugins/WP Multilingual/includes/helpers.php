<?php
/**
 * Global Helper Functions & Public Developer API.
 *
 * @package WPMultilingual
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use WPMultilingual\LanguageManager;
use WPMultilingual\TranslationManager;
use WPMultilingual\LanguageSwitcher;
use WPMultilingual\Rewrite;

/**
 * Get list of configured languages.
 *
 * @param array $args
 * @return array
 */
function wpm_get_languages( $args = [] ) {
	return LanguageManager::get_instance()->get_languages( $args );
}

/**
 * Get single language object by code or ID.
 *
 * @param string|int $identifier
 * @return object|null
 */
function wpm_get_language( $identifier ) {
	return LanguageManager::get_instance()->get_language( $identifier );
}

/**
 * Get active language code for the current request.
 *
 * @return string
 */
function wpm_get_current_language() {
	return LanguageManager::get_instance()->get_current_language();
}

/**
 * Get default language object.
 *
 * @return object|null
 */
function wpm_get_default_language() {
	return LanguageManager::get_instance()->get_default_language();
}

/**
 * Check if the specified or current language is the default language.
 *
 * @param string|null $code
 * @return bool
 */
function wpm_is_default_language( $code = null ) {
	if ( null === $code ) {
		$code = wpm_get_current_language();
	}
	return LanguageManager::get_instance()->is_default_language( $code );
}

/**
 * Check if current or given language code is Right-to-Left (RTL).
 *
 * @param string|null $code
 * @return bool
 */
function wpm_is_rtl( $code = null ) {
	return LanguageManager::get_instance()->is_rtl( $code );
}

/**
 * Get language code assigned to a post.
 *
 * @param int $post_id
 * @return string|null
 */
function wpm_get_post_language( $post_id ) {
	return TranslationManager::get_instance()->get_object_language( $post_id, 'post' );
}

/**
 * Get translation group ID for a post or term.
 *
 * @param int    $object_id
 * @param string $object_type 'post' or 'term'.
 * @return int|null
 */
function wpm_get_translation_group( $object_id, $object_type = 'post' ) {
	return TranslationManager::get_instance()->get_object_group_id( $object_id, $object_type );
}

/**
 * Get translated object ID for a given language.
 *
 * @param int    $object_id Source post/term ID.
 * @param string $lang_code Target language code.
 * @param string $object_type 'post' or 'term'.
 * @return int|null
 */
function wpm_get_translation( $object_id, $lang_code, $object_type = 'post' ) {
	return TranslationManager::get_instance()->get_translation( $object_id, $lang_code, $object_type );
}

/**
 * Get all translations for an object (key = lang_code, value = object_id).
 *
 * @param int    $object_id
 * @param string $object_type 'post' or 'term'.
 * @return array
 */
function wpm_get_translations( $object_id, $object_type = 'post' ) {
	return TranslationManager::get_instance()->get_translations( $object_id, $object_type );
}

/**
 * Check if a post or term has a translation in a specific language.
 *
 * @param int    $object_id
 * @param string $lang_code
 * @param string $object_type 'post' or 'term'.
 * @return bool
 */
function wpm_has_translation( $object_id, $lang_code, $object_type = 'post' ) {
	$trans = wpm_get_translation( $object_id, $lang_code, $object_type );
	return ! empty( $trans );
}

/**
 * Get language-aware permalink for a translated post.
 *
 * @param int    $post_id
 * @param string $lang_code
 * @return string
 */
function wpm_get_translated_url( $post_id, $lang_code ) {
	if ( 'page' === get_option( 'show_on_front' ) ) {
		$raw_front_id = (int) get_option( 'page_on_front' );
		if ( $raw_front_id ) {
			$trans_mgr   = TranslationManager::get_instance();
			$front_group = $trans_mgr->get_object_group_id( $raw_front_id, 'post' );
			$this_group  = $trans_mgr->get_object_group_id( (int) $post_id, 'post' );
			if ( (int) $post_id === $raw_front_id || ( $front_group && $front_group === $this_group ) ) {
				return wpm_get_home_url( $lang_code );
			}
		}
	}

	$translated_id = wpm_get_translation( $post_id, $lang_code, 'post' );
	if ( $translated_id ) {
		return get_permalink( $translated_id );
	}
	return wpm_get_home_url( $lang_code );
}

/**
 * Get language code assigned to a taxonomy term.
 *
 * @param int $term_id
 * @return string|null
 */
function wpm_get_term_language( $term_id ) {
	return TranslationManager::get_instance()->get_object_language( $term_id, 'term' );
}

/**
 * Get translated term ID for a given language.
 *
 * @param int    $term_id
 * @param string $lang_code
 * @return int|null
 */
function wpm_get_term_translation( $term_id, $lang_code ) {
	return TranslationManager::get_instance()->get_translation( $term_id, $lang_code, 'term' );
}

/**
 * Get all translations for a taxonomy term.
 *
 * @param int $term_id
 * @return array
 */
function wpm_get_term_translations( $term_id ) {
	return TranslationManager::get_instance()->get_translations( $term_id, 'term' );
}

/**
 * Get homepage URL for a language.
 *
 * @param string|null $lang_code
 * @return string
 */
function wpm_get_home_url( $lang_code = null ) {
	return Rewrite::get_instance()->get_home_url( $lang_code );
}

/**
 * Get URL with language prefix.
 *
 * @param string $lang_code
 * @param string $path
 * @return string
 */
function wpm_get_language_url( $lang_code, $path = '' ) {
	return Rewrite::get_instance()->get_language_url( $lang_code, $path );
}

/**
 * Output language switcher HTML.
 *
 * @param array $args
 */
function wpm_language_switcher( $args = [] ) {
	// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	echo wpm_get_language_switcher( $args );
}

/**
 * Return language switcher HTML string.
 *
 * @param array $args
 * @return string
 */
function wpm_get_language_switcher( $args = [] ) {
	return LanguageSwitcher::get_instance()->render( $args );
}

/**
 * Get all plugin settings.
 *
 * @return array
 */
function wpm_get_settings() {
	$defaults = [
		'url_mode'                   => 'mode_a',
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
	$settings = get_option( 'wpm_settings', [] );
	return wp_parse_args( $settings, $defaults );
}

/**
 * Get single plugin setting.
 *
 * @param string $key
 * @param mixed  $default
 * @return mixed
 */
function wpm_get_setting( $key, $default = null ) {
	$settings = wpm_get_settings();
	return array_key_exists( $key, $settings ) ? $settings[ $key ] : $default;
}

/*
 * =========================================================================
 * Polylang Compatibility Layer (Zero-Config Theme Bridge)
 * =========================================================================
 */

if ( ! function_exists( 'pll_the_languages' ) ) {
	/**
	 * Compatibility bridge for pll_the_languages.
	 *
	 * @param array $args
	 * @return array|string
	 */
	function pll_the_languages( $args = [] ) {
		$raw          = ! empty( $args['raw'] );
		$languages    = wpm_get_languages( [ 'enabled_only' => true ] );
		$current_lang = wpm_get_current_language();
		$post_id      = is_singular() ? ( get_queried_object_id() ?: get_the_ID() ) : 0;
		$translations = $post_id ? wpm_get_translations( $post_id, 'post' ) : [];

		if ( $raw ) {
			$raw_langs = [];
			foreach ( $languages as $lang ) {
				$url = '';
				if ( $post_id && isset( $translations[ $lang->code ] ) ) {
					$url = get_permalink( $translations[ $lang->code ] );
				} else {
					$url = wpm_get_home_url( $lang->code );
				}

				$raw_langs[ $lang->code ] = [
					'id'             => (int) $lang->id,
					'slug'           => $lang->code,
					'name'           => $lang->name,
					'url'            => $url,
					'flag'           => $lang->flag,
					'current_lang'   => ( $lang->code === $current_lang ),
					'no_translation' => false,
				];
			}
			return $raw_langs;
		}

		return wpm_get_language_switcher( $args );
	}
}

if ( ! function_exists( 'pll_register_string' ) ) {
	/**
	 * Compatibility bridge for pll_register_string.
	 *
	 * @param string $name
	 * @param string $string
	 * @param string $group
	 * @param bool   $multiline
	 * @return bool
	 */
	function pll_register_string( $name, $string, $group = 'wp-multilingual', $multiline = false ) {
		return true;
	}
}

if ( ! function_exists( 'pll__' ) ) {
	/**
	 * Compatibility bridge for pll__ string translation.
	 *
	 * @param string $string
	 * @return string
	 */
	function pll__( $string ) {
		return __( $string, 'wp-multilingual' );
	}
}

if ( ! function_exists( 'pll_e' ) ) {
	/**
	 * Compatibility bridge for pll_e string translation.
	 *
	 * @param string $string
	 */
	function pll_e( $string ) {
		_e( $string, 'wp-multilingual' );
	}
}

if ( ! function_exists( 'pll_current_language' ) ) {
	/**
	 * Compatibility bridge for pll_current_language.
	 *
	 * @param string $value 'slug', 'name', or 'locale'
	 * @return string
	 */
	function pll_current_language( $value = 'slug' ) {
		$current = wpm_get_current_language();
		if ( 'name' === $value ) {
			$lang = wpm_get_language( $current );
			return $lang ? $lang->name : $current;
		}
		if ( 'locale' === $value ) {
			$lang = wpm_get_language( $current );
			return $lang ? $lang->locale : $current;
		}
		return $current;
	}
}

if ( ! function_exists( 'pll_default_language' ) ) {
	/**
	 * Compatibility bridge for pll_default_language.
	 *
	 * @param string $value 'slug', 'name', or 'locale'
	 * @return string
	 */
	function pll_default_language( $value = 'slug' ) {
		$default = wpm_get_default_language();
		if ( ! $default ) {
			return 'en';
		}
		if ( 'name' === $value ) {
			return $default->name;
		}
		if ( 'locale' === $value ) {
			return $default->locale;
		}
		return $default->code;
	}
}

if ( ! function_exists( 'pll_get_post' ) ) {
	/**
	 * Compatibility bridge for pll_get_post.
	 *
	 * @param int    $post_id
	 * @param string $slug
	 * @return int|null
	 */
	function pll_get_post( $post_id, $slug = '' ) {
		if ( empty( $slug ) ) {
			$slug = wpm_get_current_language();
		}
		return wpm_get_translation( $post_id, $slug, 'post' );
	}
}

if ( ! function_exists( 'pll_get_term' ) ) {
	/**
	 * Compatibility bridge for pll_get_term.
	 *
	 * @param int    $term_id
	 * @param string $slug
	 * @return int|null
	 */
	function pll_get_term( $term_id, $slug = '' ) {
		if ( empty( $slug ) ) {
			$slug = wpm_get_current_language();
		}
		return wpm_get_term_translation( $term_id, $slug );
	}
}

if ( ! function_exists( 'pll_is_rtl' ) ) {
	/**
	 * Compatibility bridge for pll_is_rtl.
	 *
	 * @return bool
	 */
	function pll_is_rtl() {
		return wpm_is_rtl();
	}
}
