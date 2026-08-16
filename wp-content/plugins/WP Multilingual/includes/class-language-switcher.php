<?php
/**
 * Language Switcher Widget, Shortcode & Template Handler.
 *
 * @package WPMultilingual
 */

namespace WPMultilingual;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class LanguageSwitcher
 */
class LanguageSwitcher {

	/**
	 * Singleton instance.
	 *
	 * @var LanguageSwitcher|null
	 */
	private static $instance = null;

	/**
	 * Get singleton instance.
	 *
	 * @return LanguageSwitcher
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
	 * Initialize switcher hooks and assets.
	 */
	public function init() {
		add_shortcode( 'wpm_language_switcher', [ $this, 'render_shortcode' ] );
		add_shortcode( 'plugin_language_switcher', [ $this, 'render_shortcode' ] );
		add_shortcode( 'wpm_languages', [ $this, 'render_shortcode' ] );

		add_action( 'widgets_init', [ $this, 'register_widget' ] );
		add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_frontend_assets' ] );
	}

	/**
	 * Enqueue public styles and scripts.
	 */
	public function enqueue_frontend_assets() {
		wp_enqueue_style(
			'wpm-public-css',
			WPM_PLUGIN_URL . 'public/css/wpm-public.css',
			[],
			WPM_VERSION
		);

		wp_enqueue_script(
			'wpm-public-js',
			WPM_PLUGIN_URL . 'public/js/wpm-public.js',
			[ 'jquery' ],
			WPM_VERSION,
			true
		);
	}

	/**
	 * Register Widget.
	 */
	public function register_widget() {
		register_widget( __NAMESPACE__ . '\\LanguageSwitcherWidget' );
	}

	/**
	 * Shortcode callback.
	 *
	 * @param array $atts
	 * @return string
	 */
	public function render_shortcode( $atts = [] ) {
		$atts = shortcode_atts( [
			'type'                    => 'list', // 'list', 'dropdown'
			'show_flags'              => 1,
			'show_names'              => 1,
			'show_native_names'       => 0,
			'only_with_translations'  => 0,
		], $atts, 'wpm_language_switcher' );

		return $this->render( $atts );
	}

