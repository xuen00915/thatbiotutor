<?php

declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}

/*
 * Client-editable page content.
 *
 * Every visible piece of copy, every picture and every link on a protected page is declared once, in a
 * per-page file under inc/content/. A declaration holds the field name, the label the client sees, the
 * field type, the approved default (so a fresh install looks exactly like the reference site) and, for
 * pictures, the bundled fallback image. That single declaration feeds three things:
 *   1. the seeded defaults (tbt_reference_content() -> tbt setup-site),
 *   2. the ACF Free field panel on the page's edit screen (tbt_acf_page_groups()),
 *   3. the template, which reads the field and falls back to the same default.
 * Layout, section order and styling stay in the theme; the client only changes words, pictures and links.
 */

/** A single-line text field. */
function tbt_f_text(string $name, string $label, string $default = '', string $help = ''): array
{
    return ['name' => $name, 'label' => $label, 'type' => 'text', 'default' => $default, 'help' => $help];
}

/** A plain multi-line text field (line breaks are kept, no formatting). */
function tbt_f_area(string $name, string $label, string $default = '', string $help = ''): array
{
    return ['name' => $name, 'label' => $label, 'type' => 'textarea', 'default' => $default, 'help' => $help];
}

/** A formatted-text field: bold, italic, underline, highlight, links and bullet lists. */
function tbt_f_rich(string $name, string $label, string $default = '', string $help = ''): array
{
    return ['name' => $name, 'label' => $label, 'type' => 'wysiwyg', 'default' => $default, 'help' => $help];
}

/** A link (URL) field. */
function tbt_f_url(string $name, string $label, string $default = '', string $help = ''): array
{
    return ['name' => $name, 'label' => $label, 'type' => 'url', 'default' => $default, 'help' => $help];
}

/** A picture field. Left empty it shows the bundled picture identified by $fallbackKey. */
function tbt_f_image(string $name, string $label, string $fallbackKey, string $help = ''): array
{
    return ['name' => $name, 'label' => $label, 'type' => 'image', 'default' => '', 'fallback' => $fallbackKey, 'help' => $help];
}

/** A named group of fields that becomes one tab in the page's edit screen. */
function tbt_f_section(string $key, string $title, array $fields, string $intro = ''): array
{
    return ['key' => $key, 'title' => $title, 'intro' => $intro, 'fields' => $fields];
}

/**
 * Return every declared page-content section, keyed by protected page key.
 *
 * @return array<string,list<array{key:string,title:string,intro:string,fields:list<array<string,mixed>>}>>
 */
function tbt_page_content_schema(): array
{
    static $schema = null;
    if (is_array($schema)) {
        return $schema;
    }
    $schema = [];
    $files = glob(__DIR__ . '/content/*.php') ?: [];
    sort($files);
    foreach ($files as $file) {
        $declared = require $file;
        if (is_array($declared) && isset($declared['page'], $declared['sections'])) {
            $schema[(string) $declared['page']] = $declared['sections'];
        }
    }
    return $schema;
}

/** Return the declared sections for one page. */
function tbt_page_content_sections(string $pageKey): array
{
    return tbt_page_content_schema()[$pageKey] ?? [];
}

/** Return every declared field of one page as name => definition. */
function tbt_page_content_fields(string $pageKey): array
{
    $fields = [];
    foreach (tbt_page_content_sections($pageKey) as $section) {
        foreach ($section['fields'] as $field) {
            $fields[(string) $field['name']] = $field;
        }
    }
    return $fields;
}

/** Return the seeded text defaults of one page (pictures have no seeded value). */
function tbt_page_content_defaults(string $pageKey): array
{
    $defaults = [];
    foreach (tbt_page_content_fields($pageKey) as $name => $field) {
        if (($field['type'] ?? '') !== 'image') {
            $defaults[$name] = (string) ($field['default'] ?? '');
        }
    }
    return $defaults;
}

/** Look up one declared field by name across all pages. */
function tbt_page_content_definition(string $name): ?array
{
    static $index = null;
    if ($index === null) {
        $index = [];
        foreach (tbt_page_content_schema() as $sections) {
            foreach ($sections as $section) {
                foreach ($section['fields'] as $field) {
                    $index[(string) $field['name']] = $field;
                }
            }
        }
    }
    return $index[$name] ?? null;
}

/** The only formatting a client can add to a formatted-text field. */
function tbt_rich_allowed_html(): array
{
    return [
        'p' => [], 'br' => [], 'strong' => [], 'b' => [], 'em' => [], 'i' => [], 'u' => [], 'mark' => [],
        'sup' => [], 'sub' => [], 'ul' => [], 'ol' => [], 'li' => [],
        'a' => ['href' => true, 'target' => true, 'rel' => true, 'title' => true],
    ];
}

