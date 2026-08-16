<?php
/**
 * Test Bootstrap & WordPress Mock Environment for standalone test execution.
 *
 * @package WPMultilingual
 */

if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/../' );
}

define( 'COOKIEPATH', '/' );
define( 'COOKIE_DOMAIN', '' );
define( 'DAY_IN_SECONDS', 86400 );

// Mock global WordPress state
global $wpdb, $wp_actions, $wp_filters, $wp_options, $wp_posts, $wp_postmeta, $wp_terms, $wp_termmeta;

$wp_actions  = [];
$wp_filters  = [];
$wp_options  = [ 'home' => 'https://example.com' ];
$wp_posts    = [];
$wp_postmeta = [];
$wp_terms    = [];
$wp_termmeta = [];

/**
 * Mock WPDB
 */
class MockWPDB {
	public $prefix = 'wp_';
	public $postmeta = 'wp_postmeta';
	public $termmeta = 'wp_termmeta';
	public $insert_id = 0;
	public $tables_data = [];

	public function __construct() {
		$this->tables_data = [
			'wp_wpm_languages'          => [],
			'wp_wpm_translation_groups' => [],
			'wp_wpm_translations'       => [],
			'wp_postmeta'               => [],
			'wp_termmeta'               => [],
		];
	}

	public function get_charset_collate() {
		return 'DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci';
	}

	public function prepare( $query, ...$args ) {
		if ( empty( $args ) ) {
			return $query;
		}
		if ( is_array( $args[0] ) ) {
			$args = $args[0];
		}
		$i = 0;
		return preg_replace_callback( '/(%s|%d|%f)/', function ( $m ) use ( &$i, $args ) {
			$val = $args[ $i++ ] ?? '';
			return is_numeric( $val ) ? $val : "'" . addslashes( $val ) . "'";
		}, $query );
	}

	public function query( $sql ) {
		return true;
	}

	public function insert( $table, $data, $format = null ) {
		$this->insert_id++;
		$row = (object) array_merge( [ 'id' => $this->insert_id ], $data );
		$this->tables_data[ $table ][ $this->insert_id ] = $row;
		return 1;
	}

	public function update( $table, $data, $where, $format = null, $where_format = null ) {
		if ( ! isset( $this->tables_data[ $table ] ) ) {
			return 0;
		}
		$count = 0;
		foreach ( $this->tables_data[ $table ] as $id => $row ) {
			$match = true;
			foreach ( $where as $w_k => $w_v ) {
				if ( ! isset( $row->$w_k ) || (string) $row->$w_k !== (string) $w_v ) {
					$match = false;
					break;
				}
			}
			if ( $match ) {
				foreach ( $data as $d_k => $d_v ) {
					$row->$d_k = $d_v;
				}
				$count++;
			}
		}
		return $count;
	}

	public function delete( $table, $where, $where_format = null ) {
		if ( ! isset( $this->tables_data[ $table ] ) ) {
			return 0;
		}
		$count = 0;
		foreach ( $this->tables_data[ $table ] as $id => $row ) {
			$match = true;
			foreach ( $where as $w_k => $w_v ) {
				if ( ! isset( $row->$w_k ) || (string) $row->$w_k !== (string) $w_v ) {
					$match = false;
					break;
				}
			}
			if ( $match ) {
				unset( $this->tables_data[ $table ][ $id ] );
				$count++;
			}
		}
		return $count;
	}

	public function get_row( $query, $output = OBJECT ) {
		$results = $this->get_results( $query, $output );
		return ! empty( $results ) ? $results[0] : null;
	}

	public function get_var( $query ) {
		$row = $this->get_row( $query, ARRAY_A );
		if ( is_array( $row ) ) {
			return reset( $row );
		}
		return null;
	}

