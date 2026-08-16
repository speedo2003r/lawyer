<?php
/**
 * One-click demo content seeder.
 *
 * @package MeasuredAdvocacy
 */

if (!defined('ABSPATH')) {
    exit;
}

add_action('admin_menu', 'ma_demo_seeder_menu');
function ma_demo_seeder_menu(): void {
    add_theme_page(
        __('Demo Content Seeder', 'measured-advocacy'),
        __('Demo Content Seeder', 'measured-advocacy'),
        'manage_options',
        'ma-demo-seeder',
        'ma_demo_seeder_page'
    );
}

function ma_demo_seeder_page(): void {
    if (!current_user_can('manage_options')) {
        return;
    }
    $key = 'ma_demo_seed_notice_' . get_current_user_id();
    $notice = get_transient($key);
    delete_transient($key);
    ?>
    <div class='wrap'>
        <h1><?php esc_html_e('Measured Advocacy Demo Content', 'measured-advocacy'); ?></h1>
        <p><?php esc_html_e('Create linked English and Arabic content through WP Multilingual, plus menus, firm details, and all bundled demo images.', 'measured-advocacy'); ?></p>
        <?php if (is_array($notice)) : ?>
            <div class='notice notice-<?php echo empty($notice['errors']) ? 'success' : 'warning'; ?> is-dismissible'>
                <p><?php printf(
                    esc_html__('Finished: %1$d created, %2$d updated, %3$d preserved.', 'measured-advocacy'),
                    (int) $notice['created'],
                    (int) $notice['updated'],
                    (int) $notice['skipped']
                ); ?></p>
                <?php foreach ($notice['errors'] as $error) : ?>
                    <p><?php echo esc_html($error); ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
        <div class='card' style='max-width:720px;padding:24px;margin-top:20px;'>
            <h2 style='margin-top:0;'><?php esc_html_e('Install sample data', 'measured-advocacy'); ?></h2>
            <p><?php esc_html_e('Existing content is never deleted. Re-running refreshes seeded content.', 'measured-advocacy'); ?></p>
            <form method='post' action='<?php echo esc_url(admin_url('admin-post.php')); ?>'>
                <input type='hidden' name='action' value='ma_seed_demo_content'>
                <?php wp_nonce_field('ma_seed_demo_content', 'ma_demo_seed_nonce'); ?>
                <?php submit_button(__('Seed Demo Content', 'measured-advocacy'), 'primary large', 'submit', false); ?>
            </form>
        </div>
    </div>
    <?php
}

add_action('admin_post_ma_seed_demo_content', 'ma_demo_seed_handle');
function ma_demo_seed_handle(): void {
    if (!current_user_can('manage_options')) {
        wp_die(esc_html__('You are not allowed to seed demo content.', 'measured-advocacy'));
    }
    check_admin_referer('ma_seed_demo_content', 'ma_demo_seed_nonce');
    set_transient('ma_demo_seed_notice_' . get_current_user_id(), ma_demo_seed_content(), MINUTE_IN_SECONDS);
    wp_safe_redirect(admin_url('themes.php?page=ma-demo-seeder'));
    exit;
}

function ma_demo_seed_content(): array {
    $stats = array('created' => 0, 'updated' => 0, 'skipped' => 0, 'errors' => array());
    if (!ma_demo_prepare_multilingual($stats)) {
        return $stats;
    }
    $images = ma_demo_import_images($stats);
    $pages = ma_demo_seed_pages($stats);
    ma_demo_seed_practices($images, $stats);
    ma_demo_seed_people($images, $stats);
    ma_demo_seed_matter($images, $stats);
    ma_demo_seed_insight($images, $stats);
    ma_demo_seed_arabic($images, $stats);
    ma_demo_seed_settings();
    ma_demo_seed_menu($pages, $stats);
    update_option('show_on_front', 'page');
    update_option('page_on_front', (int) $pages['home']);
    update_option('page_for_posts', (int) $pages['insights']);
    flush_rewrite_rules(false);
    return $stats;
}

