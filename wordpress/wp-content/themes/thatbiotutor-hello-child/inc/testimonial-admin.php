<?php

declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}

/*
 * Friendlier testimonial editing.
 *
 * Each testimonial says where it appears through a dropdown (class years first, then the fixed page spots), so a
 * typo can no longer make a review vanish. The Testimonials list shows where each one appears and whether it is a
 * video, and can be filtered to one place; the Class Years list shows each year's count with a one-click
 * "Add testimonial" link that preselects that year.
 */

/** Every place a testimonial can appear, as value => label: published class years (newest first), then fixed spots. */
function tbt_testimonial_placements(): array
{
    $placements = [];
    if (function_exists('tbt_get_class_years')) {
        foreach (tbt_get_class_years() as $year) {
            $placements[$year['year']] = 'Results & Testimonials page · ' . $year['heading'];
        }
    }
    return $placements + tbt_testimonial_fixed_placements();
}

/** The label for a stored placement; anything the site does not render reads as not shown. */
function tbt_testimonial_placement_label(string $value): string
{
    return tbt_testimonial_placements()[$value] ?? tbt_testimonial_fixed_placements()['hidden'];
}

/** The placement value in the request, only when it is a known placement. */
function tbt_requested_testimonial_placement(): string
{
    $value = isset($_GET['tbt_placement']) && is_string($_GET['tbt_placement']) ? trim($_GET['tbt_placement']) : '';
    return $value !== '' && isset(tbt_testimonial_placements()[$value]) ? $value : '';
}

/** ACF load_field: fill the dropdown, keep an older saved value selectable, preselect the year from an add link. */
function tbt_testimonial_placement_field(array $field): array
{
    $choices = tbt_testimonial_placements();
    $current = isset($field['value']) && is_string($field['value']) ? $field['value'] : '';
    if ($current !== '' && !isset($choices[$current])) {
        $choices[$current] = tbt_testimonial_fixed_placements()['hidden'];
    }
    $field['choices'] = $choices;
    $requested = tbt_requested_testimonial_placement();
    if ($requested !== '') {
        $field['default_value'] = $requested;
    }
    return $field;
}

/** Testimonials list: "Where it appears" and "Type" right after the title. */
function tbt_testimonial_admin_columns(array $columns): array
{
    $result = [];
    foreach ($columns as $key => $label) {
        $result[$key] = $label;
        if ($key === 'title') {
            $result['tbt_placement'] = 'Where it appears';
            $result['tbt_kind'] = 'Type';
        }
    }
    return $result;
}

/** Render one testimonial list column. */
function tbt_render_testimonial_admin_column(string $column, int $postId): void
{
    if ($column === 'tbt_placement') {
        $value = (string) tbt_structured_meta($postId, 'tbt_display_group', '');
        $url = add_query_arg(['post_type' => 'tbt_testimonial', 'tbt_placement' => $value], admin_url('edit.php'));
        echo '<a href="' . esc_url($url) . '">' . esc_html(tbt_testimonial_placement_label($value)) . '</a>';
    }
    if ($column === 'tbt_kind') {
        echo esc_html((string) tbt_structured_meta($postId, 'tbt_video_url', '') !== '' ? 'Video' : 'Written');
    }
}

/** "Show all places" dropdown above the Testimonials list. */
function tbt_testimonial_placement_filter(string $postType): void
{
    if ($postType !== 'tbt_testimonial') {
        return;
    }
    $selected = tbt_requested_testimonial_placement();
    echo '<label class="screen-reader-text" for="tbt-placement-filter">Filter by where it appears</label>';
    echo '<select name="tbt_placement" id="tbt-placement-filter"><option value="">All places</option>';
    foreach (tbt_testimonial_placements() as $value => $label) {
        echo '<option value="' . esc_attr((string) $value) . '"' . ((string) $value === $selected ? ' selected' : '') . '>' . esc_html($label) . '</option>';
    }
    echo '</select>';
}

/** Narrow the Testimonials list to the chosen place. */
function tbt_filter_testimonials_by_placement(object $query): void
{
    if (!function_exists('is_admin') || !is_admin() || !method_exists($query, 'is_main_query') || !$query->is_main_query()) {
        return;
    }
    if ($query->get('post_type') !== 'tbt_testimonial') {
        return;
    }
    $placement = tbt_requested_testimonial_placement();
    if ($placement !== '') {
        $query->set('meta_query', [['key' => 'tbt_display_group', 'value' => $placement, 'compare' => '=']]);
    }
}