	public function get_results( $query, $output = OBJECT ) {
		// Mock simple parser for wpm tables
		if ( false !== strpos( $query, 'SHOW TABLES LIKE' ) ) {
			return [ (object) [ 'table' => 'wp_wpm_languages' ] ];
		}
		if ( false !== strpos( $query, 'wp_wpm_languages' ) ) {
			$rows = array_values( $this->tables_data['wp_wpm_languages'] ?? [] );
			// Check WHERE is_enabled = 1
			if ( false !== strpos( $query, 'WHERE is_enabled = 1' ) ) {
				$rows = array_filter( $rows, function( $r ) { return (int) $r->is_enabled === 1; } );
			}
			if ( false !== strpos( $query, 'WHERE code =' ) || false !== strpos( $query, 'WHERE code =' ) ) {
				preg_match( "/code = '([^']+)'/i", $query, $m );
				if ( ! empty( $m[1] ) ) {
					$rows = array_filter( $rows, function( $r ) use ( $m ) { return $r->code === $m[1]; } );
				}
			}
			if ( false !== strpos( $query, 'WHERE id =' ) ) {
				preg_match( "/id = (\d+)/i", $query, $m );
				if ( ! empty( $m[1] ) ) {
					$rows = array_filter( $rows, function( $r ) use ( $m ) { return (int) $r->id === (int) $m[1]; } );
				}
			}
			return array_values( $rows );
		}

		if ( false !== strpos( $query, 'wp_wpm_translations' ) ) {
			$trans = array_values( $this->tables_data['wp_wpm_translations'] ?? [] );
			$langs = $this->tables_data['wp_wpm_languages'] ?? [];

			// Parse group_id
			if ( preg_match( '/group_id = (\d+)/i', $query, $gm ) ) {
				$gid = (int) $gm[1];
				$trans = array_filter( $trans, function( $r ) use ( $gid ) { return (int) $r->group_id === $gid; } );
			}
			// Parse object_id
			if ( preg_match( '/object_id = (\d+)/i', $query, $om ) ) {
				$oid = (int) $om[1];
				$trans = array_filter( $trans, function( $r ) use ( $oid ) { return (int) $r->object_id === $oid; } );
			}

			// JOIN languages
			$joined = [];
			foreach ( $trans as $t ) {
				$l = $langs[ $t->language_id ] ?? null;
				if ( $l ) {
					$copy = clone $t;
					$copy->code        = $l->code;
					$copy->name        = $l->name;
					$copy->native_name = $l->native_name;
					$copy->flag        = $l->flag;
					$copy->direction   = $l->direction;
					$copy->url_code    = $l->url_code;
					$joined[] = $copy;
				}
			}
			return $joined;
		}

		if ( false !== strpos( $query, 'wp_wpm_translation_groups' ) ) {
			return array_values( $this->tables_data['wp_wpm_translation_groups'] ?? [] );
		}

		return [];
	}
}

$wpdb = new MockWPDB();

// Mock WP core functions
function dbDelta( $sql ) { return true; }
function wp_cache_get( $key, $group = '', $force = false, &$found = null ) { $found = false; return false; }
function wp_cache_set( $key, $val, $group = '', $expire = 0 ) { return true; }
function wp_cache_delete( $key, $group = '' ) { return true; }
function wp_cache_flush_group( $group ) { return true; }
function wp_cache_flush() { return true; }

function sanitize_text_field( $str ) { return is_string( $str ) ? strip_tags( trim( $str ) ) : $str; }
function sanitize_key( $key ) { return strtolower( preg_replace( '/[^a-z0-9_\-]/i', '', $key ) ); }
function sanitize_title( $title ) { return strtolower( trim( preg_replace( '/[^a-zA-Z0-9_\-\x{0600}-\x{06FF}]+/u', '-', $title ), '-' ) ); }
function absint( $v ) { return abs( (int) $v ); }
function wp_unslash( $val ) { return $val; }
function wp_parse_args( $args, $defaults = [] ) { return is_array( $args ) ? array_merge( $defaults, $args ) : $defaults; }
function maybe_unserialize( $val ) { return $val; }
function current_time( $type ) { return date( 'Y-m-d H:i:s' ); }
function trailingslashit( $str ) { return rtrim( $str, '/' ) . '/'; }

function add_action( $tag, $callback, $priority = 10, $accepted_args = 1 ) {
	global $wp_actions;
	$wp_actions[ $tag ][] = $callback;
}
function do_action( $tag, ...$args ) {
	global $wp_actions;
	if ( ! empty( $wp_actions[ $tag ] ) ) {
		foreach ( $wp_actions[ $tag ] as $cb ) {
			call_user_func_array( $cb, $args );
		}
	}
}
function add_filter( $tag, $callback, $priority = 10, $accepted_args = 1 ) {
	global $wp_filters;
	$wp_filters[ $tag ][] = $callback;
}
function apply_filters( $tag, $value, ...$args ) {
	global $wp_filters;
	if ( ! empty( $wp_filters[ $tag ] ) ) {
		foreach ( $wp_filters[ $tag ] as $cb ) {
			$value = call_user_func_array( $cb, array_merge( [ $value ], $args ) );
		}
	}
	return $value;
}

