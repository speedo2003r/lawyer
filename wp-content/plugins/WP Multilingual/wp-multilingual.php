<?php
/**
 * Plugin Name:       WP Multilingual
 * Plugin URI:        https://example.com/wp-multilingual
 * Description:       A lightweight, production-ready translation and language management system for WordPress.
 * Version:           1.0.0
 * Author:            Antigravity Architect
 * Author URI:        https://example.com
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       wp-multilingual
 * Domain Path:       /languages
 * Requires at least: 5.8
 * Requires PHP:      7.4
 *
 * @package           WPMultilingual
 */

// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Plugin Constants
 */
define( 'WPM_VERSION', '1.0.0' );
define( 'WPM_PLUGIN_FILE', __FILE__ );
define( 'WPM_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'WPM_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'WPM_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );

/**
 * Autoloader for WPMultilingual classes.
 *
 * Maps namespace WPMultilingual\... to includes/class-*.php or includes/subdir/class-*.php
 *
 * @param string $class Class name.
 */
spl_autoload_register( function ( $class ) {
	$prefix = 'WPMultilingual\\';
	$base_dir = WPM_PLUGIN_DIR . 'includes/';

	$len = strlen( $prefix );
	if ( strncmp( $prefix, $class, $len ) !== 0 ) {
		return;
	}

	$relative_class = substr( $class, $len );
	$parts          = explode( '\\', $relative_class );
	$class_name     = array_pop( $parts );

	// Convert CamelCase or UpperCamel to class-{name}.php format
	$file_name = 'class-' . strtolower( str_replace( '_', '-', preg_replace( '/(?<!^)[A-Z]/', '-$0', $class_name ) ) ) . '.php';

	// Clean double dashes if any
	$file_name = preg_replace( '/-+/', '-', $file_name );

	$sub_path = '';
	if ( ! empty( $parts ) ) {
		$sub_path = strtolower( implode( '/', $parts ) ) . '/';
	}

	$file = $base_dir . $sub_path . $file_name;

	if ( file_exists( $file ) ) {
		require_once $file;
	}
} );

// Load global helpers and procedural public API.
require_once WPM_PLUGIN_DIR . 'includes/helpers.php';

/**
 * Activation Hook
 */
register_activation_hook( __FILE__, function () {
	\WPMultilingual\Installer::activate();
} );

/**
 * Deactivation Hook
 */
register_deactivation_hook( __FILE__, function () {
	\WPMultilingual\Installer::deactivate();
} );

/**
 * Initialize Plugin Lifecycle on plugins_loaded.
 */
add_action( 'plugins_loaded', function () {
	\WPMultilingual\Plugin::get_instance()->init();
} );
