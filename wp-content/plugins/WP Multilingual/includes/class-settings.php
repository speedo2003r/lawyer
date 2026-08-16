<?php
/**
 * Plugin Settings Page & Configuration Options.
 *
 * @package WPMultilingual
 */

namespace WPMultilingual;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Settings
 */
class Settings {

	/**
	 * Singleton instance.
	 *
	 * @var Settings|null
	 */
	private static $instance = null;

	/**
	 * Get singleton instance.
	 *
	 * @return Settings
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
	 * Initialize settings hooks.
	 */
	public function init() {
		add_action( 'admin_menu', [ $this, 'register_settings_menu' ] );
		add_action( 'admin_init', [ $this, 'register_settings' ] );
		add_action( 'wp_ajax_wpm_assign_default_language_bulk', [ $this, 'ajax_assign_default_language_bulk' ] );
	}

	/**
	 * Register settings submenu page under Settings.
	 */
	public function register_settings_menu() {
		add_options_page(
			__( 'Multilingual Settings', 'wp-multilingual' ),
			__( 'Multilingual Settings', 'wp-multilingual' ),
			'manage_options',
			'wpm-settings',
			[ $this, 'render_settings_page' ]
		);
	}

	/**
	 * Register settings fields with Settings API.
	 */
	public function register_settings() {
		register_setting( 'wpm_settings_group', 'wpm_settings', [
			'sanitize_callback' => [ $this, 'sanitize_settings' ],
		] );
	}

	/**
	 * Sanitize submitted settings.
	 *
	 * @param array $input
	 * @return array
	 */
	public function sanitize_settings( $input ) {
		$sanitized = [];

		$sanitized['url_mode']                  = in_array( $input['url_mode'] ?? '', [ 'mode_a', 'mode_b' ], true ) ? $input['url_mode'] : 'mode_a';
		$sanitized['hide_default_language_url'] = ! empty( $input['hide_default_language_url'] ) ? 1 : 0;
		$sanitized['detect_browser_language']   = ! empty( $input['detect_browser_language'] ) ? 1 : 0;
		$sanitized['cookie_enabled']            = ! empty( $input['cookie_enabled'] ) ? 1 : 0;
		$sanitized['cookie_name']               = sanitize_key( $input['cookie_name'] ?? 'wpm_language' );
		$sanitized['sync_featured_image']       = ! empty( $input['sync_featured_image'] ) ? 1 : 0;
		$sanitized['sync_taxonomies']           = ! empty( $input['sync_taxonomies'] ) ? 1 : 0;
		$sanitized['sync_post_meta']            = ! empty( $input['sync_post_meta'] ) ? 1 : 0;

		$sanitized['translatable_post_types'] = [];
		if ( ! empty( $input['translatable_post_types'] ) && is_array( $input['translatable_post_types'] ) ) {
			foreach ( $input['translatable_post_types'] as $pt ) {
				$sanitized['translatable_post_types'][] = sanitize_key( $pt );
			}
		}

		$sanitized['translatable_taxonomies'] = [];
		if ( ! empty( $input['translatable_taxonomies'] ) && is_array( $input['translatable_taxonomies'] ) ) {
			foreach ( $input['translatable_taxonomies'] as $tax ) {
				$sanitized['translatable_taxonomies'][] = sanitize_key( $tax );
			}
		}

		// Trigger rewrite rules flush
		set_transient( 'wpm_flush_rewrite_rules', 1, 60 );

		return $sanitized;
	}

