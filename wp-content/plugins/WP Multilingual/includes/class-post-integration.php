<?php
/**
 * Post & Custom Post Type Translation Integration.
 *
 * @package WPMultilingual
 */

namespace WPMultilingual;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class PostIntegration
 */
class PostIntegration {

	/**
	 * Singleton instance.
	 *
	 * @var PostIntegration|null
	 */
	private static $instance = null;

	/**
	 * Get singleton instance.
	 *
	 * @return PostIntegration
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
		add_action( 'save_post', [ $this, 'on_save_post' ], 10, 2 );
		add_action( 'wp_trash_post', [ $this, 'on_trash_post' ] );
		add_action( 'untrashed_post', [ $this, 'on_untrash_post' ] );

		// AJAX endpoints for admin actions
		add_action( 'wp_ajax_wpm_create_translation', [ $this, 'ajax_create_translation' ] );
		add_action( 'wp_ajax_wpm_unlink_translation', [ $this, 'ajax_unlink_translation' ] );
		add_action( 'wp_ajax_wpm_change_language', [ $this, 'ajax_change_language' ] );
		add_action( 'wp_ajax_wpm_duplicate_content', [ $this, 'ajax_duplicate_content' ] );
	}

	/**
	 * Get all translatable post types.
	 *
	 * @return array
	 */
	public function get_translatable_post_types() {
		$settings   = wpm_get_settings();
		$post_types = $settings['translatable_post_types'] ?? [ 'post', 'page' ];

		if ( ! is_array( $post_types ) ) {
			$post_types = [ 'post', 'page' ];
		}

		/**
		 * Filter translatable post types.
		 *
		 * @param array $post_types
		 */
		return apply_filters( 'wpm_translatable_post_types', $post_types );
	}

	/**
	 * Check if a post type is translatable.
	 *
	 * @param string $post_type
	 * @return bool
	 */
	public function is_translatable_post_type( $post_type ) {
		$translatable = $this->get_translatable_post_types();
		return in_array( $post_type, $translatable, true );
	}

