<?php
/**
 * Admin Posts List Table Columns & Language Filtering.
 *
 * @package WPMultilingual
 */

namespace WPMultilingual;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class AdminColumns
 */
class AdminColumns {

	/**
	 * Singleton instance.
	 *
	 * @var AdminColumns|null
	 */
	private static $instance = null;

	/**
	 * Get singleton instance.
	 *
	 * @return AdminColumns
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
	 * Initialize columns and filter hooks.
	 */
	public function init() {
		$post_types = PostIntegration::get_instance()->get_translatable_post_types();

		foreach ( $post_types as $pt ) {
			if ( 'page' === $pt ) {
				add_filter( 'manage_pages_columns', [ $this, 'register_columns' ] );
				add_action( 'manage_pages_custom_column', [ $this, 'render_columns' ], 10, 2 );
			} else {
				add_filter( "manage_{$pt}_posts_columns", [ $this, 'register_columns' ] );
				add_action( "manage_{$pt}_posts_custom_column", [ $this, 'render_columns' ], 10, 2 );
			}
		}

		// Filter dropdown on list table
		add_action( 'restrict_manage_posts', [ $this, 'render_language_filter_dropdown' ] );
		add_filter( 'parse_query', [ $this, 'apply_admin_language_filter' ] );
	}

	/**
	 * Add Language & Translations columns to post list.
	 *
	 * @param array $columns
	 * @return array
	 */
	public function register_columns( $columns ) {
		$new_columns = [];

		foreach ( $columns as $key => $title ) {
			$new_columns[ $key ] = $title;
			if ( 'title' === $key ) {
				$new_columns['wpm_language']     = __( 'Language', 'wp-multilingual' );
				$new_columns['wpm_translations'] = __( 'Translations', 'wp-multilingual' );
			}
		}

		return $new_columns;
	}

	/**
	 * Render custom column content.
	 *
	 * @param string $column_name
	 * @param int    $post_id
	 */
	public function render_columns( $column_name, $post_id ) {
		$trans_mgr = TranslationManager::get_instance();
		$lang_mgr  = LanguageManager::get_instance();

		if ( 'wpm_language' === $column_name ) {
			$lang_code = $trans_mgr->get_object_language( $post_id, 'post' );
			if ( $lang_code ) {
				$lang = $lang_mgr->get_language( $lang_code );
				if ( $lang ) {
					echo '<span class="wpm-column-lang-badge" title="' . esc_attr( $lang->name ) . '">';
					echo esc_html( $lang->flag . ' ' . strtoupper( $lang->code ) );
					echo '</span>';
				} else {
					echo '<code>' . esc_html( $lang_code ) . '</code>';
				}
			} else {
				echo '<span style="color:#999;">—</span>';
			}
		}

		if ( 'wpm_translations' === $column_name ) {
			$languages    = $lang_mgr->get_enabled_languages();
			$post_lang    = $trans_mgr->get_object_language( $post_id, 'post' );
			$translations = $trans_mgr->get_translations( $post_id, 'post' );

			echo '<div class="wpm-column-translations">';
			foreach ( $languages as $lang ) {
				if ( $lang->code === $post_lang ) {
					continue;
				}

				$trans_id = $translations[ $lang->code ] ?? null;
				if ( $trans_id ) {
					$edit_url = get_edit_post_link( $trans_id );
					echo '<a href="' . esc_url( $edit_url ) . '" class="wpm-trans-icon wpm-trans-exists" title="' . esc_attr( sprintf( __( 'Edit %s translation', 'wp-multilingual' ), $lang->name ) ) . '">';
					echo esc_html( $lang->flag );
					echo '</a> ';
				} else {
					echo '<button type="button" class="wpm-trans-icon wpm-trans-add wpm-btn-create-trans" data-source-id="' . esc_attr( $post_id ) . '" data-target-lang="' . esc_attr( $lang->code ) . '" title="' . esc_attr( sprintf( __( 'Create %s translation', 'wp-multilingual' ), $lang->name ) ) . '">';
					echo '<span style="opacity:0.4;">' . esc_html( $lang->flag ) . '</span>+';
					echo '</button> ';
				}
			}
			echo '</div>';
		}
	}

	/**
	 * Render language filter dropdown above post list.
	 *
	 * @param string $post_type
	 */
	public function render_language_filter_dropdown( $post_type ) {
		if ( ! PostIntegration::get_instance()->is_translatable_post_type( $post_type ) ) {
			return;
		}

		$languages     = wpm_get_languages( [ 'enabled_only' => true ] );
		$selected_lang = isset( $_GET['wpm_lang_filter'] ) ? sanitize_text_field( wp_unslash( $_GET['wpm_lang_filter'] ) ) : '';
		?>
		<select name="wpm_lang_filter" id="wpm_lang_filter">
			<option value=""><?php esc_html_e( 'All Languages', 'wp-multilingual' ); ?></option>
			<?php foreach ( $languages as $lang ) : ?>
				<option value="<?php echo esc_attr( $lang->code ); ?>" <?php selected( $selected_lang, $lang->code ); ?>>
					<?php echo esc_html( $lang->flag . ' ' . $lang->name ); ?>
				</option>
			<?php endforeach; ?>
			<option value="unassigned" <?php selected( $selected_lang, 'unassigned' ); ?>><?php esc_html_e( 'Unassigned Language', 'wp-multilingual' ); ?></option>
		</select>
		<?php
	}

	/**
	 * Apply language filter in admin post list query.
	 *
	 * @param \WP_Query $query
	 */
	public function apply_admin_language_filter( $query ) {
		if ( ! is_admin() || ! $query->is_main_query() ) {
			return;
		}

		$filter = isset( $_GET['wpm_lang_filter'] ) ? sanitize_text_field( wp_unslash( $_GET['wpm_lang_filter'] ) ) : '';
		if ( empty( $filter ) ) {
			return;
		}

		$meta_query = $query->get( 'meta_query' );
		if ( ! is_array( $meta_query ) ) {
			$meta_query = [];
		}

		if ( 'unassigned' === $filter ) {
			$meta_query[] = [
				'key'     => TranslationManager::META_LANG,
				'compare' => 'NOT EXISTS',
			];
		} else {
			$meta_query[] = [
				'key'     => TranslationManager::META_LANG,
				'value'   => $filter,
				'compare' => '=',
			];
		}

		$query->set( 'meta_query', $meta_query );
	}
}
