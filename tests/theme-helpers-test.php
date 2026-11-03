<?php

declare(strict_types=1);

if (!defined('ABSPATH')) {
    define('ABSPATH', dirname(__DIR__) . '/.wordpress-test/');
}
if (!defined('TBT_THEME_URI')) {
    define('TBT_THEME_URI', 'https://example.test/wp-content/themes/thatbiotutor-hello-child');
}

$GLOBALS['tbt_helper_fields'] = [];
$GLOBALS['tbt_helper_attachments'] = [
    42 => ['https://example.test/wp-content/uploads/biology.webp', 800, 600],
];

function get_the_ID(): int
{
    return 7;
}

function get_option(string $key, mixed $default = false): mixed
{
    return $key === 'page_on_front' ? 99 : $default;
}

function home_url(string $path = '/'): string
{
    return 'https://example.test' . ($path === '/' ? '/' : '/' . ltrim($path, '/'));
}

function wp_get_attachment_image_src(int $attachmentId, string $size = 'full'): array|false
{
    return $GLOBALS['tbt_helper_attachments'][$attachmentId] ?? false;
}

function get_post_meta(int $postId, string $key, bool $single = false): mixed
{
    if ($key === '_wp_attachment_image_alt' && $postId === 42) {
        return 'Biology notes preview';
    }
    return $single ? '' : [];
}

function metadata_exists(string $type, int $postId, string $key): bool
{
    return $type === 'post' && array_key_exists($key, $GLOBALS['tbt_helper_fields'][$postId] ?? []);
}

final class ThemeHelpersTest
{
    private int $assertions = 0;

    public function run(): void
    {
        $module = dirname(__DIR__) . '/wordpress/wp-content/themes/thatbiotutor-hello-child/inc/theme-helpers.php';
        $this->assertTrue(is_file($module), 'Theme-helper module must exist.');
        require_once $module;

        foreach (['tbt_page_field', 'tbt_home_field', 'tbt_page_image'] as $function) {
            $this->assertTrue(function_exists($function), "{$function} must exist.");
        }

        $this->assertSame('Approved fallback', tbt_page_field('missing', 'Approved fallback', 7), 'ACF absence must return the approved fallback without a fatal error.');

        eval('function get_field(string $name, int $postId = 0): mixed { return $GLOBALS["tbt_helper_fields"][$postId][$name] ?? null; }');

        $GLOBALS['tbt_helper_fields'][7] = [
            'empty' => '',
            'null' => null,
            'zero_int' => 0,
            'zero_string' => '0',
            'false_value' => false,
            'multiline' => "First line\n第二行 — Biology",
            'scalar' => 'Client copy',
            'local_image' => [
                'url' => 'https://example.test/wp-content/uploads/classroom.webp',
                'alt' => 'Students in class',
                'width' => 1200,
                'height' => 800,
            ],
            'remote_image' => [
                'url' => 'https://unapproved.example/images/hotlink.jpg',
                'alt' => 'Remote',
                'width' => 1200,
                'height' => 800,
            ],
            'invalid_image' => 'javascript:alert(1)',
            'attachment_image' => 42,
        ];
        $GLOBALS['tbt_helper_fields'][99] = ['shared_label' => 'Home-owned shared value'];

        $this->assertSame('', tbt_page_field('empty', 'Fallback', 7), 'An intentionally empty stored field must remain empty.');
        $this->assertSame('Fallback', tbt_page_field('null', 'Fallback', 7), 'Null must use fallback.');
        $this->assertSame(0, tbt_page_field('zero_int', 18, 7), 'Integer zero must remain a valid client value.');
        $this->assertSame('0', tbt_page_field('zero_string', 18, 7), 'String zero must remain a valid client value.');
        $this->assertSame(false, tbt_page_field('false_value', true, 7), 'False must remain a valid client value.');
        $this->assertSame("First line\n第二行 — Biology", tbt_page_field('multiline', 'Fallback', 7), 'Multiline non-ASCII copy must be preserved.');
        $this->assertSame('Client copy', tbt_page_field('scalar', 'Fallback', 7), 'Scalar ACF value must be returned.');
        $this->assertSame('Home-owned shared value', tbt_home_field('shared_label', 'Fallback'), 'Shared fields must read from the static Home page.');

        $fallback = TBT_THEME_URI . '/assets/images/shared/fallback.webp';
        $local = tbt_page_image('local_image', $fallback, 7);
        $this->assertSame('https://example.test/wp-content/uploads/classroom.webp', $local['url'], 'Local image array must keep its URL.');
        $this->assertSame('Students in class', $local['alt'], 'Local image array must keep its alt text.');
        $this->assertSame(1200, $local['width'], 'Local image array must normalize width.');
        $this->assertSame(800, $local['height'], 'Local image array must normalize height.');

        $attachment = tbt_page_image('attachment_image', $fallback, 7);
        $this->assertSame('https://example.test/wp-content/uploads/biology.webp', $attachment['url'], 'Attachment ID must resolve to its local URL.');
        $this->assertSame('Biology notes preview', $attachment['alt'], 'Attachment ID must resolve its alt text.');

        foreach (['remote_image', 'invalid_image'] as $name) {
            $image = tbt_page_image($name, $fallback, 7);
            $this->assertSame($fallback, $image['url'], "{$name} must fall back instead of hotlinking or emitting an unsafe URL.");
            $this->assertSame('', $image['alt'], "{$name} fallback must have a safe default alt value.");
        }

        $missing = tbt_page_image('not_set', $fallback, 7);
        $this->assertSame($fallback, $missing['url'], 'Missing image field must return the approved local fallback.');
        $this->assertSame(0, $missing['width'], 'String fallback has no invented width.');
        $this->assertSame(0, $missing['height'], 'String fallback has no invented height.');

        $blank = tbt_page_image('empty', $fallback, 7);
        $this->assertSame('', $blank['url'], 'An intentionally cleared image must not restore the fallback image.');

        fwrite(STDOUT, "PASS: {$this->assertions} theme-helper assertions\n");
    }

    private function assertSame(mixed $expected, mixed $actual, string $message): void
    {
        $this->assertions++;
        if ($expected !== $actual) {
            throw new RuntimeException($message . ' Expected ' . var_export($expected, true) . ', got ' . var_export($actual, true));
        }
    }

    private function assertTrue(bool $condition, string $message): void
    {
        $this->assertions++;
        if (!$condition) {
            throw new RuntimeException($message);
        }
    }
}

try {
    (new ThemeHelpersTest())->run();
} catch (Throwable $error) {
    fwrite(STDERR, 'FAIL: ' . $error->getMessage() . "\n");
    exit(1);
}
