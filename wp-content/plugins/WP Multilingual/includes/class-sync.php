<?php
/**
 * Translation Synchronization Manager.
 *
 * @package WPMultilingual
 */

namespace WPMultilingual;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Sync
 */
class Sync {

	/**
	 * Singleton instance.
	 *
	 * @var Sync|null
	 */
	private static $instance = null;

	/**
	 * Get singleton instance.
	 *
	 * @return Sync
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
		// Hook when post is saved to synchronize to sibling translations if configured.
		add_action( 'wpm_after_post_save', [ $this, 'maybe_sync_translations' ], 10, 3 );
	}

	/**
	 * Duplicate content and metadata from source post to a new translated post.
	 *
	 * @param int    $source_post_id
	 * @param string $target_lang_code
	 * @param array  $override_args
	 * @return int|\WP_Error Target post ID or error.
	 */
	public function duplicate_post_to_language( $source_post_id, $target_lang_code, $override_args = [] ) {
		$source_post = get_post( $source_post_id );
		if ( ! $source_post ) {
			return new \WP_Error( 'wpm_invalid_source', __( 'Source post not found.', 'wp-multilingual' ) );
		}

		$lang_mgr = LanguageManager::get_instance();
		$lang     = $lang_mgr->get_language( $target_lang_code );
		if ( ! $lang ) {
			return new \WP_Error( 'wpm_invalid_target_language', __( 'Invalid target language.', 'wp-multilingual' ) );
		}

		$trans_mgr = TranslationManager::get_instance();
		$group_id  = $trans_mgr->get_object_group_id( $source_post_id, 'post' );

		if ( ! $group_id ) {
			// Ensure source post has language & group assigned first
			$source_lang = $trans_mgr->get_object_language( $source_post_id, 'post' );
			if ( ! $source_lang ) {
				$default_lang = $lang_mgr->get_default_language();
				$source_lang  = $default_lang ? $default_lang->code : 'en';
			}
			$group_id = $trans_mgr->create_group( 'post' );
			$trans_mgr->assign_language_and_group( $source_post_id, $source_lang, $group_id, 'post' );
		}

		// Check if target translation already exists in this group
		$existing_target_id = $trans_mgr->get_translation( $source_post_id, $target_lang_code, 'post' );
		if ( $existing_target_id ) {
			return $existing_target_id;
		}

		do_action( 'wpm_before_translation_create', $source_post_id, $target_lang_code );

		// Create post array
		$post_data = [
			'post_title'     => $override_args['post_title'] ?? $source_post->post_title,
			'post_content'   => $override_args['post_content'] ?? $source_post->post_content,
			'post_excerpt'   => $override_args['post_excerpt'] ?? $source_post->post_excerpt,
			'post_status'    => $override_args['post_status'] ?? 'draft',
			'post_type'      => $source_post->post_type,
			'post_author'    => get_current_user_id() ? get_current_user_id() : $source_post->post_author,
			'comment_status' => $source_post->comment_status,
			'ping_status'    => $source_post->ping_status,
			'post_password'  => $source_post->post_password,
			'menu_order'     => $source_post->menu_order,
		];

		// Let WordPress create unique language slug automatically based on title
		if ( ! empty( $override_args['post_name'] ) ) {
			$post_data['post_name'] = sanitize_title( $override_args['post_name'] );
		}

		$new_post_id = wp_insert_post( $post_data );

		if ( is_wp_error( $new_post_id ) || ! $new_post_id ) {
			return new \WP_Error( 'wpm_post_insert_failed', __( 'Failed to create translated post.', 'wp-multilingual' ) );
		}

		// Assign language and link to translation group
		$trans_mgr->assign_language_and_group( $new_post_id, $target_lang_code, $group_id, 'post', 'draft' );

		// Sync Featured Image
		$this->sync_featured_image( $source_post_id, $new_post_id );

		// Sync Page Template
		$template = get_post_meta( $source_post_id, '_wp_page_template', true );
		if ( $template ) {
			update_post_meta( $new_post_id, '_wp_page_template', $template );
		}

		// Sync Custom Fields / Meta
		$this->copy_post_meta( $source_post_id, $new_post_id, $target_lang_code );

		// Sync Taxonomies (with term translation mapping)
		$this->copy_taxonomies( $source_post_id, $new_post_id, $target_lang_code );

		do_action( 'wpm_after_translation_create', $new_post_id, $source_post_id, $target_lang_code );

		return $new_post_id;
	}

	/**
	 * Sync featured image from source to target post.
	 *
	 * @param int $source_id
	 * @param int $target_id
	 */
	public function sync_featured_image( $source_id, $target_id ) {
		$thumbnail_id = get_post_thumbnail_id( $source_id );
		if ( $thumbnail_id ) {
			set_post_thumbnail( $target_id, $thumbnail_id );
		} else {
			delete_post_thumbnail( $target_id );
		}
	}

