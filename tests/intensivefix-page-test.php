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
        // Rebuilt in the Inquire design: same section system as the homepage.
        $sections = ['hero', 'problem', 'track-record', 'analysis', 'details', 'fasttrack', 'proof', 'tutors', 'quality', 'practical', 'guarantee', 'fit', 'founder', 'faq', 'classroom', 'closing'];
        $last = -1;
        foreach ($sections as $section) {
            $position = strpos($html, 'data-section="' . $section . '"');
            $this->assertTrue($position !== false && $position > $last, "IntensiveFix section {$section} must exist in Inquire order.");
            $last = (int) $position;
        }
        $this->assertTrue(str_contains($html, 'tbt-home-inquire') && str_contains($html, 'tbt-intensivefix-page'), 'IntensiveFix must use the Inquire section system.');
        // Review comments applied.
        $this->assertTrue(str_contains($html, 'Before the Chaotic Sec 4 Year Begins') && preg_match('~In Just <span[^>]*>4 DAYS</span>~', $html) === 1, 'Headline must read "In Just 4 DAYS" (review comment 8).');
        $this->assertTrue(str_contains($html, "But after learning the hard way that it pays to fix your foundation early, they&#039;re saying:") || str_contains($html, 'But after learning the hard way that it pays to fix your foundation early, they’re saying:') || str_contains($html, "But after learning the hard way that it pays to fix your foundation early, they're saying:"), 'Bridge line must follow review comment 9.');
        $this->assertTrue(str_contains($html, 'Secure My Spot in IntensiveFix') && str_contains($html, 'Fix My Sec 3 Foundation Now'), 'CTA texts must be the updated IntensiveFix labels (review comment 5).');
        $this->assertTrue(str_contains($html, 'id="details"') && substr_count($html, 'href="#details"') >= 2, 'Hero and closing buttons must link to the details section (review comment 3).');
        foreach (['$110 per 2-hour lesson', '$90 per 2-hour lesson', '$160 off', '$150 referral bonus', 'refund only applies to that lesson', 'Run 1', 'Run 2'] as $detail) {
            $this->assertTrue(str_contains($html, $detail), "Details must include {$detail} (review comments 4 and 7).");
        }
        $this->assertTrue(str_contains($html, '4-Day'), 'The 4-Day label must be present (review comment 13).');
        $this->assertTrue(str_contains($html, 'Everything You Need to Know About IntensiveFix') && str_contains($html, 'Turn Your EOY Result into a Sec 4 Recovery Plan') && str_contains($html, 'Your EOY Bio Grade doesn’t have to define your O Levels.'), 'IntensiveFix headings must be kept.');
        $this->assertTrue(str_contains($html, 'tbt-intensivefix-details__cta'), 'Details CTA must have its own spacing hook (review comment 14).');
        foreach (['curated_home_testimonial_yong', 'curated_home_testimonial_gwen', 'curated_home_testimonial_aryan'] as $portrait) {
            $this->assertSame(1, substr_count($html, 'data-media-key="' . $portrait . '"'), "Student picture {$portrait} must render once (review comments 10-12).");
        }
        $this->assertTrue(preg_match('~<img[^>]+src=""~', $html) !== 1, 'No image may render with an empty source (review comments 10-12).');

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
        $this->assertTrue(str_contains($html, 'https://wa.me/6587597182'), 'Enrolment CTAs must use the safe shared WhatsApp link.');
        $this->assertTrue(str_contains($html, 'data-carousel-prev') && str_contains($html, 'data-carousel-next') && str_contains($html, 'data-carousel-dot'), 'Programme artwork must use accessible carousel controls.');
        $this->assertTrue(!str_contains($html, 'inquire.thatbiotutor.com'), 'IntensiveFix must not hotlink the Inquire site.');

        $focusedCss = (string) file_get_contents(TBT_THEME_DIR . '/assets/css/pages-intensivefix.css');
        $sharedCss = (string) file_get_contents(TBT_THEME_DIR . '/assets/css/pages.css');
        $helpers = (string) file_get_contents(TBT_THEME_DIR . '/inc/theme-helpers.php');
        $this->assertTrue(str_contains($focusedCss, '.tbt-intensivefix-page') && str_contains($focusedCss, '@media (max-width: 320px)'), 'IntensiveFix stylesheet must be scoped and responsive.');
        $this->assertTrue(str_contains($helpers, "\$styles[] = 'pages-intensivefix'"), 'IntensiveFix route must enqueue its focused stylesheet.');
        $this->assertTrue(preg_match("/is_page\('intensivefix'\)[^}]*pages-home/s", $helpers) === 1, 'IntensiveFix must load the shared Inquire section styles.');
        foreach (['.tbt-home', '.tbt-resource-page', '.tbt-programme-page', '.tbt-institutional-page', '.tbt-intensivefix'] as $routeSelector) {
            $this->assertTrue(!str_contains($sharedCss, $routeSelector), "Shared pages.css must not own {$routeSelector}.");
        }

        fwrite(STDOUT, "PASS: {$this->assertions} IntensiveFix assertions\n");
    }

    private function assertSame(mixed $expected, mixed $actual, string $message): void { $this->assertions++; if ($expected !== $actual) throw new RuntimeException($message); }
    private function assertTrue(bool $condition, string $message): void { $this->assertions++; if (!$condition) throw new RuntimeException($message); }
}

try { (new IntensiveFixPageTest())->run(); } catch (Throwable $error) { fwrite(STDERR, 'FAIL: ' . $error->getMessage() . "\n"); exit(1); }

