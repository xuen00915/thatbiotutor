<?php

declare(strict_types=1);

require_once __DIR__ . '/content-test-bootstrap.php';

final class TestimonialsTest extends TbtContentTestCase
{
    public function run(): void
    {
        $theme = dirname(__DIR__) . '/wordpress/wp-content/themes/thatbiotutor-hello-child/inc';
        foreach (['content-types.php', 'testimonials.php'] as $file) {
            $this->assertTrue(is_file($theme . '/' . $file), "{$file} must exist.");
            require_once $theme . '/' . $file;
        }

        tbt_register_content_types();
        foreach (['tbt_testimonial', 'tbt_tutor', 'tbt_faq', 'tbt_result'] as $type) {
            $config = $GLOBALS['tbt_registered_post_types'][$type] ?? null;
            $this->assertTrue(is_array($config), "{$type} must be registered.");
            $this->assertSame(false, $config['public'] ?? null, "{$type} must not expose public single routes.");
            $this->assertSame(true, $config['show_ui'] ?? null, "{$type} must remain editable in admin.");
            $this->assertTrue(in_array('title', $config['supports'] ?? [], true), "{$type} must support titles.");
            $this->assertTrue(in_array('page-attributes', $config['supports'] ?? [], true), "{$type} must support native ordering.");
        }
        foreach (['tbt_structured_admin_columns', 'tbt_render_structured_admin_column', 'tbt_order_structured_admin_query'] as $function) {
            $this->assertTrue(function_exists($function), "{$function} must exist for capability-safe administration.");
        }
        $filterHooks = array_column($GLOBALS['tbt_content_filters'], 0);
        foreach (array_keys($GLOBALS['tbt_registered_post_types']) as $type) {
            $this->assertTrue(in_array("manage_{$type}_posts_columns", $filterHooks, true), "{$type} must expose useful admin columns.");
        }
        $query = new class {
            public array $values = ['post_type' => 'tbt_testimonial'];
            public function is_main_query(): bool { return true; }
            public function get(string $key): mixed { return $this->values[$key] ?? null; }
            public function set(string $key, mixed $value): void { $this->values[$key] = $value; }
        };
        tbt_order_structured_admin_query($query);
        $this->assertSame(['menu_order' => 'ASC', 'title' => 'ASC', 'ID' => 'ASC'], $query->values['orderby'] ?? null, 'Admin list must use the same deterministic ordering as public output.');

        $GLOBALS['tbt_content_posts'] = [
            tbt_test_post(8, 'tbt_testimonial', 'Zeta', 2),
            tbt_test_post(7, 'tbt_testimonial', 'Alpha', 2),
            tbt_test_post(3, 'tbt_testimonial', 'Video story', 1),
            tbt_test_post(4, 'tbt_testimonial', 'Draft story', 0, 'draft'),
            tbt_test_post(2, 'tbt_tutor', 'Not a testimonial', 0),
        ];
        $GLOBALS['tbt_content_meta'] = [
            3 => [
                'tbt_student_name' => 'Jérôme “Jay”',
                'tbt_quote' => 'I improved — and understood why.',
                'tbt_rating' => 9,
                'tbt_video_url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
                'tbt_display_group' => '2026',
            ],
            7 => ['tbt_student_name' => 'Amy', 'tbt_quote' => 'Clear and kind.', 'tbt_rating' => 5],
            8 => ['tbt_student_name' => 'Zoë', 'tbt_quote' => 'No portrait needed.', 'tbt_rating' => 4],
            4 => ['tbt_student_name' => 'Hidden', 'tbt_quote' => 'Draft must not render.', 'tbt_rating' => 5],
        ];

        $records = tbt_get_testimonials();
        $this->assertSame([3, 7, 8], array_column($records, 'id'), 'Published testimonials must sort by order, title, then ID and exclude Drafts.');
        $this->assertSame(5, $records[0]['rating'], 'Invalid rating must normalize to the safe five-star fallback.');
        $this->assertSame('', $records[2]['portrait']['url'] ?? '', 'Missing portrait must normalize without invented media.');

        $html = $this->capture(static fn () => tbt_render_testimonials());
        $this->assertTrue(str_contains($html, 'Jérôme “Jay”'), 'Unicode student names must remain readable.');
        $this->assertTrue(str_contains($html, 'title="Jérôme “Jay” video testimonial"'), 'Video iframe must have a useful title.');
        $this->assertTrue(str_contains($html, 'loading="lazy"'), 'Video iframe must lazy-load.');
        $this->assertTrue(str_contains($html, 'allowfullscreen'), 'Video iframe must allow fullscreen playback.');
        $this->assertTrue(str_contains($html, 'scrolling="no"'), 'Video iframe must explicitly disable its internal scroll region.');
        $this->assertTrue(str_contains($html, 'tbt-video--testimonial'), 'Video iframe must use the fixed-aspect testimonial wrapper.');
        $this->assertSame(1, substr_count($html, '<iframe'), 'Only video testimonials may render an iframe.');
        $this->assertTrue(!str_contains($html, 'Draft must not render'), 'Draft testimonial must remain hidden.');
        $this->assertTrue(!str_contains($html, 'overflow:'), 'Renderer must not create a nested scrolling container.');

        fwrite(STDOUT, "PASS: {$this->assertions} testimonial assertions\n");
    }
}

try {
    (new TestimonialsTest())->run();
} catch (Throwable $error) {
    fwrite(STDERR, 'FAIL: ' . $error->getMessage() . "\n");
    exit(1);
}

