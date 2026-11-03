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
function get_header(): void { echo '<header data-test-header></header><main>'; }
function get_footer(): void { echo '</main><footer data-test-footer></footer>'; }
function tbt_render_testimonials(array $args = []): void { echo '<div data-testimonial-renderer></div>'; }
function tbt_render_tutors(array $args = []): void { echo '<div data-tutor-renderer></div>'; }
function tbt_render_faqs(string $collection): void { echo '<div data-faq-renderer="' . esc_attr($collection) . '"></div>'; }

require_once dirname(__DIR__) . '/wordpress/wp-content/themes/thatbiotutor-hello-child/inc/reference-content.php';
require_once dirname(__DIR__) . '/wordpress/wp-content/themes/thatbiotutor-hello-child/inc/theme-helpers.php';

final class HomePageTest
{
    private int $assertions = 0;

    public function run(): void
    {
        $template = TBT_THEME_DIR . '/front-page.php';
        $this->assertTrue(is_file($template), 'Home template must exist.');
        ob_start();
        require $template;
        $html = (string) ob_get_clean();

        $this->assertSame(1, substr_count($html, '<h1'), 'Home must render exactly one H1.');
        $markers = [
            'data-section="hero-carousel"',
            'The AVERAGE TBT Student',
            'The 1 MISSING KEY',
            'Our FastTrack System',
            'Why You Can Trust In TBT',
            'TBT Hall of Fame',
            'What Students Are Saying About ThatBioTutor',
            'Schools We Have Taught Students From',
            'The TBT Advantage',
            'Sneak Peek Into The TBT Experience',
            'Save The Planet With Us',
            'Our Tutors',
            'Frequently Asked Questions',
        ];
        $last = -1;
        foreach ($markers as $marker) {
            $position = strpos($html, $marker);
            $this->assertTrue($position !== false, "Home must contain {$marker}.");
            $this->assertTrue($position > $last, "Home section {$marker} must remain in original order.");
            $last = $position;
        }

        $this->assertSame(5, substr_count($html, 'data-carousel-slide'), 'Home hero must contain five ordered slides.');
        foreach (range(1, 5) as $index) {
            $key = sprintf('home_hero_%02d', $index);
            $this->assertSame(1, substr_count($html, 'data-media-key="' . $key . '"'), "Hero media {$key} must render once.");
        }
        $this->assertTrue(str_contains($html, 'object-position:50% 75%'), 'First hero slide must focus lower in the image.');
        $this->assertTrue(str_contains($html, 'data-carousel-prev') && str_contains($html, 'data-carousel-next') && str_contains($html, 'data-carousel-dot'), 'Hero must provide previous, next, and dot controls.');

        foreach (['98', '912+', '2.3', '91%'] as $value) {
            $this->assertTrue(str_contains($html, '>' . $value . '<'), "Trust counter {$value} must render.");
        }
        $this->assertTrue(substr_count($html, 'youtube-nocookie.com/embed/') >= 4, 'Home must preserve the original video-led sections.');
        $this->assertSame(12, substr_count($html, 'data-school-crest'), 'School crest panel must contain twelve crests.');
        $this->assertTrue(str_contains($html, 'data-school-marquee'), 'School crests must use the shared running panel hook.');
        $this->assertTrue(str_contains($html, 'curated_shared_hall_of_fame'), 'Home must use the local hall-of-fame results artwork.');
        $this->assertSame(3, substr_count($html, 'data-practical-image'), 'Home must show three practical-session images.');
        $this->assertSame(3, substr_count($html, 'data-classroom-image'), 'Home must show three supplied classroom images.');
        $this->assertSame(1, substr_count($html, 'data-testimonial-renderer'), 'Home must use the shared testimonial renderer.');
        $this->assertSame(1, substr_count($html, 'data-tutor-renderer'), 'Home must use the shared tutor renderer.');
        $this->assertSame(1, substr_count($html, 'data-faq-renderer="home"'), 'Home must use the shared Home FAQ renderer.');
        $this->assertTrue(substr_count($html, '/contact/') >= 2, 'Home must provide trial and closing CTA links.');
        $this->assertTrue(!preg_match('~https?://static\.wixstatic\.com~', $html), 'Home must not hotlink Wix media.');
        $this->assertTrue(substr_count($html, '.webp') >= 25, 'Home media must resolve to the local WebP library.');

        fwrite(STDOUT, "PASS: {$this->assertions} Home-page assertions\n");
    }

    private function assertSame(mixed $expected, mixed $actual, string $message): void { $this->assertions++; if ($expected !== $actual) { throw new RuntimeException($message . ' Expected ' . var_export($expected, true) . ', got ' . var_export($actual, true)); } }
    private function assertTrue(bool $condition, string $message): void { $this->assertions++; if (!$condition) { throw new RuntimeException($message); } }
}

try { (new HomePageTest())->run(); } catch (Throwable $error) { fwrite(STDERR, 'FAIL: ' . $error->getMessage() . "\n"); exit(1); }

