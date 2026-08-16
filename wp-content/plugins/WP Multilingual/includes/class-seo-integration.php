<?php
/**
 * SEO Integration (hreflang, canonicals, Yoast & Rank Math hooks).
 *
 * @package WPMultilingual
 */

namespace WPMultilingual;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class SeoIntegration
 */
class SeoIntegration {

	/**
	 * Singleton instance.
	 *
	 * @var SeoIntegration|null
	 */
	private static $instance = null;

	/**
	 * Get singleton instance.
	 *
	 * @return SeoIntegration
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
	 * Initialize SEO hooks.
	 */
	public function init() {
		add_action( 'wp_head', [ $this, 'output_hreflang_tags' ], 2 );
		add_filter( 'get_canonical_url', [ $this, 'filter_canonical_url' ], 10, 2 );

		// Third-party SEO plugin filters
		add_filter( 'wpseo_canonical', [ $this, 'filter_canonical_url_direct' ], 10, 1 );
		add_filter( 'rank_math/frontend/canonical', [ $this, 'filter_canonical_url_direct' ], 10, 1 );
		add_filter( 'aioseo_canonical_url', [ $this, 'filter_canonical_url_direct' ], 10, 1 );
	}

	/**
	 * Output hreflang alternate links and x-default in wp_head.
	 */
	public function output_hreflang_tags() {
		if ( is_admin() || is_feed() || is_trackback() ) {
			return;
		}

		$lang_mgr  = LanguageManager::get_instance();
		$trans_mgr = TranslationManager::get_instance();
		$languages = $lang_mgr->get_enabled_languages();
		$default   = $lang_mgr->get_default_language();

		if ( empty( $languages ) || count( $languages ) < 2 ) {
			return;
		}

		$links = [];

		if ( is_singular() ) {
			$post_id      = get_queried_object_id() ?: get_the_ID();
			$translations = $trans_mgr->get_translations( $post_id, 'post' );

			foreach ( $languages as $lang ) {
				$trans_id = $translations[ $lang->code ] ?? null;
				if ( $trans_id ) {
					$links[ $lang->code ] = get_permalink( $trans_id );
				}
			}

			// If current post not in list, add itself
			$current_lang = $trans_mgr->get_object_language( $post_id, 'post' );
			if ( $current_lang && ! isset( $links[ $current_lang ] ) ) {
				$links[ $current_lang ] = get_permalink( $post_id );
			}
		} elseif ( is_front_page() || is_home() ) {
			foreach ( $languages as $lang ) {
				$links[ $lang->code ] = wpm_get_home_url( $lang->code );
			}
		} elseif ( is_category() || is_tag() || is_tax() ) {
			$term = get_queried_object();
			if ( $term && isset( $term->term_id ) ) {
				$term_translations = $trans_mgr->get_translations( $term->term_id, 'term' );
				foreach ( $languages as $lang ) {
					$trans_term_id = $term_translations[ $lang->code ] ?? null;
					if ( $trans_term_id ) {
						$link = get_term_link( (int) $trans_term_id, $term->taxonomy );
						if ( ! is_wp_error( $link ) ) {
							$links[ $lang->code ] = $link;
						}
					}
				}
			}
		}

		if ( empty( $links ) ) {
			return;
		}

		/**
		 * Filter hreflang links map before output.
		 *
		 * @param array $links Array of [ 'code' => 'url' ]
		 */
		$links = apply_filters( 'wpm_hreflang_links', $links );

		echo "\n<!-- WP Multilingual SEO Alternate Links -->\n";
		foreach ( $links as $code => $url ) {
			$lang_obj = $lang_mgr->get_language( $code );
			$locale_or_code = $lang_obj ? str_replace( '_', '-', $lang_obj->locale ) : $code;
			printf(
				'<link rel="alternate" hreflang="%s" href="%s" />' . "\n",
				esc_attr( $locale_or_code ),
				esc_url( $url )
			);
		}

		// Output x-default pointing to default language version
		if ( $default && isset( $links[ $default->code ] ) ) {
			printf(
				'<link rel="alternate" hreflang="x-default" href="%s" />' . "\n",
				esc_url( $links[ $default->code ] )
			);
		}
		echo "<!-- / WP Multilingual SEO Alternate Links -->\n";
	}

	/**
	 * Filter canonical URL for current language.
	 *
	 * @param string   $canonical
	 * @param \WP_Post $post
	 * @return string
	 */
	public function filter_canonical_url( $canonical, $post ) {
		if ( ! $canonical || ! $post ) {
			return $canonical;
		}

		$lang = wpm_get_post_language( $post->ID );
		if ( $lang ) {
			return Rewrite::get_instance()->add_language_to_url( $canonical, $lang );
		}

		return $canonical;
	}

	/**
	 * Filter canonical URL string directly.
	 *
	 * @param string $canonical
	 * @return string
	 */
	public function filter_canonical_url_direct( $canonical ) {
		if ( empty( $canonical ) ) {
			return $canonical;
		}

		$current_lang = wpm_get_current_language();
		return Rewrite::get_instance()->add_language_to_url( $canonical, $current_lang );
	}
}
