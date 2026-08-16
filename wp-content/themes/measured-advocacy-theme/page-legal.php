<?php
/**
 * Template Name: Legal Notice Page
 * Description: Legal Notice & Disclaimer.
 *
 * @package MeasuredAdvocacy
 */

get_header();

$is_ar = 'ar' === ma_locale();

$kicker = $is_ar ? 'الإشعار القانوني' : __('Legal Notice', 'measured-advocacy');
$heading = $is_ar ? 'الإشعار القانوني وإخلاء المسؤولية' : __('Legal Notice & Disclaimer', 'measured-advocacy');
$lead = $is_ar ? 'المعلومات المنشورة على هذا الموقع مخصصة للأغراض التعريفية والعامة فقط، ولا تشكل مشورة قانونية ملزمة.' : __('The information published on this website is for general informational purposes only and does not constitute formal legal advice.', 'measured-advocacy');

ma_editorial_header($kicker, $heading, $lead);
?>

<section class="section surface-paper">
    <div class="container" style="max-width: 800px;">
        <div class="content-prose" style="display: flex; flex-direction: column; gap: var(--space-8);">
            <?php if ($is_ar) : ?>
                <section>
                    <h2 class="h2">١. عدم قيام علاقة محامٍ وموكل</h2>
                    <p class="body-l">لا ينشئ تصفح هذا الموقع أو إرسال استفسار عبر نموذج الاستشارة أي علاقة توكيل قانوني أو وكالة بين المرسل والمكتب، ولا تنشأ هذه العلاقة إلا بعد إبرام اتفاقية أتعاب ومصادقة رسمية.</p>
                </section>

                <section>
                    <h2 class="h2">٢. عدم تقديم استشارات عبر الموقع</h2>
                    <p class="body-l">المقالات والرؤى المنشورة تمثل تحليلات قانونية عامة ولا يجوز الاعتماد عليها كبديل عن الاستشارة المباشرة لمسألة واقعية محددة.</p>
                </section>

                <section>
                    <h2 class="h2">٣. حقوق الملكية الفكرية</h2>
                    <p class="body-l">كافة المحتويات، والنصوص، والشعارات، والتحليلات المنشورة هي ملك حصري للمكتب ومحمية بموجب أنظمة حماية حقوق المؤلف والعلامات التجارية.</p>
                </section>
            <?php else : ?>
                <section>
                    <h2 class="h2">1. No Attorney-Client Relationship</h2>
                    <p class="body-l">Browsing this website or submitting an inquiry via the consultation form does not create an attorney-client or fiduciary relationship. Such representation arises exclusively upon signing a formal engagement agreement.</p>
                </section>

                <section>
                    <h2 class="h2">2. No Direct Legal Advice</h2>
                    <p class="body-l">Articles and insights published herein reflect general legal analyses and must not be relied upon as a substitute for direct legal advice on specific factual circumstances.</p>
                </section>

                <section>
                    <h2 class="h2">3. Intellectual Property Rights</h2>
                    <p class="body-l">All contents, text, marks, and analytical publications are the exclusive property of the firm and are protected under copyright and trademark laws.</p>
                </section>
            <?php endif; ?>
        </div>
    </div>
</section>

<?php get_footer();