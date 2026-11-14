<?php
/** Cell Division crash course — everything the client can edit on this page. Layout stays in page-celldivision.php. */
declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}

return [
    'page' => 'celldivision',
    'sections' => [
        tbt_f_section('hero', 'Top section', [
            tbt_f_inline('celldivision_title', 'Page title', 'Free <mark>Cell Division</mark> Crash Course'),
            tbt_f_text('celldivision_button_label', 'Button text', 'GET ACCESS'),
            tbt_f_text('celldivision_proof_line', 'Line under the button', '[100+ Students Have Gotten Theirs]'),
        ], 'The notice under the title is in the Page content tab; the five preview pictures are in the Crash-course slider tab.'),

        tbt_f_section('problems', 'Who it is for', [
            tbt_f_inline('celldivision_problems_heading', 'First heading', 'Do you <strong>HATE</strong> cell division because:'),
            tbt_f_rich('celldivision_problems_list', 'First list', '<ul><li>SO much <strong>tedious</strong> content, can\'t <strong>memorise!</strong></li><li>Your school <strong>rushed</strong> through it, leaving you with many doubts</li><li>You feel <strong>lost</strong> as you never got a good foundation</li></ul>'),
            tbt_f_inline('celldivision_content_heading', 'Second heading', 'Maybe it\'s these <strong>CONTENT</strong> issues:'),
            tbt_f_rich('celldivision_content_list', 'Second list', '<ul><li>Identifying <strong>mitosis/meiosis</strong> stages</li><li><strong>Confused</strong> about changes in amount of DNA and number of chromosomes</li><li>Don\'t understand <strong>crossing over, independent assortment</strong></li><li>So many <strong>difficult terms</strong></li></ul>'),
            tbt_f_inline('celldivision_answer', 'Closing paragraph', '<em>If you answered \'yes\' to the above, this crash course is for you. I\'ve cut out clips from my lessons on important cell division concepts - so you can quickly go through them as a "crash course" and fix your doubts ASAP.</em>'),
        ]),

        tbt_f_section('features', 'What you get', array_merge([
            tbt_f_image('celldivision_feature_1_image', 'Feature 1 — picture', 'curated_celldivision_replayable_video'),
            tbt_f_text('celldivision_feature_1_title', 'Feature 1 — title', 'Convenient.'),
            tbt_f_text('celldivision_feature_1_text', 'Feature 1 — text', 'Easy to watch. Infinitely re-playable.'),
            tbt_f_image('celldivision_feature_2_image', 'Feature 2 — picture', 'curated_celldivision_analogy'),
            tbt_f_text('celldivision_feature_2_title', 'Feature 2 — title', 'Fun analogies'),
            tbt_f_text('celldivision_feature_2_text', 'Feature 2 — text', 'For you to easily understand those “super hard” concepts like independent assortment.'),
            tbt_f_image('celldivision_feature_3_image', 'Feature 3 — picture', 'curated_celldivision_memory_shortcuts'),
            tbt_f_text('celldivision_feature_3_title', 'Feature 3 — title', 'Memory shortcuts'),
            tbt_f_text('celldivision_feature_3_text', 'Feature 3 — text', 'Includes my very own memory shortcuts for you to memorise faster and recall easier in exams.'),
            tbt_f_text('celldivision_features_button', 'Button text', 'GIMME MINE'),
        ])),

        tbt_f_section('important', 'Important notes', [
            tbt_f_text('celldivision_important_heading', 'Heading', 'IMPORTANT:'),
            tbt_f_rich('celldivision_important_list', 'List', '<ul><li>This will <strong>expire in 2023</strong> and is only for Sec 4 Pure Bio students taking Os in 2023.</li></ul>'),
            tbt_f_text('celldivision_disclosure', 'Small note', 'This historical 2023 resource page is retained from the original ThatBioTutor website; no current enrolment claim is implied.'),
            tbt_f_inline('celldivision_access_heading', 'Second heading', 'How do I <mark>access</mark> it?'),
            tbt_f_rich('celldivision_access_list', 'Second list', '<ul><li>Enter your favourite <strong>email address</strong> below and a copy will be sent to you<br>(This is to make sure you\'re a real person)</li></ul>'),
        ]),

        tbt_f_section('access', 'Get access', [
            tbt_f_image('celldivision_portrait_image', 'Photo', 'original_056', '', 'Mr Keefe from ThatBioTutor'),
            tbt_f_text('celldivision_promise', 'Heading', 'Simply Enter Your Email Below And A Copy Will Be Sent To You.'),
            tbt_f_text('celldivision_contact_button', 'Button text', 'CONTACT THATBIOTUTOR'),
            tbt_f_rich('celldivision_instructions', 'Instructions', '<p><strong>IMPORTANT:</strong> After successfully submitting the form, you\'ll be automatically redirected to the homepage.</p><p>You\'ll then receive an email (on gmail, it might be found in your \'promotions\' folder).</p><p>Can\'t press the submit button/Having issues receiving it? Email keefe@thatbiotutor.com, and I\'ll help you out ASAP.</p>'),
        ]),

        tbt_f_section('reviews', 'Reviews', [
            tbt_f_text('celldivision_reviews_heading', 'Heading', 'What Students Are Saying About The Crash Course:'),
            tbt_f_text('celldivision_reviews_link', 'Link text', 'Click here to leave your review!'),
        ], 'The reviews are edited under Testimonials in the left-hand menu (Display group "celldivision-reviews").'),
    ],
];
