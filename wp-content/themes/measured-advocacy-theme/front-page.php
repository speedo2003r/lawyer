<?php
/**
 * Template: Front Page (Home)
 *
 * @package MeasuredAdvocacy
 */

get_header();

$locale = ma_locale();
$dir = ma_dir();
$hero_img = 'ar' === $locale ? 'hero/img-001-hero-ar.jpg' : 'hero/img-001-hero-en.jpg';
$hero_img_mobile = 'ar' === $locale ? 'hero/img-001-hero-ar-mobile.jpg' : 'hero/img-001-hero-en-mobile.jpg';
?>

<section class="hero" aria-labelledby="hero-heading">
<div class="hero__background">
<picture>
<source media="(max-width: 768px)" srcset="<?php echo esc_url(ma_img($hero_img_mobile)); ?>" width="1600" height="2000">
<img src="<?php echo esc_url(ma_img($hero_img)); ?>" alt="Senior counsel standing beside a boardroom window, prepared and present" width="2400" height="1600" fetchpriority="high" class="hero__bg-img">
</picture>
</div>
<div class="hero__overlay"></div>
<div class="hero__inner container">
<div class="hero__content">
<p class="hero__kicker small"><?php echo esc_html(__('Senior Counsel for Consequential Matters', 'measured-advocacy')); ?></p>
<h1 id="hero-heading" class="hero__heading">
<?php echo esc_html(__('When a decision', 'measured-advocacy')); ?><br><?php echo esc_html(__('carries real consequence,', 'measured-advocacy')); ?><br>
<span class="hero__heading-accent"><?php echo esc_html(__('the counsel must be decisive.', 'measured-advocacy')); ?></span>
</h1>
<p class="hero__proposition body-l">
<?php echo esc_html(__('We provide senior legal counsel for the decisions that shape businesses, protect interests, and resolve disputes that cannot afford ambiguity.', 'measured-advocacy')); ?>
</p>
<div class="hero__actions">
<a href="<?php echo esc_url(home_url('/consultation')); ?>" class="btn btn--primary" id="hero-cta-consult">
<?php echo esc_html(__('Request a Consultation', 'measured-advocacy')); ?>
</a>
<a href="#matter-lens" class="btn btn--secondary-light" id="hero-cta-lens">
<?php esc_html_e('Frame Your Matter', 'measured-advocacy'); ?>
</a>
</div>
</div>
</div>
</section>

<section class="matter-lens section surface-paper" id="matter-lens" aria-labelledby="lens-heading">
<div class="container">
<div class="matter-lens__intro">
<h2 id="lens-heading" class="matter-lens__heading h2"><?php esc_html_e('Frame your matter', 'measured-advocacy'); ?></h2>
<p class="matter-lens__description body-l">
<?php esc_html_e('Before legal terminology, there is a situation. Select what applies to help us surface the most relevant expertise, people, and experience.', 'measured-advocacy'); ?>
</p>
</div>

<div class="matter-lens__dimensions" role="group" aria-label="<?php esc_attr_e('Matter dimensions', 'measured-advocacy'); ?>">
<div class="lens-dimension">
<h3 class="lens-dimension__label h3"><?php esc_html_e('What is changing?', 'measured-advocacy'); ?></h3>
<div class="lens-dimension__options" role="radiogroup" aria-label="<?php esc_attr_e('What is changing', 'measured-advocacy'); ?>">
<button class="lens-option" data-dimension="change" data-value="business-structure" type="button">
<span class="lens-option__text"><?php esc_html_e('Business structure or ownership', 'measured-advocacy'); ?></span>
</button>
<button class="lens-option" data-dimension="change" data-value="regulatory-environment" type="button">
<span class="lens-option__text"><?php esc_html_e('Regulatory environment', 'measured-advocacy'); ?></span>
</button>
<button class="lens-option" data-dimension="change" data-value="dispute-or-claim" type="button">
<span class="lens-option__text"><?php esc_html_e('A dispute or claim has emerged', 'measured-advocacy'); ?></span>
</button>
<button class="lens-option" data-dimension="change" data-value="personal-circumstances" type="button">
<span class="lens-option__text"><?php esc_html_e('Personal or family circumstances', 'measured-advocacy'); ?></span>
</button>
<button class="lens-option" data-dimension="change" data-value="property-or-asset" type="button">
<span class="lens-option__text"><?php esc_html_e('Property or asset decisions', 'measured-advocacy'); ?></span>
</button>
</div>
</div>