function ma_demo_prepare_multilingual(array &$stats): bool {
    if (
        !class_exists('WPMultilingual\\LanguageManager')
        || !class_exists('WPMultilingual\\TranslationManager')
        || !class_exists('WPMultilingual\\Sync')
    ) {
        $stats['errors'][] = __('Activate the WP Multilingual plugin before running the demo seeder.', 'measured-advocacy');
        return false;
    }

    $manager = WPMultilingual\LanguageManager::get_instance();
    $languages = array(
        'en' => array(
            'code' => 'en',
            'locale' => 'en_US',
            'name' => 'English',
            'native_name' => 'English',
            'direction' => 'ltr',
            'flag' => 'EN',
            'url_code' => 'en',
            'is_default' => 1,
            'is_enabled' => 1,
            'ordering' => 0,
        ),
        'ar' => array(
            'code' => 'ar',
            'locale' => 'ar',
            'name' => 'Arabic',
            'native_name' => 'العربية',
            'direction' => 'rtl',
            'flag' => 'AR',
            'url_code' => 'ar',
            'is_enabled' => 1,
            'ordering' => 1,
        ),
    );

    foreach ($languages as $code => $data) {
        $language = $manager->get_language($code);
        $result = $language
            ? $manager->update_language($language->id, $data)
            : $manager->add_language($data);
        if (is_wp_error($result)) {
            $stats['errors'][] = $result->get_error_message();
            return false;
        }
    }
    $manager->set_default_language('en');
    $manager->set_current_language('en');

    $settings = get_option('wpm_settings', array());
    $post_types = isset($settings['translatable_post_types']) && is_array($settings['translatable_post_types'])
        ? $settings['translatable_post_types']
        : array('post', 'page');
    $taxonomies = isset($settings['translatable_taxonomies']) && is_array($settings['translatable_taxonomies'])
        ? $settings['translatable_taxonomies']
        : array('category', 'post_tag');
    $settings['translatable_post_types'] = array_values(array_unique(array_merge(
        $post_types,
        array('post', 'page', 'ma_practice', 'ma_attorney', 'ma_matter')
    )));
    $settings['translatable_taxonomies'] = array_values(array_unique(array_merge(
        $taxonomies,
        array('practice_group')
    )));
    $settings['sync_featured_image'] = 1;
    update_option('wpm_settings', $settings);
    return true;
}

function ma_demo_seed_pages(array &$stats): array {
    $pages = array(
        'home' => array('Home', '<p>Senior legal counsel for complex business decisions, disputes, assets, and private client matters.</p>'),
        'about' => array('About', '<h2>Clear counsel for consequential decisions</h2><p>We are a senior-led law firm focused on matters where commercial judgment, legal precision, and disciplined execution work together.</p><h2>How we work</h2><p>We define the decision, identify the exposure, and establish practical constraints before recommending a strategy.</p>'),
        'contact' => array('Contact', '<h2>Reach the office</h2><p><strong>Phone:</strong> +20 2 5555 0147<br><strong>Email:</strong> counsel@example.com<br><strong>Office:</strong> Nile Business District, Cairo, Egypt</p>'),
        'consultation' => array('Request a Consultation', '<h2>Start with a confidential conversation</h2><p>Tell us the decision you face, the timing involved, and the interests that may be exposed.</p>'),
        'insights' => array('Insights', '<p>Decision notes and practical legal analysis from the firm.</p>'),
        'privacy' => array('Privacy', '<h2>Privacy notice</h2><p>Replace this demonstration page with a notice reviewed for the firm and its actual practices.</p>'),
        'legal' => array('Legal Notice', '<h2>Legal notice</h2><p>This website provides general information and does not create a lawyer-client relationship.</p>'),
        'accessibility' => array('Accessibility', '<h2>Accessibility statement</h2><p>We are committed to making our digital presence accessible to all users.</p>'),
    );

    $ids = array();
    foreach ($pages as $slug => $page) {
        $ids[$slug] = ma_demo_upsert(array(
            'post_title' => $page[0],
            'post_name' => $slug,
            'post_type' => 'page',
            'post_status' => 'publish',
            'post_content' => $page[1],
        ), 'page-' . $slug, $stats);
    }
    return $ids;
}

function ma_demo_seed_arabic(array $images, array &$stats): void {
    ma_demo_seed_arabic_pages($stats);
    ma_demo_seed_arabic_practices($images, $stats);
    ma_demo_seed_arabic_people($images, $stats);
    ma_demo_seed_arabic_editorial($images, $stats);
}

