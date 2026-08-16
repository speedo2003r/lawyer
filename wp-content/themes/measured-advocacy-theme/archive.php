<?php
/**
 * Template: Generic Archive
 *
 * @package MeasuredAdvocacy
 */

get_header();

$archive_title = '';
$archive_description = '';

if (is_category()) {
    $archive_title = single_cat_title('', false);
    $archive_description = category_description();
} elseif (is_tag()) {
    $archive_title = single_tag_title('', false);
    $archive_description = tag_description();
} elseif (is_author()) {
    $archive_title = get_the_author();
    $archive_description = get_the_author_meta('description');
} elseif (is_date()) {
    if (is_year()) {
        $archive_title = get_the_date('Y');
    } elseif (is_month()) {
        $archive_title = get_the_date('F Y');
    } else {
        $archive_title = get_the_date();
    }
} else {
    $archive_title = get_the_archive_title();
    $archive_description = get_the_archive_description();
}

ma_editorial_header(__('Archive', 'measured-advocacy'), $archive_title, $archive_description ?: __('Browse archived content.', 'measured-advocacy'));
?>

<section class="section surface-paper">
<div class="container">
<?php if (have_posts()) : ?>
<div class="archive-grid" style="display: grid; gap: var(--space-6);">
<?php
while (have_posts()) : the_post();
?>
<article class="archive-card" style="border-bottom: 1px solid var(--color-sage); padding-bottom: var(--space-5);">
<h2 class="h3">
<a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
</h2>
<div class="archive-card__meta" style="margin-top: var(--space-2);">
<time class="small" style="color: var(--color-slate);" datetime="<?php echo esc_attr(get_the_date('c')); ?>">
<?php echo esc_html(get_the_date('F j, Y')); ?>
</time>
</div>
<?php if (has_excerpt()) : ?>
<p class="body" style="margin-top: var(--space-3); color: var(--color-slate);">
<?php echo esc_html(get_the_excerpt()); ?>
</p>
<?php endif; ?>
<a href="<?php the_permalink(); ?>" class="btn btn--text" style="margin-top: var(--space-3);">
<?php esc_html_e('Read more →', 'measured-advocacy'); ?>
</a>
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
<p class="body-l"><?php esc_html_e('No content found in this archive.', 'measured-advocacy'); ?></p>
</div>
<?php endif; ?>
</div>
</section>

<?php get_footer();