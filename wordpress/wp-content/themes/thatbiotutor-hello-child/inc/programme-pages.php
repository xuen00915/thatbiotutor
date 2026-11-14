<?php

declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}

/*
 * Sec 3 and Sec 4 programme pages.
 *
 * The two pages share one layout and differ only in their words, dates, topics and a few pictures. This module
 * declares both pages' editable fields and renders both pages from the same markup, so the layout cannot drift
 * between them and every word, picture, video and link is a field the client can edit.
 */

/** Everything that differs between the Sec 3 and Sec 4 pages, as approved defaults. */
function tbt_programme_level(string $level): array
{
    $options = [
        ['Face-to-Face Classes', 'Burlington Square, Singapore', '<ul><li><strong>Weekly lessons</strong> — same content as online</li><li><strong>Snacks provided</strong> during class</li><li><strong>Hardcopy worksheets</strong> given out every lesson</li><li><strong>Lesson recording</strong> uploaded after each class</li><li><strong>Ask questions live</strong> during lesson</li><li><strong>Kahoot</strong> review games every week</li></ul><p>Recommended for students who are easily distracted online.<br>Students are also allowed to switch freely between modes each week.</p>'],
        ['Online Classes (Zoom)', 'From anywhere in Singapore', '<ul><li><strong>Weekly lessons</strong> via Zoom — same content as F2F</li><li>No travel time — <strong>join from home</strong> or anywhere</li><li><strong>Softcopy worksheets</strong> sent before each lesson</li><li><strong>Lesson recording</strong> uploaded after each class</li><li><strong>Ask questions live</strong> during lesson via Zoom chat</li><li><strong>Kahoot</strong> review games every week</li></ul><p>Mic/camera not required — communicate via Zoom chat.</p>'],
    ];
    $shared = [
        'options' => $options,
        'included' => "TBT Notes + Flashcards\nWeek-Long WhatsApp Support\nCAQ Model Answers\nExtra Practice Papers",
        'exam_list' => '<ul><li><strong>Answering skills</strong> — FIRED method for full marks in OEQs</li><li><strong>Data-based questions</strong> — how to interpret graphs and tables</li><li><strong>Application questions</strong> — linking topics to unseen scenarios</li><li><strong>Paper 3 practical</strong> — technique, table drawing, graph plotting</li><li><strong>Time management</strong> — which questions to prioritize and when to move on</li><li><strong>Past year paper (TYS) practice</strong> — topic by topic throughout the year</li></ul>',
        'exam_note' => '<strong>Exam technique</strong> is not a separate module — it is <strong>embedded in every lesson</strong> from the first class.',
        'hall_text' => '<strong>69.21%</strong> of TBT students started at <strong>B4 or lower.</strong> These are some of the biggest jumps.',
        'proofs' => [['F9 → A1', 'Biggest jump on record'], ['E8 → A1', 'Biggest jump in 3 months'], ['C5 → A2', 'Average grade jump']],
        'practical_title' => 'Real Practical Sessions. Real Practice.',
        'practical_text' => 'Paper 3 makes up 20% of the grade, and most students walk in undertrained. TBT students can opt in to additional hands-on sessions, training on the exact experiment types most commonly tested in Paper 3, well before exam day.',
        'inclusions' => [
            ['TBT Notes', 'Every Paper 2 answer is structured using a tested framework. Students know exactly what to write — and what to leave out — to earn full marks.'],
            ['Flashcards', 'Ready-to-use per chapter. Extremely powerful memorisation tool.'],
            ['Lesson Recordings', "Full recording uploaded after every lesson, with worksheets and teacher's inkings — rewatch any session anytime."],
            ['CAQ Model Answers', 'Commonly asked exam questions with full model answers. Know exactly the key words examiners want.'],
            ['Practice Papers', 'TYS-aligned practice papers curated from actual past year prelim papers.'],
            ['Week-Long WhatsApp Help', "Ask questions anytime — our tutors are eager to help. Even if it's midnight before Os."],
            ['Kahoot Review Games', 'End-of-lesson review every lesson. Engaging, competitive, and actually improves retention.'],
            ['Homework Rewards', 'Complete homework and earn Homework Points — rewards given out after every term.'],
        ],
        'quality_ticks' => '<p>All tutors pass rigorous screening — can take 1+ month before teaching their first class</p><p>Weekly tutor coaching sessions — every TBT tutor is mentored by Mr. Keefe each week</p><p>Every class ends with a student feedback form — reviewed and acted on weekly</p>',
        'options_intro' => 'Both formats teach the same TBT content and methods — standardised across all tutors.',
    ];

    $levels = [
        'sec3' => $shared + [
            'page' => 'sec-3-pure-g3-biology-tuition',
            'number' => '3',
            'hero_image' => 'curated_heroes_sec3_original',
            'whatsapp' => 'Hi ThatBioTutor, I would like to book a Sec 3 trial lesson.',
            'phases' => [
                ['Phase 1 — Teaching the Syllabus <mark>(Mar – Aug)</mark>', "<p>Each Sec 3 topic <strong>taught in-depth</strong>, ahead of school's pace</p><p><strong>Lesson recordings and worksheets</strong> available for all past topics missed</p>", ''],
                ['Phase 2 — EOY Intensives <mark>(Sep – Oct)</mark>', '<p><strong>Focused revision</strong> on weak areas — not a repeat of everything</p><p>All Sec 3 topics <strong>recapped</strong> (~2 topics per lesson)</p><p><strong>Real End-of-Year exam</strong> questions to prepare for EOY/EYE</p>', 'Students can join any time of the year — all lesson recordings and worksheets are made available to them for what they missed!'],
                ['', '', ''],
            ],
            'topics' => '<ol><li>Cells</li><li>Movement of substances</li><li>Biomolecules</li><li>Enzymes</li><li>Nutrition in humans</li><li>Transport in humans</li><li>Respiration</li><li>Excretion</li><li>Homeostasis</li><li>Nervous system and The Eye</li></ol>',
            'topics_start' => 0,
            'hall_title' => 'Real Student Results',
            'hall_images' => ['original_106', 'original_076'],
            'hall_button' => 'Book a Trial',
            'stats' => [['98', 'Different Schools'], ['912+', 'Happy Parents & Students'], ['69.21%', 'Started at B4 or Lower'], ['2.3', 'Ave. Grade Improvement'], ['91%', 'Score A1, A2 or B3'], ['1 in 2', 'Score Distinction']],
            'testimonials_line1' => 'What Sec 3',
            'testimonials_line2' => 'TBT students are saying',
            'testimonial_group' => 'sec3-featured',
            'fallback_portraits' => ['Isaac, Sec 3 in 2025' => 'original_134', 'Dayan, Sec 3 in 2025' => 'original_050', 'Jun Jie, Sec 3 in 2025' => 'original_032'],
            'lesson_line1' => 'What a TBT Sec 3 lesson',
            'lesson_line2' => 'actually looks like',
            'lesson_video' => 'https://www.youtube.com/watch?v=e55iPGQL6ng',
            'lesson_title' => 'What a TBT Sec 3 lesson actually looks like',
            'lesson_cards' => [
                ['In-depth understanding — not just memorisation', 'At TBT, concepts are taught in-depth so students understand each topic deeply — how and why biological processes work. This is what makes the difference in application questions.'],
                ['Memory shortcuts built into every topic', 'Mr. Keefe and the TBT team have developed mnemonics and memory hacks for every major topic. Students remember content without brute-force repetition.'],
                ['OEQ answering technique — the FIRED method', 'Every Paper 2 answer is structured using a tested framework. Students know exactly what to write — and what to leave out — to earn full marks.'],
                ['Ex-MOE teacher reviewed content', 'All TBT notes have been reviewed by Ms Ng, an ex-MOE teacher with 11 years of experience. Notes are constantly updated for syllabus accuracy.'],
            ],
            'inclusions_line2' => 'as a <mark>TBT Sec 3 student</mark>',
            'quality_title' => 'Quality control — how TBT ensures every Sec 3 class is excellent:',
            'faq_heading' => 'Questions <mark>Parents and Sec 3 Students</mark> Always Ask',
            'faq_slides' => 12,
        ],
        'sec4' => $shared + [
            'page' => 'sec-4-pure-g3-biology-tuition',
            'number' => '4',
            'hero_image' => 'original_057',
            'whatsapp' => 'Hi ThatBioTutor, I would like to book a Sec 4 trial lesson.',
            'phases' => [
                ['Phase 1 — Teaching the Syllabus <mark>(Dec – May)</mark>', '<p>Each Sec 4 topic <strong>taught in-depth</strong>, ahead of school pace</p><p><strong>Lesson recordings and worksheets</strong> available for all past topics missed</p>', ''],
                ['Phase 2 — Prelim Intensives <mark>(May – Aug)</mark>', '<p><strong>Focused revision</strong> on weak areas — not a repeat of everything</p><p><strong>All topics</strong> of the entire <strong>O Level</strong> Pure / G3 Biology syllabus <strong>recapped</strong> (~2 topics per lesson)</p><p>Real <strong>prelim questions</strong> to prepare for Prelims</p><p><strong>Real practical sessions</strong> on the exact experiment types most commonly tested</p>', ''],
                ['Phase 3 — O Levels Intensives <mark>(Aug – Nov)</mark>', '<p><strong>Focused revision</strong> on weak areas — not a repeat of everything</p><p><strong>All topics</strong> of the entire <strong>O Level</strong> Pure / G3 Biology syllabus <strong>recapped</strong> (~2 topics per lesson)</p><p>Real <strong>O Level</strong> and <strong>prelim questions</strong> to prepare for <strong>O Level</strong>s</p><p><strong>Real practical sessions</strong> on the exact experiment types most commonly tested</p>', 'Students can join any time of the year — <strong>all lesson recordings and worksheets</strong> are made available to them for what they missed.'],
            ],
            'topics' => '<ol><li>Infectious Diseases</li><li>Nutrition in Plants</li><li>Transport in Plants</li><li>Organisms and Their Environment</li><li>Molecular Genetics</li><li>Reproduction in Plants</li><li>Reproduction in Humans</li><li>Inheritance</li></ol>',
            'topics_start' => 11,
            'hall_title' => 'Real Sec 4 results',
            'hall_images' => ['original_121', 'original_028'],
            'hall_button' => 'Book A Trial',
            'stats' => [['98', 'Different Schools'], ['912+', 'Happy Parents & Students'], ['69.21%', 'Started at B4 or Lower'], ['2.3', 'Avg. Grade Improvement'], ['91%', 'Score A1, A2 or B3'], ['1 in 2', 'Score Distinction']],
            'testimonials_line1' => 'What Sec 4',
            'testimonials_line2' => 'TBT students are saying',
            'testimonial_group' => 'sec4-featured',
            'fallback_portraits' => ['Yong Zheng, Sec 4 in 2024' => 'curated_home_testimonial_yong', 'Gwen, Sec 4 in 2024' => 'curated_home_testimonial_gwen', 'Aryan, Sec 4 in 2024' => 'curated_home_testimonial_aryan'],
            'lesson_line1' => 'What a TBT Sec 4',
            'lesson_line2' => 'lesson actually looks like',
            'lesson_video' => 'https://www.youtube.com/watch?v=e8F2G0P4dEA',
            'lesson_title' => 'What a TBT Sec 4 lesson actually looks like',
            'lesson_cards' => [
                ['Concept first', 'Topic is explained with in-depth understanding — not just skimming through content. Students are asked questions throughout to keep them engaged.'],
                ['Memory shortcuts', 'Mnemonics and memory hacks introduced every topic. Students have a much easier time memorising chunks of bio content.'],
                ['OEQ practice and exam skills', 'Past year questions attempted and gone through in class. FIRED method applied to every answer.'],
                ['Kahoot review', 'End-of-lesson review game every lesson. Motivates students to pay attention during class — and students really look forward to it.'],
            ],
            'inclusions_line2' => 'as a <mark>TBT Sec 4 student</mark>',
            'quality_title' => 'Quality control — how TBT ensures every Sec 4 class is excellent:',
            'faq_heading' => 'Questions <mark>Parents and Sec 4 Students</mark> Always Ask',
            'faq_slides' => 0,
        ],
    ];
    return $levels[$level];
}

