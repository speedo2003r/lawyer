<?php
/**
 * Master Test Runner for WP Multilingual.
 *
 * Can be run via CLI: `php tests/run-tests.php` or included in test suites.
 *
 * @package WPMultilingual
 */

require_once __DIR__ . '/bootstrap.php';
require_once __DIR__ . '/test-language-manager.php';
require_once __DIR__ . '/test-translation-manager.php';
require_once __DIR__ . '/test-post-integration.php';
require_once __DIR__ . '/test-taxonomy-integration.php';
require_once __DIR__ . '/test-query-filter.php';
require_once __DIR__ . '/test-rewrite.php';
require_once __DIR__ . '/test-rest-api.php';
require_once __DIR__ . '/test-edge-cases.php';

// Enable assertion exceptions/warnings
ini_set( 'assert.exception', 1 );

echo "==================================================\n";
echo "RUNNING WP MULTILINGUAL AUTOMATED TEST SUITE\n";
echo "==================================================\n\n";

$start_time = microtime( true );

try {
	\WPMultilingual\Tests\TestLanguageManager::run();
	\WPMultilingual\Tests\TestTranslationManager::run();
	\WPMultilingual\Tests\TestPostIntegration::run();
	\WPMultilingual\Tests\TestTaxonomyIntegration::run();
	\WPMultilingual\Tests\TestQueryFilter::run();
	\WPMultilingual\Tests\TestRewrite::run();
	\WPMultilingual\Tests\TestRestApi::run();
	\WPMultilingual\Tests\TestEdgeCases::run();

	$elapsed = round( ( microtime( true ) - $start_time ) * 1000, 2 );

	echo "\n==================================================\n";
	echo "ALL 8 TEST SUITES COMPLETED SUCCESSFULLY! ($elapsed ms)\n";
	echo "==================================================\n";
} catch ( \Throwable $e ) {
	echo "\n❌ TEST FAILED: " . $e->getMessage() . "\n";
	echo "File: " . $e->getFile() . " (Line " . $e->getLine() . ")\n";
	exit( 1 );
}
