<?php
/**
 * Main Plugin Orchestrator & Component Container.
 *
 * @package WPMultilingual
 */

namespace WPMultilingual;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Plugin
 */
class Plugin {

	/**
	 * Singleton instance.
	 *
	 * @var Plugin|null
	 */
	private static $instance = null;

	/**
	 * Services registry.
	 *
	 * @var array
	 */
	private $services = [];

	/**
	 * Get singleton instance.
	 *
	 * @return Plugin
	 */
	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Private constructor.
	 */
	private function __construct() {}

	/**
	 * Initialize plugin components.
	 */
	public function init() {
		// Load textdomain for i18n
		load_plugin_textdomain(
			'wp-multilingual',
			false,
			dirname( WPM_PLUGIN_BASENAME ) . '/languages'
		);

		// Check if we need to flush rewrite rules (e.g., after activation or settings change)
		add_action( 'init', [ $this, 'maybe_flush_rewrite_rules' ], 99 );

		// Check and run database migrations if needed
		Installer::maybe_update();

		// Register core services
		$this->register_services();

		// Hook services into WordPress
		$this->boot_services();

		do_action( 'wpm_loaded' );
	}

	/**
	 * Register plugin services into DI container.
	 */
	private function register_services() {
		$this->services['language_manager']     = LanguageManager::get_instance();
		$this->services['translation_manager']  = TranslationManager::get_instance();
		$this->services['language_detector']    = LanguageDetector::get_instance();
		$this->services['rewrite']              = Rewrite::get_instance();
		$this->services['query_filter']         = QueryFilter::get_instance();
		$this->services['post_integration']     = PostIntegration::get_instance();
		$this->services['taxonomy_integration'] = TaxonomyIntegration::get_instance();
		$this->services['sync']                 = Sync::get_instance();
		$this->services['language_switcher']    = LanguageSwitcher::get_instance();
		$this->services['seo_integration']      = SeoIntegration::get_instance();
		$this->services['rest_api']             = RestApi::get_instance();
		$this->services['gutenberg']            = Gutenberg::get_instance();

		if ( is_admin() ) {
			$this->services['admin']          = Admin::get_instance();
			$this->services['settings']       = Settings::get_instance();
			$this->services['admin_meta_box'] = AdminMetaBox::get_instance();
			$this->services['admin_columns']  = AdminColumns::get_instance();
		}
	}

	/**
	 * Boot all registered services by executing their init() / hooks.
	 */
	private function boot_services() {
		foreach ( $this->services as $service ) {
			if ( method_exists( $service, 'init' ) ) {
				$service->init();
			}
		}
	}

	/**
	 * Get a registered service from the container.
	 *
	 * @param string $key
	 * @return object|null
	 */
	public function get_service( $key ) {
		return $this->services[ $key ] ?? null;
	}

	/**
	 * Check and safely flush rewrite rules if transient is set.
	 */
	public function maybe_flush_rewrite_rules() {
		if ( get_transient( 'wpm_flush_rewrite_rules' ) ) {
			delete_transient( 'wpm_flush_rewrite_rules' );
			flush_rewrite_rules( false );
		}
	}
}
