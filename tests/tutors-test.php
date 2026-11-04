<?php

declare(strict_types=1);

require_once __DIR__ . '/content-test-bootstrap.php';

final class TutorsTest extends TbtContentTestCase
{
    public function run(): void
    {
        $base = dirname(__DIR__) . '/wordpress/wp-content/themes/thatbiotutor-hello-child/inc/';
        foreach (['content-types.php', 'tutors.php'] as $file) {
            $this->assertTrue(is_file($base . $file), "{$file} must exist.");
            require_once $base . $file;
        }

        $GLOBALS['tbt_content_posts'] = [
            tbt_test_post(12, 'tbt_tutor', 'Mr Keefe', 2),
            tbt_test_post(10, 'tbt_tutor', 'Ms Chelsea', 1),
            tbt_test_post(11, 'tbt_tutor', 'Retired Tutor', 0, 'draft'),
        ];
        $GLOBALS['tbt_content_meta'] = [
            10 => ['tbt_tutor_role' => 'Biology Tutor', 'tbt_tutor_short_bio' => 'Patient and clear.'],
            12 => ['tbt_tutor_role' => 'Founder', 'tbt_tutor_short_bio' => 'Makes Biology click.'],
            11 => ['tbt_tutor_role' => 'Retired', 'tbt_tutor_short_bio' => 'Hidden.'],
        ];

        $records = tbt_get_tutors();
        $this->assertSame([10, 12], array_column($records, 'id'), 'Tutors must be deterministically ordered and Draft tutors hidden.');
        $html = $this->capture(static fn () => tbt_render_tutors());
        $this->assertTrue(str_contains($html, 'Ms Chelsea'), 'Active tutor must render.');
        $this->assertTrue(str_contains($html, 'Mr Keefe'), 'Second active tutor must render.');
        $this->assertTrue(!str_contains($html, 'Retired Tutor'), 'Draft tutor must not render.');

        $aboutHtml = $this->capture(static fn () => tbt_render_tutors([
            'variant' => 'about',
            'show_roles' => false,
            'display_names' => ['Mr Keefe' => 'Mr Keefe (Founder)'],
            'profile_links' => ['Ms Chelsea' => 'https://www.linkedin.com/in/xiang-ting-chee/'],
        ]));
        $this->assertTrue(str_contains($aboutHtml, '>Mr Keefe (Founder)</h3>'), 'About tutors must support the original combined display heading.');
        $this->assertTrue(str_contains($aboutHtml, 'https://www.linkedin.com/in/xiang-ting-chee/'), 'About tutors must support the original public profile link.');
        $this->assertTrue(str_contains($aboutHtml, 'target="_blank"') && str_contains($aboutHtml, 'rel="noopener noreferrer"'), 'Tutor profile links must open safely.');

        fwrite(STDOUT, "PASS: {$this->assertions} tutor assertions\n");
    }
}

try {
    (new TutorsTest())->run();
} catch (Throwable $error) {
    fwrite(STDERR, 'FAIL: ' . $error->getMessage() . "\n");
    exit(1);
}

