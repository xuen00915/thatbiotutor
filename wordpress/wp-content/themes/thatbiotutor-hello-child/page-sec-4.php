<?php
/* Template Name: Sec 4 Pure G3 Biology Tuition */
declare(strict_types=1);
if (!defined('ABSPATH')) { exit; }
get_header();
$tbt_defaults = tbt_reference_content()['pages']['sec-4-pure-g3-biology-tuition']['fields'];
$tbt_field = static fn (string $name): string => (string) tbt_page_field($name, $tbt_defaults[$name] ?? '');
?>
<article class="tbt-programme-page tbt-sec4-page">
    <header class="tbt-programme-hero tbt-section"><p class="tbt-eyebrow">Sec 4 Pure / G3 Biology Tuition</p><h1 class="tbt-heading"><?php echo esc_html($tbt_field('sec4_heading')); ?></h1><a class="tbt-button" href="<?php echo esc_url(home_url('/contact/')); ?>">Enquire About Sec 4</a></header>
    <section class="tbt-section tbt-section--soft"><h2>Choose how your child learns best</h2><div class="tbt-choice-grid"><article><h3>Onsite Classes</h3><p>Focused lessons, practical work, and direct feedback.</p></article><article><h3>Online Classes</h3><p>Live instruction, complete resources, and ongoing support.</p></article></div></section>
    <section class="tbt-section"><h2><?php echo esc_html($tbt_field('sec4_cover_heading')); ?></h2><div class="tbt-phase-grid tbt-phase-grid--three"><article><p class="tbt-eyebrow">Phase 1 — Teaching the Syllabus</p><h3>Dec – May</h3></article><article><p class="tbt-eyebrow">Phase 2 — Prelim Intensives</p><h3>May – Aug</h3></article><article><p class="tbt-eyebrow">Phase 3 — O Levels Intensives</p><h3>Aug – Nov</h3></article></div><h2><?php echo esc_html($tbt_field('sec4_topics_heading')); ?></h2><ul class="tbt-topic-list"><li>Reproduction and Inheritance</li><li>Molecular Genetics</li><li>Organisms and their Environment</li><li>Biotechnology</li><li>Full-syllabus integration and revision</li></ul></section>
    <section class="tbt-section tbt-section--dark"><h2>Exam Techniques — Built Into Every Lesson</h2><p>Timed questions, model answers, and precise feedback turn content knowledge into marks.</p></section>
    <section class="tbt-section"><h2>TBT Hall of Fame — Real Sec 4 results</h2><div class="tbt-track-grid"><?php foreach (range(1, 4) as $tbt_index) tbt_render_media_image(sprintf('curated_shared_track_record_%02d', $tbt_index), 'tbt-cover-photo', ['loading' => 'lazy']); ?></div><?php tbt_render_testimonials(['meta_key' => 'tbt_display_group', 'meta_value' => 'sec4']); ?></section>
    <section class="tbt-section tbt-section--soft"><h2>Everything your child gets as a TBT Sec 4 student</h2><div class="tbt-feature-grid"><article><h3>TBT Notes</h3></article><article><h3>Flashcards</h3></article><article><h3>Lesson Recordings</h3></article><article><h3>CAQ Model Answers</h3></article><article><h3>Practice Papers</h3></article><article><h3>Week-Long WhatsApp Help</h3></article></div></section>
    <section class="tbt-section"><h2>Meet the TBT Team</h2><?php tbt_render_tutors(); ?></section>
    <section class="tbt-section"><h2>Questions Parents and Sec 4 Students Always Ask</h2><?php tbt_render_faqs('sec4'); ?></section>
</article>
<?php get_footer(); ?>