	/**
	 * Render switcher HTML.
	 *
	 * @param array $args
	 * @return string
	 */
	public function render( $args = [] ) {
		$defaults = [
			'type'                   => 'list',
			'show_flags'             => true,
			'show_names'             => true,
			'show_native_names'      => false,
			'only_with_translations' => false,
			'class'                  => 'wpm-language-switcher',
		];
		$args = wp_parse_args( $args, $defaults );

		$lang_mgr     = LanguageManager::get_instance();
		$trans_mgr    = TranslationManager::get_instance();
		$languages    = $lang_mgr->get_enabled_languages();
		$current_lang = $lang_mgr->get_current_language();

		if ( empty( $languages ) ) {
			return '';
		}

		$current_post_id = is_singular() ? ( get_queried_object_id() ?: get_the_ID() ) : 0;
		$translations    = $current_post_id ? $trans_mgr->get_translations( $current_post_id, 'post' ) : [];

		$items = [];
		foreach ( $languages as $lang ) {
			$url        = '';
			$is_current = ( $lang->code === $current_lang );

			if ( $current_post_id ) {
				$trans_id = $translations[ $lang->code ] ?? null;
				if ( $trans_id ) {
					$url = get_permalink( $trans_id );
				} elseif ( ! empty( $args['only_with_translations'] ) && ! $is_current ) {
					// Skip language if no translation exists
					continue;
				} else {
					$url = wpm_get_home_url( $lang->code );
				}
			} elseif ( is_tax() || is_category() || is_tag() ) {
				$term = get_queried_object();
				if ( $term && isset( $term->term_id ) ) {
					$trans_term_id = $trans_mgr->get_translation( $term->term_id, $lang->code, 'term' );
					if ( $trans_term_id ) {
						$url = get_term_link( (int) $trans_term_id, $term->taxonomy );
					} else {
						$url = wpm_get_home_url( $lang->code );
					}
				} else {
					$url = wpm_get_home_url( $lang->code );
				}
			} else {
				$url = wpm_get_home_url( $lang->code );
			}

			// Label formatting
			$label = '';
			if ( ! empty( $args['show_flags'] ) && ! empty( $lang->flag ) ) {
				$label .= '<span class="wpm-switcher-flag">' . esc_html( $lang->flag ) . '</span> ';
			}
			if ( ! empty( $args['show_native_names'] ) ) {
				$label .= esc_html( $lang->native_name );
			} elseif ( ! empty( $args['show_names'] ) ) {
				$label .= esc_html( $lang->name );
			} else {
				$label .= esc_html( strtoupper( $lang->code ) );
			}

			$items[] = [
				'code'       => $lang->code,
				'name'       => $lang->name,
				'url'        => $url,
				'label'      => trim( $label ),
				'is_current' => $is_current,
				'direction'  => $lang->direction,
			];
		}

		ob_start();

		if ( 'dropdown' === $args['type'] ) :
			?>
			<div class="<?php echo esc_attr( $args['class'] ); ?> wpm-switcher-dropdown-wrap">
				<select class="wpm-dropdown-select" aria-label="<?php esc_attr_e( 'Select Language', 'wp-multilingual' ); ?>">
					<?php foreach ( $items as $item ) : ?>
						<option value="<?php echo esc_url( $item['url'] ); ?>" <?php selected( $item['is_current'] ); ?> dir="<?php echo esc_attr( $item['direction'] ); ?>">
							<?php echo wp_strip_all_tags( $item['label'] ); ?>
						</option>
					<?php endforeach; ?>
				</select>
			</div>
			<?php
		else :
			?>
			<nav class="<?php echo esc_attr( $args['class'] ); ?> wpm-switcher-list-wrap" aria-label="<?php esc_attr_e( 'Language Switcher', 'wp-multilingual' ); ?>">
				<ul class="wpm-language-list">
					<?php foreach ( $items as $item ) : ?>
						<li class="wpm-lang-item wpm-lang-<?php echo esc_attr( $item['code'] ); ?><?php echo $item['is_current'] ? ' wpm-active' : ''; ?>" dir="<?php echo esc_attr( $item['direction'] ); ?>">
							<?php if ( $item['is_current'] ) : ?>
								<span class="wpm-lang-current" aria-current="page"><?php echo $item['label']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
							<?php else : ?>
								<a href="<?php echo esc_url( $item['url'] ); ?>" hreflang="<?php echo esc_attr( $item['code'] ); ?>" lang="<?php echo esc_attr( $item['code'] ); ?>">
									<?php echo $item['label']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
								</a>
							<?php endif; ?>
						</li>
					<?php endforeach; ?>
				</ul>
			</nav>
			<?php
		endif;

		$html = ob_get_clean();

		/**
		 * Filter language switcher rendered HTML.
		 *
		 * @param string $html
		 * @param array  $args
		 */
		return apply_filters( 'wpm_language_switcher_output', $html, $args );
	}
}

/**
 * Class LanguageSwitcherWidget
 */
class LanguageSwitcherWidget extends \WP_Widget {

	public function __construct() {
		parent::__construct(
			'wpm_language_switcher_widget',
			__( 'Language Switcher', 'wp-multilingual' ),
			[ 'description' => __( 'Display WP Multilingual language switcher.', 'wp-multilingual' ) ]
		);
	}