function get_option( $name, $default = false ) {
	global $wp_options;
	return $wp_options[ $name ] ?? $default;
}
function update_option( $name, $value ) {
	global $wp_options;
	$wp_options[ $name ] = $value;
	return true;
}
function add_option( $name, $value ) {
	global $wp_options;
	if ( ! isset( $wp_options[ $name ] ) ) {
		$wp_options[ $name ] = $value;
	}
	return true;
}
function delete_option( $name ) {
	global $wp_options;
	unset( $wp_options[ $name ] );
	return true;
}
function set_transient( $name, $val, $exp ) { return update_option( '_transient_' . $name, $val ); }
function get_transient( $name ) { return get_option( '_transient_' . $name, false ); }
function delete_transient( $name ) { return delete_option( '_transient_' . $name ); }

function get_post_meta( $post_id, $key = '', $single = false ) {
	global $wp_postmeta;
	if ( empty( $key ) ) {
		return $wp_postmeta[ $post_id ] ?? [];
	}
	$val = $wp_postmeta[ $post_id ][ $key ] ?? '';
	if ( $single ) {
		return is_array( $val ) ? reset( $val ) : $val;
	}
	return is_array( $val ) ? $val : [ $val ];
}
function update_post_meta( $post_id, $key, $value ) {
	global $wp_postmeta;
	$wp_postmeta[ $post_id ][ $key ] = $value;
	return true;
}
function delete_post_meta( $post_id, $key ) {
	global $wp_postmeta;
	unset( $wp_postmeta[ $post_id ][ $key ] );
	return true;
}

function get_term_meta( $term_id, $key = '', $single = false ) {
	global $wp_termmeta;
	if ( empty( $key ) ) {
		return $wp_termmeta[ $term_id ] ?? [];
	}
	$val = $wp_termmeta[ $term_id ][ $key ] ?? '';
	if ( $single ) {
		return is_array( $val ) ? reset( $val ) : $val;
	}
	return is_array( $val ) ? $val : [ $val ];
}
function update_term_meta( $term_id, $key, $value ) {
	global $wp_termmeta;
	$wp_termmeta[ $term_id ][ $key ] = $value;
	return true;
}
function delete_term_meta( $term_id, $key ) {
	global $wp_termmeta;
	unset( $wp_termmeta[ $term_id ][ $key ] );
	return true;
}

