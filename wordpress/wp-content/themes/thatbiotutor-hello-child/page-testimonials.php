<?php
/* Template Name: Results & Testimonials */
declare(strict_types=1);
if (!defined('ABSPATH')) { exit; }
get_header();
$tbt_defaults = tbt_reference_content()['pages']['testimonials']['fields'];
$tbt_field = static fn (string $name): string => (string) tbt_page_field($name, $tbt_defaults[$name] ?? '');
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
$tbt_story_heading = static function (bool $show_advice, string $year) use ($tbt_field): void {
    ?>
    <div class="tbt-story-heading" id="class-of-<?php echo esc_attr($year); ?>-stories">
        <h3><?php echo tbt_accent_last_word($tbt_field('testimonials_stories_heading')); ?></h3>
        <?php if ($show_advice && $tbt_field('testimonials_stories_advice') !== '') : ?><p><?php echo esc_html($tbt_field('testimonials_stories_advice')); ?></p><?php endif; ?>
    </div>
    <?php
};
$tbt_whatsapp = tbt_whatsapp_url($tbt_field('testimonials_whatsapp_message'));
$tbt_years = tbt_get_class_years();
?>
<article class="tbt-testimonials-page">
    <div class="tbt-year-nav" data-year-nav hidden>
        <button class="tbt-year-nav__toggle" type="button" aria-expanded="false" aria-controls="tbt-year-links">
            <span aria-hidden="true">☰</span> Years <span data-year-current><?php echo esc_html($tbt_years[0]['year'] ?? ''); ?></span><span class="tbt-year-nav__chevron" aria-hidden="true">⌄</span>
        </button>
        <nav class="tbt-year-nav__panel" id="tbt-year-links" aria-label="Testimonial years" hidden>
            <p class="tbt-year-nav__label">Jump to year</p>
            <?php
            foreach ($tbt_years as $tbt_index => $tbt_item) : $tbt_year = $tbt_item['year']; $tbt_year_parts = tbt_class_year_menu_parts($tbt_item); $tbt_first = $tbt_index === 0; ?>
                <div class="tbt-year-nav__group" data-open="<?php echo $tbt_first ? 'true' : 'false'; ?>">
                    <div class="tbt-year-nav__row">
                        <a class="tbt-year-nav__year" href="#class-of-<?php echo esc_attr($tbt_year); ?>"<?php echo $tbt_first ? ' aria-current="location"' : ''; ?>>
                            <span class="tbt-year-nav__title"><?php echo esc_html($tbt_item['heading']); ?></span>
                        </a>
                        <button class="tbt-year-nav__sections-toggle" type="button" aria-expanded="<?php echo $tbt_first ? 'true' : 'false'; ?>" aria-controls="tbt-year-sections-<?php echo esc_attr($tbt_year); ?>" aria-label="<?php echo esc_attr($tbt_item['heading']); ?>: show or hide its sections"><span aria-hidden="true">⌄</span></button>
                    </div>
                    <ul class="tbt-year-nav__sections" id="tbt-year-sections-<?php echo esc_attr($tbt_year); ?>">
                        <?php foreach ($tbt_year_parts as $tbt_part => $tbt_part_label) : ?>
                            <li><a class="tbt-year-nav__section" href="#class-of-<?php echo esc_attr($tbt_year . '-' . $tbt_part); ?>"><?php echo esc_html($tbt_part_label); ?></a></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endforeach; ?>
        </nav>
    </div>
    <header class="tbt-testimonials-hero" data-section="hero">
        <div class="tbt-testimonials-hero__background"><?php tbt_render_content_image('testimonials_hero_image', '', ['loading' => 'eager']); ?></div>
        <div class="tbt-testimonials-hero__content"><h1><?php echo esc_html($tbt_field('testimonials_heading')); ?></h1><p class="tbt-testimonials-hero__stars" role="img" aria-label="Five out of five stars"><svg width="0" height="0" aria-hidden="true" focusable="false" style="position:absolute"><defs><linearGradient id="tbt-star-gold" x1="0" y1="0" x2="0" y2="1"><stop offset="0" stop-color="#ffe27a"/><stop offset="1" stop-color="#ffb81c"/></linearGradient></defs></svg><?php echo str_repeat($tbt_star, 5); // static inline SVG markup ?></p></div>
    </header>

    <section class="tbt-testimonials-featured" data-section="featured">
        <div class="tbt-testimonials-container">
            <h2><?php echo wp_kses($tbt_reviews_heading, ['span' => []]); ?></h2>
            <?php $tbt_featured_video = tbt_testimonial_embed_url($tbt_field('testimonials_video_url')); if ($tbt_featured_video !== '') : ?><div class="tbt-video tbt-video--testimonial"><iframe src="<?php echo esc_url($tbt_featured_video); ?>" title="<?php echo esc_attr($tbt_field('testimonials_video_title')); ?>" loading="lazy" scrolling="no" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" referrerpolicy="strict-origin-when-cross-origin" allowfullscreen></iframe></div><?php endif; ?>
            <?php if ($tbt_whatsapp !== '' && $tbt_field('testimonials_button_label') !== '') : ?><a class="tbt-testimonials-button" href="<?php echo esc_url($tbt_whatsapp); ?>" target="_blank" rel="noopener noreferrer"><?php echo esc_html($tbt_field('testimonials_button_label')); ?></a><?php endif; ?>
        </div>
    </section>

    <?php foreach ($tbt_years as $tbt_item) :
        $tbt_year = $tbt_item['year'];
        $tbt_open = false; // whether a width container is currently open ?>
    <section class="tbt-testimonials-year" id="class-of-<?php echo esc_attr($tbt_year); ?>" data-section="<?php echo esc_attr($tbt_year); ?>" data-archive-group>
        <?php if ($tbt_item['summary'] !== '') : ?>
        <div class="tbt-testimonials-results-band"><div class="tbt-testimonials-container">
            <h2><?php echo esc_html($tbt_item['heading']); ?></h2>
            <?php if ($tbt_item['growth_icon']) { $tbt_growth_icon(); } ?>
            <p><?php echo esc_html($tbt_item['summary']); ?></p>
        </div></div>
        <?php else : $tbt_open = true; ?>
        <div class="tbt-testimonials-container">
            <div class="tbt-testimonials-year-panel"><h2><?php echo esc_html($tbt_item['heading']); ?></h2><?php if ($tbt_item['growth_icon']) { $tbt_growth_icon(); } ?></div>
        <?php endif; ?>
        <?php if ($tbt_item['posters'] !== []) : if (!$tbt_open) { $tbt_open = true; echo '<div class="tbt-testimonials-container">'; } ?>
            <div class="tbt-testimonials-artwork tbt-testimonials-artwork--portrait" id="class-of-<?php echo esc_attr($tbt_year); ?>-poster"><?php tbt_render_class_year_pictures($tbt_item['posters']); ?></div>
        <?php endif; ?>
        <?php if ($tbt_item['tables'] !== []) : if ($tbt_open) { $tbt_open = false; echo '</div>'; } ?>
        <div class="tbt-testimonials-artwork-band tbt-testimonials-artwork-band--<?php echo esc_attr($tbt_year); ?>"><div class="tbt-testimonials-container"><div class="tbt-testimonials-artwork tbt-testimonials-artwork--landscape" id="class-of-<?php echo esc_attr($tbt_year); ?>-table"><?php tbt_render_class_year_pictures($tbt_item['tables']); ?></div></div></div>
        <?php endif; ?>
        <?php if (!$tbt_open) { $tbt_open = true; echo '<div class="tbt-testimonials-container">'; } ?>
            <?php $tbt_story_heading($tbt_item['show_advice'], $tbt_year); ?>
            <div data-review-panel><?php tbt_render_testimonials($tbt_group_args($tbt_year)); ?></div>
            <?php if ($tbt_item['whatsapp_button'] && $tbt_whatsapp !== '' && $tbt_field('testimonials_archive_button_label') !== '') : ?><a class="tbt-testimonials-button tbt-testimonials-button--archive" href="<?php echo esc_url($tbt_whatsapp); ?>" target="_blank" rel="noopener noreferrer"><?php echo esc_html($tbt_field('testimonials_archive_button_label')); ?></a><?php endif; ?>
        </div>
    </section>
    <?php endforeach; ?>
</article>
<?php get_footer(); ?>
