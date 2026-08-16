<?php
/**
 * Template Name: Accessibility Page
 * Description: Accessibility Statement and WCAG 2.2 AA Conformance.
 *
 * @package MeasuredAdvocacy
 */

get_header();

$is_ar = 'ar' === ma_locale();

$kicker = $is_ar ? 'إمكانية الوصول' : __('Accessibility', 'measured-advocacy');
$heading = $is_ar ? 'بيان إمكانية الوصول وسهولة الاستخدام' : __('Accessibility Statement', 'measured-advocacy');
$lead = $is_ar ? 'نلتزم بتوفير تجربة تصفح رقمية سلسة وشاملة تتوافق مع معايير الوصول العالمية WCAG 2.2 AA لكافة المستخدمين.' : __('We are committed to ensuring an inclusive, seamless digital experience conforming to WCAG 2.2 AA accessibility standards for all users.', 'measured-advocacy');

ma_editorial_header($kicker, $heading, $lead);
?>

<section class="section surface-paper">
    <div class="container" style="max-width: 800px;">
        <div class="content-prose" style="display: flex; flex-direction: column; gap: var(--space-8);">
            <?php if ($is_ar) : ?>
                <section>
                    <h2 class="h2">١. المعايير المتبعة</h2>
                    <p class="body-l">تم تصميم الموقع وبرمجته ليتوافق مع المستوى (AA) من إرشادات إمكانية الوصول إلى محتوى الويب (WCAG 2.2)، بما يضمن دعماً كاملاً لبرمجيات قراءة الشاشة، والتباين البصري المحكم، والتنقل الكامل عبر لوحة المفاتيح.</p>
                </section>

                <section>
                    <h2 class="h2">٢. ميزات إمكانية الوصول</h2>
                    <p class="body-l">يتضمن الموقع: روابط تخطي مباشرة إلى المحتوى، وتبايناً لونياً يتجاوز النسب القياسية، وفصلاً دقيقاً لاتجاه النصوص ثنائية اللغة (Bidi Isolation)، ودعماً كاملاً لتقليل الحركة (prefers-reduced-motion).</p>
                </section>

                <section>
                    <h2 class="h2">٣. التغذية الراجعة والملاحظات</h2>
                    <p class="body-l">إذا واجهت أي صعوبة في الوصول إلى أي جزء من الموقع، يسعدنا التواصل معنا عبر: <a href="mailto:<?php echo esc_attr(ma_firm('ma_email')); ?>" class="ltr-isolate"><?php echo esc_html(ma_firm('ma_email')); ?></a> وسنعمل على توفير المحتوى بالوسيلة الملائمة لك على الفور.</p>
                </section>
            <?php else : ?>
                <section>
                    <h2 class="h2">1. Conformance Standards</h2>
                    <p class="body-l">This website is architected and tested to meet Web Content Accessibility Guidelines (WCAG 2.2) Level AA, ensuring comprehensive screen reader compatibility, robust contrast ratios, and full keyboard navigability.</p>
                </section>

                <section>
                    <h2 class="h2">2. Key Accessibility Features</h2>
                    <p class="body-l">Key features include: direct skip-to-content links, enhanced color contrast exceeding minimum ratios, rigorous bidirectional text isolation (Bidi), and full support for reduced-motion user preferences.</p>
                </section>

                <section>
                    <h2 class="h2">3. Feedback & Assistance</h2>
                    <p class="body-l">If you experience any accessibility barriers, please contact us at: <a href="mailto:<?php echo esc_attr(ma_firm('ma_email')); ?>" class="ltr-isolate"><?php echo esc_html(ma_firm('ma_email')); ?></a> and we will promptly provide the required information through an accessible format.</p>
                </section>
            <?php endif; ?>
        </div>
    </div>
</section>

<?php get_footer();