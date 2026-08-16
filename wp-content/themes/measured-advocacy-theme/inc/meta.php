<?php
/**
 * Admin meta fields.
 *
 * @package MeasuredAdvocacy
 */

if (!defined('ABSPATH')) {
    exit;
}

function ma_meta_fields(string $post_type): array {
    if ('ma_practice' === $post_type) {
        return array(
            'ma_kicker' => array('label' => __('Practice Group Label', 'measured-advocacy'), 'type' => 'text'),
            'ma_decision_heading' => array('label' => __('Decision Heading', 'measured-advocacy'), 'type' => 'text'),
            'ma_decision_body' => array('label' => __('Decision Body', 'measured-advocacy'), 'type' => 'textarea'),
            'ma_exposure_heading' => array('label' => __('Exposure Heading', 'measured-advocacy'), 'type' => 'text'),
            'ma_exposure_body' => array('label' => __('Exposure Body', 'measured-advocacy'), 'type' => 'textarea'),
            'ma_counsel_heading' => array('label' => __('Counsel Heading', 'measured-advocacy'), 'type' => 'text'),
            'ma_counsel_body' => array('label' => __('Counsel Body', 'measured-advocacy'), 'type' => 'textarea'),
            'ma_caveat' => array('label' => __('Scope Caveat', 'measured-advocacy'), 'type' => 'textarea'),
        );
    }

    if ('ma_attorney' === $post_type) {
        return array(
            'ma_role' => array('label' => __('Role', 'measured-advocacy'), 'type' => 'text'),
            'ma_focus' => array('label' => __('Focus', 'measured-advocacy'), 'type' => 'textarea'),
            'ma_admissions' => array('label' => __('Admissions', 'measured-advocacy'), 'type' => 'textarea'),
            'ma_jurisdictions' => array('label' => __('Jurisdictions', 'measured-advocacy'), 'type' => 'textarea'),
            'ma_languages' => array('label' => __('Languages', 'measured-advocacy'), 'type' => 'textarea'),
            'ma_email' => array('label' => __('Public Email', 'measured-advocacy'), 'type' => 'text'),
            'ma_phone' => array('label' => __('Public Phone', 'measured-advocacy'), 'type' => 'text'),
        );
    }

    if ('ma_matter' === $post_type) {
        return array(
            'ma_kicker' => array('label' => __('Record Label', 'measured-advocacy'), 'type' => 'text'),
            'ma_sector' => array('label' => __('Sector', 'measured-advocacy'), 'type' => 'text'),
            'ma_challenge' => array('label' => __('Challenge', 'measured-advocacy'), 'type' => 'textarea'),
            'ma_contribution' => array('label' => __('Counsel Contribution', 'measured-advocacy'), 'type' => 'textarea'),
            'ma_caveat' => array('label' => __('Confidentiality Caveat', 'measured-advocacy'), 'type' => 'textarea'),
        );
    }

    if ('post' === $post_type) {
        return array(
            'ma_kicker' => array('label' => __('Insight Type', 'measured-advocacy'), 'type' => 'text'),
            'ma_reading_time' => array('label' => __('Reading Time', 'measured-advocacy'), 'type' => 'text'),
            'ma_jurisdiction' => array('label' => __('Jurisdiction', 'measured-advocacy'), 'type' => 'text'),
            'ma_citations' => array('label' => __('Citations', 'measured-advocacy'), 'type' => 'textarea'),
        );
    }

    return array();
}

add_action('add_meta_boxes', 'ma_add_meta_boxes');
function ma_add_meta_boxes(): void {
    foreach (array('ma_practice', 'ma_attorney', 'ma_matter', 'post') as $post_type) {
        add_meta_box('ma_details', __('Measured Advocacy Details', 'measured-advocacy'), 'ma_render_meta_box', $post_type, 'normal', 'high');
    }
}

function ma_render_meta_box(WP_Post $post): void {
    wp_nonce_field('ma_save_meta', 'ma_meta_nonce');
    foreach (ma_meta_fields($post->post_type) as $key => $field) {
        $value = get_post_meta($post->ID, $key, true);
        echo '<p><label for="' . esc_attr($key) . '"><strong>' . esc_html($field['label']) . '</strong></label><br>';
        if ('textarea' === $field['type']) {
            echo '<textarea id="' . esc_attr($key) . '" name="' . esc_attr($key) . '" rows="4" style="width:100%;">' . esc_textarea($value) . '</textarea>';
        } else {
            echo '<input id="' . esc_attr($key) . '" name="' . esc_attr($key) . '" type="text" value="' . esc_attr($value) . '" style="width:100%;">';
        }
        echo '</p>';
    }
}

add_action('save_post', 'ma_save_meta');
function ma_save_meta(int $post_id): void {
    if (!isset($_POST['ma_meta_nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['ma_meta_nonce'])), 'ma_save_meta')) {
        return;
    }
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }
    if (!current_user_can('edit_post', $post_id)) {
        return;
    }

    $post_type = (string) get_post_type($post_id);
    foreach (ma_meta_fields($post_type) as $key => $field) {
        if (!array_key_exists($key, $_POST)) {
            continue;
        }
        $raw = wp_unslash($_POST[$key]);
        $value = 'textarea' === $field['type'] ? sanitize_textarea_field($raw) : sanitize_text_field($raw);
        update_post_meta($post_id, $key, $value);
    }
}