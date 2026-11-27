<?php

declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}

/*
 * Class years on Results & Testimonials.
 *
 * Each "Class of YYYY" block (green panel, result posters, results tables, Student Stories) is one Class Year
 * record under Class Years in the admin menu. The year menu on the page is built from the same records, so adding a
 * year means adding one record: no developer, no template change.
 */

/** Bundled result pictures for the years that shipped with the site, by picture position. */
function tbt_class_year_default_media(): array
{
    return [
        '2025' => ['poster' => ['curated_testimonials_results_2025_01', 'curated_testimonials_results_2025_02'], 'table' => []],
        '2024' => ['poster' => ['curated_testimonials_results_2024_reviews', 'curated_testimonials_results_2024_stats'], 'table' => ['curated_testimonials_results_2024_01', 'curated_testimonials_results_2024_02']],
        '2023' => ['poster' => ['curated_testimonials_results_2023_01', 'curated_testimonials_results_2023_02'], 'table' => ['curated_testimonials_results_2023_03']],
        '2022' => ['poster' => [], 'table' => ['curated_testimonials_results_2022_01']],
        '2021' => ['poster' => [], 'table' => []],
    ];
}

/** Convert a stored true/false value to a bool. */
function tbt_class_year_flag(mixed $value): bool
{
    return in_array($value, [true, 1, '1', 'true', 'on', 'yes'], true);
}

/**
 * Resolve the pictures of one kind ("poster" or "table"): the client's upload in a position wins, otherwise the
 * bundled picture for that position, otherwise nothing.
 *
 * @return list<array{image?:array,key:string}>
 */
function tbt_class_year_pictures(int $postId, string $year, string $kind): array
{
    $bundled = tbt_class_year_default_media()[$year][$kind] ?? [];
    $items = [];
    foreach ([1, 2, 3] as $slot) {
        $uploaded = function_exists('tbt_normalize_image')
            ? tbt_normalize_image(tbt_structured_meta($postId, 'tbt_year_' . $kind . '_' . $slot, ''))
            : null;
        $key = (string) ($bundled[$slot - 1] ?? '');
        if (is_array($uploaded)) {
            $items[] = ['image' => $uploaded, 'key' => $key];
        } elseif ($key !== '') {
            $items[] = ['key' => $key];
        }
    }
    return $items;
}

/**
 * The line under "Student Stories" for one year: the typed text (empty hides it), else the seeded line of a
 * shipped year, else null so the page falls back to the old on/off switch and the page-wide line.
 */
function tbt_class_year_advice(int $postId): ?string
{
    $value = tbt_record_field($postId, 'class_years', 'tbt_year_advice');
    return $value === null ? null : trim((string) $value);
}

/** Admin: an advice field that was never saved shows the line the page currently displays, so saving keeps it. */
function tbt_class_year_advice_load_value(mixed $value, mixed $postId, mixed $field): mixed
{
    $id = (int) $postId;
    if ($id <= 0 || (function_exists('metadata_exists') && metadata_exists('post', $id, 'tbt_year_advice'))) {
        return $value;
    }
    // Read the seed directly: tbt_record_field() goes through get_field(), which would call this filter again.
    $seedKey = function_exists('get_post_meta') ? (string) get_post_meta($id, '_tbt_seed_key', true) : '';
    foreach ((tbt_reference_content()['records']['class_years'] ?? []) as $record) {
        if (($record['seed_key'] ?? '') === $seedKey && isset($record['fields']['tbt_year_advice'])) {
            return (string) $record['fields']['tbt_year_advice'];
        }
    }
    $flag = function_exists('get_post_meta') ? get_post_meta($id, 'tbt_year_show_advice', true) : '';
    if (!tbt_class_year_flag($flag === '' ? '1' : $flag)) {
        return '';
    }
    $defaults = function_exists('tbt_page_content_defaults') ? tbt_page_content_defaults('testimonials') : [];
    return (string) ($defaults['testimonials_stories_advice'] ?? '');
}