	/**
	 * AJAX: Assign default language to untranslated existing content safely.
	 */
	public function ajax_assign_default_language_bulk() {
		check_ajax_referer( 'wpm_admin_nonce', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( [ 'message' => __( 'Permission denied.', 'wp-multilingual' ) ] );
		}

		$default_lang = wpm_get_default_language();
		if ( ! $default_lang ) {
			wp_send_json_error( [ 'message' => __( 'No default language configured yet.', 'wp-multilingual' ) ] );
		}

		$post_types = PostIntegration::get_instance()->get_translatable_post_types();
		$trans_mgr  = TranslationManager::get_instance();

		// Fetch posts without language
		$posts = get_posts( [
			'post_type'      => $post_types,
			'posts_per_page' => -1,
			'post_status'    => 'any',
			'meta_query'     => [
				[
					'key'     => TranslationManager::META_LANG,
					'compare' => 'NOT EXISTS',
				],
			],
			'fields'         => 'ids',
		] );

		$assigned_count = 0;
		foreach ( $posts as $post_id ) {
			$group_id = $trans_mgr->create_group( 'post' );
			$trans_mgr->assign_language_and_group( $post_id, $default_lang->code, $group_id, 'post', 'translated' );
			$assigned_count++;
		}

		wp_send_json_success( [
			'message' => sprintf(
				/* translators: %d: number of posts */
				__( 'Successfully assigned default language (%s) to %d existing posts/pages.', 'wp-multilingual' ),
				$default_lang->name,
				$assigned_count
			),
		] );
	}

