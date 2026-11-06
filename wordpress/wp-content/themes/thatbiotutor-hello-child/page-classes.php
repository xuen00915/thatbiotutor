<?php
/* Template Name: Classes */
declare(strict_types=1);
if (!defined('ABSPATH')) { exit; }
get_header();
$tbt_defaults = tbt_reference_content()['pages']['classes']['fields'];
$tbt_field = static fn (string $name): string => (string) tbt_page_field($name, $tbt_defaults[$name] ?? '');
$tbt_whatsapp = tbt_whatsapp_url('Hi ThatBioTutor, I would like help choosing the right Pure/G3 Biology class.');
$tbt_heading = static function (string $heading): void {
    if ($heading === 'The AVERAGE TBT Student Makes a 2.3 Grade Jump (~C5 to A2)') {
        echo '<span class="tbt-programme-hero__line">The <em>AVERAGE</em> TBT Student</span>';
        echo '<span class="tbt-programme-hero__line">Makes a <em>2.3</em> Grade Jump</span>';
        echo '<span class="tbt-programme-hero__line tbt-programme-hero__line--green">(~C5 to A2)</span>';
        return;
    }
    $parts = preg_split('/(AVERAGE|2\.3|\(~C5 to A2\))/u', $heading, -1, PREG_SPLIT_DELIM_CAPTURE);
    foreach ($parts ?: [$heading] as $part) {
        echo in_array($part, ['AVERAGE', '2.3', '(~C5 to A2)'], true) ? '<span>' . esc_html($part) . '</span>' : esc_html($part);
    }
};
?>
<article class="tbt-programme-page tbt-classes-page">
    <header class="tbt-programme-hero tbt-programme-hero--classes" data-section="hero">
        <div class="tbt-programme-hero__background"><?php tbt_render_media_image('curated_heroes_classes_original', '', ['loading' => 'eager']); ?></div>
        <div class="tbt-programme-hero__shade" aria-hidden="true"></div>
        <div class="tbt-programme-hero__content">
            <p class="tbt-programme-hero__eyebrow"><?php echo esc_html($tbt_field('classes_eyebrow')); ?></p>
            <h1><?php $tbt_heading($tbt_field('classes_heading')); ?></h1>
            <p class="tbt-programme-hero__lead"><?php echo esc_html($tbt_field('classes_hero_lead')); ?></p>
            <div class="tbt-programme-hero__actions"><a class="tbt-programme-button" href="<?php echo esc_url(home_url('/sec-3-pure-g3-biology-tuition/')); ?>">I am in Sec 3&nbsp; →</a><a class="tbt-programme-button" href="<?php echo esc_url(home_url('/sec-4-pure-g3-biology-tuition/')); ?>">I am in Sec 4&nbsp; →</a></div>
        </div>
    </header>

    <section class="tbt-programme-section tbt-programme-definition" data-section="definition">
        <div class="tbt-programme-container">
            <p class="tbt-programme-kicker">What is Pure/G3 Biology?</p>
            <h2><?php echo esc_html($tbt_field('classes_intro_heading')); ?></h2>
            <div class="tbt-programme-definition__grid">
                <?php tbt_render_media_image('curated_classes_pure_vs_combined', 'tbt-programme-artwork', ['loading' => 'lazy']); ?>
                <div class="tbt-programme-copy"><p><?php echo esc_html($tbt_field('classes_intro_copy')); ?></p><div class="tbt-topic-columns"><div><strong>Sec 3 Topics:</strong><ol><li>Cells</li><li>Movement of substances</li><li>Biomolecules</li><li>Enzymes</li><li>Nutrition in humans</li><li>Transport in humans</li><li>Respiration</li><li>Excretion</li><li>Homeostasis</li><li>Nervous system and The Eye</li></ol></div><div><strong>Sec 4 Topics:</strong><ol start="11"><li>Infectious diseases</li><li>Nutrition in plants</li><li>Transport in plants</li><li>Organisms and their environment</li><li>Molecular genetics</li><li>Reproduction in plants</li><li>Reproduction in humans</li><li>Inheritance</li></ol></div></div></div>
            </div>
        </div>
    </section>

    <section class="tbt-programme-section tbt-programme-struggles" data-section="struggles">
        <div class="tbt-programme-container"><p class="tbt-programme-kicker">Why students struggle</p><h2><?php echo esc_html($tbt_field('classes_struggle_heading')); ?></h2>
            <div class="tbt-struggle-list">
                <article><h3>1. <span>Too much content</span> to memorize</h3><p>Pure Bio has 9 topics with hundreds of key terms, definitions and processes. Students try to brute-force memorise everything — and it does not work under exam conditions.</p></article>
                <article><h3>2. Open-ended questions are <span>unpredictable</span></h3><p>Students who understand the content still drop marks on Paper 2 because they do not know how to structure answers. OEQ marking is strict — and school rarely teaches answering technique.</p></article>
                <article><h3>3. Topics feel <span>disconnected</span></h3><p>Biology topics link to each other — but most students and even some tutors teach them in isolation. Without the bigger picture, students cannot answer application questions.</p></article>
                <article><h3>4. Hands-on practice is <span>limited</span></h3><p>Many students get little hands-on practice in school, making Paper 3 feel like a gamble. Our optional practical sessions cover commonly tested experiments, building the coordination and confidence to protect the full 20%.</p></article>
            </div>
        </div>
    </section>

    <section class="tbt-programme-section tbt-programme-solutions" data-section="solutions"><div class="tbt-programme-container"><h2><?php echo esc_html($tbt_field('classes_solution_heading')); ?></h2><div class="tbt-solution-list">
        <article><h3>Problem 1 — Too much content</h3><p><strong>TBT FastTrack System</strong> — memory shortcuts and mnemonics built into every lesson. Students learn how to remember, not just what to remember.</p></article>
        <article><h3>Problem 2 — OEQ technique</h3><p>TBT teaches the <strong>FIRED method</strong> — a structured answering framework that earns full marks consistently. Practised with real past-year questions in every lesson.</p></article>
        <article><h3>Problem 3 — Seeing the big picture</h3><p>TBT's lessons are built to show how topics connect. Students develop integrated understanding that application questions test — not just topic-by-topic recall.</p></article>
        <article><h3>Problem 4 — Limited practical experience</h3><p><strong>TBT Optional Practical Sessions</strong> — optional hands-on sessions covering commonly tested experiments. Students build the coordination, familiarity and confidence needed to handle Paper 3 setups and protect the full 20%.</p></article>
    </div></div></section>

    <section class="tbt-programme-section tbt-programme-difference" data-section="difference"><div class="tbt-programme-container"><p class="tbt-programme-kicker">The TBT difference</p><h2><?php echo esc_html($tbt_field('classes_difference_heading')); ?></h2>
        <div class="tbt-difference-layout"><div class="tbt-difference-list"><article><h3>In-depth understanding — not just memorisation</h3><p>At TBT, concepts are taught in-depth so students understand each topic deeply — how and why biological processes work. This is what makes the difference in application questions.</p></article><article><h3>Memory shortcuts built into every topic</h3><p>Mr. Keefe and the TBT team have developed mnemonics and memory hacks for every major topic. Students remember content without brute-force repetition.</p></article><article><h3>OEQ answering technique — the FIRED method</h3><p>Every Paper 2 answer is structured using a tested framework. Students know exactly what to write — and what to leave out — to earn full marks.</p></article><article><h3>Ex-MOE teacher reviewed content</h3><p>All TBT notes have been reviewed by Ms Ng, an ex-MOE teacher with 11 years of experience. Notes are constantly updated for syllabus accuracy.</p></article></div><?php tbt_render_media_image('curated_classes_fasttrack_study_prep', 'tbt-programme-artwork', ['loading' => 'lazy']); ?></div>
        <div class="tbt-practical-feature"><h3>Real Practical Sessions. Real Practice.</h3><p>Paper 3 makes up 20% of the grade, and most students walk in undertrained. TBT students can opt in to additional hands-on sessions, training on the exact experiment types most commonly tested in Paper 3, well before exam day.</p><div class="tbt-practical-grid"><?php foreach (range(1, 3) as $tbt_index) { tbt_render_media_image(sprintf('curated_shared_practical_%02d', $tbt_index), 'tbt-practical-photo', ['loading' => 'lazy']); } ?></div></div>
    </div></section>

    <section class="tbt-programme-section tbt-programme-results" data-section="results"><div class="tbt-programme-container"><p class="tbt-programme-kicker">By the numbers</p><h2>Results that speak for themselves</h2><div class="tbt-programme-stats tbt-programme-stats--three"><div><strong>2.3</strong><span>Average grade jump per student</span></div><div><strong>912+</strong><span>Students taught across 98 schools</span></div><div><strong>91%</strong><span>Score A1, A2 or B3</span></div></div></div></section>

    <section class="tbt-programme-section tbt-programme-levels" data-section="levels"><div class="tbt-programme-container"><p class="tbt-programme-kicker">Choose your level</p><h2><?php echo esc_html($tbt_field('classes_level_heading')); ?></h2><p>Each level has its own programme details, schedule, and what to expect — click through to the right one for you.</p><div class="tbt-programme-levels__grid"><a href="<?php echo esc_url(home_url('/sec-3-pure-g3-biology-tuition/')); ?>"><strong>Sec 3 Pure / G3 Biology Classes</strong><span>Build the right foundation from the start — before the pressure of O Levels hits.</span><b>View Sec 3 programme →</b></a><a href="<?php echo esc_url(home_url('/sec-4-pure-g3-biology-tuition/')); ?>"><strong>Sec 4 Pure / G3 Biology Classes</strong><span>Score A1 in O Levels — even if you are starting from B4 or lower.</span><b>View Sec 4 programme →</b></a></div><?php if ($tbt_whatsapp !== '') : ?><p class="tbt-programme-levels__help">Not sure which one applies to you?<br><a href="<?php echo esc_url($tbt_whatsapp); ?>" target="_blank" rel="noopener noreferrer">WhatsApp us — we'll help you figure it out →</a></p><a class="tbt-programme-button tbt-programme-levels__button" href="<?php echo esc_url($tbt_whatsapp); ?>" target="_blank" rel="noopener noreferrer">WhatsApp Us Now!</a><?php endif; ?></div></section>

    <section class="tbt-programme-section tbt-programme-faq" data-section="faq"><div class="tbt-programme-container"><p class="tbt-programme-kicker">Common questions</p><h2>Questions About O Level Pure/G3 Biology</h2><?php tbt_render_faqs('classes'); ?></div></section>
</article>
<?php get_footer(); ?>
