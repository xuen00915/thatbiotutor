<?php
/* Template Name: Cell Division Crash Course */
declare(strict_types=1);
if (!defined('ABSPATH')) { exit; }
get_header();
$tbt_defaults = tbt_reference_content()['pages']['celldivision']['fields'];
$tbt_field = static fn (string $name): string => (string) tbt_page_field($name, $tbt_defaults[$name] ?? '');
?>
<article class="tbt-resource-page tbt-cell-page">
    <header class="tbt-resource-hero tbt-cell-hero" data-section="hero">
        <div class="tbt-resource-shell tbt-resource-hero__inner">
            <h1><?php echo tbt_heading_html($tbt_field('celldivision_title')); ?></h1>
            <p class="tbt-cell-hero__notice"><?php echo esc_html($tbt_field('celldivision_notice')); ?></p>
            <div class="tbt-resource-carousel tbt-resource-carousel--cell" data-carousel data-carousel-autoplay="7000">
                <div class="tbt-resource-carousel__viewport">
                    <?php foreach (range(1, 5) as $tbt_index) : $tbt_key = sprintf('curated_celldivision_slide_%02d', $tbt_index); ?>
                        <figure class="tbt-resource-carousel__slide" data-carousel-slide<?php echo $tbt_index > 1 ? ' hidden' : ''; ?>>
                            <?php tbt_render_page_image(sprintf('celldivision_slider_image_%02d', $tbt_index), $tbt_key, '', ['loading' => $tbt_index === 1 ? 'eager' : 'lazy']); ?>
                        </figure>
                    <?php endforeach; ?>
                </div>
                <button type="button" class="tbt-resource-carousel__control tbt-resource-carousel__control--previous" data-carousel-prev aria-label="Previous crash-course preview">&#10094;</button>
                <button type="button" class="tbt-resource-carousel__control tbt-resource-carousel__control--next" data-carousel-next aria-label="Next crash-course preview">&#10095;</button>
                <div class="tbt-resource-carousel__dots" aria-label="Crash-course previews">
                    <?php foreach (range(1, 5) as $tbt_index) : ?><button type="button" data-carousel-dot aria-label="Show crash-course preview <?php echo esc_attr((string) $tbt_index); ?>"></button><?php endforeach; ?>
                </div>
                <p class="screen-reader-text" data-carousel-status aria-live="polite">1 of 5</p>
            </div>
            <a class="tbt-resource-button" href="#cell-access"><?php echo esc_html($tbt_field('celldivision_button_label')); ?></a>
            <?php if ($tbt_field('celldivision_proof_line') !== '') : ?><p class="tbt-resource-proof"><?php echo esc_html($tbt_field('celldivision_proof_line')); ?></p><?php endif; ?>
        </div>
    </header>

    <section class="tbt-resource-section tbt-cell-problems" data-section="problems">
        <div class="tbt-resource-shell tbt-resource-narrow">
            <h2><?php echo tbt_rich_inline($tbt_field('celldivision_problems_heading')); ?></h2>
            <?php echo tbt_rich_attrs(tbt_rich($tbt_field('celldivision_problems_list')), 'ul', 'class="tbt-cell-problem-list"'); ?>
            <h3><?php echo tbt_rich_inline($tbt_field('celldivision_content_heading')); ?></h3>
            <?php echo tbt_rich_attrs(tbt_rich($tbt_field('celldivision_content_list')), 'ul', 'class="tbt-cell-content-list"'); ?>
            <p class="tbt-cell-problems__answer"><?php echo tbt_rich_inline($tbt_field('celldivision_answer')); ?></p>
        </div>
    </section>

    <section class="tbt-resource-section tbt-cell-features" data-section="features">
        <div class="tbt-resource-shell">
            <div class="tbt-cell-features__grid">
                <?php foreach ([1, 2, 3] as $tbt_n) : ?>
                    <article class="tbt-cell-feature">
                        <?php tbt_render_content_image('celldivision_feature_' . $tbt_n . '_image', '', ['loading' => 'lazy']); ?>
                        <h3><?php echo esc_html($tbt_field('celldivision_feature_' . $tbt_n . '_title')); ?></h3>
                        <p><?php echo esc_html($tbt_field('celldivision_feature_' . $tbt_n . '_text')); ?></p>
                    </article>
                <?php endforeach; ?>
            </div>
            <a class="tbt-resource-button" href="#cell-access"><?php echo esc_html($tbt_field('celldivision_features_button')); ?></a>
        </div>
    </section>

    <section class="tbt-resource-section tbt-cell-important" data-section="important">
        <div class="tbt-resource-shell tbt-resource-narrow">
            <h2><?php echo esc_html($tbt_field('celldivision_important_heading')); ?></h2>
            <?php echo tbt_rich($tbt_field('celldivision_important_list')); ?>
            <p class="tbt-resource-disclosure"><?php echo esc_html($tbt_field('celldivision_disclosure')); ?></p>
            <h3><?php echo tbt_heading_html($tbt_field('celldivision_access_heading')); ?></h3>
            <?php echo tbt_rich($tbt_field('celldivision_access_list')); ?>
        </div>
    </section>

    <section class="tbt-cell-access" id="cell-access" data-section="access">
        <div class="tbt-resource-section tbt-cell-access__promise">
            <div class="tbt-resource-shell tbt-resource-narrow">
                <?php tbt_render_content_image('celldivision_portrait_image', 'tbt-resource-access-portrait', ['data-resource-portrait' => 'cell-access', 'loading' => 'lazy']); ?>
                <h3><?php echo esc_html($tbt_field('celldivision_promise')); ?></h3>
                <a class="tbt-resource-button" href="<?php echo esc_url(home_url('/contact/')); ?>"><?php echo esc_html($tbt_field('celldivision_contact_button')); ?></a>
            </div>
        </div>
        <div class="tbt-resource-section tbt-cell-access__instructions">
            <div class="tbt-resource-shell tbt-resource-narrow">
                <?php echo tbt_rich($tbt_field('celldivision_instructions')); ?>
            </div>
        </div>
    </section>

    <section class="tbt-resource-section tbt-resource-reviews tbt-cell-reviews" data-section="reviews">
        <div class="tbt-resource-shell">
            <h2><?php echo esc_html($tbt_field('celldivision_reviews_heading')); ?></h2>
            <p class="tbt-cell-reviews__link"><a href="<?php echo esc_url(home_url('/contact/')); ?>"><?php echo esc_html($tbt_field('celldivision_reviews_link')); ?></a></p>
            <div data-testimonial-renderer><?php tbt_render_testimonials(['posts_per_page' => -1, 'meta_query' => [['key' => 'tbt_display_group', 'value' => 'celldivision-reviews', 'compare' => '=']]]); ?></div>
        </div>
    </section>
</article>
<?php get_footer(); ?>
