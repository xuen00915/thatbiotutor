<?php
/* Template Name: Free Bio Notes */
declare(strict_types=1);
if (!defined('ABSPATH')) { exit; }
get_header();
$tbt_defaults = tbt_reference_content()['pages']['notes']['fields'];
$tbt_field = static fn (string $name): string => (string) tbt_page_field($name, $tbt_defaults[$name] ?? '');
$tbt_button = esc_html($tbt_field('notes_button_label'));
?>
<article class="tbt-resource-page tbt-notes-page">
    <header class="tbt-resource-hero tbt-notes-hero" data-section="hero">
        <div class="tbt-resource-shell tbt-resource-hero__inner">
            <h1><?php echo esc_html($tbt_field('notes_heading')); ?></h1>
            <p class="tbt-notes-hero__audience"><?php
                $tbt_audience = $tbt_field('notes_audience');
                if (preg_match('/^(For\s+)(.+)$/i', $tbt_audience, $tbt_parts) === 1) {
                    echo esc_html($tbt_parts[1]) . '<span>' . esc_html($tbt_parts[2]) . '</span>';
                } else {
                    echo esc_html($tbt_audience);
                }
            ?></p>
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
            <a class="tbt-resource-button" href="#notes-form"><?php echo $tbt_button; ?></a>
            <?php if ($tbt_field('notes_proof_line') !== '') : ?><p class="tbt-resource-proof"><?php echo esc_html($tbt_field('notes_proof_line')); ?></p><?php endif; ?>
        </div>
    </header>

    <section class="tbt-resource-section tbt-resource-reviews tbt-resource-reviews--lead" data-section="reviews-top">
        <div class="tbt-resource-shell">
            <h2><?php echo esc_html($tbt_field('notes_reviews_heading')); ?></h2>
            <div data-testimonial-renderer data-review-panel><?php tbt_render_testimonials(['posts_per_page' => -1, 'meta_query' => [['key' => 'tbt_display_group', 'value' => 'notes-reviews', 'compare' => '=']]]); ?></div>
        </div>
    </section>

    <section class="tbt-resource-section tbt-notes-access-intro" id="notes-form" data-section="access-intro">
        <div class="tbt-resource-shell tbt-resource-narrow">
            <?php tbt_render_content_image('notes_portrait_image', 'tbt-resource-access-portrait', ['data-resource-portrait' => 'notes-access', 'loading' => 'lazy']); ?>
            <h2><?php echo esc_html($tbt_field('notes_access_heading')); ?></h2>
            <?php tbt_render_notes_form(); ?>
            <div class="tbt-resource-access-instructions">
                <p><strong>IMPORTANT:</strong> <?php echo esc_html($tbt_field('notes_access_copy')); ?></p>
                <?php echo tbt_rich($tbt_field('notes_access_note')); ?>
            </div>
        </div>
    </section>

    <section class="tbt-resource-section tbt-notes-fit" data-section="fit">
        <div class="tbt-resource-shell tbt-resource-narrow">
            <h2><?php echo esc_html($tbt_field('notes_fit_heading')); ?></h2>
            <?php echo tbt_rich_attrs(tbt_rich($tbt_field('notes_fit_list')), 'ul', 'class="tbt-notes-pain-list"'); ?>
            <p class="tbt-notes-fit__answer"><?php echo esc_html($tbt_field('notes_fit_answer')); ?></p>
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
        <section class="tbt-resource-section tbt-resource-faq-block" data-section="why-free">
            <div class="tbt-resource-shell tbt-resource-narrow">
                <h2><?php echo tbt_heading_html($tbt_field('notes_why_heading')); ?></h2>
                <?php echo tbt_rich($tbt_field('notes_why_list')); ?>
            </div>
        </section>

        <section class="tbt-resource-section tbt-resource-faq-block" data-section="catch">
            <div class="tbt-resource-shell tbt-resource-narrow">
                <h2><?php echo tbt_heading_html($tbt_field('notes_catch_heading')); ?></h2>
                <?php echo tbt_rich($tbt_field('notes_catch_list')); ?>
            </div>
        </section>

        <section class="tbt-resource-section tbt-notes-download" id="notes-download" data-section="download">
            <div class="tbt-resource-shell tbt-resource-narrow">
                <h2><?php echo tbt_heading_html($tbt_field('notes_download_heading')); ?></h2>
                <ul>
                    <li><?php echo esc_html($tbt_field('notes_download_copy')); ?></li>
                    <li><?php echo esc_html($tbt_field('notes_download_note')); ?></li>
                </ul>
                <a class="tbt-resource-button" href="#notes-form"><?php echo $tbt_button; ?></a>
            </div>
        </section>
    </div>

    <section class="tbt-resource-section tbt-resource-reviews" data-section="reviews-bottom">
        <div class="tbt-resource-shell">
            <h2><?php echo esc_html($tbt_field('notes_reviews_heading')); ?></h2>
            <div data-testimonial-renderer data-review-panel><?php tbt_render_testimonials(['posts_per_page' => -1, 'meta_query' => [['key' => 'tbt_display_group', 'value' => 'notes-reviews-bottom', 'compare' => '=']]]); ?></div>
            <a class="tbt-resource-button tbt-notes-end-button" href="#notes-form"><?php echo $tbt_button; ?></a>
        </div>
    </section>
</article>
<?php get_footer(); ?>
