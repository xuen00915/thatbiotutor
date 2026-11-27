<?php

declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}

/*
 * A friendlier edit screen for the protected pages.
 *
 * These pages are built from fields, not from the page body, so the default block editor (an empty
 * "Type / to choose a block" canvas with the real fields hidden in a collapsed strip) would look like an
 * empty page. Instead, protected pages open on the classic screen with the field panel front and centre,
 * the unused content box hidden and a short guide at the top. Pages the client creates themselves keep the
 * normal editor.
 */

/** Whether a post is one of the protected ThatBioTutor pages. */
function tbt_is_protected_page(mixed $post): bool
{
    $id = is_object($post) ? (int) ($post->ID ?? 0) : (int) $post;
    if ($id <= 0 || !function_exists('get_post_type') || get_post_type($id) !== 'page') {
        return false;
    }
    if ((string) get_post_meta($id, '_tbt_reference_page', true) === '1') {
        return true;
    }
    $template = (string) get_post_meta($id, '_wp_page_template', true);
    foreach (tbt_page_registry() as $page) {
        if ($template === $page['template']) {
            return true;
        }
    }
    return false;
}

/** Protected pages use the classic screen so their field panel is not hidden under the block canvas. */
function tbt_protected_pages_use_classic_editor(bool $useBlockEditor, mixed $post): bool
{
    return tbt_is_protected_page($post) ? false : $useBlockEditor;
}

/** Hide the page-body editor on protected pages and add a short guide. */
function tbt_protected_page_screen(): void
{
    $postId = (int) ($_GET['post'] ?? 0);
    if (!tbt_is_protected_page($postId)) {
        return;
    }
    remove_post_type_support('page', 'editor');
    if (function_exists('tbt_editing_is_limited') && tbt_editing_is_limited()) {
        remove_post_type_support('page', 'title');
        add_filter('admin_body_class', static fn(string $classes): string => $classes . ' tbt-notes-form-editor');
    }
    add_action('edit_form_after_title', 'tbt_protected_page_guide');
    add_action('add_meta_boxes', 'tbt_remove_protected_page_boxes', 99);
}

/** Layout-level boxes (template, parent, featured image, slug) are not for clients on protected pages. */
function tbt_remove_protected_page_boxes(): void
{
    remove_meta_box('pageparentdiv', 'page', 'side');
    remove_meta_box('postimagediv', 'page', 'side');
    remove_meta_box('slugdiv', 'page', 'normal');
}

/** The short guide shown above the field tabs. */
function tbt_protected_page_guide(): void
{
    ?>
    <div class="notice notice-info inline tbt-page-guide" style="margin:12px 0;padding:12px 16px;">
        <p style="margin:0 0 6px;"><strong>How to edit this page</strong></p>
        <?php if (function_exists('tbt_editing_is_limited') && tbt_editing_is_limited()) : ?>
            <p style="margin:0;">Edit the signup form link, photo and text in the <em>Email form</em> section below, then press <strong>Update</strong>.</p>
        <?php else : ?>
            <p style="margin:0;">Use the tabs in the <em>ThatBioTutor</em> panel below: each tab is one section of the page. Change the words, pictures and links, then press <strong>Update</strong>. Pictures you leave empty keep their current image. The page layout, colours and menu are managed by the theme and cannot be broken from here.</p>
        <?php endif; ?>
    </div>
    <?php
}

/**
 * WordPress re-validates a page's template whenever it is saved and resets one it does not list (front-page.php is
 * not a selectable template) to "default". Put each protected page's approved template back after every save.
 */
function tbt_keep_protected_page_template(int $postId): void
{
    if ((string) get_post_meta($postId, '_tbt_reference_page', true) !== '1') {
        return;
    }
    $slug = (string) get_post_field('post_name', $postId);
    foreach (tbt_page_registry() as $page) {
        if ($page['slug'] === $slug && (string) get_post_meta($postId, '_wp_page_template', true) !== $page['template']) {
            update_post_meta($postId, '_wp_page_template', $page['template']);
        }
    }
}

/** Styles for the ThatBioTutor edit screens. */
function tbt_admin_editor_styles(): void
{
    $stylesheet = '/assets/css/admin-editor.css';
    $path = TBT_THEME_DIR . $stylesheet;
    wp_enqueue_style('tbt-admin-editor', TBT_THEME_URI . $stylesheet, [], is_file($path) ? (string) filemtime($path) : TBT_THEME_VERSION);
}

/** The Hostinger AI Assistant "External Featured Image" box does nothing on ThatBioTutor pages and records. */
function tbt_remove_foreign_meta_boxes(): void
{
    $screens = array_merge(['page'], function_exists('tbt_structured_post_types') ? tbt_structured_post_types() : []);
    foreach ($screens as $screen) {
        remove_meta_box('external_featured_image', $screen, 'side');
    }
}

if (function_exists('add_filter') && function_exists('add_action')) {
    add_action('add_meta_boxes', 'tbt_remove_foreign_meta_boxes', 99);
    add_action('admin_enqueue_scripts', 'tbt_admin_editor_styles');
    add_filter('use_block_editor_for_post', 'tbt_protected_pages_use_classic_editor', 10, 2);
    add_action('load-post.php', 'tbt_protected_page_screen');
    add_action('wp_after_insert_post', 'tbt_keep_protected_page_template', 99);
}
