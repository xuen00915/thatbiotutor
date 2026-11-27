<?php

declare(strict_types=1);

const TBT_MANAGER_ROLE_FOR_TEST = 'tbt_manager';

if (!defined('ABSPATH')) {
    define('ABSPATH', dirname(__DIR__) . '/.wordpress-test/');
}

$GLOBALS['tbt_scope_option'] = null;
$GLOBALS['tbt_scope_meta'] = [];
$GLOBALS['tbt_scope_groups'] = [];

$GLOBALS['tbt_page_scope_option'] = 'all';
function get_option(string $name, mixed $default = false): mixed
{
    return match ($name) {
        'tbt_editing_scope' => $GLOBALS['tbt_scope_option'] ?? $default,
        'tbt_page_scope' => $GLOBALS['tbt_page_scope_option'],
        default => $default,
    };
}
function add_action(string $hook, mixed $callback, int $priority = 10, int $args = 1): void
{
    $GLOBALS['tbt_scope_hooks'][$hook][] = $callback;
}
function add_filter(string $hook, mixed $callback, int $priority = 10, int $args = 1): void
{
    $GLOBALS['tbt_scope_hooks'][$hook][] = $callback;
}
function acf_add_local_field_group(array $group): void
{
    $GLOBALS['tbt_scope_groups'][] = $group['key'];
    $GLOBALS['tbt_scope_fields'][$group['key']] = array_values(array_filter(array_map(
        static fn(array $field): string => (string) ($field['name'] ?: $field['key']),
        $group['fields']
    )));
}
function register_post_type(string $type, array $args): void
{
    foreach ($GLOBALS['tbt_scope_hooks']['register_post_type_args'] ?? [] as $filter) {
        $args = $filter($args, $type);
    }
    $GLOBALS['tbt_scope_post_type_args'][$type] = $args;
}
function get_post_type(int $id): string
{
    return $GLOBALS['tbt_scope_post_types'][$id] ?? 'page';
}
function get_posts(array $args): array
{
    return array_keys($GLOBALS['tbt_scope_meta']);
}
function get_post_status(int $id): string
{
    return 'publish';
}
$GLOBALS['tbt_scope_user_id'] = 1;
function get_userdata(int $id): object|false
{
    $roles = [1 => ['administrator'], 2 => [TBT_MANAGER_ROLE_FOR_TEST], 3 => ['editor'], 4 => [TBT_MANAGER_ROLE_FOR_TEST]];
    return isset($roles[$id]) ? (object) ['roles' => $roles[$id]] : false;
}
function wp_get_current_user(): object
{
    return get_userdata($GLOBALS['tbt_scope_user_id']) ?: (object) ['roles' => []];
}
function admin_url(string $path = ''): string
{
    return 'https://example.test/wp-admin/' . $path;
}
$GLOBALS['tbt_scope_admin'] = true;
function is_admin(): bool
{
    return $GLOBALS['tbt_scope_admin'];
}
function get_post_meta(int $id, string $key, bool $single = false): string
{
    return $GLOBALS['tbt_scope_meta'][$id][$key] ?? '';
}

$theme = dirname(__DIR__) . '/wordpress/wp-content/themes/thatbiotutor-hello-child';
foreach (['reference-content', 'acf-fields', 'content-types', 'admin-editing', 'editing-scope'] as $module) {
    require_once "{$theme}/inc/{$module}.php";
}

function tbt_scope_assert(bool $condition, string $message): void
{
    if (!$condition) {
        fwrite(STDERR, "FAIL: {$message}\n");
        exit(1);
    }
}

function tbt_scope_registered(): array
{
    $GLOBALS['tbt_scope_groups'] = [];
    tbt_register_acf_fields();
    return $GLOBALS['tbt_scope_groups'];
}

$GLOBALS['tbt_scope_meta'] = [
    1 => ['_wp_page_template' => 'page-testimonials.php'],
    2 => ['_wp_page_template' => 'page-about.php'],
    3 => ['_wp_page_template' => 'page-notes.php'],
];

