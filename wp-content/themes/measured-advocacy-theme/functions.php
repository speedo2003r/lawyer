<?php
/**
 * Theme bootstrap.
 *
 * @package MeasuredAdvocacy
 */

if (!defined('ABSPATH')) {
    exit;
}

define('MA_THEME_VERSION', '1.0.0');
define('MA_THEME_DIR', get_template_directory());
define('MA_THEME_URI', get_template_directory_uri());

require_once MA_THEME_DIR . '/inc/helpers.php';
require_once MA_THEME_DIR . '/inc/post-types.php';
require_once MA_THEME_DIR . '/inc/meta.php';
require_once MA_THEME_DIR . '/inc/demo-seeder.php';

add_filter('locale', 'ma_filter_locale');
add_filter('determine_locale', 'ma_filter_locale');
function ma_filter_locale(string $locale): string {
    if (function_exists('wpm_get_current_language')) {
        $lang = wpm_get_current_language();
        if ('ar' === $lang) {
            return 'ar';
        }
    }
    return $locale;
}

add_action('after_setup_theme', 'ma_theme_setup');
function ma_theme_setup(): void {
    ma_load_textdomain();

    add_theme_support('title-tag');
    add_theme_support('post-thumbnails');
    add_theme_support('custom-logo', array(
        'height' => 80,
        'width' => 320,
        'flex-height' => true,
        'flex-width' => true,
    ));
    add_theme_support('html5', array('search-form', 'gallery', 'caption', 'style', 'script'));
    add_theme_support('responsive-embeds');
    add_theme_support('editor-styles');
    add_editor_style('assets/css/theme.css');

    register_nav_menus(array(
        'primary' => __('Primary Navigation', 'measured-advocacy'),
        'footer' => __('Footer Navigation', 'measured-advocacy'),
    ));
}

add_action('init', 'ma_load_textdomain', 20);
add_action('wpm_language_switched', 'ma_load_textdomain');
function ma_load_textdomain(): void {
    $locale = ma_locale();
    unload_textdomain('measured-advocacy');
    if ('ar' === $locale) {
        load_textdomain('measured-advocacy', MA_THEME_DIR . '/languages/measured-advocacy-ar.mo');
    } else {
        load_theme_textdomain('measured-advocacy', MA_THEME_DIR . '/languages');
    }
}

add_action('wp_enqueue_scripts', 'ma_enqueue_assets');
function ma_enqueue_assets(): void {
    wp_enqueue_style(
        'ma-google-fonts',
        'https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Noto+Sans+Arabic:wght@400;500;600&family=Noto+Serif+Arabic:wght@400;500;600&family=Source+Serif+4:ital,opsz,wght@0,8..60,400;0,8..60,600&display=swap',
        array(),
        null
    );
    wp_enqueue_style('ma-theme', MA_THEME_URI . '/assets/css/theme.css', array('ma-google-fonts'), MA_THEME_VERSION);
    wp_enqueue_script('gsap', MA_THEME_URI . '/assets/js/vendor/gsap.min.js', array(), '3.15.0', true);
    wp_enqueue_script('gsap-scrolltrigger', MA_THEME_URI . '/assets/js/vendor/ScrollTrigger.min.js', array('gsap'), '3.15.0', true);
    wp_enqueue_script('ma-theme', MA_THEME_URI . '/assets/js/theme.js', array('gsap', 'gsap-scrolltrigger'), MA_THEME_VERSION, true);
}

add_filter('body_class', 'ma_body_classes');
function ma_body_classes(array $classes): array {
    $classes[] = 'ma-locale-' . ma_locale();
    $classes[] = is_front_page() ? 'page-home' : 'editorial-page';
    return $classes;
}

add_filter('template_include', 'ma_template_include');
function ma_template_include(string $template): string {
    if (is_page()) {
        $post = get_queried_object();
        if ($post instanceof WP_Post) {
            $slug = $post->post_name;
            $mapping = array(
                'about' => 'page-about.php',
                'about-ar' => 'page-about.php',
                'contact' => 'page-contact.php',
                'contact-ar' => 'page-contact.php',
                'consultation' => 'page-consultation.php',
                'consultation-ar' => 'page-consultation.php',
                'privacy' => 'page-privacy.php',
                'privacy-ar' => 'page-privacy.php',
                'legal' => 'page-legal.php',
                'legal-ar' => 'page-legal.php',
                'accessibility' => 'page-accessibility.php',
                'accessibility-ar' => 'page-accessibility.php',
            );
            if (isset($mapping[$slug])) {
                $custom_template = MA_THEME_DIR . '/' . $mapping[$slug];
                if (file_exists($custom_template)) {
                    return $custom_template;
                }
            }
        }
    }
    return $template;
}

add_action('customize_register', 'ma_customize_register');
function ma_customize_register(WP_Customize_Manager $wp_customize): void {
    $wp_customize->add_section('ma_firm', array(
        'title' => __('Firm Details', 'measured-advocacy'),
        'priority' => 30,
    ));

    $settings = array(
        'ma_firm_name' => array(__('Firm Name', 'measured-advocacy'), '[Firm Name]'),
        'ma_legal_entity' => array(__('Legal Entity', 'measured-advocacy'), '[Legal Entity]'),
        'ma_phone' => array(__('Phone', 'measured-advocacy'), '+000 000 0000'),
        'ma_email' => array(__('Email', 'measured-advocacy'), 'info@example.com'),
        'ma_address' => array(__('Address', 'measured-advocacy'), '[Office Address], [City, Country]'),
        'ma_hours' => array(__('Office Hours', 'measured-advocacy'), 'Sunday - Thursday: 8:30 AM - 5:30 PM'),
    );

    foreach ($settings as $id => $data) {
        $wp_customize->add_setting($id, array(
            'default' => $data[1],
            'sanitize_callback' => 'sanitize_text_field',
        ));
        $wp_customize->add_control($id, array(
            'label' => $data[0],
            'section' => 'ma_firm',
            'type' => 'text',
        ));
    }
}