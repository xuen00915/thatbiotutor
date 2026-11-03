<?php
/* Template Name: Free Bio Notes */
declare(strict_types=1);
if (!defined('ABSPATH')) { exit; }
get_header();
$tbt_defaults = tbt_reference_content()['pages']['notes']['fields'];
$tbt_field = static fn (string $name): string => (string) tbt_page_field($name, $tbt_defaults[$name] ?? '');
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
            <h2>Simply Enter Your Email Below And A Copy Will Be Sent To You.</h2>
            <p>After submitting the original form, students were redirected to the homepage and received the notes by email. If you have issues receiving them, contact keefe@thatbiotutor.com.</p>
            <a class="tbt-resource-button" href="#notes-download">GET THE NOTES</a>
        </div>
    </section>

    <section class="tbt-resource-section tbt-notes-fit" data-section="fit">
        <div class="tbt-resource-shell tbt-resource-narrow">
            <h2>Hey Pure Bio Student, is this you?</h2>
            <ul class="tbt-notes-pain-list">
                <li>Your school notes/textbook is thick and confusing</li>
                <li>You're not sure what is the most important content to focus on</li>
                <li>You wish there were ways to memorise content faster and easier</li>
                <li>You wish you knew what the commonly tested questions are, and how to answer them with full marks</li>
                <li>You really want to improve in bio fast</li>
            </ul>
            <p class="tbt-notes-fit__answer">If you answered 'yes' to the above, these notes were made for you. I spent hundreds of hours making these concise notes so that students like you can have an easy time studying.</p>
        </div>
    </section>

    <section class="tbt-resource-section tbt-notes-benefits" data-section="benefits">
        <div class="tbt-resource-shell">
            <h2>ThatBioTutor's Notes</h2>
            <p class="tbt-resource-section__intro">Built to make content easier to understand, remember, and apply in exam questions.</p>
            <div class="tbt-notes-benefits__grid">
                <?php
                $tbt_benefits = [
                    'concise' => ['Concise Notes', "Concise notes which cut out the clutter, so you know what's important to focus on."],
                    'memory' => ['Memory Shortcuts', 'Includes our very own memory shortcuts for you to memorise faster and recall easier in exams.'],
                    'questions' => ['Commonly Asked Questions', "I've looked through many papers and assessment books to draw out the commonly asked questions for each topic, so you know which MCQs and OEQs are important to study for."],
                    'reviewed' => ['Ex-MOE Teacher Reviewed', 'Our syllabus and notes have been reviewed by an Ex-MOE teacher of 11 years.'],
                ];
                foreach ($tbt_benefits as $tbt_key => [$tbt_title, $tbt_copy]) :
                ?>
                    <article class="tbt-notes-benefit" data-notes-benefit>
                        <div class="tbt-notes-benefit__media"><?php tbt_render_media_image('curated_notes_notes_benefit_' . $tbt_key, '', ['loading' => 'lazy']); ?></div>
                        <div><h3><?php echo esc_html($tbt_title); ?></h3><p><?php echo esc_html($tbt_copy); ?></p></div>
                    </article>
                <?php endforeach; ?>
            </div>
            <a class="tbt-resource-button" href="#notes-download">DOWNLOAD</a>
        </div>
    </section>

    <section class="tbt-resource-section tbt-resource-faq-block" data-section="why-free">
        <div class="tbt-resource-shell tbt-resource-narrow">
            <h2>Why are you giving your notes for FREE?</h2>
            <p>I get tons of messages requesting notes, so this must be a big need in the community. A lot of you are needlessly struggling in this subject, and I don't want you to be one of them.</p>
            <p>If you are, maybe you're not getting the quality of teaching and materials you need. It shouldn't be this way, because bio is an easy subject, and you deserve to score well in it.</p>
            <p>I was in your position before, and I wished someone gave me notes like these to make my life easier. Now I get to make it a reality for you, and that brings me lots of joy.</p>
        </div>
    </section>

    <section class="tbt-resource-section tbt-resource-faq-block tbt-resource-faq-block--soft" data-section="catch">
        <div class="tbt-resource-shell tbt-resource-narrow">
            <h2>This sounds too good to be true. What's the catch?</h2>
            <p>I do this because I'd like to earn your trust, by giving you results in advance.</p>
            <p>I want you to know that this is the real deal. It's my hope that those of you who want bio tuition will choose TBT when looking for bio tuition :)</p>
        </div>
    </section>

    <section class="tbt-resource-section tbt-notes-download" id="notes-download" data-section="download">
        <div class="tbt-resource-shell tbt-resource-narrow">
            <h2>How do I download it?</h2>
            <p>Enter your best email address and a copy will be sent to you. This is to make sure you're a real person, and if the notes are updated in future, the updated version can be sent automatically.</p>
            <p>The original website used an embedded form. This portable WordPress version keeps the access boundary clear: contact the team for the current download link.</p>
            <a class="tbt-resource-button" href="<?php echo esc_url(home_url('/contact/')); ?>">REQUEST THE NOTES</a>
        </div>
    </section>

    <section class="tbt-resource-section tbt-resource-reviews" data-section="reviews-bottom">
        <div class="tbt-resource-shell">
            <h2>What Students Are Saying...</h2>
            <div data-testimonial-renderer><?php tbt_render_testimonials(['posts_per_page' => -1, 'offset' => 5, 'meta_query' => [['key' => 'tbt_display_group', 'value' => 'notes-reviews', 'compare' => '=']]]); ?></div>
        </div>
    </section>
</article>
<?php get_footer(); ?>
