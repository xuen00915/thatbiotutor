<?php

declare(strict_types=1);

require_once __DIR__ . '/content-test-bootstrap.php';

function tbt_normalize_image(mixed $value): ?array
{
    return is_array($value) ? $value : null;
}

function tbt_render_media_image(string $mediaKey, string $class = '', array $attributes = []): void
{
    echo '<img data-media-key="' . esc_attr($mediaKey) . '" class="' . esc_attr($class) . '" alt="' . esc_attr($mediaKey) . '">';
}

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
            10 => [
                'tbt_tutor_role' => 'Biology Tutor',
                'tbt_tutor_short_bio' => 'Patient and clear.',
                'tbt_tutor_portrait' => [
                    'url' => 'https://example.test/chelsea.webp',
                    'alt' => 'Ms Chelsea portrait',
                    'width' => 460,
                    'height' => 460,
                ],
            ],
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
            'supplemental_media' => [
                'Ms Chelsea' => ['chelsea-certificate', 'chelsea-credentials'],
                'Mr Keefe' => ['keefe-certificate'],
            ],
        ]));
        $this->assertTrue(str_contains($aboutHtml, '>Mr Keefe (Founder)</h3>'), 'About tutors must support the original combined display heading.');
        $this->assertTrue(str_contains($aboutHtml, '<div class="tbt-tutor__visual">'), 'About tutors must group portrait media in a visual column.');
        $this->assertTrue(str_contains($aboutHtml, '<div class="tbt-tutor__details">'), 'About tutors must group names and credentials in a details column.');
        $this->assertTrue(str_contains($aboutHtml, 'class="tbt-tutor__portrait" src="https://example.test/chelsea.webp"'), 'Direct About portrait URLs must receive the shared portrait class.');
        $this->assertTrue(str_contains($aboutHtml, 'https://www.linkedin.com/in/xiang-ting-chee/'), 'About tutors must support the original public profile link.');
        $this->assertTrue(str_contains($aboutHtml, 'target="_blank"') && str_contains($aboutHtml, 'rel="noopener noreferrer"'), 'Tutor profile links must open safely.');
        $this->assertTrue(str_contains($aboutHtml, 'tbt-tutor--has-documents'), 'About tutors with certificates must opt into the three-column layout.');
        $this->assertTrue(str_contains($aboutHtml, '<aside class="tbt-tutor__certificates"'), 'About tutor certificates must render in a dedicated third column.');
        $this->assertTrue(str_contains($aboutHtml, 'data-carousel data-carousel-autoplay="4500"'), 'Multiple certificates must use the shared autoplay carousel.');
        $this->assertSame(2, substr_count($aboutHtml, 'data-carousel-slide'), 'Chelsea must render two certificate slides.');
        $this->assertSame(1, substr_count($aboutHtml, 'data-carousel-prev'), 'Only multi-certificate tutors need carousel controls.');
        $this->assertSame(1, substr_count($aboutHtml, 'data-carousel-next'), 'Only multi-certificate tutors need carousel controls.');
        $this->assertTrue(str_contains($aboutHtml, 'aria-label="Ms Chelsea certificates"'), 'Certificate carousel must have an accessible tutor-specific label.');
        $this->assertSame(3, substr_count($aboutHtml, 'data-certificate-lightbox-trigger'), 'Every certificate must be an enlarge control.');
        $this->assertSame(3, substr_count($aboutHtml, 'aria-haspopup="dialog"'), 'Certificate controls must announce the modal interaction.');
        $this->assertTrue(str_contains($aboutHtml, 'aria-label="Enlarge certificate 1 for Ms Chelsea"'), 'Certificate controls must identify the tutor and document.');
        $this->assertTrue(!str_contains($html, 'tbt-tutor__visual') && !str_contains($html, 'tbt-tutor__details'), 'Compact About wrappers must not change the shared card variant.');

        fwrite(STDOUT, "PASS: {$this->assertions} tutor assertions\n");
    }
}

try {
    (new TutorsTest())->run();
} catch (Throwable $error) {
    fwrite(STDERR, 'FAIL: ' . $error->getMessage() . "\n");
    exit(1);
}

