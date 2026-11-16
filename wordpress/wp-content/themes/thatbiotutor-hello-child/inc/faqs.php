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

/**
 * Render accessible progressive-enhancement FAQ accordions. Style "accordion" is the numbered Home-page look (one
 * bordered list, plus/minus icon, green open row); the default is the plain list used elsewhere.
 */
function tbt_render_faqs(string $collection, string $style = 'plain'): void
{
    if ($style === 'accordion') {
        echo '<div class="tbt-home-faq__list" data-faq-list>';
        foreach (tbt_get_faqs($collection) as $index => $faq) {
            $answerId = 'tbt-faq-answer-' . $faq['id'];
            echo '<article class="tbt-home-faq__item" data-home-inquire-faq><h3><button type="button" data-faq-button aria-expanded="false" aria-controls="' . esc_attr($answerId) . '"><span>' . esc_html((string) ($index + 1) . '. ' . $faq['question']) . '</span><span class="tbt-home-faq__icon" aria-hidden="true"></span></button></h3>';
            echo '<div id="' . esc_attr($answerId) . '" class="tbt-home-faq__answer" hidden>' . wp_kses_post(wpautop($faq['answer'])) . '</div></article>';
        }
        echo '</div>';
        return;
    }
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

