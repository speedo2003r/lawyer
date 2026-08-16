<?php
/**
 * Tests for WP_Query Language Filtering.
 */

namespace WPMultilingual\Tests;

use WPMultilingual\QueryFilter;
use WPMultilingual\LanguageManager;

class MockWPQuery {
	public $query_vars = [];
	public $is_single = false;
	public $is_page = false;

	public function get( $key, $default = '' ) {
		return $this->query_vars[ $key ] ?? $default;
	}

	public function set( $key, $val ) {
		$this->query_vars[ $key ] = $val;
	}

	public function is_single() { return $this->is_single; }
	public function is_page() { return $this->is_page; }
}

class TestQueryFilter {

	public static function run() {
		echo "Testing QueryFilter (pre_get_posts)...\n";
		$q_filter = QueryFilter::get_instance();
		$lang_mgr = LanguageManager::get_instance();

		// 1. Frontend query when current language is Arabic
		$lang_mgr->set_current_language( 'ar' );

		$query1 = new MockWPQuery();
		$query1->set( 'post_type', 'post' );

		$q_filter->filter_pre_get_posts( $query1 );

		$meta_q1 = $query1->get( 'meta_query' );
		assert( ! empty( $meta_q1 ), 'meta_query is added to WP_Query' );
		assert( $meta_q1[0]['key'] === '_wpm_language', 'meta_query key is _wpm_language' );
		assert( $meta_q1[0]['value'] === 'ar', 'meta_query value is ar' );

		// 2. Frontend query with explicit lang => 'en' override
		$query2 = new MockWPQuery();
		$query2->set( 'post_type', 'post' );
		$query2->set( 'lang', 'en' );

		$q_filter->filter_pre_get_posts( $query2 );

		$meta_q2 = $query2->get( 'meta_query' );
		assert( $meta_q2[0]['value'] === 'en', 'Explicit lang parameter overrides current language' );

		// 3. Query with lang => 'all' bypasses filtering
		$query3 = new MockWPQuery();
		$query3->set( 'post_type', 'post' );
		$query3->set( 'lang', 'all' );

		$q_filter->filter_pre_get_posts( $query3 );

		$meta_q3 = $query3->get( 'meta_query' );
		assert( empty( $meta_q3 ), 'lang => all bypasses language filtering' );

		echo "  ✓ QueryFilter tests passed.\n";
	}
}
