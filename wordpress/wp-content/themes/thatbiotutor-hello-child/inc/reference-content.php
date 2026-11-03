<?php

declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Return protected page routes in public navigation/setup order.
 *
 * @return array<string,array{slug:string,title:string,template:string,canonical:string}>
 */
function tbt_page_registry(): array
{
    return [
        'home' => [
            'slug' => 'home',
            'title' => 'Home',
            'template' => 'front-page.php',
            'canonical' => '/',
        ],
        'classes' => [
            'slug' => 'classes',
            'title' => 'Classes',
            'template' => 'page-classes.php',
            'canonical' => '/classes/',
        ],
        'sec-3-pure-g3-biology-tuition' => [
            'slug' => 'sec-3-pure-g3-biology-tuition',
            'title' => 'Sec 3 Pure / G3 Biology Tuition',
            'template' => 'page-sec-3.php',
            'canonical' => '/sec-3-pure-g3-biology-tuition/',
        ],
        'sec-4-pure-g3-biology-tuition' => [
            'slug' => 'sec-4-pure-g3-biology-tuition',
            'title' => 'Sec 4 Pure / G3 Biology Tuition',
            'template' => 'page-sec-4.php',
            'canonical' => '/sec-4-pure-g3-biology-tuition/',
        ],
        'testimonials' => [
            'slug' => 'testimonials',
            'title' => 'Results & Testimonials',
            'template' => 'page-testimonials.php',
            'canonical' => '/testimonials/',
        ],
        'notes' => [
            'slug' => 'notes',
            'title' => 'Free Bio Notes',
            'template' => 'page-notes.php',
            'canonical' => '/notes/',
        ],
        'celldivision' => [
            'slug' => 'celldivision',
            'title' => 'Cell Division Crash Course',
            'template' => 'page-celldivision.php',
            'canonical' => '/celldivision/',
        ],
        'about' => [
            'slug' => 'about',
            'title' => 'About Us',
            'template' => 'page-about.php',
            'canonical' => '/about/',
        ],
        'contact' => [
            'slug' => 'contact',
            'title' => 'Contact',
            'template' => 'page-contact.php',
            'canonical' => '/contact/',
        ],
        'trees' => [
            'slug' => 'trees',
            'title' => 'Our Causes',
            'template' => 'page-trees.php',
            'canonical' => '/trees/',
        ],
        'tuitionrecs' => [
            'slug' => 'tuitionrecs',
            'title' => 'Tuition Recommendations',
            'template' => 'page-tuitionrecs.php',
            'canonical' => '/tuitionrecs/',
        ],
        'intensivefix' => [
            'slug' => 'intensivefix',
            'title' => 'IntensiveFix',
            'template' => 'page-intensivefix.php',
            'canonical' => '/intensivefix/',
        ],
    ];
}

/** Return the stable ACF-compatible key assigned to one page field. */
function tbt_reference_field_key(string $pageKey, string $fieldName): string
{
    $normalized = strtolower((string) preg_replace('/[^a-z0-9_]+/i', '_', $pageKey . '_' . $fieldName));
    return 'field_tbt_' . trim($normalized, '_');
}

/**
 * Return approved fallback content, protected navigation, and provenance.
 *
 * @return array<string,mixed>
 */
