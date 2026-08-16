<?php
/**
 * Query Filter for Language Scoping.
 *
 * @package WPMultilingual
 */

namespace WPMultilingual;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class QueryFilter
 */
class QueryFilter {

	/**
	 * Singleton instance.
	 *
	 * @var QueryFilter|null
	 */
	private static $instance = null;

	/**
	 * Get singleton instance.
	 *
	 * @return QueryFilter
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
	 * Initialize query filters.
	 */
	public function init() {
		add_action( 'pre_get_posts', [ $this, 'filter_pre_get_posts' ], 10, 1 );
	}

	/**
	 * Filter WP_Query instances by language.
	 *
	 * @param \WP_Query $query
	 */
	public function filter_pre_get_posts( $query ) {
		// Check explicit 'lang' query parameter on WP_Query
		$query_lang = $query->get( 'lang' );

		// If lang is explicitly set to 'all' or false, bypass filtering
		if ( 'all' === $query_lang || false === $query_lang ) {
			return;
		}

		// Handle Admin: Only filter if on edit.php with explicit lang filter
		if ( is_admin() ) {
			// Handled separately in AdminColumns for edit screen
			return;
		}

		// Don't filter WP background jobs, cron
		if ( wp_doing_cron() ) {
			return;
		}

		// Determine target language
		$lang = ! empty( $query_lang ) ? sanitize_text_field( $query_lang ) : wpm_get_current_language();

		// Check if querying specific post by ID or name in a single post request
		if ( $query->is_single() || $query->is_page() ) {
			// Single item queries already resolve by ID or unique slug
			return;
		}

		// Filter translatable post types only
		$post_type = $query->get( 'post_type' );
		if ( empty( $post_type ) ) {
			$post_type = 'post';
		}

		$trans_post_types = PostIntegration::get_instance()->get_translatable_post_types();

		// If querying non-translatable types (like attachment, nav_menu_item), skip
		if ( is_string( $post_type ) && ! in_array( $post_type, $trans_post_types, true ) && 'any' !== $post_type ) {
			return;
		}

		// Apply meta query filter for _wpm_language
		$meta_query = $query->get( 'meta_query' );
		if ( ! is_array( $meta_query ) ) {
			$meta_query = [];
		}

		$meta_query[] = [
			'key'     => TranslationManager::META_LANG,
			'value'   => $lang,
			'compare' => '=',
		];

		$query->set( 'meta_query', $meta_query );
	}
}
