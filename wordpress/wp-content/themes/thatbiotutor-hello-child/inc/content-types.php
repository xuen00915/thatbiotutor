<?php

declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}

/** @return list<string> */
function tbt_structured_post_types(): array
{
    return ['tbt_testimonial', 'tbt_tutor', 'tbt_faq', 'tbt_result'];
}

/** Register the private, admin-editable repeated-content types. */
function tbt_register_content_types(): void
{
    $definitions = [
        'tbt_testimonial' => ['Testimonials', 'Testimonial', 'dashicons-format-quote'],
        'tbt_tutor' => ['Tutors', 'Tutor', 'dashicons-businessperson'],
        'tbt_faq' => ['FAQs', 'FAQ', 'dashicons-editor-help'],
        'tbt_result' => ['Results', 'Result', 'dashicons-chart-line'],
    ];

    foreach ($definitions as $postType => [$plural, $singular, $icon]) {
        register_post_type($postType, [
            'labels' => [
                'name' => $plural,
                'singular_name' => $singular,
                'add_new_item' => 'Add ' . $singular,
                'edit_item' => 'Edit ' . $singular,
            ],
            'public' => false,
            'publicly_queryable' => false,
            'exclude_from_search' => true,
            'show_ui' => true,
            'show_in_menu' => true,
            'show_in_rest' => false,
            'menu_icon' => $icon,
            'supports' => $postType === 'tbt_faq'
                ? ['title', 'editor', 'page-attributes']
                : ['title', 'page-attributes'],
            'has_archive' => false,
            'rewrite' => false,
            'query_var' => false,
            'map_meta_cap' => true,
            'capability_type' => 'post',
        ]);
    }
}
add_action('init', 'tbt_register_content_types');

/** Add stable order and publication-state columns to structured admin lists. */
function tbt_structured_admin_columns(array $columns): array
{
    $columns['tbt_order'] = 'Order';
    $columns['tbt_state'] = 'State';
    return $columns;
}

/** Render one structured admin-list column. */
function tbt_render_structured_admin_column(string $column, int $postId): void
{
    if ($column === 'tbt_order' && function_exists('get_post_field')) {
        echo esc_html((string) get_post_field('menu_order', $postId));
    }
    if ($column === 'tbt_state' && function_exists('get_post_status')) {
        echo esc_html(ucfirst((string) get_post_status($postId)));
    }
}

/** Keep structured admin lists in the same deterministic order as public output. */
function tbt_order_structured_admin_query(object $query): void
{
    if (!function_exists('is_admin') || !is_admin() || !method_exists($query, 'is_main_query') || !$query->is_main_query()) {
        return;
    }
    $postType = method_exists($query, 'get') ? $query->get('post_type') : null;
    if (!in_array($postType, tbt_structured_post_types(), true) || !method_exists($query, 'set')) {
        return;
    }
    $query->set('orderby', ['menu_order' => 'ASC', 'title' => 'ASC', 'ID' => 'ASC']);
    $query->set('order', 'ASC');
}

foreach (tbt_structured_post_types() as $tbt_post_type) {
    if (function_exists('add_filter')) {
        add_filter('manage_' . $tbt_post_type . '_posts_columns', 'tbt_structured_admin_columns');
    }
    add_action('manage_' . $tbt_post_type . '_posts_custom_column', 'tbt_render_structured_admin_column', 10, 2);
}
unset($tbt_post_type);
add_action('pre_get_posts', 'tbt_order_structured_admin_query');

/** Return a post meta value without assuming ACF is active. */
function tbt_structured_meta(int $postId, string $name, mixed $fallback = ''): mixed
{
    if (function_exists('get_field')) {
        $value = get_field($name, $postId);
        if ($value !== null && $value !== '' && $value !== []) {
            return $value;
        }
    }
    if (function_exists('get_post_meta')) {
        $value = get_post_meta($postId, $name, true);
        if ($value !== null && $value !== '' && $value !== []) {
            return $value;
        }
    }
    return $fallback;
}

