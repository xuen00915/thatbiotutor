<?php

declare(strict_types=1);

if (!defined('ABSPATH')) {
    define('ABSPATH', dirname(__DIR__) . '/.wordpress-test/');
}
if (!defined('OBJECT')) {
    define('OBJECT', 'OBJECT');
}

final class WP_Error
{
    public function __construct(public string $code, public string $message = '')
    {
    }

    public function get_error_code(): string
    {
        return $this->code;
    }

    public function get_error_message(): string
    {
        return $this->message;
    }
}

$GLOBALS['tbt_db'] = [
    'posts' => [],
    'meta' => [],
    'options' => [],
    'nextId' => 1,
];

function tbt_test_reset_db(): void
{
    $GLOBALS['tbt_db'] = ['posts' => [], 'meta' => [], 'options' => [], 'nextId' => 1];
}

function sanitize_title(string $value): string
{
    $value = strtolower(trim($value));
    return trim((string) preg_replace('/[^a-z0-9]+/', '-', $value), '-');
}

function get_page_by_path(string $path, string $output = OBJECT, string $postType = 'page'): ?object
{
    foreach ($GLOBALS['tbt_db']['posts'] as $post) {
        if ($post['post_type'] === $postType && $post['post_name'] === $path) {
            return (object) array_merge($post, ['ID' => $post['ID']]);
        }
    }
    return null;
}

function get_posts(array $args = []): array
{
    $matches = [];
    foreach ($GLOBALS['tbt_db']['posts'] as $post) {
        if (isset($args['post_type']) && $post['post_type'] !== $args['post_type']) {
            continue;
        }
        if (isset($args['post_status']) && $args['post_status'] !== 'any' && $post['post_status'] !== $args['post_status']) {
            continue;
        }
        $valid = true;
        foreach (($args['meta_query'] ?? []) as $condition) {
            $actual = $GLOBALS['tbt_db']['meta'][$post['ID']][$condition['key']] ?? '';
            if ($actual !== ($condition['value'] ?? null)) {
                $valid = false;
                break;
            }
        }
        if ($valid) {
            $matches[] = (object) $post;
        }
    }
    return $matches;
}

function wp_insert_post(array $post, bool $returnError = false): int|WP_Error
{
    $id = $GLOBALS['tbt_db']['nextId']++;
    $post['ID'] = $id;
    $post['post_name'] = $post['post_name'] ?? sanitize_title((string) ($post['post_title'] ?? ''));
    $post['post_type'] = $post['post_type'] ?? 'post';
    $post['post_status'] = $post['post_status'] ?? 'draft';
    $GLOBALS['tbt_db']['posts'][$id] = $post;
    return $id;
}

function wp_update_post(array $post, bool $returnError = false): int|WP_Error
{
    $id = (int) ($post['ID'] ?? 0);
    if (!isset($GLOBALS['tbt_db']['posts'][$id])) {
        return new WP_Error('missing_post', 'Missing post');
    }
    $GLOBALS['tbt_db']['posts'][$id] = array_merge($GLOBALS['tbt_db']['posts'][$id], $post);
    return $id;
}

function get_post_meta(int $postId, string $key, bool $single = false): mixed
{
    if (!array_key_exists($key, $GLOBALS['tbt_db']['meta'][$postId] ?? [])) {
        return $single ? '' : [];
    }
    $value = $GLOBALS['tbt_db']['meta'][$postId][$key];
    return $single ? $value : [$value];
}

function update_post_meta(int $postId, string $key, mixed $value): bool
{
    $GLOBALS['tbt_db']['meta'][$postId][$key] = $value;
    return true;
}

function update_option(string $key, mixed $value): bool
{
    $GLOBALS['tbt_db']['options'][$key] = $value;
    return true;
}

function get_option(string $key, mixed $default = false): mixed
{
    return $GLOBALS['tbt_db']['options'][$key] ?? $default;
}

function is_wp_error(mixed $value): bool
{
    return $value instanceof WP_Error;
}

function wp_json_encode(mixed $value, int $flags = 0, int $depth = 512): string|false
{
    return json_encode($value, $flags, $depth);
}

require_once dirname(__DIR__) . '/wordpress/wp-content/themes/thatbiotutor-hello-child/inc/reference-content.php';
require_once dirname(__DIR__) . '/wordpress/wp-content/themes/thatbiotutor-hello-child/inc/site-setup.php';

final class SiteSetupTest
{
    private int $assertions = 0;

    public function run(): void
    {
        $this->assertTrue(function_exists('tbt_upsert_reference_page'), 'Page upsert interface must exist.');
        $this->assertTrue(function_exists('tbt_update_seeded_field'), 'Seeded-field ownership interface must exist.');
        $this->assertTrue(function_exists('tbt_setup_site'), 'Site setup interface must exist.');

        $this->testCollisionSafety();
        $this->testIdempotentPageSetup();
        $this->testSeedOwnership();

        fwrite(STDOUT, "PASS: {$this->assertions} site-setup assertions\n");
    }

    private function testCollisionSafety(): void
    {
        tbt_test_reset_db();
        $unownedId = wp_insert_post([
            'post_title' => 'Someone Else Classes',
            'post_name' => 'classes',
            'post_type' => 'page',
            'post_status' => 'publish',
        ], true);

        $result = tbt_upsert_reference_page('classes', 'Classes', 'page-classes.php');
        $this->assertTrue($result instanceof WP_Error, 'An unowned slug collision must return WP_Error.');
        $this->assertSame('tbt_slug_collision', $result->get_error_code(), 'Collision must use a stable error code.');
        $this->assertSame('Someone Else Classes', $GLOBALS['tbt_db']['posts'][$unownedId]['post_title'], 'Collision handling must not overwrite the unrelated page.');
    }

