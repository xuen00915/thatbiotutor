<?php

declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}

/** @return list<string> */
function tbt_structured_post_types(): array
{
    return ['tbt_testimonial', 'tbt_class_year', 'tbt_tutor', 'tbt_faq', 'tbt_result'];
}

/** Register the private repeated-content types. */
function tbt_register_content_types(): void
{
    $definitions = [
        'tbt_testimonial' => ['Testimonials', 'Testimonial', 'dashicons-format-quote'],
        'tbt_class_year' => ['Class Years', 'Class Year', 'dashicons-calendar-alt'],
        'tbt_tutor' => ['Tutors', 'Tutor', 'dashicons-businessperson'],
        'tbt_faq' => ['FAQs', 'FAQ', 'dashicons-editor-help'],
        'tbt_result' => ['Results', 'Result', 'dashicons-chart-line'],
    ];

    foreach ($definitions as $postType => [$plural, $singular, $icon]) {
        $showEditor = $postType !== 'tbt_result';
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
            'show_ui' => $showEditor,
            'show_in_menu' => $showEditor,
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

/**
 * Read one field of a seeded record. A field that was never saved falls back to the approved reference record,
 * so the page is complete even before setup has run; a field the client saved as empty stays empty.
 */
function tbt_record_field(int $postId, string $collection, string $name): mixed
{
    $stored = tbt_structured_meta($postId, $name, null);
    if ($stored !== null) {
        return $stored;
    }
    if (function_exists('metadata_exists') && metadata_exists('post', $postId, $name)) {
        return '';
    }
    $seedKey = function_exists('get_post_meta') ? (string) get_post_meta($postId, '_tbt_seed_key', true) : '';
    $records = function_exists('tbt_reference_content') ? (tbt_reference_content()['records'][$collection] ?? []) : [];
    foreach ($records as $record) {
        if (($record['seed_key'] ?? '') === $seedKey && isset($record['fields'][$name])) {
            return $record['fields'][$name];
        }
    }
    return '';
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

/** Fixed places a testimonial can appear (class years are added when the edit screen loads). */
function tbt_testimonial_fixed_placements(): array
{
    return [
        'sec3-featured' => 'Sec 3 page · student reviews',
        'sec4-featured' => 'Sec 4 page · student reviews',
        'notes-reviews' => 'Free Bio Notes page · reviews near the top',
        'notes-reviews-bottom' => 'Free Bio Notes page · reviews at the bottom',
        'celldivision-reviews' => 'Cell Division page · reviews',
        'hidden' => 'Not shown on the site',
    ];
}

/** Return the ACF Free groups for repeated content. */
function tbt_acf_content_groups(): array
{
    return [
        [
            'key' => 'group_tbt_testimonial_fields',
            'title' => 'Testimonial details',
            'fields' => [
                tbt_acf_field('field_tbt_display_group', 'Where it appears', 'tbt_display_group', 'select', [
                    'required' => 1,
                    'choices' => tbt_testimonial_fixed_placements(),
                    'allow_null' => 0,
                    'ui' => 0,
                    'instructions' => 'Pick the page and section. To add a new class year to this list, first add it under Class Years.',
                ]),
                tbt_acf_field('field_tbt_student_name', 'Student name', 'tbt_student_name', 'text', ['instructions' => 'Shown in bold under the quote. You can add the result in brackets, for example "Gwen [F9 to A1]".']),
                tbt_acf_textarea('field_tbt_quote', 'Quote', 'tbt_quote', ['rows' => 6, 'instructions' => 'What the student said. Press Enter twice to start a new paragraph.']),
                tbt_acf_url('field_tbt_video_url', 'YouTube video link (video testimonials only)', 'tbt_video_url', ['instructions' => 'Paste any YouTube link. Leave empty for a written review. Videos are shown before the written reviews of the same year.']),
                tbt_acf_image('field_tbt_testimonial_portrait', 'Photo (optional)', 'tbt_testimonial_portrait', ['instructions' => 'A small round photo next to the review. Leave empty to show the student\'s initials.']),
                tbt_acf_field('field_tbt_rating', 'Star rating', 'tbt_rating', 'number', ['min' => 1, 'max' => 5, 'step' => 1, 'default_value' => 5]),
                tbt_acf_field('field_tbt_before_result', 'Grade before (optional)', 'tbt_before_result', 'text', ['instructions' => 'For example D7. Fill both grades to show "D7 → A2" above the quote.', 'wrapper' => ['width' => '50', 'class' => '', 'id' => '']]),
                tbt_acf_field('field_tbt_after_result', 'Grade after (optional)', 'tbt_after_result', 'text', ['instructions' => 'For example A2.', 'wrapper' => ['width' => '50', 'class' => '', 'id' => '']]),
                tbt_acf_field('field_tbt_class_year', 'Private note (not shown on the site)', 'tbt_class_year', 'text', ['instructions' => 'Optional, for your own records.']),
            ],
            'location' => [[[ 'param' => 'post_type', 'operator' => '==', 'value' => 'tbt_testimonial' ]]],
        ],
        [
            'key' => 'group_tbt_class_year_fields',
            'title' => 'Class year details',
            'fields' => [
                tbt_acf_field('field_tbt_year_value', 'Year', 'tbt_year_value', 'text', ['instructions' => 'The class year, for example 2026. Testimonials whose "Where it appears" is this year show under it (use the "Add testimonial" link in the Class Years list). Newest years are shown first automatically.']),
                tbt_acf_field('field_tbt_year_heading', 'Green panel heading', 'tbt_year_heading', 'text', ['instructions' => 'For example "Class of 2026".']),
                tbt_acf_field('field_tbt_year_summary', 'One-line result headline (optional)', 'tbt_year_summary', 'text', ['instructions' => 'For example "100% of our Class of 2021 improved their grade in 6 months or less". When filled, the year opens with a wide green banner instead of the small panel.']),
                tbt_acf_field('field_tbt_year_advice', 'Line under "Student Stories"', 'tbt_year_advice', 'text', ['instructions' => 'For example "(Watch to the End for Their Advice to Future O Level Batches!)". Leave empty to show no line.']),
                tbt_acf_image('field_tbt_year_poster_1', 'Result poster 1 (tall picture)', 'tbt_year_poster_1', ['instructions' => 'Optional. Leave empty to keep the current picture. New years start empty.']),
                tbt_acf_image('field_tbt_year_poster_2', 'Result poster 2 (tall picture)', 'tbt_year_poster_2'),
                tbt_acf_image('field_tbt_year_poster_3', 'Result poster 3 (tall picture)', 'tbt_year_poster_3'),
                tbt_acf_image('field_tbt_year_table_1', 'Results table 1 (wide picture)', 'tbt_year_table_1', ['instructions' => 'Optional. Leave empty to keep the current picture. New years start empty.']),
                tbt_acf_image('field_tbt_year_table_2', 'Results table 2 (wide picture)', 'tbt_year_table_2'),
                tbt_acf_image('field_tbt_year_table_3', 'Results table 3 (wide picture)', 'tbt_year_table_3'),
            ],
            'location' => [[[ 'param' => 'post_type', 'operator' => '==', 'value' => 'tbt_class_year' ]]],
        ],
        [
            'key' => 'group_tbt_tutor_fields',
            'title' => 'Tutor details',
            'fields' => [
                tbt_acf_field('field_tbt_tutor_role', 'Role', 'tbt_tutor_role'),
                tbt_acf_image('field_tbt_tutor_portrait', 'Portrait', 'tbt_tutor_portrait', ['instructions' => 'Optional. Leave empty to keep the current photo.']),
                tbt_acf_textarea('field_tbt_tutor_short_bio', 'Short biography', 'tbt_tutor_short_bio'),
                tbt_acf_field('field_tbt_tutor_display_name', 'Name shown on the About page', 'tbt_tutor_display_name', 'text', ['instructions' => 'For example "Mr Keefe (Founder)". Leave empty to use the title above.']),
                tbt_acf_textarea('field_tbt_tutor_qualifications', 'Qualifications (About page)', 'tbt_tutor_qualifications', ['rows' => 7, 'instructions' => 'One qualification per line. Each line becomes one bullet.']),
                tbt_acf_url('field_tbt_tutor_profile_url', 'LinkedIn or profile link (About page)', 'tbt_tutor_profile_url', ['instructions' => 'Optional. A LinkedIn address shows the LinkedIn button.']),
                tbt_acf_image('field_tbt_tutor_certificate_1', 'Certificate 1 (About page)', 'tbt_tutor_certificate_1', ['instructions' => 'Optional. Leave all three empty to keep the current certificates, if this tutor has any.']),
                tbt_acf_image('field_tbt_tutor_certificate_2', 'Certificate 2 (About page)', 'tbt_tutor_certificate_2'),
                tbt_acf_image('field_tbt_tutor_certificate_3', 'Certificate 3 (About page)', 'tbt_tutor_certificate_3'),
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

