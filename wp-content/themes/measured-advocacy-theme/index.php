<?php
/**
 * Template: Blog Index / Archive
 *
 * @package MeasuredAdvocacy
 */

get_header();

ma_editorial_header(
    __('Insights', 'measured-advocacy'),
    __('Legal Analysis & Thought Leadership', 'measured-advocacy'),
    __('Decision notes, jurisdictional analysis, and strategic counsel perspectives on matters that shape commercial and personal legal practice.', 'measured-advocacy')
);
?>

<section class="section surface-paper">
<div class="container">
<?php if (have_posts()) : ?>
<div class="insights-grid">
<?php
while (have_posts()) : the_post();
$kicker = ma_field(get_the_ID(), 'ma_kicker', __('Insight', 'measured-advocacy'));
$reading_time = ma_field(get_the_ID(), 'ma_reading_time', __('5 min read', 'measured-advocacy'));
?>
<article class="insight-card">
<a href="<?php the_permalink(); ?>" class="insight-card__image-link">
<?php if (has_post_thumbnail()) : ?>
    <?php the_post_thumbnail('large', array('class' => 'insight-card__image', 'loading' => 'lazy')); ?>
<?php else : ?>
    <img src="<?php echo esc_url(ma_img('insights/img-014-decision-note.jpg')); ?>" alt="<?php the_title_attribute(); ?>" class="insight-card__image" loading="lazy">
<?php endif; ?>
</a>
<div class="insight-card__content">
<span class="insight-card__type small" style="color: var(--color-copper);"><?php echo esc_html($kicker); ?></span>
<h2 class="insight-card__heading h3" style="margin-top: var(--space-2);">
<a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
</h2>
<?php if (has_excerpt()) : ?>
<p class="insight-card__excerpt body" style="margin-top: var(--space-3); color: var(--color-slate);">
<?php echo esc_html(get_the_excerpt()); ?>
</p>
<?php endif; ?>
<div class="insight-card__meta" style="margin-top: var(--space-4);">
<span class="small" style="color: var(--color-slate);"><?php echo esc_html($reading_time); ?></span>
<span class="small" style="color: var(--color-sage);" aria-hidden="true">·</span>
<time class="small" style="color: var(--color-slate);" datetime="<?php echo esc_attr(get_the_date('c')); ?>">
<?php echo esc_html(get_the_date('F Y')); ?>
</time>
</div>
</div>
</article>
<?php endwhile; ?>
</div>

<?php
the_posts_pagination(array(
    'mid_size' => 2,
    'prev_text' => __('← Previous', 'measured-advocacy'),
    'next_text' => __('Next →', 'measured-advocacy'),
));
?>

<?php else : ?>
<div class="no-results" style="padding: var(--space-10) 0; text-align: center;">
<p class="body-l"><?php esc_html_e('No insights published yet.', 'measured-advocacy'); ?></p>
</div>
<?php endif; ?>
</div>
</section>

<?php get_footer();