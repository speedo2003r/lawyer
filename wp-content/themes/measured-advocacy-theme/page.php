<?php
/**
 * Template: Default Page
 *
 * @package MeasuredAdvocacy
 */

get_header();

while (have_posts()) : the_post();
    ma_editorial_header('Page', get_the_title(), ma_excerpt_or_field($post, 'An overview of this section.'));
?>

<section class="section surface-paper">
<div class="container">
<div class="content-prose">
<?php the_content(); ?>
</div>
</div>
</section>

<?php endwhile;

get_footer();
?>
