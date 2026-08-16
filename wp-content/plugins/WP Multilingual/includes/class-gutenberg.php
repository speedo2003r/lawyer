<?php
/**
 * Gutenberg Block Editor Integration.
 *
 * @package WPMultilingual
 */

namespace WPMultilingual;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Gutenberg
 */
class Gutenberg {

	/**
	 * Singleton instance.
	 *
	 * @var Gutenberg|null
	 */
	private static $instance = null;

	/**
	 * Get singleton instance.
	 *
	 * @return Gutenberg
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
	 * Initialize Gutenberg block and meta registrations.
	 */
	public function init() {
		add_action( 'init', [ $this, 'register_blocks' ] );
		add_action( 'init', [ $this, 'register_post_meta' ] );
	}

	/**
	 * Register Gutenberg block type.
	 */
	public function register_blocks() {
		wp_register_script(
			'wpm-block-edit',
			WPM_PLUGIN_URL . 'blocks/language-switcher/edit.js',
			[ 'wp-blocks', 'wp-element', 'wp-block-editor', 'wp-components', 'wp-i18n' ],
			WPM_VERSION,
			true
		);

		register_block_type( WPM_PLUGIN_DIR . 'blocks/language-switcher', [
			'render_callback' => [ $this, 'render_block_callback' ],
		] );
	}

	/**
	 * Server-side render callback for language switcher block.
	 *
	 * @param array  $attributes
	 * @param string $content
	 * @return string
	 */
	public function render_block_callback( $attributes, $content ) {
		$args = [
			'type'                   => $attributes['type'] ?? 'list',
			'show_flags'             => ! empty( $attributes['showFlags'] ),
			'show_names'             => ! empty( $attributes['showNames'] ),
			'show_native_names'      => ! empty( $attributes['showNativeNames'] ),
			'only_with_translations' => ! empty( $attributes['onlyWithTranslations'] ),
		];

		return wpm_get_language_switcher( $args );
	}

	/**
	 * Register post meta for REST & block editor access.
	 */
	public function register_post_meta() {
		$post_types = PostIntegration::get_instance()->get_translatable_post_types();

		foreach ( $post_types as $pt ) {
			register_post_meta( $pt, TranslationManager::META_LANG, [
				'show_in_rest' => true,
				'single'       => true,
				'type'         => 'string',
				'auth_callback' => function() {
					return current_user_can( 'edit_posts' );
				},
			] );

			register_post_meta( $pt, TranslationManager::META_GROUP, [
				'show_in_rest' => true,
				'single'       => true,
				'type'         => 'integer',
				'auth_callback' => function() {
					return current_user_can( 'edit_posts' );
				},
			] );

			register_post_meta( $pt, TranslationManager::META_STATUS, [
				'show_in_rest' => true,
				'single'       => true,
				'type'         => 'string',
				'auth_callback' => function() {
					return current_user_can( 'edit_posts' );
				},
			] );
		}
	}
}
