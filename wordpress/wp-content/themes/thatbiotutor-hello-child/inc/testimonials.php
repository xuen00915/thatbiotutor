<?php

declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}

/** Convert a supported YouTube URL to its privacy-enhanced embed URL. */
function tbt_testimonial_embed_url(string $url): string
{
    $patterns = [
        '~youtube\.com/watch\?[^#]*v=([A-Za-z0-9_-]{6,})~',
        '~youtu\.be/([A-Za-z0-9_-]{6,})~',
        '~youtube(?:-nocookie)?\.com/embed/([A-Za-z0-9_-]{6,})~',
    ];
    foreach ($patterns as $pattern) {
        if (preg_match($pattern, $url, $matches) === 1) {
            return 'https://www.youtube-nocookie.com/embed/' . $matches[1];
        }
    }
    return '';
}

/** Return normalized, published testimonial records. */
function tbt_get_testimonials(array $args = []): array
{
    $records = [];
    foreach (tbt_get_structured_posts('tbt_testimonial', $args) as $post) {
        $rating = (int) tbt_structured_meta((int) $post->ID, 'tbt_rating', 5);
        if ($rating < 1 || $rating > 5) {
            $rating = 5;
        }
        $portrait = function_exists('tbt_normalize_image')
            ? tbt_normalize_image(tbt_structured_meta((int) $post->ID, 'tbt_testimonial_portrait', ''))
            : null;
        $records[] = [
            'id' => (int) $post->ID,
            'title' => (string) $post->post_title,
            'name' => (string) tbt_structured_meta((int) $post->ID, 'tbt_student_name', (string) $post->post_title),
            'quote' => (string) tbt_structured_meta((int) $post->ID, 'tbt_quote', ''),
            'class_year' => (string) tbt_structured_meta((int) $post->ID, 'tbt_class_year', ''),
            'before' => (string) tbt_structured_meta((int) $post->ID, 'tbt_before_result', ''),
            'after' => (string) tbt_structured_meta((int) $post->ID, 'tbt_after_result', ''),
            'rating' => $rating,
            'video_url' => tbt_testimonial_embed_url((string) tbt_structured_meta((int) $post->ID, 'tbt_video_url', '')),
            'portrait' => $portrait ?? ['url' => '', 'alt' => '', 'width' => 0, 'height' => 0],
            'group' => (string) tbt_structured_meta((int) $post->ID, 'tbt_display_group', ''),
        ];
    }
    return $records;
}

/** Render testimonial cards without an internal scroll container. */
function tbt_render_testimonials(array $args = []): void
{
    $records = tbt_get_testimonials($args);
    echo '<div class="tbt-testimonials" data-testimonial-list>';
    foreach ($records as $record) {
        $videoOnly = $record['video_url'] !== '' && $record['quote'] === '';
        echo '<article class="tbt-testimonial' . ($videoOnly ? ' tbt-testimonial--video' : '') . '">';
        if ($record['portrait']['url'] !== '') {
            echo '<img class="tbt-testimonial__portrait" src="' . esc_url($record['portrait']['url']) . '" alt="' . esc_attr($record['portrait']['alt']) . '">';
        }
        if (!$videoOnly) {
            echo '<div class="tbt-testimonial__stars" aria-label="' . esc_attr((string) $record['rating'] . ' out of 5 stars') . '">' . esc_html(str_repeat('★', $record['rating'])) . '</div>';
        }
        if ($record['quote'] !== '') {
            echo '<blockquote>' . nl2br(esc_html($record['quote'])) . '</blockquote>';
        }
        if (!$videoOnly) {
            echo '<p class="tbt-testimonial__name"><strong>' . esc_html($record['name']) . '</strong></p>';
        }
        if ($record['video_url'] !== '') {
            echo '<div class="tbt-video tbt-video--testimonial"><iframe src="' . esc_url($record['video_url']) . '" title="' . esc_attr($record['name'] . ' video testimonial') . '" loading="lazy" scrolling="no" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" referrerpolicy="strict-origin-when-cross-origin" allowfullscreen></iframe></div>';
        }
        echo '</article>';
    }
    echo '</div>';
}

