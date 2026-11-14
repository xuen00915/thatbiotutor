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
            <?php echo tbt_rich($tbt_field('contact_body')); ?>
            <?php if ($tbt_field('contact_button_label') !== '') : ?><p class="tbt-contact-content__action"><a class="tbt-institutional-button" href="<?php echo esc_url(tbt_whatsapp_url($tbt_field('contact_whatsapp_message'))); ?>"><?php echo esc_html($tbt_field('contact_button_label')); ?></a></p><?php endif; ?>
        </div>
    </section>
</article>
<?php get_footer(); ?>
