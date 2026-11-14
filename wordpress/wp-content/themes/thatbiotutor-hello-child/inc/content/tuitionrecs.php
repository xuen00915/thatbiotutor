<?php
/** Tuition Recommendations — everything the client can edit on this page. Layout stays in page-tuitionrecs.php. */
declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}

return [
    'page' => 'tuitionrecs',
    'sections' => [
        tbt_f_section('recommendations', 'Recommendations', [
            tbt_f_rich('tuitionrecs_body', 'Text', <<<'HTML'
<p>By popular request, I have compiled some secondary school + JC tuition recommendations <a href="https://docs.google.com/document/d/1Wwn8kzMaT59326gpnidbuGwRWMB6etmh7-Ff6eJiKLw/edit?tab=t.0" target="_blank" rel="noopener noreferrer"><u>here</u></a>!</p>
<p>1. For all these recommendations, <strong>I advise you to go for trial lesson</strong> for one/more centres to <u>find the best one for you.</u></p>
<p>2. <strong>Tell them that u were also referred by ThatBioTutor</strong> - might be able to <u>get a discount/free trial.</u></p>
HTML, 'To change the Google Doc address, click the word "here", then the link button in the toolbar.'),
        ], 'The page title is edited in the Page content tab.'),
    ],
];