/** Declare the editable sections of one programme page (the schema for inc/content/sec-3.php and sec-4.php). */
function tbt_programme_sections(string $level): array
{
    $d = tbt_programme_level($level);
    $p = $level;
    $n = $d['number'];

    $phaseFields = [];
    foreach ($d['phases'] as $index => [$title, $ticks, $note]) {
        $i = $index + 1;
        $hide = $title === '' ? ' Leave the title and points empty to hide this phase.' : '';
        $phaseFields[] = tbt_f_inline("{$p}_phase_{$i}_title", "Phase {$i} — title", $title, trim($hide));
        $phaseFields[] = tbt_f_rich("{$p}_phase_{$i}_ticks", "Phase {$i} — ticked points", $ticks, 'One paragraph per tick.' . $hide);
        $phaseFields[] = tbt_f_inline("{$p}_phase_{$i}_note", "Phase {$i} — note at the bottom (optional)", $note);
    }
    $slides = [];
    for ($i = 1; $i <= (int) $d['faq_slides']; $i++) {
        $slides[] = tbt_f_image(sprintf('%s_faq_image_%02d', $p, $i), "FAQ graphic {$i}", sprintf('%s_faq_%02d', $level, $i));
    }

    return [
        tbt_f_section('hero', 'Top banner', [
            tbt_f_image("{$p}_hero_image", 'Banner picture', $d['hero_image']),
            tbt_f_text("{$p}_button_label", 'Button text', 'WhatsApp Us Now'),
            tbt_f_text("{$p}_whatsapp_message", 'Message the WhatsApp buttons pre-fill', $d['whatsapp']),
        ], 'The title, the line above it and the paragraph below it are in the Page content tab.'),

        tbt_f_section('options', 'Class options', array_merge([
            tbt_f_text("{$p}_options_kicker", 'Small line above the heading', 'Class Options'),
            tbt_f_inline("{$p}_options_heading", 'Heading', 'Choose how your child <mark>learns best</mark>'),
            tbt_f_text("{$p}_options_intro", 'Paragraph under the heading', $d['options_intro']),
        ], tbt_programme_option_fields($p, $d), [
            tbt_f_text("{$p}_included_label", 'Strip under the two boxes — label', 'BOTH FORMATS INCLUDE:'),
            tbt_f_area("{$p}_included_items", 'Strip under the two boxes — items', $d['included'], 'One item per line; each gets a tick.'),
        ])),

        tbt_f_section('cover', 'What we cover', array_merge([
            tbt_f_text("{$p}_cover_kicker", 'Small line above the heading', 'What We Cover'),
        ], $phaseFields, [
            tbt_f_rich("{$p}_topics_list", 'Topic list (numbered)', $d['topics'], 'Use the numbered-list button; one topic per line.'),
            tbt_f_text("{$p}_exam_heading", 'Exam techniques — heading', 'Exam Techniques — Built Into Every Lesson'),
            tbt_f_rich("{$p}_exam_list", 'Exam techniques — bullet list', $d['exam_list']),
            tbt_f_inline("{$p}_exam_note", 'Exam techniques — closing line', $d['exam_note']),
        ]), 'The heading and the topic-list title are in the Page content tab.'),

        tbt_f_section('hall', 'Hall of Fame', array_merge([
            tbt_f_text("{$p}_hall_small", 'Small heading', 'TBT Hall of Fame'),
            tbt_f_text("{$p}_hall_title", 'Main heading', $d['hall_title']),
            tbt_f_inline("{$p}_hall_text", 'Paragraph', $d['hall_text']),
            tbt_f_image("{$p}_hall_image_1", 'Result picture 1', $d['hall_images'][0]),
            tbt_f_image("{$p}_hall_image_2", 'Result picture 2', $d['hall_images'][1]),
        ], tbt_f_stats("{$p}_proof", 'Highlight', $d['proofs']), [
            tbt_f_text("{$p}_hall_button_1", 'First button text', 'WhatsApp Us'),
            tbt_f_text("{$p}_hall_button_2", 'Second button text', $d['hall_button']),
        ])),

        tbt_f_section('numbers', 'By the numbers', array_merge([
            tbt_f_text("{$p}_numbers_heading", 'Heading', 'TBT By The Numbers'),
            tbt_f_image("{$p}_numbers_image", 'Background picture', 'original_009'),
        ], tbt_f_stats("{$p}_number", 'Number', $d['stats']))),

        tbt_f_section('testimonials', 'Student testimonials', [
            tbt_f_text("{$p}_testimonials_line1", 'Heading — first line', $d['testimonials_line1']),
            tbt_f_text("{$p}_testimonials_line2", 'Heading — second line (green)', $d['testimonials_line2']),
            tbt_f_text("{$p}_testimonials_button", 'Button text', 'More Testimonials'),
        ], 'The testimonials shown here are edited under Testimonials in the left-hand menu (Display group "' . $d['testimonial_group'] . '").'),

        tbt_f_section('lesson', 'What a lesson looks like', array_merge([
            tbt_f_text("{$p}_lesson_line1", 'Heading — first line', $d['lesson_line1']),
            tbt_f_text("{$p}_lesson_line2", 'Heading — second line (green)', $d['lesson_line2']),
            tbt_f_url("{$p}_lesson_video_url", 'YouTube video link', $d['lesson_video'], 'Paste any YouTube link.'),
            tbt_f_text("{$p}_lesson_video_title", 'Video title (read by screen readers)', $d['lesson_title']),
        ], tbt_f_cards("{$p}_lesson", 'Point', $d['lesson_cards'], 'text', 'inline'), [
            tbt_f_text("{$p}_practical_title", 'Practical sessions — title', $d['practical_title']),
            tbt_f_inline("{$p}_practical_text", 'Practical sessions — text', $d['practical_text']),
            tbt_f_image("{$p}_practical_image_1", 'Practical photo 1', 'curated_shared_practical_01'),
            tbt_f_image("{$p}_practical_image_2", 'Practical photo 2', 'curated_shared_practical_02'),
            tbt_f_image("{$p}_practical_image_3", 'Practical photo 3', 'curated_shared_practical_03'),
        ])),

        tbt_f_section('inclusions', 'What students get', array_merge([
            tbt_f_text("{$p}_inclusions_line1", 'Heading — first line', 'Everything your child gets'),
            tbt_f_inline("{$p}_inclusions_line2", 'Heading — second line', $d['inclusions_line2']),
        ], tbt_f_cards("{$p}_inclusion", 'Item', $d['inclusions'], 'text', 'text'))),

        tbt_f_section('tutors', 'Meet the team', [
            tbt_f_inline("{$p}_tutors_heading", 'Heading', 'Meet the <mark>TBT Team</mark>'),
            tbt_f_text("{$p}_quality_title", 'Quality control — title', $d['quality_title']),
            tbt_f_rich("{$p}_quality_ticks", 'Quality control — ticked points', $d['quality_ticks'], 'One paragraph per tick.'),
            tbt_f_text("{$p}_tutors_button", 'Button text', 'WhatsApp Us!'),
        ], 'The tutors themselves are edited under Tutors in the left-hand menu.'),

        tbt_f_section('faq', 'Questions', array_merge([
            tbt_f_text("{$p}_faq_kicker", 'Small line above the heading', 'FAQ'),
            tbt_f_inline("{$p}_faq_heading", 'Heading', $d['faq_heading']),
        ], $slides), 'The questions and answers are edited under FAQs in the left-hand menu (Page / collection "' . $level . '").'),
    ];
}

