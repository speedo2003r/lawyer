<?php
/**
 * Tests for Language Manager.
 */

namespace WPMultilingual\Tests;

use WPMultilingual\LanguageManager;

class TestLanguageManager {

	public static function run() {
		echo "Testing LanguageManager...\n";
		$manager = LanguageManager::get_instance();

		// 1. Add English (Default, LTR)
		$en_id = $manager->add_language( [
			'code'        => 'en',
			'locale'      => 'en_US',
			'name'        => 'English',
			'native_name' => 'English',
			'direction'   => 'ltr',
			'flag'        => '🇺🇸',
			'url_code'    => 'en',
			'is_default'  => 1,
			'is_enabled'  => 1,
		] );

		assert( is_numeric( $en_id ) && $en_id > 0, 'Language EN must be created successfully' );

		// 2. Add Arabic (RTL)
		$ar_id = $manager->add_language( [
			'code'        => 'ar',
			'locale'      => 'ar',
			'name'        => 'Arabic',
			'native_name' => 'العربية',
			'direction'   => 'rtl',
			'flag'        => '🇸🇦',
			'url_code'    => 'ar',
			'is_default'  => 0,
			'is_enabled'  => 1,
		] );

		assert( is_numeric( $ar_id ) && $ar_id > 0, 'Language AR must be created successfully' );

		// 3. Add French (LTR)
		$fr_id = $manager->add_language( [
			'code'        => 'fr',
			'locale'      => 'fr_FR',
			'name'        => 'French',
			'native_name' => 'Français',
			'direction'   => 'ltr',
			'flag'        => '🇫🇷',
			'url_code'    => 'fr',
			'is_default'  => 0,
			'is_enabled'  => 1,
		] );

		assert( is_numeric( $fr_id ) && $fr_id > 0, 'Language FR must be created successfully' );

		// 4. Test lookups
		$en_lang = $manager->get_language( 'en' );
		assert( $en_lang && 'English' === $en_lang->name, 'EN language lookup by code' );

		$ar_lang = $manager->get_language( 'ar' );
		assert( $ar_lang && 'rtl' === $ar_lang->direction, 'AR language direction is RTL' );

		// 5. Test default language
		$default = $manager->get_default_language();
		assert( $default && 'en' === $default->code, 'Default language is EN' );
		assert( $manager->is_default_language( 'en' ), 'is_default_language(en) returns true' );
		assert( ! $manager->is_default_language( 'ar' ), 'is_default_language(ar) returns false' );

		// 6. Test RTL detection
		assert( ! $manager->is_rtl( 'en' ), 'EN is not RTL' );
		assert( $manager->is_rtl( 'ar' ), 'AR is RTL' );

		// 7. Duplicate code rejection
		$dup = $manager->add_language( [
			'code'   => 'en',
			'locale' => 'en_GB',
			'name'   => 'English (UK)',
		] );
		assert( is_wp_error( $dup ), 'Duplicate language code must return WP_Error' );

		echo "  ✓ LanguageManager tests passed.\n";
	}
}
