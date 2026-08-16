<?php
/**
 * Template Name: Privacy Page
 * Description: Privacy Policy and Data Protection statement.
 *
 * @package MeasuredAdvocacy
 */

get_header();

$is_ar = 'ar' === ma_locale();

$kicker = $is_ar ? 'السياسات والامتثال' : __('Policies & Compliance', 'measured-advocacy');
$heading = $is_ar ? 'سياسة الخصوصية وحماية البيانات' : __('Privacy Policy & Data Protection', 'measured-advocacy');
$lead = $is_ar ? 'توضح هذه السياسة كيفية جمع البيانات واستخدامها ومعايير السرية المهنية لحماية خصوصية العملاء وزوار الموقع.' : __('This policy details how information is handled, processed, and safeguarded under stringent professional confidentiality standards.', 'measured-advocacy');

ma_editorial_header($kicker, $heading, $lead);
?>

<section class="section surface-paper">
    <div class="container" style="max-width: 800px;">
        <div class="content-prose" style="display: flex; flex-direction: column; gap: var(--space-8);">
            <?php if ($is_ar) : ?>
                <section>
                    <h2 class="h2">١. جمع واستخدام البيانات</h2>
                    <p class="body-l">نقوم بجمع البيانات الشخصية والمعلومات التي يقدمها العميل أو الزائر طواعية عبر نماذج الاتصال وطلب الاستشارة، بما يشمل: الاسم، الصفة المؤسسية، وسائل التواصل المباشرة، وتفاصيل المسألة القانونية المطروحة.</p>
                </section>

                <section>
                    <h2 class="h2">٢. الغرض من المعالجة والأساس النظامي</h2>
                    <p class="body-l">تُعالج البيانات حصراً لغرض تقييم الاستفسارات الأولية، والتحقق من خلو المسألة من تعارض المصالح (Conflict of Interest Check)، وتنسيق التواصل المهني مع المحامي المختص.</p>
                </section>

                <section>
                    <h2 class="h2">٣. سرية المعلومات وعدم الإفصاح</h2>
                    <p class="body-l">تخضع كافة الاستفسارات والمعلومات لالتزامات السرية المهنية المعمول بها في مهنة المحاماة، ولا يتم مشاركة أي بيانات مع أي أطراف ثالثة إلا بموجب موافقة صريحة أو امتثالاً لأمر قضائي واجب النفاذ.</p>
                </section>

                <section>
                    <h2 class="h2">٤. حقوق صاحب البيانات والتواصل</h2>
                    <p class="body-l">يحق لصاحب البيانات طلب الوصول إلى بياناته أو تصحيحها أو طلب حذفها عبر مراسلة مسؤول حماية البيانات على البريد: <a href="mailto:<?php echo esc_attr(ma_firm('ma_email')); ?>" class="ltr-isolate"><?php echo esc_html(ma_firm('ma_email')); ?></a>.</p>
                </section>
            <?php else : ?>
                <section>
                    <h2 class="h2">1. Information Collection</h2>
                    <p class="body-l">We collect personal information voluntarily submitted through our inquiry and consultation forms, including names, organizational affiliations, direct contact channels, and factual summaries of legal matters.</p>
                </section>

                <section>
                    <h2 class="h2">2. Purpose of Processing & Legal Basis</h2>
                    <p class="body-l">Data is processed exclusively to evaluate initial inquiries, perform conflict-of-interest checks, and coordinate professional communication with designated legal counsel.</p>
                </section>

                <section>
                    <h2 class="h2">3. Confidentiality & Non-Disclosure</h2>
                    <p class="body-l">All inquiries and data remain strictly protected under legal professional privilege standards. No information is disclosed to third parties without express written consent or pursuant to a binding judicial order.</p>
                </section>

                <section>
                    <h2 class="h2">4. Data Subject Rights & Contact</h2>
                    <p class="body-l">Data subjects have the right to access, rectify, or request erasure of their personal data by contacting our Data Protection Officer at: <a href="mailto:<?php echo esc_attr(ma_firm('ma_email')); ?>" class="ltr-isolate"><?php echo esc_html(ma_firm('ma_email')); ?></a>.</p>
                </section>
            <?php endif; ?>
        </div>
    </div>
</section>

<?php get_footer();