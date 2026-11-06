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
    if ($value === null || $value === []) {
        return $fallback;
    }
    if ($value === '' && (!function_exists('metadata_exists') || !metadata_exists('post', $postId, $name))) {
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
    if ($value === '' || $value === false) {
        return ['url' => '', 'alt' => '', 'width' => 0, 'height' => 0];
    }
    $normalized = tbt_normalize_image($value);
    if ($normalized !== null) {
        return $normalized;
    }

    return tbt_normalize_image($fallback) ?? ['url' => '', 'alt' => '', 'width' => 0, 'height' => 0];
}

/** Return the protected navigation model. */
function tbt_navigation_items(): array
{
    $reference = tbt_reference_content();
    return $reference['navigation'] ?? [];
}

/** Return whether a protected navigation item represents the current route. */
function tbt_navigation_item_is_active(array $item): bool
{
    $path = (string) ($item['path'] ?? '');
    if ($path === '/') {
        return function_exists('is_front_page') && is_front_page();
    }
    $slug = trim($path, '/');
    if ($slug !== '' && function_exists('is_page') && is_page($slug)) {
        return true;
    }
    foreach (($item['children'] ?? []) as $child) {
        $childSlug = trim((string) ($child['path'] ?? ''), '/');
        if ($childSlug !== '' && function_exists('is_page') && is_page($childSlug)) {
            return true;
        }
    }
    return false;
}

/** Render the protected main navigation using portable WordPress URLs. */
function tbt_render_navigation(string $class = 'site-nav__list'): void
{
    echo '<ul class="' . esc_attr($class) . '">';
    foreach (tbt_navigation_items() as $index => $item) {
        $children = $item['children'] ?? [];
        $hasChildren = is_array($children) && $children !== [];
        $isActive = tbt_navigation_item_is_active($item);
        $path = (string) $item['path'];
        $isCurrent = $path === '/'
            ? (function_exists('is_front_page') && is_front_page())
            : (function_exists('is_page') && is_page(trim($path, '/')));
        echo '<li class="site-nav__item' . ($hasChildren ? ' site-nav__item--has-children' : '') . '">';
        echo '<a class="site-nav__link' . ($isActive ? ' is-active' : '') . '" href="' . esc_url(home_url($path)) . '"' . ($isCurrent ? ' aria-current="page"' : '') . '>' . esc_html((string) $item['label']) . '</a>';
        if ($hasChildren) {
            $submenuId = 'site-nav-submenu-' . $index;
            echo '<button class="site-nav__submenu-toggle" type="button" data-submenu-toggle aria-expanded="false" aria-controls="' . esc_attr($submenuId) . '"><span class="screen-reader-text">' . esc_html('Open ' . $item['label'] . ' submenu') . '</span><svg class="site-nav__chevron" viewBox="0 0 12 8" width="12" height="8" aria-hidden="true" focusable="false"><path d="M1 1.5l5 5 5-5" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg></button>';
            echo '<ul class="site-nav__submenu" id="' . esc_attr($submenuId) . '" data-submenu>';
            foreach ($children as $child) {
                echo '<li><a class="site-nav__submenu-link" href="' . esc_url(home_url((string) $child['path'])) . '">' . esc_html((string) $child['label']) . '</a></li>';
            }
            echo '</ul>';
        }
        echo '</li>';
    }
    echo '</ul>';
}

/** Return one approved shared fallback from the reference module. */
function tbt_shared_fallback(string $name, mixed $fallback = ''): mixed
{
    $reference = tbt_reference_content();
    return $reference['shared'][$name] ?? $fallback;
}

/** Read one Home-owned shared value with its approved reference fallback. */
function tbt_shared_field(string $name, mixed $fallback = ''): mixed
{
    return tbt_home_field('home_shared_' . $name, tbt_shared_fallback($name, $fallback));
}

/** Return a safe portable WhatsApp link. */
function tbt_whatsapp_url(string $message = ''): string
{
    $number = preg_replace('/\D+/', '', (string) tbt_shared_field('whatsapp_number', '6587597182'));
    if (!is_string($number) || strlen($number) < 8 || strlen($number) > 15) {
        return '';
    }
    $text = $message !== '' ? $message : (string) tbt_shared_field('whatsapp_message', 'Hi ThatBioTutor');
    return 'https://wa.me/' . $number . ($text !== '' ? '?text=' . rawurlencode($text) : '');
}

/** Normalize a human-entered phone number to a safe tel URL. */
function tbt_safe_tel_url(string $phone): string
{
    if ($phone === '' || preg_match('/^[+0-9()\s.-]+$/', $phone) !== 1) {
        return '';
    }
    $digits = preg_replace('/\D+/', '', $phone);
    if (!is_string($digits) || strlen($digits) < 8 || strlen($digits) > 15) {
        return '';
    }
    return 'tel:+' . $digits;
}

/** Accept only absolute HTTP(S) destinations for external links. */
function tbt_safe_external_url(string $url): string
{
    if (filter_var($url, FILTER_VALIDATE_URL) === false) {
        return '';
    }
    $scheme = strtolower((string) parse_url($url, PHP_URL_SCHEME));
    return in_array($scheme, ['http', 'https'], true) ? $url : '';
}

