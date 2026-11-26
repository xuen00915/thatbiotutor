<?php

declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Editing scope: which ThatBioTutor content the WordPress dashboard lets anyone edit.
 *
 * The `tbt_editing_scope` option is "all" (default: every page panel and every content list) or "testimonials":
 * only the sections in tbt_editing_scope_sections() stay editable (Results & Testimonials: featured YouTube video
 * and Student Stories; Free Bio Notes: Email form), plus the Testimonials and Class Years lists. Every other
 * ThatBioTutor page is removed from the Pages list and cannot be opened for editing, and the Tutors, FAQs and
 * Results lists are hidden. Switch with `wp option update tbt_editing_scope testimonials` or `... all`.
 *
 * The limit applies to the dashboard only: the public site always registers every field, so values keep their
 * ACF formatting.
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

/** Whether the limit applies to this request (dashboard only). */
function tbt_editing_limit_applies(): bool
{
    return tbt_editing_is_limited() && function_exists('is_admin') && is_admin();
}

/**
 * Editable sections per page when editing is limited: tab field key => null (whole tab) or the field names kept.
 *
 * @return array<string, array<string, list<string>|null>>
 */
function tbt_editing_scope_sections(): array
{
    return [
        'testimonials' => [
            'field_tbt_testimonials_tab_featured' => ['testimonials_video_url', 'testimonials_video_title'],
            'field_tbt_testimonials_tab_stories' => null,
            'field_tbt_testimonials_tab_years' => null,
        ],
        'notes' => [
            'field_tbt_notes_tab_form' => null,
        ],
    ];
}

/** @return list<string> Page-registry keys whose field panel stays editable. */
function tbt_editable_page_keys(): array
{
    $all = array_keys(tbt_page_registry());
    return tbt_editing_is_limited() ? array_keys(tbt_editing_scope_sections()) : $all;
}

/** Trim a page group to its editable sections; fields before the first tab (the editing guide) stay. */
function tbt_acf_group_trim_to_scope(array $group, string $pageKey): array
{
    $sections = tbt_editing_scope_sections()[$pageKey] ?? null;
    if ($sections === null) {
        return $group;
    }
    $kept = [];
    $tab = null;
    foreach ($group['fields'] as $field) {
        if (($field['type'] ?? '') === 'tab') {
            $tab = (string) $field['key'];
            if (array_key_exists($tab, $sections)) {
                $kept[] = $field;
            }
            continue;
        }
        if ($tab === null) {
            $kept[] = $field;
            continue;
        }
        if (!array_key_exists($tab, $sections)) {
            continue;
        }
        $names = $sections[$tab];
        if ($names === null || in_array((string) ($field['name'] ?? ''), $names, true)) {
            $kept[] = $field;
        }
    }
    $group['fields'] = $kept;
    return $group;
}

/** The group as the current request should register it, or null to leave it out. */
function tbt_acf_group_for_scope(array $group): ?array
{
    if (!tbt_editing_limit_applies()) {
        return $group;
    }
    if (!tbt_acf_group_is_editable($group)) {
        return null;
    }
    $rule = $group['location'][0][0] ?? [];
    if (($rule['param'] ?? '') === 'page_template') {
        foreach (tbt_page_registry() as $key => $page) {
            if ($page['template'] === ($rule['value'] ?? null)) {
                return tbt_acf_group_trim_to_scope($group, (string) $key);
            }
        }
    }
    return $group;
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

/** @return list<int> IDs of protected pages outside the editable scope. */
function tbt_locked_page_ids(): array
{
    if (!tbt_editing_is_limited() || !function_exists('get_posts')) {
        return [];
    }
    $ids = get_posts([
        'post_type' => 'page',
        'post_status' => 'any',
        'numberposts' => -1,
        'fields' => 'ids',
        'suppress_filters' => true,
    ]);
    return array_values(array_filter(array_map('intval', $ids), 'tbt_page_is_locked'));
}

/** Leave locked pages out of the dashboard Pages list. */
function tbt_editing_scope_hide_locked_pages(mixed $query): void
{
    if (!is_admin() || !$query->is_main_query() || ($GLOBALS['pagenow'] ?? '') !== 'edit.php' || $query->get('post_type') !== 'page') {
        return;
    }
    $locked = tbt_locked_page_ids();
    if ($locked !== []) {
        $query->set('post__not_in', array_merge((array) $query->get('post__not_in'), $locked));
    }
}

/** Keep the Pages list counts (All, Published, ...) in step with the hidden pages. */
function tbt_editing_scope_page_counts(object $counts, string $type): object
{
    if ($type !== 'page' || !is_admin()) {
        return $counts;
    }
    $counts = clone $counts;
    foreach (tbt_locked_page_ids() as $id) {
        $status = (string) get_post_status($id);
        if (isset($counts->{$status}) && $counts->{$status} > 0) {
            $counts->{$status}--;
        }
    }
    return $counts;
}

if (function_exists('add_filter')) {
    add_action('pre_get_posts', 'tbt_editing_scope_hide_locked_pages');
    add_filter('wp_count_posts', 'tbt_editing_scope_page_counts', 10, 2);
    add_filter('register_post_type_args', 'tbt_editing_scope_post_type_args', 10, 2);
    add_filter('map_meta_cap', 'tbt_editing_scope_map_meta_cap', 10, 4);
}
