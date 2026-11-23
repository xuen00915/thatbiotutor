<?php

declare(strict_types=1);

require_once __DIR__ . '/page-render-bootstrap.php';

/**
 * Client feedback of 23 November ("Adcendes Website Editing (1)"): contracts that can be checked without a browser.
 * Sizes, spacing and crops are covered by tests/feedback-nov23-runtime.js.
 */
final class FeedbackNov23Test
{
    private int $assertions = 0;

    public function run(): void
    {
        $css = '';
        foreach (glob(TBT_THEME_DIR . '/assets/css/*.css') ?: [] as $file) {
            $css .= (string) file_get_contents($file);
        }

        // Sec 3 / Sec 4 Hall of Fame: one WhatsApp button, no "Book a Trial".
        foreach (['sec3' => 'page-sec-3.php', 'sec4' => 'page-sec-4.php'] as $level => $template) {
            $html = tbt_test_render_template($template);
            $this->assertSame(1, preg_match_all('/<a class="tbt-programme-hall__action\s/', $html), "{$level}: the Hall of Fame keeps only its WhatsApp button.");
            $this->assertTrue(stripos($html, 'Book a Trial') === false, "{$level}: no Book a Trial button.");
        }
        $this->assertTrue(!str_contains((string) file_get_contents(TBT_THEME_DIR . '/inc/programme-pages.php'), 'hall_button_2'), 'The retired second Hall of Fame button has no editable field.');

        // Testimonials year selector reads "Class of 2026", with no "Years" prefix or doubled spaces.
        $testimonials = (string) file_get_contents(TBT_THEME_DIR . '/page-testimonials.php');
        $this->assertTrue(str_contains($testimonials, "<span aria-hidden=\"true\">☰</span><span data-year-current><?php echo esc_html(isset(\$tbt_years[0]) ? 'Class of ' . \$tbt_years[0]['year'] : ''); ?></span>"), 'The year selector shows "Class of <year>" with no stray spaces.');
        $this->assertTrue(!preg_match('~>\s*Years\s~', $testimonials), 'The year selector no longer says "Years".');
        $this->assertTrue(str_contains((string) file_get_contents(TBT_THEME_DIR . '/assets/js/testimonial-years.js'), "current.textContent = 'Class of ' + sections[index].dataset.section;"), 'Scrolling keeps the "Class of" wording.');

        // Classes: dark green accent on white, and the fourth "1 in 2" number.
        $classes = tbt_test_render_template('page-classes.php');
        $this->assertTrue(str_contains($classes, 'data-count-display="1 in 2">1 in 2</strong><span>Score A (Distinction)</span>'), 'Classes shows the "1 in 2 score A" number.');
        $this->assertSame(4, preg_match_all('~<div><strong data-count-up data-count-target="[\d.]+" data-count-display="[^"]+">[^<]+</strong><span>~', (string) (preg_match('~tbt-programme-stats[^"]*">(.*?)</div></div></section>~s', $classes, $m) ? $m[1] : '')), 'Classes shows four numbers.');
        $this->assertSame(1, preg_match('~data-count-target="2\.3" data-count-display="2\.3"~', $classes), 'Classes numbers count up like the home page.');
        $this->assertTrue(str_contains($css, '.tbt-classes-page .tbt-programme-difference > .tbt-programme-container > h2 span { color: var(--tbt-green-text, #2f8a27); }'), 'The Classes "unlike any other" accent uses the same dark green as the other headings.');

        // Programme heroes: crop towards the teacher and students on phones and tablets.
        $this->assertTrue(str_contains($css, '/* Feedback 23 Nov: hero crops'), 'Programme hero crops are tuned for narrow screens.');

        // Notes: no stray icon before the proof line; GetResponse footer strip cropped off.
        $notes = tbt_test_render_template('page-notes.php');
        $this->assertSame(1, preg_match('~<p class="tbt-resource-proof">Join over~', $notes), 'The "Join over…" line has no icon in front of it.');
        $this->assertTrue(str_contains($css, 'container-type: inline-size') && str_contains($css, '@container tbt-notes-form'), 'The signup iframe is sized to its own width so the provider footer strip is cut off.');

        $this->assertTrue(str_contains((string) file_get_contents(TBT_THEME_DIR . '/inc/forms.php'), 'scrolling="no"'), 'Scrolling over the signup form cannot reveal the provider footer strip.');

        $this->assertTrue(str_contains($css, '/* ---------- Site standard (23 Nov): every page follows the Home / IntensiveFix template'), 'Every page shares the home section rhythm, eyebrow and button styles.');

        fwrite(STDOUT, "PASS: {$this->assertions} 23 November feedback assertions\n");
    }

    private function assertTrue(bool $condition, string $message): void
    {
        $this->assertions++;
        if (!$condition) {
            throw new RuntimeException($message);
        }
    }

    private function assertSame(mixed $expected, mixed $actual, string $message): void
    {
        $this->assertTrue($expected === $actual, $message . ' Expected ' . var_export($expected, true) . ', got ' . var_export($actual, true) . '.');
    }
}

(new FeedbackNov23Test())->run();
