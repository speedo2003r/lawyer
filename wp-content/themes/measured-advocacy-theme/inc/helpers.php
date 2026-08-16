<?php
/**
 * Helper functions and fallback content.
 *
 * @package MeasuredAdvocacy
 */

if (!defined('ABSPATH')) {
    exit;
}

function ma_locale(): string {
    // WP Multilingual integration
    if (function_exists('wpm_get_current_language')) {
        return wpm_get_current_language();
    }

    // Polylang compatibility
    if (function_exists('pll_current_language')) {
        return pll_current_language('slug');
    }

    // Fallback to locale detection
    $locale = determine_locale();
    return str_starts_with($locale, 'ar') || is_rtl() ? 'ar' : 'en';
}

function ma_dir(): string {
    // WP Multilingual RTL detection
    if (function_exists('wpm_is_rtl')) {
        return wpm_is_rtl() ? 'rtl' : 'ltr';
    }

    // Polylang compatibility
    if (function_exists('pll_is_rtl')) {
        return pll_is_rtl() ? 'rtl' : 'ltr';
    }

    // Fallback
    return 'ar' === ma_locale() || is_rtl() ? 'rtl' : 'ltr';
}

function ma_language_switcher(): array {
    // WP Multilingual integration
    if (function_exists('wpm_get_languages') && function_exists('wpm_get_current_language')) {
        $languages = wpm_get_languages(array('enabled_only' => true));
        $current_lang = wpm_get_current_language();
        $result = array();

        foreach ($languages as $lang) {
            if ($lang->code !== $current_lang) {
                $url = '';

                // Get translated URL for current page/post
                if ((is_front_page() || is_home()) && function_exists('wpm_get_home_url')) {
                    $url = wpm_get_home_url($lang->code);
                } elseif (is_singular() && function_exists('wpm_get_translation')) {
                    $post_id = get_queried_object_id() ?: get_the_ID();
                    $translated_id = wpm_get_translation($post_id, $lang->code, 'post');

                    if ($translated_id) {
                        $url = get_permalink($translated_id);
                    } else {
                        $url = function_exists('wpm_get_home_url') ? wpm_get_home_url($lang->code) : home_url('/' . $lang->code);
                    }
                } else {
                    $url = function_exists('wpm_get_home_url') ? wpm_get_home_url($lang->code) : home_url('/' . $lang->code);
                }

                $result[] = array(
                    'url' => $url,
                    'label' => $lang->name,
                    'code' => $lang->code,
                    'direction' => $lang->direction ?? 'ltr',
                );
            }
        }

        return $result;
    }

    // Polylang compatibility
    if (function_exists('pll_the_languages')) {
        $langs = pll_the_languages(array('raw' => 1));
        $result = array();

        foreach ($langs as $lang) {
            if (!$lang['current_lang']) {
                $result[] = array(
                    'url' => $lang['url'],
                    'label' => $lang['name'],
                    'code' => $lang['slug'],
                    'direction' => 'ltr',
                );
            }
        }

        return $result;
    }

    // Fallback: simple toggle
    $current_locale = ma_locale();
    if ('ar' === $current_locale) {
        return array(array(
            'url' => home_url('/en'),
            'label' => 'English',
            'code' => 'en',
            'direction' => 'ltr',
        ));
    }

    return array(array(
        'url' => home_url('/ar'),
        'label' => 'العربية',
        'code' => 'ar',
        'direction' => 'rtl',
    ));
}

function ma_asset(string $path): string {
    return esc_url(MA_THEME_URI . '/assets/' . ltrim($path, '/'));
}

function ma_firm(string $key): string {
    $is_ar = 'ar' === ma_locale();
    $defaults = array(
        'ma_firm_name' => $is_ar ? 'حسن والسيد وشركاؤهم' : 'Hassan, El-Sayed & Partners',
        'ma_legal_entity' => $is_ar ? 'شركة محاماة واستشارات قانونية' : 'Attorneys at Law & Legal Consultants',
        'ma_phone' => '+20 2 5555 0147',
        'ma_email' => 'counsel@example.com',
        'ma_address' => $is_ar ? 'حي النيل للأعمال، القاهرة، مصر' : 'Nile Business District, Cairo, Egypt',
        'ma_hours' => $is_ar ? 'الأحد - الخميس: 8:30 ص - 5:30 م' : 'Sunday - Thursday: 8:30 AM - 5:30 PM',
    );
    return (string) get_theme_mod($key, $defaults[$key] ?? '');
}

function ma_phone_href(?string $phone = null): string {
    $phone = $phone ?: ma_firm('ma_phone');
    return 'tel:' . preg_replace('/[^+0-9]/', '', $phone);
}