function ma_demo_seed_arabic_pages(array &$stats): void {
    $pages = array(
        'home' => array(
            'الرئيسية',
            '<p>استشارات قانونية رفيعة المستوى للقرارات التجارية المعقدة والنزاعات والأصول وشؤون العملاء الخاصة.</p>',
        ),
        'about' => array(
            'من نحن',
            '<h2>مشورة واضحة للقرارات المصيرية</h2><p>نحن مكتب محاماة يقوده مستشارون ذوو خبرة، نركز على المسائل التي تتطلب حكمة تجارية ودقة قانونية وتنفيذاً منضبطاً.</p><h2>منهج عملنا</h2><p>نحدد القرار، ونقيس المخاطر والالتزامات، ونضع خطة واضحة قبل التوصية بأي استراتيجية.</p>',
        ),
        'contact' => array(
            'تواصل معنا',
            '<h2>مكتب الاستشارات</h2><p><strong>الهاتف:</strong> +20 2 5555 0147<br><strong>البريد الإلكتروني:</strong> counsel@example.com<br><strong>المقر:</strong> حي النيل للأعمال، القاهرة، مصر</p>',
        ),
        'consultation' => array(
            'طلب استشارة',
            '<h2>ابدأ بمحادثة سرية</h2><p>أخبرنا بالقرار الذي تواجهه والأولويات والمصالح التي لا تحتمل المجازفة.</p>',
        ),
        'insights' => array('الرؤى القانونية', '<p>مذكرات قرار وتحليلات قانونية معمقة حول البيئة التنظيمية وحوكمة الشركات.</p>'),
        'privacy' => array('الخصوصية', '<h2>إشعار الخصوصية</h2><p>نلتزم بحماية سرية معلومات عملائنا وفق أعلى المعايير القانونية والأخلاقية.</p>'),
        'legal' => array('الإشعار القانوني', '<h2>إشعار قانوني</h2><p>المعلومات المنشورة عامة ولا تنشئ علاقة محامٍ وموكل ما لم يتم توقيع اتفاقية تمثيل رسمي.</p>'),
        'accessibility' => array('إمكانية الوصول', '<h2>إمكانية الوصول</h2><p>نلتزم بإتاحة محتوى موقعنا لجميع المستخدمين بما يتوافق مع المعايير الدولية.</p>'),
    );

    foreach ($pages as $slug => $page) {
        ma_demo_translate_seed(
            'page-' . $slug,
            array(
                'post_title' => $page[0],
                'post_name' => $slug . '-ar',
                'post_content' => $page[1],
                'post_excerpt' => '',
            ),
            array(),
            $stats
        );
    }
}

function ma_demo_seed_arabic_practices(array $images, array &$stats): void {
    $items = array(
        'corporate' => array('الاستشارات المؤسسية والاندماج والاستحواذ', 'التجاري والشركات', 'مشورة استراتيجية في إعادة الهيكلة، الاندماج، حوكمة الشركات، والامتثال التنظيمي.'),
        'litigation' => array('تسوية النزاعات والتقاضي المعقد', 'التقاضي والتحكيم', 'تمثيل حاسم في النزاعات التجارية والمدنية ونزاعات العقارات الكبرى أمام المحاكم وهيئات التحكيم.'),
        'real-estate' => array('العقارات والإنشاءات والبنية التحتية', 'الأصول والمشاريع', 'مشورة متكاملة للمطورين والمستثمرين في الصفقات العقارية وعقود المقاولات الكبرى (FIDIC).'),
        'employment' => array('العمل وحوكمة القيادات التنفيذية', 'حوكمة بيئة العمل', 'مشورة بشأن التعيينات التنفيذية وسياسات العمل والتحقيقات وحماية أسرار الأعمال.'),
        'family-law' => array('قانون الأسرة وهيكلة الثروات الخاصة', 'العملاء وشؤون الثروات', 'مشورة سرية في التركات والوصايا والأوقاف العائلية وحوكمة الشركات الخاصة.'),
        'criminal-defense' => array('الدفاع في جرائم الأعمال والجرائم المالية', 'التحقيقات والدفاع', 'دفاع استراتيجي في قضايا الاحتيال المالي، غسل الأموال، والتحقيقات التنظيمية والتجارية.'),
        'intellectual-property' => array('الملكية الفكرية وبراءات الاختراع والعلامات التجارية', 'الابتكار والملكية الفكرية', 'حماية العلامات والتقنيات والمعلومات السرية والتصدي للمنافسة غير المشروعة.'),
    );

    foreach ($items as $slug => $item) {
        $post_id = ma_demo_translate_seed(
            'practice-' . $slug,
            array(
                'post_title' => $item[0],
                'post_name' => $slug . '-ar',
                'post_excerpt' => $item[2],
                'post_content' => '<p>' . $item[2] . '</p><p>يجمع نهجنا بين التحليل القانوني الدقيق والواقع العملي لتمكين مجالس الإدارة من اتخاذ القرارات بثقة.</p>',
            ),
            array(
                'ma_kicker' => $item[1],
                'ma_decision_heading' => 'القرار',
                'ma_decision_body' => 'تحديد القرار والمسؤوليات والجدول الزمني والنتائج التجارية المستهدفة.',
                'ma_exposure_heading' => 'المخاطر والالتزامات',
                'ma_exposure_body' => 'حماية السيطرة والأصول والسمعة المؤسسية والامتثال التنظيمي لضمان استمرارية الأعمال.',
                'ma_counsel_heading' => 'دور المستشار',
                'ma_counsel_body' => 'نضبط الافتراضات ونحدد الخيارات ونعد الصياغات القانونية المحكمة ونتابع التنفيذ.',
                'ma_caveat' => 'تعتمد الاستراتيجية المناسبة على وقائع كل معاملة والقوانين والأدلة الخاصة بكل حالة.',
            ),
            $stats
        );
        if ($post_id) {
            ma_demo_thumbnail($post_id, $images, 'office/img-002-approach.jpg');
        }
    }
}

