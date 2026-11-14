<?php

if (!defined('ABSPATH')) {
    exit;
}
?>
    <section class="tbt-home-section tbt-home-proof" data-section="proof">
        <div class="tbt-home-container">
            <h2><?php echo esc_html($tbt_h('home_testimonials_heading')); ?></h2>
            <div class="tbt-home-proof__videos">
                <?php foreach ([1, 2] as $tbt_n) : $tbt_video = tbt_testimonial_embed_url($tbt_h('home_proof_video_' . $tbt_n . '_url')); if ($tbt_video === '') { continue; } ?>
                <div class="tbt-home-video"><iframe src="<?php echo esc_url($tbt_video); ?>" title="<?php echo esc_attr($tbt_h('home_proof_video_' . $tbt_n . '_title')); ?>" loading="lazy" allowfullscreen></iframe></div>
                <?php endforeach; ?>
            </div>
            <p class="tbt-home-proof__schools-copy"><?php echo tbt_rich_inline($tbt_h('home_schools_copy')); ?></p>
            <div class="tbt-home-proof__crests" aria-label="Schools attended by ThatBioTutor students">
                <?php foreach (range(1, 12) as $tbt_index) : ?><?php $tbt_media_image(sprintf('curated_shared_school_crest_%02d', $tbt_index), '', 'loading="lazy"'); ?><?php endforeach; ?>
            </div>
            <p class="tbt-home-proof__results-copy"><?php echo esc_html($tbt_h('home_results_copy')); ?></p>
            <div class="tbt-home-proof__results">
                <?php $tbt_media_image('curated_shared_hall_of_fame', '', 'loading="lazy"'); ?>
            </div>
            <?php require TBT_THEME_DIR . '/template-parts/google-reviews.php'; ?>
        </div>
    </section>

    <section class="tbt-home-section tbt-home-tutors" data-section="tutors">
        <div class="tbt-home-container">

            <h2><?php echo esc_html($tbt_h('home_tutors_heading')); ?></h2>
            <div class="tbt-home-tutors__grid">
                <?php foreach ($tbt_tutors as $tbt_tutor) : ?>
                    <article><?php $tbt_media_image($tbt_tutor['media'], '', 'loading="lazy"'); ?><h3><?php echo esc_html($tbt_tutor['name']); ?></h3><p><?php echo esc_html($tbt_tutor['role']); ?></p></article>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <section class="tbt-home-section tbt-home-quality" data-section="quality">
        <div class="tbt-home-container">
            <h2><?php echo esc_html($tbt_h('home_quality_heading')); ?></h2>
            <ul>
                <?php foreach ([1, 2, 3] as $tbt_n) : if ($tbt_h('home_quality_' . $tbt_n) === '') { continue; } ?><li><span>✓</span><p><?php echo esc_html($tbt_h('home_quality_' . $tbt_n)); ?></p></li><?php endforeach; ?>
            </ul>
        </div>
    </section>

    <section class="tbt-home-section tbt-home-practical" data-section="practical">
        <div class="tbt-home-container">

            <h2><?php echo esc_html($tbt_h('home_practical_heading')); ?></h2>
            <p class="tbt-home-lead"><?php echo esc_html($tbt_h('home_practical_lead')); ?></p>
            <div class="tbt-home-practical__grid"><?php foreach (range(1, 3) as $tbt_index) : ?><figure><?php $tbt_media_image(sprintf('curated_shared_practical_%02d', $tbt_index), '', 'loading="lazy"'); ?></figure><?php endforeach; ?></div>
        </div>
    </section>