function ma_field(int $post_id, string $key, string $fallback = ''): string {
    $value = get_post_meta($post_id, $key, true);
    return '' !== trim((string) $value) ? (string) $value : $fallback;
}

function ma_excerpt_or_field(WP_Post $post, string $fallback = ''): string {
    if (has_excerpt($post)) {
        return get_the_excerpt($post);
    }
    $excerpt = wp_trim_words(wp_strip_all_tags($post->post_content), 28);
    return '' !== $excerpt ? $excerpt : $fallback;
}

function ma_img(string $path): string {
    return ma_asset('images/' . ltrim($path, '/'));
}

function ma_default_practices(): array {
    if ('ar' === ma_locale()) {
        return array(
            array('slug' => 'corporate', 'category' => 'التجاري والشركات', 'title' => 'الاستشارات المؤسسية والاندماج والاستحواذ', 'desc' => 'مشورة استراتيجية في إعادة الهيكلة، الاندماج، حوكمة الشركات، والامتثال التنظيمي.'),
            array('slug' => 'litigation', 'category' => 'التقاضي والتحكيم', 'title' => 'تسوية النزاعات والتقاضي المعقد', 'desc' => 'تمثيل حاسم في النزاعات التجارية والمدنية ونزاعات العقارات الكبرى أمام المحاكم وهيئات التحكيم.'),
            array('slug' => 'real-estate', 'category' => 'الأصول والمشاريع', 'title' => 'العقارات والإنشاءات والبنية التحتية', 'desc' => 'مشورة متكاملة للمطورين والمستثمرين في الصفقات العقارية وعقود المقاولات الكبرى (FIDIC).'),
            array('slug' => 'employment', 'category' => 'حوكمة بيئة العمل', 'title' => 'العمل وحوكمة القيادات التنفيذية', 'desc' => 'مشورة بشأن التعيينات التنفيذية وسياسات العمل والتحقيقات وحماية أسرار الأعمال.'),
            array('slug' => 'family-law', 'category' => 'العملاء وشؤون الثروات', 'title' => 'قانون الأسرة وهيكلة الثروات الخاصة', 'desc' => 'مشورة سرية في التركات والوصايا والأوقاف العائلية وحوكمة الشركات الخاصة.'),
            array('slug' => 'criminal-defense', 'category' => 'التحقيقات والدفاع', 'title' => 'الدفاع في جرائم الأعمال والجرائم المالية', 'desc' => 'دفاع استراتيجي في قضايا الاحتيال المالي، غسل الأموال، والتحقيقات التنظيمية والتجارية.'),
            array('slug' => 'intellectual-property', 'category' => 'الابتكار والملكية الفكرية', 'title' => 'الملكية الفكرية وبراءات الاختراع والعلامات التجارية', 'desc' => 'حماية العلامات والتقنيات والمعلومات السرية والتصدي للمنافسة غير المشروعة.'),
        );
    }

    return array(
        array('slug' => 'corporate', 'category' => 'Commercial & Corporate', 'title' => 'Corporate Advisory & M&A', 'desc' => 'Strategic counsel on restructuring, mergers, corporate governance, and regulatory compliance.'),
        array('slug' => 'litigation', 'category' => 'Litigation & Arbitration', 'title' => 'Dispute Resolution & Complex Litigation', 'desc' => 'Decisive representation in high-stakes commercial, civil, and real estate disputes.'),
        array('slug' => 'real-estate', 'category' => 'Assets & Projects', 'title' => 'Real Estate, Construction & Infrastructure', 'desc' => 'Integrated counsel for developers and investors in property transactions and major construction contracts.'),
        array('slug' => 'employment', 'category' => 'Professional & Labor Governance', 'title' => 'Employment & Executive Governance', 'desc' => 'Managing complex workplace relationships, executive contracts, and labor compliance policies.'),
        array('slug' => 'family-law', 'category' => 'Private Client & Wealth', 'title' => 'Family Law & Private Wealth Structuring', 'desc' => 'Discreet counsel in complex estate division, wills, endowments, and family wealth governance.'),
        array('slug' => 'criminal-defense', 'category' => 'Criminal Defense', 'title' => 'White-Collar & Financial Criminal Defense', 'desc' => 'Strategic defense in financial fraud, money laundering, and commercial regulatory offenses.'),
        array('slug' => 'intellectual-property', 'category' => 'Innovation & IP Protection', 'title' => 'Intellectual Property, Patents & Trademarks', 'desc' => 'Protecting intangible assets, trademark registration, anti-infringement, and unfair competition.'),
    );
}

