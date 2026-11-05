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
            <p class="tbt-home-kicker">Singapore's ONLY 'O' Level Pure/G3 Bio Specialist</p>
            <h1>The One Missing Key to go from <span class="tbt-home-accent tbt-home-accent--red">Fail</span> to <span class="tbt-home-accent">A1</span> while cutting study time by <span class="tbt-home-accent">90%</span></h1>
            <p class="tbt-home-hero__result">The Average TBT Student Makes a <strong>2.3 Grade Jump</strong> (~C5 to A2)</p>
            <p class="tbt-home-hero__refund">If a student doesn't absolutely love the $95 trial lesson, they get a full refund.</p>
            <?php if ($tbt_whatsapp !== '') : ?><a class="tbt-home-button" href="<?php echo esc_url($tbt_whatsapp); ?>" target="_blank" rel="noopener noreferrer">Book My Trial Lesson</a><?php endif; ?>
        </div>
        <div class="tbt-home-hero__carousel" data-carousel data-carousel-autoplay="5200" aria-label="ThatBioTutor lessons and students">
            <div class="tbt-home-hero__slides">
                <?php foreach (range(1, 5) as $tbt_index) : $tbt_key = sprintf('home_hero_%02d', $tbt_index); ?>
                    <figure class="tbt-home-hero__slide<?php echo $tbt_index === 1 ? ' is-active' : ''; ?>" data-carousel-slide<?php echo $tbt_index > 1 ? ' hidden' : ''; ?>>
                        <?php $tbt_media_image($tbt_key, '', $tbt_index === 1 ? 'fetchpriority="high"' : 'loading="lazy"'); ?>
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
                <blockquote>“There's WAY too much to memorise... so many confusing terms”</blockquote>
                <blockquote>“I already wrote down a very detailed answer... how come I didn't get the points?”</blockquote>
                <blockquote>“School bio skim through the content too fast! I don't understand concepts deeply enough...”</blockquote>
            </div>
            <p class="tbt-home-problem__bridge tbt-home-problem__bridge--strong">Sound familiar? Many TBT students started exactly here.</p>
            <p class="tbt-home-problem__bridge">But by the end of the year, they’re telling me:</p>
            <div class="tbt-home-problem__proof">
                <figure><blockquote>“Unlike school teachers who just merely read off the slides, these tutors had somehow made such a content heavy subject into something engaging.”</blockquote><figcaption><?php $tbt_media_image('curated_home_testimonial_yong'); ?><span><strong>Yong Zheng</strong><small>Sec 4 (E8 → A1 in 3 months)</small></span></figcaption></figure>
                <figure><blockquote>“After using TBT's notes and memorising all the shortcuts I managed to get the model template answer and memorise it easily.”</blockquote><figcaption><?php $tbt_media_image('curated_home_testimonial_gwen'); ?><span><strong>Gwen</strong><small>Sec 4 (F9 → A1)</small></span></figcaption></figure>
                <figure><blockquote>“Very knowledgeable and has his own personal touch to everything he does and genuinely cares for the students.”</blockquote><figcaption><?php $tbt_media_image('curated_home_testimonial_aryan'); ?><span><strong>Aryan</strong><small>Sec 4 (C6 → A2)</small></span></figcaption></figure>
            </div>
        </div>
    </section>

    <section class="tbt-home-section tbt-home-stats" data-section="track-record">
        <div class="tbt-home-container">
            <h2>Here's the track record:</h2>
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
            <p class="tbt-home-stats__bridge">Here's how we make that happen.</p>
            <div class="tbt-home-video"><iframe src="https://www.youtube-nocookie.com/embed/q_IPfEdODqI" title="The missing key to Biology improvement" loading="lazy" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" allowfullscreen></iframe></div>
        </div>
    </section>

    <?php require TBT_THEME_DIR . '/template-parts/inquire-section-fasttrack.php'; ?>
    <?php require TBT_THEME_DIR . '/template-parts/inquire-sections-proof.php'; ?>

    <section class="tbt-home-section tbt-home-guarantee" id="trial" data-section="guarantee">
        <div class="tbt-home-container tbt-home-guarantee__inner">
            <div class="tbt-home-guarantee__content">
                <h2>Join a Trial Lesson. If You Didn't Absolutely Love It, <span>You Can Get a Full Refund.</span></h2>
                <div class="tbt-home-guarantee__shield" data-guarantee-shield aria-hidden="true"><svg viewBox="0 0 270 316"><path class="tbt-home-guarantee__shield-base" d="M135 0C170 28 215 45 266 50C270 150 263 265 135 316C7 265 0 150 4 50C55 45 100 28 135 0Z"/><path class="tbt-home-guarantee__shield-line" d="M135 20C170 46 212 58 254 63C256 150 248 250 135 300C22 250 14 150 16 63C58 58 100 46 135 20Z"/><path class="tbt-home-guarantee__shield-check" d="M65 160 90 142 122 180 202 92 220 110 123 226Z"/></svg></div>
                <p>A trial lesson is $95. We’re so confident of our lesson quality that if you didn’t absolutely love it, simply ask for a refund and you’ll get your money back, no questions asked.</p>
                <?php if ($tbt_whatsapp !== '') : ?><a class="tbt-home-button" href="<?php echo esc_url($tbt_whatsapp); ?>" target="_blank" rel="noopener noreferrer">Book My Trial Lesson</a><?php endif; ?>
            </div>
        </div>
    </section>

    <?php require TBT_THEME_DIR . '/template-parts/inquire-sections-end.php'; ?>

    <section class="tbt-home-section tbt-home-closing" data-section="closing">
        <div class="tbt-home-container tbt-home-closing__inner">
            <figure><?php $tbt_media_image('curated_home_founder_about', '', 'loading="lazy"'); ?></figure>
            <div><h2>Bio doesn’t have to eat up every other part of a student’s life.</h2><p>Students shouldn’t have to give up their CCA. Or their friends. Or their other subjects, just to keep their Biology grades afloat.</p><p>That’s exactly why I built the FastTrack System. The notes, the shortcuts, and the support are already done for them. Studying smart takes a fraction of the time it used to.</p><p>Students still get to have a life outside textbooks. And score the grade they know they’re capable of.</p><p>Ready to see what that actually feels like?</p><div class="tbt-home-closing__cta-wrap"><?php if ($tbt_whatsapp !== '') : ?><a class="tbt-home-button" href="<?php echo esc_url($tbt_whatsapp); ?>" target="_blank" rel="noopener noreferrer">Book My Trial Lesson</a><?php endif; ?></div></div>
        </div>
    </section>
</div>
<?php
get_footer();
