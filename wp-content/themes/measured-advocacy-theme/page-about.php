<?php
/**
 * Template Name: About Page
 * Description: Detailed About the Firm page with method, essay, factual record, and next paths.
 *
 * @package MeasuredAdvocacy
 */

get_header();

$is_ar = 'ar' === ma_locale();

$kicker = $is_ar ? 'عن المكتب وفلسفة العمل' : __('About the Firm', 'measured-advocacy');
$heading = $is_ar ? 'المشورة القانونية حين تكون بوصلة للقرارات المصيرية' : __('Legal Counsel as a Framework for Consequential Decisions', 'measured-advocacy');
$lead = $is_ar ? 'تأسس المكتب لتقديم نموذج مختلف في ممارسة المحاماة: يجمع بين الرصانة الفقهية والنظامية والعمق الاستراتيجي في إدارة التحولات والنزاعات المعقدة.' : __('Founded to provide a distinct model of legal practice: combining jurisprudential rigor with strategic depth in managing transitions and complex disputes.', 'measured-advocacy');

ma_editorial_header($kicker, $heading, $lead);
?>

<section class="about-essay section surface-paper">
    <div class="container">
        <div class="grid" style="align-items: start; gap: var(--space-8);">
            <div style="grid-column: span 7;">
                <h2 class="h2" style="margin-bottom: var(--space-5);">
                    <?php echo $is_ar ? 'المسؤولية قبل الإجراءات، والعمق قبل الحل السريع' : __('Accountability Over Procedure, Depth Over Quick Fixes', 'measured-advocacy'); ?>
                </h2>
                <div class="body-l" style="color: var(--color-slate); display: flex; flex-direction: column; gap: var(--space-5);">
                    <?php if ($is_ar) : ?>
                        <p>في عالم تتسارع فيه وتيرة التحولات الاقتصادية والتنظيمية، لم يعد كافياً تقديم إجابات قانونية مجردة عن الواقع التجاري والإنساني للعميل. نؤمن في المكتب بأن القيمة الحقيقية للمحامي تكمن في قدرته على استشراف الأبعاد وتأطير المسألة بأبعادها الحقيقية قبل المضي في أي مسار تنفيذي.</p>
                        <p>نعمل مع نخبة من الشركات الرائدة، والكيانات العائلية الكبرى، والمستثمرين، وأصحاب المصالح الحيوية الذين يواجهون منعطفات دقيقة تتطلب أعلى درجات السرية والاحترافية. كل ملف نتولاه يخضع لإشراف مباشر من الشركاء، مع التزام تام بالشفافية والتقييم الأمين لفرص النجاح والمخاطر المحتملة.</p>
                    <?php else : ?>
                        <p>In an era of accelerating regulatory and economic transformation, legal answers isolated from commercial and human realities are insufficient. We believe the true value of senior counsel lies in anticipating consequences and properly framing the matter before committing to an executive path.</p>
                        <p>We advise market-leading enterprises, prominent family groups, and investors facing inflection points that demand absolute discretion and precision. Every mandate is led directly by partners, underpinned by an unwavering commitment to intellectual honesty and realistic risk assessment.</p>
                    <?php endif; ?>
                </div>
            </div>

            <div style="grid-column: span 5;">
                <div class="about-image-card">
                    <img
                        src="<?php echo esc_url(ma_img('office/img-002-approach.jpg')); ?>"
                        alt="<?php echo esc_attr($is_ar ? 'جلسة عمل استراتيجية ونقاش عميق بين الشركاء القانونيين' : 'Senior counsel engaged in strategic session and document analysis'); ?>"
                        width="1800"
                        height="1200"
                        loading="lazy"
                        style="width: 100%; height: auto; border-radius: var(--radius-md); object-fit: cover; aspect-ratio: 4/3;"
                    />
                    <p class="small" style="margin-top: var(--space-3); color: var(--color-slate);">
                        <?php echo $is_ar ? 'فريق العمل خلال مداولة قانونية داخل المكتب — التحليل المستفيض يسبق كل خطوة إجرائية.' : 'Counsel in strategy review — rigorous analysis precedes every procedural step.'; ?>
                    </p>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="firm-record section surface-limestone">
    <div class="container">
        <div class="grid" style="gap: var(--space-8);">
            <div style="grid-column: span 4;">
                <h2 class="h3" style="margin-bottom: var(--space-3);">
                    <?php echo $is_ar ? 'حقائق ومعايير معتمدة' : __('Verified Firm Record', 'measured-advocacy'); ?>
                </h2>
                <p class="body" style="color: var(--color-slate);">
                    <?php echo $is_ar ? 'سجل مهني قائم على الشفافية والمسؤولية.' : __('An institutional foundation built on transparency and accountability.', 'measured-advocacy'); ?>
                </p>
            </div>
            <div style="grid-column: span 8;">
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: var(--space-6);">
                    <div style="border-bottom: 1px solid var(--color-sage); padding-bottom: var(--space-4);">
                        <span class="small" style="display: block; color: var(--color-copper); font-weight: 600; margin-bottom: var(--space-1);">
                            <?php echo $is_ar ? 'المقر الرئيسي' : __('Headquarters', 'measured-advocacy'); ?>
                        </span>
                        <span class="body-l" style="font-weight: 500;">
                            <?php echo esc_html(ma_firm('ma_address')); ?>
                        </span>
                    </div>
                    <div style="border-bottom: 1px solid var(--color-sage); padding-bottom: var(--space-4);">
                        <span class="small" style="display: block; color: var(--color-copper); font-weight: 600; margin-bottom: var(--space-1);">
                            <?php echo $is_ar ? 'نطاق الممارسة' : __('Jurisdictions', 'measured-advocacy'); ?>
                        </span>
                        <span class="body-l" style="font-weight: 500;">
                            <?php echo $is_ar ? 'المحاكم وهيئات التحكيم في مصر والمنطقة العربية' : __('Courts & Arbitration Panels across MENA & International', 'measured-advocacy'); ?>
                        </span>
                    </div>
                    <div style="border-bottom: 1px solid var(--color-sage); padding-bottom: var(--space-4);">
                        <span class="small" style="display: block; color: var(--color-copper); font-weight: 600; margin-bottom: var(--space-1);">
                            <?php echo $is_ar ? 'لغات العمل' : __('Working Languages', 'measured-advocacy'); ?>
                        </span>
                        <span class="body-l" style="font-weight: 500;">
                            <?php echo $is_ar ? 'العربية والإنجليزية' : __('Arabic and English', 'measured-advocacy'); ?>
                        </span>
                    </div>
                    <div style="border-bottom: 1px solid var(--color-sage); padding-bottom: var(--space-4);">
                        <span class="small" style="display: block; color: var(--color-copper); font-weight: 600; margin-bottom: var(--space-1);">
                            <?php echo $is_ar ? 'الاعتمادات والقيد' : __('Accreditations', 'measured-advocacy'); ?>
                        </span>
                        <span class="body-l" style="font-weight: 500;">
                            <?php echo $is_ar ? 'نقابة المحامين ومحاكم الاستئناف ومراكز التحكيم المعتمدة' : __('Bar Association & Certified Arbitration Centers', 'measured-advocacy'); ?>
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="about-next section surface-paper">
    <div class="container" style="text-align: center; max-width: 680px;">
        <h2 class="h2" style="margin-bottom: var(--space-4);">
            <?php echo $is_ar ? 'تعرف على الفريق وناقش مسألتك' : __('Meet the Team or Frame Your Matter', 'measured-advocacy'); ?>
        </h2>
        <p class="body-l" style="color: var(--color-slate); margin-bottom: var(--space-6);">
            <?php echo $is_ar ? 'يقود ممارساتنا نخبة من المحامين والمستشارين ذوي السيرة المهنية الراسخة.' : __('Our practices are led by seasoned attorneys with verifiable track records.', 'measured-advocacy'); ?>
        </p>
        <div style="display: flex; gap: var(--space-4); justify-content: center; flex-wrap: wrap;">
            <a href="<?php echo esc_url(home_url('/people')); ?>" class="btn btn--primary">
                <?php echo $is_ar ? 'فريق العمل والمحامون' : __('Our People', 'measured-advocacy'); ?>
            </a>
            <a href="<?php echo esc_url(home_url('/consultation')); ?>" class="btn btn--secondary">
                <?php echo $is_ar ? 'طلب استشارة أولية' : __('Request Consultation', 'measured-advocacy'); ?>
            </a>
        </div>
    </div>
</section>

<?php get_footer();