	public function widget( $args, $instance ) {
		echo $args['before_widget']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

		if ( ! empty( $instance['title'] ) ) {
			echo $args['before_title'] . apply_filters( 'widget_title', $instance['title'] ) . $args['after_title']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		}

		echo LanguageSwitcher::get_instance()->render( [
			'type'                   => $instance['type'] ?? 'list',
			'show_flags'             => ! empty( $instance['show_flags'] ),
			'show_names'             => ! empty( $instance['show_names'] ),
			'show_native_names'      => ! empty( $instance['show_native_names'] ),
			'only_with_translations' => ! empty( $instance['only_with_translations'] ),
		] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

		echo $args['after_widget']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}

	public function form( $instance ) {
		$title                  = $instance['title'] ?? __( 'Languages', 'wp-multilingual' );
		$type                   = $instance['type'] ?? 'list';
		$show_flags             = ! empty( $instance['show_flags'] );
		$show_names             = isset( $instance['show_names'] ) ? ! empty( $instance['show_names'] ) : true;
		$show_native_names      = ! empty( $instance['show_native_names'] );
		$only_with_translations = ! empty( $instance['only_with_translations'] );
		?>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"><?php esc_html_e( 'Title:', 'wp-multilingual' ); ?></label>
			<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>">
		</p>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'type' ) ); ?>"><?php esc_html_e( 'Display Type:', 'wp-multilingual' ); ?></label>
			<select class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'type' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'type' ) ); ?>">
				<option value="list" <?php selected( $type, 'list' ); ?>><?php esc_html_e( 'List', 'wp-multilingual' ); ?></option>
				<option value="dropdown" <?php selected( $type, 'dropdown' ); ?>><?php esc_html_e( 'Dropdown', 'wp-multilingual' ); ?></option>
			</select>
		</p>
		<p>
			<input type="checkbox" id="<?php echo esc_attr( $this->get_field_id( 'show_flags' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'show_flags' ) ); ?>" value="1" <?php checked( $show_flags ); ?>>
			<label for="<?php echo esc_attr( $this->get_field_id( 'show_flags' ) ); ?>"><?php esc_html_e( 'Show Flags', 'wp-multilingual' ); ?></label>
		</p>
		<p>
			<input type="checkbox" id="<?php echo esc_attr( $this->get_field_id( 'show_names' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'show_names' ) ); ?>" value="1" <?php checked( $show_names ); ?>>
			<label for="<?php echo esc_attr( $this->get_field_id( 'show_names' ) ); ?>"><?php esc_html_e( 'Show Language Names', 'wp-multilingual' ); ?></label>
		</p>
		<p>
			<input type="checkbox" id="<?php echo esc_attr( $this->get_field_id( 'show_native_names' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'show_native_names' ) ); ?>" value="1" <?php checked( $show_native_names ); ?>>
			<label for="<?php echo esc_attr( $this->get_field_id( 'show_native_names' ) ); ?>"><?php esc_html_e( 'Show Native Names', 'wp-multilingual' ); ?></label>
		</p>
		<p>
			<input type="checkbox" id="<?php echo esc_attr( $this->get_field_id( 'only_with_translations' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'only_with_translations' ) ); ?>" value="1" <?php checked( $only_with_translations ); ?>>
			<label for="<?php echo esc_attr( $this->get_field_id( 'only_with_translations' ) ); ?>"><?php esc_html_e( 'Only Show Available Translations', 'wp-multilingual' ); ?></label>
		</p>
		<?php
	}

	public function update( $new_instance, $old_instance ) {
		$instance                           = [];
		$instance['title']                  = sanitize_text_field( $new_instance['title'] ?? '' );
		$instance['type']                   = in_array( $new_instance['type'] ?? '', [ 'list', 'dropdown' ], true ) ? $new_instance['type'] : 'list';
		$instance['show_flags']             = ! empty( $new_instance['show_flags'] ) ? 1 : 0;
		$instance['show_names']             = ! empty( $new_instance['show_names'] ) ? 1 : 0;
		$instance['show_native_names']      = ! empty( $new_instance['show_native_names'] ) ? 1 : 0;
		$instance['only_with_translations'] = ! empty( $new_instance['only_with_translations'] ) ? 1 : 0;
		return $instance;
	}
}