function ma_demo_seed_arabic_people(array $images, array &$stats): void {
    $people = array(
        'managing-partner' => array('د. عبد الرحمن المنشاوي', 'الشريك المدير - الاستشارات المؤسسية والاستراتيجية', 'إعادة هيكلة الشركات، حوكمة الشركات العائلية، الاندماج والاستحواذ', 'يقود د. عبد الرحمن المشورة لمجالس الإدارات والشركات العائلية في القرارات المصيرية ذات الأثر الاستراتيجي والتنظيمي.'),
        'senior-litigation-partner' => array('أ. طارق عبد العزيز', 'شريك أول - رئيس قسم التقاضي والتحكيم', 'النزاعات التجارية الكبرى، التحكيم الدولي، القضايا المالية المعقدة', 'يقود أ. طارق النزاعات التجارية الكبرى عبر التقييم الدقيق للمخاطر وتنسيق الدفوع والمرافعات القضائية الرصينة.'),
        'real-estate-counsel' => array('المستشار هشام فاروق', 'مستشار أول - المشاريع والإنشاءات', 'المشاريع العقارية الكبرى، عقود الفيديك (FIDIC)، تمويل الأصول والبنية التحتية', 'يعمل المستشار هشام مع المطورين والمستثمرين لضمان سلامة الهيكلة التعاقدية وحماية الاستثمارات طوال دورة المشروع.'),
    );
    foreach ($people as $slug => $person) {
        $post_id = ma_demo_translate_seed(
            'attorney-' . $slug,
            array(
                'post_title' => $person[0],
                'post_name' => $slug . '-ar',
                'post_excerpt' => $person[1],
                'post_content' => '<p>' . $person[3] . '</p><h2>منهج العمل</h2><p>تحظى كل قضية باهتمام مباشر من مستشارين ذوي خبرة وتوجيهات واضحة وتواصل مستمر.</p>',
            ),
            array(
                'ma_role' => $person[1],
                'ma_focus' => $person[2],
                'ma_admissions' => 'نقابة المحامين المصرية' . PHP_EOL . 'محكمة الاستئناف ومحكمة النقض',
                'ma_jurisdictions' => 'مصر والمعاملات الإقليمية العابرة للحدود',
                'ma_languages' => 'العربية والإنجليزية',
            ),
            $stats
        );
        if ($post_id) {
            ma_demo_thumbnail($post_id, $images, 'people/img-003-managing-partner.jpg');
        }
    }
}

