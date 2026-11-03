<?php

declare(strict_types=1);

require_once __DIR__ . '/page-render-bootstrap.php';

final class ClassesPagesTest
{
    private int $assertions = 0;

    public function run(): void
    {
        $files = ['page-classes.php', 'page-sec-3.php', 'page-sec-4.php'];
        $html = [];
        foreach ($files as $file) {
            $this->assertTrue(is_file(TBT_THEME_DIR . '/' . $file), "{$file} must exist.");
            $html[$file] = tbt_test_render_template($file);
            $this->assertSame(1, substr_count($html[$file], '<h1'), "{$file} must render exactly one H1.");
            $this->assertTrue(!str_contains($html[$file], 'static.wixstatic.com'), "{$file} must not hotlink Wix media.");
        }

        $classes = $html['page-classes.php'];
        foreach (['What exactly is O Level', 'Why do so many students struggle', 'How TBT solves each one', 'What makes our classes unlike any other', 'Choose your level below', 'Sec 3 Pure / G3 Biology Classes', 'Sec 4 Pure / G3 Biology Classes'] as $marker) {
            $this->assertTrue(str_contains($classes, $marker), "Classes page must preserve {$marker}.");
        }
        $this->assertTrue(str_contains($classes, '/sec-3-pure-g3-biology-tuition/') && str_contains($classes, '/sec-4-pure-g3-biology-tuition/'), 'Classes level choices must link to distinct canonical pages.');

        $sec3 = $html['page-sec-3.php'];
        foreach (['The Best Time to Start', 'Choose how your child learns best', 'What TBT Sec 3 classes cover', 'Phase 1 — Teaching the Syllabus', 'Phase 2 — EOY Intensives', 'Sec 3 Topics', 'Exam Techniques — Built Into Every Lesson', 'Questions Parents and Sec 3 Students Always Ask'] as $marker) {
            $this->assertTrue(str_contains($sec3, $marker), "Sec 3 must preserve {$marker}.");
        }
        $this->assertTrue(!str_contains($sec3, 'Fix Your Entire Sec 3 Biology Foundation'), 'Sec 3 must not be replaced by the IntensiveFix recovery hero.');
        $this->assertTrue(!str_contains($sec3, 'IntensiveFix programme'), 'Regular Sec 3 tuition must remain separate from IntensiveFix.');

        $sec4 = $html['page-sec-4.php'];
        foreach (['From B4 or worse to A in O Levels', 'Choose how your child learns best', 'What TBT Sec 4 classes cover', 'Phase 1 — Teaching the Syllabus', 'Phase 2 — Prelim Intensives', 'Phase 3 — O Levels Intensives', 'Sec 4 Topics', 'Questions Parents and Sec 4 Students Always Ask'] as $marker) {
            $this->assertTrue(str_contains($sec4, $marker), "Sec 4 must preserve {$marker}.");
        }
        $this->assertTrue(str_contains($sec3, 'data-faq-renderer="sec3"') && str_contains($sec4, 'data-faq-renderer="sec4"'), 'Each tuition page must use its own FAQ collection.');
        $this->assertTrue(str_contains($sec3, 'data-tutor-renderer') && str_contains($sec4, 'data-tutor-renderer'), 'Both tuition pages must use the shared tutor renderer.');

        fwrite(STDOUT, "PASS: {$this->assertions} Classes/Sec tuition assertions\n");
    }

    private function assertSame(mixed $expected, mixed $actual, string $message): void { $this->assertions++; if ($expected !== $actual) throw new RuntimeException($message); }
    private function assertTrue(bool $condition, string $message): void { $this->assertions++; if (!$condition) throw new RuntimeException($message); }
}

try { (new ClassesPagesTest())->run(); } catch (Throwable $error) { fwrite(STDERR, 'FAIL: ' . $error->getMessage() . "\n"); exit(1); }

