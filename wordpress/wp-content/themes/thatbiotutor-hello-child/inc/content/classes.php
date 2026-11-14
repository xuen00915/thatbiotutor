<?php
/** Classes — everything the client can edit on this page. Layout stays in page-classes.php. */
declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}

return [
    'page' => 'classes',
    'sections' => [
        tbt_f_section('hero', 'Top banner', [
            tbt_f_image('classes_hero_image', 'Banner picture', 'curated_heroes_classes_original'),
            tbt_f_text('classes_button_sec3_label', 'Sec 3 button text', 'I am in Sec 3'),
            tbt_f_text('classes_button_sec4_label', 'Sec 4 button text', 'I am in Sec 4'),
        ], 'The title, small line above it and the paragraph below it are in the Page content tab.'),

        tbt_f_section('definition', 'What is Pure/G3 Biology?', [
            tbt_f_text('classes_definition_kicker', 'Small line above the heading', 'What is Pure/G3 Biology?'),
            tbt_f_image('classes_definition_image', 'Picture', 'curated_classes_pure_vs_combined'),
            tbt_f_text('classes_topics_sec3_title', 'Left list title', 'Sec 3 Topics:'),
            tbt_f_rich('classes_topics_sec3_list', 'Left list (numbered)', '<ol><li>Cells</li><li>Movement of substances</li><li>Biomolecules</li><li>Enzymes</li><li>Nutrition in humans</li><li>Transport in humans</li><li>Respiration</li><li>Excretion</li><li>Homeostasis</li><li>Nervous system and The Eye</li></ol>', 'Use the numbered-list button; one topic per line.'),
            tbt_f_text('classes_topics_sec4_title', 'Right list title', 'Sec 4 Topics:'),
            tbt_f_rich('classes_topics_sec4_list', 'Right list (numbered, continues from 11)', '<ol><li>Infectious diseases</li><li>Nutrition in plants</li><li>Transport in plants</li><li>Organisms and their environment</li><li>Molecular genetics</li><li>Reproduction in plants</li><li>Reproduction in humans</li><li>Inheritance</li></ol>'),
        ], 'The heading and the paragraph are in the Page content tab.'),

        tbt_f_section('struggles', 'Why students struggle', array_merge([
            tbt_f_text('classes_struggle_kicker', 'Small line above the heading', 'Why students struggle'),
        ], tbt_f_cards('classes_struggle', 'Problem', [
            ['1. <mark>Too much content</mark> to memorize', 'Pure Bio has 9 topics with hundreds of key terms, definitions and processes. Students try to brute-force memorise everything — and it does not work under exam conditions.'],
            ['2. Open-ended questions are <mark>unpredictable</mark>', 'Students who understand the content still drop marks on Paper 2 because they do not know how to structure answers. OEQ marking is strict — and school rarely teaches answering technique.'],
            ['3. Topics feel <mark>disconnected</mark>', 'Biology topics link to each other — but most students and even some tutors teach them in isolation. Without the bigger picture, students cannot answer application questions.'],
            ['4. Hands-on practice is <mark>limited</mark>', 'Many students get little hands-on practice in school, making Paper 3 feel like a gamble. Our optional practical sessions cover commonly tested experiments, building the coordination and confidence to protect the full 20%.'],
        ], 'inline', 'inline')), 'The heading is in the Page content tab. Select words in a title and press Highlight to show them in green.'),

        tbt_f_section('solutions', 'How TBT solves them', tbt_f_cards('classes_solution', 'Solution', [
            ['Problem 1 — Too much content', '<strong>TBT FastTrack System</strong> — memory shortcuts and mnemonics built into every lesson. Students learn how to remember, not just what to remember.'],
            ['Problem 2 — OEQ technique', 'TBT teaches the <strong>FIRED method</strong> — a structured answering framework that earns full marks consistently. Practised with real past-year questions in every lesson.'],
            ['Problem 3 — Seeing the big picture', "TBT's lessons are built to show how topics connect. Students develop integrated understanding that application questions test — not just topic-by-topic recall."],
            ['Problem 4 — Limited practical experience', '<strong>TBT Optional Practical Sessions</strong> — optional hands-on sessions covering commonly tested experiments. Students build the coordination, familiarity and confidence needed to handle Paper 3 setups and protect the full 20%.'],
        ], 'text', 'inline'), 'The heading is in the Page content tab.'),

        tbt_f_section('difference', 'The TBT difference', array_merge([
            tbt_f_text('classes_difference_kicker', 'Small line above the heading', 'The TBT difference'),
        ], tbt_f_cards('classes_diff', 'Point', [
            ['In-depth understanding — not just memorisation', 'At TBT, concepts are taught in-depth so students understand each topic deeply — how and why biological processes work. This is what makes the difference in application questions.'],
            ['Memory shortcuts built into every topic', 'Mr. Keefe and the TBT team have developed mnemonics and memory hacks for every major topic. Students remember content without brute-force repetition.'],
            ['OEQ answering technique — the FIRED method', 'Every Paper 2 answer is structured using a tested framework. Students know exactly what to write — and what to leave out — to earn full marks.'],
            ['Ex-MOE teacher reviewed content', 'All TBT notes have been reviewed by Ms Ng, an ex-MOE teacher with 11 years of experience. Notes are constantly updated for syllabus accuracy.'],
        ], 'text', 'inline'), [
            tbt_f_image('classes_difference_image', 'Picture beside the points', 'curated_classes_fasttrack_study_prep'),
            tbt_f_text('classes_practical_title', 'Practical sessions — title', 'Real Practical Sessions. Real Practice.'),
            tbt_f_inline('classes_practical_text', 'Practical sessions — text', 'Paper 3 makes up 20% of the grade, and most students walk in undertrained. TBT students can opt in to additional hands-on sessions, training on the exact experiment types most commonly tested in Paper 3, well before exam day.'),
            tbt_f_image('classes_practical_image_1', 'Practical photo 1', 'curated_shared_practical_01'),
            tbt_f_image('classes_practical_image_2', 'Practical photo 2', 'curated_shared_practical_02'),
            tbt_f_image('classes_practical_image_3', 'Practical photo 3', 'curated_shared_practical_03'),
        ]), 'The heading is in the Page content tab.'),

        tbt_f_section('results', 'By the numbers', array_merge([
            tbt_f_text('classes_results_kicker', 'Small line above the heading', 'By the numbers'),
            tbt_f_text('classes_results_heading', 'Heading', 'Results that speak for themselves'),
        ], tbt_f_stats('classes_stat', 'Number', [['2.3', 'Average grade jump per student'], ['912+', 'Students taught across 98 schools'], ['91%', 'Score A1, A2 or B3']]))),

        tbt_f_section('levels', 'Choose your level', [
            tbt_f_text('classes_levels_kicker', 'Small line above the heading', 'Choose your level'),
            tbt_f_text('classes_levels_intro', 'Paragraph under the heading', 'Each level has its own programme details, schedule, and what to expect — click through to the right one for you.'),
            tbt_f_text('classes_level_1_title', 'Level 1 — title', 'Sec 3 Pure / G3 Biology Classes'),
            tbt_f_text('classes_level_1_text', 'Level 1 — text', 'Build the right foundation from the start — before the pressure of O Levels hits.'),
            tbt_f_text('classes_level_1_link', 'Level 1 — link text', 'View Sec 3 programme →'),
            tbt_f_text('classes_level_2_title', 'Level 2 — title', 'Sec 4 Pure / G3 Biology Classes'),
            tbt_f_text('classes_level_2_text', 'Level 2 — text', 'Score A1 in O Levels — even if you are starting from B4 or lower.'),
            tbt_f_text('classes_level_2_link', 'Level 2 — link text', 'View Sec 4 programme →'),
            tbt_f_text('classes_levels_help', 'Help line', 'Not sure which one applies to you?'),
            tbt_f_text('classes_levels_help_link', 'Help link text', "WhatsApp us — we'll help you figure it out →"),
            tbt_f_text('classes_levels_button', 'WhatsApp button text', 'WhatsApp Us Now!'),
            tbt_f_text('classes_whatsapp_message', 'Message the WhatsApp links pre-fill', 'Hi ThatBioTutor, I would like help choosing the right Pure/G3 Biology class.'),
        ], 'The heading is in the Page content tab. The WhatsApp number is edited on the Home page under Shared site details.'),

        tbt_f_section('faq', 'Questions', [
            tbt_f_text('classes_faq_kicker', 'Small line above the heading', 'Common questions'),
            tbt_f_text('classes_faq_heading', 'Heading', 'Questions About O Level Pure/G3 Biology'),
        ], 'The questions and answers are edited under FAQs in the left-hand menu (Page / collection "classes").'),
    ],
];
