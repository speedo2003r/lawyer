<?php
/**
 * Language Detector for Request Lifecycle.
 *
 * @package WPMultilingual
 */

namespace WPMultilingual;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class LanguageDetector
 */
class LanguageDetector {

	/**
	 * Singleton instance.
	 *
	 * @var LanguageDetector|null
	 */
	private static $instance = null;

	/**
	 * Detected language code cache.
	 *
	 * @var string|null
	 */
	private $detected_language = null;

	/**
	 * Get singleton instance.
	 *
	 * @return LanguageDetector
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
	 * Initialize hooks.
	 */
	public function init() {
		// Detect language on parse_request
		add_action( 'parse_request', [ $this, 'detect_and_set_language' ], 1 );

		// Also listen on init for early language resolution
		add_action( 'init', [ $this, 'early_detect_language' ], 2 );

		// Set cookie on shutdown or template_redirect if needed
		add_action( 'template_redirect', [ $this, 'handle_frontend_detection_and_redirect' ], 1 );
	}

	/**
	 * Early detection on init to ensure language is known as soon as possible.
	 */
	public function early_detect_language() {
		if ( is_admin() || wp_doing_cron() || wp_doing_ajax() || ( defined( 'REST_REQUEST' ) && REST_REQUEST ) ) {
			return;
		}

		$this->detect_language();
	}

	/**
	 * Hook on parse_request to capture query_vars.
	 *
	 * @param \WP $wp
	 */
	public function detect_and_set_language( $wp ) {
		if ( ! empty( $wp->query_vars['lang'] ) ) {
			$lang_code = sanitize_text_field( $wp->query_vars['lang'] );
			$lang_mgr  = LanguageManager::get_instance();
			$lang      = $lang_mgr->get_language_by_url_code( $lang_code ) ?: $lang_mgr->get_language( $lang_code );
			if ( $lang ) {
				$lang_mgr->set_current_language( $lang->code );
				$this->detected_language = $lang->code;
			}
		}
	}

	/**
	 * Primary language resolution algorithm.
	 *
	 * @return string Language code.
	 */
	public function detect_language() {
		if ( null !== $this->detected_language ) {
			return $this->detected_language;
		}

		$lang_mgr = LanguageManager::get_instance();
		$settings = wpm_get_settings();

		// 1. Check URL query parameter (e.g. ?lang=ar)
		if ( ! empty( $_GET['lang'] ) ) {
			$code = sanitize_text_field( wp_unslash( $_GET['lang'] ) );
			$lang = $lang_mgr->get_language( $code );
			if ( $lang && (int) $lang->is_enabled === 1 ) {
				$this->detected_language = $lang->code;
				$lang_mgr->set_current_language( $lang->code );
				return $this->detected_language;
			}
		}

		// 2. Check URL path prefix (e.g. /ar/about-us/)
		$url_lang = $this->detect_from_url_path();
		if ( $url_lang ) {
			$this->detected_language = $url_lang;
			$lang_mgr->set_current_language( $url_lang );
			return $this->detected_language;
		}

		// 3. Check Cookie if enabled
		if ( ! empty( $settings['cookie_enabled'] ) ) {
			$cookie_name = $settings['cookie_name'] ?? 'wpm_language';
			if ( ! empty( $_COOKIE[ $cookie_name ] ) ) {
				$cookie_code = sanitize_text_field( wp_unslash( $_COOKIE[ $cookie_name ] ) );
				$lang        = $lang_mgr->get_language( $cookie_code );
				if ( $lang && (int) $lang->is_enabled === 1 ) {
					$this->detected_language = $lang->code;
					$lang_mgr->set_current_language( $lang->code );
					return $this->detected_language;
				}
			}
		}

		// 4. Check Browser Accept-Language header if enabled
		if ( ! empty( $settings['detect_browser_language'] ) && ! empty( $_SERVER['HTTP_ACCEPT_LANGUAGE'] ) ) {
			$browser_lang = $this->detect_from_browser_header();
			if ( $browser_lang ) {
				$this->detected_language = $browser_lang;
				$lang_mgr->set_current_language( $browser_lang );
				return $this->detected_language;
			}
		}

		// 5. Fallback to default configured language
		$default = $lang_mgr->get_default_language();
		$code    = $default ? $default->code : 'en';

		$this->detected_language = $code;
		$lang_mgr->set_current_language( $code );
		return $this->detected_language;
	}

