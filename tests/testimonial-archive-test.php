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
        $this->assertSame(61, count($videos), 'Seeded video count must match the 61 year-archive embeds in the authoritative crawl.');
        $quoteRecords = array_values(array_filter($records, static fn(array $r): bool => ($r['fields']['tbt_quote'] ?? '') !== ''));
        $this->assertTrue(count($quoteRecords) >= 350, 'The complete public wording archive must remain editable structured records.');
        $this->assertSame(0, count(array_filter($videos, static fn(array $r): bool => trim((string)($r['fields']['tbt_quote'] ?? '')) === '')), 'Every year-archive video must retain its accompanying live transcript.');
        $aliciaRecords = array_values(array_filter($records, static fn(array $r): bool => ($r['fields']['tbt_display_group'] ?? '') === '2025' && str_starts_with((string)($r['fields']['tbt_student_name'] ?? ''), 'Alicia')));
        $this->assertSame(1, count($aliciaRecords), 'Alicia must be one combined video-and-transcript record, not disconnected duplicate records.');
        $this->assertTrue(
            str_contains((string)($aliciaRecords[0]['fields']['tbt_quote'] ?? ''), "I actually struggled a lot with answering open-ended questions")
            && str_contains((string)($aliciaRecords[0]['fields']['tbt_quote'] ?? ''), 'I was confident going into the exam.'),
            'Alicia must retain the full wording visible on the public page.'
        );
        $this->assertTrue(count(array_unique(array_column($records, 'seed_key'))) === count($records), 'Every seeded testimonial must have a unique identity.');
        $groups = array_unique(array_map(static fn(array $r): string => (string)($r['fields']['tbt_display_group'] ?? ''), $records));
        foreach (['2025', '2024', '2023', '2022', '2021', 'notes-reviews'] as $group) $this->assertTrue(in_array($group, $groups, true), "Archive group {$group} must be seeded.");
        $groupCounts = array_count_values(array_map(static fn(array $r): string => (string)($r['fields']['tbt_display_group'] ?? ''), $records));
        $videoGroupCounts = array_count_values(array_map(static fn(array $r): string => (string)($r['fields']['tbt_display_group'] ?? ''), $videos));
        $this->assertSame(['2025' => 7, '2024' => 16, '2023' => 16, '2022' => 19, '2021' => 3], $videoGroupCounts, 'Video stories must preserve their live year archive mapping.');
        foreach (['2025' => 16, '2024' => 65, '2023' => 25, '2022' => 19, '2021' => 11] as $year => $expectedCount) {
            $this->assertSame($expectedCount, $groupCounts[$year] ?? 0, "Class of {$year} must preserve every live story and written review exactly once.");
        }
        $this->assertTrue(($groupCounts['2024'] ?? 0) >= 60, 'The long 2024 Results archive must not be truncated.');
        $this->assertTrue(($groupCounts['notes-reviews-bottom'] ?? 0) >= 200, 'The long Free Notes review archive must not be truncated.');

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
        $this->assertSame(61, substr_count($html, '<iframe'), 'Only the 61 video records may render iframes.');
        $this->assertSame(61, substr_count($html, 'loading="lazy"'), 'Every video must lazy-load.');
        $this->assertSame(61, substr_count($html, 'allowfullscreen'), 'Every video must allow fullscreen.');
        $this->assertSame(61, substr_count($html, 'scrolling="no"'), 'Every video must explicitly disable iframe scrolling.');
        $aliciaVideoPosition = strpos($html, 'RJxR3ONbylQ');
        $aliciaArticleStart = strrpos(substr($html, 0, (int) $aliciaVideoPosition), '<article class="tbt-testimonial');
        $aliciaArticleEnd = strpos($html, '</article>', (int) $aliciaVideoPosition);
        $aliciaArticle = substr($html, (int) $aliciaArticleStart, (int) $aliciaArticleEnd - (int) $aliciaArticleStart);
        $this->assertTrue(
            str_contains($aliciaArticle, 'I actually struggled a lot with answering open-ended questions')
            && strpos($aliciaArticle, 'RJxR3ONbylQ') < strpos($aliciaArticle, '<blockquote>'),
            'Alicia video and wording must render in one card with the video before its transcript.'
        );
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

        $this->assertTrue(
            str_contains($template, "'<span>Students Are Saying</span>'"),
            'Featured heading must preserve the public green emphasis without turning the heading into artwork.'
        );
        $this->assertSame(3, substr_count($template, 'class="tbt-testimonials-button'), 'Archive must expose the public top, pre-2021, and closing WhatsApp calls to action.');
        $this->assertSame(2, substr_count($template, '>WhatsApp Us Now<'), 'The two lower archive calls to action must use the public label.');
        foreach (['2025', '2024', '2023'] as $year) {
            $this->assertTrue(
                preg_match('/<h2 class="screen-reader-text">Class of ' . $year . '<\/h2>/', $template) === 1,
                "Class of {$year} must remain semantic without adding a visible heading absent from the public composition."
            );
        }
        foreach (['2022', '2021'] as $year) {
            $this->assertTrue(
                preg_match('/<h2>Class of ' . $year . '<\/h2>/', $template) === 1,
                "Class of {$year} must keep its visible heading inside the public dark results band."
            );
        }
        $this->assertTrue(str_contains($template, '100% of our Class of 2021 improved their grade<br>in 6 months or less'), 'Class of 2021 results summary must remain visible live text.');
        $this->assertSame(3, substr_count($template, 'class="tbt-testimonials-artwork-band'), 'The 2024, 2023, and 2022 result artwork must sit in full-width dark bands.');

        $css = (string) file_get_contents(dirname(__DIR__) . '/wordpress/wp-content/themes/thatbiotutor-hello-child/assets/css/pages-testimonials.css');
        $this->assertTrue(
            preg_match('/\.tbt-testimonials-artwork-band\s*\{[^}]*background:\s*#19221a;[^}]*\}/s', $css) === 1,
            'Result artwork bands must use the shared brand ink field.'
        );
        $this->assertTrue(
            preg_match('/\.tbt-testimonials-year \.tbt-testimonial\s*\{[^}]*text-align:\s*center;[^}]*\}/s', $css) === 1,
            'Archive review compositions must center semantic quote content like the public page.'
        );
        $this->assertTrue(
            preg_match('/\.tbt-testimonials-year \.tbt-testimonial__stars\s*\{[^}]*order:\s*3;[^}]*\}/s', $css) === 1,
            'Archive stars must visually follow each student name while preserving renderer semantics.'
        );
        $this->assertTrue(
            preg_match('/\.tbt-testimonials-artwork--landscape\s*\{[^}]*grid-template-columns:\s*minmax\(0,\s*1fr\);[^}]*\}/s', $css) === 1,
            'Lazy landscape artwork must retain a full-width grid track before its intrinsic image dimensions load.'
        );
        foreach (['2025' => 170, '2024' => 290, '2023' => 377] as $year => $gap) {
            $this->assertTrue(
                preg_match('/#class-of-' . $year . ' \.tbt-testimonials\s*\{[^}]*gap:\s*' . $gap . 'px;[^}]*\}/s', $css) === 1,
                "Class of {$year} desktop review rhythm must keep its public year-band boundary."
            );
        }
        $this->assertTrue(
            preg_match('/\.tbt-testimonials-artwork-band--2022 \.tbt-testimonials-artwork\s*\{[^}]*margin-top:\s*213px;[^}]*\}/s', $css) === 1,
            'Class of 2022 artwork must retain the vertical footprint of the public result badge above it.'
        );
        $this->assertTrue(
            preg_match('/\.tbt-testimonials-results-band p\s*\{[^}]*margin:\s*182px 0 0;[^}]*\}/s', $css) === 1,
            'Class of 2021 summary must keep the public 349px result-band height.'
        );
        $this->assertTrue(
            preg_match('/\.tbt-story-heading h3\s*\{[^}]*font-weight:\s*700;[^}]*\}/s', $css) === 1,
            'Student Stories headings must retain the public bold hierarchy.'
        );
        $this->assertTrue(
            preg_match('/\.tbt-testimonials-artwork-band--2022 h2, \.tbt-testimonials-results-band h2\s*\{[^}]*font-weight:\s*700;[^}]*\}/s', $css) === 1,
            'Visible Class of 2022 and Class of 2021 headings must retain the public bold hierarchy.'
        );
        fwrite(STDOUT, "PASS: {$this->assertions} testimonial-archive assertions\n");
    }
}
try { (new TestimonialArchiveTest())->run(); } catch (Throwable $e) { fwrite(STDERR, 'FAIL: '.$e->getMessage()."\n"); exit(1); }