function ma_demo_seed_arabic_editorial(array $images, array &$stats): void {
    $matter_id = ma_demo_translate_seed(
        'matter-family-enterprise-restructuring',
        array(
            'post_title' => 'إعادة هيكلة شركة عائلية قابضة تحت ضغط تنظيمي',
            'post_name' => 'family-enterprise-restructuring-ar',
            'post_excerpt' => 'إعادة هيكلة شركة عائلية لتلبية متطلبات تنظيمية جديدة دون تعطيل السيطرة أو العمليات.',
            'post_content' => '<p>تطلبت المهمة موازنة دقيقة بين خطة التعاقب والالتزامات النظامية واستمرارية الأعمال ضمن جدول تنفيذي واضح ومحكم.</p>',
        ),
        array(
            'ma_kicker' => 'قضية نموذجية',
            'ma_sector' => 'الشركات والمؤسسات العائلية',
            'ma_challenge' => 'كان على الشركة تلبية المتطلبات التنظيمية الجديدة مع حماية استمرارية الملكية والعمليات.',
            'ma_contribution' => 'أعدنا هيكلة الحوكمة ونسقنا التعديلات التنظيمية وصغنا مستندات التنفيذ المتكاملة.',
            'ma_caveat' => 'تم تعميم التفاصيل حفاظاً على السرية، ولا تضمن النتائج السابقة نتائج مماثلة في قضايا أخرى.',
        ),
        $stats
    );
    ma_demo_thumbnail($matter_id, $images, 'hero/img-001-hero-ar.jpg');

    $insight_id = ma_demo_translate_seed(
        'insight-family-enterprise-compliance',
        array(
            'post_title' => 'المتطلبات التنظيمية الجديدة للشركات العائلية',
            'post_name' => 'family-enterprise-compliance-ar',
            'post_excerpt' => 'قد تتطلب التغييرات التنظيمية مراجعة الحوكمة والسيطرة وترتيبات الشركاء والالتزامات الإدارية.',
            'post_content' => '<p>ينبغي التعامل مع المتطلبات الجديدة باعتبارها فرصة لتعزيز الحوكمة وحماية مصالح الأطراف وليست مجرد إجراءات شكلية.</p><h2>أسئلة للإدارة</h2><p>حدد الجدول الزمني والمسؤوليات وافحص الخيارات القانونية لحماية القرارات المستقبلية.</p>',
        ),
        array(
            'ma_kicker' => 'مذكرة قرار',
            'ma_reading_time' => '8 دقائق للقراءة',
            'ma_jurisdiction' => 'مصر والمنطقة العربية',
            'ma_citations' => 'التشريعات واللوائح التنظيمية المعمول بها.' . PHP_EOL . 'الممارسات المعتمدة وحوكمة الشركات.',
        ),
        $stats
    );
    ma_demo_thumbnail($insight_id, $images, 'insights/img-014-decision-note.jpg');
}

function ma_demo_seed_practices(array $images, array &$stats): void {
    foreach (ma_default_practices() as $order => $practice) {
        $post_id = ma_demo_upsert(array(
            'post_type' => 'ma_practice',
            'post_status' => 'publish',
            'post_title' => $practice['title'],
            'post_name' => $practice['slug'],
            'post_excerpt' => $practice['desc'],
            'post_content' => '<p>' . esc_html($practice['desc']) . '</p><p>Our approach combines legal analysis with practical attention to timing, leverage, stakeholders, and implementation.</p>',
            'menu_order' => $order,
        ), 'practice-' . $practice['slug'], $stats);
        if (!$post_id) {
            continue;
        }
        wp_set_object_terms($post_id, $practice['category'], 'practice_group');
        ma_demo_meta($post_id, array(
            'ma_kicker' => $practice['category'],
            'ma_decision_heading' => 'The Decision',
            'ma_decision_body' => 'Define the decision, authority, timetable, and acceptable commercial outcome.',
            'ma_exposure_heading' => 'The Exposure',
            'ma_exposure_body' => 'Control, assets, reputation, regulatory duties, evidence, and business continuity may be affected.',
            'ma_counsel_heading' => 'The Counsel',
            'ma_counsel_body' => 'We test assumptions, frame options, prepare the legal work, and coordinate implementation.',
            'ma_caveat' => 'The strategy depends on the facts, governing law, evidence, and client objectives.',
        ));
        ma_demo_thumbnail($post_id, $images, 'office/img-002-approach.jpg');
    }
}

