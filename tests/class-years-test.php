<?php

declare(strict_types=1);

require_once __DIR__ . '/content-test-bootstrap.php';
require_once dirname(__DIR__) . '/wordpress/wp-content/themes/thatbiotutor-hello-child/inc/reference-content.php';

function tbt_normalize_image(mixed $value): ?array
{
    return is_array($value) && isset($value['url']) ? $value + ['alt' => '', 'width' => 0, 'height' => 0] : null;
}

function tbt_media(string $key): array
{
    return ['url' => 'https://example.test/' . $key . '.webp', 'alt' => 'Bundled ' . $key, 'width' => 800, 'height' => 1200];
}

function tbt_render_media_image(string $key, string $class = '', array $attributes = []): void
{
    echo '<img data-media-key="' . esc_attr($key) . '" src="' . esc_attr(tbt_media($key)['url']) . '">';
}

/** Class years are client-owned records: adding a year adds a section and a menu entry with no template change. */
final class ClassYearsTest extends TbtContentTestCase
{
    public function run(): void
    {
        $base = dirname(__DIR__) . '/wordpress/wp-content/themes/thatbiotutor-hello-child/inc/';
        foreach (['acf-fields.php', 'content-types.php', 'class-years.php'] as $file) {
            $this->assertTrue(is_file($base . $file), "{$file} must exist.");
            require_once $base . $file;
        }

        $this->assertTrue(isset($GLOBALS['tbt_registered_post_types']) && in_array('tbt_class_year', tbt_structured_post_types(), true), 'Class Year must be a managed content type.');
        $groups = array_column(tbt_acf_content_groups(), null, 'key');
        $names = array_column($groups['group_tbt_class_year_fields']['fields'] ?? [], 'name');
        foreach (['tbt_year_value', 'tbt_year_heading', 'tbt_year_summary', 'tbt_year_growth_icon', 'tbt_year_show_advice', 'tbt_year_whatsapp_button', 'tbt_year_poster_1', 'tbt_year_table_1'] as $name) {
            $this->assertTrue(in_array($name, $names, true), "Class Year must expose {$name}.");
        }

        // The five shipped years are seeded as records.
        $seeded = tbt_reference_content()['records']['class_years'] ?? [];
        $this->assertSame(['2025', '2024', '2023', '2022', '2021'], array_map(static fn (array $r): string => $r['fields']['tbt_year_value'], $seeded), 'The five shipped class years must be seeded.');

        $GLOBALS['tbt_content_posts'] = [
            tbt_test_post(1, 'tbt_class_year', 'Class of 2024', 20),
            tbt_test_post(2, 'tbt_class_year', 'Class of 2026', 0),
            tbt_test_post(3, 'tbt_class_year', 'Class of 2025', 10),
            tbt_test_post(4, 'tbt_class_year', 'Hidden draft', 5, 'draft'),
            tbt_test_post(5, 'tbt_class_year', 'Broken', 6),
            tbt_test_post(6, 'tbt_class_year', 'Class of 2021', 50),
        ];
        $GLOBALS['tbt_content_meta'] = [
            1 => ['tbt_year_value' => '2024', 'tbt_year_heading' => 'Class of 2024', '_tbt_seed_key' => 'class-year-2024'],
            2 => ['tbt_year_value' => '2026', 'tbt_year_whatsapp_button' => '1', 'tbt_year_poster_1' => ['url' => 'https://example.test/new-poster.webp', 'alt' => 'New poster', 'width' => 900, 'height' => 1600]],
            3 => ['tbt_year_value' => '2025', 'tbt_year_poster_1' => ['url' => 'https://example.test/replaced.webp', 'alt' => '', 'width' => 900, 'height' => 1600], '_tbt_seed_key' => 'class-year-2025'],
            4 => ['tbt_year_value' => '2099'],
            5 => ['tbt_year_value' => '<script>'],
            6 => ['_tbt_seed_key' => 'class-year-2021'],
        ];

        $years = tbt_get_class_years();
        $this->assertSame(['2026', '2025', '2024', '2021'], array_column($years, 'year'), 'Years must be newest first; drafts and invalid years hidden.');
        $byYear = array_column($years, null, 'year');

        // Heading defaults to "Class of YYYY" when the client leaves it empty.
        $this->assertSame('Class of 2026', $byYear['2026']['heading'], 'An empty heading must default to Class of YYYY.');

        // A brand-new year has no bundled pictures: only what the client uploads shows.
        $this->assertSame(1, count($byYear['2026']['posters']), 'A new year shows only the uploaded poster.');
        $this->assertSame([], $byYear['2026']['tables'], 'A new year has no table pictures until uploaded.');
        $this->assertSame(['poster' => 'Result poster', 'stories' => 'Student stories', 'google-reviews' => 'Google reviews'], tbt_class_year_menu_parts($byYear['2026']), 'The menu lists only the parts a year has.');
        $this->assertTrue($byYear['2026']['whatsapp_button'] === true && $byYear['2024']['whatsapp_button'] === false, 'The WhatsApp button flag must be per year.');

        // A shipped year keeps its bundled picture in each position the client has not replaced.
        $posters2025 = $byYear['2025']['posters'];
        $this->assertSame(2, count($posters2025), '2025 keeps two poster positions.');
        $this->assertSame('https://example.test/replaced.webp', $posters2025[0]['image']['url'], 'An upload replaces the bundled picture in its position.');
        $this->assertSame('curated_testimonials_results_2025_02', $posters2025[1]['key'], 'The other position keeps its bundled picture.');
        $html = $this->capture(static fn () => tbt_render_class_year_pictures($posters2025));
        $this->assertTrue(str_contains($html, 'src="https://example.test/replaced.webp"') && str_contains($html, 'data-media-key="curated_testimonials_results_2025_02"'), 'Both uploaded and bundled pictures must render.');
        $this->assertSame(['poster', 'table', 'stories', 'google-reviews'], array_keys(tbt_class_year_menu_parts($byYear['2024'])), '2024 lists poster and table.');

        // A record that was never saved falls back to the approved reference values.
        $this->assertSame('100% of our Class of 2021 improved their grade in 6 months or less', $byYear['2021']['summary'], 'The 2021 headline must come from the seeded record.');
        $this->assertTrue($byYear['2021']['growth_icon'] && !$byYear['2021']['show_advice'] && $byYear['2021']['whatsapp_button'], '2021 flags must come from the seeded record.');
        $this->assertTrue($byYear['2024']['show_advice'] === true, 'Student-stories advice defaults on for a shipped year.');

        fwrite(STDOUT, "PASS: {$this->assertions} class-year assertions\n");
    }
}

try {
    (new ClassYearsTest())->run();
} catch (Throwable $error) {
    fwrite(STDERR, 'FAIL: ' . $error->getMessage() . "\n");
    exit(1);
}
