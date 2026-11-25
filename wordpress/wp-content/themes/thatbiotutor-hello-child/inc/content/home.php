<?php
/**
 * Home — everything the client can edit on this page.
 *
 * Several sections (FastTrack, proof, tutors, quality, practical, fit, founder, FAQ, classroom) are shared with the
 * IntensiveFix page, so they are edited here once and appear on both. Layout stays in front-page.php and the
 * inquire-* template parts.
 */
declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}

/** Numbered picture slots: [field prefix, label, fallback prefix, count, zero-pad]. */
$tbt_slots = static function (string $prefix, string $label, string $fallbackPrefix, int $count): array {
    $fields = [];
    for ($i = 1; $i <= $count; $i++) {
        $fields[] = tbt_f_image(sprintf('%s_%02d', $prefix, $i), sprintf('%s %d', $label, $i), sprintf('%s_%02d', $fallbackPrefix, $i));
    }
    return $fields;
};

/** Google reviews: twelve slots, the first ten seeded from the public-review snapshot. */
$tbt_google_snapshot = json_decode((string) file_get_contents(dirname(__DIR__, 2) . '/assets/content/google-reviews.json'), true) ?: [];
$tbt_google_fields = [
    tbt_f_text('home_google_heading', 'Heading', 'Google Reviews'),
    tbt_f_url('home_google_url', 'Link to your Google reviews', (string) ($tbt_google_snapshot['googleUrl'] ?? ''), 'Paste the link to your Google Business reviews. Leave empty to hide the link.'),
    tbt_f_text('home_google_link_label', 'Link text', 'Read our reviews on Google'),
];
for ($tbt_i = 1; $tbt_i <= 12; $tbt_i++) {
    $tbt_review = $tbt_google_snapshot['reviews'][$tbt_i - 1] ?? [];
    $tbt_google_fields[] = tbt_f_text("home_google_review_{$tbt_i}_name", "Review {$tbt_i} — name", (string) ($tbt_review['name'] ?? ''), $tbt_i === 1 ? 'Leave a review\'s name and text empty to hide it. Reviews 11 and 12 are spare slots for new reviews.' : '');
    $tbt_google_fields[] = tbt_f_area("home_google_review_{$tbt_i}_text", "Review {$tbt_i} — review text", (string) ($tbt_review['text'] ?? ''));
    $tbt_google_fields[] = tbt_f_text("home_google_review_{$tbt_i}_date", "Review {$tbt_i} — date (YYYY-MM-DD)", (string) ($tbt_review['date'] ?? ''), $tbt_i === 1 ? 'For example 2026-09-10. Shown as Sep 10, 2026.' : '');
    $tbt_google_fields[] = tbt_f_text("home_google_review_{$tbt_i}_stars", "Review {$tbt_i} — stars (1 to 5)", $tbt_review === [] ? '' : (string) ($tbt_review['rating'] ?? 5));
    $tbt_google_fields[] = tbt_f_image("home_google_review_{$tbt_i}_photo", "Review {$tbt_i} — photo (optional)", '', $tbt_i === 1 ? 'A small round photo. Leave empty to keep the current photo, or to show initials for a new reviewer.' : '');
}
unset($tbt_i, $tbt_review);

