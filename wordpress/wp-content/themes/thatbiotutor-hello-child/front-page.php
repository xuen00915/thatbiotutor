<?php

declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}

get_header();

$tbt_reference = tbt_reference_content();
$tbt_home_defaults = $tbt_reference['pages']['home']['fields'] ?? [];
$tbt_home_field = static function (string $name) use ($tbt_home_defaults): string {
    return (string) tbt_page_field($name, $tbt_home_defaults[$name] ?? '');
};
$tbt_media_image = static function (string $key, string $class = '', string $dataAttribute = ''): void {
    $media = tbt_media($key);
    if (($media['url'] ?? '') === '') {
        return;
    }
    echo '<img' . ($class !== '' ? ' class="' . esc_attr($class) . '"' : '')
        . ($dataAttribute !== '' ? ' ' . $dataAttribute : '')
        . ' data-media-key="' . esc_attr($key) . '" src="' . esc_url((string) $media['url'])
        . '" alt="' . esc_attr((string) ($media['alt'] ?? '')) . '" width="' . esc_attr((string) ($media['width'] ?? 0))
        . '" height="' . esc_attr((string) ($media['height'] ?? 0)) . '" loading="lazy">';
};
$tbt_hero_heading = static function (string $heading): void {
    $parts = preg_split('/(AVERAGE|2\.3|\(~C5 to A2\))/u', $heading, -1, PREG_SPLIT_DELIM_CAPTURE);
    foreach ($parts ?: [$heading] as $part) {
        $highlight = in_array($part, ['AVERAGE', '2.3', '(~C5 to A2)'], true);
        echo $highlight ? '<span>' . esc_html($part) . '</span>' : esc_html($part);
    }
};
$tbt_specialist_line = static function (string $line): void {
    $highlight = "'O' Level Pure/G3 Bio Specialist";
    $parts = explode($highlight, $line, 2);
    echo esc_html($parts[0] ?? '');
    if (count($parts) === 2) {
        echo '<span class="tbt-home-hero__specialist-highlight">' . esc_html($highlight) . '</span>';
        echo esc_html($parts[1]);
    }
};
$tbt_missing_key_heading = static function (string $heading): void {
    $parts = preg_split('/(1 MISSING KEY|FAIL|A1|90%)/u', $heading, -1, PREG_SPLIT_DELIM_CAPTURE);
    foreach ($parts ?: [$heading] as $part) {
        $class = $part === 'FAIL' ? 'tbt-emphasis-red' : (in_array($part, ['1 MISSING KEY', 'A1', '90%'], true) ? 'tbt-emphasis-green' : '');
        echo $class !== '' ? '<span class="' . esc_attr($class) . '">' . esc_html($part) . '</span>' : esc_html($part);
    }
};
$tbt_whatsapp = tbt_whatsapp_url('Hi ThatBioTutor, I would like to know more about a trial lesson.');
?>
<div class="tbt-home">
    <section class="tbt-home-hero" data-section="hero" data-carousel data-carousel-autoplay="7000" aria-label="ThatBioTutor students and classes">
        <div class="tbt-home-hero__slides">
            <?php foreach (range(1, 5) as $tbt_index) :
                $tbt_key = $tbt_index === 1 ? 'curated_home_reference_hero' : sprintf('home_hero_%02d', $tbt_index);
                $tbt_fallback = tbt_media($tbt_key);
                $tbt_image = tbt_page_image(sprintf('home_hero_image_%02d', $tbt_index), (string) ($tbt_fallback['url'] ?? ''));
                $tbt_position = $tbt_index === 1 ? '50% 54%' : (string) ($tbt_fallback['objectPosition'] ?? '50% 50%');
            ?>
                <figure class="tbt-home-hero__slide<?php echo $tbt_index === 1 ? ' is-active' : ''; ?>" data-carousel-slide<?php echo $tbt_index > 1 ? ' hidden' : ''; ?>>
                    <img data-media-key="<?php echo esc_attr($tbt_key); ?>" src="<?php echo esc_url($tbt_image['url']); ?>" alt="<?php echo esc_attr($tbt_image['alt'] ?: ($tbt_fallback['alt'] ?? 'ThatBioTutor students')); ?>" style="object-position:<?php echo esc_attr($tbt_position); ?>" width="<?php echo esc_attr((string) $tbt_image['width']); ?>" height="<?php echo esc_attr((string) $tbt_image['height']); ?>"<?php echo $tbt_index === 1 ? ' fetchpriority="high"' : ' loading="lazy"'; ?>>
                </figure>
            <?php endforeach; ?>
        </div>
        <div class="tbt-home-hero__shade" aria-hidden="true"></div>
        <div class="tbt-home-hero__overlay">
            <h1><?php $tbt_hero_heading($tbt_home_field('home_heading')); ?></h1>
            <p><?php $tbt_specialist_line($tbt_home_field('home_specialist_line')); ?></p>
        </div>
        <button class="tbt-carousel__control tbt-carousel__control--previous" type="button" data-carousel-prev aria-label="Previous image">‹</button>
        <button class="tbt-carousel__control tbt-carousel__control--next" type="button" data-carousel-next aria-label="Next image">›</button>
        <div class="tbt-carousel__dots" aria-label="Choose image">
            <?php foreach (range(1, 5) as $tbt_index) : ?><button type="button" data-carousel-dot aria-label="Show image <?php echo esc_attr((string) $tbt_index); ?>" aria-current="<?php echo $tbt_index === 1 ? 'true' : 'false'; ?>"></button><?php endforeach; ?>
        </div>
        <p class="screen-reader-text" data-carousel-status aria-live="polite">1 of 5</p>
    </section>

    <section class="tbt-home-section tbt-missing-key" data-section="missing-key">
        <div class="tbt-home-container">
            <h2><?php $tbt_missing_key_heading($tbt_home_field('home_missing_key_heading')); ?></h2>
            <div class="tbt-video"><iframe src="https://www.youtube-nocookie.com/embed/q_IPfEdODqI" title="The missing key to Biology improvement" loading="lazy" scrolling="no" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" allowfullscreen></iframe></div>
            <?php if ($tbt_whatsapp !== '') : ?><a class="tbt-button" href="<?php echo esc_url($tbt_whatsapp); ?>" target="_blank" rel="noopener noreferrer">WhatsApp Us Now</a><?php endif; ?>
            <p class="tbt-home-note">To Learn More/Trial Lesson</p>
            <p class="tbt-home-gift">🎁 Free Gift: Enquire above to get 2 Premium Sample Lesson Packages (U.P. $300).</p>
        </div>
    </section>

    <section class="tbt-home-section tbt-fasttrack" data-section="fasttrack">
        <header class="tbt-home-band"><h2><?php echo esc_html($tbt_home_field('home_fasttrack_heading')); ?></h2><p>TBT's Greatest Secret, Your Greatest Advantage</p></header>
        <div class="tbt-home-container tbt-fasttrack__grid">
            <figure><?php $tbt_media_image('curated_home_fasttrack_system', 'tbt-fasttrack__artwork'); ?><figcaption>Watch the video for explanation:</figcaption><a class="tbt-button" href="#tbt-advantage">Watch the Video</a></figure>
            <figure><?php $tbt_media_image('curated_home_trial_information', 'tbt-fasttrack__artwork'); ?><figcaption>Watch the video for pricing details:</figcaption><a class="tbt-button" href="#tbt-advantage">Watch the Video</a></figure>
        </div>
        <div class="tbt-home-container tbt-fasttrack__class-photo"><?php $tbt_media_image('curated_home_classroom_reference', 'tbt-cover-photo'); ?></div>
    </section>

    <section class="tbt-home-section tbt-trust" data-section="trust">
        <div class="tbt-home-container">
            <h2><?php echo esc_html($tbt_home_field('home_trust_heading')); ?></h2>
            <div class="tbt-stats">
                <?php foreach (($tbt_reference['stats']['items'] ?? []) as $tbt_stat) : ?><div class="tbt-stat"><strong><?php echo esc_html((string) $tbt_stat['value']); ?></strong><span><?php echo esc_html((string) $tbt_stat['label']); ?></span></div><?php endforeach; ?>
            </div>
        </div>
    </section>

    <section class="tbt-home-section tbt-hall" data-section="hall-of-fame">
        <header class="tbt-home-band"><h2>TBT Hall of Fame</h2><p>Some of our biggest grade jumps (there are many more!)</p></header>
        <div class="tbt-home-container">
            <div class="tbt-hall__gallery"><?php foreach (range(1, 4) as $tbt_index) { tbt_render_page_image(sprintf('home_number_image_%02d', $tbt_index), sprintf('home_number_%02d', $tbt_index), 'tbt-hall__image', ['loading' => 'lazy']); } ?></div>
            <?php tbt_render_results(['posts_per_page' => -1]); ?>
            <a class="tbt-button" href="<?php echo esc_url(home_url('/testimonials/')); ?>">More Results</a>
        </div>
    </section>

    <section class="tbt-home-section tbt-home-testimonials" data-section="testimonials">
        <div class="tbt-home-container"><h2><?php echo esc_html($tbt_home_field('home_testimonials_heading')); ?></h2><?php tbt_render_testimonials(['posts_per_page' => 3]); ?><a class="tbt-button" href="<?php echo esc_url(home_url('/testimonials/')); ?>">More Testimonials</a></div>
    </section>

    <section class="tbt-home-section tbt-schools" data-section="schools">
        <header class="tbt-home-band"><h2><?php echo esc_html($tbt_home_field('home_schools_heading')); ?></h2></header>
        <div class="tbt-school-marquee" data-school-marquee><div class="tbt-school-marquee__track">
            <?php foreach (range(1, 12) as $tbt_index) : $tbt_media_image(sprintf('curated_shared_school_crest_%02d', $tbt_index), 'tbt-school-crest', 'data-school-crest'); endforeach; ?>
        </div></div>
    </section>

    <section class="tbt-home-section tbt-advantage" id="tbt-advantage" data-section="advantage">
        <header class="tbt-home-band"><h2><?php echo esc_html($tbt_home_field('home_advantage_heading')); ?></h2><p>Why Bio Becomes So Fun and Easy For Our Students:</p></header>
        <div class="tbt-home-container tbt-advantage__grid">
            <article><h3>In-Depth Understanding: <span>No More Feeling Lost!</span></h3><div class="tbt-video"><iframe src="https://www.youtube-nocookie.com/embed/MX8tv9NMt9Q" title="In-depth Biology understanding" loading="lazy" scrolling="no" allowfullscreen></iframe></div></article>
            <article><h3>Memory Shortcut — <span>How ADH Works</span></h3><div class="tbt-video"><iframe src="https://www.youtube-nocookie.com/embed/e8F2G0P4dEA" title="Biology memory shortcut" loading="lazy" scrolling="no" allowfullscreen></iframe></div></article>
            <article><h3>OEQ Skills — <span>Voluntary Vs Reflex OEQ</span></h3><div class="tbt-video"><iframe src="https://www.youtube-nocookie.com/embed/lcD2d2mcgps" title="Open-ended question skills" loading="lazy" scrolling="no" allowfullscreen></iframe></div></article>
        </div>
        <?php if ($tbt_whatsapp !== '') : ?><a class="tbt-button" href="<?php echo esc_url($tbt_whatsapp); ?>" target="_blank" rel="noopener noreferrer">WhatsApp Us Now</a><?php endif; ?>
    </section>

    <section class="tbt-home-section tbt-benefits-section" data-section="benefits">
        <header class="tbt-home-band"><h2>Student Benefits</h2><p>TBT Is So Much More Than Bio Lessons...</p></header>
        <div class="tbt-home-container"><?php $tbt_media_image('curated_home_student_benefits', 'tbt-benefits-artwork'); ?></div>
    </section>

    <section class="tbt-home-section tbt-experience" data-section="experience">
        <header class="tbt-home-band"><h2><?php echo esc_html($tbt_home_field('home_experience_heading')); ?></h2></header>
        <div class="tbt-home-container">
            <div class="tbt-media-grid"><?php foreach (range(1, 3) as $tbt_index) : $tbt_media_image(sprintf('curated_shared_practical_%02d', $tbt_index), 'tbt-media-grid__image', 'data-practical-image'); endforeach; ?></div>
            <div class="tbt-media-grid"><?php foreach (range(1, 3) as $tbt_index) :
                $tbt_key = sprintf('home_classroom_%02d', $tbt_index);
                $tbt_fallback = tbt_media($tbt_key);
                $tbt_image = tbt_page_image(sprintf('home_classroom_image_%02d', $tbt_index), (string) ($tbt_fallback['url'] ?? ''));
            ?><img data-classroom-image data-media-key="<?php echo esc_attr($tbt_key); ?>" src="<?php echo esc_url($tbt_image['url']); ?>" alt="<?php echo esc_attr($tbt_image['alt'] ?: 'Life inside the ThatBioTutor classroom'); ?>" loading="lazy"><?php endforeach; ?></div>
        </div>
    </section>

    <section class="tbt-home-section tbt-causes" data-section="causes">
        <header class="tbt-home-band"><h2><?php echo esc_html($tbt_home_field('home_causes_heading')); ?></h2></header>
        <div class="tbt-home-container tbt-causes__grid"><div><p>Up to 2025, ThatBioTutor donated a portion of its profits to global reforestation, resulting in 1,982 trees planted. From 2026 onwards, students help choose an environmental cause closer to home.</p><a class="tbt-button" href="<?php echo esc_url(home_url('/trees/')); ?>">See Our Causes</a></div><?php $tbt_media_image('curated_home_cause_graphic', 'tbt-causes__artwork'); ?></div>
    </section>

    <section class="tbt-home-section tbt-home-tutors" data-section="tutors">
        <header class="tbt-home-band"><h2><?php echo esc_html($tbt_home_field('home_tutors_heading')); ?></h2></header>
        <div class="tbt-home-container"><?php tbt_render_tutors(); ?><p class="tbt-quality-note">Every tutor passes rigorous screening, receives weekly coaching, and acts on student feedback.</p><a class="tbt-button" href="<?php echo esc_url(home_url('/about/')); ?>">Meet Our Tutors</a></div>
    </section>

    <section class="tbt-home-section tbt-home-faq" data-section="faq">
        <header class="tbt-home-band"><h2><?php echo esc_html($tbt_home_field('home_faq_heading')); ?></h2></header>
        <div class="tbt-home-container"><?php tbt_render_faqs('home'); ?></div>
    </section>

    <section class="tbt-home-section tbt-closing" data-section="closing">
        <div class="tbt-home-container"><h2><?php echo esc_html($tbt_home_field('home_closing_heading')); ?></h2><a class="tbt-button" href="<?php echo esc_url(home_url('/contact/')); ?>">Book a Trial Lesson</a></div>
    </section>
</div>
<?php
get_footer();
