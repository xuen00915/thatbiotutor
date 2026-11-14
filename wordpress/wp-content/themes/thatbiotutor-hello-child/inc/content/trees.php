<?php
/** Our Causes — everything the client can edit on this page. Layout and section order stay in page-trees.php. */
declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}

return [
    'page' => 'trees',
    'sections' => [
        tbt_f_section('planting', '1. Planting Trees', [
            tbt_f_image('trees_team_image', 'Picture 1 (left)', 'curated_causes_team_trees'),
            tbt_f_image('trees_planted_image', 'Picture 2 (right)', 'curated_causes_trees_planted'),
            tbt_f_text('trees_planting_subheading', 'Line above the text', "Learning Biology isn't enough. We need to Protect it."),
            tbt_f_rich('trees_planting_body', 'Text', <<<'HTML'
<p>Up till 2025, ThatBioTutor donated a portion of its profits to <strong>global reforestation</strong> efforts through <a href="https://teamtrees.org/search?q=thatbiotutor" target="_blank" rel="noopener noreferrer">teamtrees.org</a>, <strong>which partners with Arbor Day Foundation.</strong></p>
<p>In total, TBT has planted <strong><u>1982 trees</u></strong>, which you can see on their website <a href="https://teamtrees.org/search?q=thatbiotutor" target="_blank" rel="noopener noreferrer">here</a>.</p>
HTML, 'To change a link, click the linked word and use the link button in the toolbar. Update the number of trees here.'),
        ], 'The section title is edited in the Page content tab.'),

        tbt_f_section('sg-causes', '2. SG Environmental Causes', [
            tbt_f_image('trees_giving_image', 'Picture', 'curated_causes_giving_sg'),
            tbt_f_rich('trees_sg_body', 'Text', <<<'HTML'
<p>From 2026 onwards, ThatBioTutor will be focusing our giving efforts on causes <strong>closer to home.</strong></p>
<p>Each year, <strong>our students will vote</strong> on the environmental cause on <a href="https://www.giving.sg/search?tags=%5B5%5D" target="_blank" rel="noopener noreferrer">Giving.sg</a> that matters most to them, and we’ll make a lump-sum donation to the chosen cause.</p>
HTML),
        ], 'The section title is edited in the Page content tab.'),
    ],
];
