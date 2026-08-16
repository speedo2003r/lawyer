<?php
/**
 * URL Rewriting & Permalink Filter Manager.
 *
 * @package WPMultilingual
 */

namespace WPMultilingual;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Rewrite
 */
class Rewrite {

	/**
	 * Singleton instance.
	 *
	 * @var Rewrite|null
	 */
	private static $instance = null;

	/**
	 * Get singleton instance.
	 *
	 * @return Rewrite
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
	 * Initialize rewrite hooks.
	 */
	public function init() {
		add_action( 'init', [ $this, 'register_rewrites' ], 1 );
		add_filter( 'query_vars', [ $this, 'register_query_vars' ] );

		// Permalink filters
		add_filter( 'post_link', [ $this, 'filter_post_link' ], 10, 2 );
		add_filter( 'page_link', [ $this, 'filter_page_link' ], 10, 2 );
		add_filter( 'post_type_link', [ $this, 'filter_post_type_link' ], 10, 2 );
		add_filter( 'post_type_archive_link', [ $this, 'filter_post_type_archive_link' ], 10, 2 );
		add_filter( 'term_link', [ $this, 'filter_term_link' ], 10, 3 );
		add_filter( 'home_url', [ $this, 'filter_home_url' ], 10, 4 );
		add_filter( 'wp_nav_menu_objects', [ $this, 'filter_nav_menu_objects' ], 10, 2 );

		// Front page & Posts page translation filters
		add_filter( 'option_page_on_front', [ $this, 'filter_page_on_front' ] );
		add_filter( 'option_page_for_posts', [ $this, 'filter_page_for_posts' ] );
		add_filter( 'request', [ $this, 'filter_request' ] );
		add_action( 'template_redirect', [ $this, 'redirect_front_page_slug' ] );
	}

	/**
	 * Register rewrite tags and rules.
	 */
	public function register_rewrites() {
		add_rewrite_tag( '%lang%', '([a-zA-Z]{2,10})' );

		$languages = wpm_get_languages( [ 'enabled_only' => true ] );
		if ( empty( $languages ) ) {
			return;
		}

		$lang_codes = [];
		foreach ( $languages as $lang ) {
			$lang_codes[] = preg_quote( $lang->url_code ? $lang->url_code : $lang->code, '#' );
		}

		$pattern = implode( '|', $lang_codes );

		// Language home root (e.g. /ar/ or /en/)
		add_rewrite_rule(
			'^(' . $pattern . ')/?$',
			'index.php?lang=$matches[1]',
			'top'
		);

		// Language pagination root (e.g. /ar/page/2/)
		add_rewrite_rule(
			'^(' . $pattern . ')/page/?([0-9]{1,})/?$',
			'index.php?lang=$matches[1]&paged=$matches[2]',
			'top'
		);

		// Language search (e.g. /ar/search/query/)
		add_rewrite_rule(
			'^(' . $pattern . ')/search/(.+)/?$',
			'index.php?lang=$matches[1]&s=$matches[2]',
			'top'
		);

		// Single posts / pages / CPTs prefixed with language
		add_rewrite_rule(
			'^(' . $pattern . ')/(.+?)/page/?([0-9]{1,})/?$',
			'index.php?lang=$matches[1]&name=$matches[2]&paged=$matches[3]',
			'top'
		);

		add_rewrite_rule(
			'^(' . $pattern . ')/(.+?)/?$',
			'index.php?lang=$matches[1]&name=$matches[2]',
			'top'
		);
	}

	/**
	 * Whitelist lang query variable.
	 *
	 * @param array $vars
	 * @return array
	 */
	public function register_query_vars( $vars ) {
		$vars[] = 'lang';
		return $vars;
	}

	/**
	 * Check if default language URL prefix should be omitted.
	 *
	 * @param string $lang_code
	 * @return bool
	 */
	public function should_hide_language_prefix( $lang_code ) {
		$settings = wpm_get_settings();
		if ( ! empty( $settings['hide_default_language_url'] ) || 'mode_b' === ( $settings['url_mode'] ?? '' ) ) {
			return wpm_is_default_language( $lang_code );
		}
		return false;
	}

