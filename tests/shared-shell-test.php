<?php

declare(strict_types=1);

if (!defined('ABSPATH')) { define('ABSPATH', dirname(__DIR__) . '/.wordpress-test/'); }
if (!defined('TBT_THEME_DIR')) { define('TBT_THEME_DIR', dirname(__DIR__) . '/wordpress/wp-content/themes/thatbiotutor-hello-child'); }
if (!defined('TBT_THEME_URI')) { define('TBT_THEME_URI', 'https://preview.local/wp-content/themes/thatbiotutor-hello-child'); }

$GLOBALS['tbt_shell_fields'] = [99 => [
    'home_shared_whatsapp_number' => '+65 8759 7182',
    'home_shared_whatsapp_message' => 'Hi ThatBioTutor',
    'home_shared_phone' => '+65 8759 7182',
    'home_shared_address' => "175 Bencoolen St\n#01-45\nSingapore 189649",
    'home_shared_map_url' => 'https://maps.example/thatbiotutor',
    'home_shared_opening_hours' => "Friday – Thursday\n11am – 9:30pm\n(open all 7 days)",
    'home_shared_footer_description' => "Singapore's only O Level Pure/G3 Biology specialist.",
    'home_shared_copyright' => 'ThatBioTutor. All rights reserved.',
]];

function get_option(string $key, mixed $default = false): mixed { return $key === 'page_on_front' ? 99 : $default; }
function get_field(string $name, int $postId = 0): mixed { return $GLOBALS['tbt_shell_fields'][$postId][$name] ?? null; }
function home_url(string $path = '/'): string { return 'https://preview.local/' . ltrim($path, '/'); }
function esc_url(string $value): string { return htmlspecialchars($value, ENT_QUOTES, 'UTF-8'); }
function esc_html(string $value): string { return htmlspecialchars($value, ENT_QUOTES, 'UTF-8'); }
function esc_attr(string $value): string { return htmlspecialchars($value, ENT_QUOTES, 'UTF-8'); }
function language_attributes(): void { echo 'lang="en-SG"'; }
function bloginfo(string $show): void { echo $show === 'charset' ? 'UTF-8' : 'ThatBioTutor'; }
function wp_head(): void { echo '<meta name="test-head">'; }
function wp_footer(): void { echo '<script data-test-footer></script>'; }
function body_class(string $class = ''): void { echo 'class="' . esc_attr(trim('no-js ' . $class)) . '"'; }
function wp_body_open(): void { echo '<!-- body-open -->'; }
function is_front_page(): bool { return false; }
function is_page(mixed $page = null): bool { return false; }

require_once dirname(__DIR__) . '/wordpress/wp-content/themes/thatbiotutor-hello-child/inc/reference-content.php';
require_once dirname(__DIR__) . '/wordpress/wp-content/themes/thatbiotutor-hello-child/inc/theme-helpers.php';

final class SharedShellTest
{
    private int $assertions = 0;

