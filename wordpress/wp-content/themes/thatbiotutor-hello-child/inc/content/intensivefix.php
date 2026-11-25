<?php
/** IntensiveFix — everything the client can edit on this page. Layout stays in page-intensivefix.php. */
declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}

return [
    'page' => 'intensivefix',
    'sections' => [
        tbt_f_section('hero', 'Top of the page', [
            tbt_f_text('intensivefix_kicker', 'Small line above the title', "Singapore's ONLY 'O' Level Pure/G3 Bio Specialist"),
            tbt_f_inline('intensivefix_heading', 'Main title', 'Fix Your Entire Sec 3 Bio Foundation Before the <mark data-tone="red">Chaotic Sec 4 Year</mark> Begins — In Just <mark>4 DAYS</mark>', 'Use Highlight for green words and Red for red words.'),
            tbt_f_inline('intensivefix_limited', 'Dates line under the title', '<strong>Limited time:</strong> Only during <strong>last two weeks of Nov/first two weeks of Dec.</strong>', 'Change the dates here for each run. Use Bold for the parts in bold. Leave empty to hide.'),
            tbt_f_text('intensivefix_cta_label', 'Button text', 'Fix My Sec 3 Foundation'),
            tbt_f_text('intensivefix_whatsapp_message', 'Message the page buttons pre-fill in WhatsApp', 'Hi ThatBioTutor, I would like to enquire about IntensiveFix. Please share the schedule and next steps.'),
            tbt_f_text('intensivefix_header_label', 'Top-bar button text (this page only)', 'Contact Us'),
            tbt_f_text('intensivefix_header_message', 'Message the top-bar button pre-fills in WhatsApp', 'Hi ThatBioTutor, I would like to find out more about IntensiveFix.'),
        ], 'The programme graphics on the right are in the Programme media tab.'),

        tbt_f_section('problem', 'Student worries', [
            tbt_f_text('intensivefix_quote_1', 'Worry 1', '“There\'s WAY too much to memorise... so many confusing terms”', 'Leave a worry empty to hide it.'),
            tbt_f_text('intensivefix_quote_2', 'Worry 2', '“I already wrote down a very detailed answer... how come I didn\'t get the points?”'),
            tbt_f_text('intensivefix_quote_3', 'Worry 3', '“School bio skim through the content too fast! I don\'t understand concepts deeply enough...”'),
            tbt_f_text('intensivefix_reviews_bridge', 'Line above the student reviews picture', 'Sound familiar? After The IntensiveFix Program, They’re Telling Us:'),
        ], 'The student reviews picture is in the Programme media tab.'),

        tbt_f_section('track-record', 'Track record', [
            tbt_f_text('intensivefix_track_heading', 'Heading', "Here's the track record:"),
            tbt_f_text('intensivefix_stat_4_value', 'Fourth number', '8', 'The first three numbers are shared with the Home page (Home → Track record).'),
            tbt_f_text('intensivefix_stat_4_label', 'Fourth number — label', 'Lessons over 4 days to rebuild your entire Sec 3 foundation'),
            tbt_f_text('intensivefix_track_bridge', 'Line above the video', "Here's how we make that happen."),
            tbt_f_url('intensivefix_video_url', 'YouTube video link', 'https://www.youtube.com/watch?v=bwMcGJcP0ts', 'Paste any YouTube link. Leave empty to hide the video.'),
        ], 'The four classroom photos are shared with the Home page (Home → Track record).'),

        tbt_f_section('fasttrack', 'FastTrack heading', [
            tbt_f_inline('intensivefix_fasttrack_heading', 'Heading', '<strong>All IntensiveFix students will receive</strong> ThatBioTutor\'s FastTrack System, which Helps Students Score <mark>A1/A2</mark> Without the Endless Revision Grind', 'Use Highlight for the green words.'),
        ], 'The five FastTrack steps below the heading are shared with the Home page (Home → FastTrack system).'),

        tbt_f_section('closing', 'Closing message', [
            tbt_f_text('intensivefix_closing_heading', 'Heading', 'Your EOY Bio Grade doesn’t have to define your O Levels.'),
            tbt_f_rich('intensivefix_closing_text', 'Message', <<<'HTML'
<p>It can be demoralising to have scored poorer than expected for the Sec 3 end of year bio exam. But there is still time to turn things around.</p>
<p>We’ve had students come in during this period at F9, going all the way to an A1 in O levels.</p>
<p>What you need is a thorough content overhaul, fixing that weak sec 3 foundation, to supercharge and give you confidence heading into sec 4, to score the O level grade you’re truly capable of.</p>
HTML),
            tbt_f_text('intensivefix_closing_ready', 'Bold line above the button', 'Ready to see what that feels like?'),
            tbt_f_text('intensivefix_closing_button', 'Button text', 'Fix My Sec 3 Foundation Now'),
        ], 'The photo is the founder photo from the Home page.'),
    ],
];