function ma_demo_seed_people(array $images, array &$stats): void {
    $names = array('Omar Hassan', 'Mariam El-Sayed', 'Youssef Nabil');
    $emails = array('omar.hassan@example.com', 'mariam.elsayed@example.com', 'youssef.nabil@example.com');
    foreach (ma_default_people() as $order => $person) {
        $post_id = ma_demo_upsert(array(
            'post_type' => 'ma_attorney',
            'post_status' => 'publish',
            'post_title' => $names[$order],
            'post_name' => $person['slug'],
            'post_excerpt' => $person['role'],
            'post_content' => '<p>' . esc_html($person['statement']) . '</p><h2>Approach</h2><p>Each instruction receives direct senior attention, clear recommendations, and disciplined communication.</p>',
            'menu_order' => $order,
        ), 'attorney-' . $person['slug'], $stats);
        if (!$post_id) {
            continue;
        }
        ma_demo_meta($post_id, array(
            'ma_role' => $person['role'],
            'ma_focus' => $person['focus'],
            'ma_admissions' => 'Egyptian Bar Association' . PHP_EOL . 'Court of Appeal',
            'ma_jurisdictions' => 'Egypt and regional cross-border matters',
            'ma_languages' => 'Arabic and English',
            'ma_email' => $emails[$order],
            'ma_phone' => '+20 2 5555 014' . (7 + $order),
        ));
        ma_demo_thumbnail($post_id, $images, 'people/img-003-managing-partner.jpg');
    }
}

function ma_demo_seed_matter(array $images, array &$stats): void {
    $matter = ma_default_matter();
    $post_id = ma_demo_upsert(array(
        'post_type' => 'ma_matter',
        'post_status' => 'publish',
        'post_title' => $matter['title'],
        'post_name' => 'family-enterprise-restructuring',
        'post_excerpt' => 'A family enterprise needed to meet new requirements without disrupting control or operations.',
        'post_content' => '<p>' . esc_html($matter['body']) . '</p>',
    ), 'matter-family-enterprise-restructuring', $stats);
    if ($post_id) {
        ma_demo_meta($post_id, array(
            'ma_kicker' => 'Representative Matter',
            'ma_sector' => 'Corporate and Family Enterprise',
            'ma_challenge' => 'The business needed to meet new requirements while protecting continuity and operations.',
            'ma_contribution' => 'We designed the governance workstream, coordinated regulatory analysis, and prepared implementation documents.',
            'ma_caveat' => $matter['caveat'],
        ));
        ma_demo_thumbnail($post_id, $images, 'hero/img-001-hero-en.jpg');
    }
}

function ma_demo_seed_insight(array $images, array &$stats): void {
    $insight = ma_default_insight();
    $post_id = ma_demo_upsert(array(
        'post_type' => 'post',
        'post_status' => 'publish',
        'post_title' => $insight['title'],
        'post_name' => 'family-enterprise-compliance',
        'post_excerpt' => $insight['thesis'],
        'post_content' => '<p>' . esc_html($insight['thesis']) . '</p><h2>Questions for leadership</h2><p>Confirm the timetable, decision authority, record, and legal options against operational priorities.</p>',
    ), 'insight-family-enterprise-compliance', $stats);
    if ($post_id) {
        ma_demo_meta($post_id, array(
            'ma_kicker' => $insight['type'],
            'ma_reading_time' => $insight['reading'],
            'ma_jurisdiction' => 'Egypt',
            'ma_citations' => 'Applicable legislation and regulations.' . PHP_EOL . 'Current official guidance and governing documents.',
        ));
        ma_demo_thumbnail($post_id, $images, 'insights/img-014-decision-note.jpg');
    }
}

function ma_demo_seed_settings(): void {
    foreach (array(
        'ma_firm_name' => 'Hassan, El-Sayed and Partners',
        'ma_legal_entity' => 'Attorneys and Legal Consultants',
        'ma_phone' => '+20 2 5555 0147',
        'ma_email' => 'counsel@example.com',
        'ma_address' => 'Nile Business District' . PHP_EOL . 'Cairo, Egypt',
        'ma_hours' => 'Sunday - Thursday: 8:30 AM - 5:30 PM',
    ) as $key => $value) {
        set_theme_mod($key, $value);
    }
}

