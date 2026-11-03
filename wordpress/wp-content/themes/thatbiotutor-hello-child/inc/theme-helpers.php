<?php

declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}

/** Read one ACF value while preserving valid zero and false values. */
function tbt_page_field(string $name, mixed $fallback = '', int $post_id = 0): mixed
{
    $postId = $post_id;
    if ($postId <= 0 && function_exists('get_the_ID')) {
        $postId = (int) get_the_ID();
    }

    if (!function_exists('get_field')) {
        return $fallback;
    }

    $value = get_field($name, $postId);
    if ($value === null || $value === '' || $value === []) {
        return $fallback;
    }

    return $value;
}

/** Read a shared field owned by the static Home page. */
function tbt_home_field(string $name, mixed $fallback = ''): mixed
{
    $homeId = function_exists('get_option') ? (int) get_option('page_on_front', 0) : 0;
    return tbt_page_field($name, $fallback, $homeId);
}

/** Decide whether a media URL is local to WordPress or this theme. */
function tbt_is_local_asset_url(string $url): bool
{
    if ($url === '' || str_starts_with($url, '//')) {
        return false;
    }
    if (str_starts_with($url, '/')) {
        return true;
    }

    $scheme = parse_url($url, PHP_URL_SCHEME);
    $host = parse_url($url, PHP_URL_HOST);
    if (!in_array($scheme, ['http', 'https'], true) || !is_string($host) || $host === '') {
        return false;
    }

    $allowedHosts = [];
    if (function_exists('home_url')) {
        $homeHost = parse_url(home_url('/'), PHP_URL_HOST);
        if (is_string($homeHost) && $homeHost !== '') {
            $allowedHosts[] = strtolower($homeHost);
        }
    }
    if (defined('TBT_THEME_URI')) {
        $themeHost = parse_url(TBT_THEME_URI, PHP_URL_HOST);
        if (is_string($themeHost) && $themeHost !== '') {
            $allowedHosts[] = strtolower($themeHost);
        }
    }

    return in_array(strtolower($host), array_unique($allowedHosts), true);
}

/** Normalize an ACF image array, attachment ID, or approved local URL. */
function tbt_normalize_image(mixed $value): ?array
{
    if (is_int($value) || (is_string($value) && ctype_digit($value))) {
        $attachmentId = (int) $value;
        if ($attachmentId <= 0 || !function_exists('wp_get_attachment_image_src')) {
            return null;
        }
        $source = wp_get_attachment_image_src($attachmentId, 'full');
        if (!is_array($source) || !isset($source[0]) || !tbt_is_local_asset_url((string) $source[0])) {
            return null;
        }
        $alt = function_exists('get_post_meta')
            ? (string) get_post_meta($attachmentId, '_wp_attachment_image_alt', true)
            : '';
        return [
            'url' => (string) $source[0],
            'alt' => $alt,
            'width' => (int) ($source[1] ?? 0),
            'height' => (int) ($source[2] ?? 0),
        ];
    }

    if (is_array($value)) {
        $url = (string) ($value['url'] ?? '');
        if (!tbt_is_local_asset_url($url)) {
            return null;
        }
        return [
            'url' => $url,
            'alt' => (string) ($value['alt'] ?? ''),
            'width' => max(0, (int) ($value['width'] ?? 0)),
            'height' => max(0, (int) ($value['height'] ?? 0)),
        ];
    }

    if (is_string($value) && tbt_is_local_asset_url($value)) {
        return ['url' => $value, 'alt' => '', 'width' => 0, 'height' => 0];
    }

    return null;
}

/** Return a safe normalized image, falling back to an approved local asset. */
function tbt_page_image(string $name, string $fallback, int $post_id = 0): array
{
    $value = tbt_page_field($name, null, $post_id);
    $normalized = tbt_normalize_image($value);
    if ($normalized !== null) {
        return $normalized;
    }

    return tbt_normalize_image($fallback) ?? ['url' => '', 'alt' => '', 'width' => 0, 'height' => 0];
}

