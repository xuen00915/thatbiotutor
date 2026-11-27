<?php

declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}

const TBT_MANAGER_ROLE = 'tbt_manager';
const TBT_CREATE_SITE_RECORD_CAP = 'tbt_create_site_record';

/** Full website editing is reserved for WordPress Administrators. */
function tbt_user_is_site_administrator(mixed $user): bool
{
    return is_object($user) && in_array('administrator', (array) ($user->roles ?? []), true);
}

function tbt_current_user_is_site_administrator(): bool
{
    return function_exists('wp_get_current_user') && tbt_user_is_site_administrator(wp_get_current_user());
}

function tbt_user_has_manager_role(mixed $user): bool
{
    return is_object($user) && !tbt_user_is_site_administrator($user)
        && in_array(TBT_MANAGER_ROLE, (array) ($user->roles ?? []), true);
}

function tbt_current_user_is_manager(): bool
{
    return function_exists('wp_get_current_user') && tbt_user_has_manager_role(wp_get_current_user());
}

function tbt_editing_is_limited(): bool
{
    return !tbt_current_user_is_site_administrator();
}

/**
 * Managers can edit the Notes form and Tutors, upload images, and manage other Manager accounts.
 *
 * @return array<string, bool>
 */
function tbt_manager_capabilities(): array
{
    $caps = [];
    foreach ([
        'read', 'upload_files',
        'edit_posts', 'edit_others_posts', 'edit_published_posts', 'publish_posts',
        'delete_posts', 'delete_others_posts', 'delete_published_posts',
        'edit_pages', 'edit_others_pages', 'edit_published_pages', 'publish_pages',
        'list_users', 'create_users', 'edit_users', 'delete_users', 'promote_users', 'remove_users',
    ] as $cap) {
        $caps[$cap] = true;
    }
    return $caps;
}

/** Create the Manager role, or bring its capabilities up to date. */
function tbt_register_manager_role(): void
{
    if (!function_exists('get_role') || !function_exists('add_role')) {
        return;
    }
    $role = get_role(TBT_MANAGER_ROLE);
    if ($role === null) {
        add_role(TBT_MANAGER_ROLE, 'ThatBioTutor Manager', tbt_manager_capabilities());
        return;
    }
    foreach (tbt_manager_capabilities() as $cap => $grant) {
        if (empty($role->capabilities[$cap])) {
            $role->add_cap($cap, $grant);
        }
    }
}

/** Dashboard menus a Manager never needs. */
function tbt_manager_admin_menu(): void
{
    if (!tbt_current_user_is_manager()) {
        return;
    }
    foreach (['index.php', 'edit.php', 'upload.php', 'edit-comments.php', 'tools.php'] as $menu) {
        remove_menu_page($menu);
    }
    remove_submenu_page('edit.php?post_type=page', 'post-new.php?post_type=page');
}

/** Managers land on the Pages list and cannot open the dashboard home, Posts, new pages, Media or Tools. */
function tbt_manager_admin_redirect(): void
{
    if (!tbt_current_user_is_manager() || (function_exists('wp_doing_ajax') && wp_doing_ajax())) {
        return;
    }
    $screen = (string) ($GLOBALS['pagenow'] ?? '');
    $type = isset($_GET['post_type']) ? (string) $_GET['post_type'] : 'post';
    $blocked = in_array($screen, ['index.php', 'upload.php', 'tools.php', 'edit-comments.php'], true)
        || ($screen === 'edit.php' && $type === 'post')
        || ($screen === 'post-new.php' && in_array($type, ['post', 'page'], true));
    if ($blocked) {
        wp_safe_redirect(admin_url('edit.php?post_type=page'));
        exit;
    }
}

/** Remove the admin-bar shortcuts a Manager cannot use. */
function tbt_manager_admin_bar(mixed $bar): void
{
    if (!tbt_current_user_is_manager()) {
        return;
    }
    foreach (['new-post', 'new-page', 'new-media', 'comments', 'dashboard', 'appearance'] as $node) {
        $bar->remove_node($node);
    }
}

/** Whether a user ID belongs to a Manager. */
function tbt_user_is_manager(int $userId): bool
{
    if ($userId <= 0 || !function_exists('get_userdata')) {
        return false;
    }
    return tbt_user_has_manager_role(get_userdata($userId))
        && (!function_exists('is_multisite') || !is_multisite() || !is_super_admin($userId));
}

/**
 * A Manager may edit, remove or change the role of Manager accounts only (their own included), never an
 * administrator or any other role, and never a user with super-admin rights.
 */
function tbt_manager_user_meta_cap(array $caps, string $cap, int $userId, array $args): array
{
    if (!in_array($cap, ['edit_user', 'delete_user', 'remove_user', 'promote_user'], true) || !tbt_user_is_manager($userId)) {
        return $caps;
    }
    $target = (int) ($args[0] ?? 0);
    if ($target <= 0) {
        return $caps;
    }
    if (($cap === 'delete_user' || $cap === 'remove_user') && $target === $userId) {
        return ['do_not_allow'];
    }
    return tbt_user_is_manager($target) ? $caps : ['do_not_allow'];
}

