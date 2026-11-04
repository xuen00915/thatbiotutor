<?php
declare(strict_types=1);
$theme = dirname(__DIR__) . '/wordpress/wp-content/themes/thatbiotutor-hello-child';
$css = (string) @file_get_contents($theme . '/assets/css/pages-resources.css');
$testimonials = (string) @file_get_contents($theme . '/page-testimonials.php');
$notes = (string) @file_get_contents($theme . '/page-notes.php');
$cell = (string) @file_get_contents($theme . '/page-celldivision.php');
$assertRule = static function (string $selector, array $declarations) use ($css): void {
    $pattern = '/'.preg_quote($selector, '/').'\s*\{([^}]*)\}/s';
    if (preg_match($pattern, $css, $matches) !== 1) {
        fwrite(STDERR, "FAIL: Missing {$selector} geometry rule.\n");
        exit(1);
    }
    $body = preg_replace('/\s+/', ' ', $matches[1]);
    foreach ($declarations as $declaration) {
        if (!str_contains((string) $body, $declaration)) {
            fwrite(STDERR, "FAIL: {$selector} must include {$declaration}.\n");
            exit(1);
        }
    }
};
$contracts = [
    [$css, '.tbt-resource-carousel', 'shared resource carousel'],
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
$assertRule('.tbt-resource-narrow', ['width: min(980px, calc(100% - 48px))']);
$assertRule('.tbt-resource-carousel--notes', ['max-width: 500px']);
$assertRule('.tbt-resource-carousel--notes .tbt-resource-carousel__viewport', ['aspect-ratio: 500 / 640']);
$assertRule('.tbt-notes-access-intro', ['min-height: 1155px']);
$assertRule('.tbt-resource-reviews--lead .tbt-testimonials', ['min-height: 2600px']);
$assertRule('.tbt-notes-benefits', ['min-height: 2102px']);
$assertRule('.tbt-notes-details', ['min-height: 798px']);
$assertRule('.tbt-resource-reviews[data-section="reviews-bottom"] .tbt-testimonial', ['padding: 34px 0 35px']);
$assertRule('.tbt-cell-features', ['padding: 58px 0 281px']);
$assertRule('.tbt-cell-important', ['min-height: 353px']);
$assertRule('.tbt-cell-access', ['min-height: 927px']);
fwrite(STDOUT, 'PASS: ' . (count($contracts) + 11) . " resource responsive contracts\n");
