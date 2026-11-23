<?php

declare(strict_types=1);

require_once __DIR__ . '/page-render-bootstrap.php';

/**
 * Client feedback of 19 November ("Adcendes Website Editing"): one contract per item that can be checked
 * without a browser. Visual items (sizes, colours, spacing) are covered by tests/feedback-nov19-runtime.js.
 */
final class FeedbackNov19Test
{
    private int $assertions = 0;

    public function run(): void
    {
        $css = '';
        foreach (glob(TBT_THEME_DIR . '/assets/css/*.css') ?: [] as $file) {
            $css .= (string) file_get_contents($file);
        }
        $programmeSource = (string) file_get_contents(TBT_THEME_DIR . '/inc/programme-pages.php');
        $classesSource = (string) file_get_contents(TBT_THEME_DIR . '/inc/content/classes.php');

        // Sec 3 / Sec 4: the old FIRED method is gone everywhere; Paper 3 says it is the theory paper.
        $this->assertTrue(!str_contains($programmeSource . $classesSource, 'FIRED'), 'The retired FIRED method must not appear on Classes, Sec 3 or Sec 4.');
        $this->assertTrue(str_contains($programmeSource, '<strong>Paper 3 practical (theory paper)</strong> — technique, table drawing, graph plotting'), 'Paper 3 practical must say it is the theory paper.');
        $this->assertTrue(str_contains($programmeSource, 'OEQ techniques applied to every answer.'), 'Sec 4 must say OEQ techniques are applied to every answer.');
        $this->assertTrue(str_contains($programmeSource, "['OEQ answering technique', 'Every Paper 2"), 'The Sec 3 OEQ card title must not name a method.');

        foreach (['sec3' => 'page-sec-3.php', 'sec4' => 'page-sec-4.php'] as $level => $template) {
            $html = tbt_test_render_template($template);
            $this->assertSame(1, substr_count($html, 'class="tbt-programme-photos"'), "{$level}: one photo strip above the class options.");
            $this->assertSame(1, preg_match('~<div class="tbt-programme-photos">(?:<figure>.*?</figure>){4}</div>~s', $html), "{$level}: the strip holds the four Home track-record photos.");
            $this->assertTrue(strpos($html, 'tbt-programme-photos') < strpos($html, 'tbt-programme-options__grid'), "{$level}: the photos sit above the Face-to-Face card.");
            $this->assertSame(3, substr_count($html, 'class="tbt-programme-grade"'), "{$level}: each of the three highlights shows its final grade in green.");
            $this->assertTrue(str_contains($html, '→ <span class="tbt-programme-grade">A1</span>') && str_contains($html, '→ <span class="tbt-programme-grade">A2</span>'), "{$level}: the grade after the arrow is the green one.");
        }
        $this->assertTrue(str_contains($css, '.tbt-programme-grade { color: #2f8a27;'), 'Final grades are green.');
        $this->assertTrue(str_contains($css, 'border: 1px solid #e3e6d2; border-radius: 24px'), 'Programme testimonial cards use the premium rounded card.');
        $this->assertTrue(!str_contains($css, 'border: 1px solid #000 !important'), 'The flat black card border is gone.');

        // Round two (same day): Classes FAQ accordion, green testimonial grades, selfie hero, handshake icon, standard widths.
        $this->assertTrue(str_contains((string) file_get_contents(TBT_THEME_DIR . '/page-classes.php'), "tbt_render_faqs('classes', 'accordion')"), 'The Classes FAQ uses the same accordion as the other pages.');
        $this->assertTrue(str_contains((string) file_get_contents(TBT_THEME_DIR . '/inc/testimonials.php'), 'tbt-testimonial__after'), 'The grade a student ended on is wrapped so it can be green in the testimonial cards.');
        $this->assertTrue(str_contains($css, '.tbt-testimonial__after { color: #2f8a27; }'), 'Testimonial card final grades are green.');
        $this->assertTrue(str_contains((string) file_get_contents(TBT_THEME_DIR . '/inc/content/testimonials.php'), 'curated_testimonials_hero_selfie') && str_contains((string) file_get_contents(TBT_THEME_DIR . '/page-notes.php'), 'curated_notes_icon_handshake'), 'The Testimonials hero uses the supplied selfie and the notes card uses the supplied handshake icon.');
        $this->assertTrue(str_contains($css, '.tbt-faq-slider__image { width: 100%; height: 100%; border-radius: 24px; object-fit: cover; }'), 'The FAQ graphics are cropped into rounded corners.');
        $this->assertTrue(str_contains($css, '.tbt-classes-page .tbt-programme-stats { border: 0; border-radius: 24px; background: #edf5e8;'), 'The Classes numbers panel is a light card, not teal.');

        // Footer hours on every page.
        $this->assertTrue(str_contains((string) (tbt_reference_content()['shared']['opening_hours'] ?? ''), '(open for online enquiries all 7 days)'), 'The opening hours must say the centre answers online enquiries.');

        // Results & Testimonials: new hero and CTA wording (the template needs a live stylesheet directory, so read its source and fields).
        $testimonialsTemplate = (string) file_get_contents(TBT_THEME_DIR . '/page-testimonials.php');
        $testimonialsFields = (string) file_get_contents(TBT_THEME_DIR . '/inc/content/testimonials.php');
        $this->assertTrue(str_contains($testimonialsTemplate, 'tbt-testimonials-hero__photo') && str_contains($testimonialsTemplate, 'tbt-testimonials-hero__lead') && str_contains($testimonialsTemplate, 'tbt-testimonials-hero__rating'), 'The Testimonials hero has the headline, the average-jump line, the rating and a photo.');
        $this->assertTrue(str_contains($testimonialsFields, 'The AVERAGE student at TBT <strong>jumps 2.3 Grades (~C5 to A2)</strong>') && str_contains($testimonialsFields, '5.0 (161+ Google Reviews)'), 'The Testimonials hero states the average jump and the rating.');
        $this->assertTrue(substr_count($testimonialsFields, "'Transform My Grade Now'") === 2 && !str_contains($testimonialsFields, "'WhatsApp Us"), 'Every big call to action on Testimonials says Transform My Grade Now.');

        // Free Bio Notes.
        $notes = html_entity_decode(tbt_test_render_template('page-notes.php'));
        $notesText = preg_replace('/\s+/', ' ', strip_tags($notes)) ?? '';
        $this->assertTrue(str_contains($notes, 'tbt-notes-hero__lead') && str_contains($notesText, 'Includes 60+ memory shortcuts.') && str_contains($notesText, 'Accurate to 2027 syllabus.'), 'The Notes hero has the new introduction paragraph.');
        $this->assertTrue(str_contains($notesText, 'Join over 10,000+ students who have downloaded.'), 'The Notes hero shows the 10,000+ downloads line.');
        $this->assertTrue(substr_count($notes, 'tbt-resource-button__icon') >= 4, 'Every DOWNLOAD button on Notes carries the download icon.');
        $this->assertSame(3, substr_count($notes, 'class="tbt-access-row"'), 'The form information box has three icon rows.');
        $this->assertTrue(str_contains($notesText, 'and our friendly staff will help you out ASAP.') && !str_contains($notesText, "I'll help you out"), 'The help line says the friendly staff will help.');
        $this->assertTrue(str_contains($notesText, 'By submitting, you agree to receive promotional emails from us. You can opt out anytime.'), 'The form box discloses promotional emails.');
        $this->assertTrue(str_contains($notesText, 'Hey G3/Pure Bio Student, Is This You?'), 'The Is-this-you banner is renamed.');
        $this->assertTrue(str_contains($notesText, 'Your Questions, Answered') && substr_count($notes, 'class="tbt-notes-card') >= 3, 'The why-free, catch and download blocks are cards under "Your Questions, Answered".');

        // About Us: no LinkedIn for Ms Chelsea; one ring; green founder questions.
        $tutors = tbt_reference_content()['records']['tutors'] ?? [];
        foreach ($tutors as $record) {
            $this->assertTrue(trim((string) ($record['fields']['tbt_tutor_profile_url'] ?? '')) === '', 'No tutor shows a LinkedIn link.');
        }
        $this->assertTrue(str_contains((string) file_get_contents(TBT_THEME_DIR . '/inc/tutors.php'), 'tbt-tutor__frame'), 'The About tutor portrait sits in a single-ring frame.');
        $this->assertTrue(!str_contains($css, 'box-shadow: 0 0 0 3px rgba(148, 174, 74, 0.82)'), 'The second ring (the extra shadow ring) is removed.');
        $this->assertTrue(str_contains($css, '.tbt-founder-question { margin-bottom: 20px; color: #2f8a27; font-size: 28px;'), 'Founder questions are green and bigger.');

        // Our Causes.
        $this->assertTrue(str_contains($css, '.tbt-causes-section[data-section="planting"] .tbt-causes-copy {') && str_contains($css, 'linear-gradient(180deg, #edf5e8 0%, #edf5e8 100%)'), 'The planting text sits in a soft green panel.');

        // Favicon on every page.
        require_once TBT_THEME_DIR . '/inc/site-meta.php';
        ob_start();
        tbt_render_favicon();
        $favicon = (string) ob_get_clean();
        $this->assertSame(3, substr_count($favicon, '<link rel='), 'The circular TBT logo is the favicon and home-screen icon.');
        foreach (['favicon-32.png', 'favicon-192.png', 'apple-touch-icon.png'] as $icon) {
            $this->assertTrue(is_file(TBT_THEME_DIR . '/assets/icons/' . $icon) && str_contains($favicon, $icon), "{$icon} exists and is linked.");
        }

        // Sec 3 FAQ graphics are full resolution, not 490px thumbnails.
        $manifest = json_decode((string) file_get_contents(TBT_THEME_DIR . '/assets/images/media-manifest.json'), true, 512, JSON_THROW_ON_ERROR);
        foreach ($manifest['items'] as $item) {
            if (str_starts_with((string) $item['key'], 'sec3_faq_')) {
                $this->assertTrue((int) $item['width'] >= 1000 && (int) $item['height'] >= 1300, $item['key'] . ' must be the full-resolution graphic.');
            }
        }

        echo 'PASS: ' . $this->assertions . " feedback-nov19 assertions\n";
    }

    private function assertTrue(bool $condition, string $message): void
    {
        $this->assertions++;
        if (!$condition) {
            fwrite(STDERR, 'FAIL: ' . $message . "\n");
            exit(1);
        }
    }

    private function assertSame(mixed $expected, mixed $actual, string $message): void
    {
        $this->assertTrue($expected === $actual, $message . ' (expected ' . var_export($expected, true) . ', got ' . var_export($actual, true) . ')');
    }
}

(new FeedbackNov19Test())->run();
