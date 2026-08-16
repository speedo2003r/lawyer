<?php
/**
 * Template: Practice Areas Archive
 *
 * @package MeasuredAdvocacy
 */

get_header();

ma_editorial_header(
    __('Expertise', 'measured-advocacy'),
    __('Practice Areas', 'measured-advocacy'),
    __('Senior legal counsel across corporate, litigation, property, employment, family law, criminal defense, and intellectual property matters.', 'measured-advocacy')
);
?>

<section class="section surface-paper">
<div class="container">
<?php
$query = ma_query_posts('ma_practice');
if ($query->have_posts()) :
?>
<div class="practices-grid" style="display: grid; gap: var(--space-8);">
<?php
while ($query->have_posts()) : $query->the_post();
$kicker = ma_field(get_the_ID(), 'ma_kicker', __('Practice Area', 'measured-advocacy'));
?>
<article class="practice-card" style="border-bottom: 1px solid var(--color-sage); padding-bottom: var(--space-6);">
<div class="grid">
<div style="grid-column: span 7;">
<p class="small" style="color: var(--color-copper);"><?php echo esc_html($kicker); ?></p>
<h2 class="h2" style="margin-top: var(--space-2);">
<a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
</h2>
<div class="body-l" style="margin-top: var(--space-4); color: var(--color-slate);">
<?php echo esc_html(ma_excerpt_or_field($post, '')); ?>
</div>
<a href="<?php the_permalink(); ?>" class="btn btn--text" style="margin-top: var(--space-4);">
<?php esc_html_e('View expertise →', 'measured-advocacy'); ?>
</a>
</div>
<div style="grid-column: span 5;">
<?php if (has_post_thumbnail()) : ?>
    <?php the_post_thumbnail('large', array('style' => 'width: 100%; height: auto; border-radius: var(--radius-md);', 'loading' => 'lazy')); ?>
<?php else : ?>
    <img src="<?php echo esc_url(ma_img('office/img-002-approach.jpg')); ?>" alt="<?php the_title_attribute(); ?>" style="width: 100%; height: auto; border-radius: var(--radius-md);" loading="lazy">
<?php endif; ?>
</div>
</div>
</article>
<?php endwhile; wp_reset_postdata(); ?>
</div>
<?php else : ?>
<div class="no-results" style="padding: var(--space-10) 0; text-align: center;">
<p class="body-l"><?php esc_html_e('No practice areas defined yet.', 'measured-advocacy'); ?></p>
</div>
<?php endif; ?>
</div>
</section>

<?php get_footer();