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
$tbt_enrol_whatsapp = tbt_whatsapp_url('Hi ThatBioTutor, I would like to secure a spot in IntensiveFix. Please share the schedule and next steps.');

// IntensiveFix replaces the fourth homepage statistic.
$tbt_stats[3] = ['display' => '4-Day', 'target' => '', 'label' => '8 lessons to rebuild your entire Sec 3 foundation'];

// The headline keeps its red and green emphasis while staying a single editable field.
$tbt_heading = esc_html($tbt_field('intensivefix_heading'));
$tbt_heading = preg_replace('/(Chaotic Sec 4 Year)/', '<span class="tbt-home-accent tbt-home-accent--red">$1</span>', $tbt_heading, 1) ?? $tbt_heading;
$tbt_heading = preg_replace('/(4 DAYS)/', '<span class="tbt-home-accent">$1</span>', $tbt_heading, 1) ?? $tbt_heading;
?>
<div class="tbt-home tbt-home-inquire tbt-intensivefix-page">
    <section class="tbt-home-hero" data-section="hero">
        <div class="tbt-home-container tbt-home-hero__copy">
            <p class="tbt-home-kicker">Singapore's ONLY 'O' Level Pure/G3 Bio Specialist</p>
            <h1><?php echo $tbt_heading; // phpcs:ignore WordPress.Security.EscapeOutput -- escaped above, only the accent span is added. ?></h1>
            <p class="tbt-home-hero__result"><strong>91% of Our Students Score A1, A2, or B3</strong> in O Levels (despite 69% starting at B4 or lower)</p>
            <p class="tbt-home-hero__refund">Start with a $110 trial lesson. If you don't love it, get a full refund. Runs late Nov to early Dec 2026. Limited intake.</p>
            <a class="tbt-home-button tbt-intensivefix-hero__cta" href="#trial"><?php echo esc_html($tbt_field('intensivefix_cta_label')); ?></a>
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
                <blockquote>“I flunked my EOYs and don't even know where to start.”</blockquote>
                <blockquote>“I've been meaning to start revising since the June holidays... and now it's already December.”</blockquote>
                <blockquote>“School bio skim through the content too fast! I don't understand concepts deeply enough...”</blockquote>
            </div>
            <p class="tbt-home-problem__bridge tbt-home-problem__bridge--strong">Sound familiar? Many TBT students started exactly here.</p>
            <p class="tbt-home-problem__bridge">But after learning the hard way that it pays to fix your foundation early, they're saying:</p>
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
                        <?php if ($tbt_stat['target'] !== '') : ?>
                            <strong data-count-up data-count-target="<?php echo esc_attr($tbt_stat['target']); ?>" data-count-display="<?php echo esc_attr($tbt_stat['display']); ?>"><?php echo esc_html($tbt_stat['display']); ?></strong>
                        <?php else : ?>
                            <strong><?php echo esc_html($tbt_stat['display']); ?></strong>
                        <?php endif; ?>
                        <span><?php echo esc_html($tbt_stat['label']); ?></span>
                        <?php if (isset($tbt_stat['note'])) : ?><small><?php echo esc_html($tbt_stat['note']); ?></small><?php endif; ?>
                    </article>
                <?php endforeach; ?>
            </div>
            <div class="tbt-home-stats__photos">
                <?php foreach (range(1, 4) as $tbt_index) : ?><figure><?php $tbt_media_image(sprintf('home_number_%02d', $tbt_index), '', 'loading="lazy"'); ?></figure><?php endforeach; ?>
            </div>
            <p class="tbt-home-stats__bridge">Here's how we make that happen.</p>
        </div>
    </section>

    <?php require TBT_THEME_DIR . '/template-parts/inquire-sections-main.php'; ?>

    <section class="tbt-home-section tbt-intensivefix-details" id="details" data-section="details">
        <div class="tbt-home-container">
            <p class="tbt-home-eyebrow">The 4-Day IntensiveFix</p>
            <h2><?php echo esc_html($tbt_field('intensivefix_program_heading')); ?></h2>
            <div class="tbt-intensivefix-carousel" data-carousel aria-label="IntensiveFix programme information">
                <div class="tbt-intensivefix-carousel__viewport">
                    <?php foreach (range(1, 10) as $tbt_index) : $tbt_key = sprintf('intensivefix_program_%02d', $tbt_index); ?><figure data-carousel-slide<?php echo $tbt_index > 1 ? ' hidden' : ''; ?>><?php tbt_render_page_image(sprintf('intensivefix_program_image_%02d', $tbt_index), $tbt_key, 'tbt-intensivefix__graphic', ['loading' => 'lazy']); ?></figure><?php endforeach; ?>
                </div>
                <button class="tbt-home-carousel__control tbt-home-carousel__control--previous" type="button" data-carousel-prev aria-label="Previous programme graphic"><span aria-hidden="true">‹</span></button>
                <button class="tbt-home-carousel__control tbt-home-carousel__control--next" type="button" data-carousel-next aria-label="Next programme graphic"><span aria-hidden="true">›</span></button>
                <div class="tbt-home-carousel__dots" aria-label="Choose programme graphic"><?php foreach (range(1, 10) as $tbt_index) : ?><button type="button" data-carousel-dot aria-label="Show programme graphic <?php echo esc_attr((string) $tbt_index); ?>" aria-current="<?php echo $tbt_index === 1 ? 'true' : 'false'; ?>"></button><?php endforeach; ?></div>
                <p class="screen-reader-text" data-carousel-status aria-live="polite">1 of 10</p>
            </div>
            <div class="tbt-intensivefix-text">
                <h3>IntensiveFix Fees, Format, Topics, and 2026 Schedule</h3>
                <p>Eight two-hour lessons run over four days in two weeks, with a one-hour break between lessons. Each lesson is a speedrun crash course with a complete content overhaul and question practice across nine Sec 3 topics, from cells to homeostasis.</p>
                <h4>Fees and class options</h4>
                <p>Non-TBT students: <strong>$110 per 2-hour lesson</strong>, for eight lessons in total. Current TBT students: <strong>$90 per 2-hour lesson</strong>, for eight lessons in total.</p>
                <p>Physical classes are recommended. Online attendance by Zoom is available at <strong>$5 off per lesson</strong>.</p>
                <p>Students who enrol in TBT’s 2027 lessons after IntensiveFix receive <strong>$160 off</strong> their next invoice when they enrol by 15 December 2026. Each non-TBT friend who signs up with you earns a <strong>$150 referral bonus</strong>.</p>
                <div class="tbt-intensivefix-runs">
                    <article><h4>Run 1 · Last two weeks of November</h4><p><strong>1pm–6pm</strong> on Monday 16 November 2026, Wednesday 18 November 2026, Monday 23 November 2026, and Wednesday 25 November 2026.</p></article>
                    <article><h4>Run 2 · First two weeks of December</h4><p><strong>1pm–6pm</strong> on Monday 30 November 2026, Thursday 3 December 2026, Monday 7 December 2026, and Thursday 10 December 2026.</p></article>
                </div>
                <h4>Lessons cover</h4>
                <p><strong>Cells, movement of substances, and biological molecules</strong>; enzymes and nutrition in humans; transport in humans; respiration; excretion; and homeostasis. The same sequence runs in both intakes. Contact us on WhatsApp if you need help with scheduling.</p>
            </div>
            <div class="tbt-intensivefix-feedback"><?php tbt_render_page_image('intensivefix_student_reviews_image', 'intensivefix_student_reviews', 'tbt-intensivefix__reviews', ['loading' => 'lazy']); ?></div>
            <div class="tbt-intensivefix-details__cta"><a class="tbt-home-button" href="#trial">Secure My Spot in IntensiveFix</a></div>
        </div>
    </section>

    <section class="tbt-home-section tbt-home-guarantee tbt-intensivefix-offer" id="trial" data-section="guarantee">
        <div class="tbt-home-container tbt-home-guarantee__inner">
            <div class="tbt-home-guarantee__content">
                <h2>Join the IntensiveFix Program. If You Didn't Absolutely Love Your First Lesson, <span>You Can Get a Full Refund.</span></h2>
                <div class="tbt-home-guarantee__shield" data-guarantee-shield aria-hidden="true"><svg viewBox="0 0 270 316"><path class="tbt-home-guarantee__shield-base" d="M135 0C170 28 215 45 266 50C270 150 263 265 135 316C7 265 0 150 4 50C55 45 100 28 135 0Z"/><path class="tbt-home-guarantee__shield-line" d="M135 20C170 46 212 58 254 63C256 150 248 250 135 300C22 250 14 150 16 63C58 58 100 46 135 20Z"/><path class="tbt-home-guarantee__shield-check" d="M65 160 90 142 122 180 202 92 220 110 123 226Z"/></svg></div>
                <p class="tbt-intensivefix-offer__lead">Here’s exactly what happens after the form below is submitted:</p>
                <ol class="tbt-intensivefix-steps">
                    <li><span>1</span>Fill out the form. Takes about 30 seconds.</li>
                    <li><span>2</span>We'll confirm your spot for the next IntensiveFix intake, and share the full lesson schedule.</li>
                    <li><span>3</span>Attend your first lesson and see the FastTrack System in action.</li>
                    <li><span>4</span>IntensiveFix runs over the last 2 weeks of November through the first 2 weeks of December. Spots are limited to this window only.</li>
                </ol>
                <p>IntensiveFix is a full 8-lesson programme, priced at $880 for non-TBT students. Your $110 trial covers the first lesson, and this refund only applies to that lesson, so you can try it risk free before committing to the rest.</p>
            </div>
            <div class="tbt-intensivefix-form"><iframe src="https://link.icecube.asia/widget/form/9h0XpRAiyc5b8dU3CPZx" title="IntensiveFix enrolment form" loading="lazy" data-form-id="9h0XpRAiyc5b8dU3CPZx"></iframe></div>
            <?php if ($tbt_enrol_whatsapp !== '') : ?><p class="tbt-intensivefix-whatsapp">Prefer WhatsApp? <a href="<?php echo esc_url($tbt_enrol_whatsapp); ?>" target="_blank" rel="noopener noreferrer">Message us to secure your spot.</a></p><?php endif; ?>
        </div>
    </section>

    <?php require TBT_THEME_DIR . '/template-parts/inquire-sections-end.php'; ?>

    <section class="tbt-home-section tbt-home-closing" data-section="closing">
        <div class="tbt-home-container tbt-home-closing__inner">
            <figure><?php $tbt_media_image('curated_home_founder_about', '', 'loading="lazy"'); ?></figure>
            <div>
                <h2>Bio doesn’t have to eat up every other part of a student’s life.</h2>
                <p>Students shouldn’t have to give up their CCA. Or their friends. Or their other subjects, just to keep their Biology grades afloat.</p>
                <p>That’s exactly why I built the FastTrack System. The notes, the shortcuts, and the support are already done for them. Studying smart takes a fraction of the time it used to.</p>
                <p>Students still get to have a life outside textbooks. And score the grade they know they’re capable of.</p>
                <p>Ready to see what that actually feels like?</p>
                <div class="tbt-home-closing__cta-wrap tbt-intensivefix-closing__cta"><a class="tbt-home-button" href="#trial">Fix My Sec 3 Foundation Now</a></div>
            </div>
        </div>
    </section>
</div>
<?php
get_footer();
