<?php

declare(strict_types=1);

require_once __DIR__ . '/content-test-bootstrap.php';
$inc = dirname(__DIR__) . '/wordpress/wp-content/themes/thatbiotutor-hello-child/inc/';
require_once $inc . 'reference-content.php';
require_once $inc . 'content-types.php';
require_once $inc . 'testimonials.php';

final class TestimonialArchiveTest extends TbtContentTestCase
{
    public function run(): void
    {
        $records = tbt_reference_content()['records']['testimonials'] ?? [];
        $videos = array_values(array_filter($records, static fn(array $r): bool => ($r['fields']['tbt_video_url'] ?? '') !== ''));
        $texts = array_values(array_filter($records, static fn(array $r): bool => ($r['fields']['tbt_video_url'] ?? '') === ''));
        $this->assertSame(24, count($videos), 'Seeded video count must match the 24 unique embeds in the authoritative crawl.');
        $this->assertTrue(count($texts) >= 7, 'Text-only Home and Notes reviews must remain editable structured records.');
        $this->assertTrue(count(array_unique(array_column($records, 'seed_key'))) === count($records), 'Every seeded testimonial must have a unique identity.');
        $groups = array_unique(array_map(static fn(array $r): string => (string)($r['fields']['tbt_display_group'] ?? ''), $records));
        foreach (['2025', '2024', '2023', '2022', '2021', 'notes-reviews'] as $group) $this->assertTrue(in_array($group, $groups, true), "Archive group {$group} must be seeded.");

        $GLOBALS['tbt_content_posts'] = [];
        $GLOBALS['tbt_content_meta'] = [];
        foreach ($records as $index => $record) {
            $id = 1000 + $index;
            $GLOBALS['tbt_content_posts'][] = tbt_test_post($id, 'tbt_testimonial', $record['title'], (int)$record['menu_order']);
            $GLOBALS['tbt_content_meta'][$id] = $record['fields'];
        }
        $normalized = tbt_get_testimonials();
        $this->assertSame(count($records), count($normalized), 'Every seeded testimonial must normalize once.');
        $html = $this->capture(static fn() => tbt_render_testimonials());
        $this->assertSame(count($records), substr_count($html, '<article class="tbt-testimonial'), 'Every record must render once.');
        $this->assertSame(24, substr_count($html, '<iframe'), 'Only the 24 video records may render iframes.');
        $this->assertSame(24, substr_count($html, 'loading="lazy"'), 'Every video must lazy-load.');
        $this->assertSame(24, substr_count($html, 'allowfullscreen'), 'Every video must allow fullscreen.');
        $this->assertSame(24, substr_count($html, 'scrolling="no"'), 'Every video must explicitly disable iframe scrolling.');
        $this->assertTrue(!str_contains($html, 'src=""'), 'Text-only records must not render empty media.');
        $this->assertTrue(!str_contains($html, 'overflow:'), 'Archive renderer must not create nested content scrolling.');
        $this->assertTrue(str_contains($html, 'Anisatusyifa') && str_contains($html, 'Jezelle'), 'Unicode-safe Notes review names must survive output.');

        $template = (string) file_get_contents(dirname(__DIR__) . '/wordpress/wp-content/themes/thatbiotutor-hello-child/page-testimonials.php');
        preg_match_all('/data-section="([^"]+)"/', $template, $sections);
        $this->assertSame(['hero', 'featured', '2025', '2024', '2023', '2022', '2021'], $sections[1], 'Archive must follow the current public year-group order.');
        $this->assertTrue(!str_contains($template, 'tbt-archive-jump'), 'Archive must not add locally invented year navigation.');
        foreach (['curated_heroes_testimonials_original', 'curated_testimonials_results_2025_01', 'curated_testimonials_results_2025_02', 'curated_testimonials_results_2024_reviews', 'curated_testimonials_results_2024_stats', 'curated_testimonials_results_2024_01', 'curated_testimonials_results_2024_02', 'curated_testimonials_results_2023_01', 'curated_testimonials_results_2023_02', 'curated_testimonials_results_2023_03', 'curated_testimonials_results_2022_01'] as $mediaKey) {
            $this->assertTrue(str_contains($template, $mediaKey), "Archive must render public media {$mediaKey}.");
        }
        $this->assertTrue(str_contains($template, 'ThatBioTutor Exposed: What Students Are Saying'), 'Archive must retain the public featured video title.');
        $this->assertTrue(str_contains($template, 'scrolling="no"'), 'Featured archive video must explicitly disable iframe scrolling.');
        fwrite(STDOUT, "PASS: {$this->assertions} testimonial-archive assertions\n");
    }
}
try { (new TestimonialArchiveTest())->run(); } catch (Throwable $e) { fwrite(STDERR, 'FAIL: '.$e->getMessage()."\n"); exit(1); }

