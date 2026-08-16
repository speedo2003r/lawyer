<?php
/**
 * Custom post types.
 *
 * @package MeasuredAdvocacy
 */

if (!defined('ABSPATH')) {
    exit;
}

add_action('init', 'ma_register_content_types');
function ma_register_content_types(): void {
    register_taxonomy('practice_group', array('ma_practice'), array(
        'labels' => array('name' => __('Practice Groups', 'measured-advocacy'), 'singular_name' => __('Practice Group', 'measured-advocacy')),
        'public' => true,
        'hierarchical' => true,
        'show_in_rest' => true,
        'rewrite' => array('slug' => 'practice-group'),
    ));

    register_post_type('ma_practice', array(
        'labels' => array('name' => __('Practice Areas', 'measured-advocacy'), 'singular_name' => __('Practice Area', 'measured-advocacy')),
        'public' => true,
        'has_archive' => 'expertise',
        'menu_icon' => 'dashicons-portfolio',
        'show_in_rest' => true,
        'rewrite' => array('slug' => 'expertise'),
        'supports' => array('title', 'editor', 'excerpt', 'thumbnail', 'page-attributes'),
        'taxonomies' => array('practice_group'),
    ));

    register_post_type('ma_attorney', array(
        'labels' => array('name' => __('Attorneys', 'measured-advocacy'), 'singular_name' => __('Attorney', 'measured-advocacy')),
        'public' => true,
        'has_archive' => 'people',
        'menu_icon' => 'dashicons-businessperson',
        'show_in_rest' => true,
        'rewrite' => array('slug' => 'people'),
        'supports' => array('title', 'editor', 'excerpt', 'thumbnail', 'page-attributes'),
    ));

    register_post_type('ma_matter', array(
        'labels' => array('name' => __('Representative Matters', 'measured-advocacy'), 'singular_name' => __('Representative Matter', 'measured-advocacy')),
        'public' => true,
        'has_archive' => 'experience',
        'menu_icon' => 'dashicons-analytics',
        'show_in_rest' => true,
        'rewrite' => array('slug' => 'experience'),
        'supports' => array('title', 'editor', 'excerpt', 'thumbnail', 'page-attributes'),
    ));
}