<?php
/**
 * Translation Manager for Relationship and Group Operations.
 *
 * @package WPMultilingual
 */

namespace WPMultilingual;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class TranslationManager
 */
class TranslationManager {

	/**
	 * Meta keys used for quick lookup and WP_Query compatibility.
	 */
	const META_LANG   = '_wpm_language';
	const META_GROUP  = '_wpm_group_id';
	const META_STATUS = '_wpm_translation_status';

	/**
	 * Singleton instance.
	 *
	 * @var TranslationManager|null
	 */
	private static $instance = null;

	/**
	 * Get singleton instance.
	 *
	 * @return TranslationManager
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
	 * Initialize hooks if any.
	 */
	public function init() {
		// Clean up translation relations when posts or terms are deleted.
		add_action( 'deleted_post', [ $this, 'on_delete_post' ] );
		add_action( 'delete_term', [ $this, 'on_delete_term' ], 10, 3 );
	}

	/**
	 * Create a new translation group.
	 *
	 * @param string $object_type 'post' or 'term'.
	 * @return int Group ID.
	 */
	public function create_group( $object_type = 'post' ) {
		global $wpdb;

		$table = $wpdb->prefix . 'wpm_translation_groups';
		$wpdb->insert(
			$table,
			[
				'object_type' => sanitize_key( $object_type ),
				'created_at'  => current_time( 'mysql' ),
			],
			[ '%s', '%s' ]
		);

		return (int) $wpdb->insert_id;
	}

	/**
	 * Assign language and translation group to an object (post or term).
	 *
	 * @param int         $object_id
	 * @param string      $lang_code
	 * @param int|null    $group_id
	 * @param string      $object_type
	 * @param string      $status 'not_translated', 'draft', 'translated', 'needs_update'
	 * @return bool|\WP_Error
	 */
	public function assign_language_and_group( $object_id, $lang_code, $group_id = null, $object_type = 'post', $status = 'translated' ) {
		global $wpdb;

		$object_id   = absint( $object_id );
		$object_type = sanitize_key( $object_type );
		$lang_mgr    = LanguageManager::get_instance();
		$lang        = $lang_mgr->get_language( $lang_code );

		if ( ! $lang ) {
			return new \WP_Error( 'wpm_invalid_language', __( 'Specified language does not exist.', 'wp-multilingual' ) );
		}

		// If no group ID provided, find existing or create a new group
		if ( empty( $group_id ) ) {
			$existing_group = $this->get_object_group_id( $object_id, $object_type );
			if ( $existing_group ) {
				$group_id = $existing_group;
			} else {
				$group_id = $this->create_group( $object_type );
			}
		} else {
			$group_id = absint( $group_id );
		}

		$table_trans = $wpdb->prefix . 'wpm_translations';

		// Check if an entry already exists for this (group_id, language_id)
		$existing_row = $wpdb->get_row(
			$wpdb->prepare(
				"SELECT id, object_id FROM {$table_trans} WHERE group_id = %d AND language_id = %d",
				$group_id,
				$lang->id
			)
		);

		$valid_statuses = [ 'not_translated', 'draft', 'translated', 'needs_update' ];
		if ( ! in_array( $status, $valid_statuses, true ) ) {
			$status = 'translated';
		}

		$now = current_time( 'mysql' );

		if ( $existing_row ) {
			// If existing entry points to another object, remove its meta
			if ( (int) $existing_row->object_id !== $object_id ) {
				$this->remove_object_meta( (int) $existing_row->object_id, $object_type );
			}

			// Update translation entry
			$wpdb->update(
				$table_trans,
				[
					'object_id'   => $object_id,
					'object_type' => $object_type,
					'status'      => $status,
					'updated_at'  => $now,
				],
				[ 'id' => $existing_row->id ],
				[ '%d', '%s', '%s', '%s' ],
				[ '%d' ]
			);
		} else {
			// Also check if object_id is currently in another translation row and remove it
			$wpdb->delete(
				$table_trans,
				[
					'object_id'   => $object_id,
					'object_type' => $object_type,
				],
				[ '%d', '%s' ]
			);

			// Insert new translation entry
			$wpdb->insert(
				$table_trans,
				[
					'group_id'    => $group_id,
					'language_id' => $lang->id,
					'object_id'   => $object_id,
					'object_type' => $object_type,
					'status'      => $status,
					'created_at'  => $now,
					'updated_at'  => $now,
				],
				[ '%d', '%d', '%d', '%s', '%s', '%s', '%s' ]
			);
		}

		// Update postmeta / termmeta for fast WP_Query filtering
		$this->set_object_meta( $object_id, $object_type, $lang->code, $group_id, $status );

		// Invalidate cache
		Cache::invalidate_object( $object_id, $object_type );

		do_action( 'wpm_translation_assigned', $object_id, $lang->code, $group_id, $object_type );

		return true;
	}

