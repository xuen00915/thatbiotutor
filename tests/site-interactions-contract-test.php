<?php

declare(strict_types=1);

$root = dirname(__DIR__);
$script = $root . '/wordpress/wp-content/themes/thatbiotutor-hello-child/assets/js/site.js';
$runtime = __DIR__ . '/site-interactions-runtime.js';

if (!is_file($script)) {
    fwrite(STDERR, "FAIL: Site interaction script must exist.\n");
    exit(1);
}

$command = escapeshellarg('node') . ' ' . escapeshellarg($runtime) . ' ' . escapeshellarg($script) . ' 2>&1';
$output = [];
$exitCode = 0;
exec($command, $output, $exitCode);
if ($exitCode !== 0) {
    fwrite(STDERR, "FAIL: " . implode("\n", $output) . "\n");
    exit(1);
}

$source = (string) file_get_contents($script);
foreach (['data-menu-toggle', 'data-submenu-toggle', 'data-carousel-prev', 'data-carousel-next', 'data-carousel-dot', 'data-count-up', 'IntersectionObserver', 'data-certificate-lightbox-trigger', 'showModal', 'tbt-lightbox-open', 'data-faq-button', 'tbt-reveal', 'is-visible', 'touchstart', 'touchend', 'mouseenter', 'mouseleave', 'focusin', 'focusout'] as $hook) {
    if (!str_contains($source, $hook)) {
        fwrite(STDERR, "FAIL: Missing progressive interaction path {$hook}.\n");
        exit(1);
    }
}

fwrite(STDOUT, implode("\n", $output) . "\nPASS: interaction contract\n");
