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
        // Client change list (Google Doc table): sections after FastTrack are skipped, jumping to "This is for".
        $sections = ['hero', 'problem', 'track-record', 'fasttrack', 'fit', 'closing'];
        $last = -1;
        foreach ($sections as $section) {
            $position = strpos($html, 'data-section="' . $section . '"');
            $this->assertTrue($position !== false && $position > $last, "IntensiveFix section {$section} must exist in the client's order.");
            $last = (int) $position;
        }
        foreach (['proof', 'tutors', 'quality', 'practical', 'guarantee', 'details', 'analysis', 'founder', 'faq', 'classroom'] as $skipped) {
            $this->assertTrue(!str_contains($html, 'data-section="' . $skipped . '"'), "Section {$skipped} must be skipped per the client change list.");
        }
        $this->assertTrue(str_contains($html, 'tbt-home-inquire') && str_contains($html, 'tbt-intensivefix-page'), 'IntensiveFix must use the Inquire section system.');
        // Hero.
        $this->assertTrue(str_contains($html, 'Fix Your Entire Sec 3 Bio Foundation Before the') && str_contains($html, 'Chaotic Sec 4 Year</span> Begins') && preg_match('~In Just <span[^>]*>4 DAYS</span>~', $html) === 1, 'Headline must read "...In Just 4 DAYS" with the red Chaotic phrase.');
        $this->assertTrue(!str_contains($html, 'Start with a $110 trial lesson') && !str_contains($html, 'tbt-home-hero__refund'), 'The small grey hero subtext must be deleted.');
        $this->assertTrue(str_contains($html, 'Limited time:') && str_contains($html, 'Only during') && str_contains($html, 'last two weeks of Nov/first two weeks of Dec') && str_contains($html, 'tbt-intensivefix-hero__limited'), 'Hero subtitle must be the limited-time line in black and green.');
        $this->assertTrue(!str_contains($html, '91% of Our Students'), 'The old 91% hero subtitle must be replaced.');
        $this->assertTrue(str_contains($html, '>Fix My Sec 3 Foundation</a>') && str_contains($html, 'Fix My Sec 3 Foundation Now'), 'Hero CTA must read "Fix My Sec 3 Foundation".');
        // Hero pictures become the swipeable IntensiveFix graphics in the right order.
        $this->assertTrue(!str_contains($html, 'data-media-key="home_hero_01"'), 'Hero photos must be replaced by the IntensiveFix graphics.');
        $last = -1;
        foreach (range(1, 10) as $index) {
            $key = sprintf('intensivefix_program_%02d', $index);
            $position = strpos($html, 'data-media-key="' . $key . '"');
            $this->assertTrue($position !== false, "IntensiveFix must render {$key}.");
            $this->assertTrue($position > $last, "IntensiveFix graphic {$key} must stay in the supplied order.");
            $last = $position;
        }
        $heroEnd = (int) strpos($html, 'data-section="problem"');
        $this->assertTrue($last < $heroEnd, 'The swipeable graphics must sit in the hero under the main title.');
        // Problem section.
        foreach (['“There\'s WAY too much to memorise... so many confusing terms”', '“I already wrote down a very detailed answer... how come I didn\'t get the points?”', '“School bio skim through the content too fast! I don\'t understand concepts deeply enough...”'] as $quote) {
            $this->assertTrue(str_contains($html, $quote), 'The three phrases under the slider must be the supplied ones.');
        }
        $this->assertTrue(preg_match('~tbt-home-problem__bridge--strong[^>]*>Sound familiar\? After The IntensiveFix Program, They(?:’|&#039;|\')re Telling Us:</p>~', $html) === 1, 'Bridge must read "Sound familiar? After The IntensiveFix Program, They’re Telling Us:" with the old second line deleted.');
        $this->assertTrue(!str_contains($html, 'Many TBT students started exactly here') && !str_contains($html, 'But after our 8-week IntensiveFix'), 'The red-marked bridge wording must be gone.');
        $this->assertTrue(!str_contains($html, 'I flunked my EOYs'), 'The old EOY quotes are replaced by the supplied phrases.');
        $this->assertTrue(!str_contains($html, 'tbt-home-problem__proof') && !str_contains($html, 'curated_home_testimonial_'), 'The three off-topic testimonial cards must be deleted.');
        $this->assertSame(1, substr_count($html, 'data-media-key="intensivefix_student_reviews"'), 'Only one student-review graphic must replace the video.');
        // Track record.
        $this->assertTrue(substr_count($html, 'data-count-up') === 4, 'All four numbers must use the running-number animation.');
        foreach (range(1, 4) as $index) {
            $this->assertSame(1, substr_count($html, 'data-media-key="' . sprintf('home_number_%02d', $index) . '"'), 'Track-record images must match the homepage images.');
        }
        $this->assertTrue(str_contains($html, 'youtube-nocookie.com/embed/q_IPfEdODqI'), 'The current video link stays until Mr Keefe supplies the new one.');
        // FastTrack heading.
        $this->assertTrue(str_contains($html, 'All IntensiveFix students will receive</strong> ThatBioTutor'), 'FastTrack title must start with the IntensiveFix line.');
        $this->assertTrue(str_contains($html, 'curated_shared_fasttrack_content') && str_contains($html, 'curated_shared_fasttrack_memory') && str_contains($html, 'curated_shared_fasttrack_answering'), 'IntensiveFix must explain the shared FastTrack system.');
        // Closing.
        foreach (['Your EOY Bio Grade doesn’t have to define your O Levels.', 'It can be demoralising to have scored poorer than expected for the Sec 3 end of year bio exam. But there is still time to turn things around.', 'We’ve had students come in during this period at F9, going all the way to an A1 in O levels.', 'What you need is a thorough content overhaul, fixing that weak sec 3 foundation, to supercharge and give you confidence heading into sec 4, to score the O level grade you’re truly capable of.', 'Ready to see what that feels like?'] as $copy) {
            $this->assertTrue(str_contains($html, $copy), 'Closing copy must match the change list.');
        }
        $this->assertTrue(str_contains($html, 'Fix My Sec 3 Foundation Now') && str_contains($html, 'https://wa.me/6587597182'), 'Closing CTA is unchanged and opens WhatsApp.');
        $this->assertTrue(str_contains($html, 'data-carousel-prev') && str_contains($html, 'data-carousel-next') && str_contains($html, 'data-carousel-dot'), 'Programme artwork must use accessible carousel controls.');
        $this->assertTrue(preg_match('~<img[^>]+src=""~', $html) !== 1, 'No image may render with an empty source.');
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