	/**
	 * Handle post save action to assign language and translation group.
	 *
	 * @param int      $post_id
	 * @param \WP_Post $post
	 */
	public function on_save_post( $post_id, $post ) {
		// Ignore revisions and auto-drafts
		if ( wp_is_post_revision( $post_id ) || 'auto-draft' === $post->post_status ) {
			return;
		}

		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		// Prevent infinite recursion during translation duplication or bulk save
		static $saving = [];
		if ( isset( $saving[ $post_id ] ) ) {
			return;
		}
		$saving[ $post_id ] = true;

		if ( ! $this->is_translatable_post_type( $post->post_type ) ) {
			unset( $saving[ $post_id ] );
			return;
		}

		// Security: verify nonce if posted from edit form
		$lang_code = null;
		$status    = 'translated';

		if ( isset( $_POST['wpm_meta_box_nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['wpm_meta_box_nonce'] ) ), 'wpm_save_translation_meta' ) ) {
			if ( ! empty( $_POST['wpm_post_language'] ) ) {
				$lang_code = sanitize_text_field( wp_unslash( $_POST['wpm_post_language'] ) );
			}
			if ( ! empty( $_POST['wpm_translation_status'] ) ) {
				$status = sanitize_text_field( wp_unslash( $_POST['wpm_translation_status'] ) );
			}
		}

		$trans_mgr     = TranslationManager::get_instance();
		$existing_lang = $trans_mgr->get_object_language( $post_id, 'post' );

		// If no language posted, use existing or fallback to default
		if ( empty( $lang_code ) ) {
			if ( $existing_lang ) {
				$lang_code = $existing_lang;
			} else {
				$default_lang = wpm_get_default_language();
				$lang_code    = $default_lang ? $default_lang->code : 'en';
			}
		}

		$group_id = $trans_mgr->get_object_group_id( $post_id, 'post' );
		if ( empty( $group_id ) ) {
			$group_id = $trans_mgr->create_group( 'post' );
		}

		$trans_mgr->assign_language_and_group( $post_id, $lang_code, $group_id, 'post', $status );

		do_action( 'wpm_after_post_save', $post_id, $lang_code, $group_id );

		unset( $saving[ $post_id ] );
	}

	/**
	 * Handle post trash event.
	 *
	 * @param int $post_id
	 */
	public function on_trash_post( $post_id ) {
		Cache::invalidate_object( $post_id, 'post' );
	}

	/**
	 * Handle post untrash event.
	 *
	 * @param int $post_id
	 */
	public function on_untrash_post( $post_id ) {
		Cache::invalidate_object( $post_id, 'post' );
	}

	/**
	 * AJAX: Create translation for a post.
	 */
	public function ajax_create_translation() {
		check_ajax_referer( 'wpm_admin_nonce', 'nonce' );

		if ( ! current_user_can( 'edit_posts' ) ) {
			wp_send_json_error( [ 'message' => __( 'Permission denied.', 'wp-multilingual' ) ] );
		}

		$source_id   = isset( $_POST['source_id'] ) ? absint( $_POST['source_id'] ) : 0;
		$target_lang = isset( $_POST['target_lang'] ) ? sanitize_text_field( wp_unslash( $_POST['target_lang'] ) ) : '';
		$duplicate   = ! empty( $_POST['duplicate_content'] );

		if ( ! $source_id || empty( $target_lang ) ) {
			wp_send_json_error( [ 'message' => __( 'Missing source post or target language.', 'wp-multilingual' ) ] );
		}

		$source_post = get_post( $source_id );
		if ( ! $source_post ) {
			wp_send_json_error( [ 'message' => __( 'Source post not found.', 'wp-multilingual' ) ] );
		}

		$sync = Sync::get_instance();
		$new_id = $sync->duplicate_post_to_language(
			$source_id,
			$target_lang,
			$duplicate ? [] : [ 'post_content' => '', 'post_excerpt' => '' ]
		);

		if ( is_wp_error( $new_id ) ) {
			wp_send_json_error( [ 'message' => $new_id->get_error_message() ] );
		}

		$edit_url = get_edit_post_link( $new_id, 'raw' );

		wp_send_json_success( [
			'post_id'  => $new_id,
			'edit_url' => $edit_url,
			'message'  => __( 'Translation created successfully.', 'wp-multilingual' ),
		] );
	}

	/**
	 * AJAX: Unlink translation from group.
	 */
	public function ajax_unlink_translation() {
		check_ajax_referer( 'wpm_admin_nonce', 'nonce' );

		if ( ! current_user_can( 'edit_posts' ) ) {
			wp_send_json_error( [ 'message' => __( 'Permission denied.', 'wp-multilingual' ) ] );
		}

		$post_id = isset( $_POST['post_id'] ) ? absint( $_POST['post_id'] ) : 0;
		if ( ! $post_id ) {
			wp_send_json_error( [ 'message' => __( 'Invalid post ID.', 'wp-multilingual' ) ] );
		}

		TranslationManager::get_instance()->unlink_translation( $post_id, 'post' );

		wp_send_json_success( [ 'message' => __( 'Translation unlinked successfully.', 'wp-multilingual' ) ] );
	}

	/**
	 * AJAX: Change post language.
	 */
	public function ajax_change_language() {
		check_ajax_referer( 'wpm_admin_nonce', 'nonce' );

		if ( ! current_user_can( 'edit_posts' ) ) {
			wp_send_json_error( [ 'message' => __( 'Permission denied.', 'wp-multilingual' ) ] );
		}

		$post_id  = isset( $_POST['post_id'] ) ? absint( $_POST['post_id'] ) : 0;
		$new_lang = isset( $_POST['new_lang'] ) ? sanitize_text_field( wp_unslash( $_POST['new_lang'] ) ) : '';

		if ( ! $post_id || empty( $new_lang ) ) {
			wp_send_json_error( [ 'message' => __( 'Invalid request parameters.', 'wp-multilingual' ) ] );
		}

		$trans_mgr = TranslationManager::get_instance();
		$group_id  = $trans_mgr->get_object_group_id( $post_id, 'post' );
		$status    = get_post_meta( $post_id, TranslationManager::META_STATUS, true ) ?: 'translated';

		$res = $trans_mgr->assign_language_and_group( $post_id, $new_lang, $group_id, 'post', $status );

		if ( is_wp_error( $res ) ) {
			wp_send_json_error( [ 'message' => $res->get_error_message() ] );
		}

		wp_send_json_success( [ 'message' => __( 'Language updated successfully.', 'wp-multilingual' ) ] );
	}

	/**
	 * AJAX: Duplicate content from another translation in the same group.
	 */
	public function ajax_duplicate_content() {
		check_ajax_referer( 'wpm_admin_nonce', 'nonce' );

		if ( ! current_user_can( 'edit_posts' ) ) {
			wp_send_json_error( [ 'message' => __( 'Permission denied.', 'wp-multilingual' ) ] );
		}

		$source_id = isset( $_POST['source_id'] ) ? absint( $_POST['source_id'] ) : 0;
		$target_id = isset( $_POST['target_id'] ) ? absint( $_POST['target_id'] ) : 0;

		if ( ! $source_id || ! $target_id ) {
			wp_send_json_error( [ 'message' => __( 'Invalid source or target post.', 'wp-multilingual' ) ] );
		}

		$source_post = get_post( $source_id );
		if ( ! $source_post ) {
			wp_send_json_error( [ 'message' => __( 'Source post not found.', 'wp-multilingual' ) ] );
		}

		// Update target post content
		wp_update_post( [
			'ID'           => $target_id,
			'post_title'   => $source_post->post_title,
			'post_content' => $source_post->post_content,
			'post_excerpt' => $source_post->post_excerpt,
		] );

		$target_lang = TranslationManager::get_instance()->get_object_language( $target_id, 'post' );
		$sync        = Sync::get_instance();
		$sync->sync_featured_image( $source_id, $target_id );
		$sync->copy_post_meta( $source_id, $target_id, $target_lang );
		$sync->copy_taxonomies( $source_id, $target_id, $target_lang );

		wp_send_json_success( [
			'title'   => $source_post->post_title,
			'content' => $source_post->post_content,
			'excerpt' => $source_post->post_excerpt,
			'message' => __( 'Content duplicated successfully.', 'wp-multilingual' ),
		] );
	}
}
