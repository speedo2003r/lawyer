<?php
/**
 * Template: Single Attorney
 *
 * @package MeasuredAdvocacy
 */

get_header();

while (have_posts()) : the_post();
    $role = ma_field(get_the_ID(), 'ma_role', __('Attorney', 'measured-advocacy'));
    $focus = ma_field(get_the_ID(), 'ma_focus');
    $admissions = ma_field(get_the_ID(), 'ma_admissions');
    $jurisdictions = ma_field(get_the_ID(), 'ma_jurisdictions');
    $languages = ma_field(get_the_ID(), 'ma_languages');
    $email = ma_field(get_the_ID(), 'ma_email');
    $phone = ma_field(get_the_ID(), 'ma_phone');

    ma_editorial_header(__('People', 'measured-advocacy'), get_the_title(), $role);
?>

<article class="section surface-paper">
<div class="container">
<div class="grid">
<div style="grid-column: span 7;">
<?php if ($focus) : ?>
<div style="margin-bottom: var(--space-6);">
<h2 class="h3"><?php esc_html_e('Focus', 'measured-advocacy'); ?></h2>
<p class="body-l" style="margin-top: var(--space-3); color: var(--color-slate);">
<?php echo nl2br(esc_html($focus)); ?>
</p>
</div>
<?php endif; ?>

<div class="content-prose">
<?php the_content(); ?>
</div>

<?php if ($admissions || $jurisdictions || $languages) : ?>
<div style="margin-top: var(--space-8); padding-top: var(--space-6); border-top: 1px solid var(--color-sage);">
<?php if ($admissions) : ?>
<div style="margin-bottom: var(--space-5);">
<h3 class="h3"><?php esc_html_e('Admissions', 'measured-advocacy'); ?></h3>
<p class="body" style="margin-top: var(--space-2); white-space: pre-line; color: var(--color-slate);">
<?php echo esc_html($admissions); ?>
</p>
</div>
<?php endif; ?>

<?php if ($jurisdictions) : ?>
<div style="margin-bottom: var(--space-5);">
<h3 class="h3"><?php esc_html_e('Jurisdictions', 'measured-advocacy'); ?></h3>
<p class="body" style="margin-top: var(--space-2); white-space: pre-line; color: var(--color-slate);">
<?php echo esc_html($jurisdictions); ?>
</p>
</div>
<?php endif; ?>

<?php if ($languages) : ?>
<div style="margin-bottom: var(--space-5);">
<h3 class="h3"><?php esc_html_e('Languages', 'measured-advocacy'); ?></h3>
<p class="body" style="margin-top: var(--space-2); white-space: pre-line; color: var(--color-slate);">
<?php echo esc_html($languages); ?>
</p>
</div>
<?php endif; ?>
</div>
<?php endif; ?>
</div>

<aside style="grid-column: span 5;">
<?php if (has_post_thumbnail()) : ?>
<div style="margin-bottom: var(--space-6);">
<?php the_post_thumbnail('large', array('style' => 'width: 100%; height: auto; border-radius: var(--radius-md);')); ?>
</div>
<?php else : ?>
<div style="margin-bottom: var(--space-6);">
<img src="<?php echo esc_url(ma_img('people/img-003-managing-partner.jpg')); ?>" alt="<?php the_title_attribute(); ?>" style="width: 100%; height: auto; border-radius: var(--radius-md);">
</div>
<?php endif; ?>

<?php if ($email || $phone) : ?>
<div class="contact-box" style="background: var(--color-limestone); padding: var(--space-5); border-radius: var(--radius-md);">
<h3 class="h3" style="margin-bottom: var(--space-4);"><?php esc_html_e('Contact', 'measured-advocacy'); ?></h3>
<?php if ($email) : ?>
<p class="body" style="margin-bottom: var(--space-3);">
<a href="mailto:<?php echo esc_attr($email); ?>" class="ltr-isolate">
<?php echo esc_html($email); ?>
</a>
</p>
<?php endif; ?>
<?php if ($phone) : ?>
<p class="body">
<a href="<?php echo esc_attr(ma_phone_href($phone)); ?>" class="ltr-isolate">
<?php echo esc_html($phone); ?>
</a>
</p>
<?php endif; ?>
</div>
<?php endif; ?>
</aside>
</div>
</div>
</article>

<?php endwhile;

get_footer();