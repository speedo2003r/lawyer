<?php
/**
 * Tests for Post Integration, Duplication & Slugs.
 */

namespace WPMultilingual\Tests;

use WPMultilingual\Sync;
use WPMultilingual\TranslationManager;
use WPMultilingual\PostIntegration;

class TestPostIntegration {

	public static function run() {
		echo "Testing PostIntegration & Duplication...\n";
		$sync       = Sync::get_instance();
		$trans_mgr  = TranslationManager::get_instance();
		$post_integ = PostIntegration::get_instance();

		// 1. Create original English post
		$en_post_id = wp_insert_post( [
			'post_title'   => 'About Our Law Firm',
			'post_content' => 'We are a premier law firm specialized in corporate law.',
			'post_excerpt' => 'Premier law firm',
			'post_name'    => 'about-our-law-firm',
		] );
		update_post_meta( $en_post_id, '_thumbnail_id', 999 );
		update_post_meta( $en_post_id, 'custom_field_phone', '+123456789' );
		update_post_meta( $en_post_id, '_edit_lock', '12345678:1' ); // Should NOT be copied

		// Assign language
		$group_id = $trans_mgr->create_group( 'post' );
		$trans_mgr->assign_language_and_group( $en_post_id, 'en', $group_id, 'post', 'translated' );

		// 2. Duplicate to Arabic
		$ar_post_id = $sync->duplicate_post_to_language( $en_post_id, 'ar' );

		assert( is_numeric( $ar_post_id ) && $ar_post_id > 0, 'Arabic post duplicated successfully' );
		assert( $ar_post_id !== $en_post_id, 'Arabic post is a distinct post ID' );

		// Verify Arabic post details
		$ar_post = get_post( $ar_post_id );
		assert( $ar_post->post_title === 'About Our Law Firm', 'Duplicated title matches initial source' );
		assert( $ar_post->post_content === 'We are a premier law firm specialized in corporate law.', 'Duplicated content matches' );

		// Verify translation relationship
		assert( $trans_mgr->get_object_group_id( $ar_post_id, 'post' ) === $group_id, 'Both posts share same translation group' );
		assert( $trans_mgr->get_object_language( $ar_post_id, 'post' ) === 'ar', 'Arabic post has AR language code' );

		// Verify safe meta duplication
		assert( (int) get_post_meta( $ar_post_id, '_thumbnail_id', true ) === 999, 'Featured image synced' );
		assert( get_post_meta( $ar_post_id, 'custom_field_phone', true ) === '+123456789', 'Custom meta field copied' );
		assert( empty( get_post_meta( $ar_post_id, '_edit_lock', true ) ), 'Disallowed internal meta not copied' );

		// 3. Test Slug Independence
		// Admin updates Arabic post with Arabic title & slug
		wp_update_post( [
			'ID'         => $ar_post_id,
			'post_title' => 'عن مكتب المحاماة',
			'post_name'  => 'عن-مكتب-المحاماة',
		] );

		$updated_ar = get_post( $ar_post_id );
		$en_post    = get_post( $en_post_id );

		assert( $updated_ar->post_name === 'عن-مكتب-المحاماة', 'Arabic post has independent Arabic slug' );
		assert( $en_post->post_name === 'about-our-law-firm', 'English post maintains its English slug' );

		// 4. Test translatable post types check
		assert( $post_integ->is_translatable_post_type( 'post' ), 'post is translatable' );
		assert( $post_integ->is_translatable_post_type( 'page' ), 'page is translatable' );
		assert( ! $post_integ->is_translatable_post_type( 'attachment' ), 'attachment is not translatable' );

		echo "  ✓ PostIntegration & Duplication tests passed.\n";
	}
}
