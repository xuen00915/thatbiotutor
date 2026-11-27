<?php

declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}

/** The main Pages view follows the site's navigation labels. */
function tbt_admin_main_page_labels(): array
{
    static $labels;
    if (isset($labels)) {
        return $labels;
    }

    $pages = tbt_page_registry();
    $navigation = [];
    foreach (tbt_navigation_items() as $item) {
        $navigation[$item['path']] = $item['label'];
        foreach ($item['children'] ?? [] as $child) {
            $navigation[$child['path']] = $child['label'];
        }
    }

    $labels = [];
    foreach (['home', 'classes', 'sec-3-pure-g3-biology-tuition', 'sec-4-pure-g3-biology-tuition',
        'testimonials', 'notes', 'about', 'contact'] as $key) {
        $definition = $pages[$key];
        $page = get_page_by_path($definition['slug'], OBJECT, 'page');
        if ($page && $page->post_status !== 'trash') {
            $labels[$page->ID] = $navigation[$definition['canonical']] ?? $definition['title'];
        }
    }

    $privacyId = (int) get_option('wp_page_for_privacy_policy');
    $privacy = $privacyId > 0 ? get_post($privacyId) : null;
    if (!$privacy || $privacy->post_type !== 'page' || $privacy->post_status === 'trash') {
        $privacy = get_page_by_path('privacy-policy', OBJECT, 'page');
    }
    if ($privacy && $privacy->post_status !== 'trash') {
        $labels[$privacy->ID] = 'Privacy Policy';
    }
    return $labels;
}

/** Search and the core status filters can find pages across the site. */
function tbt_admin_page_list_view(): string
{
    $view = $_GET['tbt_page_list'] ?? '';
    if (in_array($view, ['additional', 'all'], true)) {
        return $view;
    }
    return empty($_GET['s']) && empty($_GET['post_status']) && empty($_GET['author']) ? 'main' : 'all';
}

function tbt_filter_admin_pages_list(WP_Query $query): void
{
    if (!is_admin() || ($GLOBALS['pagenow'] ?? '') !== 'edit.php' || !$query->is_main_query()
        || $query->get('post_type') !== 'page' || !tbt_current_user_is_site_administrator()) {
        return;
    }

    $view = tbt_admin_page_list_view();
    if ($view === 'all') {
        return;
    }

    $mainIds = array_keys(tbt_admin_main_page_labels());
    if ($view === 'additional') {
        $query->set('post__not_in', array_merge((array) $query->get('post__not_in'), $mainIds));
        return;
    }

    $included = (array) $query->get('post__in');
    if ($included !== []) {
        $mainIds = array_intersect($mainIds, array_map('intval', $included));
    }
    $mainIds = array_diff($mainIds, (array) $query->get('post__not_in'));
    $query->set('post__in', array_values($mainIds) ?: [0]);
    if (!isset($_GET['orderby'])) {
        $query->set('orderby', 'post__in');
    }
}

/** Match the sidebar label to the view that opens by default. */
function tbt_admin_main_pages_menu_label(): void
{
    if (!tbt_current_user_is_site_administrator()) {
        return;
    }

    global $submenu;
    $list = 'edit.php?post_type=page';
    foreach ($submenu[$list] ?? [] as $index => $item) {
        if (($item[2] ?? '') === $list) {
            $submenu[$list][$index][0] = 'Main Pages';
            return;
        }
    }
}

function tbt_admin_page_list_views(array $views): array
{
    if (!tbt_current_user_is_site_administrator()) {
        return $views;
    }

    $active = tbt_admin_page_list_view();
    if ($active === 'all' && ($_GET['tbt_page_list'] ?? '') !== 'all') {
        $active = '';
    }
    $tabs = [];
    foreach (['main' => 'Main Pages', 'additional' => 'Additional Pages', 'all' => 'All Pages'] as $view => $label) {
        $url = admin_url('edit.php?post_type=page' . ($view === 'main' ? '' : '&tbt_page_list=' . $view));
        $current = $active === $view ? ' class="current" aria-current="page"' : '';
        $tabs['tbt_' . $view] = '<a href="' . esc_url($url) . '"' . $current . '>' . esc_html($label) . '</a>';
    }
    unset($views['all']);
    return $tabs + $views;
}

/** Use short navigation names on this list without changing stored page titles. */
function tbt_admin_page_list_title(string $title, int $postId): string
{
    if (!is_admin() || !function_exists('get_current_screen') || (get_current_screen()->id ?? '') !== 'edit-page'
        || !tbt_current_user_is_site_administrator()) {
        return $title;
    }
    return tbt_admin_main_page_labels()[$postId] ?? $title;
}

if (function_exists('add_filter')) {
    add_action('admin_menu', 'tbt_admin_main_pages_menu_label');
    add_action('pre_get_posts', 'tbt_filter_admin_pages_list');
    add_filter('views_edit-page', 'tbt_admin_page_list_views');
    add_filter('the_title', 'tbt_admin_page_list_title', 10, 2);
}
