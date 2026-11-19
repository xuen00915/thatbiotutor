<?php
/** Free Bio Notes — everything the client can edit on this page. Layout stays in page-notes.php. */
declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}

return [
    'page' => 'notes',
    'sections' => [
        tbt_f_section('hero', 'Top section', [
            tbt_f_text('notes_button_label', 'Download button text (used on every button)', 'DOWNLOAD'),
            tbt_f_inline('notes_hero_lead', 'Paragraph under the "For Pure / G3 Biology" line', 'Why struggle with heavy textbooks? Get our <strong>FREE High Quality</strong>, visually stunning, curated notes designed to skip the grind. Accurate to 2027 syllabus. Includes <strong>60+ memory shortcuts.</strong>', 'Use Bold for the words shown in green.'),
            tbt_f_text('notes_proof_line', 'Line under the button', 'Join over 10,000+ students who have downloaded.'),
        ], 'The page title and the "For Pure / G3 Biology" line are in the Page content tab; the three preview pictures are in the Notes slider tab.'),

        tbt_f_section('reviews', 'Student reviews', [
            tbt_f_text('notes_reviews_heading', 'Heading above the reviews (both places)', 'What Students Are Saying...'),
        ], 'The reviews themselves are edited under Testimonials in the left-hand menu (Display group "notes-reviews" for the top list and "notes-reviews-bottom" for the bottom list).'),

        tbt_f_section('form', 'Email form', [
            tbt_f_image('notes_portrait_image', 'Photo above the form', 'original_003', '', 'Mr Keefe from ThatBioTutor'),
            tbt_f_rich('notes_access_note', 'Text under the form', <<<'HTML'
<p>You'll then receive an email with our notes (on gmail, it might be found in your 'promotions' folder).</p>
<p>By submitting, you agree to receive promotional emails from us. You can opt out anytime.</p>
<p>Can't press the submit button or having issues receiving it? Email <a href="mailto:keefe@thatbiotutor.com">keefe@thatbiotutor.com</a>, and our friendly staff will help you out ASAP.</p>
HTML, 'Shown in a light-green box with an icon on each row: the first paragraph continues the "IMPORTANT" line (that line is in the Page content tab); the second and third paragraphs are the next two rows.'),
        ]),

        tbt_f_section('fit', 'Is this you?', [
            tbt_f_rich('notes_fit_list', 'Bullet list', <<<'HTML'
<ul>
<li>Your school notes/textbook is <strong>thick and confusing</strong></li>
<li>You're not sure what is the most important content to <strong>focus on</strong></li>
<li>You wish there were ways to <strong>memorise</strong> content faster and easier</li>
<li>You wish you knew what the <strong>commonly tested questions</strong> are, and how to answer them with <strong>full marks</strong></li>
<li>You really want to <strong>improve</strong> in bio fast</li>
</ul>
HTML),
        ]),

        tbt_f_section('benefits', 'What is in the notes', array_merge([
            tbt_f_text('notes_benefits_heading', 'Section title', "ThatBioTutor's Notes"),
        ], [
            tbt_f_image('notes_benefit_1_image', 'Card 1 — picture', 'curated_notes_notes_benefit_concise'),
            tbt_f_inline('notes_benefit_1_text', 'Card 1 — text', "Concise notes which cuts out the clutter, so you know what's important to <strong>focus on</strong>"),
            tbt_f_image('notes_benefit_2_image', 'Card 2 — picture', 'curated_notes_notes_benefit_memory'),
            tbt_f_inline('notes_benefit_2_text', 'Card 2 — text', 'Includes our very own <strong>memory shortcuts</strong> for you to memorise faster and recall easier in exams.'),
            tbt_f_image('notes_benefit_3_image', 'Card 3 — picture', 'curated_notes_notes_benefit_questions'),
            tbt_f_inline('notes_benefit_3_text', 'Card 3 — text', "I've looked through many papers and assessment books to draw out the <strong>commonly asked questions</strong> for each topic! Now you'll know which MCQs + OEQs are important to study for."),
            tbt_f_image('notes_benefit_4_image', 'Card 4 — picture', 'curated_notes_notes_benefit_reviewed'),
            tbt_f_inline('notes_benefit_4_text', 'Card 4 — text', '<strong>Ex-MOE Teacher Reviewed:</strong> Our syllabus and notes have been reviewed by an Ex-MOE teacher of 11 years.'),
        ])),

        tbt_f_section('details', 'Your questions, answered', [
            tbt_f_inline('notes_details_heading', 'Heading above the three boxes', 'Your Questions, <mark>Answered</mark>'),
            tbt_f_text('notes_details_intro', 'Line under the heading', "Here's why I'm giving away my notes for free, and how you can get them."),
        ], 'The three boxes below are the "Why free?", "What is the catch?" and "How to download" tabs.'),

        tbt_f_section('why-free', 'Why free?', [
            tbt_f_inline('notes_why_heading', 'Heading', 'Why are you giving your notes for <mark>FREE</mark>?'),
            tbt_f_rich('notes_why_list', 'Bullet list', <<<'HTML'
<ul>
<li>I get tons of messages requesting for notes, so this must be a big need in the community. A lot of you are <strong>needlessly struggling</strong> in this subject, and I don't want you to be one of them.</li>
<li>If you are, maybe you're not getting the quality of <strong>teaching and materials</strong> you need. It shouldn't be this way, because bio is an easy subject, and you deserve to score well in it.</li>
<li>I was in your position before, and I wished someone gave me notes like these to make my life <strong>easier</strong>. Now I get to make it a reality for you, and that brings me lots of joy.</li>
</ul>
HTML),
        ]),

        tbt_f_section('catch', 'What is the catch?', [
            tbt_f_inline('notes_catch_heading', 'Heading', 'This sounds <mark>too good to be true</mark>. What\'s the catch?'),
            tbt_f_rich('notes_catch_list', 'Bullet list', <<<'HTML'
<ul>
<li>I do this because I'd like to earn your trust, by giving you results in advance.</li>
<li>I want you to know that this is the <strong>real deal</strong>. It's my hope that those of you who want bio tuition will choose TBT when looking for bio tuition :)</li>
</ul>
HTML),
        ]),

        tbt_f_section('download', 'How to download', [
            tbt_f_inline('notes_download_heading', 'Heading', 'How do I <mark>download</mark> it?'),
            tbt_f_text('notes_download_note', 'Second bullet', "(This is to make sure you're a real person, and if I update the notes in future, I'll send you the updated version automatically!)"),
        ], 'The first bullet is in the Page content tab.'),
    ],
];
