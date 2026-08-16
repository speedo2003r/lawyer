<?php
/**
 * Tests for REST API Integration.
 */

namespace WPMultilingual\Tests;

use WPMultilingual\RestApi;

class MockRestRequest {
	public $params = [];
	public function __construct( $params = [] ) {
		$this->params = $params;
	}
	public function get_param( $key ) {
		return $this->params[ $key ] ?? null;
	}
}

class TestRestApi {

	public static function run() {
		echo "Testing RestApi...\n";
		$rest = RestApi::get_instance();

		// 1. Test query filter for GET /wp-json/wp/v2/posts?lang=ar
		$req = new MockRestRequest( [ 'lang' => 'ar' ] );
		$args = $rest->filter_rest_query( [], $req );

		assert( ! empty( $args['meta_query'] ), 'REST query filtered with meta_query' );
		assert( $args['meta_query'][0]['key'] === '_wpm_language', 'REST filter key is _wpm_language' );
		assert( $args['meta_query'][0]['value'] === 'ar', 'REST filter value is ar' );

		// 2. Test get_languages endpoint
		$resp = $rest->get_languages( new MockRestRequest() );
		assert( is_array( $resp ) && count( $resp ) >= 2, 'REST get_languages returns language array' );

		echo "  ✓ RestApi tests passed.\n";
	}
}