<div class="lens-dimension">
<h3 class="lens-dimension__label h3"><?php esc_html_e('What is exposed?', 'measured-advocacy'); ?></h3>
<div class="lens-dimension__options" role="radiogroup" aria-label="<?php esc_attr_e('What is exposed', 'measured-advocacy'); ?>">
<button class="lens-option" data-dimension="exposure" data-value="financial" type="button">
<span class="lens-option__text"><?php esc_html_e('Financial position or assets', 'measured-advocacy'); ?></span>
</button>
<button class="lens-option" data-dimension="exposure" data-value="reputation" type="button">
<span class="lens-option__text"><?php esc_html_e('Reputation or standing', 'measured-advocacy'); ?></span>
</button>
<button class="lens-option" data-dimension="exposure" data-value="operations" type="button">
<span class="lens-option__text"><?php esc_html_e('Business operations', 'measured-advocacy'); ?></span>
</button>
<button class="lens-option" data-dimension="exposure" data-value="rights" type="button">
<span class="lens-option__text"><?php esc_html_e('Personal rights or freedom', 'measured-advocacy'); ?></span>
</button>
<button class="lens-option" data-dimension="exposure" data-value="relationships" type="button">
<span class="lens-option__text"><?php esc_html_e('Commercial relationships', 'measured-advocacy'); ?></span>
</button>
</div>
</div>

<div class="lens-dimension">
<h3 class="lens-dimension__label h3"><?php esc_html_e('What decision is required?', 'measured-advocacy'); ?></h3>
<div class="lens-dimension__options" role="radiogroup" aria-label="<?php esc_attr_e('What decision is required', 'measured-advocacy'); ?>">
<button class="lens-option" data-dimension="decision" data-value="protect" type="button">
<span class="lens-option__text"><?php esc_html_e('Protect or defend a position', 'measured-advocacy'); ?></span>
</button>
<button class="lens-option" data-dimension="decision" data-value="negotiate" type="button">
<span class="lens-option__text"><?php esc_html_e('Negotiate or restructure', 'measured-advocacy'); ?></span>
</button>
<button class="lens-option" data-dimension="decision" data-value="comply" type="button">
<span class="lens-option__text"><?php esc_html_e('Comply with new requirements', 'measured-advocacy'); ?></span>
</button>
<button class="lens-option" data-dimension="decision" data-value="resolve" type="button">
<span class="lens-option__text"><?php esc_html_e('Resolve a dispute', 'measured-advocacy'); ?></span>
</button>
<button class="lens-option" data-dimension="decision" data-value="plan" type="button">
<span class="lens-option__text"><?php esc_html_e('Plan or structure proactively', 'measured-advocacy'); ?></span>
</button>
</div>
</div>
</div>

<div class="matter-lens__actions">
<button class="btn btn--primary" id="lens-apply" type="button" disabled>
<?php esc_html_e('Show Relevant Expertise', 'measured-advocacy'); ?>
</button>
<a href="<?php echo esc_url(home_url('/expertise')); ?>" class="btn btn--text" id="lens-skip">
<?php esc_html_e('View all expertise', 'measured-advocacy'); ?> →
</a>
</div>

<div class="matter-lens__results" id="lens-results" hidden aria-live="polite"></div>
</div>
</section>

<section class="evidence section surface-limestone" aria-labelledby="evidence-heading">
<div class="container">
<div class="evidence__grid grid">
<div class="evidence__content" style="grid-column: span 7;">
<h2 id="evidence-heading" class="evidence__heading h3">
<?php esc_html_e('Verified scope, demonstrated results', 'measured-advocacy'); ?>
</h2>
<p class="evidence__text body-l">
<?php esc_html_e('Our practice covers corporate advisory, dispute resolution, property and construction, employment, family law, and criminal defense — each led by practitioners with verifiable credentials and direct case involvement.', 'measured-advocacy'); ?>
</p>
</div>
<div class="evidence__annotation" style="grid-column: span 5;">
<div class="evidence-card">
<p class="evidence-card__caveat small">
<?php esc_html_e('Specific credentials and case involvement details are subject to verification and ethical disclosure requirements.', 'measured-advocacy'); ?>
</p>
<ul class="evidence-card__facts">
<li class="evidence-card__fact">
<span class="evidence-card__label small"><?php esc_html_e('Practice areas', 'measured-advocacy'); ?></span>
<span class="evidence-card__value h3">7</span>
</li>
<li class="evidence-card__fact">
<span class="evidence-card__label small"><?php esc_html_e('Years of practice', 'measured-advocacy'); ?></span>
<span class="evidence-card__value h3">25+</span>
</li>
<li class="evidence-card__fact">
<span class="evidence-card__label small"><?php esc_html_e('Jurisdictions', 'measured-advocacy'); ?></span>
<span class="evidence-card__value h3"><?php esc_html_e('Multiple', 'measured-advocacy'); ?></span>
</li>
</ul>
</div>
</div>
</div>
</div>
</section>