	/**
	 * Detect language from current request URI path prefix.
	 *
	 * @return string|null
	 */
	private function detect_from_url_path() {
		if ( ! isset( $_SERVER['REQUEST_URI'] ) ) {
			return null;
		}

		$request_uri = wp_unslash( $_SERVER['REQUEST_URI'] );
		$home_path   = trim( (string) wp_parse_url( get_option( 'home' ), PHP_URL_PATH ), '/' );
		$path        = trim( (string) wp_parse_url( $request_uri, PHP_URL_PATH ), '/' );

		if ( ! empty( $home_path ) && 0 === strpos( $path, $home_path ) ) {
			$path = trim( substr( $path, strlen( $home_path ) ), '/' );
		}

		$segments = explode( '/', $path );
		$first    = $segments[0] ?? '';

		if ( ! empty( $first ) ) {
			$first_clean = sanitize_key( urldecode( $first ) );
			$lang_mgr    = LanguageManager::get_instance();
			$lang        = $lang_mgr->get_language_by_url_code( $first_clean ) ?: $lang_mgr->get_language( $first_clean );
			if ( $lang && (int) $lang->is_enabled === 1 ) {
				return $lang->code;
			}
		}

		return null;
	}

	/**
	 * Detect matching enabled language from HTTP_ACCEPT_LANGUAGE header.
	 *
	 * @return string|null
	 */
	private function detect_from_browser_header() {
		if ( empty( $_SERVER['HTTP_ACCEPT_LANGUAGE'] ) ) {
			return null;
		}

		$header    = sanitize_text_field( wp_unslash( $_SERVER['HTTP_ACCEPT_LANGUAGE'] ) );
		$languages = explode( ',', $header );
		$lang_mgr  = LanguageManager::get_instance();
		$enabled   = $lang_mgr->get_enabled_languages();

		foreach ( $languages as $entry ) {
			$parts      = explode( ';', trim( $entry ) );
			$lang_tag   = strtolower( trim( $parts[0] ) );
			$short_code = explode( '-', $lang_tag )[0];

			// Match full locale or short code
			foreach ( $enabled as $lang ) {
				if ( strtolower( $lang->code ) === $short_code || strtolower( $lang->locale ) === str_replace( '-', '_', $lang_tag ) ) {
					return $lang->code;
				}
			}
		}

		return null;
	}

	/**
	 * Set language cookie safely and handle initial browser redirect if hitting root.
	 */
	public function handle_frontend_detection_and_redirect() {
		if ( is_admin() || wp_doing_cron() || wp_doing_ajax() ) {
			return;
		}

		$current_lang = LanguageManager::get_instance()->get_current_language();
		$settings     = wpm_get_settings();

		// Set cookie
		if ( ! empty( $settings['cookie_enabled'] ) && ! headers_sent() ) {
			$cookie_name = $settings['cookie_name'] ?? 'wpm_language';
			$cookie_val  = isset( $_COOKIE[ $cookie_name ] ) ? sanitize_text_field( wp_unslash( $_COOKIE[ $cookie_name ] ) ) : '';
			if ( $cookie_val !== $current_lang ) {
				setcookie(
					$cookie_name,
					$current_lang,
					time() + ( 30 * DAY_IN_SECONDS ),
					COOKIEPATH ? COOKIEPATH : '/',
					COOKIE_DOMAIN,
					is_ssl(),
					false
				);
			}
		}
	}
}
