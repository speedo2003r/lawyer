<?php
/**
 * Template: Single Practice Area
 *
 * @package MeasuredAdvocacy
 */

get_header();

while (have_posts()) : the_post();
    $kicker = ma_field(get_the_ID(), 'ma_kicker', __('Practice Area', 'measured-advocacy'));
    $decision_heading = ma_field(get_the_ID(), 'ma_decision_heading', __('The Decision', 'measured-advocacy'));
    $decision_body = ma_field(get_the_ID(), 'ma_decision_body');
    $exposure_heading = ma_field(get_the_ID(), 'ma_exposure_heading', __('The Exposure', 'measured-advocacy'));
    $exposure_body = ma_field(get_the_ID(), 'ma_exposure_body');
    $counsel_heading = ma_field(get_the_ID(), 'ma_counsel_heading', __('The Counsel', 'measured-advocacy'));
    $counsel_body = ma_field(get_the_ID(), 'ma_counsel_body');
    $caveat = ma_field(get_the_ID(), 'ma_caveat');

    ma_editorial_header($kicker, get_the_title(), ma_excerpt_or_field($post, ''));
?>

<article class="section surface-paper">
<div class="container">
<?php if (has_post_thumbnail()) : ?>
<div style="margin-bottom: var(--space-8);">
<?php the_post_thumbnail('full', array('style' => 'width: 100%; height: auto;')); ?>
</div>
<?php else : ?>
<div style="margin-bottom: var(--space-8);">
<img src="<?php echo esc_url(ma_img('office/img-002-approach.jpg')); ?>" alt="<?php the_title_attribute(); ?>" style="width: 100%; height: auto;">
</div>
<?php endif; ?>

<div class="content-prose">
<?php the_content(); ?>
</div>

<?php if ($decision_body || $exposure_body || $counsel_body) : ?>
<div class="practice-framework" style="margin-top: var(--space-10); padding-top: var(--space-8); border-top: 1px solid var(--color-sage);">
<div class="grid" style="gap: var(--space-8);">
<?php if ($decision_body) : ?>
<div style="grid-column: span 4;">
<h2 class="h3"><?php echo esc_html($decision_heading); ?></h2>
<p class="body" style="margin-top: var(--space-3); color: var(--color-slate);">
<?php echo nl2br(esc_html($decision_body)); ?>
</p>
</div>
<?php endif; ?>

<?php if ($exposure_body) : ?>
<div style="grid-column: span 4;">
<h2 class="h3"><?php echo esc_html($exposure_heading); ?></h2>
<p class="body" style="margin-top: var(--space-3); color: var(--color-slate);">
<?php echo nl2br(esc_html($exposure_body)); ?>
</p>
</div>
<?php endif; ?>

<?php if ($counsel_body) : ?>
<div style="grid-column: span 4;">
<h2 class="h3"><?php echo esc_html($counsel_heading); ?></h2>
<p class="body" style="margin-top: var(--space-3); color: var(--color-slate);">
<?php echo nl2br(esc_html($counsel_body)); ?>
</p>
</div>
<?php endif; ?>
</div>

<?php if ($caveat) : ?>
<p class="small" style="margin-top: var(--space-6); color: var(--color-slate); font-style: italic;">
<?php echo esc_html($caveat); ?>
</p>
<?php endif; ?>
</div>
<?php endif; ?>
</div>
</article>

<?php endwhile;

get_footer();