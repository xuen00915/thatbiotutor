<?php

declare(strict_types=1);

$root = dirname(__DIR__);
$theme = $root . '/wordpress/wp-content/themes/thatbiotutor-hello-child';
$template = $theme . '/front-page.php';
$pages = $theme . '/assets/css/pages-home.css';
$base = $theme . '/assets/css/base.css';

foreach ([$template, $pages, $base] as $file) {
    if (!is_file($file)) { fwrite(STDERR, "FAIL: Missing responsive artifact {$file}.\n"); exit(1); }
}

$templateSource = (string) file_get_contents($template);
$pageCss = (string) file_get_contents($pages);
$baseCss = (string) file_get_contents($base);
$contracts = [
    ['source' => $pageCss, 'needle' => 'scroll-margin-top', 'label' => 'sticky-header clearance'],
    ['source' => $pageCss, 'needle' => 'overflow-wrap:', 'label' => 'long-heading wrapping'],
    ['source' => $pageCss, 'needle' => 'overflow-x:', 'label' => 'horizontal-overflow protection'],
    ['source' => $pageCss, 'needle' => 'object-position: 50% 54%', 'label' => 'first-slide lower focal point'],
    ['source' => $pageCss, 'needle' => 'min-height: clamp(560px, 50.35vw, 724px)', 'label' => 'reference desktop hero height'],
    ['source' => $pageCss, 'needle' => 'background: rgba(0,0,0,.48)', 'label' => 'hero image overlay'],
    ['source' => $pageCss, 'needle' => 'aspect-ratio: 16 / 9', 'label' => 'video aspect ratio'],
    ['source' => $pageCss, 'needle' => 'tbt-schools__pages', 'label' => 'six-page school panel'],
    ['source' => $pageCss, 'needle' => 'tbt-benefits-grid', 'label' => 'student-benefit grid'],
    ['source' => $templateSource, 'needle' => 'data-carousel-prev', 'label' => 'carousel previous control'],
    ['source' => $templateSource, 'needle' => 'data-carousel-next', 'label' => 'carousel next control'],
    ['source' => $templateSource, 'needle' => 'data-carousel-status', 'label' => 'carousel status'],
    ['source' => $baseCss, 'needle' => ':focus-visible', 'label' => 'keyboard focus styling'],
    ['source' => $baseCss, 'needle' => 'prefers-reduced-motion', 'label' => 'reduced-motion styling'],
    ['source' => $pageCss, 'needle' => '@media (min-width: 1000px)', 'label' => 'desktop reference geometry'],
    ['source' => $pageCss, 'needle' => '@media (max-width: 768px)', 'label' => 'tablet behavior'],
    ['source' => $pageCss, 'needle' => '@media (max-width: 390px)', 'label' => 'mobile behavior'],
    ['source' => $pageCss, 'needle' => '@media (max-width: 320px)', 'label' => 'narrow-mobile behavior'],
];

foreach ($contracts as $contract) {
    if (!str_contains($contract['source'], $contract['needle'])) {
        fwrite(STDERR, "FAIL: Missing {$contract['label']} contract.\n");
        exit(1);
    }
}

fwrite(STDOUT, 'PASS: ' . count($contracts) . " Home responsive contracts\n");