    private function testIdempotentPageSetup(): void
    {
        tbt_test_reset_db();
        $unrelatedId = wp_insert_post([
            'post_title' => 'Private parent guide',
            'post_name' => 'parent-guide',
            'post_type' => 'page',
            'post_status' => 'draft',
        ], true);
        update_option('unrelated_option', ['keep' => true]);

        $first = tbt_setup_site(false);
        $this->assertTrue(is_array($first), 'First setup run must return its owned page IDs.');
        $this->assertSame(12, count($first['pages'] ?? []), 'First run must create every canonical page.');
        $expectedPosts = 1 + 12 + array_sum(array_map('count', tbt_reference_content()['records']));
        $this->assertSame($expectedPosts, count($GLOBALS['tbt_db']['posts']), 'Only the twelve approved pages and approved structured seeds may be added.');
        $this->assertSame(4, count($first['records']['tutors'] ?? []), 'Setup must seed the four original tutors.');
        $this->assertTrue(count($first['records']['testimonials'] ?? []) >= 50, 'Setup must seed the video and preserved text-review archives.');
        $this->assertTrue(count($first['records']['faqs'] ?? []) >= 30, 'Setup must seed the original FAQ collections.');
        $this->assertTrue(count($first['records']['results'] ?? []) >= 1, 'Setup must seed editable structured results.');
        $this->assertSame('page', $GLOBALS['tbt_db']['options']['show_on_front'] ?? null, 'Setup must select a static front page.');
        $homeId = $first['pages']['home'];
        $this->assertSame($homeId, $GLOBALS['tbt_db']['options']['page_on_front'] ?? null, 'Home must be assigned as the static front page.');

        foreach (tbt_page_registry() as $slug => $definition) {
            $pageId = $first['pages'][$slug];
            $this->assertSame($definition['template'], get_post_meta($pageId, '_wp_page_template', true), "Template must be assigned for {$slug}.");
            $this->assertSame('1', (string) get_post_meta($pageId, '_tbt_reference_page', true), "Ownership marker must be assigned for {$slug}.");
        }

        $second = tbt_setup_site(false);
        $this->assertTrue(is_array($second), 'Second setup run must succeed.');
        $this->assertSame($first['pages'], $second['pages'], 'Second run must reuse the same page IDs.');
        $this->assertSame($expectedPosts, count($GLOBALS['tbt_db']['posts']), 'Second run must not duplicate pages or structured records.');
        $this->assertTrue(isset($GLOBALS['tbt_db']['posts'][$unrelatedId]), 'Unrelated pages must survive setup.');
        $this->assertSame(['keep' => true], get_option('unrelated_option'), 'Unrelated options must survive setup.');

        $firstTutor = $first['records']['tutors'][0];
        update_post_meta($firstTutor, 'tbt_tutor_role', 'Client-updated role');
        $third = tbt_setup_site(false);
        $this->assertTrue(is_array($third), 'Setup after structured-content edit must succeed.');
        $this->assertSame('Client-updated role', get_post_meta($firstTutor, 'tbt_tutor_role', true), 'Structured client edits must survive setup reruns.');
    }

    private function testSeedOwnership(): void
    {
        $postId = (int) ($GLOBALS['tbt_db']['options']['page_on_front'] ?? 0);
        $fieldKey = 'field_tbt_home_heading';
        $fieldName = 'home_heading';

        $this->assertTrue(tbt_update_seeded_field($postId, $fieldKey, $fieldName, 'Seed A'), 'Missing field must be seeded.');
        $this->assertSame('Seed A', get_post_meta($postId, $fieldName, true), 'Initial seed must be stored.');
        $this->assertTrue(tbt_update_seeded_field($postId, $fieldKey, $fieldName, 'Seed B'), 'Untouched seed must advance.');
        $this->assertSame('Seed B', get_post_meta($postId, $fieldName, true), 'Untouched seed must receive its new reference value.');

        update_post_meta($postId, $fieldName, 'Client wording');
        $this->assertSame(false, tbt_update_seeded_field($postId, $fieldKey, $fieldName, 'Seed C'), 'Normal rerun must preserve client-edited values.');
        $this->assertSame('Client wording', get_post_meta($postId, $fieldName, true), 'Client wording must survive normal rerun.');

        update_post_meta($postId, $fieldName, '');
        $this->assertSame(false, tbt_update_seeded_field($postId, $fieldKey, $fieldName, 'Seed C'), 'Normal rerun must preserve an intentionally cleared field.');
        $this->assertSame('', get_post_meta($postId, $fieldName, true), 'Intentionally blank client wording must survive normal rerun.');

        update_post_meta($postId, 'client_private_note', 'Never touch');
        $this->assertTrue(tbt_update_seeded_field($postId, $fieldKey, $fieldName, 'Seed C', true), 'Approved refresh must update the protected reference field.');
        $this->assertSame('Seed C', get_post_meta($postId, $fieldName, true), 'Approved refresh must apply the new reference value.');
        $this->assertSame('Never touch', get_post_meta($postId, 'client_private_note', true), 'Approved refresh must leave unrelated meta unchanged.');
        $this->assertSame($fieldKey, get_post_meta($postId, '_' . $fieldName, true), 'Seeded field must retain its ACF field-key reference.');
        $this->assertTrue(get_post_meta($postId, '_tbt_seed_hash_' . $fieldName, true) !== '', 'Seed hash must be recorded for ownership checks.');
        $this->assertTrue(get_post_meta($postId, '_tbt_seed_version', true) !== '', 'Seed version must be recorded.');
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
    (new SiteSetupTest())->run();
} catch (Throwable $error) {
    fwrite(STDERR, 'FAIL: ' . $error->getMessage() . "\n");
    exit(1);
}