    public function run(): void
    {
        $theme = dirname(__DIR__) . '/wordpress/wp-content/themes/thatbiotutor-hello-child';
        foreach (['header.php', 'footer.php', 'assets/css/base.css', 'assets/css/components.css', 'assets/css/pages.css'] as $file) {
            $this->assertTrue(is_file($theme . '/' . $file), "{$file} must exist.");
        }

        ob_start();
        require $theme . '/header.php';
        echo '<p>Page content</p>';
        require $theme . '/footer.php';
        $html = (string) ob_get_clean();

        $this->assertSame(1, substr_count($html, 'class="skip-link'), 'Shared shell must contain one skip link.');
        $this->assertSame(1, substr_count($html, '<header class="site-header"'), 'Shared shell must contain one header.');
        $this->assertSame(1, substr_count($html, '<footer class="site-footer"'), 'Shared shell must contain one footer.');
        $this->assertSame(1, substr_count($html, 'class="site-footer__inner"'), 'Shared footer must contain one structured information grid.');
        $this->assertTrue(str_contains($html, 'class="site-footer__brand"') && str_contains($html, '>ThatBioTutor</span>'), 'Shared footer must display the approved logo and brand name.');
        foreach (['VISIT', 'WHATSAPP', 'HOURS', '175 Bencoolen St', '#01-45', 'Singapore 189649', '8759 7182', 'Friday – Thursday', '11am – 9:30pm', '(open all 7 days)'] as $footerText) {
            $this->assertTrue(str_contains($html, $footerText), "Shared footer must display {$footerText}.");
        }
        $this->assertTrue(str_contains($html, 'href="https://wa.me/6587597182'), 'Footer WhatsApp number must be an actionable safe link.');
        $this->assertTrue(str_contains($html, 'class="site-footer__copyright"'), 'Footer copyright must be visible rather than screen-reader-only.');
        $this->assertSame(1, substr_count($html, 'data-whatsapp-float'), 'Shared shell must contain one floating WhatsApp control.');
        $this->assertSame(1, substr_count($html, 'data-menu-toggle'), 'Shared shell must contain one mobile-menu toggle.');
        $this->assertSame(1, substr_count($html, 'class="site-header__cta"'), 'Shared header must show one Contact Us call-to-action button on every page.');
        $this->assertTrue(preg_match('/class="site-header__cta" href="https:\/\/wa\.me\/[^"]*"[^>]*>Contact Us</', $html) === 1, 'The header Contact Us button must open WhatsApp.');
        $this->assertTrue(str_contains($html, 'curated_shared_tbt_logo') || str_contains($html, '/files/b3d1102b6adcca4d05893ead7b492b4f.webp'), 'Header must use the approved original logo asset.');
        $this->assertTrue(str_contains($html, 'https://wa.me/6587597182'), 'Floating control must use the safe WhatsApp URL.');
        $this->assertTrue(str_contains($html, 'class="whatsapp-float__icon"'), 'Floating control must use an icon instead of a WA text placeholder.');
        $this->assertTrue(str_contains($html, 'class="whatsapp-float__notification"'), 'Floating control must include the original notification detail.');
        $this->assertTrue(!str_contains($html, '>WA<'), 'Floating control must not expose the old WA text placeholder.');
        $this->assertTrue(!str_contains($html, 'www.thatbiotutor.com'), 'Shell must remain hostname-portable.');

        $base = (string) file_get_contents($theme . '/assets/css/base.css');
        $components = (string) file_get_contents($theme . '/assets/css/components.css');
        $this->assertTrue(str_contains($base, 'body.tbt-site') && str_contains($base, 'Arial, Helvetica, sans-serif'), 'Body typography must override the Hello Elementor default.');
        $this->assertTrue(str_contains($base, '--tbt-header-height: 80px'), 'Shared header height must use the requested 80px shell.');
        $this->assertTrue(str_contains($components, 'width: min(100%, 980px)'), 'Desktop header content must use the measured 980px reference width.');
        $this->assertTrue(str_contains($components, 'width: 65px') && str_contains($components, 'height: 65px'), 'Desktop logo must match the 65px reference geometry.');
        $this->assertTrue(str_contains($components, '.site-footer__inner') && str_contains($components, 'grid-template-columns'), 'Site-wide footer must define the responsive information grid.');
        $this->assertTrue(str_contains($components, 'position: sticky'), 'Header CSS must keep the header sticky.');
        $this->assertTrue(preg_match('/site-nav[^}]*:hover[^}]*#[dD][0-9a-fA-F]{5}|site-nav[^}]*:hover[^}]*red/s', $components) === 1, 'Navigation hover state must be red.');
        $this->assertTrue(preg_match('/\.site-header__inner \{[^}]*grid-template-columns: 136px auto;[^}]*justify-content: center;/', $components) === 1, 'Desktop header logo and navigation must be centered as one group.');
        $this->assertTrue(str_contains($components, '.site-nav__link[aria-current="page"]') && !str_contains($components, '#8de15e') && preg_match('/\.site-nav__link\.is-active \{ color: var\(--tbt-green-text, #2f8a27\)/', $components) === 1, 'Current-page navigation state must use the same green as the page text, not the old lime.');
        $this->assertTrue(str_contains($components, 'min-width: 44px') && str_contains($components, 'min-height: 44px'), 'Interactive controls must be at least 44×44 CSS pixels.');
        $this->assertTrue(str_contains($base, 'prefers-reduced-motion'), 'Base CSS must respect reduced motion.');
        $pages = (string) file_get_contents($theme . '/assets/css/pages.css');
        $this->assertTrue(str_contains($pages, '.tbt-section') && str_contains($pages, '.tbt-button'), 'pages.css must retain only reusable page primitives.');
        $this->assertTrue(!str_contains($pages, '.tbt-home') && !str_contains($pages, '.tbt-programme-page') && !str_contains($pages, '.tbt-institutional-page'), 'Shared primitives must not leak route-family presentation.');

        fwrite(STDOUT, "PASS: {$this->assertions} shared-shell assertions\n");
    }

    private function assertSame(mixed $expected, mixed $actual, string $message): void { $this->assertions++; if ($expected !== $actual) { throw new RuntimeException($message); } }
    private function assertTrue(bool $condition, string $message): void { $this->assertions++; if (!$condition) { throw new RuntimeException($message); } }
}

try { (new SharedShellTest())->run(); } catch (Throwable $error) { fwrite(STDERR, 'FAIL: ' . $error->getMessage() . "\n"); exit(1); }