/** Load the generated media manifest once per request. */
function tbt_media_manifest(): array
{
    static $items = null;
    if (is_array($items)) {
        return $items;
    }
    $path = TBT_THEME_DIR . '/assets/images/media-manifest.json';
    $decoded = is_file($path) ? json_decode((string) file_get_contents($path), true) : null;
    $items = [];
    foreach (($decoded['items'] ?? []) as $item) {
        $items[(string) $item['key']] = $item;
    }
    return $items;
}

/** Return one manifest image with its public theme URL. */
function tbt_media(string $key): array
{
    $item = tbt_media_manifest()[$key] ?? null;
    if (!is_array($item)) {
        return ['key' => $key, 'url' => '', 'alt' => '', 'width' => 0, 'height' => 0, 'fit' => 'cover', 'objectPosition' => '50% 50%'];
    }
    $item['url'] = TBT_THEME_URI . '/assets/images/' . ltrim((string) $item['file'], '/');
    return $item;
}

/** Render one manifest-backed local image with optional safe data attributes. */
function tbt_render_media_image(string $key, string $class = '', array $attributes = []): void
{
    $media = tbt_media($key);
    if (($media['url'] ?? '') === '') {
        return;
    }
    echo '<img data-media-key="' . esc_attr($key) . '"';
    if ($class !== '') {
        echo ' class="' . esc_attr($class) . '"';
    }
    foreach ($attributes as $name => $value) {
        if (preg_match('/^(?:data-[a-z0-9-]+|aria-[a-z0-9-]+|loading)$/', (string) $name) === 1) {
            echo ' ' . esc_attr((string) $name) . '="' . esc_attr((string) $value) . '"';
        }
    }
    echo ' src="' . esc_url((string) $media['url']) . '" alt="' . esc_attr((string) ($media['alt'] ?? '')) . '" width="' . esc_attr((string) ($media['width'] ?? 0)) . '" height="' . esc_attr((string) ($media['height'] ?? 0)) . '">';
}

/** Render an editable ACF image with one approved manifest fallback. */
function tbt_render_page_image(string $fieldName, string $fallbackKey, string $class = '', array $attributes = []): void
{
    $fallback = tbt_media($fallbackKey);
    $image = tbt_page_image($fieldName, (string) ($fallback['url'] ?? ''));
    if ($image['url'] === '') { return; }
    echo '<img data-media-key="' . esc_attr($fallbackKey) . '"';
    if ($class !== '') { echo ' class="' . esc_attr($class) . '"'; }
    foreach ($attributes as $name => $value) {
        if (preg_match('/^(?:data-[a-z0-9-]+|aria-[a-z0-9-]+|loading)$/', (string) $name) === 1) {
            echo ' ' . esc_attr((string) $name) . '="' . esc_attr((string) $value) . '"';
        }
    }
    echo ' src="' . esc_url((string) $image['url']) . '" alt="' . esc_attr((string) ($image['alt'] ?: ($fallback['alt'] ?? ''))) . '" width="' . esc_attr((string) ($image['width'] ?? 0)) . '" height="' . esc_attr((string) ($image['height'] ?? 0)) . '">';
}

/** Enqueue the protected local design system and progressive interactions. */
function tbt_enqueue_assets(): void
{
    wp_enqueue_style(
        'tbt-poppins',
        'https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap',
        [],
        null
    );
    $styles = ['base', 'components', 'pages'];
    if (function_exists('is_front_page') && is_front_page()) {
        $styles[] = 'pages-home';
    }
    if (function_exists('is_page') && is_page([
        'classes',
        'sec-3-pure-g3-biology-tuition',
        'sec-4-pure-g3-biology-tuition',
    ])) {
        $styles[] = 'pages-programmes';
    }
    if (function_exists('is_page') && is_page('testimonials')) {
        $styles[] = 'pages-testimonials';
    }
    if (function_exists('is_page') && is_page(['notes', 'celldivision'])) {
        $styles[] = 'pages-resources';
    }
    if (function_exists('is_page') && is_page(['about', 'contact', 'trees', 'tuitionrecs'])) {
        $styles[] = 'pages-institutional';
    }
    if (function_exists('is_page') && is_page('intensivefix')) {
        $styles[] = 'pages-home';
        $styles[] = 'pages-intensivefix';
    }
    $styles[] = 'layout';
    $styles[] = 'standard';
    foreach ($styles as $name) {
        $path = TBT_THEME_DIR . '/assets/css/' . $name . '.css';
        wp_enqueue_style('tbt-' . $name, TBT_THEME_URI . '/assets/css/' . $name . '.css', [], is_file($path) ? (string) filemtime($path) : TBT_THEME_VERSION);
    }
    $script = TBT_THEME_DIR . '/assets/js/site.js';
    wp_enqueue_script('tbt-site', TBT_THEME_URI . '/assets/js/site.js', [], is_file($script) ? (string) filemtime($script) : TBT_THEME_VERSION, true);
}

if (function_exists('add_action')) {
    // Load after the parent reset/theme sheets so protected child-theme rules win.
    add_action('wp_enqueue_scripts', 'tbt_enqueue_assets', 99);
}

