<?php
/**
 * Template: Single Post (Insight)
 *
 * @package MeasuredAdvocacy
 */

get_header();

while (have_posts()) : the_post();
    $kicker = ma_field(get_the_ID(), 'ma_kicker', __('Insight', 'measured-advocacy'));
    $reading_time = ma_field(get_the_ID(), 'ma_reading_time', __('5 min read', 'measured-advocacy'));
    $jurisdiction = ma_field(get_the_ID(), 'ma_jurisdiction');

    ma_editorial_header($kicker, get_the_title(), ma_excerpt_or_field($post, ''));
?>

<article class="section surface-paper">
<div class="container">
<div class="article-meta" style="margin-bottom: var(--space-6); padding-bottom: var(--space-4); border-bottom: 1px solid var(--color-sage);">
<span class="small" style="color: var(--color-slate);"><?php echo esc_html($reading_time); ?></span>
<span class="small" style="color: var(--color-sage);" aria-hidden="true">·</span>
<time class="small" style="color: var(--color-slate);" datetime="<?php echo esc_attr(get_the_date('c')); ?>">
<?php echo esc_html(get_the_date('F j, Y')); ?>
</time>
<?php if ($jurisdiction) : ?>
<span class="small" style="color: var(--color-sage);" aria-hidden="true">·</span>
<span class="small" style="color: var(--color-slate);"><?php echo esc_html($jurisdiction); ?></span>
<?php endif; ?>
</div>

<?php if (has_post_thumbnail()) : ?>
<div class="article-featured-image" style="margin-bottom: var(--space-8);">
<?php the_post_thumbnail('full', array('style' => 'width: 100%; height: auto;')); ?>
</div>
<?php else : ?>
<div class="article-featured-image" style="margin-bottom: var(--space-8);">
<img src="<?php echo esc_url(ma_img('insights/img-014-decision-note.jpg')); ?>" alt="<?php the_title_attribute(); ?>" style="width: 100%; height: auto;">
</div>
<?php endif; ?>

<div class="content-prose">
<?php the_content(); ?>
</div>

<?php
$citations = ma_field(get_the_ID(), 'ma_citations');
if ($citations) :
?>
<div class="article-citations" style="margin-top: var(--space-8); padding-top: var(--space-6); border-top: 1px solid var(--color-sage);">
<h2 class="h3" style="margin-bottom: var(--space-4);"><?php esc_html_e('Citations & References', 'measured-advocacy'); ?></h2>
<div class="small" style="white-space: pre-line; color: var(--color-slate);">
<?php echo esc_html($citations); ?>
</div>
</div>
<?php endif; ?>
</div>
</article>

<?php endwhile;

get_footer();