	/**
	 * Get language URL prefix for given code.
	 *
	 * @param string $lang_code
	 * @return string Prefix with trailing slash or empty string.
	 */
	public function get_language_prefix( $lang_code ) {
		if ( empty( $lang_code ) || $this->should_hide_language_prefix( $lang_code ) ) {
			return '';
		}

		$lang = wpm_get_language( $lang_code );
		$slug = ( $lang && ! empty( $lang->url_code ) ) ? $lang->url_code : $lang_code;

		return $slug . '/';
	}

	/**
	 * Add language prefix to an existing URL.
	 *
	 * @param string $url
	 * @param string $lang_code
	 * @return string
	 */
	public function add_language_to_url( $url, $lang_code ) {
		if ( empty( $url ) || empty( $lang_code ) ) {
			return $url;
		}

		$home = untrailingslashit( get_option( 'home' ) );
		if ( empty( $home ) || 0 !== strpos( $url, $home ) ) {
			return $url;
		}

		$path = substr( $url, strlen( $home ) );
		$path = ltrim( $path, '/' );

		// Strip existing language code if already in path
		$languages = wpm_get_languages();
		foreach ( $languages as $l ) {
			$code = $l->url_code ? $l->url_code : $l->code;
			if ( 0 === strpos( $path, $code . '/' ) ) {
				$path = substr( $path, strlen( $code . '/' ) );
				break;
			} elseif ( $path === $code ) {
				$path = '';
				break;
			}
		}

		$prefix  = $this->get_language_prefix( $lang_code );
		$new_url = trailingslashit( $home ) . $prefix . $path;

		// Clean multiple slashes except http:// or https://
		$scheme  = wp_parse_url( $new_url, PHP_URL_SCHEME );
		$without = preg_replace( '#^https?://#', '', $new_url );
		$cleaned = preg_replace( '#/+#', '/', $without );

		return $scheme . '://' . $cleaned;
	}

	/**
	 * Filter post permalinks.
	 *
	 * @param string   $permalink
	 * @param \WP_Post $post
	 * @return string
	 */
	public function filter_post_link( $permalink, $post ) {
		if ( ! is_object( $post ) ) {
			$post = get_post( $post );
		}
		if ( ! $post ) {
			return $permalink;
		}

		if ( class_exists( __NAMESPACE__ . '\\PostIntegration' ) && ! PostIntegration::get_instance()->is_translatable_post_type( $post->post_type ) ) {
			return $permalink;
		}

		$lang = wpm_get_post_language( $post->ID );
		if ( ! $lang ) {
			$lang = wpm_get_current_language();
		}

		$url = $this->add_language_to_url( $permalink, $lang );
		return apply_filters( 'wpm_post_link', $url, $post->ID, $lang );
	}

	/**
	 * Filter page permalinks.
	 *
	 * @param string $permalink
	 * @param int    $post_id
	 * @return string
	 */
	public function filter_page_link( $permalink, $post_id ) {
		if ( class_exists( __NAMESPACE__ . '\\PostIntegration' ) && ! PostIntegration::get_instance()->is_translatable_post_type( 'page' ) ) {
			return $permalink;
		}

		$lang = wpm_get_post_language( $post_id );
		if ( ! $lang ) {
			$lang = wpm_get_current_language();
		}

		// If this page is the static front page or a translation of it, return clean home URL
		if ( 'page' === get_option( 'show_on_front' ) ) {
			$raw_front_id = (int) get_option( 'page_on_front' );
			if ( $raw_front_id ) {
				$trans_mgr   = TranslationManager::get_instance();
				$front_group = $trans_mgr->get_object_group_id( $raw_front_id, 'post' );
				$this_group  = $trans_mgr->get_object_group_id( (int) $post_id, 'post' );
				if ( (int) $post_id === $raw_front_id || ( $front_group && $front_group === $this_group ) ) {
					$url = $this->get_home_url( $lang );
					return apply_filters( 'wpm_page_link', $url, $post_id, $lang );
				}
			}
		}

		$url = $this->add_language_to_url( $permalink, $lang );
		return apply_filters( 'wpm_page_link', $url, $post_id, $lang );
	}

	/**
	 * Filter custom post type permalinks.
	 *
	 * @param string   $permalink
	 * @param \WP_Post $post
	 * @return string
	 */
	public function filter_post_type_link( $permalink, $post ) {
		if ( ! is_object( $post ) ) {
			$post = get_post( $post );
		}
		if ( ! $post ) {
			return $permalink;
		}

		if ( class_exists( __NAMESPACE__ . '\\PostIntegration' ) && ! PostIntegration::get_instance()->is_translatable_post_type( $post->post_type ) ) {
			return $permalink;
		}

		$lang = wpm_get_post_language( $post->ID );
		if ( ! $lang ) {
			$lang = wpm_get_current_language();
		}

		$url = $this->add_language_to_url( $permalink, $lang );
		return apply_filters( 'wpm_post_type_link', $url, $post->ID, $lang );
	}

