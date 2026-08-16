<?php
/**
 * REST API Integration & Endpoints.
 *
 * @package WPMultilingual
 */

namespace WPMultilingual;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class RestApi
 */
class RestApi {

	/**
	 * Singleton instance.
	 *
	 * @var RestApi|null
	 */
	private static $instance = null;

	/**
	 * Get singleton instance.
	 *
	 * @return RestApi
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
	 * Initialize REST API hooks.
	 */
	public function init() {
		add_action( 'rest_api_init', [ $this, 'register_routes' ] );
		add_action( 'rest_api_init', [ $this, 'register_post_type_fields' ] );

		// Filter REST list queries by lang
		$post_types = PostIntegration::get_instance()->get_translatable_post_types();
		foreach ( $post_types as $pt ) {
			add_filter( "rest_{$pt}_query", [ $this, 'filter_rest_query' ], 10, 2 );
		}
	}

	/**
	 * Register custom REST routes under wpm/v1 namespace.
	 */
	public function register_routes() {
		// GET /wp-json/wpm/v1/languages
		register_rest_route( 'wpm/v1', '/languages', [
			'methods'             => \WP_REST_Server::READABLE,
			'callback'            => [ $this, 'get_languages' ],
			'permission_callback' => '__return_true',
		] );

		// GET /wp-json/wpm/v1/translations/(?P<id>\d+)
		register_rest_route( 'wpm/v1', '/translations/(?P<id>\d+)', [
			'methods'             => \WP_REST_Server::READABLE,
			'callback'            => [ $this, 'get_translations' ],
			'permission_callback' => '__return_true',
			'args'                => [
				'id' => [
					'validate_callback' => function( $param ) {
						return is_numeric( $param );
					},
				],
			],
		] );

		// POST /wp-json/wpm/v1/translations/(?P<id>\d+)
		register_rest_route( 'wpm/v1', '/translations/(?P<id>\d+)', [
			'methods'             => \WP_REST_Server::CREATABLE,
			'callback'            => [ $this, 'create_translation' ],
			'permission_callback' => function() {
				return current_user_can( 'edit_posts' );
			},
			'args'                => [
				'id'          => [
					'validate_callback' => function( $param ) {
						return is_numeric( $param );
					},
				],
				'target_lang' => [
					'required'          => true,
					'type'              => 'string',
					'sanitize_callback' => 'sanitize_text_field',
				],
			],
		] );
	}

	/**
	 * Expose multilingual metadata fields on native post type REST endpoints.
	 */
	public function register_post_type_fields() {
		$post_types = PostIntegration::get_instance()->get_translatable_post_types();

		foreach ( $post_types as $pt ) {
			register_rest_field( $pt, 'wpm_language', [
				'get_callback'    => function( $post_arr ) {
					return wpm_get_post_language( $post_arr['id'] );
				},
				'update_callback' => null,
				'schema'          => [
					'description' => __( 'Language code of the post.', 'wp-multilingual' ),
					'type'        => 'string',
					'context'     => [ 'view', 'edit' ],
				],
			] );

			register_rest_field( $pt, 'wpm_translation_group', [
				'get_callback'    => function( $post_arr ) {
					return wpm_get_translation_group( $post_arr['id'], 'post' );
				},
				'update_callback' => null,
				'schema'          => [
					'description' => __( 'Translation group ID.', 'wp-multilingual' ),
					'type'        => 'integer',
					'context'     => [ 'view', 'edit' ],
				],
			] );

			register_rest_field( $pt, 'wpm_translations', [
				'get_callback'    => function( $post_arr ) {
					return wpm_get_translations( $post_arr['id'], 'post' );
				},
				'update_callback' => null,
				'schema'          => [
					'description' => __( 'Available translations map [lang => post_id].', 'wp-multilingual' ),
					'type'        => 'object',
					'context'     => [ 'view', 'edit' ],
				],
			] );
		}
	}

	/**
	 * Filter REST API collection queries by lang param (e.g. GET /wp-json/wp/v2/posts?lang=ar).
	 *
	 * @param array            $args
	 * @param \WP_REST_Request $request
	 * @return array
	 */
	public function filter_rest_query( $args, $request ) {
		$lang = $request->get_param( 'lang' );
		if ( ! empty( $lang ) && is_string( $lang ) && 'all' !== $lang ) {
			$args['meta_query']   = $args['meta_query'] ?? [];
			$args['meta_query'][] = [
				'key'     => TranslationManager::META_LANG,
				'value'   => sanitize_text_field( $lang ),
				'compare' => '=',
			];
		}
		return $args;
	}

	/**
	 * REST Callback: Get list of all languages.
	 *
	 * @param \WP_REST_Request $request
	 * @return \WP_REST_Response
	 */
	public function get_languages( $request ) {
		$languages = wpm_get_languages();
		return rest_ensure_response( array_values( $languages ) );
	}

	/**
	 * REST Callback: Get translations for a post.
	 *
	 * @param \WP_REST_Request $request
	 * @return \WP_REST_Response|\WP_Error
	 */
	public function get_translations( $request ) {
		$post_id = absint( $request->get_param( 'id' ) );
		$post    = get_post( $post_id );

		if ( ! $post ) {
			return new \WP_Error( 'wpm_not_found', __( 'Post not found.', 'wp-multilingual' ), [ 'status' => 404 ] );
		}

		$trans_mgr = TranslationManager::get_instance();
		$details   = $trans_mgr->get_translation_details( $post_id, 'post' );

		return rest_ensure_response( [
			'post_id'      => $post_id,
			'language'     => $trans_mgr->get_object_language( $post_id, 'post' ),
			'group_id'     => $trans_mgr->get_object_group_id( $post_id, 'post' ),
			'translations' => $details,
		] );
	}

	/**
	 * REST Callback: Create translation for a post.
	 *
	 * @param \WP_REST_Request $request
	 * @return \WP_REST_Response|\WP_Error
	 */
	public function create_translation( $request ) {
		$source_id   = absint( $request->get_param( 'id' ) );
		$target_lang = sanitize_text_field( $request->get_param( 'target_lang' ) );
		$duplicate   = $request->get_param( 'duplicate' ) !== false;

		$source_post = get_post( $source_id );
		if ( ! $source_post ) {
			return new \WP_Error( 'wpm_not_found', __( 'Source post not found.', 'wp-multilingual' ), [ 'status' => 404 ] );
		}

		$sync   = Sync::get_instance();
		$new_id = $sync->duplicate_post_to_language(
			$source_id,
			$target_lang,
			$duplicate ? [] : [ 'post_content' => '', 'post_excerpt' => '' ]
		);

		if ( is_wp_error( $new_id ) ) {
			return $new_id;
		}

		return rest_ensure_response( [
			'success'  => true,
			'post_id'  => $new_id,
			'edit_url' => get_edit_post_link( $new_id, 'raw' ),
		] );
	}
}
