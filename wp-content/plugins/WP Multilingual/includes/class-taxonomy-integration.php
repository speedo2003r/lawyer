<?php
/**
 * Taxonomy Translation Integration.
 *
 * @package WPMultilingual
 */

namespace WPMultilingual;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class TaxonomyIntegration
 */
class TaxonomyIntegration {

	/**
	 * Singleton instance.
	 *
	 * @var TaxonomyIntegration|null
	 */
	private static $instance = null;

	/**
	 * Get singleton instance.
	 *
	 * @return TaxonomyIntegration
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
	 * Initialize hooks.
	 */
	public function init() {
		$taxonomies = $this->get_translatable_taxonomies();

		foreach ( $taxonomies as $taxonomy ) {
			// Taxonomy term add/edit fields
			add_action( "{$taxonomy}_add_form_fields", [ $this, 'render_add_term_fields' ], 10, 1 );
			add_action( "{$taxonomy}_edit_form_fields", [ $this, 'render_edit_term_fields' ], 10, 2 );

			// Save term hooks
			add_action( "created_{$taxonomy}", [ $this, 'on_save_term' ], 10, 2 );
			add_action( "edited_{$taxonomy}", [ $this, 'on_save_term' ], 10, 2 );
		}

		// Filter terms on frontend if needed
		add_filter( 'get_terms_args', [ $this, 'filter_get_terms_args' ], 10, 2 );
	}

	/**
	 * Get all translatable taxonomies.
	 *
	 * @return array
	 */
	public function get_translatable_taxonomies() {
		$settings   = wpm_get_settings();
		$taxonomies = $settings['translatable_taxonomies'] ?? [ 'category', 'post_tag' ];

		if ( ! is_array( $taxonomies ) ) {
			$taxonomies = [ 'category', 'post_tag' ];
		}

		/**
		 * Filter translatable taxonomies.
		 *
		 * @param array $taxonomies
		 */
		return apply_filters( 'wpm_translatable_taxonomies', $taxonomies );
	}

	/**
	 * Check if a taxonomy is translatable.
	 *
	 * @param string $taxonomy
	 * @return bool
	 */
	public function is_translatable_taxonomy( $taxonomy ) {
		$taxonomies = $this->get_translatable_taxonomies();
		return in_array( $taxonomy, $taxonomies, true );
	}

	/**
	 * Render language field on Add Term form.
	 *
	 * @param string $taxonomy
	 */
	public function render_add_term_fields( $taxonomy ) {
		$languages = wpm_get_languages( [ 'enabled_only' => true ] );
		if ( empty( $languages ) ) {
			return;
		}

		$current_lang = wpm_get_current_language();
		wp_nonce_field( 'wpm_save_term_language', 'wpm_term_nonce' );
		?>
		<div class="form-field term-language-wrap">
			<label for="wpm_term_language"><?php esc_html_e( 'Language', 'wp-multilingual' ); ?></label>
			<select name="wpm_term_language" id="wpm_term_language">
				<?php foreach ( $languages as $lang ) : ?>
					<option value="<?php echo esc_attr( $lang->code ); ?>" <?php selected( $current_lang, $lang->code ); ?>>
						<?php echo esc_html( $lang->flag . ' ' . $lang->name . ' (' . $lang->native_name . ')' ); ?>
					</option>
				<?php endforeach; ?>
			</select>
			<p class="description"><?php esc_html_e( 'Select the language for this term.', 'wp-multilingual' ); ?></p>
		</div>
		<?php
	}

