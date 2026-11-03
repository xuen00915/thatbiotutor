<?php

declare(strict_types=1);

require_once __DIR__ . '/page-render-bootstrap.php';

final class IntensiveFixPageTest
{
    private int $assertions = 0;

    public function run(): void
    {
        $file = TBT_THEME_DIR . '/page-intensivefix.php';
        $this->assertTrue(is_file($file), 'IntensiveFix template must exist.');
        $this->assertTrue(is_file(TBT_THEME_DIR . '/assets/css/pages-intensivefix.css'), 'IntensiveFix must own a focused stylesheet.');
        $html = tbt_test_render_template('page-intensivefix.php');
        $this->assertSame(1, substr_count($html, '<h1'), 'IntensiveFix must render exactly one H1.');
        foreach (['Fix Your Entire Sec 3 Biology Foundation', 'in 4 Days', 'Everything You Need to Know About IntensiveFix', 'TBT Track Record', 'Turn Your EOY Result into a Sec 4 Recovery Plan', 'Our FastTrack System', 'Is IntensiveFix Right for You?', 'This is for:', 'This isn’t for:'] as $marker) {
            $this->assertTrue(str_contains($html, $marker), "IntensiveFix must contain {$marker}.");
        }

        $last = -1;
        foreach (range(1, 10) as $index) {
            $key = sprintf('intensivefix_program_%02d', $index);
            $position = strpos($html, 'data-media-key="' . $key . '"');
            $this->assertTrue($position !== false, "IntensiveFix must render {$key}.");
            $this->assertTrue($position > $last, "IntensiveFix media {$key} must remain in numeric order.");
            $last = $position;
        }
        $this->assertSame(1, substr_count($html, 'data-media-key="intensivefix_student_reviews"'), 'Supplied student-review artwork must render once.');
        $this->assertTrue(str_contains($html, 'youtube-nocookie.com/embed/q_IPfEdODqI'), 'Analysis video must use the retained YouTube content.');
        $this->assertTrue(str_contains($html, 'curated_shared_fasttrack_content') && str_contains($html, 'curated_shared_fasttrack_memory') && str_contains($html, 'curated_shared_fasttrack_answering'), 'IntensiveFix must explain the shared FastTrack system.');
        $this->assertTrue(str_contains($html, 'https://wa.me/6587597182'), 'Final CTA must use the safe shared WhatsApp link.');
        $this->assertTrue(str_contains($html, 'data-carousel-prev') && str_contains($html, 'data-carousel-next') && str_contains($html, 'data-carousel-dot'), 'Programme artwork must use accessible carousel controls.');
        $this->assertTrue(!str_contains($html, 'inquire.thatbiotutor.com'), 'IntensiveFix must use the original ThatBioTutor visual shell, not the Inquire shell.');

        $focusedCss = (string) file_get_contents(TBT_THEME_DIR . '/assets/css/pages-intensivefix.css');
        $sharedCss = (string) file_get_contents(TBT_THEME_DIR . '/assets/css/pages.css');
        $helpers = (string) file_get_contents(TBT_THEME_DIR . '/inc/theme-helpers.php');
        $this->assertTrue(str_contains($focusedCss, '.tbt-intensivefix-page') && str_contains($focusedCss, '@media (max-width: 320px)'), 'IntensiveFix stylesheet must be scoped and responsive.');
        $this->assertTrue(str_contains($helpers, "\$styles[] = 'pages-intensivefix'"), 'IntensiveFix route must enqueue its focused stylesheet.');
        foreach (['.tbt-home', '.tbt-resource-page', '.tbt-programme-page', '.tbt-institutional-page', '.tbt-intensivefix'] as $routeSelector) {
            $this->assertTrue(!str_contains($sharedCss, $routeSelector), "Shared pages.css must not own {$routeSelector}.");
        }

        fwrite(STDOUT, "PASS: {$this->assertions} IntensiveFix assertions\n");
    }

    private function assertSame(mixed $expected, mixed $actual, string $message): void { $this->assertions++; if ($expected !== $actual) throw new RuntimeException($message); }
    private function assertTrue(bool $condition, string $message): void { $this->assertions++; if (!$condition) throw new RuntimeException($message); }
}

try { (new IntensiveFixPageTest())->run(); } catch (Throwable $error) { fwrite(STDERR, 'FAIL: ' . $error->getMessage() . "\n"); exit(1); }

