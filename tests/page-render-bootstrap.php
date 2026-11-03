<?php

declare(strict_types=1);

if (!defined('ABSPATH')) { define('ABSPATH', dirname(__DIR__) . '/.wordpress-test/'); }
if (!defined('TBT_THEME_DIR')) { define('TBT_THEME_DIR', dirname(__DIR__) . '/wordpress/wp-content/themes/thatbiotutor-hello-child'); }
if (!defined('TBT_THEME_URI')) { define('TBT_THEME_URI', 'https://preview.local/wp-content/themes/thatbiotutor-hello-child'); }

function get_field(string $name, int $postId = 0): mixed { return null; }
function get_the_ID(): int { return 99; }
function get_option(string $key, mixed $default = false): mixed { return $key === 'page_on_front' ? 99 : $default; }
function home_url(string $path = '/'): string { return 'https://preview.local/' . ltrim($path, '/'); }
function esc_url(string $value): string { return htmlspecialchars($value, ENT_QUOTES, 'UTF-8'); }
function esc_html(string $value): string { return htmlspecialchars($value, ENT_QUOTES, 'UTF-8'); }
function esc_attr(string $value): string { return htmlspecialchars($value, ENT_QUOTES, 'UTF-8'); }
function wp_kses_post(string $value): string { return strip_tags($value, '<p><br><strong><em><b><i><a><ul><ol><li>'); }
function get_header(): void { echo '<header data-test-header></header><main>'; }
function get_footer(): void { echo '</main><footer data-test-footer></footer>'; }
function tbt_render_testimonials(array $args = []): void { echo '<div data-testimonial-renderer></div>'; }
function tbt_render_tutors(array $args = []): void { echo '<div data-tutor-renderer></div>'; }
function tbt_render_faqs(string $collection): void { echo '<div data-faq-renderer="' . esc_attr($collection) . '"></div>'; }

require_once dirname(__DIR__) . '/wordpress/wp-content/themes/thatbiotutor-hello-child/inc/reference-content.php';
require_once dirname(__DIR__) . '/wordpress/wp-content/themes/thatbiotutor-hello-child/inc/theme-helpers.php';

function tbt_test_render_template(string $filename): string
{
    ob_start();
    require TBT_THEME_DIR . '/' . $filename;
    return (string) ob_get_clean();
}