/** A Manager can only give the Manager role, so nobody can be made an administrator through their account. */
function tbt_manager_editable_roles(array $roles): array
{
    if (!tbt_current_user_is_manager()) {
        return $roles;
    }
    return array_intersect_key($roles, [TBT_MANAGER_ROLE => true]);
}

/** The Users list of a Manager shows Manager accounts only. */
function tbt_manager_user_list(mixed $query): void
{
    if (!tbt_current_user_is_manager() || !is_admin() || ($GLOBALS['pagenow'] ?? '') !== 'users.php') {
        return;
    }
    $query->set('role__in', [TBT_MANAGER_ROLE]);
}

/** Role filter links above the Users list: only Managers. */
function tbt_manager_user_views(array $views): array
{
    return tbt_current_user_is_manager() ? array_intersect_key($views, ['all' => true, TBT_MANAGER_ROLE => true]) : $views;
}

/** After login a Manager goes straight to the Pages list. */
function tbt_manager_login_redirect(string $redirect, string $requested, mixed $user): string
{
    if (tbt_user_has_manager_role($user)) {
        return admin_url('edit.php?post_type=page');
    }
    return $redirect;
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
    $group['hide_on_screen'] = ['custom_fields', 'discussion', 'comments', 'revisions', 'slug', 'author'];
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
        ? ['tbt_tutor']
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

/** Restrict the menus and creation capability of collections beyond the limited scope. */
function tbt_editing_scope_post_type_args(array $args, string $postType): array
{
    if (!in_array($postType, tbt_structured_post_types(), true) || $postType === 'tbt_tutor') {
        return $args;
    }
    $args['capabilities']['create_posts'] = TBT_CREATE_SITE_RECORD_CAP;
    if (tbt_editing_is_limited()) {
        $args['show_ui'] = false;
        $args['show_in_menu'] = false;
        $args['show_in_admin_bar'] = false;
    }
    return $args;
}

/** Whether a page is outside the limited editing scope. */
function tbt_page_is_outside_limited_scope(int $postId): bool
{
    if (!function_exists('tbt_is_protected_page')) {
        return false;
    }
    if (!tbt_is_protected_page($postId)) {
        return function_exists('get_post_type') && get_post_type($postId) === 'page';
    }
    $template = (string) get_post_meta($postId, '_wp_page_template', true);
    $registry = tbt_page_registry();
    foreach (array_keys(tbt_editing_scope_sections()) as $key) {
        if (($registry[$key]['template'] ?? null) === $template) {
            return false;
        }
    }
    return true;
}

/** Protect locked pages and records, and keep the Notes page available for form edits. */
function tbt_editing_scope_map_meta_cap(array $caps, string $cap, int $userId, array $args): array
{
    if ($cap !== TBT_CREATE_SITE_RECORD_CAP
        && (!in_array($cap, ['edit_post', 'edit_page', 'delete_post', 'delete_page', 'publish_post'], true) || empty($args[0]))) {
        return $caps;
    }
    $administrator = function_exists('get_userdata') && tbt_user_is_site_administrator(get_userdata($userId));
    if ($cap === TBT_CREATE_SITE_RECORD_CAP) {
        return $administrator ? ['edit_posts'] : ['do_not_allow'];
    }
    if ($administrator) {
        return $caps;
    }

    $postId = (int) $args[0];
    $postType = function_exists('get_post_type') ? get_post_type($postId) : false;
    if (tbt_page_is_outside_limited_scope($postId)
        || (in_array($postType, tbt_structured_post_types(), true) && $postType !== 'tbt_tutor')
        || ($postType === 'page' && in_array($cap, ['delete_post', 'delete_page'], true))) {
        return ['do_not_allow'];
    }
    return $caps;
}

/** Remove Quick Edit so the Notes list links directly to the form editor. */
function tbt_editing_scope_page_row_actions(array $actions): array
{
    if (tbt_editing_is_limited()) {
        unset($actions['inline hide-if-no-js']);
    }
    return $actions;
}

/** @return list<int> IDs of pages outside the editable scope. */
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
    return array_values(array_filter(array_map('intval', $ids), 'tbt_page_is_outside_limited_scope'));
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
    add_action('init', 'tbt_register_manager_role');
    add_action('admin_menu', 'tbt_manager_admin_menu', 999);
    add_action('admin_init', 'tbt_manager_admin_redirect');
    add_action('admin_bar_menu', 'tbt_manager_admin_bar', 999);
    add_filter('login_redirect', 'tbt_manager_login_redirect', 10, 3);
    add_filter('map_meta_cap', 'tbt_manager_user_meta_cap', 10, 4);
    add_filter('editable_roles', 'tbt_manager_editable_roles');
    add_action('pre_get_users', 'tbt_manager_user_list');
    add_filter('views_users', 'tbt_manager_user_views');
    add_action('pre_get_posts', 'tbt_editing_scope_hide_locked_pages');
    add_filter('page_row_actions', 'tbt_editing_scope_page_row_actions');
    add_filter('wp_count_posts', 'tbt_editing_scope_page_counts', 10, 2);
    add_filter('register_post_type_args', 'tbt_editing_scope_post_type_args', 10, 2);
    add_filter('map_meta_cap', 'tbt_editing_scope_map_meta_cap', 10, 4);
}
