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
?>
<div class="tbt-home">
    <section class="tbt-home-carousel" data-section="hero-carousel" data-carousel data-carousel-autoplay="7000" aria-label="Life at ThatBioTutor">
        <div class="tbt-home-carousel__viewport">
            <?php foreach (range(1, 5) as $tbt_index) :
                $tbt_key = sprintf('home_hero_%02d', $tbt_index);
                $tbt_fallback = tbt_media($tbt_key);
                $tbt_image = tbt_page_image(sprintf('home_hero_image_%02d', $tbt_index), (string) ($tbt_fallback['url'] ?? ''));
                $tbt_position = $tbt_index === 1 ? '50% 75%' : (string) ($tbt_fallback['objectPosition'] ?? '50% 50%');
            ?>
                <figure class="tbt-home-carousel__slide<?php echo $tbt_index === 1 ? ' is-active' : ''; ?>" data-carousel-slide<?php echo $tbt_index > 1 ? ' hidden' : ''; ?>>
                    <img data-media-key="<?php echo esc_attr($tbt_key); ?>" src="<?php echo esc_url($tbt_image['url']); ?>" alt="<?php echo esc_attr($tbt_image['alt'] ?: 'ThatBioTutor Biology classroom ' . $tbt_index); ?>" style="object-position:<?php echo esc_attr($tbt_position); ?>" width="<?php echo esc_attr((string) $tbt_image['width']); ?>" height="<?php echo esc_attr((string) $tbt_image['height']); ?>"<?php echo $tbt_index === 1 ? ' fetchpriority="high"' : ' loading="lazy"'; ?>>
                </figure>
            <?php endforeach; ?>
        </div>
        <button class="tbt-carousel__control tbt-carousel__control--previous" type="button" data-carousel-prev aria-label="Previous image">←</button>
        <button class="tbt-carousel__control tbt-carousel__control--next" type="button" data-carousel-next aria-label="Next image">→</button>
        <div class="tbt-carousel__dots" aria-label="Choose image">
            <?php foreach (range(1, 5) as $tbt_index) : ?><button type="button" data-carousel-dot aria-label="Show image <?php echo esc_attr((string) $tbt_index); ?>" aria-current="<?php echo $tbt_index === 1 ? 'true' : 'false'; ?>"></button><?php endforeach; ?>
        </div>
        <p class="screen-reader-text" data-carousel-status aria-live="polite">1 of 5</p>
    </section>

    <section class="tbt-section tbt-home-hero" data-section="grade-jump">
        <p class="tbt-eyebrow">Singapore’s O-Level Pure / G3 Biology Specialists</p>
        <h1 class="tbt-heading"><?php echo esc_html($tbt_home_field('home_heading')); ?></h1>
        <p class="tbt-home-hero__lead">Proven content understanding, memory shortcuts, and answering skills—built for the O-Level Biology syllabus.</p>
        <a class="tbt-button" href="<?php echo esc_url(home_url('/contact/')); ?>"><?php echo esc_html($tbt_home_field('home_primary_cta_label')); ?></a>
    </section>

    <section class="tbt-section tbt-section--soft tbt-video-story" data-section="missing-key">
        <h2 class="tbt-heading tbt-heading--section"><?php echo esc_html($tbt_home_field('home_missing_key_heading')); ?></h2>
        <div class="tbt-video"><iframe src="https://www.youtube-nocookie.com/embed/q_IPfEdODqI" title="The missing key to Biology improvement" loading="lazy" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" allowfullscreen></iframe></div>
    </section>

    <section class="tbt-section tbt-fasttrack" data-section="fasttrack">
        <h2 class="tbt-heading tbt-heading--section"><?php echo esc_html($tbt_home_field('home_fasttrack_heading')); ?></h2>
        <div class="tbt-fasttrack__grid">
            <?php foreach ([
                'curated_shared_fasttrack_content' => ['Content & Understanding', 'Detailed visual notes and clear concept-first teaching.'],
                'curated_shared_fasttrack_memory' => ['Memory', 'Memorable shortcuts and flashcards for long-term retention.'],
                'curated_shared_fasttrack_answering' => ['Answering Skills (OEQ)', 'The FIRED method turns knowledge into precise exam answers.'],
                'curated_shared_fasttrack_practical' => ['Practical Skills', 'Real practical sessions create real confidence.'],
                'curated_shared_fasttrack_support' => ['Support', 'Week-long guidance keeps students moving between lessons.'],
            ] as $tbt_key => [$tbt_title, $tbt_copy]) : ?>
                <article class="tbt-fasttrack-card"><?php $tbt_media_image($tbt_key, 'tbt-fasttrack-card__image'); ?><div><h3><?php echo esc_html($tbt_title); ?></h3><p><?php echo esc_html($tbt_copy); ?></p></div></article>
            <?php endforeach; ?>
        </div>
    </section>

    <section class="tbt-section tbt-section--dark tbt-trust" data-section="trust">
        <h2><?php echo esc_html($tbt_home_field('home_trust_heading')); ?></h2>
        <div class="tbt-stats">
            <?php foreach (($tbt_reference['stats']['items'] ?? []) as $tbt_stat) : ?><div class="tbt-stat"><strong><?php echo esc_html((string) $tbt_stat['value']); ?></strong><span><?php echo esc_html((string) $tbt_stat['label']); ?></span></div><?php endforeach; ?>
        </div>
    </section>

    <section class="tbt-section tbt-hall" data-section="hall-of-fame">
        <h2>TBT Hall of Fame</h2>
        <?php $tbt_media_image('curated_shared_hall_of_fame', 'tbt-hall__image'); ?>
        <div class="tbt-results-artwork"><?php foreach (range(1, 4) as $tbt_index) : tbt_render_page_image(sprintf('home_number_image_%02d', $tbt_index), sprintf('home_number_%02d', $tbt_index), 'tbt-contain-artwork', ['loading' => 'lazy']); endforeach; ?></div>
    </section>

    <section class="tbt-section tbt-section--soft" data-section="testimonials">
        <h2><?php echo esc_html($tbt_home_field('home_testimonials_heading')); ?></h2>
        <?php tbt_render_testimonials(['posts_per_page' => 3]); ?>
    </section>

    <section class="tbt-section tbt-schools" data-section="schools">
        <h2><?php echo esc_html($tbt_home_field('home_schools_heading')); ?></h2>
        <div class="tbt-school-marquee" data-school-marquee><div class="tbt-school-marquee__track">
            <?php foreach (range(1, 12) as $tbt_index) : $tbt_media_image(sprintf('curated_shared_school_crest_%02d', $tbt_index), 'tbt-school-crest', 'data-school-crest'); endforeach; ?>
        </div></div>
    </section>

    <section class="tbt-section tbt-advantage" data-section="advantage">
        <h2 class="tbt-heading tbt-heading--section"><?php echo esc_html($tbt_home_field('home_advantage_heading')); ?></h2>
        <div class="tbt-advantage__grid">
            <article><h3>In-Depth Understanding: No More Feeling Lost!</h3><div class="tbt-video"><iframe src="https://www.youtube-nocookie.com/embed/MX8tv9NMt9Q" title="In-depth Biology understanding" loading="lazy" allowfullscreen></iframe></div></article>
            <article><h3>Memory Shortcut - How ADH Works</h3><div class="tbt-video"><iframe src="https://www.youtube-nocookie.com/embed/e8F2G0P4dEA" title="Biology memory shortcut" loading="lazy" allowfullscreen></iframe></div></article>
            <article><h3>OEQ Skills - Voluntary Vs Reflex OEQ</h3><div class="tbt-video"><iframe src="https://www.youtube-nocookie.com/embed/lcD2d2mcgps" title="Open-ended question skills" loading="lazy" allowfullscreen></iframe></div></article>
        </div>
        <h3>Student Benefits</h3>
        <ul class="tbt-benefits"><li>Concise visual notes</li><li>Memory shortcuts</li><li>Answering frameworks</li><li>Lesson recordings and support</li></ul>
    </section>

    <section class="tbt-section tbt-section--soft tbt-experience" data-section="experience">
        <h2><?php echo esc_html($tbt_home_field('home_experience_heading')); ?></h2>
        <div class="tbt-media-grid"><?php foreach (range(1, 3) as $tbt_index) : $tbt_media_image(sprintf('curated_shared_practical_%02d', $tbt_index), 'tbt-media-grid__image', 'data-practical-image'); endforeach; ?></div>
        <div class="tbt-media-grid"><?php foreach (range(1, 3) as $tbt_index) :
            $tbt_key = sprintf('home_classroom_%02d', $tbt_index);
            $tbt_fallback = tbt_media($tbt_key);
            $tbt_image = tbt_page_image(sprintf('home_classroom_image_%02d', $tbt_index), (string) ($tbt_fallback['url'] ?? ''));
        ?><img data-classroom-image data-media-key="<?php echo esc_attr($tbt_key); ?>" src="<?php echo esc_url($tbt_image['url']); ?>" alt="<?php echo esc_attr($tbt_image['alt'] ?: 'Life inside the ThatBioTutor classroom'); ?>" loading="lazy"><?php endforeach; ?></div>
    </section>

    <section class="tbt-section tbt-causes" data-section="causes"><h2><?php echo esc_html($tbt_home_field('home_causes_heading')); ?></h2><p>Students help choose the environmental causes ThatBioTutor supports.</p><a class="tbt-button" href="<?php echo esc_url(home_url('/trees/')); ?>">See Our Causes</a></section>
    <section class="tbt-section tbt-section--soft" data-section="tutors"><h2><?php echo esc_html($tbt_home_field('home_tutors_heading')); ?></h2><?php tbt_render_tutors(); ?></section>
    <section class="tbt-section" data-section="faq"><h2><?php echo esc_html($tbt_home_field('home_faq_heading')); ?></h2><?php tbt_render_faqs('home'); ?></section>
    <section class="tbt-section tbt-section--dark tbt-closing"><h2><?php echo esc_html($tbt_home_field('home_closing_heading')); ?></h2><a class="tbt-button" href="<?php echo esc_url(home_url('/contact/')); ?>">Book a Trial Lesson</a></section>
</div>
<?php
get_footer();

