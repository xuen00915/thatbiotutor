<?php
/** Dashboard adapter for the same protected setup used by WP-CLI. */
declare(strict_types=1);
if (!defined('ABSPATH')) { exit; }

function tbt_admin_setup_submit(array $request): array|WP_Error|null
{
    if (!isset($request['tbt_setup_nonce'])) { return null; }
    if (!current_user_can('manage_options')) {
        return new WP_Error('tbt_setup_forbidden', 'Only administrators can set up this site.');
    }
    if (!is_string($request['tbt_setup_nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($request['tbt_setup_nonce'])), 'tbt_setup_site')) {
        return new WP_Error('tbt_setup_nonce', 'This setup request expired. Reload the page and try again.');
    }
    if (!function_exists('acf_add_local_field_group')) {
        return new WP_Error('tbt_setup_acf', 'Install and activate Advanced Custom Fields before running setup.');
    }
    $result = tbt_setup_site(false);
    if (!is_wp_error($result)) {
        if (!get_option('permalink_structure')) {
            update_option('permalink_structure', '/%postname%/');
        }
        flush_rewrite_rules(false);
    }
    return $result;
}

function tbt_register_setup_screen(): void
{
    add_theme_page('ThatBioTutor Setup', 'ThatBioTutor Setup', 'manage_options', 'tbt-setup', 'tbt_render_setup_screen');
}

function tbt_render_setup_screen(): void
{
    if (!current_user_can('manage_options')) { wp_die('Only administrators can access site setup.'); }
    $result = ($_SERVER['REQUEST_METHOD'] ?? '') === 'POST' ? tbt_admin_setup_submit($_POST) : null;
    ?>
    <div class="wrap">
        <h1>ThatBioTutor Setup</h1>
        <p>Install and activate the unmodified Hello Elementor parent theme and Advanced Custom Fields Free, then use this screen to create the ThatBioTutor pages and reference content.</p>
        <p>Setup assigns Home as the front page, creates missing content and preserves existing client edits. Existing pages with conflicting slugs are reported instead of replaced. On an existing website, take a backup before setup.</p>
        <?php if (is_wp_error($result)) : ?>
            <div class="notice notice-error"><p><?php echo esc_html($result->get_error_message()); ?></p></div>
        <?php elseif (is_array($result)) : ?>
            <div class="notice notice-success"><p><?php echo esc_html(sprintf('Setup complete: %d pages, %d seeded fields and %d preserved fields. Review the site and save Settings → Permalinks if your host requires rewrite rules.', count($result['pages']), $result['seeded'], $result['preserved'])); ?></p></div>
        <?php endif; ?>
        <form method="post" action="<?php echo esc_url(admin_url('themes.php?page=tbt-setup')); ?>">
            <?php wp_nonce_field('tbt_setup_site', 'tbt_setup_nonce'); ?>
            <?php submit_button('Set up ThatBioTutor pages and content'); ?>
        </form>
        <h2>Before publishing</h2>
        <ul>
            <li>Check Site Title, timezone, HTTPS and permalinks under Settings.</li>
            <li>Connect the Free Bio Notes form using Contact Form 7 and verify email delivery.</li>
            <li>Review all pages, mobile navigation, media and contact links on this host.</li>
            <li>Keep staging hidden from search engines; enable indexing only on the approved live site.</li>
        </ul>
        <p>Plugins are installed separately through Plugins → Add New. This theme does not bundle third-party plugins or change your login URL, firewall rules, users or hosting settings.</p>
    </div>
    <?php
}
add_action('admin_menu', 'tbt_register_setup_screen');
