<?php

declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Editing scope: which ThatBioTutor content the WordPress dashboard lets anyone edit.
 *
 * The `tbt_editing_scope` option is "all" (default: every page panel and every content list) or "testimonials"
 * (only the Results & Testimonials page, its Testimonials and its Class Years; every other page panel is removed,
 * those pages cannot be opened for editing and the Tutors, FAQs and Results lists are hidden).
 * Switch with `wp option update tbt_editing_scope testimonials` or `... all`. The public site is unaffected.
 */

/** The active editing scope. */
function tbt_editing_scope(): string
{
    $scope = function_exists('get_option') ? (string) get_option('tbt_editing_scope', 'all') : 'all';
    return $scope === 'testimonials' ? 'testimonials' : 'all';
}

/** Whether editing is limited to the Results & Testimonials page. */
function tbt_editing_is_limited(): bool
{
    return tbt_editing_scope() === 'testimonials';
}

/** @return list<string> Page-registry keys whose field panel stays editable. */
function tbt_editable_page_keys(): array
{
    $all = array_keys(tbt_page_registry());
    return tbt_editing_is_limited() ? ['testimonials'] : $all;
}

/** @return list<string> Structured content types that stay editable. */
function tbt_editable_content_types(): array
{
    return tbt_editing_is_limited()
        ? ['tbt_testimonial', 'tbt_class_year']
        : tbt_structured_post_types();
}

/** Whether an ACF field group belongs to editable content. */
function tbt_acf_group_is_editable(array $group): bool
{
    if (!tbt_editing_is_limited()) {
        return true;
    }

    $rule = $group['location'][0][0] ?? [];
    $param = (string) ($rule['param'] ?? '');
    $value = (string) ($rule['value'] ?? '');

    if ($param === 'page_template') {
        $registry = tbt_page_registry();
        foreach (tbt_editable_page_keys() as $key) {
            if (($registry[$key]['template'] ?? null) === $value) {
                return true;
            }
        }
        return false;
    }

    if ($param === 'post_type') {
        return in_array($value, tbt_editable_content_types(), true);
    }

    return true;
}

/** Hide the dashboard screens of locked content types. */
function tbt_editing_scope_post_type_args(array $args, string $postType): array
{
    if (in_array($postType, tbt_structured_post_types(), true) && !in_array($postType, tbt_editable_content_types(), true)) {
        $args['show_ui'] = false;
        $args['show_in_menu'] = false;
        $args['show_in_admin_bar'] = false;
    }
    return $args;
}

/** Whether a page is a protected ThatBioTutor page outside the editable scope. */
function tbt_page_is_locked(int $postId): bool
{
    if (!tbt_editing_is_limited() || !function_exists('tbt_is_protected_page') || !tbt_is_protected_page($postId)) {
        return false;
    }
    $template = (string) get_post_meta($postId, '_wp_page_template', true);
    $registry = tbt_page_registry();
    foreach (tbt_editable_page_keys() as $key) {
        if (($registry[$key]['template'] ?? null) === $template) {
            return false;
        }
    }
    return true;
}

/** Refuse editing, deleting and publishing locked pages from the dashboard. */
function tbt_editing_scope_map_meta_cap(array $caps, string $cap, int $userId, array $args): array
{
    if (!in_array($cap, ['edit_post', 'delete_post', 'publish_post'], true) || empty($args[0])) {
        return $caps;
    }
    return tbt_page_is_locked((int) $args[0]) ? ['do_not_allow'] : $caps;
}

if (function_exists('add_filter')) {
    add_filter('register_post_type_args', 'tbt_editing_scope_post_type_args', 10, 2);
    add_filter('map_meta_cap', 'tbt_editing_scope_map_meta_cap', 10, 4);
}
