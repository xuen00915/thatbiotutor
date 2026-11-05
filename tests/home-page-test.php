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
            'hero', 'problem', 'track-record', 'fasttrack', 'proof', 'tutors', 'quality',
            'practical', 'guarantee', 'fit', 'founder', 'faq', 'classroom', 'closing',
        ], $sectionMatches[1], 'Home sections must follow the Inquire landing-page order.');

        $this->assertTrue(str_contains($html, 'The One Missing Key to go from'), 'Home must use the Inquire hero headline.');
        $this->assertTrue(str_contains($html, "If a student doesn't absolutely love the $95 trial lesson"), 'Hero must state the trial refund promise.');
        $this->assertTrue(str_contains($html, '>Book My Trial Lesson<'), 'Hero must use the approved primary CTA label.');

        preg_match('/<section[^>]+data-section="hero".*?<\/section>/s', $html, $heroMatch);
        $hero = $heroMatch[0] ?? '';
        $this->assertSame(5, substr_count($hero, 'data-carousel-slide'), 'Hero must contain five Drive images in order.');
        foreach (range(1, 5) as $index) {
            $key = sprintf('home_hero_%02d', $index);
            $this->assertSame(1, substr_count($hero, 'data-media-key="' . $key . '"'), "Hero media {$key} must render once.");
        }
        $this->assertTrue(str_contains($hero, 'data-carousel-prev') && str_contains($hero, 'data-carousel-next') && str_contains($hero, 'data-carousel-dot'), 'Hero must provide accessible carousel controls.');

        foreach ([
            '912+' => 'Students taught, across 98 Singapore schools',
            '91%' => 'Score A1, A2, or B3',
            '2.3' => 'Average grade jump, roughly C5 to A2',
            '1 in 2' => 'Score A (Distinction)',
        ] as $value => $label) {
            $this->assertTrue(str_contains($html, 'data-count-display="' . $value . '"'), "Trust figure {$value} must use the running-number hook and preserve its final display.");
            $this->assertTrue(str_contains($html, $label), "Trust figure {$value} must retain its label.");
        }
        preg_match('/<section[^>]+data-section="track-record".*?<\/section>/s', $html, $trackMatch);
        $track = $trackMatch[0] ?? '';
        foreach (range(1, 4) as $index) {
            $this->assertSame(1, substr_count($track, sprintf('data-media-key="home_number_%02d"', $index)), "Number image {$index} must render in Drive order.");
        }

        foreach (['Content &amp; Understanding', 'Memory', 'Answering Skills (OEQ)', 'Practical Sessions (20% of Grade)', 'Support'] as $heading) {
            $this->assertTrue(str_contains($html, $heading), "FastTrack comparison must include {$heading}.");
        }
        $this->assertTrue(substr_count($html, 'youtube-nocookie.com/embed/') >= 3, 'Home must preserve the reference video-led proof sections.');
        foreach (['Mr Keefe', 'Ms Chelsea', 'Ms Jamie', 'Ms Ng'] as $tutor) {
            $this->assertTrue(str_contains($html, $tutor), "Tutor row must include {$tutor}.");
        }
        foreach (range(1, 3) as $index) {
            $this->assertSame(1, substr_count($html, sprintf('curated_shared_practical_%02d', $index)), "Practical image {$index} must render once.");
        }

        preg_match('/<section[^>]+data-section="guarantee".*?<\/section>/s', $html, $guaranteeMatch);
        $guarantee = $guaranteeMatch[0] ?? '';
        $this->assertTrue(str_contains($guarantee, 'data-guarantee-shield'), 'Guarantee section must include the themed shield mark.');
        $this->assertTrue(str_contains($guarantee, 'tbt-home-button') && !str_contains($guarantee, 'tbt-home-button--light'), 'Guarantee CTA must use the green button with white text.');
        $this->assertTrue(str_contains($guarantee, 'tbt-home-guarantee__shield-base') && str_contains($guarantee, 'tbt-home-guarantee__shield-check'), 'Guarantee must use the solid shield icon with a white check.');
        $homeCss = (string) file_get_contents(TBT_THEME_DIR . '/assets/css/pages-home.css');
        preg_match('/<section[^>]+data-section="closing".*?<\/section>/s', $html, $closingMatch);
        $closing = $closingMatch[0] ?? '';
        foreach (['Students shouldn’t have to give up their CCA. Or their friends. Or their other subjects, just to keep their Biology grades afloat.', 'That’s exactly why I built the FastTrack System. The notes, the shortcuts, and the support are already done for them. Studying smart takes a fraction of the time it used to.', 'Students still get to have a life outside textbooks. And score the grade they know they’re capable of.', 'Ready to see what that actually feels like?'] as $closingCopy) {
            $this->assertTrue(str_contains($closing, $closingCopy), 'Closing copy must match the supplied text.');
        }
        $this->assertTrue(str_contains($closing, 'tbt-home-button') && !str_contains($closing, 'tbt-home-button--light'), 'Closing CTA must use the green button with white text.');
        $this->assertTrue(str_contains($homeCss, '.tbt-home-guarantee__shield { display: grid; width: 88px; height: 88px;'), 'Guarantee shield must stay small.');
        $this->assertTrue(str_contains($guarantee, 'A trial lesson is $95. We’re so confident of our lesson quality that if you didn’t absolutely love it, simply ask for a refund and you’ll get your money back, no questions asked.'), 'Guarantee section must reproduce the Drive brief exactly.');
        $this->assertTrue(str_contains($guarantee, 'https://wa.me/'), 'Guarantee CTA must open WhatsApp.');
        $this->assertTrue(!str_contains($guarantee, '<form') && !str_contains($guarantee, '<input'), 'Guarantee must replace the old enquiry form.');

        $this->assertSame(9, substr_count($html, 'data-home-inquire-faq'), 'FAQ must reproduce the nine Inquire-page questions.');
        $faqPosition = strpos($html, 'data-section="faq"');
        $classroomPosition = strpos($html, 'data-section="classroom"');
        $this->assertTrue(is_int($faqPosition) && is_int($classroomPosition) && $classroomPosition > $faqPosition, 'Classroom gallery must sit after FAQ.');
        preg_match('/<section[^>]+data-section="classroom".*?<\/section>/s', $html, $classroomMatch);
        $classroom = $classroomMatch[0] ?? '';
        foreach (range(1, 3) as $index) {
            $this->assertSame(1, substr_count($classroom, sprintf('data-media-key="home_classroom_%02d"', $index)), "Classroom image {$index} must render in Drive order.");
        }
        $this->assertTrue(str_contains($html, 'tbt-home-closing__cta-wrap'), 'Last CTA must include the requested extra spacing hook.');
        $this->assertTrue(!preg_match('~https?://static\.wixstatic\.com~', $html), 'Home must not hotlink Wix media.');
        $this->assertTrue(substr_count($html, '.webp') >= 24, 'Home media must resolve to the local WebP library.');

        $headerSource = (string) file_get_contents(TBT_THEME_DIR . '/header.php');
        $this->assertTrue(str_contains($headerSource, 'tbt-landing-header') && str_contains($headerSource, 'is_front_page') && str_contains($headerSource, "is_page('intensivefix')"), 'Homepage and IntensiveFix must use the Inquire landing header.');
        $this->assertTrue(str_contains($headerSource, 'site-brand__wordmark') && str_contains($headerSource, 'tbt-landing-header__cta'), 'Landing header must show the ThatBioTutor wordmark and a booking button.');
        $this->assertTrue(str_contains($headerSource, 'tbt_render_navigation'), 'Other pages must keep the primary navigation header.');

        fwrite(STDOUT, "PASS: {$this->assertions} Home-page assertions\n");
    }

    private function assertSame(mixed $expected, mixed $actual, string $message): void { $this->assertions++; if ($expected !== $actual) { throw new RuntimeException($message . ' Expected ' . var_export($expected, true) . ', got ' . var_export($actual, true)); } }
    private function assertTrue(bool $condition, string $message): void { $this->assertions++; if (!$condition) { throw new RuntimeException($message); } }
}

try { (new HomePageTest())->run(); } catch (Throwable $error) { fwrite(STDERR, 'FAIL: ' . $error->getMessage() . "\n"); exit(1); }
