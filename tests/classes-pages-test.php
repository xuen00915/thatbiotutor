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
        $this->assertSectionOrder($classes, ['hero', 'definition', 'struggles', 'solutions', 'difference', 'results', 'levels', 'faq'], 'Classes');
        foreach (['What exactly is O Level', 'Why do so many students struggle', 'How TBT solves each one', 'What makes our classes unlike any other', 'Choose your level below', 'Sec 3 Pure / G3 Biology Classes', 'Sec 4 Pure / G3 Biology Classes'] as $marker) {
            $this->assertTrue(str_contains(html_entity_decode(strip_tags($classes), ENT_QUOTES), $marker), "Classes page must preserve {$marker}.");
        }
        $this->assertTrue(str_contains($classes, '/sec-3-pure-g3-biology-tuition/') && str_contains($classes, '/sec-4-pure-g3-biology-tuition/'), 'Classes level choices must link to distinct canonical pages.');
        $this->assertTrue(str_contains($classes, 'curated_heroes_classes_original'), 'Classes hero must use the captured public background.');
        preg_match_all('/class="[^"]*\\btbt-programme-hero__line\\b[^"]*"/', $classes, $heroLines);
        $this->assertSame(3, count($heroLines[0]), 'Classes hero must retain the public three-line headline composition.');
        $this->assertTrue(str_contains($classes, 'curated_classes_pure_vs_combined'), 'Classes definition must use the public Pure-vs-Combined artwork.');
        $this->assertTrue(str_contains($classes, 'curated_classes_fasttrack_study_prep'), 'Classes difference must use the public FastTrack artwork.');
        $this->assertTrue(!str_contains($classes, 'tbt-problem-grid'), 'Classes must not substitute the public stacked struggle composition with generic problem cards.');
        $this->assertTrue(str_contains($classes, 'class="tbt-programme-button tbt-programme-levels__button"'), 'Classes level chooser must retain the public WhatsApp button below the guidance copy.');
        foreach ([
            'a structured answering framework that earns full marks consistently',
            'Mr. Keefe and the TBT team have developed mnemonics and memory hacks for every major topic.',
            'training on the exact experiment types most commonly tested in Paper 3, well before exam day.',
        ] as $publicClassesWording) {
            $this->assertTrue(str_contains(html_entity_decode(strip_tags($classes)), $publicClassesWording), 'Classes must retain the complete public programme wording.');
        }

        $reference = tbt_reference_content();
        $classesFaqs = array_values(array_filter($reference['records']['faqs'] ?? [], static fn (array $faq): bool => ($faq['fields']['tbt_faq_collection'] ?? '') === 'classes'));
        $this->assertSame(5, count($classesFaqs), 'Classes must retain all five public FAQs.');
        $classesFaqCopy = implode("\n", array_column($classesFaqs, 'content'));
        $this->assertTrue(str_contains($classesFaqCopy, 'It will be renamed to G3 Biology starting 2027'), 'Classes FAQs must retain the complete Pure Biology explanation.');

        $sec3 = $html['page-sec-3.php'];
        $sec3Text = html_entity_decode(strip_tags($sec3));
        $this->assertSectionOrder($sec3, ['hero', 'options', 'cover', 'hall', 'stats', 'testimonials', 'lesson', 'inclusions', 'tutors', 'faq'], 'Sec 3');
        foreach (['The Best Time to Start', 'Choose how your child learns best', 'What TBT Sec 3 classes cover', 'Phase 1 — Teaching the Syllabus', 'Phase 2 — EOY Intensives', 'Sec 3 Topics', 'Exam Techniques — Built Into Every Lesson', 'Questions Parents and Sec 3 Students Always Ask'] as $marker) {
            $this->assertTrue(str_contains($sec3Text, $marker), "Sec 3 must preserve {$marker}.");
        }
        $this->assertTrue(!str_contains($sec3, 'Fix Your Entire Sec 3 Biology Foundation'), 'Sec 3 must not be replaced by the IntensiveFix recovery hero.');
        $this->assertTrue(!str_contains($sec3, 'IntensiveFix programme'), 'Regular Sec 3 tuition must remain separate from IntensiveFix.');
        $this->assertTrue(str_contains($sec3, 'curated_heroes_sec3_original'), 'Sec 3 hero must use the captured public background.');
        $this->assertTrue(substr_count($sec3, 'tbt-programme-hero__highlight') >= 2, 'Sec 3 hero must preserve the public green phrase emphasis.');
        $this->assertTrue(str_contains($sec3, 'March') && str_contains($sec3, 'Sep – Oct'), 'Sec 3 must retain its March opening and two-phase calendar.');
        $this->assertTrue(!str_contains($sec3, 'Phase 3 — O Levels Intensives'), 'Sec 3 must not inherit the Sec 4 third phase.');
        $this->assertTrue(str_contains($sec3, 'data-testimonial-cohort="sec3-featured"'), 'Sec 3 must render its three exact public featured reviews.');
        foreach ([
            'Students are also allowed to switch freely between modes each week.',
            'All Sec 3 topics recapped (~2 topics per lesson)',
            'Time management — which questions to prioritize and when to move on',
            'Full recording uploaded after every lesson, with worksheets and teacher',
        ] as $publicSec3Wording) {
            $this->assertTrue(str_contains($sec3Text, $publicSec3Wording), 'Sec 3 must retain the complete public programme wording.');
        }

        $sec4 = $html['page-sec-4.php'];
        $sec4Text = html_entity_decode(strip_tags($sec4));
        $this->assertSectionOrder($sec4, ['hero', 'options', 'cover', 'hall', 'stats', 'testimonials', 'lesson', 'inclusions', 'tutors', 'faq'], 'Sec 4');
        foreach (['From B4 or worse to A in O Levels', 'Choose how your child learns best', 'What TBT Sec 4 classes cover', 'Phase 1 — Teaching the Syllabus', 'Phase 2 — Prelim Intensives', 'Phase 3 — O Levels Intensives', 'Sec 4 Topics', 'Questions Parents and Sec 4 Students Always Ask'] as $marker) {
            $this->assertTrue(str_contains($sec4Text, $marker), "Sec 4 must preserve {$marker}.");
        }
        $this->assertTrue(str_contains($sec3, 'data-faq-renderer="sec3"') && str_contains($sec4, 'data-faq-renderer="sec4"'), 'Each tuition page must use its own FAQ collection.');
        $this->assertTrue(str_contains($sec3, 'data-tutor-renderer') && str_contains($sec4, 'data-tutor-renderer'), 'Both tuition pages must use the shared tutor renderer.');
        $this->assertTrue(str_contains($sec4, 'curated_heroes_sec4_original'), 'Sec 4 hero must use the captured public background.');
        $this->assertTrue(substr_count($sec4, 'tbt-programme-hero__highlight') >= 3, 'Sec 4 hero must preserve the public green proof emphasis.');
        $this->assertTrue(str_contains($sec4, 'Aug – Nov') && str_contains($sec4, '69.21%'), 'Sec 4 must retain its O-Level intensive phase and recovery proof.');
        $this->assertTrue(str_contains($sec4, 'data-testimonial-cohort="sec4-featured"'), 'Sec 4 must render its three exact public featured reviews.');
        foreach ([
            'Students are also allowed to switch freely between modes each week.',
            'All topics of the entire O Level Pure / G3 Biology syllabus recapped (~2 topics per lesson)',
            'Real practical sessions on the exact experiment types most commonly tested',
            'Time management — which questions to prioritize and when to move on',
        ] as $publicSec4Wording) {
            $this->assertTrue(str_contains($sec4Text, $publicSec4Wording), 'Sec 4 must retain the complete public programme wording.');
        }

        foreach (['sec3' => 13, 'sec4' => 12] as $faqCollection => $expectedFaqCount) {
            $programmeFaqs = array_values(array_filter($reference['records']['faqs'] ?? [], static fn (array $faq): bool => ($faq['fields']['tbt_faq_collection'] ?? '') === $faqCollection));
            $this->assertSame($expectedFaqCount, count($programmeFaqs), ucfirst($faqCollection) . ' must retain every public FAQ.');
            $programmeFaqCopy = implode("\n", array_column($programmeFaqs, 'content'));
            $this->assertTrue(str_contains($programmeFaqCopy, 'None of our students found it impossible to get their questions answered'), ucfirst($faqCollection) . ' FAQs must retain the complete class-size answer.');
        }
        foreach (['sec3-featured' => 'Bio was horrible and I failed even after memorizing a lot of content.', 'sec4-featured' => 'Unlike school teachers who just merely read off the slides'] as $group => $publicQuote) {
            $featured = array_values(array_filter($reference['records']['testimonials'] ?? [], static fn (array $record): bool => ($record['fields']['tbt_display_group'] ?? '') === $group));
            $this->assertSame(3, count($featured), $group . ' must preserve all three public featured reviews.');
            $featuredCopy = implode("\n", array_column(array_column($featured, 'fields'), 'tbt_quote'));
            $this->assertTrue(str_contains($featuredCopy, $publicQuote), $group . ' must preserve its exact public review wording.');
        }
        foreach ([$sec3, $sec4] as $programme) {
            $this->assertTrue(str_contains($programme, 'https://wa.me/'), 'Programme trial CTA must open the configured WhatsApp destination.');
            foreach (['98', '912+', '2.3', '91%', '69.21%', '1 in 2'] as $stat) {
                $this->assertTrue(str_contains($programme, '>' . $stat . '<'), "Programme must render public statistic {$stat}.");
            }
            $this->assertSame(2, substr_count($programme, 'data-track-record'), 'Programme hall of fame must render the two public portrait result panels.');
            $this->assertSame(3, substr_count($programme, 'class="tbt-programme-proof"'), 'Programme hall of fame must render the three public grade-jump proof blocks.');
            preg_match_all('/<a class="tbt-programme-hall__action\s/', $programme, $hallActions);
            $this->assertSame(2, count($hallActions[0]), 'Programme hall of fame must retain both public conversion actions.');
            $this->assertTrue(str_contains($programme, 'tbt-programme-numbers__background') && str_contains($programme, 'curated_heroes_classes_original'), 'Programme numbers must retain the public dark classroom-photo treatment.');
            $this->assertTrue(substr_count($programme, 'class="tbt-inclusion-card') === 8, 'Programme inclusions must render all eight public benefits.');
        }

        fwrite(STDOUT, "PASS: {$this->assertions} Classes/Sec tuition assertions\n");
    }

    private function assertSame(mixed $expected, mixed $actual, string $message): void { $this->assertions++; if ($expected !== $actual) throw new RuntimeException($message); }
    private function assertTrue(bool $condition, string $message): void { $this->assertions++; if (!$condition) throw new RuntimeException($message); }
    private function assertSectionOrder(string $html, array $expected, string $page): void
    {
        preg_match_all('/data-section="([^"]+)"/', $html, $matches);
        $this->assertSame($expected, $matches[1], "{$page} sections must follow the current public-site order.");
    }
}

try { (new ClassesPagesTest())->run(); } catch (Throwable $error) { fwrite(STDERR, 'FAIL: ' . $error->getMessage() . "\n"); exit(1); }

