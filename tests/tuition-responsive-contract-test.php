<?php

declare(strict_types=1);

$theme = dirname(__DIR__) . '/wordpress/wp-content/themes/thatbiotutor-hello-child';
$css = (string) @file_get_contents($theme . '/assets/css/pages.css');
$intensive = (string) @file_get_contents($theme . '/page-intensivefix.php');
$classes = (string) @file_get_contents($theme . '/page-classes.php');
$sec3 = (string) @file_get_contents($theme . '/page-sec-3.php');
$sec4 = (string) @file_get_contents($theme . '/page-sec-4.php');

$contracts = [
    [$css, '.tbt-programme-hero', 'full-width programme hero'],
    [$css, 'object-fit: contain', 'text-artwork contain fit'],
    [$css, 'object-fit: cover', 'crop-safe photo fit'],
    [$css, 'overflow-wrap: anywhere', 'long topic wrapping'],
    [$css, '@media (min-width: 1440px)', '1440 desktop behavior'],
    [$css, '@media (max-width: 768px)', '768 tablet behavior'],
    [$css, '@media (max-width: 390px)', '390 mobile behavior'],
    [$css, '@media (max-width: 320px)', '320 narrow-mobile behavior'],
    [$intensive, 'data-carousel-status', 'accessible programme carousel status'],
    [$intensive, 'intensivefix_program_%02d', 'ordered programme source-key pattern'],
    [$intensive, 'intensivefix_student_reviews', 'student review source key'],
    [$classes, 'classes_heading', 'Classes canonical field namespace'],
    [$sec3, 'sec3_heading', 'Sec 3 canonical field namespace'],
    [$sec4, 'sec4_heading', 'Sec 4 canonical field namespace'],
];

foreach ($contracts as [$source, $needle, $label]) {
    if ($source === '' || !str_contains($source, $needle)) { fwrite(STDERR, "FAIL: Missing {$label} contract.\n"); exit(1); }
}
fwrite(STDOUT, 'PASS: ' . count($contracts) . " tuition responsive/media contracts\n");
