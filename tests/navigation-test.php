<?php

declare(strict_types=1);

if (!defined('ABSPATH')) {
    define('ABSPATH', dirname(__DIR__) . '/.wordpress-test/');
}
if (!defined('TBT_THEME_DIR')) {
    define('TBT_THEME_DIR', dirname(__DIR__) . '/wordpress/wp-content/themes/thatbiotutor-hello-child');
}
if (!defined('TBT_THEME_URI')) {
    define('TBT_THEME_URI', 'https://local.test/wp-content/themes/thatbiotutor-hello-child');
}

$GLOBALS['tbt_nav_fields'] = [
    99 => [
        'home_shared_whatsapp_number' => '+65 8759 7182',
        'home_shared_whatsapp_message' => 'Hi ThatBioTutor',
        'home_shared_phone' => '+65 8759 7182',
    ],
];

function get_option(string $key, mixed $default = false): mixed { return $key === 'page_on_front' ? 99 : $default; }
function get_field(string $name, int $postId = 0): mixed { return $GLOBALS['tbt_nav_fields'][$postId][$name] ?? null; }
function home_url(string $path = '/'): string { return 'https://local.test/' . ltrim($path, '/'); }
function esc_url(string $value): string { return htmlspecialchars($value, ENT_QUOTES, 'UTF-8'); }
function esc_html(string $value): string { return htmlspecialchars($value, ENT_QUOTES, 'UTF-8'); }
function esc_attr(string $value): string { return htmlspecialchars($value, ENT_QUOTES, 'UTF-8'); }

require_once dirname(__DIR__) . '/wordpress/wp-content/themes/thatbiotutor-hello-child/inc/reference-content.php';
require_once dirname(__DIR__) . '/wordpress/wp-content/themes/thatbiotutor-hello-child/inc/theme-helpers.php';

final class NavigationTest
{
    private int $assertions = 0;

    public function run(): void
    {
        foreach (['tbt_navigation_items', 'tbt_render_navigation', 'tbt_whatsapp_url', 'tbt_safe_tel_url', 'tbt_safe_external_url'] as $function) {
            $this->assertTrue(function_exists($function), "{$function} must exist.");
        }
        $items = tbt_navigation_items();
        $this->assertSame(['Home', 'Classes', 'Results & Testimonials', 'Free Bio Notes', 'About Us', 'Our Causes'], array_column($items, 'label'), 'Main navigation labels must match the original.');
        $this->assertSame(['Sec 3', 'Sec 4'], array_column($items[1]['children'], 'label'), 'Classes submenu must contain only Sec 3 and Sec 4.');

        ob_start();
        tbt_render_navigation();
        $html = (string) ob_get_clean();
        $this->assertSame(1, substr_count($html, 'Results &amp; Testimonials'), 'Results & Testimonials must render once.');
        $this->assertSame(1, substr_count($html, 'data-submenu-toggle'), 'Only Classes may own a submenu control.');
        $this->assertSame(2, substr_count($html, 'site-nav__submenu-link'), 'Only Sec 3 and Sec 4 may appear in the Classes submenu.');
        $this->assertTrue(str_contains($html, 'https://local.test/classes/'), 'Navigation routes must derive from home_url.');
        $this->assertTrue(!str_contains($html, 'thatbiotutor.com'), 'Rendered navigation must not hardcode a production hostname.');

        $this->assertSame('https://wa.me/6587597182?text=Hi%20%26%20Biology', tbt_whatsapp_url('Hi & Biology'), 'WhatsApp URL must normalize the number and encode the message.');
        $this->assertSame('tel:+6587597182', tbt_safe_tel_url('+65 8759 7182'), 'Telephone URLs must contain only a safe normalized number.');
        $this->assertSame('', tbt_safe_tel_url('javascript:alert(1)'), 'Unsafe telephone input must be rejected.');
        $this->assertSame('https://maps.example/place', tbt_safe_external_url('https://maps.example/place'), 'HTTPS map URL must remain usable.');
        $this->assertSame('', tbt_safe_external_url('javascript:alert(1)'), 'Unsafe map URL must be rejected.');

        fwrite(STDOUT, "PASS: {$this->assertions} navigation assertions\n");
    }

    private function assertSame(mixed $expected, mixed $actual, string $message): void
    {
        $this->assertions++;
        if ($expected !== $actual) { throw new RuntimeException($message . ' Expected ' . var_export($expected, true) . ', got ' . var_export($actual, true)); }
    }

    private function assertTrue(bool $condition, string $message): void
    {
        $this->assertions++;
        if (!$condition) { throw new RuntimeException($message); }
    }
}

try { (new NavigationTest())->run(); } catch (Throwable $error) { fwrite(STDERR, 'FAIL: ' . $error->getMessage() . "\n"); exit(1); }

