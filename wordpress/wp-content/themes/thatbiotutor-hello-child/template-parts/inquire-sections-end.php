<?php

if (!defined('ABSPATH')) {
    exit;
}
$tbt_inquire_end_skip = isset($tbt_inquire_end_skip) && is_array($tbt_inquire_end_skip) ? $tbt_inquire_end_skip : [];
?>
    <section class="tbt-home-section tbt-home-fit" data-section="fit">
        <div class="tbt-home-container tbt-home-fit__grid">
            <article class="tbt-home-fit__yes"><h2><?php echo esc_html($tbt_h('home_fit_yes_heading')); ?></h2><?php echo tbt_rich($tbt_h('home_fit_yes_list')); ?></article>
            <article class="tbt-home-fit__no"><h2><?php echo esc_html($tbt_h('home_fit_no_heading')); ?></h2><?php echo tbt_rich($tbt_h('home_fit_no_list')); ?></article>
        </div>
    </section>

    <?php if (!in_array('founder', $tbt_inquire_end_skip, true)) : ?>
    <section class="tbt-home-section tbt-home-founder" data-section="founder">
        <div class="tbt-home-container tbt-home-founder__inner">
            <figure><?php $tbt_media_image('curated_home_founder_standing', '', 'loading="lazy"'); ?></figure>
            <div><h2><?php echo esc_html($tbt_h('home_founder_heading')); ?></h2><?php echo tbt_rich($tbt_h('home_founder_body')); ?></div>
        </div>
    </section>
    <?php endif; ?>

    <?php if (!in_array('faq', $tbt_inquire_end_skip, true)) : ?>
    <section class="tbt-home-section tbt-home-faq" data-section="faq">
        <div class="tbt-home-container">
            <h2><?php echo esc_html($tbt_h('home_faq_heading')); ?></h2>
            <div class="tbt-home-faq__list">
                <?php foreach ($tbt_faqs as $tbt_index => [$tbt_question, $tbt_answer]) : $tbt_panel_id = 'tbt-home-faq-' . ($tbt_index + 1); ?>
                    <article class="tbt-home-faq__item" data-home-inquire-faq><h3><button type="button" data-faq-button aria-expanded="false" aria-controls="<?php echo esc_attr($tbt_panel_id); ?>"><span><?php echo esc_html((string) ($tbt_index + 1) . '. ' . $tbt_question); ?></span><span class="tbt-home-faq__icon" aria-hidden="true"></span></button></h3><div id="<?php echo esc_attr($tbt_panel_id); ?>" class="tbt-home-faq__answer" hidden><?php echo tbt_rich($tbt_answer); ?></div></article>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <?php if (!in_array('classroom', $tbt_inquire_end_skip, true)) : ?>
    <section class="tbt-home-section tbt-home-classroom" data-section="classroom">
        <div class="tbt-home-container">
            <h2><?php echo esc_html($tbt_h('home_experience_heading')); ?></h2>
            <div class="tbt-home-classroom__grid"><?php foreach (range(1, 3) as $tbt_index) : ?><figure><?php $tbt_media_image(sprintf('home_classroom_%02d', $tbt_index), '', 'loading="lazy"'); ?></figure><?php endforeach; ?></div>
        </div>
    </section>
    <?php endif; ?>