/** The two class-option boxes: title, subtitle and the bullet list with its note. */
function tbt_programme_option_fields(string $p, array $d): array
{
    $fields = [];
    foreach ($d['options'] as $index => [$title, $sub, $body]) {
        $i = $index + 1;
        $fields[] = tbt_f_text("{$p}_option_{$i}_title", "Box {$i} — title", $title);
        $fields[] = tbt_f_text("{$p}_option_{$i}_sub", "Box {$i} — line under the title", $sub);
        $fields[] = tbt_f_rich("{$p}_option_{$i}_body", "Box {$i} — bullet list and note", $body, 'A bullet list, then a short paragraph.');
    }
    return $fields;
}

/** Page-specific hero heading accents (the words shown in green). */
function tbt_programme_hero_heading(string $level, string $heading): void
{
    if ($level === 'sec3') {
        $parts = preg_split('/(Start of Sec 3\.|Now!|The Second Best Time)/u', $heading, -1, PREG_SPLIT_DELIM_CAPTURE);
        foreach ($parts ?: [$heading] as $part) {
            if ($part === 'The Second Best Time') {
                echo '<br>' . esc_html($part);
                continue;
            }
            echo in_array($part, ['Start of Sec 3.', 'Now!'], true) ? '<span class="tbt-programme-hero__highlight">' . esc_html($part) . '</span>' : esc_html($part);
        }
        return;
    }
    $parts = preg_split('/(B4|\bA\b|is still possible\.|proof\.)/u', $heading, -1, PREG_SPLIT_DELIM_CAPTURE);
    foreach ($parts ?: [$heading] as $part) {
        echo in_array($part, ['B4', 'A', 'is still possible.', 'proof.'], true) ? '<span class="tbt-programme-hero__highlight">' . esc_html($part) . '</span>' : esc_html($part);
    }
}

