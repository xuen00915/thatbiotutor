<?php

declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}

/** Return normalized, published tutor records. */
function tbt_get_tutors(array $args = []): array
{
    $records = [];
    foreach (tbt_get_structured_posts('tbt_tutor', $args) as $post) {
        $portrait = function_exists('tbt_normalize_image')
            ? tbt_normalize_image(tbt_structured_meta((int) $post->ID, 'tbt_tutor_portrait', ''))
            : null;
        $records[] = [
            'id' => (int) $post->ID,
            'name' => (string) $post->post_title,
            'role' => (string) tbt_structured_meta((int) $post->ID, 'tbt_tutor_role', ''),
            'short_bio' => (string) tbt_structured_meta((int) $post->ID, 'tbt_tutor_short_bio', ''),
            'long_bio' => (string) tbt_structured_meta((int) $post->ID, 'tbt_tutor_long_bio', ''),
            'sections' => (string) tbt_structured_meta((int) $post->ID, 'tbt_tutor_sections', ''),
            'portrait' => $portrait ?? ['url' => '', 'alt' => '', 'width' => 0, 'height' => 0],
        ];
    }
    return $records;
}

/** Render reusable tutor cards. */
function tbt_render_tutors(array $args = []): void
{
    echo '<div class="tbt-tutors">';
    foreach (tbt_get_tutors($args) as $tutor) {
        echo '<article class="tbt-tutor">';
        if ($tutor['portrait']['url'] !== '') {
            echo '<img src="' . esc_url($tutor['portrait']['url']) . '" alt="' . esc_attr($tutor['portrait']['alt'] ?: $tutor['name']) . '">';
        }
        echo '<h3>' . esc_html($tutor['name']) . '</h3>';
        if ($tutor['role'] !== '') {
            echo '<p class="tbt-tutor__role">' . esc_html($tutor['role']) . '</p>';
        }
        if ($tutor['short_bio'] !== '') {
            echo '<p>' . nl2br(esc_html($tutor['short_bio'])) . '</p>';
        }
        echo '</article>';
    }
    echo '</div>';
}

