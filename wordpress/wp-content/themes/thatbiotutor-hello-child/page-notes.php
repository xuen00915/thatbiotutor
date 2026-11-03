<?php
/* Template Name: Free Bio Notes */
declare(strict_types=1);
if (!defined('ABSPATH')) { exit; }
get_header();
?>
<article class="tbt-resource-page tbt-notes-page">
    <header class="tbt-notes-hero tbt-section"><div><p class="tbt-eyebrow">For Pure / G3 Biology</p><h1 class="tbt-heading">Free O Level Biology Notes Singapore</h1><p>Concise visual notes, memory shortcuts, and commonly asked exam questions.</p><a class="tbt-button" href="#notes-download">Download the Notes</a></div><div class="tbt-notes-carousel" data-carousel><?php foreach(range(1,3) as $tbt_index): $tbt_key=sprintf('curated_notes_notes_slide_%02d',$tbt_index); ?><figure data-carousel-slide<?php echo $tbt_index>1?' hidden':''; ?>><?php tbt_render_media_image($tbt_key,'tbt-contain-artwork',['loading'=>'lazy']); ?></figure><?php endforeach; ?><button type="button" class="tbt-carousel__control tbt-carousel__control--previous" data-carousel-prev aria-label="Previous notes preview">←</button><button type="button" class="tbt-carousel__control tbt-carousel__control--next" data-carousel-next aria-label="Next notes preview">→</button><div class="tbt-carousel__dots"><?php foreach(range(1,3) as $tbt_index): ?><button type="button" data-carousel-dot aria-label="Show notes preview <?php echo esc_attr((string)$tbt_index); ?>"></button><?php endforeach; ?></div><p class="screen-reader-text" data-carousel-status aria-live="polite">1 of 3</p></div></header>
    <section class="tbt-section tbt-section--soft"><p class="tbt-eyebrow">7,000+ downloads</p><h2>Four reasons students use these notes</h2><div class="tbt-notes-benefits"><?php foreach(['concise'=>'Concise and visual','memory'=>'Memory shortcuts','questions'=>'Common exam questions','reviewed'=>'Reviewed and syllabus-focused'] as $tbt_key=>$tbt_title): ?><article data-notes-benefit><?php tbt_render_media_image('curated_notes_notes_benefit_'.$tbt_key,'tbt-contain-artwork',['loading'=>'lazy']); ?><h3><?php echo esc_html($tbt_title); ?></h3></article><?php endforeach; ?></div></section>
    <section class="tbt-section"><h2>What Students Are Saying</h2><?php tbt_render_testimonials(['posts_per_page'=>100,'meta_query'=>[['key'=>'tbt_display_group','value'=>'notes-reviews','compare'=>'=']]]); ?></section>
    <section class="tbt-section tbt-section--soft"><h2>Why are you giving your notes for FREE?</h2><p>Because clear Biology resources should help as many students as possible, whether or not they attend TBT classes.</p></section>
    <section class="tbt-section"><h2>This sounds too good to be true. What's the catch?</h2><p>There is no payment required. The notes introduce the same clear, visual learning approach used by ThatBioTutor.</p></section>
    <section class="tbt-section tbt-section--dark" id="notes-download"><h2>How do I download it?</h2><p>The original Wix page used an embedded web form. This portable WordPress build does not invent a new data-collection form; request the current access link from the team.</p><a class="tbt-button" href="<?php echo esc_url(home_url('/contact/')); ?>">Request the Notes</a></section>
    <section class="tbt-section"><h2>What Students Are Saying...</h2><?php tbt_render_faqs('notes-reviews'); ?></section>
</article>
<?php get_footer(); ?>