/** Class Years list: a Testimonials column. */
function tbt_class_year_admin_columns(array $columns): array
{
    $result = [];
    foreach ($columns as $key => $label) {
        $result[$key] = $label;
        if ($key === 'title') {
            $result['tbt_year_testimonials'] = 'Testimonials';
        }
    }
    return $result + ['tbt_year_testimonials' => 'Testimonials'];
}

/** Each year: its testimonial count (links to the filtered list) and an "Add testimonial" link for that year. */
function tbt_render_class_year_admin_column(string $column, int $postId): void
{
    if ($column !== 'tbt_year_testimonials') {
        return;
    }
    $year = trim((string) tbt_record_field($postId, 'class_years', 'tbt_year_value'));
    if ($year === '') {
        echo '—';
        return;
    }
    $count = count(tbt_get_structured_posts('tbt_testimonial', [
        'post_status' => 'publish',
        'fields' => 'ids',
        'meta_query' => [['key' => 'tbt_display_group', 'value' => $year, 'compare' => '=']],
    ]));
    $list = add_query_arg(['post_type' => 'tbt_testimonial', 'tbt_placement' => $year], admin_url('edit.php'));
    $add = add_query_arg(['post_type' => 'tbt_testimonial', 'tbt_placement' => $year], admin_url('post-new.php'));
    echo '<a href="' . esc_url($list) . '">' . esc_html($count . ($count === 1 ? ' testimonial' : ' testimonials')) . '</a>';
    echo ' · <a href="' . esc_url($add) . '">Add testimonial</a>';
}

/** Leave the title empty and it takes the student name. */
function tbt_testimonial_title_from_name(array $data, array $postarr): array
{
    if (($data['post_type'] ?? '') !== 'tbt_testimonial' || trim((string) ($data['post_title'] ?? '')) !== '') {
        return $data;
    }
    $name = $postarr['acf']['field_tbt_student_name'] ?? '';
    if (is_string($name) && trim($name) !== '') {
        $data['post_title'] = trim(tbt_strip_tags($name));
    }
    return $data;
}

/** Strip tags without requiring WordPress to be loaded. */
function tbt_strip_tags(string $value): string
{
    return function_exists('wp_strip_all_tags') ? wp_strip_all_tags($value) : strip_tags($value);
}

/** A short guide at the top of the testimonial screen. */
function tbt_testimonial_edit_guide(object $post): void
{
    if (($post->post_type ?? '') !== 'tbt_testimonial') {
        return;
    }
    ?>
    <div class="notice notice-info inline tbt-page-guide" style="margin:12px 0;padding:12px 16px;">
        <p style="margin:0 0 6px;"><strong>How to add or edit a testimonial</strong></p>
        <ol style="margin:0 0 0 18px;">
            <li>Choose <strong>Where it appears</strong>: a class year on Results &amp; Testimonials, or a spot on another page.</li>
            <li>Fill in the <strong>Student name</strong> and the <strong>Quote</strong>. For a video, paste the YouTube link instead of (or as well as) a quote.</li>
            <li>Press <strong>Publish</strong> or <strong>Update</strong>. The title above fills in from the student name if you leave it empty.</li>
        </ol>
        <p style="margin:6px 0 0;"><strong>Order</strong> (in the Page Attributes box on the right): smaller numbers show first within the same place. To hide a review without deleting it, choose "Not shown on the site" or switch it to Draft.</p>
    </div>
    <?php
}

if (function_exists('add_filter') && function_exists('add_action')) {
    add_filter('acf/load_field/key=field_tbt_display_group', 'tbt_testimonial_placement_field');
    add_filter('manage_tbt_testimonial_posts_columns', 'tbt_testimonial_admin_columns', 20);
    add_action('manage_tbt_testimonial_posts_custom_column', 'tbt_render_testimonial_admin_column', 10, 2);
    add_action('restrict_manage_posts', 'tbt_testimonial_placement_filter');
    add_action('pre_get_posts', 'tbt_filter_testimonials_by_placement');
    add_filter('manage_tbt_class_year_posts_columns', 'tbt_class_year_admin_columns', 20);
    add_action('manage_tbt_class_year_posts_custom_column', 'tbt_render_class_year_admin_column', 10, 2);
    add_filter('wp_insert_post_data', 'tbt_testimonial_title_from_name', 10, 2);
    add_action('edit_form_after_title', 'tbt_testimonial_edit_guide');
}
