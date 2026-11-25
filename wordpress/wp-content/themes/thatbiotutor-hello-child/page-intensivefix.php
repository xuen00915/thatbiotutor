<?php
/* Template Name: IntensiveFix */
declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}

get_header();

require TBT_THEME_DIR . '/template-parts/inquire-data.php';

$tbt_defaults = tbt_reference_content()['pages']['intensivefix']['fields'];
$tbt_field = static fn (string $name): string => (string) tbt_page_field($name, $tbt_defaults[$name] ?? '');
$tbt_enrol_whatsapp = tbt_whatsapp_url($tbt_field('intensivefix_whatsapp_message'));

// The fourth statistic follows the IntensiveFix programme and still runs the count-up animation.
$tbt_stat_4 = $tbt_field('intensivefix_stat_4_value');
$tbt_stats[3] = ['display' => $tbt_stat_4, 'target' => preg_match('/\d+(?:\.\d+)?/', $tbt_stat_4, $tbt_number) === 1 ? $tbt_number[0] : $tbt_stat_4, 'label' => $tbt_field('intensivefix_stat_4_label')];

// The headline: Highlight words are green, Red words are red. A title saved before the buttons existed keeps its accents.
$tbt_heading_source = $tbt_field('intensivefix_heading');
if (!str_contains($tbt_heading_source, '<mark')) {
    $tbt_heading_source = (string) preg_replace(['/(Chaotic Sec 4 Year)/', '/(4 DAYS)/'], ['<mark data-tone="red">$1</mark>', '<mark>$1</mark>'], esc_html($tbt_heading_source), 1);
}
// Keep the dash with the words after it, as on the original headline.
$tbt_heading = str_replace('— ', '—&nbsp;', tbt_accent_marks($tbt_heading_source, 'tbt-home-accent', 'tbt-home-accent tbt-home-accent--red'));