return [
    'page' => 'home',
    'sections' => [
        tbt_f_section('hero', 'Top of the page', array_merge([
            tbt_f_text('home_specialist_line', 'Small line above the title', "Singapore's ONLY 'O' Level Pure/G3 Bio Specialist"),
            tbt_f_inline('home_heading', 'Main title', 'The One Missing Key to go from <mark data-tone="red">Fail</mark> to <mark>A1</mark> while cutting study time by <mark>90%</mark>', 'Use Highlight for green words and Red for the red word.'),
            tbt_f_inline('home_hero_result', 'Result line', 'The Average TBT Student Makes a <strong>2.3 Grade Jump</strong> (~C5 to A2)'),
            tbt_f_text('home_hero_refund', 'Refund line', "If a student doesn't absolutely love the $95 trial lesson, they get a full refund."),
            tbt_f_text('home_primary_cta_label', 'Button text (used on every trial button)', 'Book My Trial Lesson'),
            tbt_f_text('home_whatsapp_message', 'Message the trial buttons pre-fill in WhatsApp', 'Hi ThatBioTutor, I would like to book a $95 trial lesson. Please share the available timings.'),
        ], $tbt_slots('home_hero_image', 'Slideshow picture', 'home_hero', 5)), 'The five slideshow pictures change every few seconds. Leave a picture empty to keep the current one.'),

        tbt_f_section('problem', 'Student worries and stories', array_merge([
            tbt_f_text('home_problem_quote_1', 'Worry 1', '“There\'s WAY too much to memorise... so many confusing terms”'),
            tbt_f_text('home_problem_quote_2', 'Worry 2', '“I already wrote down a very detailed answer... how come I didn\'t get the points?”'),
            tbt_f_text('home_problem_quote_3', 'Worry 3', '“School bio skim through the content too fast! I don\'t understand concepts deeply enough...”'),
            tbt_f_text('home_problem_bridge_1', 'Line under the worries', 'Sound familiar? Many TBT students started exactly here.'),
            tbt_f_text('home_problem_bridge_2', 'Line before the stories', 'But by the end of the year, they’re telling me:'),
        ], [
            tbt_f_area('home_story_1_quote', 'Story 1 — quote', '“Unlike school teachers who just merely read off the slides, these tutors had somehow made such a content heavy subject into something engaging.”'),
            tbt_f_text('home_story_1_name', 'Story 1 — name', 'Yong Zheng'),
            tbt_f_text('home_story_1_result', 'Story 1 — result', 'Sec 4 (E8 → A1 in 3 months)'),
            tbt_f_image('home_story_1_image', 'Story 1 — picture', 'curated_home_testimonial_yong'),
            tbt_f_area('home_story_2_quote', 'Story 2 — quote', '“After using TBT\'s notes and memorising all the shortcuts I managed to get the model template answer and memorise it easily.”'),
            tbt_f_text('home_story_2_name', 'Story 2 — name', 'Gwen'),
            tbt_f_text('home_story_2_result', 'Story 2 — result', 'Sec 4 (F9 → A1)'),
            tbt_f_image('home_story_2_image', 'Story 2 — picture', 'curated_home_testimonial_gwen'),
            tbt_f_area('home_story_3_quote', 'Story 3 — quote', '“Very knowledgeable and has his own personal touch to everything he does and genuinely cares for the students.”'),
            tbt_f_text('home_story_3_name', 'Story 3 — name', 'Aryan'),
            tbt_f_text('home_story_3_result', 'Story 3 — result', 'Sec 4 (C6 → A2)'),
            tbt_f_image('home_story_3_image', 'Story 3 — picture', 'curated_home_testimonial_aryan'),
        ])),

        tbt_f_section('track-record', 'Track record', array_merge([
            tbt_f_text('home_trust_heading', 'Heading', "Here's the track record:"),
        ], tbt_f_stats('home_stat', 'Number', [['912+', 'Students taught, across 98 Singapore schools'], ['91%', 'Score A1, A2, or B3'], ['2.3', 'Average grade jump, roughly C5 to A2'], ['1 in 2', 'Score A (Distinction)']]), [
            tbt_f_text('home_stat_2_note', 'Number 2 — small note', 'despite 69% starting at B4 or lower'),
        ], $tbt_slots('home_number_image', 'Photo', 'home_number', 4), [
            tbt_f_text('home_track_bridge', 'Line above the video', "Here's how we make that happen."),
            tbt_f_url('home_track_video_url', 'Video link', 'https://www.youtube.com/watch?v=q_IPfEdODqI', 'Paste any YouTube link.'),
            tbt_f_text('home_track_video_title', 'Video title (read by screen readers)', 'The missing key to Biology improvement'),
        ]), 'The numbers count up on the page; type them as you want them shown (for example 912+ or 91%). This section is shared with the IntensiveFix page.'),

        tbt_f_section('fasttrack', 'FastTrack system', array_merge([
            tbt_f_text('home_fasttrack_eyebrow', 'Small line above the heading', 'O Level G3/Pure Bio'),
            tbt_f_inline('home_fasttrack_heading', 'Heading', "ThatBioTutor's FastTrack System Helps Students Score <mark>A1/A2</mark> Without the Endless Revision Grind"),
            tbt_f_text('home_fasttrack_lead', 'Paragraph under the heading', 'Why should students write their own notes, make their own flashcards, and hunt for exam keywords alone, when it’s already done for them?'),
            tbt_f_text('home_fasttrack_other_label', 'Left column label', 'Other Bio Students'),
            tbt_f_text('home_fasttrack_tbt_label', 'Right column label', 'TBT Students + FastTrack'),
        ], tbt_home_fasttrack_fields(), [
            tbt_f_inline('home_fasttrack_close', 'Closing line', 'This is the FastTrack System: the one missing key. Everything students would normally have to build themselves, <strong>already built for them.</strong>'),
        ]), 'Shared with the IntensiveFix page, which keeps its own heading.'),

        tbt_f_section('proof', 'Student proof', array_merge([
            tbt_f_text('home_testimonials_heading', 'Heading', "Don't just take our word for it."),
            tbt_f_url('home_proof_video_1_url', 'Video 1 — link', 'https://www.youtube.com/watch?v=Vkzlr16AGvo'),
            tbt_f_text('home_proof_video_1_title', 'Video 1 — title (read by screen readers)', 'ThatBioTutor student testimonial'),
            tbt_f_url('home_proof_video_2_url', 'Video 2 — link', 'https://www.youtube.com/watch?v=V4dj5XYEcow'),
            tbt_f_text('home_proof_video_2_title', 'Video 2 — title (read by screen readers)', "Gwen's ThatBioTutor testimonial"),
            tbt_f_inline('home_schools_copy', 'Line above the school crests', 'Trusted by students from <strong>98 schools</strong> across Singapore.'),
        ], $tbt_slots('home_crest', 'School crest', 'curated_shared_school_crest', 12), [
            tbt_f_text('home_results_copy', 'Line above the results picture', 'Some of our biggest grade jumps. There are many more.'),
            tbt_f_image('home_hall_image', 'Results picture', 'curated_shared_hall_of_fame'),
        ]), 'Shared with the IntensiveFix page. The Google reviews under this section are in the Google reviews tab.'),

        tbt_f_section('google-reviews', 'Google reviews', $tbt_google_fields, 'The review cards under Student proof, shared with the IntensiveFix page. Copy new reviews from your Google Business page.'),

        tbt_f_section('tutors', 'Meet your tutors', array_merge([
            tbt_f_text('home_tutors_heading', 'Heading', 'Meet Your Tutors'),
        ], tbt_home_tutor_fields(), [
            tbt_f_text('home_quality_heading', 'Quality control — heading', 'Quality Control: How TBT Ensures Every Class Is Excellent'),
            tbt_f_text('home_quality_1', 'Quality control — point 1', 'All tutors pass rigorous screening. Can take 1+ month before teaching their first class.'),
            tbt_f_text('home_quality_2', 'Quality control — point 2', 'Weekly tutor coaching sessions. Every TBT tutor is mentored by Mr Keefe each week.'),
            tbt_f_text('home_quality_3', 'Quality control — point 3', 'All tutors follow the same standardised content and teaching methods. Every class ends with a student feedback form, reviewed and acted on weekly.'),
            tbt_f_text('home_practical_heading', 'Practical sessions — heading', 'Real Practical Sessions. Real Practice.'),
            tbt_f_text('home_practical_lead', 'Practical sessions — paragraph', 'Paper 3 makes up 20% of the grade, and most students walk in undertrained. TBT students can opt in to hands-on sessions covering the exact experiment types most commonly tested, well before exam day.'),
        ], $tbt_slots('home_practical_image', 'Practical photo', 'curated_shared_practical', 3)), 'Shared with the IntensiveFix page. The tutor lists on About Us and the programme pages are edited under Tutors in the left-hand menu.'),

        tbt_f_section('guarantee', 'Trial lesson guarantee', [
            tbt_f_inline('home_guarantee_heading', 'Heading', "Join a Trial Lesson. If You Didn't Absolutely Love It, <mark>You Can Get a Full Refund.</mark>"),
            tbt_f_text('home_guarantee_text', 'Paragraph', 'A trial lesson is $95. We’re so confident of our lesson quality that if you didn’t absolutely love it, simply ask for a refund and you’ll get your money back, no questions asked.'),
        ], 'The button text is the same as at the top of the page.'),

        tbt_f_section('fit', 'Who it is for', [
            tbt_f_text('home_fit_yes_heading', 'Left box — heading', 'This is for:'),
            tbt_f_rich('home_fit_yes_list', 'Left box — list', '<ul><li>Students taking O Level Pure/G3 Biology in Sec 3 or Sec 4.</li><li>Students who feel very lost in the content, or just need a few concepts strengthened.</li><li>Students who know the content but blank out on OEQs and lose marks anyway.</li><li>Students who genuinely want to improve in Bio, out of their own drive, not because someone else is forcing them.</li><li>Students based in Singapore who can attend physical or online classes.</li></ul>'),
            tbt_f_text('home_fit_no_heading', 'Right box — heading', "This isn't for:"),
            tbt_f_rich('home_fit_no_list', 'Right box — list', "<ul><li>Students taking Combined Science Biology, not Pure/G3.</li><li>Students based outside Singapore.</li><li>Students only joining because their parents are making them, without wanting to improve themselves.</li><li>Students who aren't committed to putting in the work required to ace the subject.</li></ul>"),
        ], 'Shared with the IntensiveFix page.'),

        tbt_f_section('founder', 'About the founder', [
            tbt_f_image('home_founder_image', 'Picture', 'curated_home_founder_standing'),
            tbt_f_text('home_founder_heading', 'Heading', 'About The Founder'),
            tbt_f_rich('home_founder_body', 'Text', '<p>I was a GEP student, went through Raffles Institution, and was offered a scholarship by NUS to study Life Sciences. On paper, that probably sounds like someone who never struggled in school.</p><p>In JC, my grades started slipping and I finally took Econs tuition. That\'s when I understood what a genuinely good tutor could do: help me memorise smarter and answer questions in a way that actually scores.</p><p>That inspired me to build ThatBioTutor, because I wanted other students to have that same experience with Biology. That feeling of trying hard and still not getting anywhere is exactly why I built the <strong>FastTrack System</strong>.</p>'),
        ]),

        tbt_f_section('faq', 'FAQ', [
            tbt_f_text('home_faq_heading', 'Heading', 'FAQ'),
        ], 'The questions and answers are edited under <strong>FAQs</strong> in the left-hand menu (Page / collection "home"). Use the Order number to reorder and Draft to hide one.'),

        tbt_f_section('classroom', 'Life in the classroom', array_merge([
            tbt_f_text('home_experience_heading', 'Heading', 'Life inside a TBT classroom'),
        ], $tbt_slots('home_classroom_image', 'Classroom photo', 'home_classroom', 3))),

        tbt_f_section('closing', 'Closing message', [
            tbt_f_image('home_closing_image', 'Picture', 'curated_home_founder_about'),
            tbt_f_text('home_closing_heading', 'Heading', 'Bio doesn’t have to eat up every other part of a student’s life.'),
            tbt_f_rich('home_closing_body', 'Text', '<p>Students shouldn’t have to give up their CCA. Or their friends. Or their other subjects, just to keep their Biology grades afloat.</p><p>That’s exactly why I built the FastTrack System. The notes, the shortcuts, and the support are already done for them. Studying smart takes a fraction of the time it used to.</p><p>Students still get to have a life outside textbooks. And score the grade they know they’re capable of.</p><p>Ready to see what that actually feels like?</p>'),
        ], 'The button text is the same as at the top of the page.'),
    ],
];