	/**
	 * Get language code assigned to an object.
	 *
	 * @param int    $object_id
	 * @param string $object_type 'post' or 'term'.
	 * @return string|null
	 */
	public function get_object_language( $object_id, $object_type = 'post' ) {
		$object_id = absint( $object_id );
		if ( ! $object_id ) {
			return null;
		}

		$cache_key = "{$object_type}_lang_{$object_id}";
		$found     = false;
		$cached    = Cache::get( $cache_key, $found );
		if ( $found ) {
			return $cached;
		}

		// Check native meta first for maximum speed
		if ( 'post' === $object_type ) {
			$lang = get_post_meta( $object_id, self::META_LANG, true );
		} else {
			$lang = get_term_meta( $object_id, self::META_LANG, true );
		}

		if ( ! empty( $lang ) ) {
			Cache::set( $cache_key, $lang );
			return $lang;
		}

		// Fallback query to custom table
		global $wpdb;
		$table_trans = $wpdb->prefix . 'wpm_translations';
		$table_lang  = $wpdb->prefix . 'wpm_languages';

		$code = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT l.code FROM {$table_trans} t
				 JOIN {$table_lang} l ON t.language_id = l.id
				 WHERE t.object_id = %d AND t.object_type = %s LIMIT 1",
				$object_id,
				$object_type
			)
		);

		if ( $code ) {
			$this->set_object_meta( $object_id, $object_type, $code );
			Cache::set( $cache_key, $code );
			return $code;
		}

		return null;
	}

	/**
	 * Get group ID for an object.
	 *
	 * @param int    $object_id
	 * @param string $object_type 'post' or 'term'.
	 * @return int|null
	 */
	public function get_object_group_id( $object_id, $object_type = 'post' ) {
		$object_id = absint( $object_id );
		if ( ! $object_id ) {
			return null;
		}

		$cache_key = "{$object_type}_group_{$object_id}";
		$found     = false;
		$cached    = Cache::get( $cache_key, $found );
		if ( $found ) {
			return $cached ? (int) $cached : null;
		}

		if ( 'post' === $object_type ) {
			$group_id = get_post_meta( $object_id, self::META_GROUP, true );
		} else {
			$group_id = get_term_meta( $object_id, self::META_GROUP, true );
		}

		if ( ! empty( $group_id ) ) {
			$group_id = (int) $group_id;
			Cache::set( $cache_key, $group_id );
			return $group_id;
		}

		global $wpdb;
		$table_trans = $wpdb->prefix . 'wpm_translations';
		$db_group_id = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT group_id FROM {$table_trans} WHERE object_id = %d AND object_type = %s LIMIT 1",
				$object_id,
				$object_type
			)
		);

		if ( $db_group_id ) {
			$group_id = (int) $db_group_id;
			$this->set_object_meta( $object_id, $object_type, null, $group_id );
			Cache::set( $cache_key, $group_id );
			return $group_id;
		}

		return null;
	}

	/**
	 * Get translated object ID for a specific language.
	 *
	 * @param int    $object_id
	 * @param string $lang_code
	 * @param string $object_type
	 * @return int|null
	 */
	public function get_translation( $object_id, $lang_code, $object_type = 'post' ) {
		$translations = $this->get_translations( $object_id, $object_type );
		return $translations[ $lang_code ] ?? null;
	}

	/**
	 * Get all translation map [ lang_code => object_id ] for an object.
	 *
	 * @param int    $object_id
	 * @param string $object_type
	 * @return array
	 */
	public function get_translations( $object_id, $object_type = 'post' ) {
		$object_id = absint( $object_id );
		if ( ! $object_id ) {
			return [];
		}

		$cache_key = "{$object_type}_translations_{$object_id}";
		$found     = false;
		$cached    = Cache::get( $cache_key, $found );
		if ( $found && is_array( $cached ) ) {
			return $cached;
		}

		$group_id = $this->get_object_group_id( $object_id, $object_type );
		if ( ! $group_id ) {
			// Post has no translation group yet, only return self if has language
			$lang = $this->get_object_language( $object_id, $object_type );
			$res  = $lang ? [ $lang => $object_id ] : [];
			Cache::set( $cache_key, $res );
			return $res;
		}

		global $wpdb;
		$table_trans = $wpdb->prefix . 'wpm_translations';
		$table_lang  = $wpdb->prefix . 'wpm_languages';

		$rows = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT l.code, t.object_id FROM {$table_trans} t
				 JOIN {$table_lang} l ON t.language_id = l.id
				 WHERE t.group_id = %d AND t.object_type = %s",
				$group_id,
				$object_type
			)
		);

		$translations = [];
		if ( is_array( $rows ) ) {
			foreach ( $rows as $row ) {
				$translations[ $row->code ] = (int) $row->object_id;
			}
		}

		Cache::set( $cache_key, $translations );

		return $translations;
	}

	/**
	 * Get detailed translation records including status and language object.
	 *
	 * @param int    $object_id
	 * @param string $object_type
	 * @return array Keyed by language code.
	 */
	public function get_translation_details( $object_id, $object_type = 'post' ) {
		$object_id = absint( $object_id );
		if ( ! $object_id ) {
			return [];
		}

		$group_id = $this->get_object_group_id( $object_id, $object_type );
		if ( ! $group_id ) {
			return [];
		}

		global $wpdb;
		$table_trans = $wpdb->prefix . 'wpm_translations';
		$table_lang  = $wpdb->prefix . 'wpm_languages';

		$rows = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT t.object_id, t.status, t.updated_at, l.code, l.name, l.native_name, l.flag, l.direction, l.url_code
				 FROM {$table_trans} t
				 JOIN {$table_lang} l ON t.language_id = l.id
				 WHERE t.group_id = %d AND t.object_type = %s",
				$group_id,
				$object_type
			)
		);

		$details = [];
		if ( is_array( $rows ) ) {
			foreach ( $rows as $row ) {
				$details[ $row->code ] = [
					'object_id'   => (int) $row->object_id,
					'status'      => $row->status,
					'updated_at'  => $row->updated_at,
					'code'        => $row->code,
					'name'        => $row->name,
					'native_name' => $row->native_name,
					'flag'        => $row->flag,
					'direction'   => $row->direction,
					'url_code'    => $row->url_code,
				];
			}
		}

		return $details;
	}

	/**
	 * Unlink an object from its translation group.
	 *
	 * @param int    $object_id
	 * @param string $object_type
	 * @return bool
	 */
	public function unlink_translation( $object_id, $object_type = 'post' ) {
		global $wpdb;

		$object_id = absint( $object_id );
		$lang      = $this->get_object_language( $object_id, $object_type );
		$old_group = $this->get_object_group_id( $object_id, $object_type );

		if ( ! $object_id || ! $old_group ) {
			return true;
		}

		$table_trans = $wpdb->prefix . 'wpm_translations';

		// Delete relation from old group
		$wpdb->delete(
			$table_trans,
			[
				'object_id'   => $object_id,
				'object_type' => $object_type,
			],
			[ '%d', '%s' ]
		);

		// If it has a language, create a new isolated single-member group
		if ( $lang ) {
			$new_group = $this->create_group( $object_type );
			$this->assign_language_and_group( $object_id, $lang, $new_group, $object_type );
		} else {
			$this->remove_object_meta( $object_id, $object_type );
		}

		// Invalidate old group cache
		$this->cleanup_empty_group( $old_group, $object_type );
		Cache::invalidate_object( $object_id, $object_type );

		do_action( 'wpm_translation_unlinked', $object_id, $old_group, $object_type );

		return true;
	}

	/**
	 * Set or update object meta.
	 *
	 * @param int         $object_id
	 * @param string      $object_type
	 * @param string|null $lang
	 * @param int|null    $group_id
	 * @param string|null $status
	 */
	private function set_object_meta( $object_id, $object_type = 'post', $lang = null, $group_id = null, $status = null ) {
		if ( 'post' === $object_type ) {
			if ( null !== $lang ) {
				update_post_meta( $object_id, self::META_LANG, $lang );
			}
			if ( null !== $group_id ) {
				update_post_meta( $object_id, self::META_GROUP, $group_id );
			}
			if ( null !== $status ) {
				update_post_meta( $object_id, self::META_STATUS, $status );
			}
		} else {
			if ( null !== $lang ) {
				update_term_meta( $object_id, self::META_LANG, $lang );
			}
			if ( null !== $group_id ) {
				update_term_meta( $object_id, self::META_GROUP, $group_id );
			}
			if ( null !== $status ) {
				update_term_meta( $object_id, self::META_STATUS, $status );
			}
		}
	}

	/**
	 * Remove object meta keys.
	 *
	 * @param int    $object_id
	 * @param string $object_type
	 */
	private function remove_object_meta( $object_id, $object_type = 'post' ) {
		if ( 'post' === $object_type ) {
			delete_post_meta( $object_id, self::META_LANG );
			delete_post_meta( $object_id, self::META_GROUP );
			delete_post_meta( $object_id, self::META_STATUS );
		} else {
			delete_term_meta( $object_id, self::META_LANG );
			delete_term_meta( $object_id, self::META_GROUP );
			delete_term_meta( $object_id, self::META_STATUS );
		}
	}

	/**
	 * Clean up group record if all translations have been deleted.
	 *
	 * @param int    $group_id
	 * @param string $object_type
	 */
	public function cleanup_empty_group( $group_id, $object_type = 'post' ) {
		global $wpdb;

		$group_id = absint( $group_id );
		if ( ! $group_id ) {
			return;
		}

		$table_trans  = $wpdb->prefix . 'wpm_translations';
		$table_groups = $wpdb->prefix . 'wpm_translation_groups';

		$remaining = (int) $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$table_trans} WHERE group_id = %d",
				$group_id
			)
		);

		if ( 0 === $remaining ) {
			$wpdb->delete( $table_groups, [ 'id' => $group_id ], [ '%d' ] );
		}
	}

	/**
	 * Cleanup translation records on post deletion.
	 *
	 * @param int $post_id
	 */
	public function on_delete_post( $post_id ) {
		$group_id = $this->get_object_group_id( $post_id, 'post' );
		global $wpdb;
		$table = $wpdb->prefix . 'wpm_translations';
		$wpdb->delete( $table, [ 'object_id' => $post_id, 'object_type' => 'post' ], [ '%d', '%s' ] );

		if ( $group_id ) {
			$this->cleanup_empty_group( $group_id, 'post' );
		}
		Cache::invalidate_object( $post_id, 'post' );
	}

	/**
	 * Cleanup translation records on term deletion.
	 *
	 * @param int    $term_id
	 * @param int    $tt_id
	 * @param string $taxonomy
	 */
	public function on_delete_term( $term_id, $tt_id, $taxonomy ) {
		$group_id = $this->get_object_group_id( $term_id, 'term' );
		global $wpdb;
		$table = $wpdb->prefix . 'wpm_translations';
		$wpdb->delete( $table, [ 'object_id' => $term_id, 'object_type' => 'term' ], [ '%d', '%s' ] );

		if ( $group_id ) {
			$this->cleanup_empty_group( $group_id, 'term' );
		}
		Cache::invalidate_object( $term_id, 'term' );
	}
}
