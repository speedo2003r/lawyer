</main>

<footer class="site-footer surface-ink">
<div class="footer-inner container">
<!-- Primary footer content -->
<div class="footer-grid grid">
<!-- Firm identity -->
<div class="footer-identity">
<a href="<?php echo esc_url(home_url('/')); ?>" class="footer-logo">
<?php echo esc_html(ma_firm('ma_firm_name')); ?>
</a>
<p class="footer-entity"><?php echo esc_html(ma_firm('ma_legal_entity')); ?></p>
</div>

<!-- Navigation columns -->
<nav class="footer-nav" aria-label="Footer navigation">
<div class="footer-nav-col">
<?php ma_footer_menu(); ?>
</div>
<div class="footer-nav-col">
<ul role="list">
<li><a href="<?php echo esc_url(home_url('/contact')); ?>"><?php esc_html_e('Contact', 'measured-advocacy'); ?></a></li>
<li><a href="<?php echo esc_url(home_url('/consultation')); ?>"><?php echo esc_html(__('Request Consultation', 'measured-advocacy')); ?></a></li>
</ul>
</div>
</nav>

<!-- Contact info -->
<div class="footer-contact">
<a href="<?php echo esc_attr(ma_phone_href()); ?>" class="footer-phone ltr-isolate"><?php echo esc_html(ma_firm('ma_phone')); ?></a>
<a href="mailto:<?php echo esc_attr(ma_firm('ma_email')); ?>" class="footer-email ltr-isolate"><?php echo esc_html(ma_firm('ma_email')); ?></a>
</div>
</div>

<!-- Footer bottom -->
<div class="footer-bottom">
<div class="footer-legal-links">
<a href="<?php echo esc_url(home_url('/privacy')); ?>"><?php esc_html_e('Privacy', 'measured-advocacy'); ?></a>
<span class="footer-divider" aria-hidden="true">·</span>
<a href="<?php echo esc_url(home_url('/legal')); ?>"><?php esc_html_e('Legal Notice', 'measured-advocacy'); ?></a>
<span class="footer-divider" aria-hidden="true">·</span>
<a href="<?php echo esc_url(home_url('/accessibility')); ?>"><?php esc_html_e('Accessibility', 'measured-advocacy'); ?></a>
</div>
<div class="footer-meta">
<?php
$lang_switcher = ma_language_switcher();
if (!empty($lang_switcher)) :
?>
<a href="<?php echo esc_url($lang_switcher[0]['url']); ?>" class="footer-lang" lang="<?php echo esc_attr($lang_switcher[0]['code']); ?>" dir="<?php echo esc_attr($lang_switcher[0]['direction'] ?? 'ltr'); ?>">
<?php echo esc_html($lang_switcher[0]['label']); ?>
</a>
<?php endif; ?>
<p class="footer-rights">&copy; <?php echo esc_html(date('Y')); ?> <?php echo esc_html(ma_firm('ma_firm_name')); ?>. <?php esc_html_e('All rights reserved.', 'measured-advocacy'); ?></p>
</div>
</div>
</div>
</footer>

<?php wp_footer(); ?>
</body>
</html>
