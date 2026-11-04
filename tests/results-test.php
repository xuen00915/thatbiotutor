<?php

declare(strict_types=1);

require_once __DIR__ . '/content-test-bootstrap.php';

final class ResultsTest extends TbtContentTestCase
{
    public function run(): void
    {
        $base = dirname(__DIR__) . '/wordpress/wp-content/themes/thatbiotutor-hello-child/inc/';
        foreach (['content-types.php', 'results.php'] as $file) {
            $this->assertTrue(is_file($base . $file), "{$file} must exist.");
            require_once $base . $file;
        }

        $GLOBALS['tbt_content_posts'] = [
            tbt_test_post(32, 'tbt_result', 'Result B', 1),
            tbt_test_post(31, 'tbt_result', 'Result A', 1),
            tbt_test_post(33, 'tbt_result', 'Draft result', 0, 'draft'),
        ];
        $GLOBALS['tbt_content_meta'] = [
            31 => ['tbt_result_student' => 'Student A', 'tbt_result_before' => 'C6', 'tbt_result_after' => 'A2', 'tbt_result_year' => '2025'],
            32 => ['tbt_result_student' => 'Student B', 'tbt_result_before' => 'F9', 'tbt_result_after' => 'A1', 'tbt_result_year' => '2026'],
            33 => ['tbt_result_student' => 'Hidden', 'tbt_result_before' => 'D7', 'tbt_result_after' => 'B3'],
        ];

        $records = tbt_get_results();
        $this->assertSame([31, 32], array_column($records, 'id'), 'Results must sort by order/title/ID and exclude Drafts.');
        $this->assertSame('C6', $records[0]['before'], 'Before grade must normalize.');
        $this->assertSame('A2', $records[0]['after'], 'After grade must normalize.');
        $this->assertSame('2025', $records[0]['year'], 'Year must normalize.');
        $html = $this->capture(static fn () => tbt_render_results());
        $this->assertSame(2, substr_count($html, '<article class="tbt-result">'), 'Published result records must render through the shared public renderer.');

        fwrite(STDOUT, "PASS: {$this->assertions} result assertions\n");
    }
}

try {
    (new ResultsTest())->run();
} catch (Throwable $error) {
    fwrite(STDERR, 'FAIL: ' . $error->getMessage() . "\n");
    exit(1);
}
