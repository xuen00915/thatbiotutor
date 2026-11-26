<?php

declare(strict_types=1);

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
function is_admin(): bool
{
    return true;
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
tbt_scope_assert($limited === ['group_tbt_class_year_fields', 'group_tbt_page_testimonials', 'group_tbt_testimonial_fields'], 'Scope "testimonials" registers only the testimonials page, testimonials and class years: ' . implode(',', $limited));
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

fwrite(STDOUT, "PASS: editing scope assertions\n");
