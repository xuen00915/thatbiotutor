<?php

declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}

/** Build one ACF Free field definition. */
function tbt_acf_field(
    string $key,
    string $label,
    string $name,
    string $type = 'text',
    array $settings = []
): array {
    return array_merge([
        'key' => $key,
        'label' => $label,
        'name' => $name,
        'type' => $type,
        'instructions' => '',
        'required' => 0,
        'conditional_logic' => 0,
        'wrapper' => ['width' => '', 'class' => '', 'id' => ''],
    ], $settings);
}

/** Build a visual section tab that stores no content. */
function tbt_acf_tab(string $key, string $label): array
{
    return tbt_acf_field($key, $label, '', 'tab', ['placement' => 'top', 'endpoint' => 0]);
}

/** Build a multiline plain-text field. */
function tbt_acf_textarea(string $key, string $label, string $name, array $settings = []): array
{
    return tbt_acf_field($key, $label, $name, 'textarea', array_merge([
        'rows' => 4,
        'new_lines' => '',
    ], $settings));
}

/** Build a validated URL field. */
function tbt_acf_url(string $key, string $label, string $name, array $settings = []): array
{
    return tbt_acf_field($key, $label, $name, 'url', $settings);
}

/** Build a formatted-text field with the client-safe toolbar (bold, italic, underline, highlight, links, lists). */
function tbt_acf_wysiwyg(string $key, string $label, string $name, array $settings = []): array
{
    return tbt_acf_field($key, $label, $name, 'wysiwyg', array_merge([
        'tabs' => 'visual',
        'toolbar' => 'thatbiotutor', // ACF slugifies the toolbar name registered in tbt_editor_toolbars()
        'media_upload' => 0,
        'delay' => 0,
    ], $settings));
}

/** Build the ACF fields for one declared page-content section: a tab, an optional note, then its fields. */
function tbt_acf_section_fields(string $prefix, array $section): array
{
    $fields = [tbt_acf_tab('field_tbt_' . $prefix . '_tab_' . $section['key'], (string) $section['title'])];
    if (($section['intro'] ?? '') !== '') {
        $fields[] = tbt_acf_field('field_tbt_' . $prefix . '_note_' . $section['key'], 'About this section', '', 'message', [
            'message' => (string) $section['intro'],
            'new_lines' => 'wpautop',
            'esc_html' => 0,
        ]);
    }
    foreach ($section['fields'] as $declared) {
        $name = (string) $declared['name'];
        $key = tbt_reference_field_key((string) ($section['page'] ?? ''), $name);
        $settings = ['instructions' => (string) ($declared['help'] ?? '')];
        $label = (string) $declared['label'];
        switch ($declared['type']) {
            case 'wysiwyg':
                if (!empty($declared['inline'])) {
                    $settings['wrapper'] = ['width' => '', 'class' => 'tbt-inline-rich', 'id' => ''];
                    $settings['instructions'] = trim($settings['instructions'] . ' One line; Highlight marks words in green.');
                }
                $fields[] = tbt_acf_wysiwyg($key, $label, $name, $settings);
                break;
            case 'textarea':
                $fields[] = tbt_acf_textarea($key, $label, $name, $settings);
                break;
            case 'url':
                $fields[] = tbt_acf_url($key, $label, $name, $settings);
                break;
            case 'image':
                $settings['instructions'] = trim(($settings['instructions'] !== '' ? $settings['instructions'] . ' ' : '') . 'Leave empty to keep the current picture. Upload a new image to replace it.');
                $fields[] = tbt_acf_image($key, $label, $name, $settings);
                break;
            default:
                $fields[] = tbt_acf_field($key, $label, $name, 'text', $settings);
        }
    }
    return $fields;
}

/** Build an image field that returns the full ACF image array. */
function tbt_acf_image(string $key, string $label, string $name, array $settings = []): array
{
    return tbt_acf_field($key, $label, $name, 'image', array_merge([
        'return_format' => 'array',
        'preview_size' => 'medium',
        'library' => 'all',
    ], $settings));
}

