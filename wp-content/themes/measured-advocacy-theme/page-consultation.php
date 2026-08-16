<?php
/**
 * Template Name: Consultation Page
 * Description: Interactive Confidential Legal Consultation Request Form.
 *
 * @package MeasuredAdvocacy
 */

get_header();

$is_ar = 'ar' === ma_locale();

$kicker = $is_ar ? 'الاستشارة وبدء العمل' : __('Consultation & Intake', 'measured-advocacy');
$heading = $is_ar ? 'طلب استشارة قانونية سرية' : __('Request a Confidential Legal Consultation', 'measured-advocacy');
$lead = $is_ar ? 'نستقبل طلبات الاستشارة من مسؤولي الشركات والكيانات الاستثمارية والأفراد لتقييم الموقف القانوني وإجراء فحص تعارض المصالح الأولي.' : __('We accept consultation requests from corporate executives, investors, and individuals to assess legal position and perform preliminary conflict checks.', 'measured-advocacy');

ma_editorial_header($kicker, $heading, $lead);
?>

<section class="section surface-paper">
    <div class="container">
        <div class="grid" style="gap: var(--space-8); align-items: start;">
            <!-- Form Column -->
            <div style="grid-column: span 8;">
                <form id="consultation-form" class="consultation-form surface-limestone" style="padding: var(--space-8); border-radius: var(--radius-standard); border: 1px solid var(--color-slate-35);" method="post" action="#" novalidate>
                    <!-- Accessible Error Summary -->
                    <div id="error-summary" style="display: none; background-color: #FDF3F2; padding: var(--space-5); border-radius: var(--radius-standard); border: 1px solid var(--color-error); margin-bottom: var(--space-6);" tabindex="-1" role="alert">
                        <h2 class="h3" style="color: var(--color-error); margin-bottom: var(--space-2);">
                            <?php echo $is_ar ? 'يرجى مراجعة البيانات التالية وتصحيحها:' : __('Please review and correct the following information:', 'measured-advocacy'); ?>
                        </h2>
                        <ul id="error-list" class="small" style="color: var(--color-error); padding-inline-start: var(--space-4);"></ul>
                    </div>

                    <div id="form-success-msg" style="display: none; background-color: #EBF3ED; padding: var(--space-6); border-radius: var(--radius-standard); border: 1px solid var(--color-success); margin-bottom: var(--space-6);" tabindex="-1">
                        <h2 class="h3" style="color: var(--color-ink); margin-bottom: var(--space-2);">
                            <?php echo $is_ar ? 'تم استلام طلب الاستشارة بنجاح' : __('Consultation Request Received', 'measured-advocacy'); ?>
                        </h2>
                        <p class="body" style="color: var(--color-slate);">
                            <?php echo $is_ar ? 'شكراً لتواصلك. سيتم إجراء فحص تعارض المصالح والتواصل معك عبر الوسيلة المحددة في أقرب وقت.' : __('Thank you for contacting us. Our team will review the inquiry and reach out shortly.', 'measured-advocacy'); ?>
                        </p>
                    </div>

                    <!-- Client Identification -->
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: var(--space-5); margin-bottom: var(--space-5);">
                        <div>
                            <label for="fullName" class="small" style="display: block; font-weight: 600; margin-bottom: var(--space-2); color: var(--color-ink);">
                                <?php echo $is_ar ? 'الاسم الكامل' : __('Full Name', 'measured-advocacy'); ?> <span style="color: var(--color-copper);">*</span>
                            </label>
                            <input type="text" id="fullName" name="fullName" required style="width: 100%; padding: var(--space-3) var(--space-4); background-color: var(--color-white); border: 1px solid var(--color-slate-35); border-radius: var(--radius-standard); color: var(--color-ink);" />
                            <span class="field-error small" id="err-fullName" style="color: var(--color-error); display: none; margin-top: 4px;">
                                <?php echo $is_ar ? 'الاسم الكامل مطلوب.' : __('Full name is required.', 'measured-advocacy'); ?>
                            </span>
                        </div>

                        <div>
                            <label for="organization" class="small" style="display: block; font-weight: 600; margin-bottom: var(--space-2); color: var(--color-ink);">
                                <?php echo $is_ar ? 'الصفة أو اسم الشركة / الكيان' : __('Organization / Entity Name', 'measured-advocacy'); ?>
                            </label>
                            <input type="text" id="organization" name="organization" style="width: 100%; padding: var(--space-3) var(--space-4); background-color: var(--color-white); border: 1px solid var(--color-slate-35); border-radius: var(--radius-standard); color: var(--color-ink);" />
                        </div>
                    </div>

                    <!-- Direct Contact -->
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: var(--space-5); margin-bottom: var(--space-5);">
                        <div>
                            <label for="email" class="small" style="display: block; font-weight: 600; margin-bottom: var(--space-2); color: var(--color-ink);">
                                <?php echo $is_ar ? 'البريد الإلكتروني' : __('Email Address', 'measured-advocacy'); ?> <span style="color: var(--color-copper);">*</span>
                            </label>
                            <input type="email" id="email" name="email" required class="ltr-isolate" style="width: 100%; padding: var(--space-3) var(--space-4); background-color: var(--color-white); border: 1px solid var(--color-slate-35); border-radius: var(--radius-standard); color: var(--color-ink);" />
                            <span class="field-error small" id="err-email" style="color: var(--color-error); display: none; margin-top: 4px;">
                                <?php echo $is_ar ? 'البريد الإلكتروني غير صحيح.' : __('A valid email address is required.', 'measured-advocacy'); ?>
                            </span>
                        </div>

                        <div>
                            <label for="phone" class="small" style="display: block; font-weight: 600; margin-bottom: var(--space-2); color: var(--color-ink);">
                                <?php echo $is_ar ? 'رقم الهاتف للتواصل' : __('Contact Telephone', 'measured-advocacy'); ?> <span style="color: var(--color-copper);">*</span>
                            </label>
                            <input type="tel" id="phone" name="phone" required class="ltr-isolate" style="width: 100%; padding: var(--space-3) var(--space-4); background-color: var(--color-white); border: 1px solid var(--color-slate-35); border-radius: var(--radius-standard); color: var(--color-ink);" />
                            <span class="field-error small" id="err-phone" style="color: var(--color-error); display: none; margin-top: 4px;">
                                <?php echo $is_ar ? 'رقم الهاتف للتواصل مطلوب.' : __('A valid telephone number is required.', 'measured-advocacy'); ?>
                            </span>
                        </div>
                    </div>

                    <!-- Practice Area Selection -->
                    <div style="margin-bottom: var(--space-5);">
                        <label for="inquiryType" class="small" style="display: block; font-weight: 600; margin-bottom: var(--space-2); color: var(--color-ink);">
                            <?php echo $is_ar ? 'تصنيف المسألة / مجال الممارسة' : __('Practice Area Classification', 'measured-advocacy'); ?> <span style="color: var(--color-copper);">*</span>
                        </label>
                        <select id="inquiryType" name="inquiryType" required style="width: 100%; padding: var(--space-3) var(--space-4); background-color: var(--color-white); border: 1px solid var(--color-slate-35); border-radius: var(--radius-standard); color: var(--color-ink);">
                            <option value=""><?php echo $is_ar ? '— يرجى تحديد مجال الممارسة الأنسب —' : __('— Select the applicable practice area —', 'measured-advocacy'); ?></option>
                            <option value="corporate"><?php echo $is_ar ? 'الاستشارات المؤسسية والاندماج والاستحواذ' : 'Corporate Advisory & M&A'; ?></option>
                            <option value="litigation"><?php echo $is_ar ? 'تسوية النزاعات والتقاضي والتحكيم' : 'Dispute Resolution & Litigation'; ?></option>
                            <option value="real-estate"><?php echo $is_ar ? 'العقارات والإنشاءات والبنية التحتية' : 'Real Estate & Construction'; ?></option>
                            <option value="employment"><?php echo $is_ar ? 'العمل وحوكمة القيادات التنفيذية' : 'Employment & Executive Governance'; ?></option>
                            <option value="family-law"><?php echo $is_ar ? 'قانون الأسرة وهيكلة الثروات الخاصة' : 'Family Law & Private Wealth'; ?></option>
                            <option value="criminal-defense"><?php echo $is_ar ? 'الدفاع في الجرائم المالية وجرائم الأعمال' : 'White-Collar & Financial Criminal Defense'; ?></option>
                            <option value="intellectual-property"><?php echo $is_ar ? 'الملكية الفكرية وبراءات الاختراع' : 'Intellectual Property & Patents'; ?></option>
                            <option value="other"><?php echo $is_ar ? 'مسألة أخرى / متعددة التخصصات' : 'Other / Multi-disciplinary Matter'; ?></option>
                        </select>
                        <span class="field-error small" id="err-inquiryType" style="color: var(--color-error); display: none; margin-top: 4px;">
                            <?php echo $is_ar ? 'تصنيف المسألة مطلوب.' : __('Practice area classification is required.', 'measured-advocacy'); ?>
                        </span>
                    </div>

                    <!-- Matter Summary -->
                    <div style="margin-bottom: var(--space-5);">
                        <label for="message" class="small" style="display: block; font-weight: 600; margin-bottom: var(--space-2); color: var(--color-ink);">
                            <?php echo $is_ar ? 'ملخص المسألة والأطراف المعنية' : __('Matter Summary & Background', 'measured-advocacy'); ?> <span style="color: var(--color-copper);">*</span>
                        </label>
                        <textarea id="message" name="message" rows="5" required style="width: 100%; padding: var(--space-3) var(--space-4); background-color: var(--color-white); border: 1px solid var(--color-slate-35); border-radius: var(--radius-standard); color: var(--color-ink); font-family: inherit;"></textarea>
                        <span class="field-error small" id="err-message" style="color: var(--color-error); display: none; margin-top: 4px;">
                            <?php echo $is_ar ? 'ملخص المسألة مطلوب.' : __('A brief summary of the matter is required.', 'measured-advocacy'); ?>
                        </span>
                    </div>

                    <!-- Consent Checkbox -->
                    <div style="margin-bottom: var(--space-6);">
                        <label style="display: flex; align-items: flex-start; gap: var(--space-3); cursor: pointer;" class="small">
                            <input type="checkbox" id="consent" name="consent" required style="margin-top: 3px;" />
                            <span style="color: var(--color-slate);">
                                <?php echo $is_ar ? 'أقر بأن إرسال هذا الطلب يخضع لإجراءات فحص تعارض المصالح والسرية المهنية، ولا يُنشئ علاقة توكيل قانوني إلا بعد توقيع اتفاقية الأتعاب الرسمية.' : __('I acknowledge that this inquiry is subject to conflict-of-interest screening and confidentiality standards, and does not create an attorney-client relationship until a formal engagement agreement is executed.', 'measured-advocacy'); ?>
                            </span>
                        </label>
                        <span class="field-error small" id="err-consent" style="color: var(--color-error); display: none; margin-top: 4px;">
                            <?php echo $is_ar ? 'يجب الموافقة على الإقرار القانوني.' : __('Consent acknowledgement is required.', 'measured-advocacy'); ?>
                        </span>
                    </div>

                    <button type="submit" class="btn btn--primary" style="width: 100%; justify-content: center;">
                        <?php echo $is_ar ? 'إرسال طلب الاستشارة' : __('Submit Consultation Request', 'measured-advocacy'); ?>
                    </button>
                </form>
            </div>

            <!-- Direct Alternatives Sidebar -->
            <div style="grid-column: span 4;">
                <div class="surface-limestone" style="padding: var(--space-6); border-radius: var(--radius-standard); border: 1px solid var(--color-slate-35);">
                    <h3 class="h3" style="margin-bottom: var(--space-3);">
                        <?php echo $is_ar ? 'قنوات الاتصال المباشر' : __('Direct Channels', 'measured-advocacy'); ?>
                    </h3>
                    <p class="body" style="color: var(--color-slate); margin-bottom: var(--space-4);">
                        <?php echo $is_ar ? 'إذا كانت مسألتك طارئة أو تتطلب تنسيقاً فورياً:' : __('If your matter requires immediate operational contact:', 'measured-advocacy'); ?>
                    </p>
                    <div style="display: flex; flex-direction: column; gap: var(--space-3);">
                        <a href="<?php echo esc_attr(ma_phone_href()); ?>" class="btn btn--secondary" style="justify-content: center; width: 100%;">
                            <?php echo $is_ar ? 'الاتصال المباشر:' : 'Direct Call:'; ?> <span class="ltr-isolate" style="margin-inline-start: var(--space-2);"><?php echo esc_html(ma_firm('ma_phone')); ?></span>
                        </a>
                        <a href="mailto:<?php echo esc_attr(ma_firm('ma_email')); ?>" class="btn btn--secondary" style="justify-content: center; width: 100%;">
                            <?php echo $is_ar ? 'المراسلة:' : 'Email:'; ?> <?php echo esc_html(ma_firm('ma_email')); ?>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var form = document.getElementById('consultation-form');
    var summary = document.getElementById('error-summary');
    var errorList = document.getElementById('error-list');
    var successMsg = document.getElementById('form-success-msg');

    if (form) {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            var isValid = true;
            var errors = [];

            // Name
            var nameInput = document.getElementById('fullName');
            var errName = document.getElementById('err-fullName');
            if (!nameInput.value.trim()) {
                errName.style.display = 'block';
                errors.push('<?php echo $is_ar ? "الاسم الكامل مطلوب." : "Full name is required."; ?>');
                isValid = false;
            } else {
                errName.style.display = 'none';
            }

            // Email
            var emailInput = document.getElementById('email');
            var errEmail = document.getElementById('err-email');
            var emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(emailInput.value.trim())) {
                errEmail.style.display = 'block';
                errors.push('<?php echo $is_ar ? "البريد الإلكتروني غير صحيح." : "A valid email address is required."; ?>');
                isValid = false;
            } else {
                errEmail.style.display = 'none';
            }

            // Phone
            var phoneInput = document.getElementById('phone');
            var errPhone = document.getElementById('err-phone');
            if (phoneInput.value.trim().length < 7) {
                errPhone.style.display = 'block';
                errors.push('<?php echo $is_ar ? "رقم الهاتف للتواصل مطلوب." : "A valid telephone number is required."; ?>');
                isValid = false;
            } else {
                errPhone.style.display = 'none';
            }

            // Inquiry Type
            var typeSelect = document.getElementById('inquiryType');
            var errType = document.getElementById('err-inquiryType');
            if (!typeSelect.value) {
                errType.style.display = 'block';
                errors.push('<?php echo $is_ar ? "تصنيف المسألة مطلوب." : "Practice area classification is required."; ?>');
                isValid = false;
            } else {
                errType.style.display = 'none';
            }

            // Message
            var msgInput = document.getElementById('message');
            var errMessage = document.getElementById('err-message');
            if (!msgInput.value.trim()) {
                errMessage.style.display = 'block';
                errors.push('<?php echo $is_ar ? "ملخص المسألة مطلوب." : "A brief summary of the matter is required."; ?>');
                isValid = false;
            } else {
                errMessage.style.display = 'none';
            }

            // Consent
            var consentInput = document.getElementById('consent');
            var errConsent = document.getElementById('err-consent');
            if (!consentInput.checked) {
                errConsent.style.display = 'block';
                errors.push('<?php echo $is_ar ? "يجب الموافقة على الإقرار القانوني." : "Consent acknowledgement is required."; ?>');
                isValid = false;
            } else {
                errConsent.style.display = 'none';
            }

            if (!isValid) {
                errorList.innerHTML = errors.map(function(err) { return '<li>' + err + '</li>'; }).join('');
                summary.style.display = 'block';
                summary.scrollIntoView({ behavior: 'smooth' });
            } else {
                summary.style.display = 'none';
                form.style.display = 'none';
                successMsg.style.display = 'block';
                successMsg.scrollIntoView({ behavior: 'smooth' });
            }
        });
    }
});
</script>

<?php get_footer();