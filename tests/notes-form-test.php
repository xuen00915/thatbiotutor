<?php
declare(strict_types=1);
define('ABSPATH', __DIR__);
function esc_url(string $value): string { return htmlspecialchars($value, ENT_QUOTES, 'UTF-8'); }
function esc_attr(string $value): string { return htmlspecialchars($value, ENT_QUOTES, 'UTF-8'); }
function esc_html(string $value): string { return htmlspecialchars($value, ENT_QUOTES, 'UTF-8'); }
function home_url(string $path): string { return 'https://local.test/'; }
// The signup link is the client-editable notes_form_url field (its default is checked in client-editable-sections-test.php).
function tbt_page_field(string $name, mixed $fallback = ''): mixed { return $name === 'notes_form_url' ? 'https://12-dec-2025-updated-signup-form-02264.getresponsesite.com/' : $fallback; }
require __DIR__ . '/../wordpress/wp-content/themes/thatbiotutor-hello-child/inc/forms.php';
ob_start(); tbt_render_notes_form(); $html = (string) ob_get_clean();
$checks = [
    str_contains($html, 'https://12-dec-2025-updated-signup-form-02264.getresponsesite.com/'),
    str_contains($html, 'data-tbt-form="getresponse"'),
    str_contains($html, 'title="Free Bio Notes signup form"'),
    str_contains($html, '<iframe'),
    !str_contains($html, 'Open signup form in a new tab'),
    !str_contains($html, '<form'),
    !str_contains($html, 'mailto:'),
    !str_contains($html, 'data-tbt-redirect'),
];
foreach ($checks as $i => $ok) {
    if (!$ok) { fwrite(STDERR, 'FAIL: GetResponse form contract ' . $i . "\n"); exit(1); }
}
fwrite(STDOUT, 'PASS: ' . count($checks) . " GetResponse form assertions\n");