$records = [5 => 'tbt_testimonial', 6 => 'tbt_class_year', 7 => 'tbt_tutor', 8 => 'tbt_faq', 9 => 'tbt_result'];
$GLOBALS['tbt_scope_post_types'] = $records;
$allGroups = count(tbt_page_registry()) + count($records);

foreach ([[null, 'all'], ['testimonials', 'all'], ['all', 'limited'], ['limited', 'limited']] as [$editingOption, $pageOption]) {
    $GLOBALS['tbt_scope_option'] = $editingOption;
    $GLOBALS['tbt_page_scope_option'] = $pageOption;
    tbt_scope_assert(count(tbt_scope_registered()) === $allGroups, 'Administrators see every page and structured field group regardless of legacy options.');
    tbt_scope_assert(in_array('notes_heading', $GLOBALS['tbt_scope_fields']['group_tbt_page_notes'], true), 'Administrators see the full Notes panel.');
    tbt_scope_assert(tbt_locked_page_ids() === [], 'Administrators see the complete Pages list.');
    tbt_scope_assert(tbt_editing_scope_map_meta_cap(['edit_pages'], 'edit_post', 1, [2]) === ['edit_pages'], 'Administrators can open About directly.');
    tbt_register_content_types();
    foreach ($records as $id => $type) {
        $args = $GLOBALS['tbt_scope_post_type_args'][$type];
        tbt_scope_assert($args['show_ui'] && $args['show_in_menu'], "Administrators see the {$type} menu.");
        tbt_scope_assert(tbt_editing_scope_map_meta_cap(['edit_posts'], 'edit_post', 1, [$id]) === ['edit_posts'], "Administrators can edit {$type} records.");
        $createCap = $args['capabilities']['create_posts'] ?? 'edit_posts';
        tbt_scope_assert(tbt_editing_scope_map_meta_cap([$createCap], $createCap, 1, []) === ['edit_posts'], "Administrators can create {$type} records.");
    }
}

$GLOBALS['tbt_scope_option'] = 'all';
$GLOBALS['tbt_page_scope_option'] = 'all';
foreach ([2, 3] as $userId) {
    $GLOBALS['tbt_scope_user_id'] = $userId;
    $limited = tbt_scope_registered();
    sort($limited);
    tbt_scope_assert($limited === ['group_tbt_page_notes', 'group_tbt_tutor_fields'], 'Managers and Editors see only Notes and Tutors.');
    $notesFields = $GLOBALS['tbt_scope_fields']['group_tbt_page_notes'];
    foreach (['notes_form_url', 'notes_portrait_image', 'notes_access_note'] as $name) {
        tbt_scope_assert(in_array($name, $notesFields, true), "Limited access keeps {$name}.");
    }
    foreach (['notes_heading', 'notes_hero_lead', 'notes_fit_list', 'notes_slider_image_01', 'tbt_notes_seo_title'] as $name) {
        tbt_scope_assert(!in_array($name, $notesFields, true), "Limited access hides {$name}.");
    }
    foreach ([1, 2, 4] as $postId) {
        tbt_scope_assert(tbt_editing_scope_map_meta_cap(['edit_pages'], 'edit_post', $userId, [$postId]) === ['do_not_allow'], 'Restricted pages cannot be opened directly.');
    }
    tbt_scope_assert(tbt_editing_scope_map_meta_cap(['edit_pages'], 'edit_post', $userId, [3]) === ['edit_pages'], 'The Notes page stays editable.');
    tbt_scope_assert(tbt_editing_scope_map_meta_cap(['delete_pages'], 'delete_post', $userId, [3]) === ['do_not_allow'], 'The Notes page cannot be trashed.');
    tbt_scope_assert(tbt_editing_scope_map_meta_cap(['edit_pages'], 'edit_page', $userId, [2]) === ['do_not_allow'], 'WordPress edit_page also blocks restricted pages.');
    tbt_scope_assert(tbt_editing_scope_map_meta_cap(['delete_pages'], 'delete_page', $userId, [3]) === ['do_not_allow'], 'WordPress delete_page also protects Notes.');
    tbt_scope_assert(tbt_editing_scope_map_meta_cap(['read'], 'read_post', $userId, [2]) === ['read'], 'Reading is unaffected.');
    tbt_register_content_types();
    foreach ($records as $id => $type) {
        $allowed = $type === 'tbt_tutor';
        $args = $GLOBALS['tbt_scope_post_type_args'][$type];
        tbt_scope_assert($args['show_ui'] === $allowed && $args['show_in_menu'] === $allowed, "Limited access sets {$type} menu visibility.");
        tbt_scope_assert(tbt_editing_scope_map_meta_cap(['edit_posts'], 'edit_post', $userId, [$id]) === ($allowed ? ['edit_posts'] : ['do_not_allow']), "Limited access controls {$type} editing.");
        $createCap = $args['capabilities']['create_posts'] ?? 'edit_posts';
        tbt_scope_assert(tbt_editing_scope_map_meta_cap([$createCap], $createCap, $userId, []) === ($allowed ? ['edit_posts'] : ['do_not_allow']), "Limited access controls {$type} creation.");
    }

    tbt_scope_assert(tbt_locked_page_ids() === [1, 2], 'Only Notes appears in the restricted Pages list.');
    $counts = tbt_editing_scope_page_counts((object) ['publish' => 13, 'draft' => 1], 'page');
    tbt_scope_assert($counts->publish === 11 && $counts->draft === 1, 'Page counts exclude hidden pages.');

    $GLOBALS['tbt_scope_admin'] = false;
    tbt_scope_assert(count(tbt_scope_registered()) === $allGroups, 'Public requests register every field for rendering.');
    $GLOBALS['tbt_scope_admin'] = true;
}