/** Sanitise formatted client text for output; layout-breaking markup is removed. */
function tbt_rich(string $html): string
{
    // The editor's native underline is a styled span; keep it as the <u> the design styles.
    $html = (string) preg_replace('~<span[^>]*text-decoration:\s*underline[^>]*>(.*?)</span>~is', '<u>$1</u>', $html);
    $html = function_exists('wp_kses')
        ? wp_kses($html, tbt_rich_allowed_html())
        : strip_tags($html, '<p><br><strong><b><em><i><u><mark><sup><sub><ul><ol><li><a>');
    // A link typed as /classes/ or /#faq follows the site wherever WordPress is installed.
    $html = (string) preg_replace_callback('~href="/(?!/)([^"]*)"~', static fn (array $m): string => 'href="' . esc_url(home_url('/' . $m[1])) . '"', $html);
    // Links that open a new tab are always opened safely.
    return (string) preg_replace_callback('~<a\b([^>]*\btarget="_blank"[^>]*)>~i', static function (array $m): string {
        $attributes = (string) preg_replace('~\srel="[^"]*"~i', '', $m[1]);
        return '<a' . $attributes . ' rel="noopener noreferrer">';
    }, $html);
}

/** Formatted text for a single line (a heading or a label): no paragraph wrapper, same safe formatting. */
function tbt_rich_inline(string $html): string
{
    return trim((string) preg_replace('~</?p>~i', '', tbt_rich($html)));
}

/** Wrap the last whitespace-separated word, with its punctuation, in the accent span (e.g. "Student <span>Stories!</span>"). */
function tbt_accent_last_word(string $text): string
{
    $text = trim($text);
    $at = strrpos($text, ' ');
    if ($at === false) {
        return '<span>' . esc_html($text) . '</span>';
    }
    return esc_html(substr($text, 0, $at + 1)) . '<span>' . esc_html(substr($text, $at + 1)) . '</span>';
}

/** Output one plain-text field with line breaks kept. */
function tbt_multiline(string $text): string
{
    return nl2br(esc_html($text));
}

/**
 * Render a picture field: the client's upload when there is one, otherwise the bundled picture.
 * Always carries the bundled picture's key as data-media-key so the page keeps a stable hook.
 */
function tbt_render_content_image(string $name, string $class = '', array $attributes = [], int $postId = 0): void
{
    $definition = tbt_page_content_definition($name);
    $key = (string) ($definition['fallback'] ?? '');
    $value = tbt_page_field($name, null, $postId);
    $custom = ($value !== null && $value !== '' && $value !== false && $value !== []) ? tbt_normalize_image($value) : null;
    if ($custom === null) {
        if ($key !== '') {
            tbt_render_media_image($key, $class, $attributes);
        }
        return;
    }
    $fallback = $key !== '' ? tbt_media($key) : [];
    echo '<img data-media-key="' . esc_attr($key) . '"';
    if ($class !== '') {
        echo ' class="' . esc_attr($class) . '"';
    }
    foreach ($attributes as $attribute => $attributeValue) {
        if (preg_match('/^(?:data-[a-z0-9-]+|aria-[a-z0-9-]+|loading)$/', (string) $attribute) === 1) {
            echo ' ' . esc_attr((string) $attribute) . '="' . esc_attr((string) $attributeValue) . '"';
        }
    }
    echo ' src="' . esc_url((string) $custom['url']) . '" alt="' . esc_attr((string) ($custom['alt'] !== '' ? $custom['alt'] : ($fallback['alt'] ?? ''))) . '" width="' . esc_attr((string) $custom['width']) . '" height="' . esc_attr((string) $custom['height']) . '">';
}

/** Return a content link: the client's value, or the declared default; empty when neither is set. */
function tbt_content_url(string $name, string $default = ''): string
{
    $definition = tbt_page_content_definition($name);
    $value = trim((string) tbt_page_field($name, (string) ($definition['default'] ?? $default)));
    return $value;
}

/** Give the formatted-text editor a clean, client-safe toolbar and keep underline/highlight as plain tags. */
function tbt_editor_toolbars(array $toolbars): array
{
    $toolbars['ThatBioTutor'] = [1 => ['bold', 'italic', 'underline', 'tbt_highlight', 'link', 'unlink', 'bullist', 'numlist', 'removeformat', 'undo', 'redo']];
    return $toolbars;
}

/** Load the Highlight button / <u> underline plugin and let the editor keep <mark> and <u>. */
function tbt_editor_plugins(array $plugins): array
{
    $plugins['tbt_highlight'] = TBT_THEME_URI . '/assets/js/admin-editor.js';
    return $plugins;
}

/** Keep <mark> and <u> in the editor's allowed markup (only inside ACF formatted-text fields). */
function tbt_editor_settings(array $settings, string $editorId = ''): array
{
    if (!str_starts_with($editorId, 'acf')) {
        return $settings;
    }
    $settings['extended_valid_elements'] = 'mark,u';
    $settings['content_style'] = ($settings['content_style'] ?? '') . ' mark{background:#bff0b0;color:inherit;padding:0 2px}';
    return $settings;
}

if (function_exists('add_filter')) {
    add_filter('acf/fields/wysiwyg/toolbars', 'tbt_editor_toolbars');
    add_filter('mce_external_plugins', 'tbt_editor_plugins');
    add_filter('tiny_mce_before_init', 'tbt_editor_settings', 10, 2);
}
