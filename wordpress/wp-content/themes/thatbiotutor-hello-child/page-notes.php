<?php
/* Template Name: Free Bio Notes */
declare(strict_types=1);
if (!defined('ABSPATH')) { exit; }
get_header();
$tbt_defaults = tbt_reference_content()['pages']['notes']['fields'];
$tbt_field = static fn (string $name): string => (string) tbt_page_field($name, $tbt_defaults[$name] ?? '');
$tbt_icon_download = '<svg class="tbt-resource-button__icon" viewBox="0 0 24 24" width="22" height="22" aria-hidden="true" focusable="false"><path d="M12 3v11m0 0 4.5-4.5M12 14 7.5 9.5M5 17v1.5A1.5 1.5 0 0 0 6.5 20h11a1.5 1.5 0 0 0 1.5-1.5V17" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"/></svg>';
$tbt_button = esc_html($tbt_field('notes_button_label')) . $tbt_icon_download; // the icon is static markup
$tbt_heading = esc_html($tbt_field('notes_heading'));
$tbt_heading = str_replace('Biology Notes', '<span>Biology Notes</span>', $tbt_heading);
?>
<article class="tbt-resource-page tbt-notes-page">
    <header class="tbt-resource-hero tbt-notes-hero" data-section="hero">
        <div class="tbt-resource-shell tbt-notes-hero__inner">
            <div class="tbt-notes-hero__copy">
                <h1><?php echo wp_kses($tbt_heading, ['span' => []]); ?></h1>
                <p class="tbt-notes-hero__audience"><?php echo esc_html($tbt_field('notes_audience')); ?></p>
                <?php if ($tbt_field('notes_hero_lead') !== '') : ?><p class="tbt-notes-hero__lead"><?php echo tbt_rich_inline($tbt_field('notes_hero_lead')); ?></p><?php endif; ?>
                <a class="tbt-resource-button tbt-notes-hero__download" href="#notes-form"><?php echo $tbt_button; // escaped label plus static icon ?></a>
                <?php if ($tbt_field('notes_proof_line') !== '') : ?><p class="tbt-resource-proof"><svg viewBox="0 0 24 24" width="16" height="16" aria-hidden="true" focusable="false"><path d="M12 4v9m0 0 3.5-3.5M12 13 8.5 9.5M5 16v2.5h14V16" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round"/></svg><?php echo esc_html($tbt_field('notes_proof_line')); ?></p><?php endif; ?>
            </div>
            <div class="tbt-resource-carousel tbt-resource-carousel--notes" data-carousel data-carousel-autoplay="7000">
                <div class="tbt-resource-carousel__viewport">
                    <?php foreach (range(1, 3) as $tbt_index) : $tbt_key = sprintf('curated_notes_notes_slide_%02d', $tbt_index); ?>
                        <figure class="tbt-resource-carousel__slide" data-carousel-slide<?php echo $tbt_index > 1 ? ' hidden' : ''; ?>>
                            <?php tbt_render_page_image(sprintf('notes_slider_image_%02d', $tbt_index), $tbt_key, '', ['loading' => $tbt_index === 1 ? 'eager' : 'lazy']); ?>
                        </figure>
                    <?php endforeach; ?>
                </div>
                <button type="button" class="tbt-resource-carousel__control tbt-resource-carousel__control--previous" data-carousel-prev aria-label="Previous notes preview">&#10094;</button>
                <button type="button" class="tbt-resource-carousel__control tbt-resource-carousel__control--next" data-carousel-next aria-label="Next notes preview">&#10095;</button>
                <div class="tbt-resource-carousel__dots" aria-label="Notes previews">
                    <?php foreach (range(1, 3) as $tbt_index) : ?><button type="button" data-carousel-dot aria-label="Show notes preview <?php echo esc_attr((string) $tbt_index); ?>"></button><?php endforeach; ?>
                </div>
                <p class="screen-reader-text" data-carousel-status aria-live="polite">1 of 3</p>
            </div>
        </div>
    </header>

    <section class="tbt-resource-section tbt-resource-reviews tbt-resource-reviews--lead" data-section="reviews-top">
        <div class="tbt-resource-shell">
            <h2><?php echo esc_html($tbt_field('notes_reviews_heading')); ?></h2>
            <a class="tbt-notes-skip-reviews" href="#notes-form">Skip testimonials <span aria-hidden="true">↓</span></a>
            <div data-testimonial-renderer data-review-panel><?php tbt_render_testimonials(['posts_per_page' => -1, 'meta_query' => [['key' => 'tbt_display_group', 'value' => 'notes-reviews', 'compare' => '=']]]); ?></div>
        </div>
    </section>

    <section class="tbt-resource-section tbt-notes-access-intro" id="notes-form" data-section="access-intro">
        <div class="tbt-resource-shell tbt-resource-narrow">
            <?php tbt_render_content_image('notes_portrait_image', 'tbt-resource-access-portrait', ['data-resource-portrait' => 'notes-access', 'loading' => 'lazy']); ?>
            <h2><?php echo esc_html($tbt_field('notes_access_heading')); ?></h2>
            <?php tbt_render_notes_form(); ?>
            <?php
            $tbt_note_rows = [];
            if (preg_match_all('~<p>(.*?)</p>~s', tbt_rich($tbt_field('notes_access_note')), $tbt_note_found) > 0) { $tbt_note_rows = $tbt_note_found[1]; }
            $tbt_first_row = '<strong>IMPORTANT:</strong> ' . esc_html($tbt_field('notes_access_copy')) . (isset($tbt_note_rows[0]) ? '<br>' . $tbt_note_rows[0] : '');
            $tbt_rows = array_merge([[$tbt_first_row, 'redirect']], array_map(static fn (string $row, int $i): array => [$row, $i === 0 ? 'mail' : 'help'], array_slice($tbt_note_rows, 1), array_keys(array_slice($tbt_note_rows, 1))));
            $tbt_row_icons = [
                'redirect' => '<svg viewBox="0 0 24 24" aria-hidden="true" focusable="false"><rect x="3" y="4.5" width="18" height="13" rx="2" fill="none" stroke="currentColor" stroke-width="1.8"/><path d="M3 8.5h18M9 21l-2.5-2.5L9 16M7 18.5h8a3 3 0 0 0 3-3" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>',
                'mail' => '<svg viewBox="0 0 24 24" aria-hidden="true" focusable="false"><rect x="3" y="5" width="18" height="14" rx="2" fill="none" stroke="currentColor" stroke-width="1.8"/><path d="m3.5 6.5 8.5 6.5 8.5-6.5" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>',
                'help' => '<svg viewBox="0 0 24 24" aria-hidden="true" focusable="false"><circle cx="12" cy="12" r="9" fill="none" stroke="currentColor" stroke-width="1.8"/><path d="M9.5 9.5a2.5 2.5 0 1 1 3.5 2.3c-.7.3-1 .8-1 1.6M12 16.6v.2" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round"/></svg>',
            ];
            ?>
            <div class="tbt-resource-access-instructions">
                <?php foreach ($tbt_rows as [$tbt_row_html, $tbt_row_icon]) : ?>
                    <div class="tbt-access-row"><span class="tbt-access-row__icon"><?php echo $tbt_row_icons[$tbt_row_icon]; // static icon markup ?></span><p><?php echo wp_kses($tbt_row_html, ['strong' => [], 'br' => [], 'a' => ['href' => [], 'target' => [], 'rel' => []], 'em' => [], 'u' => []]); ?></p></div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <section class="tbt-resource-section tbt-notes-fit" data-section="fit">
        <div class="tbt-resource-shell tbt-resource-narrow">
            <h2><?php echo esc_html($tbt_field('notes_fit_heading')); ?></h2>
            <?php echo tbt_rich_attrs(tbt_rich($tbt_field('notes_fit_list')), 'ul', 'class="tbt-notes-pain-list"'); ?>
            <?php $tbt_answer = $tbt_field('notes_fit_answer'); $tbt_answer_split = preg_match('/^(.+?[.!?])\s+(.+)$/su', $tbt_answer, $tbt_answer_parts) === 1; ?>
            <p class="tbt-notes-fit__answer"><?php echo $tbt_answer_split ? '<strong>' . esc_html($tbt_answer_parts[1]) . '</strong><span>' . esc_html($tbt_answer_parts[2]) . '</span>' : esc_html($tbt_answer); // escaped parts ?></p>
        </div>
    </section>

    <section class="tbt-resource-section tbt-notes-benefits" data-section="benefits">
        <div class="tbt-resource-shell">
            <h2><?php echo esc_html($tbt_field('notes_benefits_heading')); ?></h2>
            <div class="tbt-notes-benefits__grid">
                <?php foreach ([1, 2, 3, 4] as $tbt_n) : $tbt_benefit = $tbt_field('notes_benefit_' . $tbt_n . '_text'); ?>
                    <article class="tbt-notes-benefit" data-notes-benefit>
                        <div class="tbt-notes-benefit__media"><?php tbt_render_content_image('notes_benefit_' . $tbt_n . '_image', '', ['loading' => 'lazy']); ?></div>
                        <p><?php echo tbt_rich_inline($tbt_benefit); ?></p>
                    </article>
                <?php endforeach; ?>
            </div>
            <a class="tbt-resource-button" href="#notes-form"><?php echo $tbt_button; ?></a>
        </div>
    </section>

    <div class="tbt-notes-details">
        <div class="tbt-resource-shell">
            <header class="tbt-notes-details__intro">
                <h2><?php echo tbt_heading_html($tbt_field('notes_details_heading')); ?></h2>
                <?php if ($tbt_field('notes_details_intro') !== '') : ?><p><?php echo esc_html($tbt_field('notes_details_intro')); ?></p><?php endif; ?>
            </header>
            <div class="tbt-notes-details__grid">
                <section class="tbt-notes-card" data-section="why-free">
                    <span class="tbt-notes-card__icon" aria-hidden="true"><svg viewBox="0 0 48 48" focusable="false"><path d="M8 20h32v22H8zM6 14h36v6H6zM24 14v28M24 14c-2-6-9-9-11-5s4 5 11 5c7 0 13-1 11-5s-9-1-11 5z" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"/></svg></span>
                    <h3><?php echo tbt_heading_html($tbt_field('notes_why_heading')); ?></h3>
                    <?php echo tbt_rich($tbt_field('notes_why_list')); ?>
                </section>
                <section class="tbt-notes-card" data-section="catch">
                    <span class="tbt-notes-card__icon" aria-hidden="true"><?php tbt_render_media_image('curated_notes_icon_handshake', '', ['loading' => 'lazy']); ?></span>
                    <h3><?php echo tbt_heading_html($tbt_field('notes_catch_heading')); ?></h3>
                    <?php echo tbt_rich($tbt_field('notes_catch_list')); ?>
                </section>
                <section class="tbt-notes-card tbt-notes-card--wide tbt-notes-download" id="notes-download" data-section="download">
                    <span class="tbt-notes-card__icon" aria-hidden="true"><svg viewBox="0 0 48 48" focusable="false"><path d="M24 6v24m0 0 9-9m-9 9-9-9M8 30v8a4 4 0 0 0 4 4h24a4 4 0 0 0 4-4v-8" fill="none" stroke="currentColor" stroke-width="3.4" stroke-linecap="round" stroke-linejoin="round"/></svg></span>
                    <div class="tbt-notes-card__body">
                        <h3><?php echo tbt_heading_html($tbt_field('notes_download_heading')); ?></h3>
                        <ul>
                            <li><?php echo esc_html($tbt_field('notes_download_copy')); ?></li>
                            <li><?php echo esc_html($tbt_field('notes_download_note')); ?></li>
                        </ul>
                        <a class="tbt-resource-button" href="#notes-form"><?php echo $tbt_button; // escaped label plus static icon ?></a>
                    </div>
                </section>
            </div>
        </div>
    </div>

    <section class="tbt-resource-section tbt-resource-reviews" data-section="reviews-bottom">
        <div class="tbt-resource-shell">
            <h2><?php echo esc_html($tbt_field('notes_reviews_heading')); ?></h2>
            <a class="tbt-notes-skip-reviews" href="#notes-reviews-end">Skip testimonials <span aria-hidden="true">↓</span></a>
            <div data-testimonial-renderer data-review-panel><?php tbt_render_testimonials(['posts_per_page' => -1, 'meta_query' => [['key' => 'tbt_display_group', 'value' => 'notes-reviews-bottom', 'compare' => '=']]]); ?></div>
            <a id="notes-reviews-end" class="tbt-resource-button tbt-notes-end-button" href="#notes-form"><?php echo $tbt_button; ?></a>
        </div>
    </section>
</article>
<?php get_footer(); ?>
