<?php
/* Template Name: Contact */
declare(strict_types=1);
if (!defined('ABSPATH')) { exit; }
get_header();
$tbt_defaults = tbt_reference_content()['pages']['contact']['fields'];
$tbt_field = static fn (string $name): string => (string) tbt_page_field($name, $tbt_defaults[$name] ?? '');
?>
<article class="tbt-institutional-page tbt-contact-page">
    <section class="tbt-contact-content" data-section="content">
        <div class="tbt-institutional-shell">
            <h1><?php echo tbt_accent_heading($tbt_field('contact_heading')); ?></h1>
            <p>Dear parent/student,</p>
            <p data-copy="read our FAQs first">If you have a question, please read our <a href="<?php echo esc_url(home_url('/#faq')); ?>"><strong><u>FAQs</u></strong></a> first!</p>
            <p>If you’re planning to sign up for our lessons and have any other questions, just drop us a message on WhatsApp.</p>
            <p>Our friendly team will get back to you as soon as possible!</p>
            <p class="tbt-contact-content__action"><a class="tbt-institutional-button" href="<?php echo esc_url(tbt_whatsapp_url("Hi ThatBioTutor, I'm a Student/Parent and I'd like to enquire about classes.")); ?>">WhatsApp Us Now</a></p>
        </div>
    </section>
</article>
<?php get_footer(); ?>
