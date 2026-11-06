<?php

declare(strict_types=1);
require_once __DIR__ . '/content-test-bootstrap.php';
require_once dirname(__DIR__) . '/wordpress/wp-content/themes/thatbiotutor-hello-child/inc/reference-content.php';

// Original-site order, with one copy of Nicolas. Fingerprints lock the pre-change
// record identities, wording, grades, source and media; only group/order may change.
$expected = json_decode((string) file_get_contents(__DIR__ . '/fixtures/testimonial-sequence.json'), true, 512, JSON_THROW_ON_ERROR);
$records = tbt_reference_content()['records']['testimonials'];
foreach ($expected as $year => $entries) {
    $actual = array_values(array_filter($records, static fn(array $r): bool => $r['fields']['tbt_display_group'] === (string) $year));
    usort($actual, static fn(array $a, array $b): int => $a['menu_order'] <=> $b['menu_order']);
    if (array_column($actual, 'seed_key') !== array_column($entries, 'seed_key')) {
        fwrite(STDERR, "FAIL: Class of {$year} must match the complete original sequence (" . count($entries) . " entries).\n");
        exit(1);
    }
    foreach ($actual as $index => $record) {
        unset($record['menu_order'], $record['fields']['tbt_display_group']);
        $hash = hash('sha256', json_encode($record, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
        if ($hash !== $entries[$index]['content_sha256']) {
            fwrite(STDERR, "FAIL: Preserve original record content and media for {$entries[$index]['name']}.\n");
            exit(1);
        }
    }
}
fwrite(STDOUT, "PASS: all 136 testimonials retain content/media and match the five cohort sequences.\n");
