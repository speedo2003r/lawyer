<!DOCTYPE html>
<html <?php language_attributes(); ?> dir="<?php echo esc_attr(ma_dir()); ?>">
<head>
<meta charset="<?php bloginfo('charset'); ?>">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<a class="skip-link" href="#main-content"><?php esc_html_e('Skip to main content', 'measured-advocacy'); ?></a>

<header class="site-header" id="site-header" data-compact="false">
<div class="header-inner container">
<!-- Logo -->
<a href="<?php echo esc_url(home_url('/')); ?>" class="header-logo" aria-label="<?php echo esc_attr(ma_firm('ma_firm_name')); ?>">
<span class="header-logo__text"><?php echo esc_html(ma_firm('ma_firm_name')); ?></span>
</a>

<!-- Desktop Navigation -->
<nav class="header-nav" aria-label="<?php echo 'ar' === ma_locale() ? 'التنقل الرئيسي' : 'Primary navigation'; ?>">
<?php ma_primary_menu(); ?>
</nav>

<!-- Utilities -->
<div class="header-utilities">
<!-- Phone -->
<a href="<?php echo esc_attr(ma_phone_href()); ?>" class="header-phone ltr-isolate" aria-label="<?php esc_attr_e('Call Us', 'measured-advocacy'); ?>">
<svg class="header-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
<path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path>
</svg>
<span class="header-phone__label sr-only"><?php esc_html_e('Call Us', 'measured-advocacy'); ?></span>
</a>

<!-- Language Switch -->
<?php
$lang_switcher = ma_language_switcher();
if (!empty($lang_switcher)) :
    $lang = $lang_switcher[0];
?>
<a href="<?php echo esc_url($lang['url']); ?>" class="header-lang-switch" lang="<?php echo esc_attr($lang['code']); ?>" dir="<?php echo esc_attr($lang['direction'] ?? 'ltr'); ?>">
<?php echo esc_html($lang['label']); ?>
</a>
<?php endif; ?>

<!-- Consultation CTA -->
<a href="<?php echo esc_url(home_url('/consultation')); ?>" class="header-cta">
<?php echo 'ar' === ma_locale() ? 'طلب استشارة' : __('Request Consultation', 'measured-advocacy'); ?>
</a>

<!-- Mobile Menu Toggle -->
<button class="header-menu-toggle" aria-expanded="false" aria-controls="mobile-menu" aria-label="<?php esc_attr_e('Menu', 'measured-advocacy'); ?>" data-menu-open-label="<?php esc_attr_e('Menu', 'measured-advocacy'); ?>" data-menu-close-label="<?php esc_attr_e('Close', 'measured-advocacy'); ?>">
<svg class="header-menu-toggle__icon header-menu-toggle__icon--open" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
<line x1="3" y1="6" x2="21" y2="6"></line>
<line x1="3" y1="12" x2="21" y2="12"></line>
<line x1="3" y1="18" x2="21" y2="18"></line>
</svg>
<svg class="header-menu-toggle__icon header-menu-toggle__icon--close" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true" style="display:none;">
<line x1="18" y1="6" x2="6" y2="18"></line>
<line x1="6" y1="6" x2="18" y2="18"></line>
</svg>
</button>
</div>
</div>

<!-- Mobile Menu -->
<div class="mobile-menu" id="mobile-menu" aria-hidden="true">
<nav aria-label="<?php echo 'ar' === ma_locale() ? 'التنقل في الهاتف' : 'Mobile navigation'; ?>">
<ul class="mobile-menu__list" role="list">
<?php
$mobile_items = ma_nav_items();
foreach ($mobile_items as $item) {
    $is_active = ma_is_nav_active($item);
    $active_class = $is_active ? ' is-active' : '';
    printf(
        '<li class="mobile-menu__item"><a href="%s" class="mobile-menu__link%s">%s</a></li>',
        esc_url(home_url('/' . $item['slug'] . '/')),
        esc_attr($active_class),
        esc_html($item['label'])
    );
}
?>
</ul>
<div class="mobile-menu__actions">
<a href="<?php echo esc_url(home_url('/consultation')); ?>" class="mobile-menu__cta">
<?php echo 'ar' === ma_locale() ? 'طلب استشارة' : __('Request Consultation', 'measured-advocacy'); ?>
</a>
<a href="<?php echo esc_attr(ma_phone_href()); ?>" class="mobile-menu__phone ltr-isolate">
<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
<path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path>
</svg>
<span><?php echo esc_html(ma_firm('ma_phone')); ?></span>
</a>
<?php if (!empty($lang_switcher)) : ?>
<a href="<?php echo esc_url($lang_switcher[0]['url']); ?>" class="mobile-menu__lang" lang="<?php echo esc_attr($lang_switcher[0]['code']); ?>" dir="<?php echo esc_attr($lang_switcher[0]['direction'] ?? 'ltr'); ?>">
<?php echo esc_html($lang_switcher[0]['label']); ?>
</a>
<?php endif; ?>
</div>
</nav>
</div>
</header>

<main id="main-content">