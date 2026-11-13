<?php
/* Template Name: Results & Testimonials */
declare(strict_types=1);
if (!defined('ABSPATH')) { exit; }
get_header();
$tbt_defaults = tbt_reference_content()['pages']['testimonials']['fields'];
$tbt_field = static fn (string $name): string => (string) tbt_page_field($name, $tbt_defaults[$name] ?? '');
$tbt_whatsapp = tbt_whatsapp_url('Hi ThatBioTutor, I would like to enquire about a trial lesson.');
$tbt_reviews_heading = esc_html($tbt_field('testimonials_reviews_heading'));
$tbt_reviews_heading = str_replace('Students Are Saying', '<span>Students Are Saying</span>', $tbt_reviews_heading);
$tbt_review_portraits = json_decode((string) file_get_contents(get_stylesheet_directory() . '/assets/content/testimonial-review-portraits.json'), true) ?: [];
$tbt_group_args = static fn (string $year): array => [
    'posts_per_page' => -1,
    'review_presentation' => true,
    'fallback_portraits' => $tbt_review_portraits[$year] ?? [],
    'meta_query' => [['key' => 'tbt_display_group', 'value' => $year, 'compare' => '=']],
];
$tbt_growth_icon = static function (): void {
    ?>
    <svg class="tbt-testimonials-icon" viewBox="0 0 120 100" aria-hidden="true" focusable="false">
        <rect x="6" y="52" width="20" height="40" rx="3" fill="#ff7a1a"/>
        <rect x="34" y="44" width="20" height="48" rx="3" fill="#ff9d0a"/>
        <rect x="62" y="30" width="20" height="62" rx="3" fill="#ffb300"/>
        <rect x="90" y="14" width="20" height="78" rx="3" fill="#ffc400"/>
        <path d="M4 40C30 40 60 28 96 7" fill="none" stroke="#17c964" stroke-width="7" stroke-linecap="round"/>
        <path d="M82 6l22-3-4 22" fill="none" stroke="#17c964" stroke-width="7" stroke-linecap="round" stroke-linejoin="round"/>
        <rect x="2" y="92" width="112" height="6" rx="3" fill="#5a3b2e"/>
    </svg>
    <?php
};
$tbt_star = '<svg viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="M12 1.8l3.2 6.5 7.2 1-5.2 5.1 1.2 7.1L12 18.1l-6.4 3.4 1.2-7.1L1.6 9.3l7.2-1z" fill="url(#tbt-star-gold)" stroke="#e8a300" stroke-width="1" stroke-linejoin="round"/></svg>';
$tbt_story_heading = static function (bool $show_advice = true): void {
    ?>
    <div class="tbt-story-heading">
        <h3>Student <span>Stories!</span></h3>
        <?php if ($show_advice) : ?><p>(Watch to the End for Their Advice to Future O Level Batches!)</p><?php endif; ?>
    </div>
    <?php
};
?>
<article class="tbt-testimonials-page">
    <div class="tbt-year-nav" data-year-nav hidden>
        <button class="tbt-year-nav__toggle" type="button" aria-expanded="false" aria-controls="tbt-year-links">
            <span aria-hidden="true">☰</span> Years <span data-year-current>2025</span><span class="tbt-year-nav__chevron" aria-hidden="true">⌄</span>
        </button>
        <nav class="tbt-year-nav__panel" id="tbt-year-links" aria-label="Testimonial years" hidden>
            <p class="tbt-year-nav__label">Jump to year</p>
            <?php
            // Headline result of each class, as shown in that year's own result pictures further down the page.
            $tbt_year_results = [
                '2025' => '86% scored B3 and above · 46% scored A1/A2',
                '2024' => '94% scored As and Bs · 62% scored A1/A2',
                '2023' => '92% scored As and Bs · 69% scored A1/A2',
                '2022' => '64% scored A1/A2 (Distinction)',
                '2021' => '100% improved their grade in 6 months or less',
            ];
            foreach ($tbt_year_results as $tbt_year => $tbt_year_summary) : $tbt_year = (string) $tbt_year; ?>
                <a href="#class-of-<?php echo esc_attr($tbt_year); ?>"<?php echo $tbt_year === '2025' ? ' aria-current="location"' : ''; ?>>
                    <span class="tbt-year-nav__title">Class of <?php echo esc_html($tbt_year); ?></span>
                    <span class="tbt-year-nav__summary"><?php foreach (explode(' · ', $tbt_year_summary) as $tbt_year_line) : ?><span><?php echo esc_html($tbt_year_line); ?></span> <?php endforeach; ?></span>
                </a>
            <?php endforeach; ?>
        </nav>
    </div>
    <header class="tbt-testimonials-hero" data-section="hero">
        <div class="tbt-testimonials-hero__background"><?php tbt_render_media_image('curated_heroes_testimonials_original', '', ['loading' => 'eager']); ?></div>
        <div class="tbt-testimonials-hero__content"><h1><?php echo esc_html($tbt_field('testimonials_heading')); ?></h1><p class="tbt-testimonials-hero__stars" role="img" aria-label="Five out of five stars"><svg width="0" height="0" aria-hidden="true" focusable="false" style="position:absolute"><defs><linearGradient id="tbt-star-gold" x1="0" y1="0" x2="0" y2="1"><stop offset="0" stop-color="#ffe27a"/><stop offset="1" stop-color="#ffb81c"/></linearGradient></defs></svg><?php echo str_repeat($tbt_star, 5); // static inline SVG markup ?></p></div>
    </header>

    <section class="tbt-testimonials-featured" data-section="featured">
        <div class="tbt-testimonials-container">
            <h2><?php echo wp_kses($tbt_reviews_heading, ['span' => []]); ?></h2>
            <div class="tbt-video tbt-video--testimonial"><iframe src="https://www.youtube-nocookie.com/embed/Vkzlr16AGvo" title="ThatBioTutor Exposed: What Students Are Saying" loading="lazy" scrolling="no" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" referrerpolicy="strict-origin-when-cross-origin" allowfullscreen></iframe></div>
            <?php if ($tbt_whatsapp !== '') : ?><a class="tbt-testimonials-button" href="<?php echo esc_url($tbt_whatsapp); ?>" target="_blank" rel="noopener noreferrer">WhatsApp Us</a><?php endif; ?>
        </div>
    </section>

    <section class="tbt-testimonials-year" id="class-of-2025" data-section="2025" data-archive-group>
        <div class="tbt-testimonials-container">
            <div class="tbt-testimonials-year-panel"><h2>Class of 2025</h2></div>
            <div class="tbt-testimonials-artwork tbt-testimonials-artwork--portrait"><?php tbt_render_media_image('curated_testimonials_results_2025_01', '', ['loading' => 'lazy']); ?><?php tbt_render_media_image('curated_testimonials_results_2025_02', '', ['loading' => 'lazy']); ?></div>
            <?php $tbt_story_heading(); ?>
            <div data-review-panel><?php tbt_render_testimonials($tbt_group_args('2025')); ?></div>
        </div>
    </section>

    <section class="tbt-testimonials-year" id="class-of-2024" data-section="2024" data-archive-group>
        <div class="tbt-testimonials-container">
            <div class="tbt-testimonials-year-panel"><h2>Class of 2024</h2></div>
            <div class="tbt-testimonials-artwork tbt-testimonials-artwork--portrait"><?php tbt_render_media_image('curated_testimonials_results_2024_reviews', '', ['loading' => 'lazy']); ?><?php tbt_render_media_image('curated_testimonials_results_2024_stats', '', ['loading' => 'lazy']); ?></div>
        </div>
        <div class="tbt-testimonials-artwork-band"><div class="tbt-testimonials-container"><div class="tbt-testimonials-artwork tbt-testimonials-artwork--landscape"><?php tbt_render_media_image('curated_testimonials_results_2024_01', '', ['loading' => 'lazy']); ?><?php tbt_render_media_image('curated_testimonials_results_2024_02', '', ['loading' => 'lazy']); ?></div></div></div>
        <div class="tbt-testimonials-container">
            <?php $tbt_story_heading(); ?>
            <div data-review-panel><?php tbt_render_testimonials($tbt_group_args('2024')); ?></div>
        </div>
    </section>

    <section class="tbt-testimonials-year" id="class-of-2023" data-section="2023" data-archive-group>
        <div class="tbt-testimonials-container">
            <div class="tbt-testimonials-year-panel"><h2>Class of 2023</h2></div>
            <div class="tbt-testimonials-artwork tbt-testimonials-artwork--portrait"><?php tbt_render_media_image('curated_testimonials_results_2023_01', '', ['loading' => 'lazy']); ?><?php tbt_render_media_image('curated_testimonials_results_2023_02', '', ['loading' => 'lazy']); ?></div>
        </div>
        <div class="tbt-testimonials-artwork-band"><div class="tbt-testimonials-container"><div class="tbt-testimonials-artwork tbt-testimonials-artwork--landscape"><?php tbt_render_media_image('curated_testimonials_results_2023_03', '', ['loading' => 'lazy']); ?></div></div></div>
        <div class="tbt-testimonials-container">
            <?php $tbt_story_heading(); ?>
            <div data-review-panel><?php tbt_render_testimonials($tbt_group_args('2023')); ?></div>
        </div>
    </section>

    <section class="tbt-testimonials-year" id="class-of-2022" data-section="2022" data-archive-group>
        <div class="tbt-testimonials-container"><div class="tbt-testimonials-year-panel"><h2>Class of 2022</h2><?php $tbt_growth_icon(); ?></div></div>
        <div class="tbt-testimonials-artwork-band tbt-testimonials-artwork-band--2022"><div class="tbt-testimonials-container">
            <div class="tbt-testimonials-artwork tbt-testimonials-artwork--landscape"><?php tbt_render_media_image('curated_testimonials_results_2022_01', '', ['loading' => 'lazy']); ?></div>
        </div></div>
        <div class="tbt-testimonials-container">
            <?php $tbt_story_heading(); ?>
            <div data-review-panel><?php tbt_render_testimonials($tbt_group_args('2022')); ?></div>
            <?php if ($tbt_whatsapp !== '') : ?><a class="tbt-testimonials-button tbt-testimonials-button--archive" href="<?php echo esc_url($tbt_whatsapp); ?>" target="_blank" rel="noopener noreferrer">WhatsApp Us Now</a><?php endif; ?>
        </div>
    </section>

    <section class="tbt-testimonials-year" id="class-of-2021" data-section="2021" data-archive-group>
        <div class="tbt-testimonials-results-band"><div class="tbt-testimonials-container">
            <h2>Class of 2021</h2>
            <?php $tbt_growth_icon(); ?>
            <p>100% of our Class of 2021 improved their grade in 6 months or less</p>
        </div></div>
        <div class="tbt-testimonials-container">
            <?php $tbt_story_heading(false); ?>
            <div data-review-panel><?php tbt_render_testimonials($tbt_group_args('2021')); ?></div>
            <?php if ($tbt_whatsapp !== '') : ?><a class="tbt-testimonials-button tbt-testimonials-button--archive" href="<?php echo esc_url($tbt_whatsapp); ?>" target="_blank" rel="noopener noreferrer">WhatsApp Us Now</a><?php endif; ?>
        </div>
    </section>
</article>
<?php get_footer(); ?>