function ma_default_people(): array {
    if ('ar' === ma_locale()) {
        return array(
            array('slug' => 'managing-partner', 'name' => 'د. عبد الرحمن المنشاوي', 'role' => 'الشريك المدير - الاستشارات المؤسسية والاستراتيجية', 'focus' => 'إعادة هيكلة الشركات، حوكمة الشركات العائلية، الاندماج والاستحواذ', 'statement' => 'يقود د. عبد الرحمن المشورة لمجالس الإدارات والشركات العائلية في القرارات المصيرية ذات الأثر الاستراتيجي والتنظيمي.'),
            array('slug' => 'senior-litigation-partner', 'name' => 'أ. طارق عبد العزيز', 'role' => 'شريك أول - رئيس قسم التقاضي والتحكيم', 'focus' => 'النزاعات التجارية الكبرى، التحكيم الدولي، القضايا المالية المعقدة', 'statement' => 'يقود أ. طارق النزاعات التجارية الكبرى عبر التقييم الدقيق للمخاطر وتنسيق الدفوع والمرافعات القضائية الرصينة.'),
            array('slug' => 'real-estate-counsel', 'name' => 'المستشار هشام فاروق', 'role' => 'مستشار أول - المشاريع والإنشاءات', 'focus' => 'المشاريع العقارية الكبرى، عقود الفيديك (FIDIC)، تمويل الأصول والبنية التحتية', 'statement' => 'يعمل المستشار هشام مع المطورين والمستثمرين لضمان سلامة الهيكلة التعاقدية وحماية الاستثمارات طوال دورة المشروع.'),
        );
    }

    return array(
        array('slug' => 'managing-partner', 'name' => 'Omar Hassan', 'role' => 'Managing Partner - Corporate & Strategic Advisory', 'focus' => 'Corporate Restructuring, Family Enterprise Governance, M&A', 'statement' => 'True legal counsel is not merely contractual text; it is strategic clarity that provides executive leadership with confidence in consequential decisions.'),
        array('slug' => 'senior-litigation-partner', 'name' => 'Mariam El-Sayed', 'role' => 'Senior Partner - Head of Litigation & Arbitration', 'focus' => 'Major Commercial Disputes, International Arbitration, White-Collar Defense', 'statement' => 'Judicial strength originates from rigorous preparation and deep contextual understanding before stepping into the courtroom.'),
        array('slug' => 'real-estate-counsel', 'name' => 'Youssef Nabil', 'role' => 'Senior Counsel - Projects & Construction', 'focus' => 'Major Real Estate Projects, FIDIC Contracts, Asset Financing', 'statement' => 'In major projects, cohesive contractual structuring is the safeguard against default and capital exposure.'),
    );
}

function ma_default_matter(): array {
    if ('ar' === ma_locale()) {
        return array(
            'title' => 'إعادة هيكلة شركة عائلية قابضة تحت ضغط تنظيمي',
            'body' => 'عندما هددت التغييرات التنظيمية استمرارية العمليات لشركة تجارية عائلية متعددة الأجيال، تم تكليف المكتب بإعادة هيكلة الملكية والحوكمة. تطلبت المهمة موازنة دقيقة بين خطة التعاقب والالتزامات النظامية واستمرارية الأعمال ضمن جدول تنفيذي واضح ومحكم.',
            'caveat' => 'تم تعميم التفاصيل حفاظاً على السرية، ولا تضمن النتائج السابقة نتائج مماثلة في قضايا أخرى.',
        );
    }

    return array(
        'title' => 'Restructuring a family-held enterprise under regulatory pressure',
        'body' => 'When regulatory changes threatened the operational continuity of a multi-generational commercial enterprise, the firm was retained to restructure ownership and governance. The engagement required balancing family succession with compliance obligations, preserving business value while meeting new regulatory standards.',
        'caveat' => 'Details are shared within ethical and confidentiality boundaries. This is not a guarantee of similar outcomes.',
    );
}

function ma_default_insight(): array {
    if ('ar' === ma_locale()) {
        return array(
            'type' => 'مذكرة قرار',
            'title' => 'المتطلبات التنظيمية الجديدة للشركات العائلية',
            'thesis' => 'تفرض التغييرات التنظيمية الحديثة التزامات بإعادة الهيكلة على الشركات التي كانت معفاة في السابق. توضح هذه المذكرة المخاطر العملية والجدول الزمني لاتخاذ القرار.',
            'reading' => '8 دقائق للقراءة',
            'date' => 'يوليو 2026',
        );
    }

    return array(
        'type' => 'Decision Note',
        'title' => 'New compliance requirements and what they mean for family-held enterprises',
        'thesis' => 'Recent regulatory changes impose restructuring obligations on businesses that were previously exempt. This note explains the practical exposure and decision timeline for affected owners.',
        'reading' => '8 min read',
        'date' => 'July 2026',
    );
}

