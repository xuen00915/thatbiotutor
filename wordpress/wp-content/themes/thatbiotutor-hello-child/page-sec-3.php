<?php
/* Template Name: Sec 3 Pure G3 Biology Tuition */
declare(strict_types=1);
if (!defined('ABSPATH')) { exit; }
get_header();
$tbt_defaults = tbt_reference_content()['pages']['sec-3-pure-g3-biology-tuition']['fields'];
$tbt_field = static fn (string $name): string => (string) tbt_page_field($name, $tbt_defaults[$name] ?? '');
?>
<article class="tbt-programme-page tbt-sec3-page">
    <header class="tbt-programme-hero tbt-section"><p class="tbt-eyebrow">Sec 3 Pure / G3 Biology Tuition</p><h1 class="tbt-heading"><?php echo esc_html($tbt_field('sec3_heading')); ?></h1><a class="tbt-button" href="<?php echo esc_url(home_url('/contact/')); ?>">Enquire About Sec 3</a></header>
    <section class="tbt-section tbt-section--soft"><h2>Choose how your child learns best</h2><div class="tbt-choice-grid"><article><h3>Onsite Classes</h3><p>Interactive small-group lessons with real practical sessions.</p></article><article><h3>Online Classes</h3><p>Structured live learning with the same notes and support.</p></article></div></section>
    <section class="tbt-section"><h2><?php echo esc_html($tbt_field('sec3_cover_heading')); ?></h2><div class="tbt-phase-grid"><article><p class="tbt-eyebrow">Phase 1 — Teaching the Syllabus</p><h3>Mar – Aug</h3><p>Build strong concepts and connect each new topic.</p></article><article><p class="tbt-eyebrow">Phase 2 — EOY Intensives</p><h3>Sep – Oct</h3><p>Consolidate content and apply it to exam questions.</p></article></div><h2><?php echo esc_html($tbt_field('sec3_topics_heading')); ?></h2><ul class="tbt-topic-list"><li>Cells and Movement of Substances</li><li>Biological Molecules and Nutrition</li><li>Transport in Humans and Plants</li><li>Respiration and Excretion</li><li>Homeostasis and Coordination</li></ul></section>
    <section class="tbt-section tbt-section--dark"><h2>Exam Techniques — Built Into Every Lesson</h2><p>Students practise past-year questions and apply the FIRED method to every open-ended answer.</p></section>
    <section class="tbt-section"><h2>TBT Hall of Fame — Real Sec 3 results</h2><div class="tbt-track-grid"><?php foreach (range(1, 3) as $tbt_index) tbt_render_media_image(sprintf('curated_shared_track_record_%02d', $tbt_index), 'tbt-cover-photo', ['loading' => 'lazy']); ?></div><?php tbt_render_testimonials(['meta_key' => 'tbt_display_group', 'meta_value' => 'sec3']); ?></section>
    <section class="tbt-section tbt-section--soft"><h2>What a TBT Sec 3 lesson actually looks like</h2><div class="tbt-feature-grid"><article><h3>Concept first</h3></article><article><h3>Memory shortcuts</h3></article><article><h3>OEQ practice and exam skills</h3></article><article><h3>Kahoot review</h3></article><article><h3>Real Practical Sessions. Real Practice.</h3></article></div></section>
    <section class="tbt-section"><h2>Meet the TBT Team</h2><?php tbt_render_tutors(); ?></section>
    <section class="tbt-section"><h2>Questions Parents and Sec 3 Students Always Ask</h2><?php tbt_render_faqs('sec3'); ?></section>
</article>
<?php get_footer(); ?>

