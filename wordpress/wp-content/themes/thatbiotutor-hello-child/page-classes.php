<?php
/* Template Name: Classes */
declare(strict_types=1);
if (!defined('ABSPATH')) { exit; }
get_header();
$tbt_defaults = tbt_reference_content()['pages']['classes']['fields'];
$tbt_field = static fn (string $name): string => (string) tbt_page_field($name, $tbt_defaults[$name] ?? '');
?>
<article class="tbt-programme-page tbt-classes-page">
    <header class="tbt-programme-hero tbt-section"><p class="tbt-eyebrow">O-Level Pure / G3 Biology</p><h1 class="tbt-heading"><?php echo esc_html($tbt_field('classes_heading')); ?></h1><a class="tbt-button" href="<?php echo esc_url(home_url('/contact/')); ?>">Enquire About Classes</a></header>
    <section class="tbt-section tbt-section--soft"><div class="tbt-split"><div><h2><?php echo esc_html($tbt_field('classes_intro_heading')); ?></h2><p>Pure / G3 Biology goes beyond remembering facts. Students must connect concepts, apply them to unfamiliar situations, and communicate precise answers.</p></div><?php tbt_render_media_image('curated_classes_pure_vs_combined', 'tbt-contain-artwork', ['loading' => 'lazy']); ?></div></section>
    <section class="tbt-section"><h2><?php echo esc_html($tbt_field('classes_struggle_heading')); ?></h2><div class="tbt-problem-grid"><article><strong>1</strong><h3>Too much content to memorize</h3></article><article><strong>2</strong><h3>Open-ended questions are unpredictable</h3></article><article><strong>3</strong><h3>Topics feel disconnected</h3></article><article><strong>4</strong><h3>Hands-on practice is limited</h3></article></div></section>
    <section class="tbt-section tbt-section--dark"><h2><?php echo esc_html($tbt_field('classes_solution_heading')); ?></h2><div class="tbt-solution-list"><article><h3>Problem 1 — Too much content</h3><p>Concise visual resources and memorable shortcuts focus students on what matters.</p></article><article><h3>Problem 2 — OEQ technique</h3><p>The FIRED method gives every open-ended answer a reliable structure.</p></article><article><h3>Problem 3 — Seeing the big picture</h3><p>Concept-first lessons make links across topics explicit.</p></article><article><h3>Problem 4 — Limited practical experience</h3><p>Real practical sessions replace guesswork with confidence.</p></article></div></section>
    <section class="tbt-section"><h2><?php echo esc_html($tbt_field('classes_difference_heading')); ?></h2><div class="tbt-feature-grid"><article><h3>In-depth understanding</h3><p>No more feeling lost.</p></article><article><h3>Memory shortcuts</h3><p>Built into every topic.</p></article><article><h3>OEQ answering technique</h3><p>The FIRED method.</p></article><article><h3>Ex-MOE reviewed content</h3><p>Resources aligned to syllabus expectations.</p></article><article><h3>Real Practical Sessions</h3><p>Real practice, not just slides.</p></article></div><?php tbt_render_media_image('curated_classes_fasttrack_study_prep', 'tbt-contain-artwork', ['loading' => 'lazy']); ?></section>
    <section class="tbt-section tbt-section--soft tbt-level-choice"><h2><?php echo esc_html($tbt_field('classes_level_heading')); ?></h2><div class="tbt-level-choice__grid"><a href="<?php echo esc_url(home_url('/sec-3-pure-g3-biology-tuition/')); ?>"><strong>Sec 3 Pure / G3 Biology Classes</strong><span>Build a strong foundation from the start.</span></a><a href="<?php echo esc_url(home_url('/sec-4-pure-g3-biology-tuition/')); ?>"><strong>Sec 4 Pure / G3 Biology Classes</strong><span>Turn knowledge into O-Level results.</span></a></div></section>
    <section class="tbt-section"><h2>Questions About O Level Pure/G3 Biology</h2><?php tbt_render_faqs('classes'); ?></section>
</article>
<?php get_footer(); ?>

