<?php
/**
 * Tests for Translation Manager.
 */

namespace WPMultilingual\Tests;

use WPMultilingual\TranslationManager;

class TestTranslationManager {

	public static function run() {
		echo "Testing TranslationManager...\n";
		$manager = TranslationManager::get_instance();

		// 1. Create a translation group
		$group_id = $manager->create_group( 'post' );
		assert( is_numeric( $group_id ) && $group_id > 0, 'Translation group created' );

		// 2. Assign English post (ID 101) to group
		$manager->assign_language_and_group( 101, 'en', $group_id, 'post', 'translated' );
		assert( 'en' === $manager->get_object_language( 101, 'post' ), 'Post 101 language is EN' );
		assert( $group_id === $manager->get_object_group_id( 101, 'post' ), 'Post 101 group matches' );

		// 3. Assign Arabic post (ID 102) to same group
		$manager->assign_language_and_group( 102, 'ar', $group_id, 'post', 'translated' );
		assert( 'ar' === $manager->get_object_language( 102, 'post' ), 'Post 102 language is AR' );

		// 4. Assign French post (ID 103) to same group
		$manager->assign_language_and_group( 103, 'fr', $group_id, 'post', 'draft' );

		// 5. Test lookups
		$trans_en = $manager->get_translations( 101, 'post' );
		assert( isset( $trans_en['en'] ) && $trans_en['en'] === 101, 'Translation map has EN' );
		assert( isset( $trans_en['ar'] ) && $trans_en['ar'] === 102, 'Translation map has AR' );
		assert( isset( $trans_en['fr'] ) && $trans_en['fr'] === 103, 'Translation map has FR' );

		assert( 102 === $manager->get_translation( 101, 'ar', 'post' ), 'Direct translation lookup 101 -> AR is 102' );
		assert( 101 === $manager->get_translation( 102, 'en', 'post' ), 'Direct translation lookup 102 -> EN is 101' );

		// 6. Test details
		$details = $manager->get_translation_details( 101, 'post' );
		assert( isset( $details['fr'] ) && 'draft' === $details['fr']['status'], 'Translation details has FR with draft status' );

		// 7. Test unlinking
		$manager->unlink_translation( 103, 'post' );
		$trans_after = $manager->get_translations( 101, 'post' );
		assert( ! isset( $trans_after['fr'] ), 'FR unlinked from group 1' );

		echo "  ✓ TranslationManager tests passed.\n";
	}
}