	/**
	 * Copy safe custom post meta from source to target post.
	 *
	 * @param int    $source_id
	 * @param int    $target_id
	 * @param string $target_lang
	 */
	public function copy_post_meta( $source_id, $target_id, $target_lang ) {
		$all_meta = get_post_meta( $source_id );
		if ( ! is_array( $all_meta ) ) {
			return;
		}

		// Disallowed internal meta keys
		$blocked_keys = [
			'_edit_lock',
			'_edit_last',
			'_wpm_language',
			'_wpm_group_id',
			'_wpm_translation_status',
			'_wp_old_slug',
			'_wp_trash_meta_status',
			'_wp_trash_meta_time',
		];

		$safe_meta_keys = [];
		foreach ( array_keys( $all_meta ) as $meta_key ) {
			if ( in_array( $meta_key, $blocked_keys, true ) ) {
				continue;
			}
			$safe_meta_keys[] = $meta_key;
		}

		/**
		 * Filter safe post meta keys to be copied over to translated post.
		 *
		 * @param array  $safe_meta_keys
		 * @param int    $source_id
		 * @param string $target_lang
		 */
		$filtered_keys = apply_filters( 'wpm_translation_copy_meta', $safe_meta_keys, $source_id, $target_lang );

		if ( is_array( $filtered_keys ) ) {
			foreach ( $filtered_keys as $key ) {
				if ( isset( $all_meta[ $key ] ) ) {
					// Delete existing on target first to avoid duplicates for array values
					delete_post_meta( $target_id, $key );
					foreach ( $all_meta[ $key ] as $value ) {
						// maybe_unserialize to maintain data types
						update_post_meta( $target_id, $key, maybe_unserialize( $value ) );
					}
				}
			}
		}
	}

	/**
	 * Copy taxonomy terms to target post, mapping to translated terms where available.
	 *
	 * @param int    $source_id
	 * @param int    $target_id
	 * @param string $target_lang
	 */
	public function copy_taxonomies( $source_id, $target_id, $target_lang ) {
		$post_type  = get_post_type( $source_id );
		$taxonomies = get_object_taxonomies( $post_type, 'names' );
		$trans_mgr  = TranslationManager::get_instance();

		foreach ( $taxonomies as $taxonomy ) {
			// Skip post format
			if ( 'post_format' === $taxonomy ) {
				continue;
			}

			$terms = wp_get_object_terms( $source_id, $taxonomy, [ 'fields' => 'ids' ] );
			if ( is_wp_error( $terms ) || empty( $terms ) ) {
				continue;
			}

			$target_terms = [];
			foreach ( $terms as $term_id ) {
				// Check if there is a translated term in target_lang
				$translated_term_id = $trans_mgr->get_translation( $term_id, $target_lang, 'term' );
				if ( $translated_term_id ) {
					$target_terms[] = (int) $translated_term_id;
				} else {
					// Fallback to original term if untranslated
					$target_terms[] = (int) $term_id;
				}
			}

			if ( ! empty( $target_terms ) ) {
				wp_set_object_terms( $target_id, $target_terms, $taxonomy );
			}
		}
	}

	/**
	 * Maybe synchronize sibling translations when a post is updated.
	 *
	 * @param int    $post_id
	 * @param string $lang_code
	 * @param int    $group_id
	 */
	public function maybe_sync_translations( $post_id, $lang_code, $group_id ) {
		if ( ! $group_id ) {
			return;
		}

		$settings = wpm_get_settings();
		$trans_mgr = TranslationManager::get_instance();
		$translations = $trans_mgr->get_translations( $post_id, 'post' );

		// Avoid infinite loops
		static $syncing = false;
		if ( $syncing ) {
			return;
		}
		$syncing = true;

		foreach ( $translations as $sibling_lang => $sibling_id ) {
			if ( (int) $sibling_id === (int) $post_id ) {
				continue;
			}

			// Sync featured image if enabled
			if ( ! empty( $settings['sync_featured_image'] ) ) {
				$this->sync_featured_image( $post_id, $sibling_id );
			}

			// Sync template
			$template = get_post_meta( $post_id, '_wp_page_template', true );
			if ( $template ) {
				update_post_meta( $sibling_id, '_wp_page_template', $template );
			}

			// Mark sibling status as needs_update if original was modified
			$sibling_status = get_post_meta( $sibling_id, TranslationManager::META_STATUS, true );
			if ( 'translated' === $sibling_status ) {
				update_post_meta( $sibling_id, TranslationManager::META_STATUS, 'needs_update' );
				// Update custom table status
				global $wpdb;
				$table = $wpdb->prefix . 'wpm_translations';
				$wpdb->update(
					$table,
					[ 'status' => 'needs_update', 'updated_at' => current_time( 'mysql' ) ],
					[ 'object_id' => $sibling_id, 'object_type' => 'post' ],
					[ '%s', '%s' ],
					[ '%d', '%s' ]
				);
			}
		}

		$syncing = false;
	}
}
