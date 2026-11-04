<?php
/* Template Name: Free Bio Notes */
declare(strict_types=1);
if (!defined('ABSPATH')) { exit; }
get_header();
$tbt_defaults = tbt_reference_content()['pages']['notes']['fields'];
$tbt_field = static fn (string $name): string => (string) tbt_page_field($name, $tbt_defaults[$name] ?? '');
$tbt_notes_portrait = tbt_media('original_003');
?>
<article class="tbt-resource-page tbt-notes-page">
    <header class="tbt-resource-hero tbt-notes-hero" data-section="hero">
        <div class="tbt-resource-shell tbt-resource-hero__inner">
            <h1><?php echo esc_html($tbt_field('notes_heading')); ?></h1>
            <p class="tbt-notes-hero__audience"><?php echo esc_html($tbt_field('notes_audience')); ?></p>
            <div class="tbt-resource-carousel tbt-resource-carousel--notes" data-carousel data-carousel-autoplay="7000">
                <div class="tbt-resource-carousel__viewport">
                    <?php foreach (range(1, 3) as $tbt_index) : $tbt_key = sprintf('curated_notes_notes_slide_%02d', $tbt_index); ?>
                        <figure class="tbt-resource-carousel__slide" data-carousel-slide<?php echo $tbt_index > 1 ? ' hidden' : ''; ?>>
                            <?php tbt_render_page_image(sprintf('notes_slider_image_%02d', $tbt_index), $tbt_key, '', ['loading' => $tbt_index === 1 ? 'eager' : 'lazy']); ?>
                        </figure>
                    <?php endforeach; ?>
                </div>
                <button type="button" class="tbt-resource-carousel__control tbt-resource-carousel__control--previous" data-carousel-prev aria-label="Previous notes preview">&#10094;</button>
                <button type="button" class="tbt-resource-carousel__control tbt-resource-carousel__control--next" data-carousel-next aria-label="Next notes preview">&#10095;</button>
                <div class="tbt-resource-carousel__dots" aria-label="Notes previews">
                    <?php foreach (range(1, 3) as $tbt_index) : ?><button type="button" data-carousel-dot aria-label="Show notes preview <?php echo esc_attr((string) $tbt_index); ?>"></button><?php endforeach; ?>
                </div>
                <p class="screen-reader-text" data-carousel-status aria-live="polite">1 of 3</p>
            </div>
            <a class="tbt-resource-button" href="#notes-download">DOWNLOAD</a>
            <p class="tbt-resource-proof">[7,000+ downloads]</p>
        </div>
    </header>

    <section class="tbt-resource-section tbt-resource-reviews tbt-resource-reviews--lead" data-section="reviews-top">
        <div class="tbt-resource-shell">
            <h2>What Students Are Saying...</h2>
            <div data-testimonial-renderer><?php tbt_render_testimonials(['posts_per_page' => -1, 'meta_query' => [['key' => 'tbt_display_group', 'value' => 'notes-reviews', 'compare' => '=']]]); ?></div>
        </div>
    </section>

    <section class="tbt-resource-section tbt-notes-access-intro" data-section="access-intro">
        <div class="tbt-resource-shell tbt-resource-narrow">
            <img class="tbt-resource-access-portrait" data-resource-portrait="notes-access" data-media-key="original_003" src="<?php echo esc_url((string) $tbt_notes_portrait['url']); ?>" alt="Mr Keefe from ThatBioTutor" width="160" height="160" loading="lazy">
            <h2><?php echo esc_html($tbt_field('notes_access_heading')); ?></h2>
            <div class="tbt-resource-access-spacer" aria-hidden="true"></div>
            <div class="tbt-resource-access-instructions">
                <p><strong>IMPORTANT:</strong> <?php echo esc_html($tbt_field('notes_access_copy')); ?></p>
                <p>You'll then receive an email with our notes (on gmail, it might be found in your 'promotions' folder).</p>
                <p>Can't press the submit button/Having issues receiving it? Email <a href="mailto:keefe@thatbiotutor.com">keefe@thatbiotutor.com</a>, and I'll help you out ASAP.</p>
            </div>
        </div>
    </section>

    <section class="tbt-resource-section tbt-notes-fit" data-section="fit">
        <div class="tbt-resource-shell tbt-resource-narrow">
            <h2><?php echo esc_html($tbt_field('notes_fit_heading')); ?></h2>
            <ul class="tbt-notes-pain-list">
                <li>Your school notes/textbook is <strong>thick and confusing</strong></li>
                <li>You're not sure what is the most important content to <strong>focus on</strong></li>
                <li>You wish there were ways to <strong>memorise</strong> content faster and easier</li>
                <li>You wish you knew what the <strong>commonly tested questions</strong> are, and how to answer them with <strong>full marks</strong></li>
                <li>You really want to <strong>improve</strong> in bio fast</li>
            </ul>
            <p class="tbt-notes-fit__answer"><?php echo esc_html($tbt_field('notes_fit_answer')); ?></p>
        </div>
    </section>

    <section class="tbt-resource-section tbt-notes-benefits" data-section="benefits">
        <div class="tbt-resource-shell">
            <h2>ThatBioTutor's Notes</h2>
            <div class="tbt-notes-benefits__grid">
                <?php
                $tbt_benefits = [
                    'concise' => "Concise notes which cuts out the clutter, so you know what's important to <strong>focus on</strong>",
                    'memory' => 'Includes our very own <strong>memory shortcuts</strong> for you to memorise faster and recall easier in exams.',
                    'questions' => "I've looked through many papers and assessment books to draw out the <strong>commonly asked questions</strong> for each topic! Now you'll know which MCQs + OEQs are important to study for.",
                    'reviewed' => '<strong>Ex-MOE Teacher Reviewed:</strong> Our syllabus and notes have been reviewed by an Ex-MOE teacher of 11 years.',
                ];
                foreach ($tbt_benefits as $tbt_key => $tbt_copy) :
                ?>
                    <article class="tbt-notes-benefit" data-notes-benefit>
                        <div class="tbt-notes-benefit__media"><?php tbt_render_media_image('curated_notes_notes_benefit_' . $tbt_key, '', ['loading' => 'lazy']); ?></div>
                        <p><?php echo wp_kses_post($tbt_copy); ?></p>
                    </article>
                <?php endforeach; ?>
            </div>
            <a class="tbt-resource-button" href="#notes-download">DOWNLOAD</a>
        </div>
    </section>

    <div class="tbt-notes-details">
        <section class="tbt-resource-section tbt-resource-faq-block" data-section="why-free">
            <div class="tbt-resource-shell tbt-resource-narrow">
                <h2>Why are you giving your notes for <span>FREE</span>?</h2>
                <ul>
                    <li>I get tons of messages requesting notes, so this must be a big need in the community. A lot of you are <strong>needlessly struggling</strong> in this subject, and I don't want you to be one of them.</li>
                    <li>If you are, maybe you're not getting the quality of <strong>teaching and materials</strong> you need. It shouldn't be this way, because bio is an easy subject, and you deserve to score well in it.</li>
                    <li>I was in your position before, and I wished someone gave me notes like these to make my life <strong>easier</strong>. Now I get to make it a reality for you, and that brings me lots of joy.</li>
                </ul>
            </div>
        </section>

        <section class="tbt-resource-section tbt-resource-faq-block" data-section="catch">
            <div class="tbt-resource-shell tbt-resource-narrow">
                <h2>This sounds <span>too good to be true</span>. What's the catch?</h2>
                <ul>
                    <li>I do this because I'd like to earn your trust, by giving you results in advance.</li>
                    <li>I want you to know that this is the <strong>real deal</strong>. It's my hope that those of you who want bio tuition will choose TBT when looking for bio tuition :)</li>
                </ul>
            </div>
        </section>

        <section class="tbt-resource-section tbt-notes-download" id="notes-download" data-section="download">
            <div class="tbt-resource-shell tbt-resource-narrow">
                <h2>How do I <span>download</span> it?</h2>
                <ul>
                    <li><?php echo esc_html($tbt_field('notes_download_copy')); ?></li>
                    <li>(This is to make sure you're a real person, and if I update the notes in future, I'll send you the updated version automatically!)</li>
                </ul>
                <a class="tbt-resource-button" href="<?php echo esc_url(home_url('/contact/')); ?>">DOWNLOAD</a>
            </div>
        </section>
    </div>

    <section class="tbt-resource-section tbt-resource-reviews" data-section="reviews-bottom">
        <div class="tbt-resource-shell">
            <h2>What Students Are Saying...</h2>
            <div data-testimonial-renderer><?php tbt_render_testimonials(['posts_per_page' => -1, 'meta_query' => [['key' => 'tbt_display_group', 'value' => 'notes-reviews-bottom', 'compare' => '=']]]); ?></div>
        </div>
    </section>
</article>
<?php get_footer(); ?>