	/**
	 * Render language and translation fields on Edit Term form.
	 *
	 * @param \WP_Term $term
	 * @param string   $taxonomy
	 */
	public function render_edit_term_fields( $term, $taxonomy ) {
		$languages = wpm_get_languages( [ 'enabled_only' => true ] );
		if ( empty( $languages ) ) {
			return;
		}

		$trans_mgr    = TranslationManager::get_instance();
		$term_lang    = $trans_mgr->get_object_language( $term->term_id, 'term' );
		$translations = $trans_mgr->get_translations( $term->term_id, 'term' );

		if ( ! $term_lang ) {
			$default   = wpm_get_default_language();
			$term_lang = $default ? $default->code : 'en';
		}

		wp_nonce_field( 'wpm_save_term_language', 'wpm_term_nonce' );
		?>
		<tr class="form-field term-language-wrap">
			<th scope="row">
				<label for="wpm_term_language"><?php esc_html_e( 'Language', 'wp-multilingual' ); ?></label>
			</th>
			<td>
				<select name="wpm_term_language" id="wpm_term_language">
					<?php foreach ( $languages as $lang ) : ?>
						<option value="<?php echo esc_attr( $lang->code ); ?>" <?php selected( $term_lang, $lang->code ); ?>>
							<?php echo esc_html( $lang->flag . ' ' . $lang->name . ' (' . $lang->native_name . ')' ); ?>
						</option>
					<?php endforeach; ?>
				</select>
				<p class="description"><?php esc_html_e( 'Language assigned to this term.', 'wp-multilingual' ); ?></p>
			</td>
		</tr>
		<tr class="form-field term-translations-wrap">
			<th scope="row"><?php esc_html_e( 'Translations', 'wp-multilingual' ); ?></th>
			<td>
				<fieldset>
					<legend class="screen-reader-text"><?php esc_html_e( 'Translations', 'wp-multilingual' ); ?></legend>
					<ul style="margin: 0; padding: 0; list-style: none;">
						<?php foreach ( $languages as $lang ) : ?>
							<?php if ( $lang->code === $term_lang ) continue; ?>
							<li style="margin-bottom: 8px;">
								<strong><?php echo esc_html( $lang->flag . ' ' . $lang->name ); ?>:</strong>
								<?php
								$translated_id = $translations[ $lang->code ] ?? null;
								if ( $translated_id ) {
									$trans_term = get_term( $translated_id, $taxonomy );
									if ( $trans_term && ! is_wp_error( $trans_term ) ) {
										$edit_url = get_edit_term_link( $trans_term->term_id, $taxonomy );
										echo ' <a href="' . esc_url( $edit_url ) . '"><strong>' . esc_html( $trans_term->name ) . '</strong></a>';
									}
								} else {
									$add_url = admin_url( "edit-tags.php?taxonomy={$taxonomy}&wpm_source_term={$term->term_id}&wpm_target_lang={$lang->code}" );
									echo ' <span style="color:#777;">' . esc_html__( 'Not translated', 'wp-multilingual' ) . '</span> ';
									echo '<a href="' . esc_url( $add_url ) . '" class="button button-small">' . esc_html__( '+ Add translation', 'wp-multilingual' ) . '</a>';
								}
								?>
							</li>
						<?php endforeach; ?>
					</ul>
				</fieldset>
			</td>
		</tr>
		<?php
	}

	/**
	 * Save term language and translation group.
	 *
	 * @param int $term_id
	 * @param int $tt_id
	 */
	public function on_save_term( $term_id, $tt_id ) {
		if ( ! isset( $_POST['wpm_term_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['wpm_term_nonce'] ) ), 'wpm_save_term_language' ) ) {
			return;
		}

		$lang_code = isset( $_POST['wpm_term_language'] ) ? sanitize_text_field( wp_unslash( $_POST['wpm_term_language'] ) ) : '';
		if ( empty( $lang_code ) ) {
			return;
		}

		$trans_mgr = TranslationManager::get_instance();
		$group_id  = $trans_mgr->get_object_group_id( $term_id, 'term' );

		// Check if created as translation of another term via GET param
		if ( empty( $group_id ) && ! empty( $_POST['wpm_source_term'] ) ) {
			$source_term_id = absint( $_POST['wpm_source_term'] );
			$source_group   = $trans_mgr->get_object_group_id( $source_term_id, 'term' );
			if ( $source_group ) {
				$group_id = $source_group;
			}
		}

		if ( empty( $group_id ) ) {
			$group_id = $trans_mgr->create_group( 'term' );
		}

		$trans_mgr->assign_language_and_group( $term_id, $lang_code, $group_id, 'term' );
	}

	/**
	 * Filter get_terms query args to filter by language if requested.
	 *
	 * @param array $args
	 * @param array $taxonomies
	 * @return array
	 */
	public function filter_get_terms_args( $args, $taxonomies ) {
		if ( ! empty( $args['suppress_filters'] ) ) {
			return $args;
		}

		$lang = isset( $args['lang'] ) ? $args['lang'] : null;
		if ( 'all' === $lang || false === $lang ) {
			return $args;
		}

		if ( is_admin() && empty( $lang ) ) {
			return $args;
		}

		if ( empty( $lang ) ) {
			$lang = wpm_get_current_language();
		}

		if ( empty( $lang ) ) {
			return $args;
		}

		$trans_taxonomies = $this->get_translatable_taxonomies();
		$has_translatable = false;

		$check_taxonomies = ! empty( $taxonomies ) ? (array) $taxonomies : ( ! empty( $args['taxonomy'] ) ? (array) $args['taxonomy'] : [] );
		if ( ! empty( $check_taxonomies ) ) {
			foreach ( $check_taxonomies as $tax ) {
				if ( in_array( $tax, $trans_taxonomies, true ) ) {
					$has_translatable = true;
					break;
				}
			}
		} else {
			$has_translatable = true;
		}

		if ( ! $has_translatable ) {
			return $args;
		}

		if ( empty( $args['meta_query'] ) || ! is_array( $args['meta_query'] ) ) {
			$args['meta_query'] = [];
		}

		$args['meta_query'][] = [
			'key'     => TranslationManager::META_LANG,
			'value'   => sanitize_text_field( $lang ),
			'compare' => '=',
		];

		return $args;
	}
}
