<?php
/**
 * Template: Attorneys Archive
 *
 * @package MeasuredAdvocacy
 */

get_header();

ma_editorial_header(
    __('People', 'measured-advocacy'),
    __('Our Attorneys', 'measured-advocacy'),
    __('Senior counsel with verifiable credentials, direct case involvement, and demonstrated expertise in their respective practice areas.', 'measured-advocacy')
);
?>

<section class="section surface-paper">
<div class="container">
<?php
$query = ma_query_posts('ma_attorney');
if ($query->have_posts()) :
?>
<div class="attorneys-grid" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: var(--space-8);">
<?php
while ($query->have_posts()) : $query->the_post();
$role = ma_field(get_the_ID(), 'ma_role', __('Attorney', 'measured-advocacy'));
$focus = ma_field(get_the_ID(), 'ma_focus');
?>
<article class="attorney-card">
<a href="<?php the_permalink(); ?>">
<?php if (has_post_thumbnail()) : ?>
    <?php the_post_thumbnail('large', array('style' => 'width: 100%; height: auto; border-radius: var(--radius-md);', 'loading' => 'lazy')); ?>
<?php else : ?>
    <img src="<?php echo esc_url(ma_img('people/img-003-managing-partner.jpg')); ?>" alt="<?php the_title_attribute(); ?>" style="width: 100%; height: auto; border-radius: var(--radius-md);" loading="lazy">
<?php endif; ?>
</a>
<div style="margin-top: var(--space-4);">
<h2 class="h3">
<a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
</h2>
<p class="small" style="margin-top: var(--space-2); color: var(--color-copper);">
<?php echo esc_html($role); ?>
</p>
<?php if ($focus) : ?>
<p class="body" style="margin-top: var(--space-3); color: var(--color-slate);">
<?php echo esc_html(wp_trim_words($focus, 20)); ?>
</p>
<?php endif; ?>
<a href="<?php the_permalink(); ?>" class="btn btn--text" style="margin-top: var(--space-3);">
<?php esc_html_e('View profile →', 'measured-advocacy'); ?>
</a>
</div>
</article>
<?php endwhile; wp_reset_postdata(); ?>
</div>
<?php else : ?>
<div class="no-results" style="padding: var(--space-10) 0; text-align: center;">
<p class="body-l"><?php esc_html_e('No attorneys listed yet.', 'measured-advocacy'); ?></p>
</div>
<?php endif; ?>
</div>
</section>

<?php get_footer();