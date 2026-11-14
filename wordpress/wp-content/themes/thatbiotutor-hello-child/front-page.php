<?php

declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}

get_header();

require TBT_THEME_DIR . '/template-parts/inquire-data.php';
?>
<div class="tbt-home tbt-home-inquire">
    <section class="tbt-home-hero" data-section="hero">
        <div class="tbt-home-container tbt-home-hero__copy">
            <p class="tbt-home-kicker"><?php echo esc_html($tbt_h('home_specialist_line')); ?></p>
            <h1><?php echo tbt_accent_marks($tbt_h('home_heading'), 'tbt-home-accent', 'tbt-home-accent tbt-home-accent--red'); ?></h1>
            <p class="tbt-home-hero__result"><?php echo tbt_rich_inline($tbt_h('home_hero_result')); ?></p>
            <p class="tbt-home-hero__refund"><?php echo esc_html($tbt_h('home_hero_refund')); ?></p>
            <?php if ($tbt_whatsapp !== '') : ?><a class="tbt-home-button" href="<?php echo esc_url($tbt_whatsapp); ?>" target="_blank" rel="noopener noreferrer"><?php echo esc_html($tbt_h('home_primary_cta_label')); ?></a><?php endif; ?>
        </div>
        <div class="tbt-home-hero__carousel" data-carousel data-carousel-fade data-carousel-autoplay="5200" aria-label="ThatBioTutor lessons and students">
            <div class="tbt-home-hero__slides">
                <?php foreach (range(1, 5) as $tbt_index) : $tbt_key = sprintf('home_hero_%02d', $tbt_index); ?>
                    <figure class="tbt-home-hero__slide<?php echo $tbt_index === 1 ? ' is-active' : ''; ?>" data-carousel-slide>
                        <?php $tbt_media_image($tbt_key, '', $tbt_index === 1 ? 'fetchpriority="high"' : 'loading="eager"'); ?>
                    </figure>
                <?php endforeach; ?>
            </div>
            <button class="tbt-home-carousel__control tbt-home-carousel__control--previous" type="button" data-carousel-prev aria-label="Previous image"><span aria-hidden="true">‹</span></button>
            <button class="tbt-home-carousel__control tbt-home-carousel__control--next" type="button" data-carousel-next aria-label="Next image"><span aria-hidden="true">›</span></button>
            <div class="tbt-home-carousel__dots" aria-label="Choose image">
                <?php foreach (range(1, 5) as $tbt_index) : ?><button type="button" data-carousel-dot aria-label="Show image <?php echo esc_attr((string) $tbt_index); ?>" aria-current="<?php echo $tbt_index === 1 ? 'true' : 'false'; ?>"></button><?php endforeach; ?>
            </div>
            <p class="screen-reader-text" data-carousel-status aria-live="polite">1 of 5</p>
        </div>
    </section>

    <section class="tbt-home-section tbt-home-problem" data-section="problem">
        <div class="tbt-home-container">
            <div class="tbt-home-problem__quotes">
                <?php foreach ([1, 2, 3] as $tbt_n) : ?><blockquote><?php echo esc_html($tbt_h('home_problem_quote_' . $tbt_n)); ?></blockquote><?php endforeach; ?>
            </div>
            <p class="tbt-home-problem__bridge tbt-home-problem__bridge--strong"><?php echo esc_html($tbt_h('home_problem_bridge_1')); ?></p>
            <p class="tbt-home-problem__bridge"><?php echo esc_html($tbt_h('home_problem_bridge_2')); ?></p>
            <div class="tbt-home-problem__proof">
                <?php foreach ([1, 2, 3] as $tbt_n) : ?>
                <figure><blockquote><?php echo esc_html($tbt_h('home_story_' . $tbt_n . '_quote')); ?></blockquote><figcaption><?php $tbt_media_image(['curated_home_testimonial_yong', 'curated_home_testimonial_gwen', 'curated_home_testimonial_aryan'][$tbt_n - 1]); ?><span><strong><?php echo esc_html($tbt_h('home_story_' . $tbt_n . '_name')); ?></strong><small><?php echo esc_html($tbt_h('home_story_' . $tbt_n . '_result')); ?></small></span></figcaption></figure>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <section class="tbt-home-section tbt-home-stats" data-section="track-record">
        <div class="tbt-home-container">
            <h2><?php echo esc_html($tbt_h('home_trust_heading')); ?></h2>
            <div class="tbt-home-stats__grid">
                <?php foreach ($tbt_stats as $tbt_stat) : ?>
                    <article class="tbt-home-stat">
                        <strong data-count-up data-count-target="<?php echo esc_attr($tbt_stat['target']); ?>" data-count-display="<?php echo esc_attr($tbt_stat['display']); ?>"><?php echo esc_html($tbt_stat['display']); ?></strong>
                        <span><?php echo esc_html($tbt_stat['label']); ?></span>
                        <?php if (isset($tbt_stat['note'])) : ?><small><?php echo esc_html($tbt_stat['note']); ?></small><?php endif; ?>
                    </article>
                <?php endforeach; ?>
            </div>
            <div class="tbt-home-stats__photos">
                <?php foreach (range(1, 4) as $tbt_index) : ?><figure><?php $tbt_media_image(sprintf('home_number_%02d', $tbt_index), '', 'loading="lazy"'); ?></figure><?php endforeach; ?>
            </div>
            <p class="tbt-home-stats__bridge"><?php echo esc_html($tbt_h('home_track_bridge')); ?></p>
            <?php $tbt_track_video = tbt_testimonial_embed_url($tbt_h('home_track_video_url')); if ($tbt_track_video !== '') : ?><div class="tbt-home-video"><iframe src="<?php echo esc_url($tbt_track_video); ?>" title="<?php echo esc_attr($tbt_h('home_track_video_title')); ?>" loading="lazy" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" allowfullscreen></iframe></div><?php endif; ?>
        </div>
    </section>

    <?php require TBT_THEME_DIR . '/template-parts/inquire-section-fasttrack.php'; ?>
    <?php require TBT_THEME_DIR . '/template-parts/inquire-sections-proof.php'; ?>

    <section class="tbt-home-section tbt-home-guarantee" id="trial" data-section="guarantee">
        <div class="tbt-home-container tbt-home-guarantee__inner">
            <div class="tbt-home-guarantee__content">
                <h2><?php echo tbt_heading_html($tbt_h('home_guarantee_heading')); ?></h2>
                <div class="tbt-home-guarantee__shield" data-guarantee-shield aria-hidden="true"><svg viewBox="0 0 270 316"><path class="tbt-home-guarantee__shield-base" d="M135 0C170 28 215 45 266 50C270 150 263 265 135 316C7 265 0 150 4 50C55 45 100 28 135 0Z"/><path class="tbt-home-guarantee__shield-line" d="M135 20C170 46 212 58 254 63C256 150 248 250 135 300C22 250 14 150 16 63C58 58 100 46 135 20Z"/><path class="tbt-home-guarantee__shield-check" d="M65 160 90 142 122 180 202 92 220 110 123 226Z"/></svg></div>
                <p><?php echo esc_html($tbt_h('home_guarantee_text')); ?></p>
                <?php if ($tbt_whatsapp !== '') : ?><a class="tbt-home-button" href="<?php echo esc_url($tbt_whatsapp); ?>" target="_blank" rel="noopener noreferrer"><?php echo esc_html($tbt_h('home_primary_cta_label')); ?></a><?php endif; ?>
            </div>
        </div>
    </section>

    <?php require TBT_THEME_DIR . '/template-parts/inquire-sections-end.php'; ?>

    <section class="tbt-home-section tbt-home-closing" data-section="closing">
        <div class="tbt-home-container tbt-home-closing__inner">
            <figure><?php $tbt_media_image('curated_home_founder_about', '', 'loading="lazy"'); ?></figure>
            <div><h2><?php echo esc_html($tbt_h('home_closing_heading')); ?></h2><?php echo tbt_rich($tbt_h('home_closing_body')); ?><div class="tbt-home-closing__cta-wrap"><?php if ($tbt_whatsapp !== '') : ?><a class="tbt-home-button" href="<?php echo esc_url($tbt_whatsapp); ?>" target="_blank" rel="noopener noreferrer"><?php echo esc_html($tbt_h('home_primary_cta_label')); ?></a><?php endif; ?></div></div>
        </div>
    </section>
</div>
<?php
get_footer();
