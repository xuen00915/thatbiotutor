<?php

declare(strict_types=1);

if (!defined('ABSPATH')) {
    define('ABSPATH', dirname(__DIR__) . '/.wordpress-test/');
}
define('TBT_THEME_DIR', dirname(__DIR__) . '/wordpress/wp-content/themes/thatbiotutor-hello-child');

// Minimal stand-ins for the WordPress functions the sanitiser calls.
function home_url(string $path = '/'): string { return 'https://site.test' . $path; }
function esc_url(string $value): string { return htmlspecialchars($value, ENT_QUOTES, 'UTF-8'); }
function esc_html(string $value): string { return htmlspecialchars($value, ENT_QUOTES, 'UTF-8'); }
function esc_attr(string $value): string { return htmlspecialchars($value, ENT_QUOTES, 'UTF-8'); }
/** A small wp_kses look-alike: keeps only allowed tags and attributes. */
function wp_kses(string $html, array $allowed): string
{
    $html = (string) preg_replace('~<(script|style)\b.*?</\1>~is', '', $html);
    return (string) preg_replace_callback('~<(/?)([a-z0-9]+)([^>]*)>~i', static function (array $m) use ($allowed): string {
        $tag = strtolower($m[2]);
        if (!isset($allowed[$tag])) {
            return '';
        }
        if ($m[1] === '/') {
            return '</' . $tag . '>';
        }
        preg_match_all('~([a-z-]+)="([^"]*)"~i', $m[3], $attributes, PREG_SET_ORDER);
        $kept = '';
        foreach ($attributes as $attribute) {
            if (isset($allowed[$tag][strtolower($attribute[1])]) && !preg_match('~^\s*javascript:~i', $attribute[2])) {
                $kept .= ' ' . strtolower($attribute[1]) . '="' . $attribute[2] . '"';
            }
        }
        return '<' . $tag . $kept . '>';
    }, $html);
}

require_once dirname(__DIR__) . '/wordpress/wp-content/themes/thatbiotutor-hello-child/inc/reference-content.php';

/** Every client-editable field is declared once, is read by its page, and cannot break the layout. */
final class PageContentTest
{
    private int $assertions = 0;