	/**
	 * Render settings page.
	 */
	public function render_settings_page() {
		$settings = wpm_get_settings();

		// Get all public post types
		$all_post_types = get_post_types( [ 'public' => true ], 'objects' );
		unset( $all_post_types['attachment'] );

		// Get all public taxonomies
		$all_taxonomies = get_taxonomies( [ 'public' => true ], 'objects' );
		unset( $all_taxonomies['post_format'] );
		?>
		<div class="wrap wpm-admin-wrap">
			<h1><?php esc_html_e( 'Multilingual Configuration Settings', 'wp-multilingual' ); ?></h1>
			<hr class="wp-header-end">

			<form method="post" action="options.php">
				<?php settings_fields( 'wpm_settings_group' ); ?>

				<div class="wpm-card">
					<h2><?php esc_html_e( 'URL & Permalink Configuration', 'wp-multilingual' ); ?></h2>
					<table class="form-table">
						<tr>
							<th scope="row"><?php esc_html_e( 'URL Structure Mode', 'wp-multilingual' ); ?></th>
							<td>
								<fieldset>
									<label style="display:block; margin-bottom:8px;">
										<input type="radio" name="wpm_settings[url_mode]" value="mode_a" <?php checked( $settings['url_mode'] ?? 'mode_a', 'mode_a' ); ?>>
										<strong><?php esc_html_e( 'Mode A: Language code in all URLs', 'wp-multilingual' ); ?></strong>
										<br><span class="description" style="margin-left:22px;">(e.g., <code>https://example.com/en/about-us/</code> and <code>https://example.com/ar/about-us/</code>)</span>
									</label>
									<label style="display:block; margin-bottom:8px;">
										<input type="radio" name="wpm_settings[url_mode]" value="mode_b" <?php checked( $settings['url_mode'] ?? 'mode_a', 'mode_b' ); ?>>
										<strong><?php esc_html_e( 'Mode B: Hide language code for default language', 'wp-multilingual' ); ?></strong>
										<br><span class="description" style="margin-left:22px;">(e.g., <code>https://example.com/about-us/</code> for English, <code>https://example.com/ar/about-us/</code> for Arabic)</span>
									</label>
								</fieldset>
							</td>
						</tr>
					</table>
				</div>

				<div class="wpm-card">
					<h2><?php esc_html_e( 'Language Detection & Cookies', 'wp-multilingual' ); ?></h2>
					<table class="form-table">
						<tr>
							<th scope="row"><?php esc_html_e( 'Browser Detection', 'wp-multilingual' ); ?></th>
							<td>
								<label>
									<input type="checkbox" name="wpm_settings[detect_browser_language]" value="1" <?php checked( ! empty( $settings['detect_browser_language'] ) ); ?>>
									<?php esc_html_e( 'Detect user language from browser Accept-Language header on first visit', 'wp-multilingual' ); ?>
								</label>
							</td>
						</tr>
						<tr>
							<th scope="row"><?php esc_html_e( 'Language Cookie', 'wp-multilingual' ); ?></th>
							<td>
								<label style="display:block; margin-bottom:8px;">
									<input type="checkbox" name="wpm_settings[cookie_enabled]" value="1" <?php checked( ! empty( $settings['cookie_enabled'] ) ); ?>>
									<?php esc_html_e( 'Remember user language preference via cookie', 'wp-multilingual' ); ?>
								</label>
								<input type="text" name="wpm_settings[cookie_name]" value="<?php echo esc_attr( $settings['cookie_name'] ?? 'wpm_language' ); ?>" class="regular-text" placeholder="Cookie name">
							</td>
						</tr>
					</table>
				</div>

				<div class="wpm-card">
					<h2><?php esc_html_e( 'Translatable Content Types', 'wp-multilingual' ); ?></h2>
					<table class="form-table">
						<tr>
							<th scope="row"><?php esc_html_e( 'Post Types', 'wp-multilingual' ); ?></th>
							<td>
								<fieldset>
									<?php foreach ( $all_post_types as $pt ) : ?>
										<label style="display:block; margin-bottom:6px;">
											<input type="checkbox" name="wpm_settings[translatable_post_types][]" value="<?php echo esc_attr( $pt->name ); ?>"
												<?php checked( in_array( $pt->name, $settings['translatable_post_types'] ?? [ 'post', 'page' ], true ) ); ?>>
											<?php echo esc_html( $pt->labels->name . ' (' . $pt->name . ')' ); ?>
										</label>
									<?php endforeach; ?>
								</fieldset>
							</td>
						</tr>
						<tr>
							<th scope="row"><?php esc_html_e( 'Taxonomies', 'wp-multilingual' ); ?></th>
							<td>
								<fieldset>
									<?php foreach ( $all_taxonomies as $tax ) : ?>
										<label style="display:block; margin-bottom:6px;">
											<input type="checkbox" name="wpm_settings[translatable_taxonomies][]" value="<?php echo esc_attr( $tax->name ); ?>"
												<?php checked( in_array( $tax->name, $settings['translatable_taxonomies'] ?? [ 'category', 'post_tag' ], true ) ); ?>>
											<?php echo esc_html( $tax->labels->name . ' (' . $tax->name . ')' ); ?>
										</label>
									<?php endforeach; ?>
								</fieldset>
							</td>
						</tr>
					</table>
				</div>

				<div class="wpm-card">
					<h2><?php esc_html_e( 'Translation Synchronization', 'wp-multilingual' ); ?></h2>
					<table class="form-table">
						<tr>
							<th scope="row"><?php esc_html_e( 'Sync Options', 'wp-multilingual' ); ?></th>
							<td>
								<label style="display:block; margin-bottom:6px;">
									<input type="checkbox" name="wpm_settings[sync_featured_image]" value="1" <?php checked( ! empty( $settings['sync_featured_image'] ) ); ?>>
									<?php esc_html_e( 'Synchronize featured image across translations', 'wp-multilingual' ); ?>
								</label>
								<label style="display:block; margin-bottom:6px;">
									<input type="checkbox" name="wpm_settings[sync_taxonomies]" value="1" <?php checked( ! empty( $settings['sync_taxonomies'] ) ); ?>>
									<?php esc_html_e( 'Synchronize taxonomies (translate categories/tags where available)', 'wp-multilingual' ); ?>
								</label>
							</td>
						</tr>
					</table>
				</div>

				<?php submit_button( __( 'Save Multilingual Settings', 'wp-multilingual' ) ); ?>
			</form>

			<div class="wpm-card" style="margin-top:20px; border-left:4px solid #0073aa;">
				<h2><?php esc_html_e( 'Existing Content Migration Tool', 'wp-multilingual' ); ?></h2>
				<p class="description">
					<?php esc_html_e( 'If you installed WP Multilingual on a site with existing posts/pages, you can assign your default language to all unassigned posts at once.', 'wp-multilingual' ); ?>
				</p>
				<button type="button" id="wpm_assign_bulk_btn" class="button button-secondary">
					<?php esc_html_e( 'Assign Default Language to Untranslated Content', 'wp-multilingual' ); ?>
				</button>
				<span id="wpm_bulk_status" style="margin-left: 10px; font-weight: bold;"></span>
			</div>
		</div>
		<?php
	}
}