/** Return the protected ACF page groups before registering them. */
function tbt_acf_page_groups(): array
{
    $reference = tbt_reference_content();
    $seoDefaults = function_exists('tbt_page_metadata') ? tbt_page_metadata() : [];
    $groups = [];

    foreach (tbt_page_registry() as $pageKey => $page) {
        $prefix = str_replace('-', '_', $pageKey);
        $fields = [
            tbt_acf_field(
                'field_tbt_' . $prefix . '_instructions',
                'Editing instructions',
                '',
                'message',
                [
                    'message' => 'Edit wording and approved media here. Page layout, section order, navigation, and responsive behavior are protected by the theme.',
                    'new_lines' => 'wpautop',
                    'esc_html' => 0,
                ]
            ),
            tbt_acf_tab('field_tbt_' . $prefix . '_tab_content', 'Page content'),
        ];

        $managed = tbt_page_content_fields($pageKey);
        foreach (($reference['pages'][$pageKey]['fields'] ?? []) as $name => $value) {
            if (isset($managed[(string) $name])) {
                continue;
            }
            $label = ucwords(str_replace('_', ' ', (string) preg_replace('/^' . preg_quote($prefix, '/') . '_?/', '', (string) $name)));
            $constructor = is_string($value) && (str_contains($value, "\n") || strlen($value) > 110)
                ? 'tbt_acf_textarea'
                : null;
            $key = tbt_reference_field_key($pageKey, (string) $name);
            $fields[] = $constructor !== null
                ? $constructor($key, $label, (string) $name)
                : tbt_acf_field($key, $label, (string) $name);
        }

        foreach (tbt_page_content_sections($pageKey) as $section) {
            $section['page'] = $pageKey;
            foreach (tbt_acf_section_fields($prefix, $section) as $sectionField) {
                $fields[] = $sectionField;
            }
        }

        if ($pageKey === 'home') {
            $fields[] = tbt_acf_tab('field_tbt_home_tab_shared', 'Shared site details');
            $fields[] = tbt_acf_image('field_tbt_home_shared_logo', 'Site logo', 'home_shared_logo');
            $fields[] = tbt_acf_field('field_tbt_home_shared_whatsapp_number', 'WhatsApp number', 'home_shared_whatsapp_number');
            $fields[] = tbt_acf_textarea('field_tbt_home_shared_whatsapp_message', 'Default WhatsApp message', 'home_shared_whatsapp_message');
            $fields[] = tbt_acf_field('field_tbt_home_shared_phone', 'Phone number', 'home_shared_phone');
            $fields[] = tbt_acf_textarea('field_tbt_home_shared_address', 'Address', 'home_shared_address');
            $fields[] = tbt_acf_url('field_tbt_home_shared_map_url', 'Map link', 'home_shared_map_url');
            $fields[] = tbt_acf_textarea('field_tbt_home_shared_opening_hours', 'Opening hours', 'home_shared_opening_hours');
            $fields[] = tbt_acf_textarea('field_tbt_home_shared_footer_description', 'Footer description', 'home_shared_footer_description');
            $fields[] = tbt_acf_field('field_tbt_home_shared_copyright', 'Copyright line', 'home_shared_copyright');
        }

        if ($pageKey === 'notes') {
            $fields[] = tbt_acf_tab('field_tbt_notes_tab_slider', 'Notes slider');
            foreach (range(1, 3) as $index) {
                $name = sprintf('notes_slider_image_%02d', $index);
                $fields[] = tbt_acf_image('field_tbt_' . $name, 'Notes slide ' . $index, $name);
            }
        }

        if ($pageKey === 'celldivision') {
            $fields[] = tbt_acf_tab('field_tbt_celldivision_tab_slider', 'Crash-course slider');
            foreach (range(1, 5) as $index) {
                $name = sprintf('celldivision_slider_image_%02d', $index);
                $fields[] = tbt_acf_image('field_tbt_' . $name, 'Crash-course slide ' . $index, $name);
            }
        }

        if ($pageKey === 'intensivefix') {
            $fields[] = tbt_acf_tab('field_tbt_intensivefix_tab_programme_media', 'Programme media');
            foreach (range(1, 10) as $index) {
                $name = sprintf('intensivefix_program_image_%02d', $index);
                $fields[] = tbt_acf_image('field_tbt_' . $name, 'Programme graphic ' . $index, $name, [
                    'instructions' => 'Keep supplied artwork in its approved numeric order. Use contain fit in the protected template.',
                ]);
            }
            $fields[] = tbt_acf_image(
                'field_tbt_intensivefix_student_reviews_image',
                'Student reviews artwork',
                'intensivefix_student_reviews_image'
            );
        }

        $fields[] = tbt_acf_tab('field_tbt_' . $prefix . '_tab_seo', 'SEO');
        $fields[] = tbt_acf_field('field_tbt_' . $prefix . '_seo_title', 'SEO title', 'tbt_' . $prefix . '_seo_title', 'text', [
            'placeholder' => $seoDefaults[$pageKey]['title'] ?? '',
            'instructions' => 'Optional override of the prepared page title. Leave blank to use the default. Include ThatBioTutor once if you want the brand in the title.',
        ]);
        $fields[] = tbt_acf_textarea('field_tbt_' . $prefix . '_seo_description', 'SEO description', 'tbt_' . $prefix . '_seo_description', [
            'rows' => 3,
            'placeholder' => $seoDefaults[$pageKey]['description'] ?? '',
            'instructions' => 'Optional page-specific search description. Leave blank to use the prepared default. This also updates the social-share description.',
        ]);
        $fields[] = tbt_acf_field('field_tbt_' . $prefix . '_seo_keywords', 'SEO keywords', 'tbt_' . $prefix . '_seo_keywords', 'text', [
            'placeholder' => $seoDefaults[$pageKey]['keywords'] ?? '',
            'instructions' => 'Optional comma-separated search phrases for this page, e.g. "Sec 3 Biology tuition, Pure Biology notes". Leave blank to use the prepared default.',
        ]);

        $groups[] = [
            'key' => 'group_tbt_page_' . $prefix,
            'title' => 'ThatBioTutor — ' . $page['title'],
            'fields' => $fields,
            // The front page also matches by page type: WordPress resets front-page.php (not a selectable template) to
            // "default" whenever the page is saved, which would otherwise hide this panel after the first Update.
            'location' => array_merge([[[
                'param' => 'page_template',
                'operator' => '==',
                'value' => $page['template'],
            ]]], $pageKey === 'home' ? [[[
                'param' => 'page_type',
                'operator' => '==',
                'value' => 'front_page',
            ]]] : []),
            'menu_order' => 0,
            'position' => 'normal',
            'style' => 'default',
            'label_placement' => 'top',
            'instruction_placement' => 'label',
            'hide_on_screen' => '',
            'active' => true,
            'description' => '',
            'show_in_rest' => 0,
        ];
    }

    return $groups;
}

/** Register all ACF Free page groups when ACF is available. */
function tbt_register_acf_fields(): void
{
    if (!function_exists('acf_add_local_field_group')) {
        return;
    }

    $scoped = static fn(array $group): ?array => function_exists('tbt_acf_group_for_scope') ? tbt_acf_group_for_scope($group) : $group;

    foreach (tbt_acf_page_groups() as $group) {
        if (($group = $scoped($group)) !== null) {
            acf_add_local_field_group($group);
        }
    }

    if (function_exists('tbt_acf_content_groups')) {
        foreach (tbt_acf_content_groups() as $group) {
            if (($group = $scoped($group)) !== null) {
                acf_add_local_field_group($group);
            }
        }
    }
}

add_action('acf/init', 'tbt_register_acf_fields');

