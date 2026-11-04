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
$tbt_group_args = static fn (string $year): array => [
    'posts_per_page' => -1,
    'meta_query' => [['key' => 'tbt_display_group', 'value' => $year, 'compare' => '=']],
];
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
    <header class="tbt-testimonials-hero" data-section="hero">
        <div class="tbt-testimonials-hero__background"><?php tbt_render_media_image('curated_heroes_testimonials_original', '', ['loading' => 'eager']); ?></div>
        <div class="tbt-testimonials-hero__content"><h1><?php echo esc_html($tbt_field('testimonials_heading')); ?></h1><p aria-label="Five out of five stars">⭐⭐⭐⭐⭐</p></div>
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
            <h2 class="screen-reader-text">Class of 2025</h2>
            <div class="tbt-testimonials-artwork tbt-testimonials-artwork--portrait"><?php tbt_render_media_image('curated_testimonials_results_2025_01', '', ['loading' => 'lazy']); ?><?php tbt_render_media_image('curated_testimonials_results_2025_02', '', ['loading' => 'lazy']); ?></div>
            <?php $tbt_story_heading(); ?>
            <?php tbt_render_testimonials($tbt_group_args('2025')); ?>
        </div>
    </section>

    <section class="tbt-testimonials-year" id="class-of-2024" data-section="2024" data-archive-group>
        <div class="tbt-testimonials-container">
            <h2 class="screen-reader-text">Class of 2024</h2>
            <div class="tbt-testimonials-artwork tbt-testimonials-artwork--portrait"><?php tbt_render_media_image('curated_testimonials_results_2024_reviews', '', ['loading' => 'lazy']); ?><?php tbt_render_media_image('curated_testimonials_results_2024_stats', '', ['loading' => 'lazy']); ?></div>
        </div>
        <div class="tbt-testimonials-artwork-band"><div class="tbt-testimonials-container"><div class="tbt-testimonials-artwork tbt-testimonials-artwork--landscape"><?php tbt_render_media_image('curated_testimonials_results_2024_01', '', ['loading' => 'lazy']); ?><?php tbt_render_media_image('curated_testimonials_results_2024_02', '', ['loading' => 'lazy']); ?></div></div></div>
        <div class="tbt-testimonials-container">
            <?php $tbt_story_heading(); ?>
            <?php tbt_render_testimonials($tbt_group_args('2024')); ?>
        </div>
    </section>

    <section class="tbt-testimonials-year" id="class-of-2023" data-section="2023" data-archive-group>
        <div class="tbt-testimonials-container">
            <h2 class="screen-reader-text">Class of 2023</h2>
            <div class="tbt-testimonials-artwork tbt-testimonials-artwork--portrait"><?php tbt_render_media_image('curated_testimonials_results_2023_01', '', ['loading' => 'lazy']); ?><?php tbt_render_media_image('curated_testimonials_results_2023_02', '', ['loading' => 'lazy']); ?></div>
        </div>
        <div class="tbt-testimonials-artwork-band"><div class="tbt-testimonials-container"><div class="tbt-testimonials-artwork tbt-testimonials-artwork--landscape"><?php tbt_render_media_image('curated_testimonials_results_2023_03', '', ['loading' => 'lazy']); ?></div></div></div>
        <div class="tbt-testimonials-container">
            <?php $tbt_story_heading(); ?>
            <?php tbt_render_testimonials($tbt_group_args('2023')); ?>
        </div>
    </section>

    <section class="tbt-testimonials-year" id="class-of-2022" data-section="2022" data-archive-group>
        <div class="tbt-testimonials-artwork-band tbt-testimonials-artwork-band--2022"><div class="tbt-testimonials-container">
            <h2>Class of 2022</h2>
            <div class="tbt-testimonials-artwork tbt-testimonials-artwork--landscape"><?php tbt_render_media_image('curated_testimonials_results_2022_01', '', ['loading' => 'lazy']); ?></div>
        </div></div>
        <div class="tbt-testimonials-container">
            <?php $tbt_story_heading(); ?>
            <?php tbt_render_testimonials($tbt_group_args('2022')); ?>
            <?php if ($tbt_whatsapp !== '') : ?><a class="tbt-testimonials-button tbt-testimonials-button--archive" href="<?php echo esc_url($tbt_whatsapp); ?>" target="_blank" rel="noopener noreferrer">WhatsApp Us Now</a><?php endif; ?>
        </div>
    </section>

    <section class="tbt-testimonials-year" id="class-of-2021" data-section="2021" data-archive-group>
        <div class="tbt-testimonials-results-band"><div class="tbt-testimonials-container">
            <h2>Class of 2021</h2>
            <p>100% of our Class of 2021 improved their grade<br>in 6 months or less</p>
        </div></div>
        <div class="tbt-testimonials-container">
            <?php $tbt_story_heading(false); ?>
            <?php tbt_render_testimonials($tbt_group_args('2021')); ?>
            <?php if ($tbt_whatsapp !== '') : ?><a class="tbt-testimonials-button tbt-testimonials-button--archive" href="<?php echo esc_url($tbt_whatsapp); ?>" target="_blank" rel="noopener noreferrer">WhatsApp Us Now</a><?php endif; ?>
        </div>
    </section>
</article>
<?php get_footer(); ?>
