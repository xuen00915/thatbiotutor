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
        ['notes-review-l', 'L', 'I like how concise everything was. There were many tables and drawings that allowed me to understand just the important stuff.', 'notes-reviews'],
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

    $additionalReviews = [
        ['notes-review-chloe', 'Chloe', 'Great and helpful notes for last-minute revision, and easy to understand.', 'notes-reviews'],
        ['notes-review-angelina', 'Angelina', 'The notes helped me consolidate topics effectively and understand the keywords required in answers.', 'notes-reviews'],
        ['notes-review-lohitha', 'Lohitha', 'I love that such good-quality notes are free and save students hours of making their own revision notes.', 'notes-reviews'],
        ['notes-review-farah-haque', 'Farah Haque', 'It is helpful for last-minute revision and the notes are concise and easy to read, unlike the textbook.', 'notes-reviews'],
        ['notes-review-marcus', 'Marcus', 'The concise notes, memory shortcuts, and clear organisation made studying Biology much easier.', 'notes-reviews'],
        ['notes-review-kelly', 'Kelly', 'The notes are concise, thorough, well-organised, and easy to understand.', 'notes-reviews'],
        ['notes-review-c', 'C', 'The Bio notes reinforced my concepts and helped me recall them better with the memory hacks and acronyms. I feel much more confident for Biology.', 'notes-reviews'],
        ['notes-review-megan', 'Megan', 'It was helpful and thorough, with clear visuals and a useful how-it-works page.', 'notes-reviews'],
        ['notes-review-lee-yang', 'Lee Yang', 'Very concise and accurate Biology notes for revision and learning.', 'notes-reviews'],
        ['notes-review-andrea', 'Andrea', 'It is super clear and easy to understand because everything is compiled so neatly.', 'notes-reviews'],
        ['notes-review-xin-yi', 'Xin Yi', 'The notes are concise, highlight the most important parts of every topic, and explain specific details from a new perspective.', 'notes-reviews'],
        ['notes-review-aaron', 'Aaron', 'The notes are straight to the point and also teach memorable ways to retain the content.', 'notes-reviews'],
        ['cell-review-anita', 'Anita', 'Detailed, with analogies that helped me visualise the information and understand it much better.', 'celldivision-reviews'],
        ['cell-review-jeeva', 'Jeeva', 'The in-depth explanations clarified ideas school moved through quickly, and replayable videos were especially useful.', 'celldivision-reviews'],
        ['cell-review-lihna', 'Lihna', 'Independent assortment, chromosome terms, graphs, and exam keywords became much clearer.', 'celldivision-reviews'],
        ['cell-review-hansini', 'Hansini', 'The clips clarified common misconceptions about mitosis and meiosis and helped clear my doubts.', 'celldivision-reviews'],
        ['cell-review-louisa', 'Louisa', 'The memory hacks and analogies made concepts easier to understand and showed me what to write.', 'celldivision-reviews'],
        ['cell-review-amelia', 'Amelia', 'Breaking down the terms, colour coding, and showing each stage visually made the changes less confusing.', 'celldivision-reviews'],
        ['cell-review-anshika', 'Anshika', 'The structured explanations, drawings, and sectional clips cleared my doubts across the topic.', 'celldivision-reviews'],
        ['cell-review-edgar', 'Edgar', 'Chromosome terminology, DNA graphs, questions, and analogies addressed the concepts needed for exams.', 'celldivision-reviews'],
        ['cell-review-venus', 'Venus', 'Simple explanations, colour drawings, and memory hacks made long definitions easier to retain.', 'celldivision-reviews'],
        ['cell-review-vjun', 'Vjun', 'Analogies, detailed diagrams, and sectional clips made it easy to target specific parts of the chapter.', 'celldivision-reviews'],
        ['cell-review-lia', 'Lia', 'The terms became clearer and I knew what to include in my answers after watching the clips.', 'celldivision-reviews'],
        ['cell-review-qx', 'QX', 'The explanation of chromosome number and DNA amount gave me the clear summary I was missing.', 'celldivision-reviews'],
        ['cell-review-sl', 'SL', 'The examples made chromosome number, DNA amount, diploid, and haploid easier to understand.', 'celldivision-reviews'],
        ['cell-review-roy', 'Roy', 'The analogies, acronyms, and step-by-step explanations simplified the content without losing key concepts.', 'celldivision-reviews'],
    ];
    foreach ($additionalReviews as $index => [$seedKey, $name, $quote, $group]) {
        $testimonialRecords[] = ['seed_key' => $seedKey, 'title' => $name, 'menu_order' => 200 + $index, 'source' => $crawlSource($group === 'notes-reviews' ? '/notes' : '/celldivision'), 'fields' => ['tbt_student_name' => $name, 'tbt_quote' => $quote, 'tbt_rating' => 5, 'tbt_video_url' => '', 'tbt_display_group' => $group]];
    }

    $sharedClassFaqs = [
        ['Can my child try a lesson before committing?', 'Yes. WhatsApp us to arrange a trial lesson. If your child did not love it, ask about the stated refund policy.'],
        ['Face-to-face or online—which should my child choose?', 'Both formats cover the same content and methods. Students may switch formats when places are available.'],
        ['How many students are in a class?', 'There are usually 20–25 students per timeslot, with tutor support available between lessons.'],
        ['What if my child misses a class?', 'Students may attend another available timeslot or use the supplied recording, worksheets, and answer keys.'],
        ['Does my child need to turn on the camera for online lessons?', 'No. Students can respond through the Zoom chat.'],
        ['Is there a lot of homework?', 'A small weekly set is recommended but not compulsory, with Homework Points used for rewards.'],
        ['How do I find out about fees and available timeslots?', 'WhatsApp the team for the latest fees and timeslot availability.'],
        ['What’s the difference between the timeslots?', 'Timings differ, but lesson content and methods are standardised across tutors.'],
    ];
    $faqCollections = [
        'home' => [
            ['Do you teach physical/online lessons?', 'Yes. Students can choose between online and physical lessons.'],
            ['Other than weekly lessons, what other perks are included?', 'Students receive notes, flashcards, lesson clips, practice papers, WhatsApp help, and classroom snacks.'],
            ['Do students need to turn on their mic/camera during online lessons?', 'It is not compulsory; students are encouraged to respond through Zoom chat.'],
            ['Is there a lot of homework?', 'There are a handful of manageable questions each week, with Homework Points for completed work.'],
            ['How many students are there in a class?', 'Physical classes are capped at around 20–25 students.'],
            ['What levels do you teach?', 'ThatBioTutor specialises exclusively in O Level Pure/G3 Biology, not Combined Biology.'],
            ['Do you teach 1-1 tuition?', 'No. Group teaching supports syllabus-specific resources while students retain direct WhatsApp access for questions.'],
            ['Are all the classes taught by Mr Keefe?', 'No. A trained team teaches standardised content and methods.'],
            ['Can students find out more about class fees and timeslots?', 'Message the team on WhatsApp for current pricing and timeslots.'],
        ],
        'classes' => [
            ['What is the difference between Pure Biology and Combined Science?', 'Pure Biology is a full subject with three papers and more content. Combined Science shares its grade and papers with another science.'],
            ['When should my child start?', 'Ideally at the start of Sec 3, though meaningful improvement is still possible in Sec 4.'],
            ['Do you teach Combined Biology?', 'No. ThatBioTutor specialises exclusively in Pure/G3 Biology.'],
        ],
        'sec3' => array_merge([
            ['How is the Sec 3 programme structured across the year?', 'Content teaching runs from March to August, followed by EOY Intensive lessons from September to October.'],
            ['Do you have hands-on practical sessions?', 'Yes. Students practise commonly tested experiments and build Paper 3 confidence.'],
            ['My child just started Sec 3—is it too early to join?', 'No. Starting early builds the foundation and answering techniques needed before Sec 4.'],
            ['When do Sec 3 classes open?', 'Sec 3 classes open every March; contact the team early about availability.'],
        ], $sharedClassFaqs),
        'sec4' => array_merge([
            ['How is the Sec 4 programme structured across the year?', 'Content teaching is followed by Prelim Intensive and O Level Intensive phases.'],
            ['Do you have hands-on practical sessions?', 'Yes. Students practise commonly tested experiments and build Paper 3 confidence.'],
            ['My child is already in Sec 4—is it too late to join?', 'No. Students can still repair gaps and improve, although starting sooner gives more time.'],
            ['What if my child’s school has not covered certain topics yet?', 'Every syllabus topic is covered in the programme, with earlier recordings and worksheets supplied where appropriate.'],
        ], $sharedClassFaqs),
    ];
    $faqRecords = [];
    foreach ($faqCollections as $collection => $items) {
        foreach ($items as $index => [$question, $answer]) {
            $faqRecords[] = ['seed_key' => sprintf('faq-%s-%02d', $collection, $index + 1), 'title' => $question, 'content' => '<p>' . $answer . '</p>', 'menu_order' => $index + 1, 'source' => $crawlSource($collection === 'home' ? '/' : '/' . $collection), 'fields' => ['tbt_faq_collection' => $collection]];
        }
    }

    $resultRecords = [[
        'seed_key' => 'result-cayden-zhu-2025', 'title' => 'Cayden Zhu', 'menu_order' => 10, 'source' => $crawlSource('/testimonials'),
        'fields' => ['tbt_result_student' => 'Cayden Zhu', 'tbt_result_before' => '65%', 'tbt_result_after' => '85%', 'tbt_result_year' => '2025'],
    ]];

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
                    'home_specialist_line' => "ThatBioTutor | Singapore's ONLY 'O' Level Pure/G3 Bio Specialist",
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
                ['value' => '69.21%', 'label' => 'started at B4 or lower'],
                ['value' => '1 in 2', 'label' => 'score distinction'],
            ],
        ],
        'external_links' => [
            'source' => $crawlSource('/contact'),
            'whatsapp' => '',
            'map' => '',
        ],
        'records' => [
            'testimonials' => $testimonialRecords,
            'faqs' => $faqRecords,
            'results' => $resultRecords,
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

