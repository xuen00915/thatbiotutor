<?php

if (!defined('ABSPATH')) {
    exit;
}
?>
    <section class="tbt-home-section tbt-home-proof" data-section="proof">
        <div class="tbt-home-container">
            <h2>Don't just take our word for it.</h2>
            <div class="tbt-home-proof__videos">
                <div class="tbt-home-video"><iframe src="https://www.youtube-nocookie.com/embed/Vkzlr16AGvo" title="ThatBioTutor student testimonial" loading="lazy" allowfullscreen></iframe></div>
                <div class="tbt-home-video"><iframe src="https://www.youtube-nocookie.com/embed/V4dj5XYEcow" title="Gwen's ThatBioTutor testimonial" loading="lazy" allowfullscreen></iframe></div>
            </div>
            <p class="tbt-home-proof__schools-copy">Trusted by students from <strong>98 schools</strong> across Singapore.</p>
            <div class="tbt-home-proof__crests" aria-label="Schools attended by ThatBioTutor students">
                <?php foreach (range(1, 12) as $tbt_index) : ?><?php $tbt_media_image(sprintf('curated_shared_school_crest_%02d', $tbt_index), '', 'loading="lazy"'); ?><?php endforeach; ?>
            </div>
            <p class="tbt-home-proof__results-copy">Some of our biggest grade jumps. There are many more.</p>
            <div class="tbt-home-proof__results">
                <?php $tbt_media_image('curated_shared_hall_of_fame', '', 'loading="lazy"'); ?>
            </div>
            <?php require TBT_THEME_DIR . '/template-parts/google-reviews.php'; ?>
        </div>
    </section>

    <section class="tbt-home-section tbt-home-tutors" data-section="tutors">
        <div class="tbt-home-container">

            <h2>Meet Your Tutors</h2>
            <div class="tbt-home-tutors__grid">
                <?php foreach ($tbt_tutors as $tbt_tutor) : ?>
                    <article><?php $tbt_media_image($tbt_tutor['media'], '', 'loading="lazy"'); ?><h3><?php echo esc_html($tbt_tutor['name']); ?></h3><p><?php echo esc_html($tbt_tutor['role']); ?></p></article>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <section class="tbt-home-section tbt-home-quality" data-section="quality">
        <div class="tbt-home-container">
            <h2>Quality Control: How TBT Ensures Every Class Is Excellent</h2>
            <ul>
                <li><span>✓</span><p>All tutors pass rigorous screening. Can take 1+ month before teaching their first class.</p></li>
                <li><span>✓</span><p>Weekly tutor coaching sessions. Every TBT tutor is mentored by Mr Keefe each week.</p></li>
                <li><span>✓</span><p>All tutors follow the same standardised content and teaching methods. Every class ends with a student feedback form, reviewed and acted on weekly.</p></li>
            </ul>
        </div>
    </section>

    <section class="tbt-home-section tbt-home-practical" data-section="practical">
        <div class="tbt-home-container">

            <h2>Real Practical Sessions. Real Practice.</h2>
            <p class="tbt-home-lead">Paper 3 makes up 20% of the grade, and most students walk in undertrained. TBT students can opt in to hands-on sessions covering the exact experiment types most commonly tested, well before exam day.</p>
            <div class="tbt-home-practical__grid"><?php foreach (range(1, 3) as $tbt_index) : ?><figure><?php $tbt_media_image(sprintf('curated_shared_practical_%02d', $tbt_index), '', 'loading="lazy"'); ?></figure><?php endforeach; ?></div>
        </div>
    </section>

