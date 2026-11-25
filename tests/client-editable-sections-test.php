<?php

declare(strict_types=1);

require_once __DIR__ . '/page-render-bootstrap.php';

/** A field the client saved as empty exists in post meta (so it stays empty instead of falling back). */
function metadata_exists(string $type, int $id, string $key): bool { return array_key_exists($key, $GLOBALS['tbt_test_fields'] ?? []); }

/**
 * Client request (25 Nov): IntensiveFix copy, the Google reviews strip and the Notes signup-form link are edited in
 * WordPress like the rest of the main website. Defaults keep the approved wording; a saved field replaces it.
 */
final class ClientEditableSectionsTest
{
    private int $assertions = 0;

    public function run(): void
    {
        $fields = static function (string $page): array {
            $out = [];
            foreach (tbt_page_content_sections($page) as $section) {
                foreach ($section['fields'] as $field) {
                    $out[$field['name']] = $field + ['section' => $section['title']];
                }
            }
            return $out;
        };

        // IntensiveFix: every visible line is a field, grouped in tabs.
        $ifx = $fields('intensivefix');
        foreach ([
            'intensivefix_kicker', 'intensivefix_heading', 'intensivefix_limited', 'intensivefix_cta_label', 'intensivefix_whatsapp_message',
            'intensivefix_quote_1', 'intensivefix_quote_2', 'intensivefix_quote_3', 'intensivefix_reviews_bridge',
            'intensivefix_track_heading', 'intensivefix_stat_4_value', 'intensivefix_stat_4_label', 'intensivefix_track_bridge', 'intensivefix_video_url',
            'intensivefix_fasttrack_heading', 'intensivefix_closing_heading', 'intensivefix_closing_text', 'intensivefix_closing_ready', 'intensivefix_closing_button',
            'intensivefix_header_label', 'intensivefix_header_message',
        ] as $name) {
            $this->assertTrue(isset($ifx[$name]), "IntensiveFix must declare {$name}.");
        }
        $template = (string) file_get_contents(TBT_THEME_DIR . '/page-intensivefix.php');
        foreach (["Singapore's ONLY", 'Limited time', 'WAY too much', 'Sound familiar', "Here's the track record", 'Lessons over 4 days', "Here's how we make", 'bwMcGJcP0ts', 'All IntensiveFix students', 'demoralising', 'Ready to see', 'Fix My Sec 3 Foundation Now', 'Please share the schedule'] as $fixed) {
            $this->assertTrue(!str_contains($template, $fixed), "IntensiveFix template must not hard-code \"{$fixed}\".");
        }
        $header = (string) file_get_contents(TBT_THEME_DIR . '/header.php');
        $this->assertTrue(str_contains($header, 'intensivefix_header_label') && str_contains($header, 'intensivefix_header_message') && !str_contains($header, 'find out more about IntensiveFix'), 'The IntensiveFix header button text and message must be fields.');

        $GLOBALS['tbt_test_fields'] = [
            'intensivefix_limited' => '<strong>Limited time:</strong> Only during <strong>June holidays</strong>',
            'intensivefix_quote_2' => 'A new worry',
            'intensivefix_closing_button' => 'Book IntensiveFix',
            'intensivefix_stat_4_value' => '10',
            'intensivefix_heading' => 'Fix Sec 3 Bio <mark data-tone="red">Before Sec 4</mark> — In <mark>5 DAYS</mark>',
        ];
        $html = tbt_test_render_template('page-intensivefix.php');
        $this->assertTrue(str_contains($html, 'June holidays') && !str_contains($html, 'last two weeks of Nov'), 'A saved limited-time line replaces the default.');
        $this->assertTrue(str_contains($html, 'A new worry'), 'A saved quote replaces the default.');
        $this->assertTrue(str_contains($html, '>Book IntensiveFix</a>'), 'A saved closing button label replaces the default.');
        $this->assertTrue(str_contains($html, 'data-count-target="10"'), 'A saved 4th number still counts up.');
        $this->assertTrue(preg_match('~<span class="tbt-home-accent tbt-home-accent--red">Before Sec 4</span>~', $html) === 1 && preg_match('~<span class="tbt-home-accent">5 DAYS</span>~', $html) === 1, 'Red and green heading words come from the Red and Highlight buttons.');
        $GLOBALS['tbt_test_fields'] = ['intensivefix_quote_3' => ''];
        $html = tbt_test_render_template('page-intensivefix.php');
        $this->assertSame(2, substr_count($html, '<blockquote>'), 'An emptied quote is hidden.');

        // Google reviews: twelve review slots on Home (shared with IntensiveFix), the first ten seeded from the snapshot.
        $home = $fields('home');
        $snapshot = json_decode((string) file_get_contents(TBT_THEME_DIR . '/assets/content/google-reviews.json'), true);
        $this->assertTrue(isset($home['home_google_heading'], $home['home_google_link_label'], $home['home_google_url']), 'Google reviews heading and link must be fields.');
        foreach (range(1, 12) as $n) {
            foreach (['name', 'text', 'date', 'stars', 'photo'] as $part) {
                $this->assertTrue(isset($home["home_google_review_{$n}_{$part}"]), "Google review {$n} {$part} must be a field.");
            }
        }
        $this->assertSame($snapshot['reviews'][0]['name'], $home['home_google_review_1_name']['default'], 'Review 1 defaults to the snapshot.');
        $this->assertSame('', $home['home_google_review_11_name']['default'], 'Spare slots start empty.');
        $this->assertSame('Google reviews', $home['home_google_review_1_name']['section'], 'Google reviews have their own tab.');

        $GLOBALS['tbt_test_fields'] = ['home_google_review_2_name' => 'New Student', 'home_google_review_2_text' => 'Great classes!', 'home_google_review_2_stars' => '4', 'home_google_review_3_name' => '', 'home_google_review_3_text' => ''];
        ob_start();
        require TBT_THEME_DIR . '/template-parts/google-reviews.php';
        $reviews = (string) ob_get_clean();
        $this->assertSame(9, substr_count($reviews, '<article class="tbt-google-review"'), 'An emptied review is hidden and spare slots stay hidden.');
        $this->assertTrue(str_contains($reviews, 'New Student') && str_contains($reviews, 'Great classes!') && str_contains($reviews, 'aria-label="4 out of 5 stars">★★★★<'), 'A saved review shows its name, text and stars.');
        $this->assertTrue(str_contains($reviews, 'google-review-01.webp'), 'Seeded reviews keep their bundled photo.');
        $this->assertTrue(preg_match('~<article class="tbt-google-review">\s*<div class="tbt-google-review__avatar"><span class="tbt-google-review__initials"[^>]*>NS</span>~', $reviews) === 1, 'A review without a photo shows initials.');
        $GLOBALS['tbt_test_fields'] = [];

        // Free Bio Notes: the signup form link is a field.
        $notes = $fields('notes');
        $this->assertTrue(isset($notes['notes_form_url']) && $notes['notes_form_url']['type'] === 'url', 'Notes must declare the signup-form link.');
        $this->assertSame('https://12-dec-2025-updated-signup-form-02264.getresponsesite.com/', $notes['notes_form_url']['default'], 'The form link defaults to the live GetResponse form.');
        $forms = (string) file_get_contents(TBT_THEME_DIR . '/inc/forms.php');
        $this->assertTrue(str_contains($forms, "notes_form_url") && !str_contains($forms, "\$url = 'https://"), 'The form embed reads the editable link.');

        fwrite(STDOUT, "PASS: {$this->assertions} client-editable section assertions\n");
    }

    private function assertSame(mixed $expected, mixed $actual, string $message): void { $this->assertions++; if ($expected !== $actual) throw new RuntimeException($message . ' Got ' . var_export($actual, true)); }
    private function assertTrue(bool $condition, string $message): void { $this->assertions++; if (!$condition) throw new RuntimeException($message); }
}

try { (new ClientEditableSectionsTest())->run(); } catch (Throwable $error) { fwrite(STDERR, 'FAIL: ' . $error->getMessage() . "\n"); exit(1); }