/** Accent words in the hero paragraph. */
function tbt_programme_hero_lead(string $level, string $lead): string
{
    $accents = $level === 'sec3'
        ? ['right foundation before Sec 4', 'best shot at A1']
        : ['69.21%', 'B4 or lower...', '91%', 'A1/A2/B3', 'right method', 'syllabus accuracy', 'patient support'];
    return str_replace($accents, array_map(static fn (string $a): string => '<span>' . $a . '</span>', $accents), esc_html($lead)); // phpcs:ignore WordPress.Security.EscapeOutput -- escaped first, only accent spans are added.
}

/** Render one programme page's article. */
function tbt_render_programme_page(string $level): void
{
    $d = tbt_programme_level($level);
    $p = $level;
    $defaults = tbt_reference_content()['pages'][$d['page']]['fields'];
    $f = static fn (string $name): string => (string) tbt_page_field($p . '_' . $name, $defaults[$p . '_' . $name] ?? '');
    $tbt_whatsapp = tbt_whatsapp_url($f('whatsapp_message'));
    $tbt_button = static function (string $label, string $class = '') use ($tbt_whatsapp): void {
        if ($tbt_whatsapp === '' || $label === '') {
            return;
        }
        echo '<a class="' . esc_attr(trim($class . ' tbt-programme-button')) . '" href="' . esc_url($tbt_whatsapp) . '" target="_blank" rel="noopener noreferrer">' . esc_html($label) . '</a>';
    };
    $card = static fn (string $prefix, int $i): array => [$f("{$prefix}_{$i}_title"), $f("{$prefix}_{$i}_text")];
    $iconBoxes = array_map(static fn (array $box): string => sprintf('--sx:%d;--sy:%d;--sw:%d;--sh:%d', ...$box), [[119, 99, 232, 298], [608, 134, 308, 213], [1081, 133, 359, 214], [1627, 142, 209, 196], [116, 737, 251, 250], [632, 736, 260, 260], [1180, 751, 175, 233], [1597, 721, 242, 266]]);
    ?>
<article class="tbt-programme-page tbt-<?php echo esc_attr($level); ?>-page">
    <header class="tbt-programme-hero tbt-programme-hero--sec" data-section="hero">
        <div class="tbt-programme-hero__background"><?php tbt_render_content_image("{$p}_hero_image", '', ['loading' => 'eager']); ?></div>
        <div class="tbt-programme-hero__shade" aria-hidden="true"></div>
        <div class="tbt-programme-hero__content">
            <p class="tbt-programme-hero__eyebrow"><?php echo esc_html($f('eyebrow')); ?></p>
            <h1><?php tbt_programme_hero_heading($level, $f('heading')); ?></h1>
            <p class="tbt-programme-hero__lead"><?php echo tbt_programme_hero_lead($level, $f('hero_lead')); // phpcs:ignore WordPress.Security.EscapeOutput -- escaped inside. ?></p>
            <?php $tbt_button($f('button_label')); ?>
        </div>
    </header>

    <section class="tbt-programme-section tbt-programme-options" data-section="options"><div class="tbt-programme-container"><p class="tbt-programme-kicker"><?php echo esc_html($f('options_kicker')); ?></p><h2><?php echo tbt_heading_html($f('options_heading')); ?></h2><p><?php echo esc_html($f('options_intro')); ?></p><div class="tbt-programme-options__grid">
        <?php foreach ([1, 2] as $i) : ?><article><header class="tbt-programme-option-heading"><h3><?php echo esc_html($f("option_{$i}_title")); ?></h3><b><?php echo esc_html($f("option_{$i}_sub")); ?></b></header><?php echo tbt_rich($f("option_{$i}_body")); ?></article><?php endforeach; ?>
    </div><div class="tbt-programme-options__included"><strong><?php echo esc_html($f('included_label')); ?></strong><?php foreach (preg_split('/\R/', $f('included_items')) ?: [] as $item) : if (trim($item) === '') { continue; } ?><span class="tbt-tick"><?php echo esc_html(trim($item)); ?></span><?php endforeach; ?></div></div></section>

    <section class="tbt-programme-section tbt-programme-cover" data-section="cover"><div class="tbt-programme-container"><p class="tbt-programme-kicker"><?php echo esc_html($f('cover_kicker')); ?></p><h2><?php echo esc_html($f('cover_heading')); ?></h2><div class="tbt-programme-phases">
        <?php foreach ([1, 2, 3] as $i) : $title = $f("phase_{$i}_title"); $ticks = $f("phase_{$i}_ticks"); if ($title === '' && trim($ticks) === '') { continue; } ?><article class="tbt-programme-phase"><h3><?php echo tbt_heading_html($title); ?></h3><?php echo tbt_rich_attrs(tbt_rich($ticks), 'p', 'class="tbt-tick"'); ?><?php if (trim($f("phase_{$i}_note")) !== '') : ?><p class="tbt-programme-join-note"><?php echo tbt_rich_inline($f("phase_{$i}_note")); ?></p><?php endif; ?></article><?php endforeach; ?>
    </div><div class="tbt-programme-topics-card"><h2><?php echo esc_html($f('topics_heading')); ?></h2><?php echo tbt_rich_attrs(tbt_rich($f('topics_list')), 'ol', 'class="tbt-programme-topics"' . ($d['topics_start'] > 0 ? ' start="' . (int) $d['topics_start'] . '"' : '')); ?></div><div class="tbt-programme-exam"><h2><?php echo esc_html($f('exam_heading')); ?></h2><?php echo tbt_rich($f('exam_list')); ?><p><?php echo tbt_rich_inline($f('exam_note')); ?></p></div></div></section>

    <section class="tbt-programme-section tbt-programme-hall" data-section="hall"><div class="tbt-programme-container"><h2><small><?php echo esc_html($f('hall_small')); ?></small><span><?php echo esc_html($f('hall_title')); ?></span></h2><p><?php echo tbt_rich_inline($f('hall_text')); ?></p><div class="tbt-programme-hall__grid"><?php foreach ([1, 2] as $i) { tbt_render_content_image("{$p}_hall_image_{$i}", 'tbt-programme-track', ['loading' => 'lazy', 'data-track-record' => (string) $i]); } ?></div><div class="tbt-programme-proofs"><?php foreach ([1, 2, 3] as $i) : ?><div class="tbt-programme-proof"><strong><?php echo esc_html($f("proof_{$i}_value")); ?></strong><span><?php echo esc_html($f("proof_{$i}_label")); ?></span></div><?php endforeach; ?></div><?php if ($tbt_whatsapp !== '') : ?><div class="tbt-programme-hall__actions"><?php $tbt_button($f('hall_button_1'), 'tbt-programme-hall__action'); $tbt_button($f('hall_button_2'), 'tbt-programme-hall__action'); ?></div><?php endif; ?></div></section>

    <section class="tbt-programme-section tbt-programme-numbers" data-section="stats"><div class="tbt-programme-numbers__background"><?php tbt_render_content_image("{$p}_numbers_image", '', ['loading' => 'lazy']); ?></div><div class="tbt-programme-numbers__shade" aria-hidden="true"></div><div class="tbt-programme-container"><h2><?php echo esc_html($f('numbers_heading')); ?></h2><div class="tbt-programme-stats"><?php foreach ([1, 2, 3, 4, 5, 6] as $i) : if ($f("number_{$i}_value") === '' && $f("number_{$i}_label") === '') { continue; } ?><div><strong><?php echo esc_html($f("number_{$i}_value")); ?></strong><span><?php echo esc_html($f("number_{$i}_label")); ?></span></div><?php endforeach; ?></div></div></section>

    <section class="tbt-programme-section tbt-programme-testimonials" data-section="testimonials"><div class="tbt-programme-container"><h2><?php echo esc_html($f('testimonials_line1')); ?><br><span><?php echo esc_html($f('testimonials_line2')); ?></span></h2><div data-testimonial-cohort="<?php echo esc_attr($level); ?>-featured"><?php tbt_render_testimonials(['posts_per_page' => -1, 'meta_query' => [['key' => 'tbt_display_group', 'value' => $d['testimonial_group']]], 'fallback_portraits' => $d['fallback_portraits']]); ?></div><a class="tbt-programme-text-link tbt-programme-button" href="<?php echo esc_url(home_url('/testimonials/')); ?>"><?php echo esc_html($f('testimonials_button')); ?></a></div></section>

    <section class="tbt-programme-section tbt-programme-lesson" data-section="lesson"><div class="tbt-programme-container"><h2><?php echo esc_html($f('lesson_line1')); ?><br><span><?php echo esc_html($f('lesson_line2')); ?></span></h2><?php $video = tbt_testimonial_embed_url($f('lesson_video_url')); if ($video !== '') : ?><div class="tbt-programme-lesson__video"><iframe width="960" height="540" src="<?php echo esc_url($video); ?>" title="<?php echo esc_attr($f('lesson_video_title')); ?>" loading="lazy" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" allowfullscreen></iframe></div><?php endif; ?><div class="tbt-programme-lesson__grid"><?php foreach ([1, 2, 3, 4] as $i) : [$title, $text] = $card('lesson', $i); if ($title === '' && trim($text) === '') { continue; } ?><article><h3><?php echo esc_html($title); ?></h3><p><?php echo tbt_rich_inline($text); ?></p></article><?php endforeach; ?></div><div class="tbt-practical-feature"><h3><?php echo esc_html($f('practical_title')); ?></h3><p><?php echo tbt_rich_inline($f('practical_text')); ?></p><div class="tbt-practical-grid"><?php foreach ([1, 2, 3] as $i) { tbt_render_content_image("{$p}_practical_image_{$i}", 'tbt-practical-photo', ['loading' => 'lazy']); } ?></div></div></div></section>

    <section class="tbt-programme-section tbt-programme-inclusions-section" data-section="inclusions"><div class="tbt-programme-container"><h2><?php echo esc_html($f('inclusions_line1')); ?><br><?php echo tbt_heading_html($f('inclusions_line2')); ?></h2><div class="tbt-programme-inclusions"><?php foreach ([1, 2, 3, 4, 5, 6, 7, 8] as $i) : [$title, $text] = $card('inclusion', $i); if ($title === '' && $text === '') { continue; } ?><article class="tbt-inclusion-card"><span class="tbt-inclusion-icon" style="<?php echo esc_attr($iconBoxes[$i - 1] ?? ''); ?>" aria-hidden="true"><?php tbt_render_media_image('original_136', '', ['loading' => 'lazy', 'alt' => '']); ?></span><h3><?php echo esc_html($title); ?></h3><p><?php echo esc_html($text); ?></p></article><?php endforeach; ?></div></div></section>

    <section class="tbt-programme-section tbt-programme-tutors" data-section="tutors"><div class="tbt-programme-container"><h2><?php echo tbt_heading_html($f('tutors_heading')); ?></h2><?php ob_start(); tbt_render_tutors(['fallback_portraits' => ['Ms Jamie' => 'curated_shared_tutor_jamie', 'Ms Chelsea' => 'curated_shared_tutor_chelsea', 'Mr Keefe' => 'curated_shared_tutor_keefe', 'Ms Ng' => 'curated_shared_tutor_ng']]); echo preg_replace(['~<h3>([^<]+)</h3><p class="tbt-tutor__role">(Founder|Ex-MOE Teacher &amp; Syllabus Reviewer)</p>~u', '~<p class="tbt-tutor__role">[^<]*</p>~u'], ['<h3>$1 ($2)</h3>', ''], (string) ob_get_clean()); // phpcs:ignore WordPress.Security.EscapeOutput -- output of the escaped tutor renderer. ?><div class="tbt-programme-quality"><strong><?php echo esc_html($f('quality_title')); ?></strong><?php echo tbt_rich_attrs(tbt_rich($f('quality_ticks')), 'p', 'class="tbt-tick"'); ?></div><?php $tbt_button($f('tutors_button')); ?></div></section>

    <section class="tbt-programme-section tbt-programme-faq" data-section="faq"><div class="tbt-programme-container"><p class="tbt-programme-kicker"><?php echo esc_html($f('faq_kicker')); ?></p><h2><?php echo tbt_heading_html($f('faq_heading')); ?></h2><?php tbt_render_faqs($level); ?>
        <?php $slides = (int) $d['faq_slides']; if ($slides > 0) : ?><div class="tbt-faq-slider" data-carousel data-carousel-fade data-carousel-autoplay="4000" aria-label="Frequently asked questions graphics"><div class="tbt-faq-slider__viewport"><?php for ($i = 1; $i <= $slides; $i++) : ?><figure data-carousel-slide<?php echo $i === 1 ? ' class="is-active"' : ''; ?>><?php tbt_render_content_image(sprintf('%s_faq_image_%02d', $p, $i), 'tbt-faq-slider__image', ['loading' => $i === 1 ? 'eager' : 'lazy']); ?></figure><?php endfor; ?></div><?php if ($slides > 1) : ?><button class="tbt-faq-slider__control tbt-faq-slider__control--previous" type="button" data-carousel-prev aria-label="Previous graphic"><svg aria-hidden="true" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><path d="m15 5-7 7 7 7"/></svg></button><button class="tbt-faq-slider__control tbt-faq-slider__control--next" type="button" data-carousel-next aria-label="Next graphic"><svg aria-hidden="true" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><path d="m9 5 7 7-7 7"/></svg></button><div class="tbt-faq-slider__dots"><?php for ($i = 1; $i <= $slides; $i++) : ?><button type="button" data-carousel-dot aria-label="Show graphic <?php echo esc_attr((string) $i); ?>" aria-current="<?php echo $i === 1 ? 'true' : 'false'; ?>"></button><?php endfor; ?></div><p class="tbt-faq-slider__counter" data-carousel-status data-carousel-status-separator=" / " aria-live="polite" aria-atomic="true">1 / <?php echo esc_html((string) $slides); ?></p><?php endif; ?></div><?php endif; ?>
    </div></section>
</article>
    <?php
}
