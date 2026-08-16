<?php
/**
 * Tests for Rewrite & Language-Specific URLs.
 */

namespace WPMultilingual\Tests;

use WPMultilingual\Rewrite;
use WPMultilingual\LanguageManager;

class TestRewrite {

	public static function run() {
		echo "Testing Rewrite & URLs...\n";
		$rewrite  = Rewrite::get_instance();
		$lang_mgr = LanguageManager::get_instance();

		// Mode A: Prefix in all URLs
		update_option( 'wpm_settings', [ 'url_mode' => 'mode_a', 'hide_default_language_url' => 0 ] );

		$en_url = $rewrite->add_language_to_url( 'https://example.com/about-us/', 'en' );
		assert( $en_url === 'https://example.com/en/about-us/', 'Mode A prefixes EN url with /en/' );

		$ar_url = $rewrite->add_language_to_url( 'https://example.com/عن-مكتب-المحاماة/', 'ar' );
		assert( $ar_url === 'https://example.com/ar/عن-مكتب-المحاماة/', 'Mode A prefixes AR url with /ar/' );

		// Mode B: Hide default language prefix
		update_option( 'wpm_settings', [ 'url_mode' => 'mode_b', 'hide_default_language_url' => 1 ] );

		$en_url_b = $rewrite->add_language_to_url( 'https://example.com/about-us/', 'en' );
		assert( $en_url_b === 'https://example.com/about-us/', 'Mode B hides default EN prefix' );

		$ar_url_b = $rewrite->add_language_to_url( 'https://example.com/about-us/', 'ar' );
		assert( $ar_url_b === 'https://example.com/ar/about-us/', 'Mode B keeps non-default AR prefix /ar/' );

		// Home URLs
		$home_ar = $rewrite->get_home_url( 'ar' );
		assert( $home_ar === 'https://example.com/ar/', 'Arabic home URL is https://example.com/ar/' );

		echo "  ✓ Rewrite & URL tests passed.\n";
	}
}
