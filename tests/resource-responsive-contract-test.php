<?php
declare(strict_types=1);
$theme = dirname(__DIR__) . '/wordpress/wp-content/themes/thatbiotutor-hello-child';
$css = (string) @file_get_contents($theme . '/assets/css/pages-resources.css');
$testimonials = (string) @file_get_contents($theme . '/page-testimonials.php');
$notes = (string) @file_get_contents($theme . '/page-notes.php');
$cell = (string) @file_get_contents($theme . '/page-celldivision.php');
$contracts = [
    [$css, '.tbt-resource-carousel', 'shared resource carousel'],
    [$css, 'aspect-ratio: 1170 / 747', 'public Notes slider ratio'],
    [$css, '.tbt-resource-carousel__control', 'resource carousel controls'],
    [$css, '.tbt-notes-benefits', 'Notes benefit composition'],
    [$css, '.tbt-resource-reviews', 'resource review typography'],
    [$css, 'overflow-wrap: anywhere', 'narrow review wrapping'],
    [$css, '@media (min-width: 1440px)', '1440 layout'],
    [$css, '@media (max-width: 768px)', '768 layout'],
    [$css, '@media (max-width: 390px)', '390 layout'],
    [$css, '@media (max-width: 320px)', '320 layout'],
    [$testimonials, 'data-archive-group', 'group anchors'],
    [$testimonials, 'posts_per_page', 'bounded grouped queries'],
    [$notes, 'data-carousel-status', 'Notes slider status'],
    [$notes, 'data-carousel-autoplay', 'Notes reduced-motion autoplay contract'],
    [$notes, 'notes-reviews', 'Notes review collection'],
    [$cell, 'data-carousel-status', 'Cell Division slider status'],
    [$cell, 'celldivision-reviews', 'Cell Division review collection'],
];
foreach ($contracts as [$source, $needle, $label]) {
    if ($source === '' || !str_contains($source, $needle)) { fwrite(STDERR, "FAIL: Missing {$label} contract.\n"); exit(1); }
}
fwrite(STDOUT, 'PASS: ' . count($contracts) . " resource responsive contracts\n");
