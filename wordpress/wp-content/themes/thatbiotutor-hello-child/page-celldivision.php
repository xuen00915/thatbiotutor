<?php
/* Template Name: Cell Division Crash Course */
declare(strict_types=1);
if (!defined('ABSPATH')) { exit; }
get_header();
?>
<article class="tbt-resource-page tbt-cell-page">
    <header class="tbt-programme-hero tbt-section"><p class="tbt-eyebrow">Cell Division Crash Course</p><h1 class="tbt-heading">Only For 2023 Sec 4 Pure Bio Students!</h1><p>This is a historical 2023 resource page retained from the original ThatBioTutor website.</p></header>
    <section class="tbt-section tbt-section--soft"><h2>IMPORTANT:</h2><p>The original crash-course access window and embedded Wix form may no longer be active. No current enrolment claim is implied.</p></section>
    <section class="tbt-section"><h2>How do I access it?</h2><p>Contact the ThatBioTutor team to ask whether an updated Cell Division resource is available.</p><a class="tbt-button" href="<?php echo esc_url(home_url('/contact/')); ?>">Contact ThatBioTutor</a></section>
    <section class="tbt-section"><h2>What Students Are Saying About The Crash Course:</h2><?php tbt_render_faqs('celldivision-reviews'); ?></section>
</article>
<?php get_footer(); ?>

