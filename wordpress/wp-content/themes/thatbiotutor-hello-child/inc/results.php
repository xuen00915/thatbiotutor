<?php

declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}

/** Return normalized, published grade-jump records. */
function tbt_get_results(array $args = []): array
{
    return array_map(static fn (object $post): array => [
        'id' => (int) $post->ID,
        'title' => (string) $post->post_title,
        'student' => (string) tbt_structured_meta((int) $post->ID, 'tbt_result_student', (string) $post->post_title),
        'before' => (string) tbt_structured_meta((int) $post->ID, 'tbt_result_before', ''),
        'after' => (string) tbt_structured_meta((int) $post->ID, 'tbt_result_after', ''),
        'year' => (string) tbt_structured_meta((int) $post->ID, 'tbt_result_year', ''),
    ], tbt_get_structured_posts('tbt_result', $args));
}

/** Render editable structured result records below preserved source artwork. */
function tbt_render_results(array $args = []): void
{
    echo '<div class="tbt-results" data-result-list>';
    foreach (tbt_get_results($args) as $result) {
        echo '<article class="tbt-result"><h3>' . esc_html($result['student']) . '</h3><p><span>' . esc_html($result['before']) . '</span> <span aria-hidden="true">→</span> <strong>' . esc_html($result['after']) . '</strong></p>';
        if ($result['year'] !== '') { echo '<p>Class of ' . esc_html($result['year']) . '</p>'; }
        echo '</article>';
    }
    echo '</div>';
}

