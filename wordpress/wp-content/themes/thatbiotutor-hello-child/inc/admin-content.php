<?php

declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}

function tbt_register_website_content_screen(): void
{
    if (tbt_current_user_is_site_administrator()) {
        add_menu_page('Website Content', 'Website Content', 'edit_pages', 'tbt-website-content', 'tbt_render_website_content_screen', 'dashicons-edit-page', 4);
    }
}
add_action('admin_menu', 'tbt_register_website_content_screen');

/** Render links to WordPress editors using the same page registry as site setup. */
function tbt_render_website_content_screen(): void
{
    if (!tbt_current_user_is_site_administrator() || !current_user_can('edit_pages')) {
        wp_die('Only administrators can open Website Content.');
    }

    $mainPages = [
        'home' => 'Homepage, logo, contact details, and footer',
        'classes' => 'Class overview and tuition options',
        'sec-3-pure-g3-biology-tuition' => 'Sec 3 programme, schedule, and enrolment',
        'sec-4-pure-g3-biology-tuition' => 'Sec 4 programme, schedule, and enrolment',
        'testimonials' => 'Results page headings and featured video',
        'notes' => 'Free notes, signup form, and download links',
        'about' => 'Mission, teaching approach, and founder message',
        'contact' => 'Contact page copy and location details',
    ];
    $collections = [
        'tbt_testimonial' => 'Student quotes, videos, and where they appear',
        'tbt_class_year' => 'Results posters, tables, and student stories by year',
        'tbt_tutor' => 'Profiles, qualifications, portraits, and display order',
        'tbt_faq' => 'Questions, answers, and their page assignments',
        'tbt_result' => 'Structured result records',
    ];

    $pages = tbt_page_registry();
    $privacyId = (int) get_option('wp_page_for_privacy_policy');
    $privacyPage = $privacyId > 0 ? get_post($privacyId) : null;
    if (!$privacyPage || $privacyPage->post_type !== 'page' || $privacyPage->post_status === 'trash') {
        $privacyPage = get_page_by_path('privacy-policy', OBJECT, 'page');
    }
    ?>
    <div class="wrap tbt-content-hub">
        <h1>Website Content</h1>
        <p>Choose a page or content list, make your changes, then use <strong>Preview Changes</strong> or <strong>Update</strong> in its editor.</p>
        <?php if (!function_exists('acf_add_local_field_group')) : ?>
            <div class="notice notice-warning inline"><p>Activate Advanced Custom Fields to show the ThatBioTutor editing panels.</p></div>
        <?php endif; ?>

        <section aria-labelledby="tbt-main-pages">
            <h2 id="tbt-main-pages">Pages</h2>
            <table class="widefat striped" aria-label="Main pages">
                <thead><tr><th scope="col">Page</th><th scope="col">What you can edit</th></tr></thead>
                <tbody>
                    <?php foreach ($mainPages as $key => $description) :
                        $page = $pages[$key];
                        tbt_render_website_content_page_row(get_page_by_path($page['slug'], OBJECT, 'page'), $page['title'], $description);
                    endforeach;
                    tbt_render_website_content_page_row($privacyPage, 'Privacy Policy', 'Website privacy information');
                    ?>
                </tbody>
            </table>
            <details class="tbt-content-hub__additional">
                <summary>Additional Pages</summary>
                <table class="widefat striped" aria-label="Additional pages">
                    <thead><tr><th scope="col">Page</th><th scope="col">What you can edit</th></tr></thead>
                    <tbody>
                        <?php foreach (array_diff_key($pages, $mainPages) as $page) :
                            tbt_render_website_content_page_row(get_page_by_path($page['slug'], OBJECT, 'page'), $page['title'], 'Page content and SEO');
                        endforeach; ?>
                    </tbody>
                </table>
            </details>
        </section>

        <section aria-labelledby="tbt-content-lists">
            <h2 id="tbt-content-lists">Content lists</h2>
            <table class="widefat striped" aria-labelledby="tbt-content-lists">
                <thead><tr><th scope="col">List</th><th scope="col">What you can edit</th></tr></thead>
                <tbody>
                    <?php foreach ($collections as $type => $description) :
                        $postType = get_post_type_object($type);
                        if (!$postType || !current_user_can($postType->cap->edit_posts)) {
                            continue;
                        }
                        ?>
                        <tr>
                            <th scope="row"><a href="<?php echo esc_url(admin_url('edit.php?post_type=' . $type)); ?>"><?php echo esc_html($postType->labels->name); ?></a></th>
                            <td><?php echo esc_html($description); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </section>
    </div>
    <?php
}

/** Show the page's actual state, including an unpublished privacy policy. */
function tbt_render_website_content_page_row(?WP_Post $post, string $title, string $description): void
{
    $editUrl = $post ? get_edit_post_link($post, 'raw') : null;
    $status = $post && $post->post_status !== 'publish' ? get_post_status_object($post->post_status) : null;
    ?>
    <tr>
        <th scope="row">
            <?php if ($editUrl) : ?>
                <a href="<?php echo esc_url($editUrl); ?>"><?php echo esc_html($title); ?></a>
            <?php else : ?>
                <?php echo esc_html($title); ?>
            <?php endif; ?>
            <?php if ($status) : ?>
                <span class="tbt-content-hub__status"><?php echo esc_html($status->label); ?></span>
            <?php elseif (!$post) : ?>
                <span class="tbt-content-hub__status">Missing</span>
            <?php endif; ?>
        </th>
        <td><?php echo esc_html($description); ?></td>
    </tr>
    <?php
}
