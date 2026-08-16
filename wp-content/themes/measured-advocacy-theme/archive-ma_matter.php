<?php
/**
 * Template: Representative Matters Archive
 *
 * @package MeasuredAdvocacy
 */

get_header();

ma_editorial_header(
    __('Experience', 'measured-advocacy'),
    __('Representative Matters', 'measured-advocacy'),
    __('Selected engagements that demonstrate the scope and nature of counsel provided, within ethical and confidentiality boundaries.', 'measured-advocacy')
);
?>

<section class="section surface-paper">
<div class="container">
<?php
$query = ma_query_posts('ma_matter');
if ($query->have_posts()) :
?>
<div class="matters-grid" style="display: grid; gap: var(--space-8);">
<?php
while ($query->have_posts()) : $query->the_post();
$kicker = ma_field(get_the_ID(), 'ma_kicker', __('Representative Matter', 'measured-advocacy'));
$sector = ma_field(get_the_ID(), 'ma_sector');
?>
<article class="matter-card" style="border-bottom: 1px solid var(--color-sage); padding-bottom: var(--space-6);">
<p class="small" style="color: var(--color-copper);"><?php echo esc_html($kicker); ?></p>
<?php if ($sector) : ?>
<p class="small" style="margin-top: var(--space-1); color: var(--color-slate);"><?php esc_html_e('Sector:', 'measured-advocacy'); ?> <?php echo esc_html($sector); ?></p>
<?php endif; ?>
<h2 class="h2" style="margin-top: var(--space-3);">
<a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
</h2>
<div class="body-l" style="margin-top: var(--space-4); color: var(--color-slate);">
<?php echo esc_html(ma_excerpt_or_field($post, '')); ?>
</div>
<a href="<?php the_permalink(); ?>" class="btn btn--text" style="margin-top: var(--space-4);">
<?php esc_html_e('View matter →', 'measured-advocacy'); ?>
</a>
</article>
<?php endwhile; wp_reset_postdata(); ?>
</div>
<?php else : ?>
<div class="no-results" style="padding: var(--space-10) 0; text-align: center;">
<p class="body-l"><?php esc_html_e('No representative matters shared yet.', 'measured-advocacy'); ?></p>
</div>
<?php endif; ?>
</div>
</section>

<?php get_footer();