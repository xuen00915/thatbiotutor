<?php

declare(strict_types=1);

require_once __DIR__ . '/page-render-bootstrap.php';

/**
 * Sec 3 and Sec 4 render from one shared module. Every field declared for them must actually appear on the
 * rendered page (otherwise editing it would silently do nothing), and the two pages must differ only in data.
 */
function metadata_exists(string $type, int $id, string $key): bool
{
    return array_key_exists($key, $GLOBALS['tbt_test_programme_meta'] ?? []);
}

function get_post_meta(int $id, string $key, bool $single): mixed
{
    return $GLOBALS['tbt_test_programme_meta'][$key] ?? '';
}

final class ProgrammePagesTest
{
    private int $assertions = 0;

    public function run(): void
    {
        $registry = tbt_page_registry();
        $rendered = [];
        foreach (['sec3' => ['page-sec-3.php', 'sec-3-pure-g3-biology-tuition'], 'sec4' => ['page-sec-4.php', 'sec-4-pure-g3-biology-tuition']] as $level => [$template, $pageKey]) {
            $this->assertSame($template, $registry[$pageKey]['template'], "{$level} keeps its protected template.");
            $html = tbt_test_render_template($template);
            $rendered[$level] = $html;
            $text = $this->squash($html);
            $fields = tbt_page_content_fields($pageKey);
            $this->assertTrue(isset($fields[$level . '_inclusions_heading']), "{$level} needs one combined inclusions heading.");
            $this->assertTrue(!isset($fields[$level . '_inclusions_line1']) && !isset($fields[$level . '_inclusions_line2']), "{$level} must not expose separate heading lines.");
            $this->assertTrue(!isset(tbt_page_content_defaults($pageKey)[$level . '_inclusions_heading']), "{$level} must not seed a value that would shadow saved heading lines.");
            $GLOBALS['tbt_test_programme_meta'] = [
                $level . '_inclusions_line1' => 'Saved text & first line',
                $level . '_inclusions_line2' => '<mark>Saved second line</mark>',
            ];
            $this->assertTrue(str_contains(tbt_test_render_template($template), '<h2>Saved text &amp; first line<br><span>Saved second line</span></h2>'), 'Existing heading edits retain their words, break and accent.');
            $GLOBALS['tbt_test_programme_meta'][$level . '_inclusions_heading'] = '<p>New first line</p><p><mark>New second line</mark></p>';
            $this->assertTrue(str_contains(tbt_test_render_template($template), '<h2>New first line<br><span>New second line</span></h2>'), 'The new heading takes precedence and preserves an editor line break.');
            unset($GLOBALS['tbt_test_programme_meta']);
            foreach (['testimonials', 'lesson', 'hall'] as $section) {
                $heading = $level . '_' . $section . '_heading';
                $first = $level . '_' . $section . ($section === 'hall' ? '_small' : '_line1');
                $second = $level . '_' . $section . ($section === 'hall' ? '_title' : '_line2');
                $this->assertTrue(isset($fields[$heading]) && !isset($fields[$first]) && !isset($fields[$second]), "{$level} needs one {$section} heading editor.");
                $this->assertTrue(!isset(tbt_page_content_defaults($pageKey)[$heading]), "{$heading} must preserve saved legacy copy on setup.");
                $GLOBALS['tbt_test_programme_meta'] = [$first => 'Original & first', $second => 'Original second'];
                $old = $section === 'hall'
                    ? '<h2><small>Original &amp; first</small><span>Original second</span></h2>'
                    : '<h2>Original &amp; first<br><span>Original second</span></h2>';
                $this->assertTrue(str_contains(tbt_test_render_template($template), $old), 'Saved headline text, hierarchy and accent must carry forward.');
                $GLOBALS['tbt_test_programme_meta'][$heading] = '<p>New opening</p><p><mark>New heading</mark></p>';
                $new = $section === 'hall'
                    ? '<h2><small>New opening</small><span>New heading</span></h2>'
                    : '<h2>New opening<br><span>New heading</span></h2>';
                $this->assertTrue(str_contains(tbt_test_render_template($template), $new), 'One heading must preserve user line breaks and text colour.');
                $GLOBALS['tbt_test_programme_meta'][$heading] = "New opening\r\n<mark>New heading</mark>";
                $this->assertTrue(str_contains(tbt_test_render_template($template), $new), 'A headline keeps the layout when WordPress saves its break as a newline.');
                unset($GLOBALS['tbt_test_programme_meta']);
            }
            $checked = 0;
            foreach (tbt_page_content_fields($pageKey) as $name => $field) {
                $this->assertTrue(!str_starts_with($name, $level . '_faq_image_'), "{$level} must not expose unused FAQ image fields.");
                if ($field['type'] === 'image') {
                    $this->assertTrue(str_contains($html, 'data-media-key="' . $field['fallback'] . '"'), "{$name}: the bundled picture {$field['fallback']} must render.");
                    $checked++;
                    continue;
                }
                $default = (string) $field['default'];
                if ($default === '') {
                    continue; // an optional field (for example the unused third phase) renders nothing by design
                }
                if (str_ends_with($name, '_whatsapp_message')) {
                    $this->assertTrue(str_contains($html, rawurlencode($default)), "{$name}: the message must pre-fill the WhatsApp links.");
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
                    if ($needle === '') {
                        continue;
                    }
                    $this->assertTrue(str_contains($text, $needle), "{$level}: field {$name} is declared but its default text is not on the page: {$line}");
                }
                $checked++;
            }
            $this->assertTrue($checked > 80, "{$level} must declare its whole page, not a handful of fields (checked {$checked}).");
        }

        // The pages differ only in their data.
        $this->assertTrue(str_contains($rendered['sec3'], 'tbt-sec3-page') && str_contains($rendered['sec4'], 'tbt-sec4-page'), 'Each page keeps its own body class.');
        $this->assertTrue(str_contains($rendered['sec3'], 'data-testimonial-cohort="sec3-featured"') && str_contains($rendered['sec4'], 'data-testimonial-cohort="sec4-featured"'), 'Each page shows its own featured testimonials.');
        $this->assertTrue(!str_contains($rendered['sec3'], 'tbt-faq-slider') && !str_contains($rendered['sec4'], 'tbt-faq-slider'), 'Programme pages omit the retired FAQ graphics slider.');
        $this->assertTrue(!str_contains($rendered['sec3'], 'Phase 3') && str_contains($rendered['sec4'], 'Phase 3'), 'Sec 3 has two phases, Sec 4 has three.');
        $this->assertTrue(str_contains($rendered['sec4'], '<ol class="tbt-programme-topics" start="11">') && str_contains($rendered['sec3'], '<ol class="tbt-programme-topics">'), 'Sec 4 topics continue from 11.');
        $this->assertSame(8, substr_count($rendered['sec3'], 'class="tbt-inclusion-card'), 'Eight inclusion cards.');
        $this->assertTrue(str_contains($rendered['sec3'], 'https://wa.me/') && str_contains($rendered['sec3'], 'Sec%203%20trial'), 'The Sec 3 WhatsApp message is pre-filled for Sec 3.');

        fwrite(STDOUT, "PASS: {$this->assertions} programme-page assertions\n");
    }

    /** Visible text with markup, entities and whitespace removed, for comparing defaults to output. */
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
    (new ProgrammePagesTest())->run();
} catch (Throwable $error) {
    fwrite(STDERR, 'FAIL: ' . $error->getMessage() . "\n");
    exit(1);
}