<?php
$default_matter = ma_default_matter();
$default_people = ma_default_people();
$managing_partner = $default_people[0] ?? array();
?>

<section class="contribution section surface-paper" aria-labelledby="contribution-heading">
<div class="container">
<div class="contribution__grid grid">
<div class="contribution__narrative" style="grid-column: span 7;">
<p class="contribution__label small"><?php esc_html_e('Representative Contribution', 'measured-advocacy'); ?></p>
<h2 id="contribution-heading" class="contribution__heading h2">
<?php echo esc_html($default_matter['title'] ?? __('Restructuring a family-held enterprise under regulatory pressure', 'measured-advocacy')); ?>
</h2>
<div class="contribution__body body-l">
<p><?php echo esc_html($default_matter['body'] ?? ''); ?></p>
<p class="contribution__limitation small" style="margin-top: var(--space-5); color: var(--color-slate);">
<?php echo esc_html($default_matter['caveat'] ?? ''); ?>
</p>
</div>
<a href="<?php echo esc_url(home_url('/expertise/corporate')); ?>" class="btn btn--text" style="margin-top: var(--space-5);">
<?php esc_html_e('Explore corporate expertise', 'measured-advocacy'); ?> →
</a>
</div>
<div class="contribution__counsel" style="grid-column: span 5;">
<div class="profile-plate">
<img src="<?php echo esc_url(ma_img('people/img-003-managing-partner.jpg')); ?>" alt="<?php esc_attr_e('Managing partner, formal portrait', 'measured-advocacy'); ?>" width="1600" height="1200" loading="lazy" class="profile-plate__image">
<div class="profile-plate__info">
<h3 class="profile-plate__name h3"><?php echo esc_html($managing_partner['name'] ?? '[Managing Partner Name]'); ?></h3>
<p class="profile-plate__role small"><?php echo esc_html($managing_partner['role'] ?? __('Managing Partner', 'measured-advocacy')); ?></p>
<p class="profile-plate__focus small" style="color: var(--color-slate);">
<?php echo esc_html($managing_partner['focus'] ?? __('Corporate governance, mergers, regulatory compliance', 'measured-advocacy')); ?>
</p>
</div>
<a href="<?php echo esc_url(home_url('/people/managing-partner')); ?>" class="profile-plate__link small">
<?php esc_html_e('View profile', 'measured-advocacy'); ?> →
</a>
</div>
</div>
</div>
</div>
</section>

<section class="principle section surface-limestone" aria-labelledby="principle-heading">
<div class="container">
<div class="principle__grid grid">
<div class="principle__image" style="grid-column: span 7;">
<img src="<?php echo esc_url(ma_img('office/img-002-approach.jpg')); ?>" alt="<?php esc_attr_e('Senior counsel in strategic working session', 'measured-advocacy'); ?>" width="1800" height="1200" loading="lazy" class="principle__img">
<p class="principle__caption small" style="margin-top: var(--space-3); color: var(--color-slate);">
<?php esc_html_e('Counsel reviewing position analysis during a strategy session — decisions are prepared through sustained attention, not improvised responses.', 'measured-advocacy'); ?>
</p>
</div>
<div class="principle__content" style="grid-column: span 5;">
<p class="principle__label small"><?php esc_html_e('How We Work', 'measured-advocacy'); ?></p>
<h2 id="principle-heading" class="principle__heading h2">
<?php esc_html_e('Every matter', 'measured-advocacy'); ?><br><?php esc_html_e('deserves a clear', 'measured-advocacy'); ?><br><?php esc_html_e('frame of reference.', 'measured-advocacy'); ?>
</h2>
<p class="principle__text body-l">
<?php esc_html_e('Before prescribing a course of action, we define what is actually at stake. We frame the situation, identify exposure, and only then apply legal expertise to the decision that matters. This discipline shapes every engagement.', 'measured-advocacy'); ?>
</p>
<a href="<?php echo esc_url(home_url('/about')); ?>" class="btn btn--text" style="margin-top: var(--space-5);">
<?php esc_html_e('About the firm', 'measured-advocacy'); ?> →
</a>
</div>
</div>
</div>
</section>