    public function run(): void
    {
        $theme = TBT_THEME_DIR . '/';
        $registry = tbt_page_registry();
        $manifest = json_decode((string) file_get_contents($theme . 'assets/images/media-manifest.json'), true)['items'] ?? [];
        $mediaKeys = array_column($manifest, 'key');
        $schema = tbt_page_content_schema();
        $this->assertTrue(count($schema) >= 5, 'The institutional and results pages must declare their content.');

        $names = [];
        foreach ($schema as $pageKey => $sections) {
            $this->assertTrue(isset($registry[$pageKey]), "{$pageKey} must be a protected page.");
            $template = (string) file_get_contents($theme . $registry[$pageKey]['template']);
            $this->assertTrue($template !== '', "{$pageKey} template must be readable.");
            $sectionKeys = array_column($sections, 'key');
            $this->assertSame(count($sectionKeys), count(array_unique($sectionKeys)), "{$pageKey} section keys must be unique.");

            foreach ($sections as $section) {
                $this->assertTrue(trim((string) $section['title']) !== '', "{$pageKey}/{$section['key']} needs a tab title a client understands.");
                foreach ($section['fields'] as $field) {
                    $name = (string) $field['name'];
                    $this->assertTrue(!isset($names[$name]), "Field name {$name} must be unique across the site.");
                    $names[$name] = true;
                    $this->assertTrue(in_array($field['type'], ['text', 'textarea', 'wysiwyg', 'url', 'image'], true), "{$name} must use an ACF Free field type.");
                    $this->assertTrue(trim((string) $field['label']) !== '', "{$name} needs a label.");
                    $this->assertTrue(!preg_match('/^[a-z0-9_]+$/', (string) $field['label']), "{$name} label must be plain words, not a code name.");

                    // The page must read the field, otherwise editing it would silently do nothing.
                    $dynamicPrefix = preg_replace('/\d+(?:_[a-z]+)?$/', '', $name);
                    $sharedRenderer = in_array($pageKey, ['sec-3-pure-g3-biology-tuition', 'sec-4-pure-g3-biology-tuition'], true); // proven field by field in programme-pages-test.php
                    $this->assertTrue(
                        $sharedRenderer ||
                        ($pageKey === 'home' && $field['type'] === 'image' && tbt_home_image_field((string) $field['fallback']) === $name) || // shared sections find Home pictures by their bundled key
                        str_contains($template, "'{$name}'") || ($dynamicPrefix !== $name && str_contains($template, "'{$dynamicPrefix}'")) || $this->readByModule($name) || ($dynamicPrefix !== $name && $this->readByModule($dynamicPrefix)),
                        "{$pageKey}: field {$name} is declared but its template never reads it."
                    );

                    // Google reviewer photos are optional: the shipped reviewers keep their snapshot photo, new ones show initials.
                    if ($field['type'] === 'image' && preg_match('/^home_google_review_\d+_photo$/', $name) !== 1) {
                        $this->assertTrue(in_array($field['fallback'], $mediaKeys, true), "{$name}: fallback picture {$field['fallback']} must exist in the media manifest.");
                    } else {
                        $this->assertTrue(is_string($field['default']), "{$name} must carry an approved default.");
                    }
                    if ($field['type'] === 'wysiwyg') {
                        $optional = str_contains((string) $field['label'] . (string) $field['help'], 'optional') || str_contains((string) $field['help'], 'to hide');
                        $this->assertTrue($field['default'] !== '' || $optional, "{$name} must ship its approved text (or be marked optional).");
                        preg_match_all('~</?([a-z0-9]+)~i', $field['default'], $tags);
                        foreach (array_unique($tags[1]) as $tag) {
                            $this->assertTrue(isset(tbt_rich_allowed_html()[strtolower($tag)]), "{$name} default uses <{$tag}>, which the sanitiser would remove.");
                        }
                    }
                }
            }
        }

        // The defaults are seeded with the page's other approved fields.
        $reference = tbt_reference_content();
        $this->assertSame('SUCCESS TO US = <mark>TRULY HELPING STUDENTS.</mark>', $reference['pages']['about']['fields']['about_mission_tagline'], 'Declared text defaults must be seeded.');
        $this->assertTrue(!isset($reference['pages']['about']['fields']['about_hero_image']), 'Pictures are never seeded as values.');
        $this->assertTrue(!array_key_exists('contact_intro', $reference['pages']['contact']['fields']), 'A field no page reads must not exist.');

        // The sanitiser: clients cannot break the layout, and links stay safe.
        $this->assertSame('<p>Hi <strong>there</strong></p>', tbt_rich('<p onclick="x()">Hi <strong>there</strong></p><script>alert(1)</script>'), 'Scripts and event attributes must be removed.');
        $this->assertSame('Plain text', tbt_rich('<h1>Plain text</h1>'), 'Headings are not allowed inside formatted text.');
        $this->assertSame('<u>under</u>', tbt_rich('<span style="text-decoration: underline;">under</span>'), 'The editor underline must become <u>.');
        $this->assertSame('<a href="https://site.test/classes/">Classes</a>', tbt_rich('<a href="/classes/">Classes</a>'), 'A root-relative link must follow the site address.');
        $this->assertSame('<a href="https://site.test/#faq">FAQs</a>', tbt_rich('<a href="/#faq">FAQs</a>'), 'A link to a section of the home page must follow the site address.');
        $this->assertSame('<a href="https://x.test/" target="_blank" rel="noopener noreferrer">x</a>', tbt_rich('<a href="https://x.test/" target="_blank">x</a>'), 'A new-tab link must be opened safely.');
        $this->assertSame('<a href="https://x.test/" target="_blank" rel="noopener noreferrer">x</a>', tbt_rich('<a href="https://x.test/" target="_blank" rel="opener">x</a>'), 'A client cannot weaken a new-tab link.');
        $this->assertSame('Big <mark>line</mark>', tbt_rich_inline('<p>Big <mark>line</mark></p>'), 'Single-line fields must not gain a paragraph.');
        $this->assertSame('Student <span>Stories!</span>', tbt_accent_last_word('Student Stories!'), 'The accent keeps the last word with its punctuation.');
        $this->assertSame('Line one<br />' . "\n" . 'Line &amp; two', tbt_multiline("Line one\nLine & two"), 'Plain multi-line text keeps its line breaks and is escaped.');

        fwrite(STDOUT, "PASS: {$this->assertions} page-content assertions\n");
    }

    /** Fields read by a shared module rather than the page template itself. */
    private function readByModule(string $name): bool
    {
        static $source = null;
        $source ??= (string) file_get_contents(TBT_THEME_DIR . '/inc/class-years.php') . (string) file_get_contents(TBT_THEME_DIR . '/inc/tutors.php') . (string) file_get_contents(TBT_THEME_DIR . '/header.php') . (string) file_get_contents(TBT_THEME_DIR . '/inc/forms.php')
            . implode('', array_map(static fn (string $f): string => (string) file_get_contents($f), glob(TBT_THEME_DIR . '/template-parts/*.php') ?: [])); // Home's shared sections
        return str_contains($source, "'{$name}'");
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
    (new PageContentTest())->run();
} catch (Throwable $error) {
    fwrite(STDERR, 'FAIL: ' . $error->getMessage() . "\n");
    exit(1);
}
