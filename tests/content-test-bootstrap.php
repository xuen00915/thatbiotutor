<?php

declare(strict_types=1);

if (!defined('ABSPATH')) {
    define('ABSPATH', dirname(__DIR__) . '/.wordpress-test/');
}

$GLOBALS['tbt_content_actions'] = [];
$GLOBALS['tbt_content_filters'] = [];
$GLOBALS['tbt_registered_post_types'] = [];
$GLOBALS['tbt_content_posts'] = [];
$GLOBALS['tbt_content_meta'] = [];

function add_action(string $hook, callable|string|array $callback, int $priority = 10, int $acceptedArgs = 1): void
{
    $GLOBALS['tbt_content_actions'][] = [$hook, $callback, $priority, $acceptedArgs];
}

function add_filter(string $hook, callable|string|array $callback, int $priority = 10, int $acceptedArgs = 1): void
{
    $GLOBALS['tbt_content_filters'][] = [$hook, $callback, $priority, $acceptedArgs];
}

function is_admin(): bool
{
    return true;
}

function register_post_type(string $postType, array $args): void
{
    $GLOBALS['tbt_registered_post_types'][$postType] = $args;
}

function get_posts(array $args = []): array
{
    $posts = array_filter($GLOBALS['tbt_content_posts'], static function (object $post) use ($args): bool {
        if (isset($args['post_type']) && $post->post_type !== $args['post_type']) {
            return false;
        }
        if (isset($args['post_status']) && $post->post_status !== $args['post_status']) {
            return false;
        }
        foreach (($args['meta_query'] ?? []) as $condition) {
            if (!is_array($condition) || !isset($condition['key'])) {
                continue;
            }
            $actual = $GLOBALS['tbt_content_meta'][$post->ID][$condition['key']] ?? '';
            if (($condition['compare'] ?? '=') === '=' && $actual !== ($condition['value'] ?? null)) {
                return false;
            }
        }
        return true;
    });
    return array_values($posts);
}

function get_post_meta(int $postId, string $key, bool $single = false): mixed
{
    if (!array_key_exists($key, $GLOBALS['tbt_content_meta'][$postId] ?? [])) {
        return $single ? '' : [];
    }
    $value = $GLOBALS['tbt_content_meta'][$postId][$key];
    return $single ? $value : [$value];
}

function get_the_title(object|int $post): string
{
    $id = is_object($post) ? (int) $post->ID : $post;
    foreach ($GLOBALS['tbt_content_posts'] as $candidate) {
        if ((int) $candidate->ID === $id) {
            return (string) $candidate->post_title;
        }
    }
    return '';
}

function esc_html(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function esc_attr(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function esc_url(string $value): string
{
    return filter_var($value, FILTER_VALIDATE_URL) ? htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') : '';
}

function wp_kses_post(string $value): string
{
    return strip_tags($value, '<p><br><strong><em><b><i><a><ul><ol><li>');
}

function tbt_test_post(int $id, string $type, string $title, int $order = 0, string $status = 'publish', string $content = ''): object
{
    return (object) [
        'ID' => $id,
        'post_type' => $type,
        'post_title' => $title,
        'post_status' => $status,
        'post_content' => $content,
        'menu_order' => $order,
    ];
}

abstract class TbtContentTestCase
{
    protected int $assertions = 0;

    protected function assertSame(mixed $expected, mixed $actual, string $message): void
    {
        $this->assertions++;
        if ($expected !== $actual) {
            throw new RuntimeException($message . ' Expected ' . var_export($expected, true) . ', got ' . var_export($actual, true));
        }
    }

    protected function assertTrue(bool $condition, string $message): void
    {
        $this->assertions++;
        if (!$condition) {
            throw new RuntimeException($message);
        }
    }

    protected function capture(callable $renderer): string
    {
        ob_start();
        $renderer();
        return (string) ob_get_clean();
    }
}

