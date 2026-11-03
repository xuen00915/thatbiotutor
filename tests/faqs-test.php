<?php

declare(strict_types=1);

require_once __DIR__ . '/content-test-bootstrap.php';

final class FaqsTest extends TbtContentTestCase
{
    public function run(): void
    {
        $base = dirname(__DIR__) . '/wordpress/wp-content/themes/thatbiotutor-hello-child/inc/';
        foreach (['content-types.php', 'faqs.php'] as $file) {
            $this->assertTrue(is_file($base . $file), "{$file} must exist.");
            require_once $base . $file;
        }

        $longAnswer = str_repeat('A detailed answer with context. ', 30) . '结束。';
        $GLOBALS['tbt_content_posts'] = [
            tbt_test_post(22, 'tbt_faq', 'Second question?', 2, 'publish', $longAnswer),
            tbt_test_post(21, 'tbt_faq', 'First question?', 1, 'publish', 'First answer.'),
            tbt_test_post(23, 'tbt_faq', 'Other collection?', 0, 'publish', 'Elsewhere.'),
            tbt_test_post(24, 'tbt_faq', 'Draft question?', 0, 'draft', 'Hidden.'),
        ];
        $GLOBALS['tbt_content_meta'] = [
            21 => ['tbt_faq_collection' => 'home'],
            22 => ['tbt_faq_collection' => 'home'],
            23 => ['tbt_faq_collection' => 'notes'],
            24 => ['tbt_faq_collection' => 'home'],
        ];

        $records = tbt_get_faqs('home');
        $this->assertSame([21, 22], array_column($records, 'id'), 'FAQ query must filter by collection, hide Drafts, and sort deterministically.');
        $html = $this->capture(static fn () => tbt_render_faqs('home'));
        $this->assertTrue(str_contains($html, 'data-faq-button'), 'FAQ renderer must expose an accessible interaction hook.');
        $this->assertTrue(str_contains($html, 'aria-expanded="false"'), 'FAQ controls must begin collapsed.');
        $this->assertTrue(str_contains($html, '结束。'), 'Long Unicode answers must render intact.');
        $this->assertTrue(!str_contains($html, 'Other collection?'), 'Unassigned collection records must not leak into the page.');
        $this->assertTrue(!str_contains($html, 'Draft question?'), 'Draft FAQs must remain hidden.');

        fwrite(STDOUT, "PASS: {$this->assertions} FAQ assertions\n");
    }
}

try {
    (new FaqsTest())->run();
} catch (Throwable $error) {
    fwrite(STDERR, 'FAIL: ' . $error->getMessage() . "\n");
    exit(1);
}

