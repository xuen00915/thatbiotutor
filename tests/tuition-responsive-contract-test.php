<?php

declare(strict_types=1);

$theme = dirname(__DIR__) . '/wordpress/wp-content/themes/thatbiotutor-hello-child';
$css = (string) @file_get_contents($theme . '/assets/css/pages-programmes.css');
$intensive = (string) @file_get_contents($theme . '/page-intensivefix.php');
$classes = (string) @file_get_contents($theme . '/page-classes.php');
$sec3 = (string) @file_get_contents($theme . '/page-sec-3.php');
$sec4 = (string) @file_get_contents($theme . '/page-sec-4.php');

$contracts = [
    [$css, '.tbt-programme-hero', 'full-width programme hero'],
    [$css, '.tbt-programme-hero__background', 'programme hero background layer'],
    [$css, 'min-height: 575px', 'desktop Classes hero height'],
    [$css, 'min-height: 710px', 'desktop Sec hero height'],
    [$css, '.tbt-programme-options__grid', 'public class-options two-column composition'],
    [$css, '.tbt-programme-phase', 'public phase cards'],
    [$css, '.tbt-programme-stats', 'public six-stat grid'],
    [$css, '.tbt-programme-inclusions', 'public inclusion cards'],
    [$css, 'object-fit: contain', 'text-artwork contain fit'],
    [$css, 'object-fit: cover', 'crop-safe photo fit'],
    [$css, 'overflow-wrap: anywhere', 'long topic wrapping'],
    [$css, '@media (min-width: 1440px)', '1440 desktop behavior'],
    [$css, '@media (max-width: 768px)', '768 tablet behavior'],
    [$css, '@media (max-width: 390px)', '390 mobile behavior'],
    [$css, '@media (max-width: 320px)', '320 narrow-mobile behavior'],
    [$css, '@media (min-width: 981px)', 'desktop public-section geometry'],
    [$css, '.tbt-programme-numbers__background', 'classroom-photo numbers layer'],
    [$css, '.tbt-programme-proof', 'public grade-jump proof blocks'],
    [$css, '.tbt-programme-hall__actions', 'paired hall-of-fame conversion actions'],
    [$css, '.tbt-programme-options__grid { grid-template-columns: 1fr;', 'stacked public class-option panels'],
    [$css, '.tbt-programme-cover { background: #08664f;', 'dark-teal programme coverage section'],
    [$css, '.tbt-programme-lesson { background: #000;', 'black public lesson section'],
    [$css, '.tbt-classes-page .tbt-programme-struggles { background: #000;', 'black Classes struggle section'],
    [$css, '.tbt-programme-tutors .tbt-tutors { display: grid; grid-template-columns: 1fr;', 'vertical public tutor flow'],
    [$intensive, 'data-carousel-status', 'accessible programme carousel status'],
    [$intensive, 'intensivefix_program_%02d', 'ordered programme source-key pattern'],
    [$intensive, 'intensivefix_student_reviews', 'student review source key'],
    [$classes, 'classes_heading', 'Classes canonical field namespace'],
    [$sec3, 'sec3_heading', 'Sec 3 canonical field namespace'],
    [$sec4, 'sec4_heading', 'Sec 4 canonical field namespace'],
    [$classes, 'curated_heroes_classes_original', 'Classes hero media key'],
    [$sec3, 'curated_heroes_sec3_original', 'Sec 3 hero media key'],
    [$sec4, 'curated_heroes_sec4_original', 'Sec 4 hero media key'],
];

foreach ($contracts as [$source, $needle, $label]) {
    if ($source === '' || !str_contains($source, $needle)) { fwrite(STDERR, "FAIL: Missing {$label} contract.\n"); exit(1); }
}
foreach (['padding-block: 271px', 'padding-block: 447px', 'padding-block: 453px'] as $inflatedPadding) {
    if (str_contains($css, $inflatedPadding)) {
        fwrite(STDERR, "FAIL: Programme geometry must not use artificial whole-page padding {$inflatedPadding}.\n");
        exit(1);
    }
}
fwrite(STDOUT, 'PASS: ' . count($contracts) . " tuition responsive/media contracts\n");
