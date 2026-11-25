<?php

declare(strict_types=1);

require_once __DIR__ . '/page-render-bootstrap.php';

/**
 * Every field declared for the Home page must appear on the rendered page (otherwise editing it would silently
 * do nothing), including the sections Home shares with IntensiveFix.
 */
final class HomeContentTest
{
    private int $assertions = 0;

    public function run(): void
    {
        $html = tbt_test_render_template('front-page.php');
        $text = $this->squash($html);
        $raw = (string) preg_replace('/\s+/u', '', html_entity_decode($html, ENT_QUOTES | ENT_HTML5, 'UTF-8')); // keeps attributes such as a video's title
        $checked = 0;
        foreach (tbt_page_content_fields('home') as $name => $field) {
            if ($field['type'] === 'image' && preg_match('/^home_google_review_\d+_photo$/', $name) === 1) {
                continue; // optional reviewer photo, proven in client-editable-sections-test.php
            }
            if ($field['type'] === 'image') {
                $this->assertTrue(str_contains($html, 'data-media-key="' . $field['fallback'] . '"'), "{$name}: the bundled picture {$field['fallback']} must render.");
                $this->assertSame($name, tbt_home_image_field((string) $field['fallback']), "{$name} must be the field that replaces {$field['fallback']}.");
                $checked++;
                continue;
            }
            $default = (string) $field['default'];
            if ($default === '') {
                continue;
            }
            if ($name === 'home_whatsapp_message') {
                $this->assertTrue(str_contains($html, rawurlencode($default)), 'The WhatsApp message must pre-fill the trial buttons.');
                $checked++;
                continue;
            }
            if ($name === 'home_google_url') {
                $this->assertTrue(str_contains($html, htmlspecialchars($default, ENT_QUOTES, 'UTF-8')), 'The Google reviews link must be used.');
                $checked++;
                continue;
            }
            if ($field['type'] === 'url') {
                preg_match('~(?:v=|youtu\.be/|embed/)([A-Za-z0-9_-]{6,})~', $default, $id);
                $this->assertTrue(isset($id[1]) && str_contains($html, $id[1]), "{$name}: the video {$default} must be embedded.");
                $checked++;
                continue;
            }
            foreach (preg_split('/\R/', $default) ?: [] as $line) {
                $needle = $this->squash($line);
                if ($needle !== '') {
                    $this->assertTrue(str_contains($text, $needle) || str_contains($raw, $needle), "Home field {$name} is declared but its default text is not on the page: {$line}");
                }
            }
            $checked++;
        }
        $this->assertTrue($checked > 100, "Home must declare its whole page (checked {$checked}).");

        // The accents the design styles are built from the editor's Highlight / Red buttons.
        $this->assertTrue(str_contains($html, '<span class="tbt-home-accent tbt-home-accent--red">Fail</span>') && str_contains($html, '<span class="tbt-home-accent">A1</span>') && str_contains($html, '<span class="tbt-home-accent">90%</span>'), 'The hero title keeps its red and green accent words.');
        $this->assertSame(4, substr_count($html, 'data-count-up'), 'All four numbers keep their count-up animation.');
        $this->assertTrue(str_contains($html, 'data-count-target="912"') && str_contains($html, 'data-count-target="91"') && str_contains($html, 'data-count-target="2.3"'), 'Count-up targets come from the typed numbers.');
        $this->assertSame(9, substr_count($html, 'data-home-inquire-faq'), 'All nine FAQs render before setup has run.');
        $this->assertSame(3, substr_count($html, 'Book My Trial Lesson</a>'), 'The trial button text is one field used three times.');

        fwrite(STDOUT, "PASS: {$this->assertions} home-content assertions\n");
    }

    private function squash(string $html): string
    {
        $text = html_entity_decode((string) preg_replace('/<[^>]+>/', ' ', $html), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        return (string) preg_replace('/\s+/u', '', $text);
    }

    private function assertSame(mixed $expected, mixed $actual, string $message): void
    {
        $this->assertions++;
        if ($expected !== $actual) {
            throw new RuntimeException($message . ' Expected ' . var_export($expected, true) . ', got ' . var_export($actual, true));
        }
    }

    private function assertTrue(bool $condition, string $message): void
    {
        $this->assertions++;
        if (!$condition) {
            throw new RuntimeException($message);
        }
    }
}

try {
    (new HomeContentTest())->run();
} catch (Throwable $error) {
    fwrite(STDERR, 'FAIL: ' . $error->getMessage() . "\n");
    exit(1);
}
