<?php
/**
 * Cache Wrapper for WP Multilingual.
 *
 * @package WPMultilingual
 */

namespace WPMultilingual;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Cache
 */
class Cache {

	/**
	 * Cache group name.
	 */
	const GROUP = 'wpm_multilingual';

	/**
	 * In-memory runtime cache.
	 *
	 * @var array
	 */
	private static $memory_cache = [];

	/**
	 * Get cached item.
	 *
	 * @param string $key Cache key.
	 * @param bool   $found Output parameter for whether key was found.
	 * @return mixed
	 */
	public static function get( $key, &$found = null ) {
		if ( array_key_exists( $key, self::$memory_cache ) ) {
			$found = true;
			return self::$memory_cache[ $key ];
		}

		$value = wp_cache_get( $key, self::GROUP, false, $found );
		if ( $found ) {
			self::$memory_cache[ $key ] = $value;
			return $value;
		}

		return false;
	}

	/**
	 * Set cached item.
	 *
	 * @param string $key Cache key.
	 * @param mixed  $value Value to cache.
	 * @param int    $expire Expiration in seconds (0 = never).
	 * @return bool
	 */
	public static function set( $key, $value, $expire = 3600 ) {
		self::$memory_cache[ $key ] = $value;
		return wp_cache_set( $key, $value, self::GROUP, $expire );
	}

	/**
	 * Delete cached item.
	 *
	 * @param string $key Cache key.
	 * @return bool
	 */
	public static function delete( $key ) {
		if ( isset( self::$memory_cache[ $key ] ) ) {
			unset( self::$memory_cache[ $key ] );
		}
		return wp_cache_delete( $key, self::GROUP );
	}

	/**
	 * Flush all memory cache and invalidate known cache keys.
	 */
	public static function flush() {
		self::$memory_cache = [];
		wp_cache_flush_group( self::GROUP );
	}

	/**
	 * Invalidate language-related caches.
	 */
	public static function invalidate_languages() {
		self::delete( 'languages_all' );
		self::delete( 'languages_enabled' );
		self::delete( 'language_default' );
	}

	/**
	 * Invalidate group-related cache for an object.
	 *
	 * @param int    $object_id Object ID (post_id or term_id).
	 * @param string $object_type 'post' or 'term'.
	 */
	public static function invalidate_object( $object_id, $object_type = 'post' ) {
		self::delete( "{$object_type}_lang_{$object_id}" );
		self::delete( "{$object_type}_group_{$object_id}" );
		self::delete( "{$object_type}_translations_{$object_id}" );
	}
}
