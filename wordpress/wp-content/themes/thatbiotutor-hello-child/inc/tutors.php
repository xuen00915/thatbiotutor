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
        $documentKeys = $variant === 'about' && isset($supplementalMedia[$tutor['name']]) && is_array($supplementalMedia[$tutor['name']])
            ? array_values(array_filter($supplementalMedia[$tutor['name']], 'is_string'))
            : [];
        $hasDocuments = $documentKeys !== [] && function_exists('tbt_render_media_image');
        echo '<article class="tbt-tutor' . ($variant === 'about' ? ' tbt-tutor--about' : '') . ($hasDocuments ? ' tbt-tutor--has-documents' : '') . '">';
        if ($variant === 'about') {
            $displayName = (string) ($displayNames[$tutor['name']] ?? $tutor['name']);
            echo '<div class="tbt-tutor__visual">';
            if ($tutor['portrait']['url'] !== '') {
                echo '<img class="tbt-tutor__portrait" src="' . esc_url($tutor['portrait']['url']) . '" alt="' . esc_attr($tutor['portrait']['alt'] ?: $tutor['name']) . '">';
            } elseif (isset($fallbackPortraits[$tutor['name']]) && function_exists('tbt_render_media_image')) {
                tbt_render_media_image((string) $fallbackPortraits[$tutor['name']], 'tbt-tutor__portrait', ['loading' => 'eager']);
            }
            echo '</div><div class="tbt-tutor__details">';
            echo '<h3>' . esc_html($displayName) . ($showRoles && $tutor['role'] !== '' ? '<small>' . esc_html($tutor['role']) . '</small>' : '') . '</h3>';
            if (isset($qualifications[$tutor['name']]) && is_array($qualifications[$tutor['name']])) {
                echo '<ul class="tbt-tutor__qualifications">';
                foreach ($qualifications[$tutor['name']] as $qualification) {
                    echo '<li>' . esc_html((string) $qualification) . '</li>';
                }
                echo '</ul>';
            }
            if (isset($profileLinks[$tutor['name']]) && (string) $profileLinks[$tutor['name']] !== '') {
                $profileUrl = (string) $profileLinks[$tutor['name']];
                echo '<p class="tbt-tutor__profile"><a href="' . esc_url($profileUrl) . '" target="_blank" rel="noopener noreferrer">' . esc_html($profileUrl) . '</a></p>';
            }
            echo '</div>';
            if ($hasDocuments) {
                $documentCount = count($documentKeys);
                $isCarousel = $documentCount > 1;
                echo '<aside class="tbt-tutor__certificates" aria-label="' . esc_attr($displayName . ' certificates') . '">';
                echo '<h4>Certificates</h4>';
                echo '<div class="tbt-tutor__documents"' . ($isCarousel ? ' data-carousel data-carousel-autoplay="4500"' : '') . '>';
                foreach ($documentKeys as $index => $mediaKey) {
                    echo '<figure class="tbt-tutor__document-slide"' . ($isCarousel ? ' data-carousel-slide' . ($index > 0 ? ' hidden' : '') : '') . '>';
                    echo '<button class="tbt-tutor__document-trigger" type="button" data-certificate-lightbox-trigger aria-haspopup="dialog" aria-label="' . esc_attr('Enlarge certificate ' . ($index + 1) . ' for ' . $displayName) . '">';
                    tbt_render_media_image($mediaKey, 'tbt-tutor__document', ['loading' => 'eager']);
                    echo '<span class="tbt-tutor__document-enlarge" aria-hidden="true">&#x26F6;</span></button></figure>';
                }
                if ($isCarousel) {
                    echo '<button class="tbt-tutor__document-control tbt-tutor__document-control--previous" type="button" data-carousel-prev aria-label="Previous certificate"><svg aria-hidden="true" viewBox="0 0 24 24"><path d="m15 18-6-6 6-6"/></svg></button>';
                    echo '<button class="tbt-tutor__document-control tbt-tutor__document-control--next" type="button" data-carousel-next aria-label="Next certificate"><svg aria-hidden="true" viewBox="0 0 24 24"><path d="m9 18 6-6-6-6"/></svg></button>';
                    echo '<div class="tbt-tutor__document-dots">';
                    foreach ($documentKeys as $index => $_mediaKey) {
                        echo '<button type="button" data-carousel-dot aria-label="Show certificate ' . esc_attr((string) ($index + 1)) . '"></button>';
                    }
                    echo '</div><p class="screen-reader-text" data-carousel-status aria-live="polite">1 of ' . esc_html((string) $documentCount) . '</p>';
                }
                echo '</div></aside>';
            }
        } else {
            if ($tutor['portrait']['url'] !== '') {
                echo '<img src="' . esc_url($tutor['portrait']['url']) . '" alt="' . esc_attr($tutor['portrait']['alt'] ?: $tutor['name']) . '">';
            } elseif (isset($fallbackPortraits[$tutor['name']]) && function_exists('tbt_render_media_image')) {
                tbt_render_media_image((string) $fallbackPortraits[$tutor['name']], 'tbt-tutor__portrait', ['loading' => 'lazy']);
            }
            echo '<h3>' . esc_html($tutor['name']) . '</h3>';
            if ($tutor['role'] !== '') {
                echo '<p class="tbt-tutor__role">' . esc_html($tutor['role']) . '</p>';
            }
            if ($tutor['short_bio'] !== '') {
                echo '<p>' . nl2br(esc_html($tutor['short_bio'])) . '</p>';
            }
        }
        echo '</article>';
    }
    echo '</div>';
}

