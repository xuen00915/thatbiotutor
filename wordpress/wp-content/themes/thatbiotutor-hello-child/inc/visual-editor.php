<?php

declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}

function tbt_visual_editor_available(int $postId): bool
{
    return $postId > 0 && tbt_current_user_is_site_administrator()
        && function_exists('acf_add_local_field_group')
        && tbt_is_protected_page($postId) && current_user_can('edit_post', $postId);
}

/** Get the registered layout even when WordPress reports the front-page template as default. */
function tbt_visual_editor_page_key(WP_Post $post): string
{
    if ((int) get_option('page_on_front') === $post->ID) {
        return 'home';
    }
    $template = get_page_template_slug($post);
    foreach (tbt_page_registry() as $key => $page) {
        if ($page['template'] === $template || $page['slug'] === $post->post_name) {
            return $key;
        }
    }
    return '';
}

function tbt_visual_editor_url(int $postId): string
{
    return add_query_arg('tbt_visual', '1', get_edit_post_link($postId, 'raw'));
}

/** Match public section names that differ from the section names in ACF. */
function tbt_visual_editor_section_aliases(string $pageKey): array
{
    return match ($pageKey) {
        'home' => ['quality' => 'tutors', 'practical' => 'tutors'],
        'notes' => ['access-intro' => 'form', 'reviews-top' => 'reviews', 'reviews-bottom' => 'reviews'],
        'sec-3-pure-g3-biology-tuition', 'sec-4-pure-g3-biology-tuition' => ['stats' => 'numbers'],
        'contact' => ['content' => 'message'],
        default => [],
    };
}

/** Add the visual entry point alongside Edit on the native Pages list. */
function tbt_visual_editor_row_action(array $actions, WP_Post $post): array
{
    if (isset($actions['edit']) && tbt_visual_editor_available($post->ID)) {
        $actions = ['tbt_visual' => '<a href="' . esc_url(tbt_visual_editor_url($post->ID)) . '">Visual editor</a>'] + $actions;
    }
    return $actions;
}

function tbt_visual_editor_admin_bar(WP_Admin_Bar $bar): void
{
    $postId = is_singular('page') ? get_queried_object_id() : 0;
    if (!is_admin() && tbt_visual_editor_available($postId)) {
        $bar->add_node([
            'id' => 'tbt-visual-editor',
            'title' => 'Visual editor',
            'href' => tbt_visual_editor_url($postId),
        ]);
    }
}

function tbt_visual_editor_assets(string $screen): void
{
    if ($screen !== 'post.php' || ($_GET['tbt_visual'] ?? '') !== '1'
        || !tbt_visual_editor_available((int) ($_GET['post'] ?? 0))) {
        return;
    }

    add_filter('admin_body_class', static fn(string $classes): string => $classes . ' tbt-visual-editor');
    $style = '/assets/css/visual-editor.css';
    $script = '/assets/js/visual-editor.js';
    wp_enqueue_style('tbt-visual-editor', TBT_THEME_URI . $style, [], (string) filemtime(TBT_THEME_DIR . $style));
    wp_enqueue_script('tbt-visual-editor', TBT_THEME_URI . $script, ['tbt-admin-section-picker'], (string) filemtime(TBT_THEME_DIR . $script), true);
}

function tbt_visual_editor_render(WP_Post $post): void
{
    if (!tbt_visual_editor_available($post->ID)) {
        return;
    }
    if (($_GET['tbt_visual'] ?? '') !== '1') {
        printf('<p><a class="button button-primary" href="%s">Open visual editor</a></p>', esc_url(tbt_visual_editor_url($post->ID)));
        return;
    }

    $key = tbt_visual_editor_page_key($post);
    $url = get_preview_post_link($post);
    ?>
    <input type="hidden" name="tbt_visual" value="1">
    <input type="hidden" name="tbt_visual_section" value="">
    <section class="tbt-visual-preview" aria-labelledby="tbt-visual-preview-heading"
        data-page="<?php echo esc_attr($key); ?>"
        data-aliases="<?php echo esc_attr((string) wp_json_encode(tbt_visual_editor_section_aliases($key))); ?>">
        <div class="tbt-visual-preview__heading">
            <h2 id="tbt-visual-preview-heading">Page preview</h2>
            <a href="<?php echo esc_url(get_edit_post_link($post->ID, 'raw')); ?>">Classic editor</a>
        </div>
        <p class="tbt-visual-preview__help" role="status">Loading the page preview…</p>
        <iframe class="tbt-visual-preview__frame" src="<?php echo esc_url($url); ?>" title="Page preview"
            sandbox="allow-same-origin allow-scripts"></iframe>
    </section>
    <?php
}

/** Preserve visual mode and the selected section when WordPress finishes saving. */
function tbt_visual_editor_save_redirect(string $url, int $postId): string
{
    if (($_POST['tbt_visual'] ?? '') !== '1' || !tbt_visual_editor_available($postId)) {
        return $url;
    }

    $section = $_POST['tbt_visual_section'] ?? '';
    return add_query_arg([
        'tbt_visual' => '1',
        'tbt_visual_section' => is_string($section) ? sanitize_key($section) : '',
    ], $url);
}

if (function_exists('add_filter')) {
    add_filter('page_row_actions', 'tbt_visual_editor_row_action', 20, 2);
    add_action('admin_bar_menu', 'tbt_visual_editor_admin_bar', 90);
    add_action('admin_enqueue_scripts', 'tbt_visual_editor_assets', 20);
    add_action('edit_form_after_title', 'tbt_visual_editor_render', 20);
    add_filter('redirect_post_location', 'tbt_visual_editor_save_redirect', 10, 2);
}
