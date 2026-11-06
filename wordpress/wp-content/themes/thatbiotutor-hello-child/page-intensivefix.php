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
$tbt_enrol_whatsapp = tbt_whatsapp_url('Hi ThatBioTutor, I would like to enquire about IntensiveFix. Please share the schedule and next steps.');

// The fourth statistic follows the IntensiveFix programme and still runs the count-up animation.
$tbt_stats[3] = ['display' => '8', 'target' => '8', 'label' => 'Lessons over 4 days to rebuild your entire Sec 3 foundation'];

// The headline keeps its red and green emphasis while staying a single editable field.
$tbt_heading = esc_html($tbt_field('intensivefix_heading'));
// Keep the dash with the words after it, as on the original headline.
$tbt_heading = str_replace('— ', '—&nbsp;', $tbt_heading);
$tbt_heading = preg_replace('/(Chaotic Sec 4 Year)/', '<span class="tbt-home-accent tbt-home-accent--red">$1</span>', $tbt_heading, 1) ?? $tbt_heading;
$tbt_heading = preg_replace('/(4 DAYS)/', '<span class="tbt-home-accent">$1</span>', $tbt_heading, 1) ?? $tbt_heading;

// FastTrack title carries the IntensiveFix promise.
$tbt_fasttrack_heading = '<strong>All IntensiveFix students will receive</strong> ThatBioTutor\'s FastTrack System, which Helps Students Score <span>A1/A2</span> Without the Endless Revision Grind';
?>
<div class="tbt-home tbt-home-inquire tbt-intensivefix-page">
    <section class="tbt-home-hero" data-section="hero">
        <div class="tbt-home-container tbt-home-hero__copy">
            <p class="tbt-home-kicker">Singapore's ONLY 'O' Level Pure/G3 Bio Specialist</p>
            <h1><?php echo $tbt_heading; // phpcs:ignore WordPress.Security.EscapeOutput -- escaped above, only accent spans are added. ?></h1>
            <p class="tbt-home-hero__result tbt-intensivefix-hero__limited"><strong>Limited time:</strong> Only during <strong>last two weeks of Nov/first two weeks of Dec.</strong></p>
            <?php if ($tbt_enrol_whatsapp !== '') : ?><a class="tbt-home-button tbt-intensivefix-hero__cta" href="<?php echo esc_url($tbt_enrol_whatsapp); ?>" target="_blank" rel="noopener noreferrer"><?php echo esc_html($tbt_field('intensivefix_cta_label')); ?></a><?php endif; ?>
        </div>
        <div class="tbt-home-container tbt-intensivefix-hero__graphics">
            <div class="tbt-intensivefix-carousel" data-carousel data-carousel-fade data-carousel-autoplay="4500" aria-label="IntensiveFix programme graphics">
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
                <blockquote>“I flunked my EOYs and don't even know where to start.”</blockquote>
                <blockquote>“I've been meaning to start revising since the June holidays... and now it's already December.”</blockquote>
                <blockquote>“School bio skim through the content too fast! I don't understand concepts deeply enough...”</blockquote>
            </div>
            <p class="tbt-home-problem__bridge tbt-home-problem__bridge--strong">Sound familiar? After our 2-week IntensiveFix, they’re telling us:</p>
            <div class="tbt-intensivefix-feedback"><?php tbt_render_page_image('intensivefix_student_reviews_image', 'intensivefix_student_reviews', 'tbt-intensivefix__reviews', ['loading' => 'lazy']); ?></div>
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

    <?php require TBT_THEME_DIR . '/template-parts/inquire-sections-end.php'; ?>

    <section class="tbt-home-section tbt-home-closing" data-section="closing">
        <div class="tbt-home-container tbt-home-closing__inner">
            <figure><?php $tbt_media_image('curated_home_founder_about', '', 'loading="lazy"'); ?></figure>
            <div>
                <h2>Your EOY Bio Grade doesn’t have to define your O Levels.</h2>
                <p>It can be demoralising to have scored poorer than expected for the Sec 3 end of year bio exam. But there is still time to turn things around.</p>
                <p>We’ve had students come in during this period at F9, going all the way to an A1 in O levels.</p>
                <p>What you need is a thorough content overhaul, fixing that weak sec 3 foundation, to supercharge and give you confidence heading into sec 4, to score the O level grade you’re truly capable of.</p>
                <p class="tbt-intensivefix-closing__ready"><strong>Ready to see what that feels like?</strong></p>
                <?php if ($tbt_enrol_whatsapp !== '') : ?><div class="tbt-home-closing__cta-wrap tbt-intensivefix-closing__cta"><a class="tbt-home-button" href="<?php echo esc_url($tbt_enrol_whatsapp); ?>" target="_blank" rel="noopener noreferrer">Fix My Sec 3 Foundation Now</a></div><?php endif; ?>
            </div>
        </div>
    </section>
</div>
<?php
get_footer();