/** Fetch only published records and enforce deterministic public ordering. */
function tbt_get_structured_posts(string $postType, array $args = []): array
{
    $query = array_merge([
        'post_type' => $postType,
        'post_status' => 'publish',
        'posts_per_page' => -1,
        'orderby' => ['menu_order' => 'ASC', 'title' => 'ASC', 'ID' => 'ASC'],
        'order' => 'ASC',
        'no_found_rows' => true,
        'suppress_filters' => false,
    ], $args);

    $posts = get_posts($query);
    usort($posts, static function (object $left, object $right): int {
        $order = ((int) ($left->menu_order ?? 0)) <=> ((int) ($right->menu_order ?? 0));
        if ($order !== 0) {
            return $order;
        }
        $title = strnatcasecmp((string) ($left->post_title ?? ''), (string) ($right->post_title ?? ''));
        return $title !== 0 ? $title : ((int) $left->ID <=> (int) $right->ID);
    });

    return $posts;
}

/** Return the ACF Free groups for repeated content. */
function tbt_acf_content_groups(): array
{
    return [
        [
            'key' => 'group_tbt_testimonial_fields',
            'title' => 'Testimonial details',
            'fields' => [
                tbt_acf_field('field_tbt_student_name', 'Student name', 'tbt_student_name'),
                tbt_acf_textarea('field_tbt_quote', 'Quote', 'tbt_quote'),
                tbt_acf_field('field_tbt_class_year', 'Class / year', 'tbt_class_year'),
                tbt_acf_field('field_tbt_before_result', 'Before result', 'tbt_before_result'),
                tbt_acf_field('field_tbt_after_result', 'After result', 'tbt_after_result'),
                tbt_acf_field('field_tbt_rating', 'Star rating', 'tbt_rating', 'number', ['min' => 1, 'max' => 5, 'step' => 1]),
                tbt_acf_url('field_tbt_video_url', 'YouTube video URL', 'tbt_video_url'),
                tbt_acf_image('field_tbt_testimonial_portrait', 'Portrait / thumbnail', 'tbt_testimonial_portrait'),
                tbt_acf_field('field_tbt_display_group', 'Display group / year', 'tbt_display_group'),
            ],
            'location' => [[[ 'param' => 'post_type', 'operator' => '==', 'value' => 'tbt_testimonial' ]]],
        ],
        [
            'key' => 'group_tbt_tutor_fields',
            'title' => 'Tutor details',
            'fields' => [
                tbt_acf_field('field_tbt_tutor_role', 'Role', 'tbt_tutor_role'),
                tbt_acf_image('field_tbt_tutor_portrait', 'Portrait', 'tbt_tutor_portrait'),
                tbt_acf_textarea('field_tbt_tutor_short_bio', 'Short biography', 'tbt_tutor_short_bio'),
                tbt_acf_textarea('field_tbt_tutor_long_bio', 'Long biography', 'tbt_tutor_long_bio', ['rows' => 8]),
                tbt_acf_field('field_tbt_tutor_sections', 'Active pages / sections', 'tbt_tutor_sections'),
            ],
            'location' => [[[ 'param' => 'post_type', 'operator' => '==', 'value' => 'tbt_tutor' ]]],
        ],
        [
            'key' => 'group_tbt_faq_fields',
            'title' => 'FAQ placement',
            'fields' => [
                tbt_acf_field('field_tbt_faq_collection', 'Page / collection', 'tbt_faq_collection'),
            ],
            'location' => [[[ 'param' => 'post_type', 'operator' => '==', 'value' => 'tbt_faq' ]]],
        ],
        [
            'key' => 'group_tbt_result_fields',
            'title' => 'Result details',
            'fields' => [
                tbt_acf_field('field_tbt_result_student', 'Student', 'tbt_result_student'),
                tbt_acf_field('field_tbt_result_before', 'Before', 'tbt_result_before'),
                tbt_acf_field('field_tbt_result_after', 'After', 'tbt_result_after'),
                tbt_acf_field('field_tbt_result_year', 'Year', 'tbt_result_year'),
                tbt_acf_image('field_tbt_result_artwork', 'Source artwork', 'tbt_result_artwork'),
            ],
            'location' => [[[ 'param' => 'post_type', 'operator' => '==', 'value' => 'tbt_result' ]]],
        ],
    ];
}

