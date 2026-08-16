<?php
/**
 * Admin Language Management UI & Actions.
 *
 * @package WPMultilingual
 */

namespace WPMultilingual;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Admin
 */
class Admin {

	/**
	 * Singleton instance.
	 *
	 * @var Admin|null
	 */
	private static $instance = null;

	/**
	 * Get singleton instance.
	 *
	 * @return Admin
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
	 * Initialize admin hooks.
	 */
	public function init() {
		add_action( 'admin_menu', [ $this, 'register_admin_menus' ] );
		add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_admin_assets' ] );
		add_action( 'admin_init', [ $this, 'handle_form_submissions' ] );
		add_action( 'admin_notices', [ $this, 'display_admin_notices' ] );
	}

	/**
	 * Register admin menu pages under Settings.
	 */
	public function register_admin_menus() {
		add_options_page(
			__( 'Languages', 'wp-multilingual' ),
			__( 'Languages', 'wp-multilingual' ),
			'manage_options',
			'wpm-languages',
			[ $this, 'render_languages_page' ]
		);
	}

	/**
	 * Enqueue admin scripts and stylesheets.
	 *
	 * @param string $hook_suffix
	 */
	public function enqueue_admin_assets( $hook_suffix ) {
		// Enqueue on Languages page, Settings page, or Post edit screens
		$is_post_edit = in_array( $hook_suffix, [ 'post.php', 'post-new.php', 'edit.php' ], true );
		$is_wpm_page  = false !== strpos( $hook_suffix, 'wpm-' );

		if ( ! $is_post_edit && ! $is_wpm_page ) {
			return;
		}

		wp_enqueue_style(
			'wpm-admin-css',
			WPM_PLUGIN_URL . 'admin/css/wpm-admin.css',
			[],
			WPM_VERSION
		);

		wp_enqueue_script(
			'wpm-admin-js',
			WPM_PLUGIN_URL . 'admin/js/wpm-admin.js',
			[ 'jquery', 'jquery-ui-sortable' ],
			WPM_VERSION,
			true
		);

		wp_localize_script( 'wpm-admin-js', 'wpmAdmin', [
			'ajaxUrl'   => admin_url( 'admin-ajax.php' ),
			'nonce'     => wp_create_nonce( 'wpm_admin_nonce' ),
			'confirm'   => __( 'Are you sure you want to delete this language?', 'wp-multilingual' ),
			'unlinking' => __( 'Are you sure you want to unlink this translation?', 'wp-multilingual' ),
		] );
	}

	/**
	 * Handle standard POST/GET actions for languages.
	 */
	public function handle_form_submissions() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		// Handle Add Language
		if ( isset( $_POST['wpm_action'] ) && 'add_language' === $_POST['wpm_action'] ) {
			check_admin_referer( 'wpm_add_language_action', 'wpm_add_language_nonce' );

			$lang_mgr = LanguageManager::get_instance();
			$res = $lang_mgr->add_language( [
				'code'        => sanitize_text_field( wp_unslash( $_POST['code'] ?? '' ) ),
				'locale'      => sanitize_text_field( wp_unslash( $_POST['locale'] ?? '' ) ),
				'name'        => sanitize_text_field( wp_unslash( $_POST['name'] ?? '' ) ),
				'native_name' => sanitize_text_field( wp_unslash( $_POST['native_name'] ?? '' ) ),
				'direction'   => sanitize_text_field( wp_unslash( $_POST['direction'] ?? 'ltr' ) ),
				'flag'        => sanitize_text_field( wp_unslash( $_POST['flag'] ?? '' ) ),
				'url_code'    => sanitize_text_field( wp_unslash( $_POST['url_code'] ?? '' ) ),
				'is_default'  => ! empty( $_POST['is_default'] ) ? 1 : 0,
				'is_enabled'  => ! empty( $_POST['is_enabled'] ) ? 1 : 0,
				'ordering'    => isset( $_POST['ordering'] ) ? absint( $_POST['ordering'] ) : 0,
			] );

			if ( is_wp_error( $res ) ) {
				add_settings_error( 'wpm_languages', 'wpm_error', $res->get_error_message(), 'error' );
			} else {
				add_settings_error( 'wpm_languages', 'wpm_success', __( 'Language added successfully.', 'wp-multilingual' ), 'success' );
			}

			wp_safe_redirect( admin_url( 'options-general.php?page=wpm-languages' ) );
			exit;
		}

		// Handle Delete Language
		if ( isset( $_GET['wpm_action'] ) && 'delete_language' === $_GET['wpm_action'] && isset( $_GET['lang_id'] ) ) {
			$lang_id = absint( $_GET['lang_id'] );
			check_admin_referer( 'wpm_delete_language_' . $lang_id );

			$lang_mgr = LanguageManager::get_instance();
			$res = $lang_mgr->delete_language( $lang_id );

			if ( is_wp_error( $res ) ) {
				add_settings_error( 'wpm_languages', 'wpm_error', $res->get_error_message(), 'error' );
			} else {
				add_settings_error( 'wpm_languages', 'wpm_success', __( 'Language deleted successfully.', 'wp-multilingual' ), 'success' );
			}

			wp_safe_redirect( admin_url( 'options-general.php?page=wpm-languages' ) );
			exit;
		}