function ma_demo_seed_menu(array $pages, array &$stats): void {
    $menu = wp_get_nav_menu_object('Measured Advocacy Demo');
    if (!$menu) {
        $menu_id = wp_create_nav_menu('Measured Advocacy Demo');
        if (is_wp_error($menu_id)) {
            $stats['errors'][] = $menu_id->get_error_message();
            return;
        }
        $stats['created']++;
    } else {
        $menu_id = (int) $menu->term_id;
    }

    // Clean up any existing items to ensure canonical order & clean URLs
    $existing_items = wp_get_nav_menu_items($menu_id);
    if ($existing_items) {
        foreach ($existing_items as $item) {
            wp_delete_post($item->ID, true);
        }
    }

    $nav_structure = array(
        array('title' => 'About', 'url' => home_url('/about/')),
        array('title' => 'Expertise', 'url' => home_url('/expertise/')),
        array('title' => 'People', 'url' => home_url('/people/')),
        array('title' => 'Experience', 'url' => home_url('/experience/')),
        array('title' => 'Insights', 'url' => home_url('/insights/')),
        array('title' => 'Contact', 'url' => home_url('/contact/')),
    );

    foreach ($nav_structure as $item) {
        wp_update_nav_menu_item($menu_id, 0, array(
            'menu-item-title' => $item['title'],
            'menu-item-url' => $item['url'],
            'menu-item-type' => 'custom',
            'menu-item-status' => 'publish',
        ));
    }

    $locations = get_theme_mod('nav_menu_locations', array());
    $locations['primary'] = $menu_id;
    $locations['footer'] = $menu_id;
    set_theme_mod('nav_menu_locations', $locations);
}

function ma_demo_translate_seed(string $source_key, array $data, array $meta, array &$stats): int {
    $source_id = ma_demo_seed_id($source_key);
    if (!$source_id) {
        $stats['errors'][] = sprintf(__('Missing source item for translation: %s', 'measured-advocacy'), $source_key);
        return 0;
    }

    $translation_manager = WPMultilingual\TranslationManager::get_instance();
    $group_id = $translation_manager->get_object_group_id($source_id, 'post');
    if (!$group_id) {
        $group_id = $translation_manager->create_group('post');
    }
    $translation_manager->assign_language_and_group($source_id, 'en', $group_id, 'post', 'translated');

    $translation_key = $source_key . '-ar';
    $translation_id = ma_demo_seed_id($translation_key);
    if (!$translation_id) {
        $translation_id = (int) $translation_manager->get_translation($source_id, 'ar', 'post');
    }

    if ($translation_id) {
        $data['ID'] = $translation_id;
        $data['post_status'] = 'publish';
        $result = wp_update_post(wp_slash($data), true);
        if (is_wp_error($result)) {
            $stats['errors'][] = $result->get_error_message();
            return 0;
        }
        $stats['updated']++;
    } else {
        $data['post_status'] = 'publish';
        $result = WPMultilingual\Sync::get_instance()->duplicate_post_to_language(
            $source_id,
            'ar',
            $data
        );
        if (is_wp_error($result)) {
            $stats['errors'][] = $result->get_error_message();
            return 0;
        }
        $translation_id = (int) $result;
        $stats['created']++;
    }

    update_post_meta($translation_id, '_ma_demo_seed_key', $translation_key);
    ma_demo_meta($translation_id, $meta);
    $translation_manager->assign_language_and_group(
        $translation_id,
        'ar',
        $group_id,
        'post',
        'translated'
    );
    return $translation_id;
}

function ma_demo_seed_id(string $key): int {
    $posts = get_posts(array(
        'post_type' => array('post', 'page', 'ma_practice', 'ma_attorney', 'ma_matter'),
        'post_status' => 'any',
        'posts_per_page' => 1,
        'fields' => 'ids',
        'meta_key' => '_ma_demo_seed_key',
        'meta_value' => $key,
        'suppress_filters' => true,
    ));
    return $posts ? (int) $posts[0] : 0;
}