tbt_scope_assert(tbt_editing_scope_map_meta_cap(['edit_pages'], 'edit_post', 1, [2]) === ['edit_pages'], 'Capability checks use the administrator being checked.');
$GLOBALS['tbt_scope_user_id'] = 1;
tbt_scope_assert(tbt_editing_scope_map_meta_cap(['edit_pages'], 'edit_post', 2, [2]) === ['do_not_allow'], 'Checking a Manager as an administrator still denies access to locked pages.');

$caps = tbt_manager_capabilities();
foreach (['manage_options', 'edit_theme_options', 'activate_plugins', 'install_plugins', 'delete_pages'] as $cap) {
    tbt_scope_assert(empty($caps[$cap]), "A Manager must not have {$cap}.");
}
foreach (['read', 'upload_files', 'edit_pages', 'edit_published_pages', 'publish_pages', 'edit_posts', 'edit_others_posts', 'list_users', 'create_users', 'edit_users', 'delete_users', 'promote_users'] as $cap) {
    tbt_scope_assert(!empty($caps[$cap]), "A Manager needs {$cap}.");
}

$roles = ['administrator' => [], TBT_MANAGER_ROLE => []];
$GLOBALS['tbt_scope_user_id'] = 2;
foreach ($GLOBALS['tbt_scope_hooks']['editable_roles'] ?? [] as $filter) {
    $roles = $filter($roles);
}
tbt_scope_assert(array_keys($roles) === [TBT_MANAGER_ROLE], 'Managers can only assign the Manager role.');
foreach (['edit_user', 'delete_user', 'remove_user', 'promote_user'] as $cap) {
    $administrator = ['core_cap'];
    $manager = ['core_cap'];
    foreach ($GLOBALS['tbt_scope_hooks']['map_meta_cap'] ?? [] as $filter) {
        $administrator = $filter($administrator, $cap, 2, [1]);
        $manager = $filter($manager, $cap, 2, [4]);
    }
    tbt_scope_assert($administrator === ['do_not_allow'], "Managers cannot use {$cap} on an administrator.");
    tbt_scope_assert($manager === ['core_cap'], "Managers can use {$cap} on another Manager when core permits it.");
}
tbt_scope_assert(str_ends_with(tbt_manager_login_redirect('x', '', get_userdata(2)), 'edit.php?post_type=page'), 'A Manager lands on the Pages list.');
tbt_scope_assert(tbt_manager_login_redirect('x', '', get_userdata(1)) === 'x', 'Administrators keep their login destination.');

fwrite(STDOUT, "PASS: editing scope assertions\n");
