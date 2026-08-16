<?php
/**
 * Template: Single Representative Matter
 *
 * @package MeasuredAdvocacy
 */

get_header();

while (have_posts()) : the_post();
    $kicker = ma_field(get_the_ID(), 'ma_kicker', __('Representative Matter', 'measured-advocacy'));
    $sector = ma_field(get_the_ID(), 'ma_sector');
    $challenge = ma_field(get_the_ID(), 'ma_challenge');
    $contribution = ma_field(get_the_ID(), 'ma_contribution');
    $caveat = ma_field(get_the_ID(), 'ma_caveat');

    ma_editorial_header($kicker, get_the_title(), $sector ? sprintf(__('Sector: %s', 'measured-advocacy'), $sector) : '');
?>

<article class="section surface-paper">
<div class="container">
<div class="grid">
<div style="grid-column: span 8;">
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

<?php if ($challenge || $contribution) : ?>
<div style="margin-top: var(--space-8); padding-top: var(--space-6); border-top: 1px solid var(--color-sage);">
<?php if ($challenge) : ?>
<div style="margin-bottom: var(--space-6);">
<h2 class="h3"><?php esc_html_e('The Challenge', 'measured-advocacy'); ?></h2>
<p class="body-l" style="margin-top: var(--space-3); color: var(--color-slate);">
<?php echo nl2br(esc_html($challenge)); ?>
</p>
</div>
<?php endif; ?>

<?php if ($contribution) : ?>
<div style="margin-bottom: var(--space-6);">
<h2 class="h3"><?php esc_html_e('Counsel Contribution', 'measured-advocacy'); ?></h2>
<p class="body-l" style="margin-top: var(--space-3); color: var(--color-slate);">
<?php echo nl2br(esc_html($contribution)); ?>
</p>
</div>
<?php endif; ?>
</div>
<?php endif; ?>

<?php if ($caveat) : ?>
<div style="margin-top: var(--space-6); padding: var(--space-5); background: var(--color-limestone); border-radius: var(--radius-md);">
<p class="small" style="color: var(--color-slate); font-style: italic;">
<?php echo esc_html($caveat); ?>
</p>
</div>
<?php endif; ?>
</div>

<aside style="grid-column: span 4;">
<div style="position: sticky; top: var(--space-6);">
<div style="background: var(--color-limestone); padding: var(--space-5); border-radius: var(--radius-md);">
<h3 class="h3" style="margin-bottom: var(--space-4);"><?php esc_html_e('Discuss a similar matter', 'measured-advocacy'); ?></h3>
<p class="body" style="margin-bottom: var(--space-4); color: var(--color-slate);">
<?php esc_html_e('If you face comparable circumstances, we can assess the application of relevant expertise to your situation.', 'measured-advocacy'); ?>
</p>
<a href="<?php echo esc_url(home_url('/consultation')); ?>" class="btn btn--primary" style="width: 100%; text-align: center;">
<?php esc_html_e('Request Consultation', 'measured-advocacy'); ?>
</a>
</div>
</div>
</aside>
</div>
</div>
</article>

<?php endwhile;

get_footer();