<?php

declare(strict_types=1);

require_once __DIR__ . '/page-render-bootstrap.php';

final class ResourcePagesTest
{
    private int $assertions = 0;

    public function run(): void
    {
        foreach (['page-notes.php', 'page-celldivision.php'] as $file) {
            $this->assertTrue(is_file(TBT_THEME_DIR . '/' . $file), "{$file} must exist.");
        }
        $notes = html_entity_decode(tbt_test_render_template('page-notes.php'));
        $cell = html_entity_decode(tbt_test_render_template('page-celldivision.php'));
        foreach ([$notes, $cell] as $html) $this->assertSame(1, substr_count($html, '<h1'), 'Every resource template must have one H1.');

        preg_match_all('/data-section="([^"]+)"/', $notes, $noteSections);
        $this->assertSame(['hero', 'reviews-top', 'access-intro', 'fit', 'benefits', 'why-free', 'catch', 'download', 'reviews-bottom'], $noteSections[1], 'Notes sections must follow the public page order.');
        $notesText = strip_tags($notes);
        $last = -1;
        foreach (['Free O Level Biology Notes Singapore', 'For Pure / G3 Biology', 'What Students Are Saying...', 'Hey Pure Bio Student, is this you?', "ThatBioTutor's Notes", 'Why are you giving your notes for FREE?', "This sounds too good to be true. What's the catch?", 'How do I download it?'] as $marker) {
            $position = strpos($notesText, $marker, $last + 1); $this->assertTrue($position !== false, "Notes must contain {$marker}."); $this->assertTrue($position > $last, "Notes section {$marker} must stay in original order."); $last = $position;
        }
        $this->assertSame(3, substr_count($notes, 'data-carousel-slide'), 'Notes hero must include three slides.');
        $this->assertTrue(str_contains($notes, 'data-carousel-prev') && str_contains($notes, 'data-carousel-next') && str_contains($notes, 'data-carousel-status'), 'Notes slider must have accessible controls and status.');
        $this->assertSame(4, substr_count($notes, 'data-notes-benefit'), 'Notes must explain four benefits.');
        $this->assertTrue(str_contains($notes, '7,000+ downloads'), 'Notes must preserve its download-count proof.');
        $this->assertTrue(str_contains($notes, 'id="notes-download"'), 'Download CTA must resolve to the disclosed access section.');
        $this->assertTrue(str_contains($notes, 'data-testimonial-renderer'), 'Notes must render its complete editable review collection.');
        $this->assertTrue(str_contains($notes, 'data-resource-portrait="notes-access"'), 'Notes access area must preserve the public Mr Keefe portrait.');
        $this->assertTrue(str_contains($notes, 'alt="Mr Keefe from ThatBioTutor"'), 'Notes access portrait must have useful alternative text.');
        $this->assertTrue(str_contains($notes, 'tbt-notes-details'), 'Notes FAQ and download copy must share the public dark details band.');
        $this->assertTrue(str_contains($notes, '<strong>thick and confusing</strong>'), 'Notes fit copy must preserve the public emphasis hierarchy.');
        foreach (range(1, 3) as $index) $this->assertTrue(str_contains($notes, sprintf('curated_notes_notes_slide_%02d', $index)), "Notes slide {$index} must use approved local media.");
        $this->assertTrue(!str_contains($notes, '<form'), 'Resource page must not invent a replacement data-collection form.');

        $cellText = strip_tags($cell);
        foreach (['Only For 2023 Sec 4 Pure Bio Students!', 'IMPORTANT:', 'How do I access it?', 'What Students Are Saying About The Crash Course:'] as $marker) $this->assertTrue(str_contains($cellText, $marker), "Cell Division must contain {$marker}.");
        preg_match_all('/data-section="([^"]+)"/', $cell, $cellSections);
        $this->assertSame(['hero', 'problems', 'features', 'important', 'access', 'reviews'], $cellSections[1], 'Cell Division sections must follow the public resource order.');
        $this->assertSame(5, substr_count($cell, 'data-carousel-slide'), 'Cell Division hero must preserve all five public slides.');
        foreach (range(1, 5) as $index) $this->assertTrue(str_contains($cell, sprintf('curated_celldivision_slide_%02d', $index)), "Cell Division slide {$index} must use approved local media.");
        foreach (['curated_celldivision_replayable_video', 'curated_celldivision_analogy', 'curated_celldivision_memory_shortcuts'] as $mediaKey) $this->assertTrue(str_contains($cell, $mediaKey), "Cell Division must retain {$mediaKey}.");
        $this->assertTrue(str_contains($cell, 'data-resource-portrait="cell-access"'), 'Cell Division access area must preserve the public portrait.');
        $this->assertTrue(str_contains($cell, 'alt="Mr Keefe from ThatBioTutor"'), 'Cell Division access portrait must have useful alternative text.');
        $this->assertTrue(str_contains($cell, '<strong>HATE</strong>') && str_contains($cell, '<strong>CONTENT</strong>'), 'Cell Division problem copy must preserve the public emphasis hierarchy.');
        $this->assertTrue(str_contains($cell, '/contact/') && str_contains($cell, 'historical 2023'), 'Cell Division access must clearly disclose its dated external/contact boundary.');
        $cellReviews = array_values(array_filter(
            tbt_reference_content()['records']['testimonials'] ?? [],
            static fn(array $record): bool => ($record['fields']['tbt_display_group'] ?? '') === 'celldivision-reviews'
        ));
        $this->assertSame(15, count($cellReviews), 'Cell Division must preserve all 15 public crash-course reviews.');
        $this->assertTrue(
            str_contains(implode(' ', array_column(array_column($cellReviews, 'fields'), 'tbt_quote')), 'Tysm. I liked that qns types were given'),
            'Cell Division must retain the Y1 review omitted from the abbreviated archive.'
        );
        foreach ([$notes, $cell] as $html) $this->assertTrue(!str_contains($html, 'static.wixstatic.com'), 'Resource page must use local media.');

        fwrite(STDOUT, "PASS: {$this->assertions} resource-page assertions\n");
    }
    private function assertSame(mixed $e, mixed $a, string $m): void { $this->assertions++; if ($e !== $a) throw new RuntimeException($m); }
    private function assertTrue(bool $c, string $m): void { $this->assertions++; if (!$c) throw new RuntimeException($m); }
}
try { (new ResourcePagesTest())->run(); } catch (Throwable $e) { fwrite(STDERR, 'FAIL: '.$e->getMessage()."\n"); exit(1); }