/** Admin: show the bundled picture under an empty poster/table field, so "No image selected" is not mistaken for a missing picture. */
function tbt_class_year_field_builtin_preview(mixed $field): mixed
{
    if (!is_array($field) || !empty($field['value']) || !function_exists('get_the_ID') || !function_exists('tbt_media')) {
        return $field;
    }
    // ACF swaps "name" for the field key while preparing; the meta name is kept in "_name".
    $fieldName = (string) ($field['_name'] ?? $field['name'] ?? '');
    if (preg_match('/^tbt_year_(poster|table)_([123])$/', $fieldName, $match) !== 1) {
        return $field;
    }
    $postId = (int) get_the_ID();
    $year = $postId > 0 ? trim((string) tbt_record_field($postId, 'class_years', 'tbt_year_value')) : '';
    $key = (string) (tbt_class_year_default_media()[$year][$match[1]][(int) $match[2] - 1] ?? '');
    $image = $key !== '' ? tbt_media($key) : ['url' => ''];
    if (($image['url'] ?? '') === '') {
        return $field;
    }
    $field['instructions'] = 'Currently shown on the site (built in). Add an image here only to replace it.'
        . '<br><img src="' . esc_url((string) $image['url']) . '" alt="" style="max-width:220px;max-height:220px;margin-top:8px;border-radius:6px;">';
    return $field;
}

if (function_exists('add_filter')) {
    add_filter('acf/load_value/key=field_tbt_year_advice', 'tbt_class_year_advice_load_value', 10, 3);
    foreach (['poster', 'table'] as $tbtKind) {
        foreach ([1, 2, 3] as $tbtSlot) {
            add_filter('acf/prepare_field/key=field_tbt_year_' . $tbtKind . '_' . $tbtSlot, 'tbt_class_year_field_builtin_preview');
        }
    }
}

/** Return the published class years, newest first. */
function tbt_get_class_years(): array
{
    $years = [];
    foreach (tbt_get_structured_posts('tbt_class_year') as $post) {
        $id = (int) $post->ID;
        $year = trim((string) tbt_record_field($id, 'class_years', 'tbt_year_value'));
        if ($year === '' || preg_match('/^[0-9A-Za-z-]{1,12}$/', $year) !== 1) {
            continue;
        }
        $heading = trim((string) tbt_record_field($id, 'class_years', 'tbt_year_heading'));
        $years[] = [
            'id' => $id,
            'year' => $year,
            'heading' => $heading !== '' ? $heading : 'Class of ' . $year,
            'summary' => trim((string) tbt_record_field($id, 'class_years', 'tbt_year_summary')),
            'growth_icon' => tbt_class_year_flag(tbt_record_field($id, 'class_years', 'tbt_year_growth_icon')),
            'show_advice' => tbt_class_year_flag(tbt_record_field($id, 'class_years', 'tbt_year_show_advice')),
            'whatsapp_button' => tbt_class_year_flag(tbt_record_field($id, 'class_years', 'tbt_year_whatsapp_button')),
            'advice' => tbt_class_year_advice($id),
            'posters' => tbt_class_year_pictures($id, $year, 'poster'),
            'tables' => tbt_class_year_pictures($id, $year, 'table'),
        ];
    }
    usort($years, static fn (array $a, array $b): int => strnatcmp($b['year'], $a['year']));
    return $years;
}

/** Output a list of resolved pictures. */
function tbt_render_class_year_pictures(array $items): void
{
    foreach ($items as $item) {
        if (isset($item['image'])) {
            $image = $item['image'];
            $alt = $image['alt'] !== '' ? $image['alt'] : (string) (tbt_media((string) $item['key'])['alt'] ?? '');
            echo '<img data-media-key="' . esc_attr((string) $item['key']) . '" loading="lazy" src="' . esc_url($image['url']) . '" alt="' . esc_attr($alt) . '" width="' . esc_attr((string) $image['width']) . '" height="' . esc_attr((string) $image['height']) . '">';
        } elseif ((string) $item['key'] !== '') {
            tbt_render_media_image((string) $item['key'], '', ['loading' => 'lazy']);
        }
    }
}

/** The parts a year offers in the year menu, in page order. */
function tbt_class_year_menu_parts(array $year): array
{
    $parts = [];
    if ($year['posters'] !== []) {
        $parts['poster'] = 'Result poster';
    }
    if ($year['tables'] !== []) {
        $parts['table'] = 'Results table';
    }
    $parts['stories'] = 'Student stories';
    $parts['google-reviews'] = 'Google reviews';
    return $parts;
}