// FastTrack title carries the IntensiveFix promise.
$tbt_fasttrack_heading = tbt_heading_html($tbt_field('intensivefix_fasttrack_heading'));
$tbt_video = tbt_testimonial_embed_url($tbt_field('intensivefix_video_url'));
?>
<div class="tbt-home tbt-home-inquire tbt-intensivefix-page">
    <section class="tbt-home-hero" data-section="hero">
        <div class="tbt-home-container tbt-home-hero__copy">
            <?php if ($tbt_field('intensivefix_kicker') !== '') : ?><p class="tbt-home-kicker"><?php echo esc_html($tbt_field('intensivefix_kicker')); ?></p><?php endif; ?>
            <h1><?php echo $tbt_heading; // phpcs:ignore WordPress.Security.EscapeOutput -- sanitised by tbt_accent_marks(). ?></h1>
            <?php if ($tbt_field('intensivefix_limited') !== '') : ?><p class="tbt-home-hero__result tbt-intensivefix-hero__limited"><?php echo tbt_rich_inline($tbt_field('intensivefix_limited')); // phpcs:ignore WordPress.Security.EscapeOutput ?></p><?php endif; ?>
            <?php if ($tbt_enrol_whatsapp !== '') : ?><a class="tbt-home-button tbt-intensivefix-hero__cta" href="<?php echo esc_url($tbt_enrol_whatsapp); ?>" target="_blank" rel="noopener noreferrer"><?php echo esc_html($tbt_field('intensivefix_cta_label')); ?></a><?php endif; ?>
        </div>
        <div class="tbt-home-container tbt-intensivefix-hero__graphics">
            <div class="tbt-intensivefix-carousel" data-carousel data-carousel-fade data-carousel-autoplay="3000" aria-label="IntensiveFix programme graphics">
                <div class="tbt-intensivefix-carousel__viewport">
                    <?php foreach (range(1, 10) as $tbt_index) : $tbt_key = sprintf('intensivefix_program_%02d', $tbt_index); ?><figure data-carousel-slide<?php echo $tbt_index === 1 ? ' class="is-active"' : ''; ?>><?php tbt_render_page_image(sprintf('intensivefix_program_image_%02d', $tbt_index), $tbt_key, 'tbt-intensivefix__graphic', ['loading' => 'eager']); ?></figure><?php endforeach; ?>
                </div>
                <button class="tbt-home-carousel__control tbt-home-carousel__control--previous" type="button" data-carousel-prev aria-label="Previous programme graphic"><span aria-hidden="true">‹</span></button>
                <button class="tbt-home-carousel__control tbt-home-carousel__control--next" type="button" data-carousel-next aria-label="Next programme graphic"><span aria-hidden="true">›</span></button>
                <div class="tbt-home-carousel__dots" aria-label="Choose programme graphic"><?php foreach (range(1, 10) as $tbt_index) : ?><button type="button" data-carousel-dot aria-label="Show programme graphic <?php echo esc_attr((string) $tbt_index); ?>" aria-current="<?php echo $tbt_index === 1 ? 'true' : 'false'; ?>"></button><?php endforeach; ?></div>
                <p class="screen-reader-text" data-carousel-status aria-live="polite">1 of 10</p>
            </div>
        </div>
    </section>

    <section class="tbt-home-section tbt-home-problem" data-section="problem">
        <div class="tbt-home-container">
            <div class="tbt-home-problem__quotes">
                <?php foreach ([1, 2, 3] as $tbt_n) : if ($tbt_field('intensivefix_quote_' . $tbt_n) === '') { continue; } ?><blockquote><?php echo esc_html($tbt_field('intensivefix_quote_' . $tbt_n)); ?></blockquote>
                <?php endforeach; ?>
            </div>
            <?php if ($tbt_field('intensivefix_reviews_bridge') !== '') : ?><p class="tbt-home-problem__bridge tbt-home-problem__bridge--strong"><?php echo esc_html($tbt_field('intensivefix_reviews_bridge')); ?></p><?php endif; ?>
            <div class="tbt-intensivefix-feedback"><?php tbt_render_page_image('intensivefix_student_reviews_image', 'intensivefix_student_reviews', 'tbt-intensivefix__reviews', ['loading' => 'lazy']); ?></div>
        </div>
    </section>

    <section class="tbt-home-section tbt-home-stats" data-section="track-record">
        <div class="tbt-home-container">
            <h2><?php echo esc_html($tbt_field('intensivefix_track_heading')); ?></h2>
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
            <?php if ($tbt_field('intensivefix_track_bridge') !== '') : ?><p class="tbt-home-stats__bridge"><?php echo esc_html($tbt_field('intensivefix_track_bridge')); ?></p><?php endif; ?>
            <?php if ($tbt_video !== '') : ?><div class="tbt-home-video"><iframe src="<?php echo esc_url($tbt_video); ?>" title="ThatBioTutor IntensiveFix video" loading="lazy" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" allowfullscreen></iframe></div><?php endif; ?>
        </div>
    </section>

    <?php require TBT_THEME_DIR . '/template-parts/inquire-section-fasttrack.php'; ?>

    <?php $tbt_inquire_end_skip = ['founder', 'faq', 'classroom']; require TBT_THEME_DIR . '/template-parts/inquire-sections-end.php'; ?>

    <section class="tbt-home-section tbt-home-closing" data-section="closing">
        <div class="tbt-home-container tbt-home-closing__inner">
            <figure><?php $tbt_media_image('curated_home_founder_about', '', 'loading="lazy"'); ?></figure>
            <div>
                <h2><?php echo esc_html($tbt_field('intensivefix_closing_heading')); ?></h2>
                <?php echo tbt_rich($tbt_field('intensivefix_closing_text')); // phpcs:ignore WordPress.Security.EscapeOutput ?>
                <?php if ($tbt_field('intensivefix_closing_ready') !== '') : ?><p class="tbt-intensivefix-closing__ready"><strong><?php echo esc_html($tbt_field('intensivefix_closing_ready')); ?></strong></p><?php endif; ?>
                <?php if ($tbt_enrol_whatsapp !== '') : ?><div class="tbt-home-closing__cta-wrap tbt-intensivefix-closing__cta"><a class="tbt-home-button" href="<?php echo esc_url($tbt_enrol_whatsapp); ?>" target="_blank" rel="noopener noreferrer"><?php echo esc_html($tbt_field('intensivefix_closing_button')); ?></a></div><?php endif; ?>
            </div>
        </div>
    </section>
</div>
<?php
get_footer();
