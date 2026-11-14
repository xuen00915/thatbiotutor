<?php

declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}

/*
 * Home page support: field builders for its repeated blocks, the picture lookup used by the shared "inquire"
 * template parts (Home and IntensiveFix), and the FAQ list.
 */

/** The five FastTrack comparisons: title, other students, TBT students, bundled picture. */
function tbt_home_fasttrack_defaults(): array
{
    return [
        ['Content & Understanding', "Dig through school notes and the textbook, figure out what's actually important, and rewrite it all from scratch.", 'Struggling to recall a topic? Just Ctrl+F through our detailed visual notes, or watch crash course clips (think: Netflix, but for Bio.)', 'curated_shared_fasttrack_content'],
        ['Memory', 'Read, reread, blurt out, copy out again and again and hope it sticks by exam day.', 'Confusing terms turn into memory shortcuts that stick, backed by ready-made flashcards for long-term retention. The logic clicks, so the material still sticks long after exams are over.', 'curated_shared_fasttrack_memory'],
        ['Answering Skills (OEQ)', 'Vomit out everything they know and hope it hits the right keywords.', 'Our Commonly Asked Questions database contains every question type, with the exact keywords and phrasing that score full marks. No guessing, and time to spare in the exam.', 'curated_shared_fasttrack_answering'],
        ['Practical Sessions (20% of Grade)', "Severely lack practice in school and hope the exam tests an experiment they've actually done before.", 'Optional hands-on practical sessions cover the most commonly tested experiments, so students walk into Paper 3 with the hand-eye coordination and confidence to protect the full 20%.', 'curated_shared_fasttrack_practical'],
        ['Support', 'Save up questions and wait for the next lesson.', 'Message your tutor anytime. Get help the same day instead of waiting 7 days, with detailed explanations accurate to the O Level syllabus.', 'curated_shared_fasttrack_support'],
    ];
}

/** Editable fields for the FastTrack comparisons. */
function tbt_home_fasttrack_fields(): array
{
    $fields = [];
    foreach (tbt_home_fasttrack_defaults() as $index => [$title, $other, $tbt, $media]) {
        $n = $index + 1;
        $fields[] = tbt_f_text("home_fasttrack_{$n}_title", "Comparison {$n} — title", $title);
        $fields[] = tbt_f_area("home_fasttrack_{$n}_other", "Comparison {$n} — other students", $other);
        $fields[] = tbt_f_area("home_fasttrack_{$n}_text", "Comparison {$n} — TBT students", $tbt);
        $fields[] = tbt_f_image("home_fasttrack_{$n}_image", "Comparison {$n} — picture", $media);
    }
    return $fields;
}

/** Editable fields for the four tutors shown on the Home page. */
function tbt_home_tutor_fields(): array
{
    $defaults = [
        ['Mr Keefe', 'Founder', 'curated_shared_tutor_keefe'],
        ['Ms Chelsea', 'Pure Biology Specialist', 'curated_shared_tutor_chelsea'],
        ['Ms Jamie', 'Pure Biology Specialist', 'curated_shared_tutor_jamie'],
        ['Ms Ng', 'Our Ex-MOE Academic Adviser', 'curated_shared_tutor_ng'],
    ];
    $fields = [];
    foreach ($defaults as $index => [$name, $role, $media]) {
        $n = $index + 1;
        $fields[] = tbt_f_image("home_tutor_{$n}_image", "Tutor {$n} — picture", $media);
        $fields[] = tbt_f_text("home_tutor_{$n}_name", "Tutor {$n} — name", $name);
        $fields[] = tbt_f_text("home_tutor_{$n}_role", "Tutor {$n} — role", $role);
    }
    return $fields;
}

/** The Home picture field that replaces a bundled picture in the shared sections, if there is one. */
function tbt_home_image_field(string $mediaKey): ?string
{
    static $map = null;
    if ($map === null) {
        $map = [];
        foreach (tbt_page_content_fields('home') as $name => $field) {
            if (($field['type'] ?? '') === 'image' && ($field['fallback'] ?? '') !== '' && !isset($map[$field['fallback']])) {
                $map[(string) $field['fallback']] = (string) $name;
            }
        }
    }
    return $map[$mediaKey] ?? null;
}

/** The nine Home FAQs as [question, answer] defaults. */
function tbt_home_faq_defaults(): array
{
    return [
        ['Do you teach physical/online lessons?', 'Yes. Starting 2024, students can choose between coming online or physically.'],
        ['Other than weekly lessons, what other perks are included?', 'Notes, flashcards, lesson clips, extra practice papers, WhatsApp help, and snacks for physical lessons. All are free for students.'],
        ['Do students need to turn on their mic/camera during online lessons?', 'Not compulsory, though students are encouraged to respond to questions via the Zoom chat.'],
        ['Is there a lot of homework?', 'A handful of questions a week, highly manageable. We don’t believe in overloading students. It is not compulsory, but completing it earns Homework Points for rewards.'],
        ['How many students are there in a class?', 'Physical classes are capped at around 20–25 students. Try it yourself in a trial lesson and see why so many students benefit greatly from our classes.'],
        ['What levels do you teach?', 'We specialise exclusively in O Level Pure/G3 Biology, not Combined Biology. Sec 3 classes open in March each year.'],
        ['Do you teach 1-1 tuition?', 'No, and that lets us build specialised notes, CAQs and memory shortcuts for this syllabus. Students still get 1-1 access for questions on WhatsApp and can get help the same day.'],
        ['Are all the classes taught by Mr Keefe?', 'No. There is a small team of tutors, with content and teaching methods standardised across all of them.'],
        ['Can students find out more about class fees and timeslots?', 'Our trial class is $95. After joining, fees vary depending on the class option chosen. Message us on WhatsApp for the latest timeslots and pricing.'],
    ];
}

/** The Home FAQs as [question, answer HTML]: the FAQ records, or the approved defaults before setup has run. */
function tbt_home_faqs(): array
{
    $records = function_exists('tbt_get_faqs') ? tbt_get_faqs('home') : [];
    if ($records !== []) {
        return array_map(static fn (array $faq): array => [$faq['question'], wpautop($faq['answer'])], $records);
    }
    return array_map(static fn (array $faq): array => [$faq[0], '<p>' . esc_html($faq[1]) . '</p>'], tbt_home_faq_defaults());
}