function ma_demo_upsert(array $data, string $key, array &$stats): int {
    $existing = get_posts(array(
        'post_type' => $data['post_type'],
        'post_status' => 'any',
        'posts_per_page' => 1,
        'fields' => 'ids',
        'meta_key' => '_ma_demo_seed_key',
        'meta_value' => $key,
        'suppress_filters' => true,
    ));
    if ($existing) {
        $data['ID'] = (int) $existing[0];
        $post_id = wp_update_post(wp_slash($data), true);
        if (is_wp_error($post_id)) {
            $stats['errors'][] = $post_id->get_error_message();
            return 0;
        }
        update_post_meta((int) $post_id, '_ma_demo_seed_key', $key);
        $stats['updated']++;
        return (int) $post_id;
    }
    $match = get_page_by_path($data['post_name'], OBJECT, $data['post_type']);
    if ($match instanceof WP_Post) {
        update_post_meta($match->ID, '_ma_demo_seed_key', $key);
        $stats['skipped']++;
        return (int) $match->ID;
    }
    $post_id = wp_insert_post(wp_slash($data), true);
    if (is_wp_error($post_id)) {
        $stats['errors'][] = $post_id->get_error_message();
        return 0;
    }
    update_post_meta($post_id, '_ma_demo_seed_key', $key);
    $stats['created']++;
    return (int) $post_id;
}

function ma_demo_is_seeded(int $post_id): bool {
    return '' !== (string) get_post_meta($post_id, '_ma_demo_seed_key', true);
}

function ma_demo_meta(int $post_id, array $values): void {
    foreach ($values as $key => $value) {
        update_post_meta($post_id, $key, $value);
    }
}

function ma_demo_import_images(array &$stats): array {
    $stored = get_option('ma_demo_seed_attachments', array());
    $images = is_array($stored) ? $stored : array();
    $directory = MA_THEME_DIR . '/assets/images';
    if (!is_dir($directory)) {
        return $images;
    }

    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($directory, FilesystemIterator::SKIP_DOTS)
    );
    foreach ($iterator as $file) {
        if (!$file->isFile()) {
            continue;
        }
        $extension = strtolower($file->getExtension());
        if (!in_array($extension, array('jpg', 'jpeg', 'png', 'webp'), true)) {
            continue;
        }

        $relative = str_replace(
            trailingslashit(wp_normalize_path($directory)),
            '',
            wp_normalize_path($file->getPathname())
        );
        $attachment_id = isset($images[$relative]) ? (int) $images[$relative] : 0;
        if ($attachment_id && 'attachment' === get_post_type($attachment_id)) {
            continue;
        }

        $found = get_posts(array(
            'post_type' => 'attachment',
            'post_status' => 'inherit',
            'posts_per_page' => 1,
            'fields' => 'ids',
            'meta_key' => '_ma_demo_seed_asset',
            'meta_value' => $relative,
        ));
        if ($found) {
            $images[$relative] = (int) $found[0];
            continue;
        }

        $contents = file_get_contents($file->getPathname());
        if (false === $contents) {
            $stats['errors'][] = sprintf(__('Could not read demo image: %s', 'measured-advocacy'), $relative);
            continue;
        }
        $upload = wp_upload_bits($file->getFilename(), null, $contents);
        if (!empty($upload['error'])) {
            $stats['errors'][] = (string) $upload['error'];
            continue;
        }

        $filetype = wp_check_filetype($upload['file']);
        $title = ucwords(str_replace(array('-', '_'), ' ', pathinfo($file->getFilename(), PATHINFO_FILENAME)));
        $attachment_id = wp_insert_attachment(array(
            'post_mime_type' => $filetype['type'],
            'post_title' => $title,
            'post_status' => 'inherit',
        ), $upload['file'], 0, true);
        if (is_wp_error($attachment_id)) {
            $stats['errors'][] = $attachment_id->get_error_message();
            continue;
        }

        require_once ABSPATH . 'wp-admin/includes/image.php';
        wp_update_attachment_metadata(
            $attachment_id,
            wp_generate_attachment_metadata($attachment_id, $upload['file'])
        );
        update_post_meta($attachment_id, '_ma_demo_seed_asset', $relative);
        update_post_meta($attachment_id, '_wp_attachment_image_alt', $title);
        $images[$relative] = $attachment_id;
        $stats['created']++;
    }
    update_option('ma_demo_seed_attachments', $images, false);
    return $images;
}

function ma_demo_thumbnail(int $post_id, array $images, string $path): void {
    if (!empty($images[$path])) {
        set_post_thumbnail($post_id, (int) $images[$path]);
    }
}


