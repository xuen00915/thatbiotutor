<?php

declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}

/** Return published FAQs assigned to one protected collection. */
function tbt_get_faqs(string $collection): array
{
    $posts = tbt_get_structured_posts('tbt_faq', [
        'meta_query' => [[
            'key' => 'tbt_faq_collection',
            'value' => $collection,
            'compare' => '=',
        ]],
    ]);
    return array_map(static fn (object $post): array => [
        'id' => (int) $post->ID,
        'question' => (string) $post->post_title,
        'answer' => (string) ($post->post_content ?? ''),
        'collection' => $collection,
    ], $posts);
}

/** Render accessible progressive-enhancement FAQ accordions. */
function tbt_render_faqs(string $collection): void
{
    echo '<div class="tbt-faqs" data-faq-list>';
    foreach (tbt_get_faqs($collection) as $faq) {
        $answerId = 'tbt-faq-answer-' . $faq['id'];
        echo '<article class="tbt-faq">';
        echo '<h3><button type="button" data-faq-button aria-expanded="false" aria-controls="' . esc_attr($answerId) . '">' . esc_html($faq['question']) . '</button></h3>';
        echo '<div id="' . esc_attr($answerId) . '" data-faq-panel><div class="tbt-faq__answer">' . wp_kses_post($faq['answer']) . '</div></div>';
        echo '</article>';
    }
    echo '</div>';
}

