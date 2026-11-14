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
