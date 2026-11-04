<?php

declare(strict_types=1);

final class ReferenceContentTest
{
    private int $assertions = 0;

    public function run(): void
    {
        $root = dirname(__DIR__);
        $module = $root . '/wordpress/wp-content/themes/thatbiotutor-hello-child/inc/reference-content.php';
        $manifest = $root . '/resources/source-manifest.json';

        $this->assertTrue(is_file($module), 'Reference-content module must exist.');
        $this->assertTrue(is_file($manifest), 'Source manifest must exist.');

        if (!defined('ABSPATH')) {
            define('ABSPATH', $root . '/.wordpress-test/');
        }
        require_once $module;

        $this->assertTrue(function_exists('tbt_reference_content'), 'Reference-content accessor must exist.');
        $this->assertTrue(function_exists('tbt_page_registry'), 'Page registry accessor must exist.');

        $registry = tbt_page_registry();
        $expectedKeys = [
            'home',
            'classes',
            'sec-3-pure-g3-biology-tuition',
            'sec-4-pure-g3-biology-tuition',
            'testimonials',
            'notes',
            'celldivision',
            'about',
            'contact',
            'trees',
            'tuitionrecs',
            'intensivefix',
        ];
        $this->assertSame($expectedKeys, array_keys($registry), 'Canonical page registry must remain complete and ordered.');

        foreach ($registry as $key => $page) {
            $this->assertSame($key, $page['slug'], "Registry slug must match key for {$key}.");
            $this->assertTrue(str_starts_with($page['template'], 'page-') || $page['template'] === 'front-page.php', "Protected template missing for {$key}.");
            $this->assertTrue(str_starts_with($page['canonical'], '/'), "Canonical route missing for {$key}.");
        }
        $this->assertSame('/', $registry['home']['canonical'], 'Home must own the root canonical route.');
        $this->assertSame('page-intensivefix.php', $registry['intensivefix']['template'], 'IntensiveFix must be an additional protected page.');

        $content = tbt_reference_content();
        $this->assertSame('2026-11-03', $content['source_date'] ?? null, 'Reference content must name the authoritative crawl date.');
        $this->assertSame($expectedKeys, array_keys($content['pages'] ?? []), 'Every canonical page must have reference content.');

        foreach ($content['pages'] as $key => $block) {
            $source = $block['source'] ?? [];
            $this->assertTrue(is_array($source), "Source provenance must be structured for {$key}.");
            $this->assertTrue(isset($source['type'], $source['reference']), "Source provenance must identify type and reference for {$key}.");
            $this->assertTrue($source['reference'] !== '', "Source reference cannot be empty for {$key}.");
            $this->assertTrue(isset($block['fields']) && is_array($block['fields']), "Page fallback fields must be structured for {$key}.");
        }
        $this->assertSame('supplied-asset-group', $content['pages']['intensivefix']['source']['type'], 'IntensiveFix must be sourced separately from the original crawl.');

        $aliases = $content['aliases'] ?? [];
        $this->assertSame('/sec-3-pure-g3-biology-tuition/', $aliases['/classes/sec-3-pure-g3-biology-tuition/'] ?? null, 'Legacy Sec 3 alias must resolve to its canonical page.');
        $this->assertSame('/intensivefix/', $aliases['/s3-intensivefix/'] ?? null, 'Legacy IntensiveFix alias must resolve to its additional page.');
        foreach (array_keys($aliases) as $alias) {
            $this->assertTrue(!in_array($alias, array_column($registry, 'canonical'), true), "Alias {$alias} must stay outside canonical sitemap data.");
        }

        $navigation = $content['navigation'] ?? [];
        $this->assertSame(['Home', 'Classes', 'Results & Testimonials', 'Free Bio Notes', 'About Us', 'Our Causes'], array_column($navigation, 'label'), 'Protected navigation labels must match the original.');
        $this->assertSame(['Sec 3', 'Sec 4'], array_column($navigation[1]['children'] ?? [], 'label'), 'Classes submenu must contain only Sec 3 and Sec 4.');

        $records = $content['records']['tutors'] ?? [];
        $this->assertSame(4, count($records), 'Reference content must seed the four original tutors.');
        foreach ($records as $record) {
            $this->assertTrue(isset($record['seed_key'], $record['title'], $record['source']), 'Every seeded tutor must have stable identity and provenance.');
        }

        $decoded = json_decode((string) file_get_contents($manifest), true, 512, JSON_THROW_ON_ERROR);
        $this->assertSame('2026-11-03', $decoded['authoritativeCrawl']['date'] ?? null, 'Manifest must record the authoritative crawl date.');
        $crawlPath = $decoded['authoritativeCrawl']['path'] ?? '';
        $suppliedAssetsPath = $decoded['suppliedAssets']['path'] ?? '';
        if (getenv('CI') === 'true') {
            $this->assertTrue(str_starts_with($crawlPath, '/'), 'Manifest crawl path must remain absolute in CI.');
            $this->assertTrue(str_starts_with($suppliedAssetsPath, '/'), 'Manifest supplied-asset path must remain absolute in CI.');
        } else {
            $this->assertTrue(is_dir($crawlPath), 'Manifest crawl path must resolve locally.');
            $this->assertTrue(is_dir($suppliedAssetsPath), 'Manifest supplied-asset path must resolve locally.');
        }

        fwrite(STDOUT, "PASS: {$this->assertions} reference-content assertions\n");
    }

    private function assertSame(mixed $expected, mixed $actual, string $message): void
    {
        $this->assertions++;
        if ($expected !== $actual) {
            throw new RuntimeException($message . ' Expected ' . var_export($expected, true) . ', got ' . var_export($actual, true));
        }
    }

    private function assertTrue(bool $condition, string $message): void
    {
        $this->assertions++;
        if (!$condition) {
            throw new RuntimeException($message);
        }
    }
}

try {
    (new ReferenceContentTest())->run();
} catch (Throwable $error) {
    fwrite(STDERR, 'FAIL: ' . $error->getMessage() . "\n");
    exit(1);
}

