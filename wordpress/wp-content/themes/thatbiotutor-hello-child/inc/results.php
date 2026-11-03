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

