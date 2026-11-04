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
function tbt_render_results(array $args = []): void { echo '<div data-result-renderer></div>'; }

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
        preg_match_all('/data-section="([^"]+)"/', $html, $sectionMatches);
        $this->assertSame([
            'hero', 'missing-key', 'fasttrack', 'trust', 'hall-of-fame', 'testimonials',
            'schools', 'advantage', 'benefits', 'causes', 'tutors', 'faq', 'closing',
        ], $sectionMatches[1], 'Home sections must follow the exact public-site order.');

        preg_match('/<section[^>]+data-section="hero".*?<\/section>/s', $html, $heroMatch);
        $hero = $heroMatch[0] ?? '';
        $this->assertTrue(str_contains($hero, 'tbt-home-hero__overlay'), 'Grade-jump copy must sit inside the image-overlay hero.');
        $heroText = html_entity_decode(strip_tags($hero));
        $this->assertTrue(str_contains($heroText, 'The AVERAGE TBT Student'), 'Hero must contain the public grade-jump headline.');
        $this->assertTrue(str_contains($heroText, "Singapore's ONLY 'O' Level Pure/G3 Bio Specialist"), 'Hero must contain the public specialist line.');
        $this->assertTrue(str_contains($hero, 'class="tbt-home-hero__specialist-highlight"'), 'Hero specialist line must retain the public green emphasis.');
        $this->assertTrue(str_contains($hero, 'curated_home_reference_hero'), 'First hero slide must use the current public group photograph.');

        preg_match('/<section[^>]+data-section="missing-key".*?<h2>(.*?)<\/h2>/s', $html, $missingKeyMatch);
        $missingKeyHeading = $missingKeyMatch[1] ?? '';
        $this->assertTrue(str_contains($missingKeyHeading, 'class="tbt-emphasis-green"'), 'Missing-key heading must preserve the public green emphasis.');
        $this->assertTrue(str_contains($missingKeyHeading, 'class="tbt-emphasis-red"'), 'Missing-key heading must preserve the public red emphasis.');

        $this->assertSame(5, substr_count($html, 'data-carousel-slide'), 'Home hero must contain five ordered slides.');
        foreach (range(1, 5) as $index) {
            $key = $index === 1 ? 'curated_home_reference_hero' : sprintf('home_hero_%02d', $index);
            $this->assertSame(1, substr_count($html, 'data-media-key="' . $key . '"'), "Hero media {$key} must render once.");
        }
        $this->assertTrue(str_contains($html, 'object-position:50% 54%'), 'First hero slide must preserve the approved lower focal point.');
        $this->assertTrue(str_contains($html, 'data-carousel-prev') && str_contains($html, 'data-carousel-next') && str_contains($html, 'data-carousel-dot'), 'Hero must provide previous, next, and dot controls.');

        foreach (['98', '912+', '2.3', '91%', '69.21%', '1 in 2'] as $value) {
            $this->assertTrue(str_contains($html, '>' . $value . '<'), "Trust counter {$value} must render.");
        }
        $this->assertTrue(substr_count($html, 'youtube-nocookie.com/embed/') >= 4, 'Home must preserve the original video-led sections.');
        $this->assertSame(6, substr_count($html, 'data-school-page'), 'School panel must reproduce all six public school pages.');
        foreach (range(1, 6) as $index) {
            $this->assertTrue(str_contains($html, sprintf('curated_home_schools_%02d', $index)), "School page {$index} must use its approved public artwork.");
        }
        $this->assertSame(1, substr_count($html, 'curated_home_hall_of_fame'), 'Home must reproduce the single public Hall of Fame artwork.');
        $this->assertSame(3, substr_count($html, 'data-home-testimonial'), 'Home must reproduce the three public featured testimonials.');
        $this->assertTrue(
            str_contains($html, 'He got me from a C6 (my Sec 3 EOY grade) to A2 in O Level 🙏'),
            'Home must retain the complete Aryan testimonial rather than an abbreviated rewrite.'
        );
        foreach (range(1, 4) as $index) {
            $this->assertSame(1, substr_count($html, sprintf('curated_home_benefit_%02d', $index)), "Student benefit artwork {$index} must render once.");
        }
        $this->assertTrue(str_contains($html, 'data-experience-heading'), 'The public Sneak Peek heading must remain inside Student Benefits.');
        $this->assertTrue(str_contains($html, 'Each year, our students will vote'), 'Home environmental-giving copy must reproduce the current public wording.');
        $this->assertSame(1, substr_count($html, 'curated_home_cause_graphic'), 'Home must use the public environmental-giving artwork.');
        $this->assertSame(1, substr_count($html, 'data-tutor-renderer'), 'Home must use the shared tutor renderer.');
        $this->assertSame(1, substr_count($html, 'data-faq-renderer="home"'), 'Home must use the shared Home FAQ renderer.');
        $homeFaqs = array_filter($tbt_reference['records']['faqs'] ?? [], static fn (array $faq): bool => ($faq['fields']['tbt_faq_collection'] ?? '') === 'home');
        $this->assertSame(13, count($homeFaqs), 'Home must retain all thirteen public FAQs.');
        $homeFaqCopy = implode("\n", array_column($homeFaqs, 'content'));
        foreach ([
            "We don't believe in overloading students with homework when they already have enough from school",
            'We even take the time to voice message them explanations if the question is more complex.',
            'Because we offer several class options tailored to different needs, fees may vary.',
        ] as $publicFaqWording) {
            $this->assertTrue(str_contains($homeFaqCopy, $publicFaqWording), 'Home FAQs must preserve the complete public wording.');
        }
        $this->assertSame(1, substr_count($html, 'curated_home_faq_graphic'), 'Home closing must reproduce the public FAQ artwork.');
        $this->assertTrue(str_contains($html, 'https://wa.me/'), 'Home must provide WhatsApp trial CTA links.');
        $this->assertTrue(!preg_match('~https?://static\.wixstatic\.com~', $html), 'Home must not hotlink Wix media.');
        $this->assertTrue(substr_count($html, '.webp') >= 22, 'Home media must resolve to the local WebP library.');

        fwrite(STDOUT, "PASS: {$this->assertions} Home-page assertions\n");
    }

    private function assertSame(mixed $expected, mixed $actual, string $message): void { $this->assertions++; if ($expected !== $actual) { throw new RuntimeException($message . ' Expected ' . var_export($expected, true) . ', got ' . var_export($actual, true)); } }
    private function assertTrue(bool $condition, string $message): void { $this->assertions++; if (!$condition) { throw new RuntimeException($message); } }
}

try { (new HomePageTest())->run(); } catch (Throwable $error) { fwrite(STDERR, 'FAIL: ' . $error->getMessage() . "\n"); exit(1); }

