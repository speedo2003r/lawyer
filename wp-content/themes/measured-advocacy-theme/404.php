<?php
/**
 * Template: 404 Not Found
 *
 * @package MeasuredAdvocacy
 */

get_header();

ma_editorial_header('404', __('Page Not Found', 'measured-advocacy'), __('The page you are looking for does not exist or has been moved.', 'measured-advocacy'));
?>

<section class="section surface-paper">
<div class="container" style="text-align: center; padding: var(--space-10) 0;">
<p class="body-l" style="margin-bottom: var(--space-6);">
<?php esc_html_e('You may have followed an outdated link, or the page may have been removed.', 'measured-advocacy'); ?>
</p>
<div style="display: flex; gap: var(--space-4); justify-content: center; flex-wrap: wrap;">
<a href="<?php echo esc_url(home_url('/')); ?>" class="btn btn--primary">
<?php esc_html_e('Return Home', 'measured-advocacy'); ?>
</a>
<a href="<?php echo esc_url(home_url('/contact')); ?>" class="btn btn--secondary">
<?php esc_html_e('Contact Us', 'measured-advocacy'); ?>
</a>
</div>
</div>
</section>

<?php get_footer();