	/**
	 * Filter term permalinks.
	 *
	 * @param string   $url
	 * @param \WP_Term $term
	 * @param string   $taxonomy
	 * @return string
	 */
	public function filter_term_link( $url, $term, $taxonomy ) {
		if ( ! is_object( $term ) ) {
			return $url;
		}

		$lang = wpm_get_term_language( $term->term_id );
		if ( ! $lang ) {
			$lang = wpm_get_current_language();
		}

		return $this->add_language_to_url( $url, $lang );
	}

	/**
	 * Filter home URL when appropriate with recursion protection.
	 *
	 * @param string      $url
	 * @param string      $path
	 * @param string|null $orig_scheme
	 * @param int|null    $blog_id
	 * @return string
	 */
	public function filter_home_url( $url, $path, $orig_scheme, $blog_id ) {
		static $is_filtering = false;
		if ( $is_filtering ) {
			return $url;
		}

		// Don't modify in admin, rest, ajax, login
		if ( is_admin() || wp_doing_ajax() || ( defined( 'REST_REQUEST' ) && REST_REQUEST ) || false !== strpos( (string) $path, 'wp-login.php' ) || false !== strpos( (string) $path, 'wp-admin' ) ) {
			return $url;
		}

		// Don't modify during admin redirects (e.g., after saving settings)
		if ( is_admin() && false !== strpos( $_SERVER['REQUEST_URI'] ?? '', 'options-permalink.php' ) ) {
			return $url;
		}

		// Don't modify if path is an internal endpoint or asset
		if ( 0 === strpos( (string) $path, 'wp-json' ) || 0 === strpos( (string) $path, 'xmlrpc.php' ) ) {
			return $url;
		}

		$is_filtering = true;
		$current_lang = wpm_get_current_language();
		$filtered_url = $this->add_language_to_url( $url, $current_lang );
		$is_filtering = false;

		return $filtered_url;
	}

	/**
	 * Get homepage URL for specific language.
	 *
	 * @param string|null $lang_code
	 * @return string
	 */
	public function get_home_url( $lang_code = null ) {
		if ( null === $lang_code ) {
			$lang_code = wpm_get_current_language();
		}

		$home   = get_option( 'home' );
		$prefix = $this->get_language_prefix( $lang_code );

		return trailingslashit( trailingslashit( $home ) . $prefix );
	}

	/**
	 * Get arbitrary path URL for specific language.
	 *
	 * @param string $lang_code
	 * @param string $path
	 * @return string
	 */
	public function get_language_url( $lang_code, $path = '' ) {
		$home   = get_option( 'home' );
		$prefix = $this->get_language_prefix( $lang_code );
		$clean  = ltrim( $path, '/' );

		return trailingslashit( $home ) . $prefix . $clean;
	}

	/**
	 * Filter page_on_front option by current language on frontend.
	 *
	 * @param mixed $page_id
	 * @return mixed
	 */
	public function filter_page_on_front( $page_id ) {
		if ( empty( $page_id ) || ( is_admin() && ! wp_doing_ajax() ) ) {
			return $page_id;
		}

		$current_lang = wpm_get_current_language();
		if ( ! $current_lang ) {
			return $page_id;
		}

		$trans_id = wpm_get_translation( (int) $page_id, $current_lang, 'post' );
		return $trans_id ?: $page_id;
	}

	/**
	 * Filter page_for_posts option by current language on frontend.
	 *
	 * @param mixed $page_id
	 * @return mixed
	 */
	public function filter_page_for_posts( $page_id ) {
		if ( empty( $page_id ) || ( is_admin() && ! wp_doing_ajax() ) ) {
			return $page_id;
		}

		$current_lang = wpm_get_current_language();
		if ( ! $current_lang ) {
			return $page_id;
		}

		$trans_id = wpm_get_translation( (int) $page_id, $current_lang, 'post' );
		return $trans_id ?: $page_id;
	}

