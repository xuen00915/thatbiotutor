<?php

if (!defined('ABSPATH')) {
    exit;
}
?>
    <section class="tbt-home-section tbt-home-fasttrack" data-section="fasttrack">
        <div class="tbt-home-container">
            <p class="tbt-home-eyebrow">O Level G3/Pure Bio</p>
            <h2>ThatBioTutor's FastTrack System Helps Students Score <span>A1/A2</span> Without the Endless Revision Grind</h2>
            <p class="tbt-home-lead">Why should students write their own notes, make their own flashcards, and hunt for exam keywords alone, when it’s already done for them?</p>
            <div class="tbt-home-fasttrack__list">
                <?php foreach ($tbt_comparisons as $tbt_item) : ?>
                    <article class="tbt-home-fasttrack__comparison">
                        <h3><?php echo esc_html($tbt_item['title']); ?></h3>
                        <div class="tbt-home-fasttrack__other"><strong>Other Bio Students</strong><p><?php echo esc_html($tbt_item['other']); ?></p></div>
                        <div class="tbt-home-fasttrack__tbt"><strong>TBT Students + FastTrack</strong><div class="tbt-home-fasttrack__tbt-content"><?php $tbt_media_image($tbt_item['media'], '', 'loading="lazy"'); ?><p><?php echo esc_html($tbt_item['tbt']); ?></p></div></div>
                    </article>
                <?php endforeach; ?>
            </div>
            <p class="tbt-home-fasttrack__close">This is the FastTrack System: the one missing key. Everything students would normally have to build themselves, <strong>already built for them.</strong></p>
        </div>
    </section>

    <section class="tbt-home-section tbt-home-proof" data-section="proof">
        <div class="tbt-home-container">
            <h2>Don't just take our word for it.</h2>
            <div class="tbt-home-proof__videos">
                <div class="tbt-home-video"><iframe src="https://www.youtube-nocookie.com/embed/Vkzlr16AGvo" title="ThatBioTutor student testimonial" loading="lazy" allowfullscreen></iframe></div>
                <div class="tbt-home-video"><iframe src="https://www.youtube-nocookie.com/embed/RJxR3ONbylQ" title="Alicia's ThatBioTutor testimonial" loading="lazy" allowfullscreen></iframe></div>
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

