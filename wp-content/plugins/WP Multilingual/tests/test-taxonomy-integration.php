<?php
/**
 * Tests for Taxonomy Integration.
 */

namespace WPMultilingual\Tests;

use WPMultilingual\TranslationManager;
use WPMultilingual\TaxonomyIntegration;

class TestTaxonomyIntegration {

	public static function run() {
		echo "Testing TaxonomyIntegration...\n";
		$trans_mgr = TranslationManager::get_instance();
		$tax_integ = TaxonomyIntegration::get_instance();

		global $wp_terms;
		// 1. Create English Category (term_id = 10)
		$wp_terms[10] = (object) [
			'term_id'  => 10,
			'name'     => 'Family Law',
			'slug'     => 'family-law',
			'taxonomy' => 'category',
		];

		// 2. Create Arabic Category (term_id = 20)
		$wp_terms[20] = (object) [
			'term_id'  => 20,
			'name'     => 'قانون الأسرة',
			'slug'     => 'قانون-الأسرة',
			'taxonomy' => 'category',
		];

		// 3. Link them in translation group
		$group_id = $trans_mgr->create_group( 'term' );
		$trans_mgr->assign_language_and_group( 10, 'en', $group_id, 'term', 'translated' );
		$trans_mgr->assign_language_and_group( 20, 'ar', $group_id, 'term', 'translated' );

		// 4. Verify term language and relationship
		assert( $trans_mgr->get_object_language( 10, 'term' ) === 'en', 'Term 10 language is EN' );
		assert( $trans_mgr->get_object_language( 20, 'term' ) === 'ar', 'Term 20 language is AR' );
		assert( $trans_mgr->get_translation( 10, 'ar', 'term' ) === 20, 'Term 10 AR translation is 20' );
		assert( $trans_mgr->get_translation( 20, 'en', 'term' ) === 10, 'Term 20 EN translation is 10' );

		// 5. Test translatable taxonomy check
		assert( $tax_integ->is_translatable_taxonomy( 'category' ), 'category is translatable' );
		assert( $tax_integ->is_translatable_taxonomy( 'post_tag' ), 'post_tag is translatable' );

		echo "  ✓ TaxonomyIntegration tests passed.\n";
	}
}
