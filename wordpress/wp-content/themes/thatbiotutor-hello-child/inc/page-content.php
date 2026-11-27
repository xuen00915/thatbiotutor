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

/** A single line of formatted text (a card title or heading): bold, italic, underline, green Highlight and links. */
function tbt_f_inline(string $name, string $label, string $default = '', string $help = ''): array
{
    return ['name' => $name, 'label' => $label, 'type' => 'wysiwyg', 'inline' => true, 'default' => $default, 'help' => $help];
}

/**
 * A numbered group of cards, each with a title and a text. $cards is a list of [title, text] defaults.
 * $titleKind is 'text' or 'inline'; $textKind is 'text', 'area', 'inline' or 'rich'. Leave a card's title and
 * text empty to hide that card.
 *
 * @return list<array<string,mixed>>
 */
function tbt_f_cards(string $prefix, string $noun, array $cards, string $titleKind = 'text', string $textKind = 'inline', string $titleLabel = 'title', string $textLabel = 'text'): array
{
    $make = static fn (string $kind, string $name, string $label, string $default): array => match ($kind) {
        'inline' => tbt_f_inline($name, $label, $default),
        'rich' => tbt_f_rich($name, $label, $default),
        'area' => tbt_f_area($name, $label, $default),
        default => tbt_f_text($name, $label, $default),
    };
    $fields = [];
    foreach (array_values($cards) as $index => $card) {
        $n = $index + 1;
        $fields[] = $make($titleKind, "{$prefix}_{$n}_title", "{$noun} {$n} — {$titleLabel}", (string) ($card[0] ?? ''));
        $fields[] = $make($textKind, "{$prefix}_{$n}_text", "{$noun} {$n} — {$textLabel}", (string) ($card[1] ?? ''));
    }
    return $fields;
}

/** A numbered row of value + label pairs (statistics). $items is a list of [value, label] defaults. */
function tbt_f_stats(string $prefix, string $noun, array $items): array
{
    $fields = [];
    foreach (array_values($items) as $index => $item) {
        $n = $index + 1;
        $fields[] = tbt_f_text("{$prefix}_{$n}_value", "{$noun} {$n} — number", (string) $item[0]);
        $fields[] = tbt_f_text("{$prefix}_{$n}_label", "{$noun} {$n} — label", (string) $item[1]);
    }
    return $fields;
}

/** A link (URL) field. */
function tbt_f_url(string $name, string $label, string $default = '', string $help = ''): array
{
    return ['name' => $name, 'label' => $label, 'type' => 'url', 'default' => $default, 'help' => $help];
}

