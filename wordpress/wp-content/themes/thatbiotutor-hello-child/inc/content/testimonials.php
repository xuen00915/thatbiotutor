<?php
/** Results & Testimonials — everything the client can edit on this page. Layout stays in page-testimonials.php. */
declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}

return [
    'page' => 'testimonials',
    'sections' => [
        tbt_f_section('hero', 'Top banner', [
            tbt_f_inline('testimonials_hero_lead', 'Line under the title', 'The AVERAGE student at TBT <strong>jumps 2.3 Grades (~C5 to A2)</strong>', 'Use Bold for the part shown in green. Leave empty to hide the line.'),
            tbt_f_text('testimonials_hero_rating', 'Rating next to the five stars', '5.0 (161+ Google Reviews)', 'Leave empty to show only the stars.'),
            tbt_f_image('testimonials_hero_image', 'Banner picture (shown in a rounded frame)', 'curated_home_hero_03'),
        ], 'The page title is edited in the Page content tab.'),

        tbt_f_section('featured', 'Featured video', [
            tbt_f_url('testimonials_video_url', 'YouTube video link', 'https://www.youtube.com/watch?v=Vkzlr16AGvo', 'Paste any YouTube link (youtube.com/watch…, youtu.be/… or an embed link).'),
            tbt_f_text('testimonials_video_title', 'Video title (read by screen readers)', 'ThatBioTutor Exposed: What Students Are Saying'),
            tbt_f_text('testimonials_button_label', 'Button text under the video', 'Transform My Grade Now'),
            tbt_f_text('testimonials_whatsapp_message', 'Message the buttons pre-fill in WhatsApp', 'Hi ThatBioTutor, I would like to enquire about a trial lesson.'),
        ], 'The line above the video is "What Students Are Saying…" in the Page content tab.'),

        tbt_f_section('stories', 'Student Stories wording', [
            tbt_f_text('testimonials_stories_heading', 'Heading shown in every year', 'Student Stories!'),
            tbt_f_text('testimonials_stories_advice', 'Line under the heading', '(Watch to the End for Their Advice to Future O Level Batches!)'),
            tbt_f_text('testimonials_archive_button_label', 'End-of-year button text', 'Transform My Grade Now'),
        ]),

        tbt_f_section('years', 'Years and testimonials', [], '<p><strong>Add a new class year:</strong> Class Years in the left-hand menu → Add Class Year. Enter the year, the green panel heading and the result pictures. The year menu and the page update automatically; the newest year is shown first.</p><p><strong>Add student testimonials:</strong> Testimonials in the left-hand menu → Add Testimonial. Set "Display group / year" to the class year (for example 2026).</p>'),
    ],
];
