<?php

declare(strict_types=1);

if (!defined('ABSPATH')) {
    define('ABSPATH', dirname(__DIR__) . '/.wordpress-test/');
}

$GLOBALS['tbt_preview_filters'] = [];
function add_filter(string $hook, mixed $callback, int $priority = 10, int $args = 1): void
{
    $GLOBALS['tbt_preview_filters'][] = $hook;
}
function add_action(string $hook, mixed $callback, int $priority = 10, int $args = 1): void {}
function get_the_ID(): int
{
    return 10;
}
function get_the_title(int $id): string
{
    return 'Ms Chelsea';
}
function tbt_media(string $key): array
{
    return ['url' => 'https://example.test/' . $key . '.webp'];
}
function esc_url(string $url): string
{
    return $url;
}

$theme = dirname(__DIR__) . '/wordpress/wp-content/themes/thatbiotutor-hello-child';
require_once $theme . '/inc/tutors.php';

function tbt_preview_assert(bool $condition, string $message): void
{
    if (!$condition) {
        fwrite(STDERR, "FAIL: {$message}\n");
        exit(1);
    }
}

// The admin preview map must match what the About page actually falls back to.
$about = (string) file_get_contents($theme . '/page-about.php');
$media = tbt_tutor_builtin_media();
foreach ($media['portraits'] as $name => $key) {
    tbt_preview_assert(str_contains($about, "'{$name}' => '{$key}'"), "About page portrait for {$name} must be {$key}.");
}
foreach ($media['certificates'] as $name => $keys) {
    tbt_preview_assert(str_contains($about, "'{$name}' => ['" . implode("', '", $keys) . "']"), "About page certificates for {$name} must match.");
}

foreach (['portrait', 'certificate_1', 'certificate_2', 'certificate_3'] as $suffix) {
    tbt_preview_assert(in_array('acf/prepare_field/key=field_tbt_tutor_' . $suffix, $GLOBALS['tbt_preview_filters'], true), "{$suffix} preview is registered.");
}

$empty = tbt_tutor_field_builtin_preview(['name' => 'field_tbt_tutor_portrait', '_name' => 'tbt_tutor_portrait', 'value' => '', 'instructions' => 'x']);
tbt_preview_assert(str_contains($empty['instructions'], 'curated_about_tutor_chelsea.webp'), 'An empty portrait shows the built-in photo.');
$cert = tbt_tutor_field_builtin_preview(['name' => 'field_tbt_tutor_certificate_2', '_name' => 'tbt_tutor_certificate_2', 'value' => '', 'instructions' => 'x']);
tbt_preview_assert(str_contains($cert['instructions'], 'curated_about_chelsea_credentials.webp'), 'An empty certificate 2 shows the built-in picture.');
$none = tbt_tutor_field_builtin_preview(['name' => 'tbt_tutor_certificate_3', 'value' => '', 'instructions' => 'x']);
tbt_preview_assert($none['instructions'] === 'x', 'A slot without a built-in picture is unchanged.');
$filled = tbt_tutor_field_builtin_preview(['name' => 'tbt_tutor_portrait', 'value' => 42, 'instructions' => 'x']);
tbt_preview_assert($filled['instructions'] === 'x', 'An uploaded portrait needs no preview.');

$fields = (string) file_get_contents($theme . '/inc/content-types.php');
tbt_preview_assert(!str_contains($fields, 'tbt_tutor_long_bio') && !str_contains($fields, 'tbt_tutor_sections'), 'Unused Long biography and Active pages fields are gone.');

fwrite(STDOUT, "PASS: tutor admin preview assertions\n");
