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
    $variant = (string) ($args['variant'] ?? 'cards');
    $fallbackPortraits = is_array($args['fallback_portraits'] ?? null) ? $args['fallback_portraits'] : [];
    $qualifications = is_array($args['qualifications'] ?? null) ? $args['qualifications'] : [];
    $supplementalMedia = is_array($args['supplemental_media'] ?? null) ? $args['supplemental_media'] : [];
    $displayNames = is_array($args['display_names'] ?? null) ? $args['display_names'] : [];
    $profileLinks = is_array($args['profile_links'] ?? null) ? $args['profile_links'] : [];
    $showRoles = !array_key_exists('show_roles', $args) || (bool) $args['show_roles'];
    echo '<div class="tbt-tutors">';
    foreach (tbt_get_tutors($args) as $tutor) {
        echo '<article class="tbt-tutor' . ($variant === 'about' ? ' tbt-tutor--about' : '') . '">';
        if ($variant === 'about') {
            $displayName = (string) ($displayNames[$tutor['name']] ?? $tutor['name']);
            echo '<h3>' . esc_html($displayName) . ($showRoles && $tutor['role'] !== '' ? '<small>' . esc_html($tutor['role']) . '</small>' : '') . '</h3>';
        }
        if ($tutor['portrait']['url'] !== '') {
            echo '<img src="' . esc_url($tutor['portrait']['url']) . '" alt="' . esc_attr($tutor['portrait']['alt'] ?: $tutor['name']) . '">';
        } elseif (isset($fallbackPortraits[$tutor['name']]) && function_exists('tbt_render_media_image')) {
            tbt_render_media_image((string) $fallbackPortraits[$tutor['name']], 'tbt-tutor__portrait', ['loading' => $variant === 'about' ? 'eager' : 'lazy']);
        }
        if ($variant !== 'about') {
            echo '<h3>' . esc_html($tutor['name']) . '</h3>';
            if ($tutor['role'] !== '') {
                echo '<p class="tbt-tutor__role">' . esc_html($tutor['role']) . '</p>';
            }
            if ($tutor['short_bio'] !== '') {
                echo '<p>' . nl2br(esc_html($tutor['short_bio'])) . '</p>';
            }
        }
        if ($variant === 'about' && isset($qualifications[$tutor['name']]) && is_array($qualifications[$tutor['name']])) {
            echo '<ul class="tbt-tutor__qualifications">';
            foreach ($qualifications[$tutor['name']] as $qualification) {
                echo '<li>' . esc_html((string) $qualification) . '</li>';
            }
            echo '</ul>';
        }
        if ($variant === 'about' && isset($profileLinks[$tutor['name']]) && (string) $profileLinks[$tutor['name']] !== '') {
            $profileUrl = (string) $profileLinks[$tutor['name']];
            echo '<p class="tbt-tutor__profile"><a href="' . esc_url($profileUrl) . '" target="_blank" rel="noopener noreferrer">' . esc_html($profileUrl) . '</a></p>';
        }
        if ($variant === 'about' && isset($supplementalMedia[$tutor['name']]) && is_array($supplementalMedia[$tutor['name']]) && function_exists('tbt_render_media_image')) {
            echo '<div class="tbt-tutor__documents">';
            foreach ($supplementalMedia[$tutor['name']] as $mediaKey) {
                tbt_render_media_image((string) $mediaKey, 'tbt-tutor__document', ['loading' => 'eager']);
            }
            echo '</div>';
        }
        echo '</article>';
    }
    echo '</div>';
}