		// Handle Set Default Language
		if ( isset( $_GET['wpm_action'] ) && 'set_default' === $_GET['wpm_action'] && isset( $_GET['lang_id'] ) ) {
			$lang_id = absint( $_GET['lang_id'] );
			check_admin_referer( 'wpm_set_default_' . $lang_id );

			$lang_mgr = LanguageManager::get_instance();
			$res = $lang_mgr->set_default_language( $lang_id );

			if ( is_wp_error( $res ) ) {
				add_settings_error( 'wpm_languages', 'wpm_error', $res->get_error_message(), 'error' );
			} else {
				add_settings_error( 'wpm_languages', 'wpm_success', __( 'Default language updated.', 'wp-multilingual' ), 'success' );
			}

			wp_safe_redirect( admin_url( 'options-general.php?page=wpm-languages' ) );
			exit;
		}
	}

	/**
	 * Display notices on language screens.
	 */
	public function display_admin_notices() {
		$screen = get_current_screen();
		if ( ! $screen || 'settings_page_wpm-languages' !== $screen->id ) {
			return;
		}

		settings_errors( 'wpm_languages' );
	}

	/**
	 * Render Languages management admin view.
	 */
	public function render_languages_page() {
		$lang_mgr   = LanguageManager::get_instance();
		$languages  = $lang_mgr->get_languages();
		$presets    = LanguageManager::get_preset_languages();
		?>
		<div class="wrap wpm-admin-wrap">
			<h1 class="wp-heading-inline"><?php esc_html_e( 'Languages Management', 'wp-multilingual' ); ?></h1>
			<hr class="wp-header-end">

			<div class="wpm-columns">
				<!-- Left Column: Add Language Form -->
				<div class="wpm-column wpm-column-form">
					<div class="wpm-card">
						<h2><?php esc_html_e( 'Add New Language', 'wp-multilingual' ); ?></h2>

						<!-- Preset selector -->
						<div class="wpm-form-group">
							<label for="wpm_preset_select"><?php esc_html_e( 'Choose from preset:', 'wp-multilingual' ); ?></label>
							<select id="wpm_preset_select" class="regular-text">
								<option value=""><?php esc_html_e( '— Select a language preset —', 'wp-multilingual' ); ?></option>
								<?php foreach ( $presets as $code => $preset ) : ?>
									<option value="<?php echo esc_attr( $code ); ?>"
										data-locale="<?php echo esc_attr( $preset['locale'] ); ?>"
										data-name="<?php echo esc_attr( $preset['name'] ); ?>"
										data-native="<?php echo esc_attr( $preset['native_name'] ); ?>"
										data-direction="<?php echo esc_attr( $preset['direction'] ); ?>"
										data-flag="<?php echo esc_attr( $preset['flag'] ); ?>"
										data-url="<?php echo esc_attr( $preset['url_code'] ); ?>">
										<?php echo esc_html( $preset['flag'] . ' ' . $preset['name'] . ' (' . $preset['native_name'] . ')' ); ?>
									</option>
								<?php endforeach; ?>
							</select>
						</div>

						<form method="post" action="">
							<?php wp_nonce_field( 'wpm_add_language_action', 'wpm_add_language_nonce' ); ?>
							<input type="hidden" name="wpm_action" value="add_language">

							<div class="wpm-form-group">
								<label for="wpm_name"><?php esc_html_e( 'Full Name *', 'wp-multilingual' ); ?></label>
								<input type="text" id="wpm_name" name="name" required class="regular-text" placeholder="e.g. English">
							</div>

							<div class="wpm-form-group">
								<label for="wpm_native_name"><?php esc_html_e( 'Native Name *', 'wp-multilingual' ); ?></label>
								<input type="text" id="wpm_native_name" name="native_name" required class="regular-text" placeholder="e.g. English">
							</div>

							<div class="wpm-form-group">
								<label for="wpm_code"><?php esc_html_e( 'Language Code (ISO 639-1) *', 'wp-multilingual' ); ?></label>
								<input type="text" id="wpm_code" name="code" required class="regular-text" placeholder="e.g. en">
							</div>

							<div class="wpm-form-group">
								<label for="wpm_locale"><?php esc_html_e( 'Locale *', 'wp-multilingual' ); ?></label>
								<input type="text" id="wpm_locale" name="locale" required class="regular-text" placeholder="e.g. en_US">
							</div>

							<div class="wpm-form-group">
								<label for="wpm_url_code"><?php esc_html_e( 'URL Code / Slug *', 'wp-multilingual' ); ?></label>
								<input type="text" id="wpm_url_code" name="url_code" required class="regular-text" placeholder="e.g. en">
							</div>

							<div class="wpm-form-group">
								<label for="wpm_flag"><?php esc_html_e( 'Flag Icon / Emoji', 'wp-multilingual' ); ?></label>
								<input type="text" id="wpm_flag" name="flag" class="regular-text" placeholder="e.g. 🇺🇸">
							</div>

							<div class="wpm-form-group">
								<label for="wpm_direction"><?php esc_html_e( 'Text Direction', 'wp-multilingual' ); ?></label>
								<select id="wpm_direction" name="direction">
									<option value="ltr"><?php esc_html_e( 'LTR (Left-to-Right)', 'wp-multilingual' ); ?></option>
									<option value="rtl"><?php esc_html_e( 'RTL (Right-to-Left)', 'wp-multilingual' ); ?></option>
								</select>
							</div>

							<div class="wpm-form-group">
								<label>
									<input type="checkbox" name="is_default" value="1" <?php checked( empty( $languages ) ); ?>>
									<?php esc_html_e( 'Set as default language', 'wp-multilingual' ); ?>
								</label>
							</div>

							<div class="wpm-form-group">
								<label>
									<input type="checkbox" name="is_enabled" value="1" checked>
									<?php esc_html_e( 'Enable this language', 'wp-multilingual' ); ?>
								</label>
							</div>

							<?php submit_button( __( 'Add Language', 'wp-multilingual' ), 'primary', 'submit_add_language' ); ?>
						</form>
					</div>
				</div>

				<!-- Right Column: Existing Languages List Table -->
				<div class="wpm-column wpm-column-list">
					<div class="wpm-card">
						<h2><?php esc_html_e( 'Configured Languages', 'wp-multilingual' ); ?></h2>
						<?php if ( empty( $languages ) ) : ?>
							<p class="description"><?php esc_html_e( 'No languages added yet. Please add your primary/default language first.', 'wp-multilingual' ); ?></p>
						<?php else : ?>
							<table class="wp-list-table widefat fixed striped">
								<thead>
									<tr>
										<th width="30"><?php esc_html_e( 'Flag', 'wp-multilingual' ); ?></th>
										<th><?php esc_html_e( 'Name', 'wp-multilingual' ); ?></th>
										<th><?php esc_html_e( 'Code', 'wp-multilingual' ); ?></th>
										<th><?php esc_html_e( 'Locale', 'wp-multilingual' ); ?></th>
										<th><?php esc_html_e( 'URL Slug', 'wp-multilingual' ); ?></th>
										<th><?php esc_html_e( 'Direction', 'wp-multilingual' ); ?></th>
										<th><?php esc_html_e( 'Default', 'wp-multilingual' ); ?></th>
										<th><?php esc_html_e( 'Status', 'wp-multilingual' ); ?></th>
										<th><?php esc_html_e( 'Actions', 'wp-multilingual' ); ?></th>
									</tr>
								</thead>
								<tbody>
									<?php foreach ( $languages as $lang ) : ?>
										<tr>
											<td><span class="wpm-flag-display"><?php echo esc_html( $lang->flag ); ?></span></td>
											<td>
												<strong><?php echo esc_html( $lang->name ); ?></strong>
												<div class="row-actions"><span class="native-name"><?php echo esc_html( $lang->native_name ); ?></span></div>
											</td>
											<td><code><?php echo esc_html( $lang->code ); ?></code></td>
											<td><code><?php echo esc_html( $lang->locale ); ?></code></td>
											<td><code>/<?php echo esc_html( $lang->url_code ); ?>/</code></td>
											<td><span class="wpm-badge wpm-badge-<?php echo esc_attr( $lang->direction ); ?>"><?php echo esc_html( strtoupper( $lang->direction ) ); ?></span></td>
											<td>
												<?php if ( (int) $lang->is_default === 1 ) : ?>
													<span class="dashicons dashicons-star-filled" style="color:#e0a800;" title="<?php esc_attr_e( 'Default Language', 'wp-multilingual' ); ?>"></span>
												<?php else : ?>
													<a href="<?php echo esc_url( wp_nonce_url( admin_url( 'options-general.php?page=wpm-languages&wpm_action=set_default&lang_id=' . $lang->id ), 'wpm_set_default_' . $lang->id ) ); ?>" class="button button-small">
														<?php esc_html_e( 'Make Default', 'wp-multilingual' ); ?>
													</a>
												<?php endif; ?>
											</td>
											<td>
												<?php if ( (int) $lang->is_enabled === 1 ) : ?>
													<span class="wpm-status-active"><?php esc_html_e( 'Active', 'wp-multilingual' ); ?></span>
												<?php else : ?>
													<span class="wpm-status-disabled"><?php esc_html_e( 'Disabled', 'wp-multilingual' ); ?></span>
												<?php endif; ?>
											</td>
											<td>
												<?php if ( (int) $lang->is_default !== 1 ) : ?>
													<a href="<?php echo esc_url( wp_nonce_url( admin_url( 'options-general.php?page=wpm-languages&wpm_action=delete_language&lang_id=' . $lang->id ), 'wpm_delete_language_' . $lang->id ) ); ?>" class="button button-small wpm-btn-delete" onclick="return confirm(wpmAdmin.confirm);">
														<?php esc_html_e( 'Delete', 'wp-multilingual' ); ?>
													</a>
												<?php endif; ?>
											</td>
										</tr>
									<?php endforeach; ?>
								</tbody>
							</table>
						<?php endif; ?>
					</div>
				</div>
			</div>
		</div>
		<?php
	}
}