/** A picture field. Left empty it shows the bundled picture identified by $fallbackKey. */
function tbt_f_image(string $name, string $label, string $fallbackKey, string $help = '', string $alt = ''): array
{
    return ['name' => $name, 'label' => $label, 'type' => 'image', 'default' => '', 'fallback' => $fallbackKey, 'help' => $help, 'alt' => $alt];
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
        'p' => [], 'br' => [], 'strong' => [], 'b' => [], 'em' => [], 'i' => [], 'u' => [],
        'sup' => [], 'sub' => [], 'ul' => [], 'ol' => ['start' => true], 'li' => [],
        'a' => ['href' => true, 'target' => true, 'rel' => true, 'title' => true],
        'mark' => ['data-tone' => true],
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
    // Text the client has saved is typographically processed by WordPress (curly quotes); do the same to the built-in
    // defaults so a page looks identical before and after its first save.
    if (function_exists('wptexturize')) {
        $html = wptexturize($html);
    }
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

/** Render saved emphasis as green text in headings. */
function tbt_heading_html(string $html): string
{
    return str_replace('</mark>', '</span>', (string) preg_replace('~<mark[^>]*>~i', '<span>', tbt_rich_inline($html)));
}

/** Render green and red accents in hero titles. */
function tbt_accent_marks(string $html, string $greenClass, string $redClass): string
{
    $html = tbt_rich_inline($html);
    $html = str_replace('<mark data-tone="red">', '<span class="' . esc_attr($redClass) . '">', $html);
    $html = str_replace('<mark>', '<span class="' . esc_attr($greenClass) . '">', $html);
    return str_replace('</mark>', '</span>', $html);
}

/** Add attributes to every opening <$tag> of formatted text, e.g. a class on its lists. */
function tbt_rich_attrs(string $html, string $tag, string $attributes): string
{
    return (string) preg_replace('~<' . preg_quote($tag, '~') . '(\s[^>]*)?>~i', '<' . $tag . ' ' . $attributes . '>', $html);
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
    $bundled = $key !== '' ? tbt_media($key) : [];
    if ($custom === null && (string) ($bundled['url'] ?? '') === '') {
        return;
    }
    $source = $custom ?? $bundled;
    // The client's picture alt text wins, then the page's own description, then the bundled picture's.
    $alt = (string) (($custom['alt'] ?? '') !== '' ? $custom['alt'] : (($definition['alt'] ?? '') !== '' ? $definition['alt'] : ($bundled['alt'] ?? '')));
    echo '<img data-media-key="' . esc_attr($key) . '"';
    if ($class !== '') {
        echo ' class="' . esc_attr($class) . '"';
    }
    foreach ($attributes as $attribute => $attributeValue) {
        if (preg_match('/^(?:data-[a-z0-9-]+|aria-[a-z0-9-]+|loading|fetchpriority)$/', (string) $attribute) === 1) {
            echo ' ' . esc_attr((string) $attribute) . '="' . esc_attr((string) $attributeValue) . '"';
        }
    }
    echo ' src="' . esc_url((string) $source['url']) . '" alt="' . esc_attr($alt) . '" width="' . esc_attr((string) ($source['width'] ?? 0)) . '" height="' . esc_attr((string) ($source['height'] ?? 0)) . '">';
}

/** Convert a supported YouTube URL to its privacy-enhanced embed URL. */
function tbt_testimonial_embed_url(string $url): string
{
    $patterns = [
        '~youtube\.com/watch\?[^#]*v=([A-Za-z0-9_-]{6,})~',
        '~youtu\.be/([A-Za-z0-9_-]{6,})~',
        '~youtube(?:-nocookie)?\.com/embed/([A-Za-z0-9_-]{6,})~',
    ];
    foreach ($patterns as $pattern) {
        if (preg_match($pattern, $url, $matches) === 1) {
            return 'https://www.youtube-nocookie.com/embed/' . $matches[1];
        }
    }
    return '';
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
    $formatting = ['bold', 'italic', 'underline'];
    $links = ['link', 'unlink'];
    $history = ['removeformat', 'undo', 'redo'];
    $toolbars['ThatBioTutor'] = [1 => array_merge($formatting, ['tbt_highlight'], $links, ['bullist', 'numlist'], $history)];
    $toolbars['ThatBioTutor Green'] = [1 => array_merge($formatting, ['tbt_green_text'], $links, $history)];
    $toolbars['ThatBioTutor Green and Red'] = [1 => array_merge($formatting, ['tbt_green_text', 'tbt_red'], $links, $history)];
    return $toolbars;
}

/** Load the Highlight button / <u> underline plugin and let the editor keep <mark> and <u>. */
function tbt_editor_plugins(array $plugins): array
{
    $script = '/assets/js/admin-editor.js';
    $plugins['tbt_highlight'] = TBT_THEME_URI . $script . '?ver=' . (string) filemtime(TBT_THEME_DIR . $script);
    return $plugins;
}

/** Keep <mark> and <u> in the editor's allowed markup (only inside ACF formatted-text fields). */
function tbt_editor_settings(array $settings, string $editorId = ''): array
{
    if (!str_starts_with($editorId, 'acf')) {
        return $settings;
    }
    $settings['extended_valid_elements'] = 'mark,u';
    $settings['content_style'] = ($settings['content_style'] ?? '') . ' mark{background:#d9f2d2;color:inherit;padding:0 2px} mark[data-tone=red]{background:#ffc9c9}';
    return $settings;
}

/** Typed text such as ":)" stays exactly as typed instead of turning into an emoji. */
function tbt_keep_typed_text(): void
{
    remove_filter('acf_the_content', 'convert_smilies', 20);
}

if (function_exists('add_filter')) {
    add_action('acf/init', 'tbt_keep_typed_text');
    add_filter('acf/fields/wysiwyg/toolbars', 'tbt_editor_toolbars');
    add_filter('mce_external_plugins', 'tbt_editor_plugins');
    add_filter('tiny_mce_before_init', 'tbt_editor_settings', 10, 2);
}

require_once __DIR__ . '/programme-pages.php';
require_once __DIR__ . '/home-page.php';
