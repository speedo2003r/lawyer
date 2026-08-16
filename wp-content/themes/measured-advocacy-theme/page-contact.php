<?php
/**
 * Template Name: Contact Page
 * Description: Contact and Headquarters page with official communication channels.
 *
 * @package MeasuredAdvocacy
 */

get_header();

$is_ar = 'ar' === ma_locale();

$kicker = $is_ar ? 'التواصل ومقر المكتب' : __('Contact & Office', 'measured-advocacy');
$heading = $is_ar ? 'تواصل مهني موثوق وقنوات اتصال مباشرة' : __('Direct Communications and Headquarters Access', 'measured-advocacy');
$lead = $is_ar ? 'نرحب بالتواصل مع ممثلي الشركات والكيانات الاستثمارية والأفراد عبر القنوات الرسمية المعتمدة للمكتب.' : __('We welcome inquiries from corporate executives, investors, and individuals through our verified official communication channels.', 'measured-advocacy');

ma_editorial_header($kicker, $heading, $lead);
?>

<section class="section surface-paper">
    <div class="container">
        <div class="grid" style="gap: var(--space-8); align-items: stretch;">
            <!-- Direct Channels -->
            <div style="grid-column: span 7;">
                <h2 class="h2" style="margin-bottom: var(--space-6);">
                    <?php echo $is_ar ? 'قنوات التواصل المباشرة' : __('Official Channels', 'measured-advocacy'); ?>
                </h2>

                <div style="display: flex; flex-direction: column; gap: var(--space-4);">
                    <div class="channel-card surface-limestone" style="padding: var(--space-6); border-radius: var(--radius-standard); border: 1px solid var(--color-slate-35);">
                        <span class="small" style="color: var(--color-copper); font-weight: 600;">
                            <?php echo $is_ar ? 'الاتصال الهاتفي' : __('Telephone Inquiries', 'measured-advocacy'); ?>
                        </span>
                        <p class="body-l" style="margin-top: var(--space-2);">
                            <a href="<?php echo esc_attr(ma_phone_href()); ?>" class="ltr-isolate" style="font-weight: 600; color: var(--color-ink); text-decoration: none;">
                                <?php echo esc_html(ma_firm('ma_phone')); ?>
                            </a>
                        </p>
                        <p class="small" style="color: var(--color-slate); margin-top: var(--space-1);">
                            <?php echo esc_html(ma_firm('ma_hours')); ?>
                        </p>
                    </div>

                    <div class="channel-card surface-limestone" style="padding: var(--space-6); border-radius: var(--radius-standard); border: 1px solid var(--color-slate-35);">
                        <span class="small" style="color: var(--color-copper); font-weight: 600;">
                            <?php echo $is_ar ? 'المراسلات الرسمية' : __('Electronic Correspondence', 'measured-advocacy'); ?>
                        </span>
                        <p class="body-l" style="margin-top: var(--space-2);">
                            <a href="mailto:<?php echo esc_attr(ma_firm('ma_email')); ?>" class="ltr-isolate" style="font-weight: 600; color: var(--color-ink); text-decoration: none;">
                                <?php echo esc_html(ma_firm('ma_email')); ?>
                            </a>
                        </p>
                        <p class="small" style="color: var(--color-slate); margin-top: var(--space-1);">
                            <?php echo $is_ar ? 'يتم الرد خلال ساعات العمل الرسمية' : __('Response provided during standard business hours', 'measured-advocacy'); ?>
                        </p>
                    </div>

                    <div class="channel-card surface-limestone" style="padding: var(--space-6); border-radius: var(--radius-standard); border: 1px solid var(--color-slate-35);">
                        <span class="small" style="color: var(--color-copper); font-weight: 600;">
                            <?php echo $is_ar ? 'المقر الرئيسي والعنوان' : __('Headquarters & Physical Address', 'measured-advocacy'); ?>
                        </span>
                        <p class="body" style="margin-top: var(--space-2); color: var(--color-ink); font-weight: 500; white-space: pre-line;">
                            <?php echo esc_html(ma_firm('ma_address')); ?>
                        </p>
                        <p class="small" style="color: var(--color-slate); margin-top: var(--space-2);">
                            <?php echo $is_ar ? 'الاستقبال المكتبي يتطلب موعداً مسبقاً لضمان الخصوصية والجاهزية.' : __('Office visits require a prior appointment to ensure confidentiality and preparation.', 'measured-advocacy'); ?>
                        </p>
                    </div>
                </div>
            </div>

            <!-- Route to Formal Consultation -->
            <div style="grid-column: span 5;">
                <div class="consultation-promo-card surface-ink" style="background-color: var(--color-ink); color: var(--color-white); padding: var(--space-8); border-radius: var(--radius-standard); height: 100%; display: flex; flex-direction: column; justify-content: space-between;">
                    <div>
                        <span class="small" style="color: var(--color-copper); font-weight: 600;">
                            <?php echo $is_ar ? 'مسألة جديدة؟' : __('New Matter?', 'measured-advocacy'); ?>
                        </span>
                        <h3 class="h2" style="color: var(--color-limestone); margin-top: var(--space-3); margin-bottom: var(--space-4);">
                            <?php echo $is_ar ? 'طلب استشارة قانونية سرية' : __('Request a Confidential Legal Consultation', 'measured-advocacy'); ?>
                        </h3>
                        <p class="body" style="color: rgba(255, 255, 255, 0.75); line-height: 1.7;">
                            <?php echo $is_ar ? 'إذا كنت ترغب في مناقشة موضوع نزاع، أو إعادة هيكلة، أو استشارة استراتيجية، نفضل استخدام نموذج الاستشارة المخصص لضمان السرية والتحقق الفوري من عدم تعارض المصالح.' : __('If you are facing a potential dispute, corporate restructuring, or require strategic legal counsel, we recommend using our dedicated consultation portal for immediate conflict-of-interest screening.', 'measured-advocacy'); ?>
                        </p>
                    </div>
                    <div style="margin-top: var(--space-7);">
                        <a href="<?php echo esc_url(home_url('/consultation')); ?>" class="btn btn--primary" style="width: 100%; justify-content: center; text-align: center;">
                            <?php echo $is_ar ? 'الانتقال إلى نموذج الاستشارة ←' : __('Proceed to Consultation Portal →', 'measured-advocacy'); ?>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php get_footer();