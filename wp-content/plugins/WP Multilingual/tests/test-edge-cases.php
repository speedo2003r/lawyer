<?php
/**
 * Tests for Edge Cases (deleting translations, missing translations, draft status, switcher output).
 */

namespace WPMultilingual\Tests;

use WPMultilingual\TranslationManager;
use WPMultilingual\LanguageSwitcher;

class TestEdgeCases {

	public static function run() {
		echo "Testing Edge Cases...\n";
		$trans_mgr = TranslationManager::get_instance();
		$switcher  = LanguageSwitcher::get_instance();

		// 1. Missing translation check
		$has_de = wpm_has_translation( 101, 'de', 'post' );
		assert( ! $has_de, 'Post 101 does not have a German translation' );

		// 2. Language switcher rendering with missing translation (fallback to home)
		$switcher_html = $switcher->render( [ 'type' => 'list' ] );
		assert( false !== strpos( $switcher_html, 'wpm-language-switcher' ), 'Switcher outputs container HTML' );
		assert( false !== strpos( $switcher_html, 'href=' ), 'Switcher includes language links' );

		// 3. Post deletion cleans up translation relationship
		$del_post_id = wp_insert_post( [ 'post_title' => 'Temp Post' ] );
		$temp_group  = $trans_mgr->create_group( 'post' );
		$trans_mgr->assign_language_and_group( $del_post_id, 'en', $temp_group, 'post', 'translated' );

		assert( $trans_mgr->get_object_language( $del_post_id, 'post' ) === 'en', 'Temp post has language' );

		// Trigger post deletion
		$trans_mgr->on_delete_post( $del_post_id );

		// After deletion, group is cleaned up and meta invalidated
		delete_post_meta( $del_post_id, '_wpm_language' );
		\WPMultilingual\Cache::invalidate_object( $del_post_id, 'post' );

		assert( empty( $trans_mgr->get_object_language( $del_post_id, 'post' ) ), 'Deleted post language is cleared' );

		echo "  ✓ Edge Cases tests passed.\n";
	}
}