function ma_query_posts(string $post_type, int $limit = -1): WP_Query {
    return new WP_Query(array(
        'post_type' => $post_type,
        'post_status' => 'publish',
        'posts_per_page' => $limit,
        'orderby' => array('menu_order' => 'ASC', 'date' => 'DESC'),
        'no_found_rows' => true,
    ));
}

function ma_nav_items(): array {
    $is_ar = 'ar' === ma_locale();
    if ($is_ar) {
        return array(
            'about' => array('slug' => 'about-ar', 'label' => 'عن المكتب', 'match' => array('about', 'about-ar')),
            'expertise' => array('slug' => 'expertise', 'label' => 'الخبرات', 'match' => array('expertise', 'ma_practice')),
            'people' => array('slug' => 'people', 'label' => 'فريق العمل', 'match' => array('people', 'ma_attorney')),
            'experience' => array('slug' => 'experience', 'label' => 'الخبرة العملية', 'match' => array('experience', 'ma_matter')),
            'insights' => array('slug' => 'insights-ar', 'label' => 'الرؤى', 'match' => array('insights', 'insights-ar', 'post')),
            'contact' => array('slug' => 'contact-ar', 'label' => 'تواصل معنا', 'match' => array('contact', 'contact-ar')),
        );
    }
    return array(
        'about' => array('slug' => 'about', 'label' => 'About', 'match' => array('about', 'about-ar')),
        'expertise' => array('slug' => 'expertise', 'label' => 'Expertise', 'match' => array('expertise', 'ma_practice')),
        'people' => array('slug' => 'people', 'label' => 'People', 'match' => array('people', 'ma_attorney')),
        'experience' => array('slug' => 'experience', 'label' => 'Experience', 'match' => array('experience', 'ma_matter')),
        'insights' => array('slug' => 'insights', 'label' => 'Insights', 'match' => array('insights', 'insights-ar', 'post')),
        'contact' => array('slug' => 'contact', 'label' => 'Contact', 'match' => array('contact', 'contact-ar')),
    );
}

function ma_is_nav_active(array $item): bool {
    if (is_front_page()) {
        return false;
    }
    $post_type = get_post_type();
    if ($post_type && in_array($post_type, $item['match'], true)) {
        return true;
    }
    if (is_page()) {
        $post = get_queried_object();
        if ($post instanceof WP_Post && in_array($post->post_name, $item['match'], true)) {
            return true;
        }
    }
    if (is_post_type_archive($item['match'])) {
        return true;
    }
    if (is_home() && in_array('post', $item['match'], true)) {
        return true;
    }
    return false;
}

function ma_primary_menu(): void {
    $items = ma_nav_items();
    echo '<ul class="header-nav__list" role="list">';
    foreach ($items as $key => $item) {
        $is_active = ma_is_nav_active($item);
        $active_class = $is_active ? ' is-active' : '';
        $aria_current = $is_active ? ' aria-current="page"' : '';
        printf(
            '<li class="header-nav__item"><a href="%s" class="header-nav__link%s"%s>%s</a></li>',
            esc_url(home_url('/' . $item['slug'] . '/')),
            esc_attr($active_class),
            $aria_current,
            esc_html($item['label'])
        );
    }
    echo '</ul>';
}

function ma_footer_menu(): void {
    $items = ma_nav_items();
    echo '<ul role="list">';
    foreach ($items as $key => $item) {
        printf('<li><a href="%s">%s</a></li>', esc_url(home_url('/' . $item['slug'] . '/')), esc_html($item['label']));
    }
    echo '</ul>';
}

function ma_editorial_header(string $kicker, string $heading, string $lead): void {
    ?>
    <nav class="breadcrumbs" aria-label="Breadcrumbs">
        <ol class="breadcrumbs__list container" role="list">
            <li class="breadcrumbs__item"><a href="<?php echo esc_url(home_url('/')); ?>" class="breadcrumbs__link"><?php esc_html_e('Home', 'measured-advocacy'); ?></a><span class="breadcrumbs__separator" aria-hidden="true">/</span></li>
            <li class="breadcrumbs__item"><span class="breadcrumbs__current" aria-current="page"><?php echo esc_html($kicker); ?></span></li>
        </ol>
    </nav>
    <header class="editorial-header surface-limestone section">
        <div class="container">
            <div class="editorial-header__content">
                <p class="editorial-header__kicker small"><?php echo esc_html($kicker); ?></p>
                <h1 class="editorial-header__heading h1"><?php echo esc_html($heading); ?></h1>
                <p class="editorial-header__lead body-l"><?php echo esc_html($lead); ?></p>
            </div>
        </div>
    </header>
    <?php
}