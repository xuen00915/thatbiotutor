<?php

declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}

get_header();

$tbt_media_image = static function (string $key, string $class = '', string $attributes = ''): void {
    $media = tbt_media($key);
    if (($media['url'] ?? '') === '') {
        return;
    }
    echo '<img data-media-key="' . esc_attr($key) . '"';
    if ($class !== '') {
        echo ' class="' . esc_attr($class) . '"';
    }
    if ($attributes !== '') {
        echo ' ' . $attributes;
    }
    echo ' src="' . esc_url((string) $media['url']) . '" alt="' . esc_attr((string) ($media['alt'] ?? ''))
        . '" width="' . esc_attr((string) ($media['width'] ?? 0)) . '" height="' . esc_attr((string) ($media['height'] ?? 0)) . '">';
};

$tbt_whatsapp = tbt_whatsapp_url('Hi ThatBioTutor, I would like to book a $95 trial lesson. Please share the available timings.');
$tbt_stats = [
    ['display' => '912+', 'target' => '912', 'label' => 'Students taught, across 98 Singapore schools'],
    ['display' => '91%', 'target' => '91', 'label' => 'Score A1, A2, or B3', 'note' => 'despite 69% starting at B4 or lower'],
    ['display' => '2.3', 'target' => '2.3', 'label' => 'Average grade jump, roughly C5 to A2'],
    ['display' => '1 in 2', 'target' => '1', 'label' => 'Score A (Distinction)'],
];
$tbt_comparisons = [
    [
        'title' => 'Content & Understanding',
        'other' => "Dig through school notes and the textbook, figure out what's actually important, and rewrite it all from scratch.",
        'tbt' => 'Struggling to recall a topic? Just Ctrl+F through our detailed visual notes, or watch crash course clips (think: Netflix, but for Bio.)',
        'media' => 'curated_shared_fasttrack_content',
    ],
    [
        'title' => 'Memory',
        'other' => 'Read, reread, blurt out, copy out again and again and hope it sticks by exam day.',
        'tbt' => 'Confusing terms turn into memory shortcuts that stick, backed by ready-made flashcards for long-term retention. The logic clicks, so the material still sticks long after exams are over.',
        'media' => 'curated_shared_fasttrack_memory',
    ],
    [
        'title' => 'Answering Skills (OEQ)',
        'other' => 'Vomit out everything they know and hope it hits the right keywords.',
        'tbt' => 'Our Commonly Asked Questions database contains every question type, with the exact keywords and phrasing that score full marks. No guessing, and time to spare in the exam.',
        'media' => 'curated_shared_fasttrack_answering',
    ],
    [
        'title' => 'Practical Sessions (20% of Grade)',
        'other' => "Severely lack practice in school and hope the exam tests an experiment they've actually done before.",
        'tbt' => 'Optional hands-on practical sessions cover the most commonly tested experiments, so students walk into Paper 3 with the hand-eye coordination and confidence to protect the full 20%.',
        'media' => 'curated_shared_fasttrack_practical',
    ],
    [
        'title' => 'Support',
        'other' => 'Save up questions and wait for the next lesson.',
        'tbt' => 'Message your tutor anytime. Get help the same day instead of waiting 7 days, with detailed explanations accurate to the O Level syllabus.',
        'media' => 'curated_shared_fasttrack_support',
    ],
];
$tbt_tutors = [
    ['media' => 'curated_shared_tutor_keefe', 'name' => 'Mr Keefe', 'role' => 'Founder'],
    ['media' => 'curated_shared_tutor_chelsea', 'name' => 'Ms Chelsea', 'role' => 'Pure Biology Specialist'],
    ['media' => 'curated_shared_tutor_jamie', 'name' => 'Ms Jamie', 'role' => 'Pure Biology Specialist'],
    ['media' => 'curated_shared_tutor_ng', 'name' => 'Ms Ng', 'role' => 'Our Ex-MOE Academic Adviser'],
];
$tbt_faqs = [
    ['Do you teach physical/online lessons?', 'Yes. Starting 2024, students can choose between coming online or physically.'],
    ['Other than weekly lessons, what other perks are included?', 'Notes, flashcards, lesson clips, extra practice papers, WhatsApp help, and snacks for physical lessons. All are free for students.'],
    ['Do students need to turn on their mic/camera during online lessons?', 'Not compulsory, though students are encouraged to respond to questions via the Zoom chat.'],
    ['Is there a lot of homework?', 'A handful of questions a week, highly manageable. We don’t believe in overloading students. It is not compulsory, but completing it earns Homework Points for rewards.'],
    ['How many students are there in a class?', 'Physical classes are capped at around 20–25 students. Try it yourself in a trial lesson and see why so many students benefit greatly from our classes.'],
    ['What levels do you teach?', 'We specialise exclusively in O Level Pure/G3 Biology, not Combined Biology. Sec 3 classes open in March each year.'],
    ['Do you teach 1-1 tuition?', 'No, and that lets us build specialised notes, CAQs and memory shortcuts for this syllabus. Students still get 1-1 access for questions on WhatsApp and can get help the same day.'],
    ['Are all the classes taught by Mr Keefe?', 'No. There is a small team of tutors, with content and teaching methods standardised across all of them.'],
    ['Can students find out more about class fees and timeslots?', 'Our trial class is $95. After joining, fees vary depending on the class option chosen. Message us on WhatsApp for the latest timeslots and pricing.'],
];
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

    <section class="tbt-home-section tbt-home-guarantee" id="trial" data-section="guarantee">
        <div class="tbt-home-container tbt-home-guarantee__inner">
            <div class="tbt-home-guarantee__content">
                <h2>Join a Trial Lesson. If You Didn't Absolutely Love It, <span>You Can Get a Full Refund.</span></h2>
                <div class="tbt-home-guarantee__shield" data-guarantee-shield aria-hidden="true"><svg viewBox="0 0 64 72"><path d="M32 3 57 12v19c0 18-10 31-25 38C17 62 7 49 7 31V12L32 3Z"/><path d="m21 35 7 7 16-18"/></svg></div>
                <p>A trial lesson is $95. We’re so confident of our lesson quality that if you didn’t absolutely love it, simply ask for a refund and you’ll get your money back, no questions asked.</p>
                <?php if ($tbt_whatsapp !== '') : ?><a class="tbt-home-button" href="<?php echo esc_url($tbt_whatsapp); ?>" target="_blank" rel="noopener noreferrer">Book My Trial Lesson</a><?php endif; ?>
            </div>
        </div>
    </section>

    <section class="tbt-home-section tbt-home-fit" data-section="fit">
        <div class="tbt-home-container tbt-home-fit__grid">
            <article class="tbt-home-fit__yes"><h2>This is for:</h2><ul><li>Students taking O Level Pure/G3 Biology in Sec 3 or Sec 4.</li><li>Students who feel very lost in the content, or just need a few concepts strengthened.</li><li>Students who know the content but blank out on OEQs and lose marks anyway.</li><li>Students who genuinely want to improve in Bio, out of their own drive, not because someone else is forcing them.</li><li>Students based in Singapore who can attend physical or online classes.</li></ul></article>
            <article class="tbt-home-fit__no"><h2>This isn't for:</h2><ul><li>Students taking Combined Science Biology, not Pure/G3.</li><li>Students based outside Singapore.</li><li>Students only joining because their parents are making them, without wanting to improve themselves.</li><li>Students who aren't committed to putting in the work required to ace the subject.</li></ul></article>
        </div>
    </section>

    <section class="tbt-home-section tbt-home-founder" data-section="founder">
        <div class="tbt-home-container tbt-home-founder__inner">
            <figure><?php $tbt_media_image('curated_home_founder_standing', '', 'loading="lazy"'); ?></figure>
            <div><h2>About The Founder</h2><p>I was a GEP student, went through Raffles Institution, and was offered a scholarship by NUS to study Life Sciences. On paper, that probably sounds like someone who never struggled in school.</p><p>In JC, my grades started slipping and I finally took Econs tuition. That's when I understood what a genuinely good tutor could do: help me memorise smarter and answer questions in a way that actually scores.</p><p>That inspired me to build ThatBioTutor, because I wanted other students to have that same experience with Biology. That feeling of trying hard and still not getting anywhere is exactly why I built the <strong>FastTrack System</strong>.</p></div>
        </div>
    </section>

    <section class="tbt-home-section tbt-home-faq" data-section="faq">
        <div class="tbt-home-container">
            <h2>FAQ</h2>
            <div class="tbt-home-faq__list">
                <?php foreach ($tbt_faqs as $tbt_index => [$tbt_question, $tbt_answer]) : $tbt_panel_id = 'tbt-home-faq-' . ($tbt_index + 1); ?>
                    <article class="tbt-home-faq__item" data-home-inquire-faq><h3><button type="button" data-faq-button aria-expanded="false" aria-controls="<?php echo esc_attr($tbt_panel_id); ?>"><span><?php echo esc_html((string) ($tbt_index + 1) . '. ' . $tbt_question); ?></span><span aria-hidden="true">+</span></button></h3><div id="<?php echo esc_attr($tbt_panel_id); ?>" class="tbt-home-faq__answer" hidden><p><?php echo esc_html($tbt_answer); ?></p></div></article>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <section class="tbt-home-section tbt-home-classroom" data-section="classroom">
        <div class="tbt-home-container">
            <h2>Life inside a TBT classroom</h2>
            <div class="tbt-home-classroom__grid"><?php foreach (range(1, 3) as $tbt_index) : ?><figure><?php $tbt_media_image(sprintf('home_classroom_%02d', $tbt_index), '', 'loading="lazy"'); ?></figure><?php endforeach; ?></div>
        </div>
    </section>

    <section class="tbt-home-section tbt-home-closing" data-section="closing">
        <div class="tbt-home-container tbt-home-closing__inner">
            <figure><?php $tbt_media_image('curated_home_founder_about', '', 'loading="lazy"'); ?></figure>
            <div><h2>Bio doesn’t have to eat up every other part of a student’s life.</h2><p>Students shouldn’t have to give up their CCA. Or their friends. Or their other subjects, just to keep their Biology grades afloat.</p><p>That’s exactly why I built the FastTrack System. The notes, the shortcuts, and the support are already done for them. Studying smart takes a fraction of the time it used to.</p><p>Students still get to have a life outside textbooks. And score the grade they know they’re capable of.</p><p>Ready to see what that actually feels like?</p><div class="tbt-home-closing__cta-wrap"><?php if ($tbt_whatsapp !== '') : ?><a class="tbt-home-button" href="<?php echo esc_url($tbt_whatsapp); ?>" target="_blank" rel="noopener noreferrer">Book My Trial Lesson</a><?php endif; ?></div></div>
        </div>
    </section>
</div>
<?php
get_footer();