function tbt_reference_content(): array
{
    $crawlSource = static fn (string $route): array => [
        'type' => 'live-crawl',
        'reference' => 'https://www.thatbiotutor.com' . $route,
        'captured' => '2026-11-03',
    ];

    $testimonialRecords = [];
    $videoIds = ['-YGN_dQE3qM', 'BZeEXmU8oQQ', 'EfS7RQit79U', 'Fxa8-Hi2dfQ', 'H8YSD2u4tjc', 'MY7T5Hqf8l4', 'QzfoV5yH1Tc', 'RJxR3ONbylQ', 'STE5tyEo10w', 'UE2K7-92pkM', 'V4dj5XYEcow', 'Vkzlr16AGvo', 'XuuBFtldw_A', 'YNEW5TFVyMs', 'Ycvn6yK_F0o', 'bTQfAjTy0_E', 'cDz1UQe4wOI', 'hqZtkbQNxxc', 'jH3ve35ECXE', 'nCfeU3UthgA', 'shZaStH4nGI', 'uewzV0bhqoc', 'xG8PcBOOhCA', 'xT8OzWLZ4w8'];
    $videoGroups = ['2025', '2024', '2023', '2022', '2021'];
    foreach ($videoIds as $index => $videoId) {
        $number = $index + 1;
        $testimonialRecords[] = [
            'seed_key' => sprintf('testimonial-video-%02d', $number),
            'title' => sprintf('Student Story %02d', $number),
            'menu_order' => $number,
            'source' => $crawlSource('/testimonials'),
            'fields' => [
                'tbt_student_name' => sprintf('Student Story %02d', $number),
                'tbt_quote' => '',
                'tbt_rating' => 5,
                'tbt_video_url' => 'https://www.youtube.com/watch?v=' . $videoId,
                'tbt_display_group' => $videoGroups[min(4, intdiv($index, 5))],
            ],
        ];
    }
    $textTestimonials = [
        ['testimonial-javier-tee', 'Javier Tee', 'VERY GOOD TEACHING LOVE IT. Ms Chelsea and Mr Keefe are really nice and patient, plus the notes are excellent.', '2025'],
        ['testimonial-cayden-zhu', 'Cayden Zhu', 'TBT exposed me to how Biology can actually be fun, with interactive classes and concise notes.', '2025'],
        ['testimonial-jordan-tang', 'Jordan Tang', 'The notes are incredibly concise and helped me clear the syllabus faster and memorise content more efficiently.', '2025'],
        ['notes-review-jezelle', 'Jezelle', 'Thank you so much for this! This is my lifesaver and my day-to-day grind for O-Level A1 in Pure Bio.', 'notes-reviews'],
        ['notes-review-j', 'J', 'The notes were very informative and good.', 'notes-reviews'],
        ['notes-review-anisatusyifa', 'Anisatusyifa', 'Very useful. During O-Level Bio Paper 2 I barely left any blanks because I could remember most of the points.', 'notes-reviews'],
        ['notes-review-l', 'L', 'Concise, visual, and much easier to revise from.', 'notes-reviews'],
    ];
    foreach ($textTestimonials as $index => [$seedKey, $name, $quote, $group]) {
        $testimonialRecords[] = [
            'seed_key' => $seedKey,
            'title' => $name,
            'menu_order' => 100 + $index,
            'source' => $crawlSource($group === 'notes-reviews' ? '/notes' : '/testimonials'),
            'fields' => ['tbt_student_name' => $name, 'tbt_quote' => $quote, 'tbt_rating' => 5, 'tbt_video_url' => '', 'tbt_display_group' => $group],
        ];
    }

    return [
        'version' => '2026-11-03.1',
        'source_date' => '2026-11-03',
        'navigation' => [
            ['label' => 'Home', 'path' => '/'],
            [
                'label' => 'Classes',
                'path' => '/classes/',
                'children' => [
                    ['label' => 'Sec 3', 'path' => '/sec-3-pure-g3-biology-tuition/'],
                    ['label' => 'Sec 4', 'path' => '/sec-4-pure-g3-biology-tuition/'],
                ],
            ],
            ['label' => 'Results & Testimonials', 'path' => '/testimonials/'],
            ['label' => 'Free Bio Notes', 'path' => '/notes/'],
            ['label' => 'About Us', 'path' => '/about/'],
            ['label' => 'Our Causes', 'path' => '/trees/'],
        ],
        'aliases' => [
            '/classes/sec-3-pure-g3-biology-tuition/' => '/sec-3-pure-g3-biology-tuition/',
            '/s3-intensivefix/' => '/intensivefix/',
        ],
        'shared' => [
            'source' => $crawlSource('/contact'),
            'whatsapp_number' => '6587597182',
            'whatsapp_message' => 'Hi ThatBioTutor',
            'phone' => '+65 8759 7182',
            'address' => 'ThatBioTutor, Singapore',
            'map_url' => '',
            'opening_hours' => '',
            'footer_description' => 'Singapore O-Level Pure / G3 Biology specialists.',
            'copyright' => 'ThatBioTutor. All rights reserved.',
        ],
        'pages' => [
            'home' => [
                'source' => $crawlSource('/'),
                'fields' => [
                    'home_heading' => "The AVERAGE TBT Student Makes A 2.3 Grade Jump (~C5 to A2)",
                    'home_primary_cta_label' => 'Find Out More',
                    'home_missing_key_heading' => 'The 1 MISSING KEY To Go From FAIL to A1, While Cutting Down Study Time By 90%',
                    'home_fasttrack_heading' => 'Our FastTrack System 🚀',
                    'home_trust_heading' => 'Why You Can Trust In TBT:',
                    'home_testimonials_heading' => 'What Students Are Saying About ThatBioTutor:',
                    'home_schools_heading' => 'Schools We Have Taught Students From:',
                    'home_advantage_heading' => 'The TBT Advantage',
                    'home_experience_heading' => 'Sneak Peek Into The TBT Experience:',
                    'home_causes_heading' => 'Save The Planet With Us!',
                    'home_tutors_heading' => 'Our Tutors',
                    'home_faq_heading' => 'Frequently Asked Questions',
                    'home_closing_heading' => 'Ready to make Biology click?',
                ],
            ],
            'classes' => [
                'source' => $crawlSource('/classes'),
                'fields' => [
                    'classes_heading' => 'The AVERAGE TBT Student Makes a 2.3 Grade Jump (~C5 to A2)',
                    'classes_level_heading' => 'Ready to find out more? Choose your level below.',
                    'classes_intro_heading' => 'What exactly is O Level Pure/G3 Biology?',
                    'classes_struggle_heading' => 'Why do so many students struggle with Pure/ G3 Biology?',
                    'classes_solution_heading' => 'How TBT solves each one:',
                    'classes_difference_heading' => 'What makes our classes unlike any other',
                ],
            ],
            'sec-3-pure-g3-biology-tuition' => [
                'source' => $crawlSource('/sec-3-pure-g3-biology-tuition'),
                'fields' => [
                    'sec3_heading' => 'The Best Time to Start Pure / G3 Biology Tuition is at the Start of Sec 3. The Second Best Time is Now!',
                    'sec3_cover_heading' => 'What TBT Sec 3 classes cover',
                    'sec3_topics_heading' => 'Sec 3 Topics:',
                ],
            ],
            'sec-4-pure-g3-biology-tuition' => [
                'source' => $crawlSource('/sec-4-pure-g3-biology-tuition'),
                'fields' => [
                    'sec4_heading' => "From B4 or worse to A in O Levels is still possible. Here's the proof.",
                    'sec4_cover_heading' => 'What TBT Sec 4 classes cover',
                    'sec4_topics_heading' => 'Sec 4 Topics:',
                ],
            ],
            'testimonials' => [
                'source' => $crawlSource('/testimonials'),
                'fields' => [
                    'testimonials_heading' => 'Results & Testimonials',
                    'testimonials_reviews_heading' => 'What Students Are Saying About ThatBioTutor:',
                ],
            ],
            'notes' => [
                'source' => $crawlSource('/notes'),
                'fields' => [
                    'notes_heading' => 'Free O Level Biology Notes Singapore',
                    'notes_audience' => 'For Pure / G3 Biology',
                ],
            ],
            'celldivision' => [
                'source' => $crawlSource('/celldivision'),
                'fields' => [
                    'celldivision_notice' => 'Only For 2023 Sec 4 Pure Bio Students!',
                ],
            ],
            'about' => [
                'source' => $crawlSource('/about'),
                'fields' => [
                    'about_heading' => 'About ThatBioTutor',
                    'about_mission_heading' => 'Our Mission',
                    'about_quality_heading' => 'Quality Teaching Only',
                    'about_tutors_heading' => 'Our Tutors',
                    'about_founder_heading' => 'A Message From The Founder',
                    'about_beliefs_heading' => 'What We Believe In',
                ],
            ],
            'contact' => [
                'source' => $crawlSource('/contact'),
                'fields' => [
                    'contact_heading' => 'Contact.',
                    'contact_intro' => 'If you have a question, please read our FAQs first!',
                ],
            ],
            'trees' => [
                'source' => $crawlSource('/trees'),
                'fields' => [
                    'trees_primary_heading' => '1. Planting Trees!',
                    'trees_secondary_heading' => '2. SG Environmental Causes',
                ],
            ],
            'tuitionrecs' => [
                'source' => $crawlSource('/tuitionrecs'),
                'fields' => [
                    'tuitionrecs_heading' => 'O Level Tuition Recommendations',
                ],
            ],
            'intensivefix' => [
                'source' => [
                    'type' => 'supplied-asset-group',
                    'reference' => '2A. intensivefix graphics + 2B. Intensivefix reviews + approved maintained page',
                    'captured' => '2026-11-02',
                ],
                'fields' => [
                    'intensivefix_heading' => 'Fix Your Entire Sec 3 Biology Foundation in 4 Days',
                    'intensivefix_cta_label' => 'Enquire About IntensiveFix',
                    'intensivefix_program_heading' => 'Everything You Need to Know About IntensiveFix',
                    'intensivefix_analysis_heading' => 'Turn Your EOY Result into a Sec 4 Recovery Plan',
                    'intensivefix_suitability_heading' => 'Is IntensiveFix Right for You?',
                ],
            ],
        ],
        'stats' => [
            'source' => $crawlSource('/'),
            'items' => [
                ['value' => '98', 'label' => 'schools'],
                ['value' => '912+', 'label' => 'students'],
                ['value' => '2.3', 'label' => 'average grade jump'],
                ['value' => '91%', 'label' => 'A1/A2/B3'],
            ],
        ],
        'external_links' => [
            'source' => $crawlSource('/contact'),
            'whatsapp' => '',
            'map' => '',
        ],
        'records' => [
            'testimonials' => $testimonialRecords,
            'tutors' => [
                [
                    'seed_key' => 'tutor-ms-chelsea',
                    'title' => 'Ms Chelsea',
                    'menu_order' => 10,
                    'source' => $crawlSource('/about'),
                    'fields' => [
                        'tbt_tutor_role' => 'Biology Tutor',
                        'tbt_tutor_short_bio' => 'Patient, encouraging, and focused on helping students understand Biology clearly.',
                    ],
                ],
                [
                    'seed_key' => 'tutor-ms-jamie',
                    'title' => 'Ms Jamie',
                    'menu_order' => 20,
                    'source' => $crawlSource('/about'),
                    'fields' => [
                        'tbt_tutor_role' => 'Biology Tutor',
                        'tbt_tutor_short_bio' => 'Builds student confidence through clear explanations and guided practice.',
                    ],
                ],
                [
                    'seed_key' => 'tutor-mr-keefe',
                    'title' => 'Mr Keefe',
                    'menu_order' => 30,
                    'source' => $crawlSource('/about'),
                    'fields' => [
                        'tbt_tutor_role' => 'Founder',
                        'tbt_tutor_short_bio' => 'Founder of ThatBioTutor and specialist in making content-heavy Biology easier to understand and remember.',
                    ],
                ],
                [
                    'seed_key' => 'tutor-ms-ng',
                    'title' => 'Ms Ng',
                    'menu_order' => 40,
                    'source' => $crawlSource('/about'),
                    'fields' => [
                        'tbt_tutor_role' => 'Ex-MOE Teacher & Syllabus Reviewer',
                        'tbt_tutor_short_bio' => 'Provides experienced syllabus and teaching perspective for ThatBioTutor resources.',
                    ],
                ],
            ],
        ],
    ];
}

