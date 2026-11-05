<?php

declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}

$tbt_media_image = static function (string $key, string $class = '', string $attributes = ''): void {
    $media = tbt_media($key);
    if (($media['url'] ?? '') === '') {
        return;
    }
    echo '<img data-media-key="' . esc_attr($key) . '"';
    if ($class !== '') {
        echo ' class="' . esc_attr($class) . '"';
    }
    if ($attributes !== '') {
        echo ' ' . $attributes;
    }
    echo ' src="' . esc_url((string) $media['url']) . '" alt="' . esc_attr((string) ($media['alt'] ?? ''))
        . '" width="' . esc_attr((string) ($media['width'] ?? 0)) . '" height="' . esc_attr((string) ($media['height'] ?? 0)) . '">';
};

$tbt_whatsapp = tbt_whatsapp_url('Hi ThatBioTutor, I would like to book a $95 trial lesson. Please share the available timings.');
$tbt_stats = [
    ['display' => '912+', 'target' => '912', 'label' => 'Students taught, across 98 Singapore schools'],
    ['display' => '91%', 'target' => '91', 'label' => 'Score A1, A2, or B3', 'note' => 'despite 69% starting at B4 or lower'],
    ['display' => '2.3', 'target' => '2.3', 'label' => 'Average grade jump, roughly C5 to A2'],
    ['display' => '1 in 2', 'target' => '1', 'label' => 'Score A (Distinction)'],
];
$tbt_comparisons = [
    [
        'title' => 'Content & Understanding',
        'other' => "Dig through school notes and the textbook, figure out what's actually important, and rewrite it all from scratch.",
        'tbt' => 'Struggling to recall a topic? Just Ctrl+F through our detailed visual notes, or watch crash course clips (think: Netflix, but for Bio.)',
        'media' => 'curated_shared_fasttrack_content',
    ],
    [
        'title' => 'Memory',
        'other' => 'Read, reread, blurt out, copy out again and again and hope it sticks by exam day.',
        'tbt' => 'Confusing terms turn into memory shortcuts that stick, backed by ready-made flashcards for long-term retention. The logic clicks, so the material still sticks long after exams are over.',
        'media' => 'curated_shared_fasttrack_memory',
    ],
    [
        'title' => 'Answering Skills (OEQ)',
        'other' => 'Vomit out everything they know and hope it hits the right keywords.',
        'tbt' => 'Our Commonly Asked Questions database contains every question type, with the exact keywords and phrasing that score full marks. No guessing, and time to spare in the exam.',
        'media' => 'curated_shared_fasttrack_answering',
    ],
    [
        'title' => 'Practical Sessions (20% of Grade)',
        'other' => "Severely lack practice in school and hope the exam tests an experiment they've actually done before.",
        'tbt' => 'Optional hands-on practical sessions cover the most commonly tested experiments, so students walk into Paper 3 with the hand-eye coordination and confidence to protect the full 20%.',
        'media' => 'curated_shared_fasttrack_practical',
    ],
    [
        'title' => 'Support',
        'other' => 'Save up questions and wait for the next lesson.',
        'tbt' => 'Message your tutor anytime. Get help the same day instead of waiting 7 days, with detailed explanations accurate to the O Level syllabus.',
        'media' => 'curated_shared_fasttrack_support',
    ],
];
$tbt_tutors = [
    ['media' => 'curated_shared_tutor_keefe', 'name' => 'Mr Keefe', 'role' => 'Founder'],
    ['media' => 'curated_shared_tutor_chelsea', 'name' => 'Ms Chelsea', 'role' => 'Pure Biology Specialist'],
    ['media' => 'curated_shared_tutor_jamie', 'name' => 'Ms Jamie', 'role' => 'Pure Biology Specialist'],
    ['media' => 'curated_shared_tutor_ng', 'name' => 'Ms Ng', 'role' => 'Our Ex-MOE Academic Adviser'],
];
$tbt_faqs = [
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