function wp_insert_post( $args ) {
	global $wp_posts;
	static $p_id = 100;
	$p_id++;
	$post = (object) array_merge( [
		'ID'             => $p_id,
		'post_title'     => '',
		'post_content'   => '',
		'post_excerpt'   => '',
		'post_name'      => sanitize_title( $args['post_title'] ?? '' ),
		'post_status'    => 'publish',
		'post_type'      => 'post',
		'post_author'    => 1,
		'comment_status' => 'open',
		'ping_status'    => 'open',
		'post_password'  => '',
		'menu_order'     => 0,
	], $args );
	$wp_posts[ $p_id ] = $post;
	return $p_id;
}
function wp_update_post( $args ) {
	global $wp_posts;
	$id = $args['ID'];
	if ( isset( $wp_posts[ $id ] ) ) {
		foreach ( $args as $k => $v ) {
			$wp_posts[ $id ]->$k = $v;
		}
	}
	return $id;
}
function get_post( $id ) {
	global $wp_posts;
	return $wp_posts[ $id ] ?? null;
}
function get_post_type( $id ) {
	$p = get_post( $id );
	return $p ? $p->post_type : 'post';
}
function get_post_thumbnail_id( $id ) { return get_post_meta( $id, '_thumbnail_id', true ); }
function set_post_thumbnail( $id, $thumb_id ) { update_post_meta( $id, '_thumbnail_id', $thumb_id ); }
function delete_post_thumbnail( $id ) { delete_post_meta( $id, '_thumbnail_id' ); }
function get_permalink( $id ) {
	$p = get_post( $id );
	$slug = $p ? $p->post_name : 'post-' . $id;
	return "https://example.com/{$slug}/";
}
function home_url( $path = '' ) { return 'https://example.com' . ( $path ? '/' . ltrim( $path, '/' ) : '' ); }
function get_edit_post_link( $id, $context = 'display' ) { return "https://example.com/wp-admin/post.php?post={$id}&action=edit"; }
function get_edit_term_link( $id, $taxonomy ) { return "https://example.com/wp-admin/term.php?taxonomy={$taxonomy}&tag_ID={$id}"; }
function get_term( $id, $tax = '' ) {
	global $wp_terms;
	return $wp_terms[ $id ] ?? null;
}
function get_term_link( $term, $tax = '' ) {
	$id = is_object( $term ) ? $term->term_id : $term;
	return "https://example.com/category/term-{$id}/";
}
function get_object_taxonomies( $post_type, $output = 'names' ) { return [ 'category', 'post_tag' ]; }
function wp_get_object_terms( $id, $tax, $args = [] ) { return []; }
function wp_set_object_terms( $id, $terms, $tax ) { return true; }
function is_singular() { return true; }
function get_the_ID() { return 101; }
function is_tax() { return false; }
function is_category() { return false; }
function is_tag() { return false; }
function is_front_page() { return false; }
function is_home() { return false; }
function is_admin() { return false; }
function is_ssl() { return false; }
function wp_doing_cron() { return false; }
function wp_doing_ajax() { return false; }
function get_current_user_id() { return 1; }
function current_user_can( $cap ) { return true; }
function is_wp_error( $thing ) { return $thing instanceof WP_Error; }
function esc_html( $s ) { return htmlspecialchars( (string) $s, ENT_QUOTES, 'UTF-8' ); }
function esc_attr( $s ) { return htmlspecialchars( (string) $s, ENT_QUOTES, 'UTF-8' ); }
function esc_url( $s ) { return filter_var( $s, FILTER_SANITIZE_URL ); }
function esc_html__( $t, $d = 'default' ) { return $t; }
function esc_attr__( $t, $d = 'default' ) { return $t; }
function __( $t, $d = 'default' ) { return $t; }
function _e( $t, $d = 'default' ) { echo $t; }
function wp_strip_all_tags( $s ) { return strip_tags( $s ); }
function selected( $selected, $current = true, $echo = true ) {
	$res = ( (string) $selected === (string) $current ) ? ' selected="selected"' : '';
	if ( $echo ) echo $res;
	return $res;
}
function checked( $checked, $current = true, $echo = true ) {
	$res = ( (string) $checked === (string) $current ) ? ' checked="checked"' : '';
	if ( $echo ) echo $res;
	return $res;
}
function wp_parse_url( $url, $component = -1 ) { return parse_url( $url, $component ); }
function add_rewrite_tag( $tag, $regex ) { return true; }
function add_rewrite_rule( $regex, $query, $after = 'bottom' ) { return true; }
function flush_rewrite_rules( $hard = true ) { return true; }

class WP_Error {
	public $code;
	public $message;
	public $data;
	public function __construct( $code = '', $message = '', $data = '' ) {
		$this->code = $code;
		$this->message = $message;
		$this->data = $data;
	}
	public function get_error_message() { return $this->message; }
	public function get_error_code() { return $this->code; }
}

// Load plugin files
require_once __DIR__ . '/../includes/class-cache.php';
require_once __DIR__ . '/../includes/class-installer.php';
require_once __DIR__ . '/../includes/class-language-manager.php';
require_once __DIR__ . '/../includes/class-translation-manager.php';
require_once __DIR__ . '/../includes/class-sync.php';
require_once __DIR__ . '/../includes/class-post-integration.php';
require_once __DIR__ . '/../includes/class-taxonomy-integration.php';
require_once __DIR__ . '/../includes/class-language-detector.php';
require_once __DIR__ . '/../includes/class-rewrite.php';
require_once __DIR__ . '/../includes/class-query-filter.php';
require_once __DIR__ . '/../includes/class-language-switcher.php';
require_once __DIR__ . '/../includes/class-seo-integration.php';
require_once __DIR__ . '/../includes/class-rest-api.php';
require_once __DIR__ . '/../includes/class-gutenberg.php';
require_once __DIR__ . '/../includes/helpers.php';