<?php
$default_insight = ma_default_insight();
?>

<section class="thinking section surface-paper" aria-labelledby="thinking-heading">
<div class="container">
<div class="thinking__grid grid">
<div class="thinking__content" style="grid-column: span 7;">
<p class="thinking__label small"><?php esc_html_e('Current Thinking', 'measured-advocacy'); ?></p>
<span class="thinking__type small" style="color: var(--color-copper);"><?php echo esc_html($default_insight['type'] ?? __('Decision Note', 'measured-advocacy')); ?></span>
<h2 id="thinking-heading" class="thinking__heading h2" style="margin-top: var(--space-3);">
<?php echo esc_html($default_insight['title'] ?? ''); ?>
</h2>
<p class="thinking__thesis body-l" style="margin-top: var(--space-4);">
<?php echo esc_html($default_insight['thesis'] ?? ''); ?>
</p>
<div class="thinking__meta" style="margin-top: var(--space-5);">
<span class="thinking__reading small" style="color: var(--color-slate);"><?php echo esc_html($default_insight['reading'] ?? __('8 min read', 'measured-advocacy')); ?></span>
<span class="thinking__divider small" style="color: var(--color-sage);" aria-hidden="true">·</span>
<time class="thinking__date small" style="color: var(--color-slate);" datetime="2026-07-15"><?php echo esc_html($default_insight['date'] ?? 'July 2026'); ?></time>
</div>
<a href="<?php echo esc_url(home_url('/insights')); ?>" class="btn btn--text" style="margin-top: var(--space-5);">
<?php esc_html_e('Read the full note', 'measured-advocacy'); ?> →
</a>
</div>
<div class="thinking__image" style="grid-column: span 5;">
<img src="<?php echo esc_url(ma_img('insights/img-014-decision-note.jpg')); ?>" alt="<?php esc_attr_e('Legal documents and regulatory references on desk', 'measured-advocacy'); ?>" width="1600" height="1200" loading="lazy" class="thinking__img">
</div>
</div>
</div>
</section>

<section class="next-step section surface-ink" aria-labelledby="next-heading">
<div class="container">
<div class="next-step__grid grid">
<div class="next-step__consult" style="grid-column: span 6;">
<h2 id="next-heading" class="next-step__heading h2" style="color: var(--color-limestone);">
<?php esc_html_e('Discuss a new matter', 'measured-advocacy'); ?>
</h2>
<p class="next-step__text body-l" style="color: rgba(255,255,255,0.75); margin-top: var(--space-4);">
<?php esc_html_e('Begin with a confidential consultation. We will listen to your situation, identify relevant expertise, and explain what counsel can offer before any commitment is required.', 'measured-advocacy'); ?>
</p>
<a href="<?php echo esc_url(home_url('/consultation')); ?>" class="btn btn--primary" style="margin-top: var(--space-5); padding: var(--space-3) var(--space-7); text-decoration: none;" id="final-cta-consult">
<?php echo esc_html(__('Request a Consultation', 'measured-advocacy')); ?>
</a>
</div>
<div class="next-step__contact" style="grid-column: span 6;">
<h3 class="next-step__contact-heading h3" style="color: var(--color-limestone);">
<?php esc_html_e('Reach the office', 'measured-advocacy'); ?>
</h3>
<div class="next-step__details" style="margin-top: var(--space-5);">
<p class="body" style="color: rgba(255,255,255,0.7);">
<span class="ltr-isolate"><?php echo esc_html(ma_firm('ma_phone')); ?></span>
</p>
<p class="body" style="color: rgba(255,255,255,0.7); margin-top: var(--space-2);">
<span class="ltr-isolate"><?php echo esc_html(ma_firm('ma_email')); ?></span>
</p>
<p class="body" style="color: rgba(255,255,255,0.7); margin-top: var(--space-2);">
<?php echo nl2br(esc_html(ma_firm('ma_address'))); ?>
</p>
</div>
<a href="<?php echo esc_url(home_url('/contact')); ?>" class="btn btn--secondary-light" style="margin-top: var(--space-5); padding: var(--space-3) var(--space-7); text-decoration: none;" id="final-cta-contact">
<?php esc_html_e('Contact Details & Directions', 'measured-advocacy'); ?> →
</a>
</div>
</div>
</div>
</section>

<?php get_footer(); ?>
