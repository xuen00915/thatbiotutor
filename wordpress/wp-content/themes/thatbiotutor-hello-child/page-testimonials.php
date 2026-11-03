<?php
/* Template Name: Results & Testimonials */
declare(strict_types=1);
if (!defined('ABSPATH')) { exit; }
get_header();
?>
<article class="tbt-resource-page tbt-testimonials-page">
    <header class="tbt-programme-hero tbt-section"><p class="tbt-eyebrow">Real student journeys</p><h1 class="tbt-heading">Results &amp; Testimonials</h1><p>⭐⭐⭐⭐⭐</p></header>
    <section class="tbt-section"><h2>What Students Are Saying About ThatBioTutor:</h2><div class="tbt-results-artwork"><?php tbt_render_media_image('curated_testimonials_results_2025_01', 'tbt-contain-artwork', ['loading'=>'lazy']); tbt_render_media_image('curated_testimonials_results_2025_02', 'tbt-contain-artwork', ['loading'=>'lazy']); ?></div></section>
    <section class="tbt-section tbt-section--soft"><h2>Student Stories</h2><p>(Watch to the End for Their Advice to Future O Level Batches!)</p><nav class="tbt-archive-jump" aria-label="Testimonial years"><?php foreach (['2025','2024','2023','2022','2021'] as $tbt_year) : ?><a href="#class-of-<?php echo esc_attr($tbt_year); ?>">Class of <?php echo esc_html($tbt_year); ?></a><?php endforeach; ?></nav></section>
    <div class="tbt-review-archive">
        <?php foreach (['2025','2024','2023','2022','2021'] as $tbt_year) : ?>
            <section class="tbt-section" id="class-of-<?php echo esc_attr($tbt_year); ?>" data-archive-group><h2>Class of <?php echo esc_html($tbt_year); ?></h2><?php tbt_render_testimonials(['posts_per_page'=>60,'meta_query'=>[['key'=>'tbt_display_group','value'=>$tbt_year,'compare'=>'=']]]); ?></section>
        <?php endforeach; ?>
    </div>
</article>
<?php get_footer(); ?>

