<?php

declare(strict_types=1);

require_once __DIR__ . '/page-render-bootstrap.php';

final class ResourcePagesTest
{
    private int $assertions = 0;

    public function run(): void
    {
        foreach (['page-testimonials.php', 'page-notes.php', 'page-celldivision.php'] as $file) {
            $this->assertTrue(is_file(TBT_THEME_DIR . '/' . $file), "{$file} must exist.");
        }
        $testimonials = html_entity_decode(tbt_test_render_template('page-testimonials.php'));
        $notes = html_entity_decode(tbt_test_render_template('page-notes.php'));
        $cell = html_entity_decode(tbt_test_render_template('page-celldivision.php'));
        foreach ([$testimonials, $notes, $cell] as $html) $this->assertSame(1, substr_count($html, '<h1'), 'Every resource template must have one H1.');

        foreach (['Results & Testimonials', 'What Students Are Saying About ThatBioTutor', 'Student Stories', 'Class of 2025', 'Class of 2022', 'Class of 2021'] as $marker) $this->assertTrue(str_contains($testimonials, $marker), "Testimonials must contain {$marker}.");
        $this->assertTrue(str_contains($testimonials, 'data-archive-group'), 'Long testimonial archive must expose grouping anchors.');

        preg_match_all('/data-section="([^"]+)"/', $notes, $noteSections);
        $this->assertSame(['hero', 'reviews-top', 'access-intro', 'fit', 'benefits', 'why-free', 'catch', 'download', 'reviews-bottom'], $noteSections[1], 'Notes sections must follow the public page order.');
        $last = -1;
        foreach (['Free O Level Biology Notes Singapore', 'For Pure / G3 Biology', 'What Students Are Saying...', 'Hey Pure Bio Student, is this you?', "ThatBioTutor's Notes", 'Why are you giving your notes for FREE?', "This sounds too good to be true. What's the catch?", 'How do I download it?'] as $marker) {
            $position = strpos($notes, $marker, $last + 1); $this->assertTrue($position !== false, "Notes must contain {$marker}."); $this->assertTrue($position > $last, "Notes section {$marker} must stay in original order."); $last = $position;
        }
        $this->assertSame(3, substr_count($notes, 'data-carousel-slide'), 'Notes hero must include three slides.');
        $this->assertTrue(str_contains($notes, 'data-carousel-prev') && str_contains($notes, 'data-carousel-next') && str_contains($notes, 'data-carousel-status'), 'Notes slider must have accessible controls and status.');
        $this->assertSame(4, substr_count($notes, 'data-notes-benefit'), 'Notes must explain four benefits.');
        $this->assertTrue(str_contains($notes, '7,000+ downloads'), 'Notes must preserve its download-count proof.');
        $this->assertTrue(str_contains($notes, 'id="notes-download"'), 'Download CTA must resolve to the disclosed access section.');
        $this->assertTrue(str_contains($notes, 'data-testimonial-renderer'), 'Notes must render its complete editable review collection.');
        foreach (range(1, 3) as $index) $this->assertTrue(str_contains($notes, sprintf('curated_notes_notes_slide_%02d', $index)), "Notes slide {$index} must use approved local media.");
        $this->assertTrue(!str_contains($notes, '<form'), 'Resource page must not invent a replacement data-collection form.');

        foreach (['Only For 2023 Sec 4 Pure Bio Students!', 'IMPORTANT:', 'How do I access it?', 'What Students Are Saying About The Crash Course:'] as $marker) $this->assertTrue(str_contains($cell, $marker), "Cell Division must contain {$marker}.");
        preg_match_all('/data-section="([^"]+)"/', $cell, $cellSections);
        $this->assertSame(['hero', 'problems', 'features', 'important', 'access', 'reviews'], $cellSections[1], 'Cell Division sections must follow the public resource order.');
        $this->assertSame(5, substr_count($cell, 'data-carousel-slide'), 'Cell Division hero must preserve all five public slides.');
        foreach (range(1, 5) as $index) $this->assertTrue(str_contains($cell, sprintf('curated_celldivision_slide_%02d', $index)), "Cell Division slide {$index} must use approved local media.");
        foreach (['curated_celldivision_replayable_video', 'curated_celldivision_analogy', 'curated_celldivision_memory_shortcuts'] as $mediaKey) $this->assertTrue(str_contains($cell, $mediaKey), "Cell Division must retain {$mediaKey}.");
        $this->assertTrue(str_contains($cell, '/contact/') && str_contains($cell, 'historical 2023'), 'Cell Division access must clearly disclose its dated external/contact boundary.');
        foreach ([$notes, $cell] as $html) $this->assertTrue(!str_contains($html, 'static.wixstatic.com'), 'Resource page must use local media.');

        fwrite(STDOUT, "PASS: {$this->assertions} resource-page assertions\n");
    }
    private function assertSame(mixed $e, mixed $a, string $m): void { $this->assertions++; if ($e !== $a) throw new RuntimeException($m); }
    private function assertTrue(bool $c, string $m): void { $this->assertions++; if (!$c) throw new RuntimeException($m); }
}
try { (new ResourcePagesTest())->run(); } catch (Throwable $e) { fwrite(STDERR, 'FAIL: '.$e->getMessage()."\n"); exit(1); }