	/**
	 * Filter request query variables for language home roots.
	 *
	 * @param array $query_vars
	 * @return array
	 */
	public function filter_request( $query_vars ) {
		if ( isset( $query_vars['lang'] ) && 1 === count( $query_vars ) ) {
			if ( 'page' === get_option( 'show_on_front' ) ) {
				$lang_mgr    = LanguageManager::get_instance();
				$lang_obj    = $lang_mgr->get_language_by_url_code( $query_vars['lang'] ) ?: $lang_mgr->get_language( $query_vars['lang'] );
				$target_lang = $lang_obj ? $lang_obj->code : $query_vars['lang'];
				$lang_mgr->set_current_language( $target_lang );

				$front_page_id = (int) get_option( 'page_on_front' );
				if ( $front_page_id ) {
					$query_vars['page_id'] = $front_page_id;
				}
			}
		}
		return $query_vars;
	}

	/**
	 * Redirect any direct visit to the front page slug to the clean home URL.
	 */
	public function redirect_front_page_slug() {
		if ( is_admin() || ! is_page() ) {
			return;
		}

		if ( 'page' === get_option( 'show_on_front' ) ) {
			$raw_front_id = (int) get_option( 'page_on_front' );
			$current_id   = (int) get_queried_object_id();
			if ( $raw_front_id && $current_id ) {
				$trans_mgr   = TranslationManager::get_instance();
				$front_group = $trans_mgr->get_object_group_id( $raw_front_id, 'post' );
				$this_group  = $trans_mgr->get_object_group_id( $current_id, 'post' );

				if ( $current_id === $raw_front_id || ( $front_group && $front_group === $this_group ) ) {
					$req_uri    = trim( (string) wp_parse_url( $_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH ), '/' );
					$lang       = wpm_get_post_language( $current_id ) ?: wpm_get_current_language();
					$target_url = $this->get_home_url( $lang );
					$target_uri = trim( (string) wp_parse_url( $target_url, PHP_URL_PATH ), '/' );

					if ( $req_uri !== $target_uri ) {
						wp_safe_redirect( $target_url, 301 );
						exit;
					}
				}
			}
		}
	}

	/**
	 * Filter post type archive link to include language prefix.
	 *
	 * @param string $link
	 * @param string $post_type
	 * @return string
	 */
	public function filter_post_type_archive_link( $link, $post_type ) {
		if ( class_exists( __NAMESPACE__ . '\\PostIntegration' ) && ! PostIntegration::get_instance()->is_translatable_post_type( $post_type ) ) {
			return $link;
		}

		$lang = wpm_get_current_language();
		return $this->add_language_to_url( $link, $lang );
	}

	/**
	 * Filter WordPress navigation menu items to switch to current language translation.
	 *
	 * @param array $sorted_menu_items
	 * @param array $args
	 * @return array
	 */
	public function filter_nav_menu_objects( $sorted_menu_items, $args ) {
		$current_lang = wpm_get_current_language();
		if ( empty( $current_lang ) || empty( $sorted_menu_items ) ) {
			return $sorted_menu_items;
		}

		$trans_mgr = TranslationManager::get_instance();

		foreach ( $sorted_menu_items as $item ) {
			if ( 'post_type' === $item->type && ! empty( $item->object_id ) ) {
				$trans_id = $trans_mgr->get_translation( (int) $item->object_id, $current_lang, 'post' );
				if ( $trans_id && (int) $trans_id !== (int) $item->object_id ) {
					$trans_post = get_post( $trans_id );
					if ( $trans_post ) {
						$item->object_id = $trans_id;
						$item->url       = get_permalink( $trans_id );
						if ( empty( $item->post_title ) || $item->title === get_the_title( (int) $item->object_id ) ) {
							$item->title = $trans_post->post_title;
						}
					}
				}
			} elseif ( 'taxonomy' === $item->type && ! empty( $item->object_id ) ) {
				$trans_id = $trans_mgr->get_translation( (int) $item->object_id, $current_lang, 'term' );
				if ( $trans_id && (int) $trans_id !== (int) $item->object_id ) {
					$trans_term = get_term( $trans_id, $item->object );
					if ( $trans_term && ! is_wp_error( $trans_term ) ) {
						$item->object_id = $trans_id;
						$item->url       = get_term_link( $trans_term, $item->object );
						$item->title     = $trans_term->name;
					}
				}
			}
		}

		return $sorted_menu_items;
	}
}
