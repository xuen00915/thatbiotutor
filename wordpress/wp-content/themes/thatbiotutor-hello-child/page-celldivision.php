<?php
/* Template Name: Cell Division Crash Course */
declare(strict_types=1);
if (!defined('ABSPATH')) { exit; }
get_header();
?>
<article class="tbt-resource-page tbt-cell-page">
    <header class="tbt-resource-hero tbt-cell-hero" data-section="hero">
        <div class="tbt-resource-shell tbt-resource-hero__inner">
            <h1>Free <span>Cell Division</span> Crash Course</h1>
            <p class="tbt-cell-hero__notice">Only For 2023 Sec 4 Pure Bio Students!</p>
            <div class="tbt-resource-carousel tbt-resource-carousel--cell" data-carousel data-carousel-autoplay="7000">
                <div class="tbt-resource-carousel__viewport">
                    <?php foreach (range(1, 5) as $tbt_index) : $tbt_key = sprintf('curated_celldivision_slide_%02d', $tbt_index); ?>
                        <figure class="tbt-resource-carousel__slide" data-carousel-slide<?php echo $tbt_index > 1 ? ' hidden' : ''; ?>>
                            <?php tbt_render_page_image(sprintf('celldivision_slider_image_%02d', $tbt_index), $tbt_key, '', ['loading' => $tbt_index === 1 ? 'eager' : 'lazy']); ?>
                        </figure>
                    <?php endforeach; ?>
                </div>
                <button type="button" class="tbt-resource-carousel__control tbt-resource-carousel__control--previous" data-carousel-prev aria-label="Previous crash-course preview">&#10094;</button>
                <button type="button" class="tbt-resource-carousel__control tbt-resource-carousel__control--next" data-carousel-next aria-label="Next crash-course preview">&#10095;</button>
                <div class="tbt-resource-carousel__dots" aria-label="Crash-course previews">
                    <?php foreach (range(1, 5) as $tbt_index) : ?><button type="button" data-carousel-dot aria-label="Show crash-course preview <?php echo esc_attr((string) $tbt_index); ?>"></button><?php endforeach; ?>
                </div>
                <p class="screen-reader-text" data-carousel-status aria-live="polite">1 of 5</p>
            </div>
            <a class="tbt-resource-button" href="#cell-access">GET ACCESS</a>
            <p class="tbt-resource-proof">[100+ Students Have Gotten Theirs]</p>
        </div>
    </header>

    <section class="tbt-resource-section tbt-cell-problems" data-section="problems">
        <div class="tbt-resource-shell tbt-resource-narrow">
            <h2>Do you HATE cell division because:</h2>
            <ul class="tbt-cell-problem-list">
                <li>SO much tedious content, can't memorise!</li>
                <li>Your school rushed through it, leaving you with many doubts</li>
                <li>You feel lost as you never got a good foundation</li>
            </ul>
            <h3>Maybe it's these CONTENT issues:</h3>
            <ul class="tbt-cell-content-list">
                <li>Identifying mitosis/meiosis stages</li>
                <li>Confused about changes in amount of DNA and number of chromosomes</li>
                <li>Don't understand crossing over, independent assortment</li>
                <li>So many difficult terms</li>
            </ul>
            <p class="tbt-cell-problems__answer">If you answered 'yes' to the above, this crash course is for you. I've cut out clips from my lessons on important cell division concepts—so you can quickly go through them as a “crash course” and fix your doubts ASAP.</p>
        </div>
    </section>

    <section class="tbt-resource-section tbt-cell-features" data-section="features">
        <div class="tbt-resource-shell">
            <div class="tbt-cell-features__grid">
                <?php
                $tbt_features = [
                    ['curated_celldivision_replayable_video', 'Convenient.', 'Easy to watch. Infinitely re-playable.'],
                    ['curated_celldivision_analogy', 'Fun analogies', 'For you to easily understand those “super hard” concepts like independent assortment.'],
                    ['curated_celldivision_memory_shortcuts', 'Memory shortcuts', 'Includes my very own memory shortcuts for you to memorise faster and recall easier in exams.'],
                ];
                foreach ($tbt_features as [$tbt_media_key, $tbt_title, $tbt_copy]) :
                ?>
                    <article class="tbt-cell-feature">
                        <?php tbt_render_media_image($tbt_media_key, '', ['loading' => 'lazy']); ?>
                        <h3><?php echo esc_html($tbt_title); ?></h3>
                        <p><?php echo esc_html($tbt_copy); ?></p>
                    </article>
                <?php endforeach; ?>
            </div>
            <a class="tbt-resource-button" href="#cell-access">GIMME MINE</a>
        </div>
    </section>

    <section class="tbt-resource-section tbt-cell-important" data-section="important">
        <div class="tbt-resource-shell tbt-resource-narrow">
            <h2>IMPORTANT:</h2>
            <p>This will expire in 2023 and is only for Sec 4 Pure Bio students taking Os in 2023. This historical 2023 resource page is retained from the original ThatBioTutor website; no current enrolment claim is implied.</p>
        </div>
    </section>

    <section class="tbt-cell-access" id="cell-access" data-section="access">
        <div class="tbt-cell-access__dark">
            <div class="tbt-resource-shell tbt-resource-narrow">
                <h2>How do I access it?</h2>
                <p>Enter your favourite email address and a copy will be sent to you. The original page used this to make sure you were a real person.</p>
            </div>
        </div>
        <div class="tbt-resource-section tbt-cell-access__promise">
            <div class="tbt-resource-shell tbt-resource-narrow">
                <h3>Simply Enter Your Email Below And A Copy Will Be Sent To You.</h3>
                <a class="tbt-resource-button" href="<?php echo esc_url(home_url('/contact/')); ?>">CONTACT THATBIOTUTOR</a>
            </div>
        </div>
        <div class="tbt-resource-section tbt-cell-access__instructions">
            <div class="tbt-resource-shell tbt-resource-narrow">
                <p><strong>IMPORTANT:</strong> After successfully submitting the original form, students were automatically redirected to the homepage.</p>
                <p>They then received an email; in Gmail, it might be found in the “Promotions” folder.</p>
                <p>Having issues receiving it? Email keefe@thatbiotutor.com, or use the contact page, and the team will help.</p>
            </div>
        </div>
    </section>

    <section class="tbt-resource-section tbt-resource-reviews tbt-cell-reviews" data-section="reviews">
        <div class="tbt-resource-shell">
            <h2>What Students Are Saying About The Crash Course:</h2>
            <div data-testimonial-renderer><?php tbt_render_testimonials(['posts_per_page' => -1, 'meta_query' => [['key' => 'tbt_display_group', 'value' => 'celldivision-reviews', 'compare' => '=']]]); ?></div>
        </div>
    </section>
</article>
<?php get_footer(); ?>
