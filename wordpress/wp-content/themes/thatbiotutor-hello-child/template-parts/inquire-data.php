<?php

declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}

// Copy and pictures of the Home / IntensiveFix shared sections are fields on the Home page (inc/content/home.php).
$tbt_home_defaults = tbt_reference_content()['pages']['home']['fields'];
$tbt_home_id = function_exists('get_option') ? (int) get_option('page_on_front', 0) : 0;
$tbt_h = static fn (string $name): string => (string) tbt_home_field($name, $tbt_home_defaults[$name] ?? '');
$tbt_media_image = static function (string $key, string $class = '', string $attributes = '') use ($tbt_home_id): void {
    $field = tbt_home_image_field($key);
    if ($field !== null) {
        $attrs = [];
        if (preg_match_all('/([a-z-]+)="([^"]*)"/', $attributes, $found, PREG_SET_ORDER) > 0) {
            foreach ($found as $attribute) {
                $attrs[$attribute[1]] = $attribute[2];
            }
        }
        tbt_render_content_image($field, $class, $attrs, $tbt_home_id);
        return;
    }
    $media = tbt_media($key);
    if (($media['url'] ?? '') === '') {
        return;
    }
    echo '<img data-media-key="' . esc_attr($key) . '"';
    if ($class !== '') {
        echo ' class="' . esc_attr($class) . '"';
    }
    if ($attributes !== '') {
        echo ' ' . $attributes;
    }
    echo ' src="' . esc_url((string) $media['url']) . '" alt="' . esc_attr((string) ($media['alt'] ?? ''))
        . '" width="' . esc_attr((string) ($media['width'] ?? 0)) . '" height="' . esc_attr((string) ($media['height'] ?? 0)) . '">';
};

$tbt_whatsapp = tbt_whatsapp_url($tbt_h('home_whatsapp_message'));
$tbt_stats = [];
foreach ([1, 2, 3, 4] as $tbt_n) {
    $tbt_display = $tbt_h('home_stat_' . $tbt_n . '_value');
    $tbt_stat = ['display' => $tbt_display, 'target' => preg_match('/\d+(?:\.\d+)?/', $tbt_display, $tbt_number) === 1 ? $tbt_number[0] : $tbt_display, 'label' => $tbt_h('home_stat_' . $tbt_n . '_label')];
    if ($tbt_h('home_stat_' . $tbt_n . '_note') !== '') {
        $tbt_stat['note'] = $tbt_h('home_stat_' . $tbt_n . '_note');
    }
    $tbt_stats[] = $tbt_stat;
}
$tbt_comparisons = [];
foreach (tbt_home_fasttrack_defaults() as $tbt_index => [, , , $tbt_media_key]) {
    $tbt_n = $tbt_index + 1;
    $tbt_comparisons[] = ['title' => $tbt_h('home_fasttrack_' . $tbt_n . '_title'), 'other' => $tbt_h('home_fasttrack_' . $tbt_n . '_other'), 'tbt' => $tbt_h('home_fasttrack_' . $tbt_n . '_text'), 'media' => $tbt_media_key];
}
$tbt_tutors = [];
foreach ([1, 2, 3, 4] as $tbt_n) {
    $tbt_tutors[] = ['media' => ['curated_shared_tutor_keefe', 'curated_shared_tutor_chelsea', 'curated_shared_tutor_jamie', 'curated_shared_tutor_ng'][$tbt_n - 1], 'name' => $tbt_h('home_tutor_' . $tbt_n . '_name'), 'role' => $tbt_h('home_tutor_' . $tbt_n . '_role')];
}
$tbt_faqs = tbt_home_faqs();
