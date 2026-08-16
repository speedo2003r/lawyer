<?php
/**
 * Post Editor Translation Meta Box.
 *
 * @package WPMultilingual
 */

namespace WPMultilingual;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class AdminMetaBox
 */
class AdminMetaBox {

	/**
	 * Singleton instance.
	 *
	 * @var AdminMetaBox|null
	 */
	private static $instance = null;

	/**
	 * Get singleton instance.
	 *
	 * @return AdminMetaBox
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
	 * Initialize meta box hooks.
	 */
	public function init() {
		add_action( 'add_meta_boxes', [ $this, 'register_meta_box' ] );
	}

	/**
	 * Register meta box on translatable post types.
	 */
	public function register_meta_box() {
		$post_types = PostIntegration::get_instance()->get_translatable_post_types();

		foreach ( $post_types as $pt ) {
			add_meta_box(
				'wpm_translations_meta_box',
				__( 'Language & Translations', 'wp-multilingual' ),
				[ $this, 'render_meta_box' ],
				$pt,
				'side',
				'high'
			);
		}
	}

	/**
	 * Render translation meta box.
	 *
	 * @param \WP_Post $post
	 */
	public function render_meta_box( $post ) {
		$lang_mgr     = LanguageManager::get_instance();
		$trans_mgr    = TranslationManager::get_instance();
		$languages    = $lang_mgr->get_enabled_languages();
		$current_lang = $trans_mgr->get_object_language( $post->ID, 'post' );
		$status       = get_post_meta( $post->ID, TranslationManager::META_STATUS, true ) ?: 'translated';
		$translations = $trans_mgr->get_translations( $post->ID, 'post' );

		if ( empty( $current_lang ) ) {
			$default      = $lang_mgr->get_default_language();
			$current_lang = $default ? $default->code : 'en';
		}

		wp_nonce_field( 'wpm_save_translation_meta', 'wpm_meta_box_nonce' );
		?>
		<div class="wpm-meta-box-wrap" data-post-id="<?php echo esc_attr( $post->ID ); ?>">

			<!-- Current Language Selector -->
			<div class="wpm-mb-row">
				<label for="wpm_post_language"><strong><?php esc_html_e( 'Post Language:', 'wp-multilingual' ); ?></strong></label>
				<select name="wpm_post_language" id="wpm_post_language" class="widefat" style="margin-top: 4px;">
					<?php foreach ( $languages as $lang ) : ?>
						<option value="<?php echo esc_attr( $lang->code ); ?>" <?php selected( $current_lang, $lang->code ); ?>>
							<?php echo esc_html( $lang->flag . ' ' . $lang->name . ' (' . $lang->native_name . ')' ); ?>
						</option>
					<?php endforeach; ?>
				</select>
			</div>

			<!-- Translation Status -->
			<div class="wpm-mb-row" style="margin-top: 10px;">
				<label for="wpm_translation_status"><strong><?php esc_html_e( 'Translation Status:', 'wp-multilingual' ); ?></strong></label>
				<select name="wpm_translation_status" id="wpm_translation_status" class="widefat" style="margin-top: 4px;">
					<option value="translated" <?php selected( $status, 'translated' ); ?>><?php esc_html_e( 'Translated / Up to date', 'wp-multilingual' ); ?></option>
					<option value="draft" <?php selected( $status, 'draft' ); ?>><?php esc_html_e( 'Draft Translation', 'wp-multilingual' ); ?></option>
					<option value="needs_update" <?php selected( $status, 'needs_update' ); ?>><?php esc_html_e( 'Needs Update', 'wp-multilingual' ); ?></option>
				</select>
			</div>

			<hr style="margin: 15px 0;">

			<!-- Translations List -->
			<div class="wpm-mb-translations">
				<h4 style="margin: 0 0 10px 0;"><?php esc_html_e( 'Translations', 'wp-multilingual' ); ?></h4>
				<ul class="wpm-trans-list" style="margin: 0; padding: 0; list-style: none;">
					<?php foreach ( $languages as $lang ) : ?>
						<?php if ( $lang->code === $current_lang ) continue; ?>
						<?php
						$target_id     = $translations[ $lang->code ] ?? null;
						$target_post   = $target_id ? get_post( $target_id ) : null;
						$target_status = $target_id ? ( get_post_meta( $target_id, TranslationManager::META_STATUS, true ) ?: 'translated' ) : 'not_translated';
						?>
						<li class="wpm-trans-item" style="padding: 8px 0; border-bottom: 1px solid #f0f0f0;">
							<div style="display: flex; align-items: center; justify-content: space-between;">
								<div>
									<span style="font-size: 1.1em;"><?php echo esc_html( $lang->flag ); ?></span>
									<strong><?php echo esc_html( $lang->name ); ?></strong>
								</div>

								<?php if ( $target_post ) : ?>
									<span class="wpm-badge wpm-badge-<?php echo esc_attr( $target_status ); ?>">
										<?php
										if ( 'needs_update' === $target_status ) {
											esc_html_e( 'Needs Update', 'wp-multilingual' );
										} elseif ( 'draft' === $target_status ) {
											esc_html_e( 'Draft', 'wp-multilingual' );
										} else {
											esc_html_e( 'Translated', 'wp-multilingual' );
										}
										?>
									</span>
								<?php else : ?>
									<span class="wpm-badge wpm-badge-empty"><?php esc_html_e( 'Not translated', 'wp-multilingual' ); ?></span>
								<?php endif; ?>
							</div>

							<div style="margin-top: 6px; font-size: 12px;">
								<?php if ( $target_post ) : ?>
									<a href="<?php echo esc_url( get_edit_post_link( $target_post->ID ) ); ?>" class="button button-small">
										<span class="dashicons dashicons-edit" style="font-size:14px; line-height:22px; width:14px; height:14px;"></span>
										<?php esc_html_e( 'Edit Translation', 'wp-multilingual' ); ?>
									</a>
									<button type="button" class="button button-small wpm-btn-unlink" data-post-id="<?php echo esc_attr( $target_post->ID ); ?>" title="<?php esc_attr_e( 'Unlink translation', 'wp-multilingual' ); ?>">
										<?php esc_html_e( 'Unlink', 'wp-multilingual' ); ?>
									</button>
								<?php else : ?>
									<button type="button" class="button button-small button-primary wpm-btn-create-trans" data-source-id="<?php echo esc_attr( $post->ID ); ?>" data-target-lang="<?php echo esc_attr( $lang->code ); ?>">
										+ <?php esc_html_e( 'Create Translation', 'wp-multilingual' ); ?>
									</button>
									<label style="display: block; margin-top: 4px;">
										<input type="checkbox" class="wpm-chk-duplicate" data-lang="<?php echo esc_attr( $lang->code ); ?>" checked>
										<?php esc_html_e( 'Duplicate original content', 'wp-multilingual' ); ?>
									</label>
								<?php endif; ?>
							</div>
						</li>
					<?php endforeach; ?>
				</ul>
			</div>

			<!-- Unlink Current Post Action -->
			<?php if ( count( $translations ) > 1 ) : ?>
				<div style="margin-top: 15px; text-align: right;">
					<button type="button" class="button button-link-delete button-small wpm-btn-unlink" data-post-id="<?php echo esc_attr( $post->ID ); ?>">
						<?php esc_html_e( 'Unlink this post from translation group', 'wp-multilingual' ); ?>
					</button>
				</div>
			<?php endif; ?>
		</div>
		<?php
	}
}
