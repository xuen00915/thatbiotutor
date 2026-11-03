<?php

declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}

define('TBT_THEME_VERSION', '0.1.0');
define('TBT_THEME_DIR', get_stylesheet_directory());
define('TBT_THEME_URI', get_stylesheet_directory_uri());

/**
 * Return the stable list of focused theme modules in dependency order.
 *
 * @return list<string>
 */
function tbt_theme_includes(): array
{
    return [
        'inc/reference-content.php',
        'inc/theme-helpers.php',
        'inc/acf-fields.php',
        'inc/content-types.php',
        'inc/testimonials.php',
        'inc/tutors.php',
        'inc/faqs.php',
        'inc/results.php',
        'inc/site-setup.php',
        'inc/site-meta.php',
        'inc/security.php',
    ];
}

foreach (tbt_theme_includes() as $tbt_include) {
    $tbt_include_path = TBT_THEME_DIR . '/' . $tbt_include;
    if (is_file($tbt_include_path)) {
        require_once $tbt_include_path;
    }
}
unset($tbt_include, $tbt_include_path);

/** Register the theme capabilities owned by the child theme. */
function tbt_theme_setup(): void
{
    add_theme_support('title-tag');
    add_theme_support('post-thumbnails');
    add_theme_support('custom-logo', [
        'height' => 160,
        'width' => 240,
        'flex-height' => true,
        'flex-width' => true,
    ]);
    add_theme_support('html5', [
        'search-form',
        'comment-form',
        'comment-list',
        'gallery',
        'caption',
        'style',
        'script',
    ]);

    register_nav_menus([
        'primary' => __('Primary navigation', 'thatbiotutor'),
    ]);
}
add_action('after_setup_theme', 'tbt_theme_setup');

