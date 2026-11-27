<?php

declare(strict_types=1);

const TBT_MANAGER_ROLE_FOR_TEST = 'tbt_manager';

if (!defined('ABSPATH')) {
    define('ABSPATH', dirname(__DIR__) . '/.wordpress-test/');
}

$GLOBALS['tbt_scope_option'] = 'all';
$GLOBALS['tbt_scope_meta'] = [];
$GLOBALS['tbt_scope_groups'] = [];

function get_option(string $name, mixed $default = false): mixed
{
    return $name === 'tbt_editing_scope' ? $GLOBALS['tbt_scope_option'] : $default;
}
function add_action(string $hook, mixed $callback, int $priority = 10, int $args = 1): void {}
function add_filter(string $hook, mixed $callback, int $priority = 10, int $args = 1): void {}
function acf_add_local_field_group(array $group): void
{
    $GLOBALS['tbt_scope_groups'][] = $group['key'];
    $GLOBALS['tbt_scope_fields'][$group['key']] = array_values(array_filter(array_map(
        static fn(array $field): string => (string) ($field['name'] ?: $field['key']),
        $group['fields']
    )));
}
function get_post_type(int $id): string
{
    return 'page';
}
function get_posts(array $args): array
{
    return array_keys($GLOBALS['tbt_scope_meta']);
}
function get_post_status(int $id): string
{
    return 'publish';
}
$GLOBALS['tbt_scope_roles'] = [];
function wp_get_current_user(): object
{
    return (object) ['roles' => $GLOBALS['tbt_scope_roles']];
}
function admin_url(string $path = ''): string
{
    return 'https://example.test/wp-admin/' . $path;
}
$GLOBALS['tbt_scope_user_roles'] = [1 => ['administrator'], 5 => [TBT_MANAGER_ROLE_FOR_TEST], 6 => [TBT_MANAGER_ROLE_FOR_TEST], 7 => ['editor']];
function get_userdata(int $id): object|false
{
    return isset($GLOBALS['tbt_scope_user_roles'][$id]) ? (object) ['roles' => $GLOBALS['tbt_scope_user_roles'][$id]] : false;
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

// Default: everything editable.
$all = tbt_scope_registered();
tbt_scope_assert(count($all) === count(tbt_page_registry()) + 5, 'Scope "all" registers every page and content group.');
tbt_scope_assert(tbt_editing_scope_map_meta_cap(['edit_pages'], 'edit_post', 1, [2]) === ['edit_pages'], 'Scope "all" keeps every page editable.');
tbt_scope_assert(tbt_editing_scope_post_type_args(['show_ui' => true], 'tbt_faq')['show_ui'] === true, 'Scope "all" keeps FAQs visible.');

// Testimonials only.
$GLOBALS['tbt_scope_option'] = 'testimonials';
$limited = tbt_scope_registered();
sort($limited);
tbt_scope_assert($limited === ['group_tbt_class_year_fields', 'group_tbt_page_notes', 'group_tbt_page_testimonials', 'group_tbt_testimonial_fields'], 'Limited scope registers only the testimonials and notes pages, testimonials and class years: ' . implode(',', $limited));
$testimonialFields = $GLOBALS['tbt_scope_fields']['group_tbt_page_testimonials'];
foreach (['testimonials_video_url', 'testimonials_video_title', 'testimonials_stories_heading', 'testimonials_stories_advice', 'testimonials_archive_button_label'] as $name) {
    tbt_scope_assert(in_array($name, $testimonialFields, true), "Testimonials keeps {$name}.");
}
foreach (['testimonials_heading', 'testimonials_hero_lead', 'testimonials_button_label', 'testimonials_whatsapp_message', 'tbt_testimonials_seo_title'] as $name) {
    tbt_scope_assert(!in_array($name, $testimonialFields, true), "Testimonials drops {$name}.");
}
$notesFields = $GLOBALS['tbt_scope_fields']['group_tbt_page_notes'];
foreach (['notes_form_url', 'notes_portrait_image', 'notes_access_note'] as $name) {
    tbt_scope_assert(in_array($name, $notesFields, true), "Notes keeps {$name}.");
}
foreach (['notes_heading', 'notes_hero_lead', 'notes_fit_list', 'notes_slider_image_01', 'tbt_notes_seo_title'] as $name) {
    tbt_scope_assert(!in_array($name, $notesFields, true), "Notes drops {$name}.");
}
tbt_scope_assert(tbt_editing_scope_map_meta_cap(['edit_pages'], 'edit_post', 1, [3]) === ['edit_pages'], 'The notes page stays editable.');

// The public site always registers every field so values keep their formatting.
$GLOBALS['tbt_scope_admin'] = false;
tbt_scope_assert(count(tbt_scope_registered()) === count($all), 'The limit never applies outside the dashboard.');
$GLOBALS['tbt_scope_admin'] = true;
tbt_scope_assert(tbt_editing_scope_map_meta_cap(['edit_pages'], 'edit_post', 1, [1]) === ['edit_pages'], 'The testimonials page stays editable.');
tbt_scope_assert(tbt_editing_scope_map_meta_cap(['edit_pages'], 'edit_post', 1, [2]) === ['do_not_allow'], 'Other protected pages are locked.');
tbt_scope_assert(tbt_editing_scope_map_meta_cap(['read'], 'read_post', 1, [2]) === ['read'], 'Reading is never blocked.');
foreach (['tbt_tutor', 'tbt_faq', 'tbt_result'] as $type) {
    tbt_scope_assert(tbt_editing_scope_post_type_args(['show_ui' => true], $type)['show_ui'] === false, "{$type} is hidden.");
}
foreach (['tbt_testimonial', 'tbt_class_year'] as $type) {
    tbt_scope_assert(tbt_editing_scope_post_type_args(['show_ui' => true], $type)['show_ui'] === true, "{$type} stays visible.");
}

tbt_scope_assert(tbt_locked_page_ids() === [2], 'Only locked pages are hidden from the Pages list.');
$counts = tbt_editing_scope_page_counts((object) ['publish' => 13, 'draft' => 1], 'page');
tbt_scope_assert($counts->publish === 12 && $counts->draft === 1, 'Page counts drop by the hidden pages.');

$GLOBALS['tbt_scope_option'] = 'all';
tbt_scope_assert(tbt_locked_page_ids() === [], 'Scope "all" hides no pages.');

// Manager role: limited whatever the option says; administrators follow the option.
$GLOBALS['tbt_scope_option'] = 'all';
$GLOBALS['tbt_scope_roles'] = ['administrator'];
tbt_scope_assert(!tbt_editing_is_limited(), 'An administrator gets full editing when the option is "all".');
$GLOBALS['tbt_scope_roles'] = [TBT_MANAGER_ROLE];
tbt_scope_assert(tbt_editing_is_limited(), 'A Manager always gets limited editing.');
tbt_scope_assert(tbt_editing_scope_map_meta_cap(['edit_pages'], 'edit_post', 1, [2]) === ['do_not_allow'], 'A Manager cannot edit About.');
tbt_scope_assert(tbt_editing_scope_map_meta_cap(['edit_pages'], 'edit_post', 1, [1]) === ['edit_pages'], 'A Manager can edit Results & Testimonials.');
tbt_scope_assert(tbt_editing_scope_map_meta_cap(['edit_pages'], 'edit_post', 1, [3]) === ['edit_pages'], 'A Manager can edit Free Bio Notes.');
tbt_scope_assert(tbt_editing_scope_map_meta_cap(['edit_pages'], 'edit_post', 1, [4]) === ['do_not_allow'], 'A Manager cannot edit other pages such as Privacy Policy.');
tbt_scope_assert(count(tbt_scope_registered()) === 4, 'A Manager sees only the limited panels.');
$caps = tbt_manager_capabilities();
foreach (['manage_options', 'edit_theme_options', 'activate_plugins', 'install_plugins', 'delete_pages'] as $cap) {
    tbt_scope_assert(empty($caps[$cap]), "A Manager must not have {$cap}.");
}
foreach (['read', 'upload_files', 'edit_pages', 'edit_published_pages', 'publish_pages', 'edit_posts', 'edit_others_posts', 'list_users', 'create_users', 'edit_users', 'delete_users', 'promote_users'] as $cap) {
    tbt_scope_assert(!empty($caps[$cap]), "A Manager needs {$cap}.");
}
tbt_scope_assert(str_ends_with(tbt_manager_login_redirect('x', '', (object) ['roles' => [TBT_MANAGER_ROLE]]), 'edit.php?post_type=page'), 'A Manager lands on the Pages list.');
tbt_scope_assert(tbt_manager_login_redirect('x', '', (object) ['roles' => ['administrator']]) === 'x', 'Administrators keep their login destination.');
// Manager user management: other Managers only, never administrators, only the Manager role can be given.
tbt_scope_assert(TBT_MANAGER_ROLE === TBT_MANAGER_ROLE_FOR_TEST, 'Role slug is tbt_manager.');
foreach (['edit_user', 'delete_user', 'remove_user', 'promote_user'] as $cap) {
    tbt_scope_assert(tbt_manager_user_meta_cap(['edit_users'], $cap, 5, [1]) === ['do_not_allow'], "A Manager cannot {$cap} an administrator.");
    tbt_scope_assert(tbt_manager_user_meta_cap(['edit_users'], $cap, 5, [7]) === ['do_not_allow'], "A Manager cannot {$cap} an editor.");
    tbt_scope_assert(tbt_manager_user_meta_cap(['edit_users'], $cap, 5, [6]) === ['edit_users'], "A Manager can {$cap} another Manager.");
}
tbt_scope_assert(tbt_manager_user_meta_cap(['edit_users'], 'edit_user', 5, [5]) === ['edit_users'], 'A Manager can edit their own profile.');
tbt_scope_assert(tbt_manager_user_meta_cap(['delete_users'], 'delete_user', 5, [5]) === ['do_not_allow'], 'A Manager cannot delete themselves.');
tbt_scope_assert(array_keys(tbt_manager_editable_roles(['administrator' => [], 'editor' => [], TBT_MANAGER_ROLE => []])) === [TBT_MANAGER_ROLE], 'A Manager can only give the Manager role.');
$GLOBALS['tbt_scope_roles'] = ['administrator'];
tbt_scope_assert(tbt_manager_user_meta_cap(['edit_users'], 'edit_user', 1, [5]) === ['edit_users'], 'Administrators are unaffected.');
tbt_scope_assert(count(tbt_manager_editable_roles(['administrator' => [], TBT_MANAGER_ROLE => []])) === 2, 'Administrators can give any role.');
$GLOBALS['tbt_scope_roles'] = [];

fwrite(STDOUT, "PASS: editing scope assertions\n");
