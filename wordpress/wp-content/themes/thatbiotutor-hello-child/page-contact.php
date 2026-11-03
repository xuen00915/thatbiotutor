<?php
/* Template Name: Contact */
declare(strict_types=1);
if (!defined('ABSPATH')) { exit; }
get_header();
$tbt_defaults = tbt_reference_content()['pages']['contact']['fields'];
$tbt_field = static fn (string $name): string => (string) tbt_page_field($name, $tbt_defaults[$name] ?? '');
?>
<article class="tbt-institutional-page tbt-contact-page">
    <header class="tbt-section tbt-utility-hero"><h1><?php echo esc_html($tbt_field('contact_heading')); ?></h1><p class="tbt-lead">Dear parent/student,</p></header>
    <section class="tbt-section tbt-institutional-section"><div class="tbt-reading-width"><h2>Have a question?</h2><p><?php echo esc_html($tbt_field('contact_intro')); ?></p><p>They cover lesson modes, homework, class size, levels, fees, and trial lessons.</p></div></section>
    <section class="tbt-section tbt-section--soft tbt-contact-cta"><div class="tbt-contact-cta__panel"><h2>Planning to sign up for lessons?</h2><p>If the FAQs do not answer your question, drop us a message on WhatsApp. Our friendly team will get back to you as soon as possible!</p><a class="tbt-button" href="<?php echo esc_url(tbt_whatsapp_url('Hi ThatBioTutor, I would like to ask about lessons.')); ?>">WhatsApp Us Now</a></div></section>
</article>
<?php get_footer(); ?>
