<?php

declare(strict_types=1);

require_once __DIR__ . '/content-test-bootstrap.php';
require_once dirname(__DIR__) . '/wordpress/wp-content/themes/thatbiotutor-hello-child/inc/reference-content.php';

function admin_url(string $path = ''): string
{
    return 'https://example.test/wp-admin/' . $path;
}

function add_query_arg(array $args, string $url): string
{
    return $url . (str_contains($url, '?') ? '&' : '?') . http_build_query($args);
}

/** Editing testimonials: pick where each one appears from a list, see and filter it in the admin list. */
final class TestimonialAdminTest extends TbtContentTestCase
{
    public function run(): void
    {
        $base = dirname(__DIR__) . '/wordpress/wp-content/themes/thatbiotutor-hello-child/';
        foreach (['acf-fields.php', 'content-types.php', 'class-years.php', 'testimonials.php', 'testimonial-admin.php'] as $file) {
            $this->assertTrue(is_file($base . 'inc/' . $file), "{$file} must exist.");
            require_once $base . 'inc/' . $file;
        }
        $this->assertTrue(in_array('inc/testimonial-admin.php', (static function () use ($base): array {
            $source = (string) file_get_contents($base . 'functions.php');
            preg_match_all("/'(inc\\/[a-z-]+\\.php)'/", $source, $m);
            return $m[1];
        })(), true), 'functions.php must load the testimonial admin helpers.');

        // The placement field is a dropdown, first in the form, with plain-English labels.
        $groups = array_column(tbt_acf_content_groups(), null, 'key');
        $fields = $groups['group_tbt_testimonial_fields']['fields'];
        $byName = array_column($fields, null, 'name');
        $this->assertSame('tbt_display_group', $fields[0]['name'], 'Where it appears must be the first field.');
        $this->assertSame('select', $byName['tbt_display_group']['type'], 'Where it appears must be a dropdown, not free text.');
        $this->assertSame('Where it appears', $byName['tbt_display_group']['label'], 'The placement field needs a plain label.');
        $this->assertTrue(($byName['tbt_display_group']['required'] ?? 0) === 1, 'A testimonial must say where it appears.');
        foreach (['tbt_student_name', 'tbt_quote', 'tbt_video_url', 'tbt_before_result', 'tbt_after_result'] as $name) {
            $this->assertTrue(trim((string) ($byName[$name]['instructions'] ?? '')) !== '', "{$name} needs a one-line hint.");
        }

        // Choices: every published class year (newest first) and every fixed page spot.
        $GLOBALS['tbt_content_posts'] = [
            tbt_test_post(1, 'tbt_class_year', 'Class of 2025', 10),
            tbt_test_post(2, 'tbt_class_year', 'Class of 2026', 0),
            tbt_test_post(10, 'tbt_testimonial', 'Alicia', 1),
            tbt_test_post(11, 'tbt_testimonial', 'Ben', 2),
            tbt_test_post(12, 'tbt_testimonial', 'Chloe', 3),
        ];
        $GLOBALS['tbt_content_meta'] = [
            1 => ['tbt_year_value' => '2025'],
            2 => ['tbt_year_value' => '2026'],
            10 => ['tbt_display_group' => '2026', 'tbt_video_url' => 'https://youtu.be/abc'],
            11 => ['tbt_display_group' => '2026'],
            12 => ['tbt_display_group' => 'notes-reviews'],
        ];
        $placements = tbt_testimonial_placements();
        $this->assertSame(['2026', '2025'], array_map('strval', array_slice(array_keys($placements), 0, 2)), 'Class years come first, newest first.');
        $this->assertSame('Results & Testimonials page · Class of 2026', $placements['2026'], 'A year reads as its page and heading.');
        foreach (['sec3-featured', 'sec4-featured', 'notes-reviews', 'notes-reviews-bottom', 'celldivision-reviews', 'hidden'] as $spot) {
            $this->assertTrue(isset($placements[$spot]), "{$spot} must be offered.");
        }
        $this->assertSame('Not shown on the site', $placements['hidden'], 'There must be a way to park a testimonial.');
        $this->assertSame('Not shown on the site', tbt_testimonial_placement_label('video-transcript-source'), 'Unrendered legacy groups read as not shown.');

        // ACF field load: choices filled, an unknown saved value kept, the year from "Add testimonial" links preselected.
        $field = tbt_testimonial_placement_field($byName['tbt_display_group'] + ['value' => 'home-featured']);
        $this->assertSame('Not shown on the site', $field['choices']['home-featured'] ?? null, 'A saved legacy value must stay selectable so saving keeps it.');
        $_GET['tbt_placement'] = '2026';
        $field = tbt_testimonial_placement_field($byName['tbt_display_group']);
        $this->assertSame('2026', $field['default_value'], 'Add testimonial from a year preselects that year.');
        $_GET['tbt_placement'] = '<script>';
        $field = tbt_testimonial_placement_field($byName['tbt_display_group']);
        $this->assertTrue(($field['default_value'] ?? '') !== '<script>', 'Only known placements may be preselected.');
        unset($_GET['tbt_placement']);

        // Admin list: "Where it appears" and "Type" columns, plus a filter.
        $columns = tbt_testimonial_admin_columns(['cb' => '', 'title' => 'Title', 'date' => 'Date']);
        $this->assertSame(['cb', 'title', 'tbt_placement', 'tbt_kind', 'date'], array_keys($columns), 'Placement and type sit next to the title.');
        $html = $this->capture(static fn () => tbt_render_testimonial_admin_column('tbt_placement', 10));
        $this->assertTrue(str_contains($html, 'Class of 2026') && str_contains($html, 'tbt_placement=2026'), 'The placement links to the filtered list.');
        $this->assertSame('Video', trim(strip_tags($this->capture(static fn () => tbt_render_testimonial_admin_column('tbt_kind', 10)))), 'A YouTube link makes it a video.');
        $this->assertSame('Written', trim(strip_tags($this->capture(static fn () => tbt_render_testimonial_admin_column('tbt_kind', 11)))), 'No video makes it written.');

        $query = new class {
            public array $vars = ['post_type' => 'tbt_testimonial'];
            public function is_main_query(): bool { return true; }
            public function get(string $key): mixed { return $this->vars[$key] ?? null; }
            public function set(string $key, mixed $value): void { $this->vars[$key] = $value; }
        };
        $_GET['tbt_placement'] = 'notes-reviews';
        tbt_filter_testimonials_by_placement($query);
        $this->assertSame([['key' => 'tbt_display_group', 'value' => 'notes-reviews', 'compare' => '=']], $query->vars['meta_query'] ?? null, 'The filter narrows the list to one placement.');
        unset($_GET['tbt_placement']);

        // Class Years list: each year shows its testimonial count with links to see and add them.
        $yearColumns = tbt_class_year_admin_columns(['cb' => '', 'title' => 'Title', 'date' => 'Date']);
        $this->assertTrue(isset($yearColumns['tbt_year_testimonials']), 'Class Years list must show its testimonials.');
        $html = $this->capture(static fn () => tbt_render_class_year_admin_column('tbt_year_testimonials', 2));
        $this->assertTrue(str_contains($html, '2 testimonials'), 'The count must be shown.');
        $this->assertTrue(str_contains($html, 'post-new.php?post_type=tbt_testimonial&amp;tbt_placement=2026') || str_contains($html, 'post-new.php?post_type=tbt_testimonial&tbt_placement=2026'), 'A one-click add link for that year.');

        // The title follows the student name when left empty.
        $data = tbt_testimonial_title_from_name(['post_type' => 'tbt_testimonial', 'post_title' => ''], ['acf' => ['field_tbt_student_name' => 'Dana [B4 to A1]']]);
        $this->assertSame('Dana [B4 to A1]', $data['post_title'], 'An empty title takes the student name.');
        $data = tbt_testimonial_title_from_name(['post_type' => 'tbt_testimonial', 'post_title' => 'Kept'], ['acf' => ['field_tbt_student_name' => 'Other']]);
        $this->assertSame('Kept', $data['post_title'], 'A typed title is kept.');

        // A short guide sits on the testimonial screen.
        $guide = $this->capture(static fn () => tbt_testimonial_edit_guide((object) ['post_type' => 'tbt_testimonial']));
        $this->assertTrue(str_contains($guide, 'Where it appears') && str_contains($guide, 'Order'), 'The guide explains placement and order.');
        $this->assertSame('', $this->capture(static fn () => tbt_testimonial_edit_guide((object) ['post_type' => 'page'])), 'No guide on other screens.');

        echo "PASS: testimonial admin ({$this->assertions} assertions)\n";
    }
}

try {
    (new TestimonialAdminTest())->run();
} catch (Throwable $error) {
    fwrite(STDERR, 'FAIL: ' . $error->getMessage() . "\n");
    exit(1);
}
