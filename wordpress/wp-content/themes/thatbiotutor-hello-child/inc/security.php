<?php
declare(strict_types=1);
if (!defined('ABSPATH')) { exit; }

function tbt_comments_closed(bool $open = false): bool { return false; }

function tbt_disable_pingback_method(array $methods): array
{
    unset($methods['pingback.ping'], $methods['pingback.extensions.getPingbacks']);
    return $methods;
}

function tbt_empty_generator(string $generator = ''): string { return ''; }

function tbt_send_security_headers(): void
{
    if (headers_sent()) { return; }
    header('X-Content-Type-Options: nosniff');
    header('Referrer-Policy: strict-origin-when-cross-origin');
    header('X-Frame-Options: SAMEORIGIN');
}

if (function_exists('add_filter')) {
    add_filter('comments_open', 'tbt_comments_closed', 20, 2);
    add_filter('pings_open', 'tbt_comments_closed', 20, 2);
    add_filter('xmlrpc_methods', 'tbt_disable_pingback_method');
    add_filter('the_generator', 'tbt_empty_generator');
}
if (function_exists('remove_action')) { remove_action('wp_head', 'wp_generator'); }
if (function_exists('add_action')) { add_action('send_headers', 'tbt_send_security_headers'); }
