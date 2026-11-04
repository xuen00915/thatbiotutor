<?php

declare(strict_types=1);

final class MediaContractTest
{
    private int $assertions = 0;

    public function run(): void
    {
        $root = dirname(__DIR__);
        $assetRoot = $root . '/wordpress/wp-content/themes/thatbiotutor-hello-child/assets/images';
        $manifestPath = $assetRoot . '/media-manifest.json';
        $fontNotes = $root . '/wordpress/wp-content/themes/thatbiotutor-hello-child/assets/fonts/README.md';

        $this->assertTrue(is_file($manifestPath), 'Generated media manifest must exist.');
        $manifest = json_decode((string) file_get_contents($manifestPath), true, 512, JSON_THROW_ON_ERROR);
        $items = $manifest['items'] ?? [];
        $this->assertTrue(count($items) >= 150, 'Media library must include the original, curated, and supplied source groups.');
        $this->assertSame(count($items), count(array_unique(array_column($items, 'key'))), 'Logical media keys must be unique.');

        $required = ['key', 'source', 'file', 'sha256', 'alt', 'width', 'height', 'fit', 'objectPosition', 'pages', 'licenseStatus'];
        foreach ($items as $item) {
            $this->assertSame([], array_values(array_diff($required, array_keys($item))), "Manifest item {$item['key']} must expose every contract field.");
            $this->assertTrue($item['alt'] !== '', "Manifest item {$item['key']} must have alt text.");
            $this->assertTrue(str_ends_with($item['file'], '.webp'), "Manifest item {$item['key']} must resolve to WebP.");
            $path = $assetRoot . '/' . $item['file'];
            $this->assertTrue(is_file($path), "Manifest file missing for {$item['key']}.");
            $this->assertSame($item['sha256'], hash_file('sha256', $path), "Manifest hash mismatch for {$item['key']}.");
            $size = getimagesize($path);
            $this->assertTrue(is_array($size), "Generated image is unreadable for {$item['key']}.");
            $this->assertSame((int) $item['width'], (int) $size[0], "Manifest width mismatch for {$item['key']}.");
            $this->assertSame((int) $item['height'], (int) $size[1], "Manifest height mismatch for {$item['key']}.");
            $this->assertTrue(in_array($item['fit'], ['cover', 'contain'], true), "Fit mode must be explicit for {$item['key']}.");
        }

        $intensive = array_values(array_filter($items, static fn (array $item): bool => str_starts_with($item['key'], 'intensivefix_program_')));
        $this->assertSame(array_map(static fn (int $index): string => sprintf('intensivefix_program_%02d', $index), range(1, 10)), array_column($intensive, 'key'), 'IntensiveFix graphics must remain in supplied order 1–10.');
        foreach ($intensive as $item) {
            $this->assertSame('contain', $item['fit'], "Text artwork {$item['key']} must use contain fit.");
        }

        $homeHero = array_values(array_filter($items, static fn (array $item): bool => str_starts_with($item['key'], 'home_hero_')));
        $this->assertSame(5, count($homeHero), 'Five supplied Home hero images must be present.');
        $this->assertSame('50% 75%', $homeHero[0]['objectPosition'] ?? null, 'The first Home hero must focus lower in its frame.');

        $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($assetRoot));
        foreach ($iterator as $file) {
            if (!$file->isFile() || $file->getFilename() === 'media-manifest.json') {
                continue;
            }
            $this->assertSame('webp', strtolower($file->getExtension()), 'Every theme-owned raster must be WebP.');
        }

        $this->assertTrue(is_file($fontNotes), 'Font licensing and fallback decision must be documented.');
        $fontFiles = glob(dirname($fontNotes) . '/*.{ttf,otf,woff,woff2}', GLOB_BRACE) ?: [];
        $this->assertSame([], $fontFiles, 'No captured proprietary font may be redistributed without a licence.');

        fwrite(STDOUT, "PASS: {$this->assertions} media-contract assertions\n");
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
    (new MediaContractTest())->run();
} catch (Throwable $error) {
    fwrite(STDERR, 'FAIL: ' . $error->getMessage() . "\n");
    exit(1);
}
