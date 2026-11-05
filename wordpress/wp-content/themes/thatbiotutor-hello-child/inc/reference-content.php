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
    $videoTranscriptPath = dirname(__DIR__) . '/assets/content/video-testimonial-transcripts.json';
    $videoTranscriptArchive = is_file($videoTranscriptPath) ? json_decode((string) file_get_contents($videoTranscriptPath), true) : null;
    $videoTranscripts = [];
    $pendingVideoTranscriptNames = [];
    $pendingVideoTranscriptBrackets = [];
    foreach (($videoTranscriptArchive['transcripts'] ?? []) as $transcript) {
        $videoId = trim((string) ($transcript['video_id'] ?? ''));
        $name = trim((string) ($transcript['name'] ?? ''));
        $quote = trim((string) ($transcript['quote'] ?? ''));
        $group = trim((string) ($transcript['group'] ?? ''));
        if ($videoId === '' || $name === '' || $quote === '' || !in_array($group, ['2025', '2024', '2023', '2022', '2021'], true)) {
            continue;
        }
        $videoTranscripts[$videoId] = ['name' => $name, 'quote' => $quote, 'group' => $group];
        $nameBase = strtolower(trim((string) preg_replace('/\s*\[[^]]*]\s*$/u', '', $name)));
        $transcriptNameKey = $group . '|' . $nameBase;
        $pendingVideoTranscriptNames[$transcriptNameKey] = ($pendingVideoTranscriptNames[$transcriptNameKey] ?? 0) + 1;
        $transcriptBracket = preg_match('/\[([^]]*)]\s*$/u', $name, $bracketMatch) === 1 ? strtolower(trim((string) preg_replace('/[\s\x{00A0}]+/u', ' ', $bracketMatch[1]))) : '';
        $pendingVideoTranscriptBrackets[$transcriptNameKey][] = $transcriptBracket;
    }
    $videoArchive = [
        ['RJxR3ONbylQ', "Alicia's Story [C6 to A1!]", '2025'],
        ['EfS7RQit79U', "Richelle's Story [Class of 2025]", '2025'],
        ['STE5tyEo10w', "Chloe's Story [Class of 2025]", '2025'],
        ['UE2K7-92pkM', "Leona's Story [Class of 2025]", '2025'],
        ['nCfeU3UthgA', "Manqi's Story [Class of 2025]", '2025'],
        ['MY7T5Hqf8l4', "Ezel's Story [Class of 2025]", '2025'],
        ['cDz1UQe4wOI', "Amelie's Story [Class of 2025]", '2025'],
        ['V4dj5XYEcow', "Gwen's Story [Class of 2024]", '2024'],
        ['shZaStH4nGI', "Megan's Story [Class of 2024]", '2024'],
        ['xG8PcBOOhCA', "Yong Zheng's Story [Class of 2024]", '2024'],
        ['XuuBFtldw_A', "Giselle's Story [Class of 2024]", '2024'],
        ['xT8OzWLZ4w8', "Jing Ru's Story [Class of 2024]", '2024'],
        ['uewzV0bhqoc', "Jiayi's Story [Class of 2024]", '2024'],
        ['YNEW5TFVyMs', "Dyanne's Story [Class of 2024]", '2024'],
        ['H8YSD2u4tjc', "Lakshana's Story [Class of 2024]", '2024'],
        ['QzfoV5yH1Tc', "Deveshwar's Success Story [Class of 2024]", '2024'],
        ['jH3ve35ECXE', "Lebelle's Story [Class of 2024]", '2024'],
        ['bTQfAjTy0_E', "Sonia's Story [Class of 2024]", '2024'],
        ['Ycvn6yK_F0o', "Emily's Story [Class of 2024]", '2024'],
        ['-YGN_dQE3qM', "Jia En's Story [Class of 2024]", '2024'],
        ['Fxa8-Hi2dfQ', "Jing En's Story [Class of 2024]", '2024'],
        ['BZeEXmU8oQQ', "Charmaine's Story [Class of 2024]", '2024'],
        ['hqZtkbQNxxc', "Magesh's Story [Class of 2024]", '2024'],
        ['3V8xUj2mKyI', "Xiaohan's Story [Class of 2023]", '2023'],
        ['kEb-tGNNixE', "Gareth's Story [Class of 2023]", '2023'],
        ['nmng0-6Li5A', "Raniya's Story [Class of 2023]", '2023'],
        ['thLdbLZx0so', "Aryan's Story [Class of 2023]", '2023'],
        ['mzWs-4agDjY', "Isaac's Story [Class of 2023]", '2023'],
        ['QldLg8X7qAE', "Rui En's Story [Class of 2023]", '2023'],
        ['zioHbIDys0w', "Yu Heng's Story [Story of 2023]", '2023'],
        ['ADnbKkV9Isc', "Raicy's Story [Class of 2023]", '2023'],
        ['nYeFv7a5L78', "Shivani's Story [Class of 2023]", '2023'],
        ['WL8_ziqlq7s', "Giselle's Story [Class of 2023]", '2023'],
        ['dKZxiqBVXTc', "Sahanna's Story [Class of 2023]", '2023'],
        ['wXiYDwwdDgM', "Frances' Story [Class of 2023]", '2023'],
        ['W1WnUFygLhA', "Xinqiao's Story [Class of 2023]", '2023'],
        ['Cej5PUA-ICE', "Jovita's Story [Class of 2023]", '2023'],
        ['f3dfLcZ7XqY', "Lenel's Story [Class of 2023]", '2023'],
        ['PmAzr0ycX38', "Amelia's Story [Class of 2023]", '2023'],
        ['2qbdprvxXh0', "Emily's Story [Class of 2022]", '2022'],
        ['E7-rp_p1CVg', "Nathaniel's Story [Class of 2022]", '2022'],
        ['SWHGmES4VNI', "Kaitlyn's Story [Class of 2022]", '2022'],
        ['ay7w4ZZy3ec', "Febella's Story [Class of 2022]", '2022'],
        ['IXXuN2_fkTw', "Aspen's Story [Class of 2022]", '2022'],
        ['jfE_9MYrfgw', "Sharilyn's Story [Class of 2022]", '2022'],
        ['_YGWDe-MLTs', "Jessica's Story [Class of 2022]", '2022'],
        ['AQwdcGSjsOI', "Tania's Story [Class of 2022]", '2022'],
        ['83pqomh2rwE', "Janine's Story [Class of 2022]", '2022'],
        ['zq7KbeHpKYc', "Tan Ching's Story [Class of 2022]", '2022'],
        ['R1WUxV4M9gs', "Jolene's Story [Class of 2022]", '2022'],
        ['dXqkbuFYkLU', "Tonee's Story [Class of 2022]", '2022'],
        ['Z9benoNGU-E', "Phyu Phyu's Story [Class of 2022]", '2022'],
        ['O0-c-82JCes', "Glenna's Story [Class of 2022]", '2022'],
        ['bzAEu4MzPoU', "Xinyu's Story [Class of 2022]", '2022'],
        ['oVZTlaOjyRI', "Xi Ning's Story [Class of 2022]", '2022'],
        ['nc_LW_e8tA0', "Le Min's Story [Class of 2022]", '2022'],
        ['qv8sd3ciVGc', "Jen Lin's Story [Class of 2022]", '2022'],
        ['lU1ZIckiN20', "Elena's Story [Class of 2022]", '2022'],
        ['2guZtb86pN0', "Jia Heng's Story [Class of 2021]", '2021'],
        ['rcnCudMPW8I', "Agnes' Story [Class of 2021]", '2021'],
        ['JD-q5xPFmXY', "Nadya's Story [Class of 2021]", '2021'],
    ];
    foreach ($videoArchive as $index => [$videoId, $videoTitle, $videoGroup]) {
        $number = $index + 1;
        $transcript = $videoTranscripts[$videoId] ?? ['name' => $videoTitle, 'quote' => '', 'group' => $videoGroup];
        $testimonialRecords[] = [
            'seed_key' => sprintf('testimonial-video-%02d', $number),
            'title' => $transcript['name'],
            'menu_order' => $number,
            'source' => $crawlSource('/testimonials'),
            'fields' => [
                'tbt_student_name' => $transcript['name'],
                'tbt_quote' => $transcript['quote'],
                'tbt_rating' => 5,
                'tbt_video_url' => 'https://www.youtube.com/watch?v=' . $videoId,
                'tbt_display_group' => $videoGroup,
            ],
        ];
    }
    $textTestimonials = [
        ['testimonial-javier-tee', 'Javier Tee', 'VERY GOOD TEACHING LOVE IT. Ms Chelsea and Mr Keefe are really nice and patient, plus the notes are excellent.', 'home-featured'],
        ['testimonial-cayden-zhu', 'Cayden Zhu', 'TBT exposed me to how Biology can actually be fun, with interactive classes and concise notes.', 'home-featured'],
        ['testimonial-jordan-tang', 'Jordan Tang', 'The notes are incredibly concise and helped me clear the syllabus faster and memorise content more efficiently.', 'home-featured'],
        ['notes-review-jezelle', 'Jezelle', 'Thank you soooooo much for this! This is my lifesaver and my day to day grind for o level A1 in pure bio 🫶🫶🫶🫶🫶🫶🫶🫶🫶🫶🫶🫶🫶', 'notes-reviews'],
        ['notes-review-j', 'J', 'The notes were very informative and good', 'notes-reviews'],
        ['notes-review-anisatusyifa', 'Anisatusyifa', "vv useful. During olevel bio p2 i barely left any blanks like i used to cos I couldn't remember most of the points.", 'notes-reviews'],
        ['notes-review-l', 'L', 'I like how concise everything was. There were many tables and drawings that allowed me to understand just the important stuff :)', 'notes-reviews'],
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
        ['sec3-featured-isaac', 'Isaac, Sec 3 in 2025', '"Bio was horrible and I failed even after memorizing a lot of content. Ms Chelsea\'s lessons were extremely clear so I understood all the concepts. Her lesson planning and answer keys are also extremely good such that I learnt how to apply niche keywords and score marks consistently for every topic."', 'sec3-featured'],
        ['sec3-featured-dayan', 'Dayan, Sec 3 in 2025', '"Bio has always been my favourite science, and coming to TBT makes bio even more enjoyable! In TBT, content is well spread out in an organised manner so it is easy to keep tabs of what we need to know! Sometimes, we discuss Biology out of our (boring) syllabus and that gives me a deeper appreciation and understanding of certain topics!\n\nThank you TBT!"', 'sec3-featured'],
        ['sec3-featured-jun-jie', 'Jun Jie, Sec 3 in 2025', '"Before I joined TBT, I found biology quite hard to understand and honestly a bit boring at times… After joining, the way concepts are explained really helps me understand instead of just memorising for example the memory shortcuts that TBT has taught me… One thing that stood out to me is how supportive the tutors are. They are always willing to answer questions via whatsapp if I have any doubts. Overall, TBT made biology much more manageable and even enjoyable for me."', 'sec3-featured'],
        ['sec4-featured-yong-zheng', 'Yong Zheng, Sec 4 in 2024', '"Unlike school teachers who just merely read off the slides, these tutors had somehow made such a content heavy subject into something engaging... The night before bio O-levels, they were all staying up till midnight to answer their students\' questions... it really showed how much they care for their students."', 'sec4-featured'],
        ['sec4-featured-gwen', 'Gwen, Sec 4 in 2024', '"After using TBT\'s notes and memorising all the shortcuts I managed to get the model template answer and memorise it easily... TBT is a really good option because I joined and I managed to get from a failing student to an A1 student."', 'sec4-featured'],
        ['sec4-featured-aryan', 'Aryan, Sec 4 in 2024', '"Excellent tutor in terms of the amount of resources he has... Very knowledgeable and has his own personal touch to everything he does and genuinely cares for the students. Lessons are very beneficial and 100% would recommend for O Level taking students. He got me from a C6 (my Sec 3 EOY grade) to A2 in O Level 🙏"', 'sec4-featured'],
        ['notes-review-chloe', 'Chloe', 'great and helpful notes for some last minute revision! also easy to understand :)', 'notes-reviews'],
        ['notes-review-angelina', 'Angelina', 'ThatBioTutor’s notes have helped me consolidate my knowledge about the different topics very effectively and quickly. The TYS answers provided also helped me understand the keywords that I need to know. What I especially found helpful were the CAQs and answers provided, and they gave me greater confidence in my answers. I believe that ThatBioTutor is very respectable, to provided Singaporean students these documents for free and guide us kindly', 'notes-reviews'],
        ['notes-review-lohitha', 'Lohitha', 'i really love how we are able to get such good quality notes free of charge! and its even better that many students can save hours of their time of making their own notes and can just use these for their revision. thank you so much for all your efforts and i truly appreciate it!', 'notes-reviews'],
        ['notes-review-farah-haque', 'Farah Haque', 'It is helpful for last minute revision and the notes are concise and easy to read not like tb', 'notes-reviews'],
        ['notes-review-marcus', 'Marcus', 'The notes are very concise and allow me to recap on my content very easily. The memory shortcuts were also helpful in memorising answering techniques which was tedious to do. The organisation of the notes really made studying Bio a lot easier', 'notes-reviews'],
        ['notes-review-kelly', 'Kelly', 'the notes are extremely concise yet still thoroughly explain each topic and well-organised! it is easy to understand, i wish i found these notes earlier', 'notes-reviews'],
        ['notes-review-c', 'C', 'the bio notes further reinforced my bio concepts and also helped me recall them better with those memory hacks and acronyms (i love Really Cool Pupil Effect the most) i think im confident that i can actually score a1 for bio!!! thank you mr keefe and the team behind TBT!!!', 'notes-reviews'],
        ['notes-review-megan', 'Megan', 'it was really helpful and thorough! I liked that there was a "how it works" page and all of it was really clear with visuals', 'notes-reviews'],
        ['notes-review-lee-yang', 'Lee Yang', 'Very concise and accurate bio notes, it will definitely be of immense help for revision/learning purposes!', 'notes-reviews'],
        ['notes-review-andrea', 'Andrea', 'its superrr clear 😭 its really easy to understand because its compiled really nicely, it gets you your a1 trusttt 😋😋', 'notes-reviews'],
        ['notes-review-xin-yi', 'Xin Yi', 'I like how the notes are really concise, giving you the most important aspects of each topic that you should focus on! Although I have yet to finish scheming through all of the chapters, I’ve already gained new information and value from special, specific details that allowed me to understand certain concepts in a new perspective. Plus, the notes are really easy to understand as well.', 'notes-reviews'],
        ['notes-review-aaron', 'Aaron', 'Your free notes are very straight to the point and are very concise. I also do love the fact that you really go above and beyond to teach us how to remember the notes on top of simplifying everything. The notes is a very powerful tool and I love it a lot!', 'notes-reviews'],
        ['cell-review-anita', 'Anita', "It's detailed and has analogies to help visualise the info and understand it much better :)", 'celldivision-reviews'],
        ['cell-review-jeeva', 'jeeva', 'i loved the videos a lot! really helped me give so much of clarity as schools usually go at a fast pace, so an in depth explanation was so useful. and the fact that the videos are always accessible makes it chefs kiss. thanks so much keefe!!!! ❤️', 'celldivision-reviews'],
        ['cell-review-lihna', 'Lihna', "Hii.. i ve watched all the vid.. it was really very useful.. like some of the concepts have not really been taught in my school like individual assortment, and i was very confused abt them. But after watching the vid, i could understand them better. Also, the graph qn was my school ws, but i didnt know how to do it even after the teacher went through it. Now can undertand it.. u were very detailed on how to draw the line as well.. for this topic , i was unsure of how to write the ans , but after watching the video , i know the way to ans and keywords to include..\nI was also very confused with chroma terms. But now got a clear pic of it..\nU were very detailed in ur explanation, which helped me understand meiosis better as it was confusing bfr... thanks for the vid..!!!", 'celldivision-reviews'],
        ['cell-review-hansini', 'Hansini', "Hello Mr Keefe!!\nI found them very helpful in understanding common misconceptions on mitosis and meiosis\nIt really helped to clarify my doubts regarding the topic!\nThank you!!", 'celldivision-reviews'],
        ['cell-review-louisa', 'louisa', "hi! i've watched ur lesson clips and i think it's SUPER helpful! ur memory hacks and analogies are all really unique and it helps make understanding the concepts and remembering it a lot easier. it also stands out alot and i find that while trying out practice questions on cell division after watching ur videos i actually can understand the question better and know what points to write in order to get the marks. I actually watched some of the videos on ur ig page and quite a lot of the tips are things i haven't been doing so i think those will rlly help with my bio too!\nthank you sm for sharing this google drive with me it rlly helped alot !!", 'celldivision-reviews'],
        ['cell-review-amelia', 'amelia', "hi mr keefe :)\nive watched almost all the vids and i really appreciate the fact that you break down the terms and relate them to easy to remember things like how to remember what are asters. In your explanation you also colour coded and showed the visual steps on what happens at each stage so i think that was really helpful because before i was quite confused about what happens at each stage and the changes etc", 'celldivision-reviews'],
        ['cell-review-anshika', 'Anshika', "Hi Mr Keefe,\nI’ve watched the videos on your crash course for cell division and I really really found them very helpful. Some things I loved about the videos are that it is so well explained and structured and it is very easy to understand, especially with all the drawings etc. I also loved how well it was being explained and honestly it has really cleared all my doubts on this topic. It has really covered the full content really really well. I love the fact that for the 2 processes, you have one video that is brief and another that is detailed, really helps for revision ( especially last minute ones )\nThe diploid vs haploid analogy was really helpful too as it can be quite confusing at times.\nHonestly, overall it was a really good series of videos and was super helpful, and it has really helped to clear all my doubts regarding this topic. I really look forward to your upcoming contents ( on a side note, I really really love your content and is super helpful and easy to retain )\nThank you once again 😃", 'celldivision-reviews'],
        ['cell-review-edgar', 'Edgar', "The clips were very useful in my understanding of the cell division concepts😍\n1. Helpful in understanding the differences between chromosomes, chromatin, chromatids and daughter chromosomes. It’s not as confusing as compared to my school’s elaborations.\n2. Annotations and elaboration on the chromosome and DNA graph is quite beneficial (it’s slightly difficult to understand the graph without the video’s explanation). Moreover I find that the questions asked in the video stimulates my brain to think while watching the videos.\n3. Lots of analogies that really aid in my understanding of the concepts\n4. Overall, addresses all the necessary concepts required for exams.", 'celldivision-reviews'],
        ['cell-review-venus', 'Venus', 'Hi Mr Keefe, thank you so much again for the lesson clips!! I find it really helpful as you use simple words to explain the terms for mitosis and meiosis, unlike what i learn in school because it only feels like as if foreign words with long definitions are thrown at me for me to memorise them all.(eg the chroma terms which really confused me at first) I also like how you draw them out with colour because i am a visual learner. My school notes are always just black and white, and the bio textbook is so lengthy that i only feel discouraged. The lesson clips also include small memory hacks like the ones in your notes and reels, making it so much more easy and fun for me to learn.', 'celldivision-reviews'],
        ['cell-review-vjun', 'vjun', "hi! i’ve watch all the clips from the google drive. would like to mention some things\n\n1. i really like the analogy given to help better understand the concept (eg. homologous chromosome is like the same person in a parallel uni)\n\n2. the diagrams drawn are really specific in details which really helps to visualise the entire process\n\n3. the clip for independent assortment really helped me to understand how does it leads to genetic variations (esp the bridging of different possibilities)\n\n4. the explanations were explained in a simpler manner as compared to tb & notes which helped me to understand the entire chapter properly\n\nto sum up, the clips were really helpful & they’re all in sectional clips making learning more specific & easier for me to target my learning", 'celldivision-reviews'],
        ['cell-review-lia', 'lia', "the lesson clips really provided me with a deeper understanding on the different terms used in cell division, I feel that all my doubts have been clarified just by watching all the clips and now I know what to include in my answers after watching the videos\nthank you so much and I really appreciate the clips 🙏", 'celldivision-reviews'],
        ['cell-review-qx', 'qx', "the videos on how chromosome no. and DNA amount changes during meiosis/mitosis was v helpful for me as my bio tcher didn't rlly gave us a full summary so the explanation was super clear! highly recommended :)", 'celldivision-reviews'],
        ['cell-review-y1', 'Y1', "Tysm. I liked that qns types were given and there were long detailed descriptions for me to understand. The videos are also vv clear to me.\nThis really helped me understand more about mitosis and meiosis.\nThis crash course was vv explainable and understandable, easy to go back and convenient all-in-all.", 'celldivision-reviews'],
        ['cell-review-sl', 'sl', 'The videos were very helpful! I managed to have a better understanding of meiosis as the examples that you gave made it easy for me to understand how the number of chromosomes and DNA can be different. The example to understand the difference between diploid and haploid were also very interesting, keeping me engaged while learning. Thank you!', 'celldivision-reviews'],
        ['cell-review-roy', 'roy', 'i just finished going through the lesson clips. In the lesson, the analogies used were very useful. They were simple to understand and they illustrate the concept in a much more simplified manner, making it easier to visualise and understand the concept. The use of acronyms also make it easier to memorise keywords and since there is constant reference to the acronyms when explaining the concepts, it helps us to really memorise the acronyms and ultimately the keywords. Concepts were also explained in a very detailed and concise manner, by breaking down the concept and explaining them in a step by step manner, ensuring that we do not get lost in the midst of the large amount of content. In conclusion, it was very helpful for me to really understand cell division. 👍👍', 'celldivision-reviews'],
        ['testimonial-2021-isa', 'Isa [C5 to A1 in 3 months]', '"the notes given and aid provided was very helpful during my o levels, especially the suggested answers for common oeqs. mr keefe also helped to clear up many misconceptions I had during lessons and also outside of lessons. he is a very supportive tutor who helped me get from c5 for prelims to a1 for o levels. thank you mr keefe"', '2021'],
        ['testimonial-2021-melissa', 'Melissa [A2 for Pure Bio]', '"He is a dedicated tutor that will patiently guide you through lessons and answer any questions or doubts you may have at any point in time... he teaches us answering techniques too instead of only content so that we can attain the highest possible marks in exams."', '2021'],
        ['testimonial-2021-shams', 'Shams [F9 to C6 for Pure Bio]', '"The teacher is very encouraging and caring. Lessons are planned in an organised manner such that learning content and doing practice questions make it easier to understand the topics better."', '2021'],
        ['testimonial-2021-emelia', 'Emelia [D7 to B3]', '"He is a very approachable tutor and plays a big part in helping me comprehend all the topics that are taught very well... He teaches useful tips that allows me to remember points a lot more efficiently. Being very mindful, he pays special attention to every student and I find his guidance very helpful."', '2021'],
        ['testimonial-2021-jolie', 'Jolie [C5 to B3 in 2 months]', '"This class is an all in one package, as the lessons are all recorded so you can always refer back for revision. There are also homework questions provided, and even if you can’t complete it due to commitments, the tutor is very understanding.\n\nWhenever I have any doubts or misconceptions, he is able to answer them quickly, and he is also very patient and understanding. You don’t need to do notes and flashcards from scratch which helps to save time if time is not on your side and you need to do very quick revision."', '2021'],
    ];
    foreach ($additionalReviews as $index => [$seedKey, $name, $quote, $group]) {
        $reviewRoute = $group === 'notes-reviews'
            ? '/notes'
            : ($group === 'celldivision-reviews' ? '/celldivision' : '/testimonials');
        $featuredGrades = [
            'sec3-featured-isaac' => ['D7', 'A2'],
            'sec3-featured-dayan' => ['B4', 'A2'],
            'sec3-featured-jun-jie' => ['B4', 'B3'],
            'sec4-featured-yong-zheng' => ['E8', 'A1 in 3 months'],
            'sec4-featured-gwen' => ['F9', 'A1'],
            'sec4-featured-aryan' => ['C6', 'A2'],
        ];
        $fields = ['tbt_student_name' => $name, 'tbt_quote' => $quote, 'tbt_rating' => 5, 'tbt_video_url' => '', 'tbt_display_group' => $group];
        if (isset($featuredGrades[$seedKey])) {
            [$fields['tbt_before_result'], $fields['tbt_after_result']] = $featuredGrades[$seedKey];
        }
        $testimonialRecords[] = ['seed_key' => $seedKey, 'title' => $name, 'menu_order' => 200 + $index, 'source' => $crawlSource($reviewRoute), 'fields' => $fields];
    }

    $publicArchivePath = dirname(__DIR__) . '/assets/content/public-review-archive.json';
    $publicArchive = is_file($publicArchivePath) ? json_decode((string) file_get_contents($publicArchivePath), true) : null;
    $publicReviewSeedKeys = [];
    foreach (($publicArchive['reviews'] ?? []) as $index => $review) {
        $name = trim((string) ($review['name'] ?? ''));
        $quote = trim((string) ($review['quote'] ?? ''));
        $group = trim((string) ($review['group'] ?? ''));
        if ($name === '' || $quote === '' || !in_array($group, ['2025', '2024', '2023', '2022', '2021', 'notes-reviews-bottom'], true)) {
            continue;
        }
        $nameBase = strtolower(trim((string) preg_replace('/\s*\[[^]]*]\s*$/u', '', $name)));
        $transcriptNameKey = $group . '|' . $nameBase;
        $displayGroup = $group;
        $reviewBracket = preg_match('/\[([^]]*)]\s*$/u', $name, $reviewBracketMatch) === 1 ? strtolower(trim((string) preg_replace('/[\s\x{00A0}]+/u', ' ', $reviewBracketMatch[1]))) : '';
        $bracketIndex = array_search($reviewBracket, $pendingVideoTranscriptBrackets[$transcriptNameKey] ?? [], true);
        if ($bracketIndex === false && $reviewBracket === '') {
            $bracketIndex = 0;
        }
        if (($pendingVideoTranscriptNames[$transcriptNameKey] ?? 0) > 0 && $bracketIndex !== false) {
            $pendingVideoTranscriptNames[$transcriptNameKey]--;
            array_splice($pendingVideoTranscriptBrackets[$transcriptNameKey], (int) $bracketIndex, 1);
            $displayGroup = 'video-transcript-source';
        }
        $seedKey = 'public-review-' . sha1($group . "\0" . $name . "\0" . $quote);
        if (isset($publicReviewSeedKeys[$seedKey])) {
            continue;
        }
        $publicReviewSeedKeys[$seedKey] = true;
        $testimonialRecords[] = [
            'seed_key' => $seedKey,
            'title' => $name,
            'menu_order' => 500 + (int) $index,
            'source' => $crawlSource($group === 'notes-reviews-bottom' ? '/notes' : '/testimonials'),
            'fields' => [
                'tbt_student_name' => $name,
                'tbt_quote' => $quote,
                'tbt_rating' => 5,
                'tbt_video_url' => '',
                'tbt_display_group' => $displayGroup,
            ],
        ];
    }

    $sharedClassFaqs = [
        ['Can my child try a lesson before committing?', "A: Yes — WhatsApp us to request a trial lesson. One of our staff will respond ASAP to arrange the details.<br><br>We're so confident your child will love the lesson that we offer a 100% money-back guarantee on the trial. If they didn't love it, just let us know and we'll refund you fully — no questions asked."],
        ['Face-to-face or online — which should my child choose?', 'A: Both formats cover the same content and use the same TBT methods. Face-to-face classes are at Burlington Square, S(189649). Online lessons are held on Zoom. Choose based on what suits your child better — your child can also switch between formats from week to week.'],
        ['How many students are in a class?', "A: 20-25 per timeslot. Sometimes, it doesn't even reach that number since some students may opt to join online on a particular week.<br><br>If you were to come early, you can sit at the front. If you have questions, you can come earlier or stay back a bit later to ask your tutor.<br><br>None of our students found it impossible to get their questions answered, since we allow them to message their tutor all week-long via WhatsApp. Many of our students find the class size engaging and effective for learning!<br><br>If you still are concerned, try a trial lesson – and see why students still love the format – and the results it produces.<br><br>We're so confident you will love the lesson that we offer a 100% money-back guarantee on the trial. If you didn't love it, just let us know and we'll refund you fully — no questions asked."],
        ['What if my child misses a class?', "A: They can join any other available timeslot that week, even if it's a different tutor — our content is standardised across all classes.<br><br>If they can't make any slot, full lesson recordings along with all relevant worksheets / answer keys are uploaded after every class, so your child can catch up anytime, anywhere."],
        ['Does my child need to turn on the camera for online lessons?', 'A: No — camera is not required. Students can respond to tutor questions via the Zoom chat.'],
        ['Is there a lot of homework?', 'A: Small weekly set — highly recommended, but not compulsory. Students who complete it earn Homework Points – rewards given out after each term.'],
        ['How do I find out about fees and available timeslots?', 'A: WhatsApp us — our friendly staff will share the most updated fees and timeslot availability ASAP.'],
        ['What’s the difference between the timeslots?', 'A: We offer different class timings to suit students’ schedules. Our staff will share the latest schedule via WhatsApp to ensure you receive the most updated information. No matter which timeslot you go to, the content and methods taught is the same across tutors, as we standardise it for every lesson.'],
    ];
    $formatFaqAnswer = static function (array $faqs, string $venue): array {
        foreach ($faqs as $i => $faq) {
            if (str_starts_with($faq[0], 'Face-to-face or online')) {
                $faqs[$i][1] = 'A: Both formats cover the same content and use the same TBT methods. ' . $venue . ' at Burlington Square, S(189649). Online lessons are held on Zoom. Choose based on what suits your child better - your child can also switch between formats from week to week.';
            }
        }
        return $faqs;
    };
    $faqCollections = [
        'home' => [
            ['Do you teach physical lessons?', 'Yes! Starting 2024, you choose between coming online or physically.'],
            ['Do you have hands-on practical sessions?', 'Yes, our hands-on practical sessions cover the most commonly tested experiments, so students walk into Paper 3 with the hand-eye coordination and confidence to protect the full 20% of their grade.'],
            ['Other than weekly lessons, what are the other perks of being a student here?', 'There are a plethora of benefits, including notes, flashcards, lesson clips, extra practice papers, week-long WhatsApp help, snacks when you come for physical lessons... just to name a few!<br><br>All these extra perks and resources are free of charge for our students. For more details please watch the video here.'],
            ['Do I need to turn on my mic/camera during online lessons?', 'It is not compulsory, though we encourage students to respond to questions their tutor poses to the class via the zoom chat!'],
            ['Is there a lot of homework?', "The weekly homework consists of a few questions, and is highly manageable (We don't believe in overloading students with homework when they already have enough from school).<br><br>Homework is also not compulsory to do, although if you do it, you can claim Homework Points for rewards! (e.g. Bubble Tea, Starbucks Gift Card)"],
            ['How many students are there in a class?', "The cap for physical class will be around 20-25 students. Don't be daunted by the numbers, try it out yourself in a trial lesson, and see why so many students still love our classes.<br><br>We're so confident your child will love the lesson that we offer a 100% money-back guarantee on the trial. If they didn't love it, just let us know and we'll refund you fully — no questions asked."],
            ['What levels do you teach?', 'We teach only O Level Pure/G3 Biology, so we can truly specialise and develop the best lessons, teaching methods and resources for our students.<br><br>Sec 3s, classes open for you March of each year!'],
            ['Do you teach combined bio?', 'At the moment, only Pure Bio.'],
            ['Do you teach 1-1 tuition?', 'Unfortunately, we have too many students to afford the time to teach 1-1. Good news: Our students can ask us questions 1-1 on WhatsApp, all week long!'],
            ['But what if I can’t ask all the questions I want in a group class?', 'Our students never face this problem because they get week-long WhatsApp help.<br><br>We even take the time to voice message them explanations if the question is more complex.'],
            ['Are all the classes taught by Mr Keefe?', 'No, as we have a small and elite team of tutors who each take classes of their own.<br><br>Rest assured that no matter which timeslot you go to, the content and methods taught is the same across tutors, as we standardise it for every lesson.'],
            ['Can I try just one lesson?', "Yes! To request for a trial lesson, drop us a WhatsApp message— one of our friendly staff will respond ASAP!<br><br>We're so confident you will love the lesson that we offer a 100% money-back guarantee on the trial. If you didn't love it, just let us know and we'll refund you fully — no questions asked."],
            ['Can I know more about the class fees and lesson timeslots?', 'Because we offer several class options tailored to different needs, fees may vary.<br>Our team will also provide the most updated timeslot availability through WhatsApp.<br><br>To know more, drop us a message — one of our friendly staff will respond ASAP!'],
        ],
        'classes' => [
            ['What is the difference between Pure Biology and Combined Science?', 'A: Pure Biology is a full subject (Syllabus 6093) with 3 papers (It will be renamed to G3 Biology starting 2027). Combined Science shares exam papers with another subject. Pure Bio carries more weight in the L1R5/L1R4 score and is required for certain post-secondary courses.'],
            ['Is Pure Biology harder than Combined Science?', 'A: Yes — Pure / G3 Bio covers more content and has stricter marking. But with the right study approach and answering technique, it is very manageable. Despite most TBT students starting below B4, a large majority of TBT students end up scoring B3 or higher.'],
            ['What topics are covered in O Level Pure Biology?', 'A: These are the main categories of topics, although every school organises them differently: Cell Biology · Biological Molecules · Movement of Substances · Nutrition · Transport · Respiration · Excretion · Nervous System and Coordination · Reproduction. (Cambridge Syllabus 6093)'],
            ['When should my child start Pure Biology tuition?', 'A: The earlier the better — ideally at the start of Sec 3. But late is better than never. Some students who started in Sec 4, even after prelims, still improved significantly (e.g. C6 to B3). 69.21% of TBT students joined below B4, yet 91% end up scoring B3 or higher in O levels.'],
            ['Does TBT teach Combined Biology students too?', 'A: No – TBT specialises in Pure / G3 Biology only. This level of focus allows us to develop the best possible materials, techniques and resources specifically for Pure / G3 Biology students.'],
        ],
        'sec3' => array_merge([
            ['How is the sec 3 programme structured across the year?', 'A: The Sec 3 programme is split into two phases, each building progressively towards exam readiness:<br><br>1. Content Teaching Phase (Mar - Aug)<br><br>2. EOY Intensive Lessons (Sept - Oct)<br><br>Students are allowed to join anytime throughout the year, provided there are available spots for the class. Lesson recordings and worksheets are fully provided for those lessons you missed prior to joining.'],
            ['Do you have hands-on practical sessions?', 'A: Yes, our hands-on practical sessions cover the most commonly tested experiments, so students walk into Paper 3 with the hand-eye coordination and confidence to protect the full 20% of their grade.'],
            ['My child just started Sec 3 — is it too early to join?', 'A: No — starting early is the single best thing a Sec 3 student can do. The earlier they build a solid content foundation and answering techniques, the less stress they’ll have and the less catching up they’ll need in Sec 4.'],
            ['When do Sec 3 classes open?', 'A: Sec 3 classes open every March. WhatsApp us early to get on the waitlist — spots fill up quickly.'],
            ['What if my child’s school has not covered certain topics yet?', 'A: Students are welcome to join even if certain topics have not been covered in school. Rest assured, all topics are already part of our programme and will be covered in due course. If you missed lessons where we first taught a topic, they’ve all been recorded for you along with the full worksheets.<br><br>Students have also said they greatly benefitted from first learning a topic at TBT, because it became so easy to understand when their school later taught it.'],
        ], $formatFaqAnswer($sharedClassFaqs, 'Face-to-face classes')),
        'sec4' => array_merge([
            ['How is the sec 4 programme structured across the year?', 'A: The Sec 4 programme is split into three phases, each building progressively towards exam readiness:<br><br>1. Content Teaching Phase (Dec - May)<br><br>2. Prelim Intensive Lessons (May - Aug)<br><br>3. Os Intensive Lessons (Aug - Nov)<br><br>Students are allowed to join anytime throughout the year, provided there are available spots for the class. Lesson recordings and worksheets are fully provided for those lessons you missed prior to joining.'],
            ['Do you have hands-on practical sessions?', 'Yes, our hands-on practical sessions cover the most commonly tested experiments, so students walk into Paper 3 with the hand-eye coordination and confidence to protect the full 20% of their grade.'],
            ['My child is already in Sec 4 — is it too late to join?', 'A: Not at all. 69.21% of TBT students joined at B4 or lower, and the average TBT student improved 2.3 grades (~C5 to A2). Many students who joined post-prelims have gone from C6 to B3 in two months. That said, the sooner a student starts fixing bio by joining TBT, the better.'],
            ['What if my child’s school has not covered certain topics yet?', 'A: Students are welcome to join even if certain topics have not been covered in school. Rest assured, all topics are already part of our programme and will be covered in due course. If you missed lessons where we first taught a topic, they’ve all been recorded for you along with the full worksheets.<br><br>Students have also said they greatly benefitted from first learning a topic at TBT, because it became so easy to understand when their school later taught it.'],
        ], $formatFaqAnswer($sharedClassFaqs, 'F2F is')),
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
        'version' => '2026-11-04.5',
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
            'address' => "175 Bencoolen St\n#01-45\nSingapore 189649",
            'map_url' => 'https://www.google.com/maps/search/?api=1&query=175+Bencoolen+Street+Singapore+189649',
            'opening_hours' => "Friday – Thursday\n11am – 9:30pm\n(open all 7 days)",
            'footer_description' => "Singapore's only O Level Pure/G3 Biology specialist.",
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
                    'classes_eyebrow' => 'O Level Pure Biology Tuition Singapore · Sec 3 & Sec 4',
                    'classes_hero_lead' => "ThatBioTutor | Singapore's only “O” Level Pure / G3 Bio specialist",
                    'classes_level_heading' => 'Ready to find out more? Choose your level below.',
                    'classes_intro_heading' => 'What exactly is O Level Pure/G3 Biology?',
                    'classes_intro_copy' => 'O Level Pure Biology, syllabus 6093 (renamed to G3 Biology starting 2027) is a full-subject O Level science taken by Sec 3 and Sec 4 students. Unlike Combined Science, Pure Bio students are examined on the complete Cambridge syllabus — making it more rigorous but also worth a full subject grade on the L1R5/L1R4 score.',
                    'classes_struggle_heading' => 'Why do so many students struggle with Pure/ G3 Biology?',
                    'classes_solution_heading' => 'How TBT solves each one:',
                    'classes_difference_heading' => 'What makes our classes unlike any other',
                ],
            ],
            'sec-3-pure-g3-biology-tuition' => [
                'source' => $crawlSource('/sec-3-pure-g3-biology-tuition'),
                'fields' => [
                    'sec3_heading' => 'The Best Time to Start Pure / G3 Biology Tuition is at the Start of Sec 3. The Second Best Time is Now!',
                    'sec3_eyebrow' => 'Sec 3 Pure / G3 Biology Tuition Singapore · Face-to-Face & Online · Classes Open March',
                    'sec3_hero_lead' => 'Build the right foundation before Sec 4 pressure hits and give yourself the best shot at A1 when O Levels come.',
                    'sec3_options_intro' => 'Both formats teach the same TBT content and methods — standardised across all tutors.',
                    'sec3_cover_heading' => 'What TBT Sec 3 classes cover',
                    'sec3_topics_heading' => 'Sec 3 Topics:',
                ],
            ],
            'sec-4-pure-g3-biology-tuition' => [
                'source' => $crawlSource('/sec-4-pure-g3-biology-tuition'),
                'fields' => [
                    'sec4_heading' => "From B4 or worse to A in O Levels is still possible. Here's the proof.",
                    'sec4_eyebrow' => 'Sec 4 Pure / G3 Biology Tuition Singapore · Face-to-Face & Online · O Level Focused',
                    'sec4_hero_lead' => '69.21% of TBT students started at B4 or lower... yet 91% of them go on to score A1/A2/B3 in O levels. With the right method, high syllabus accuracy and patient support, Pure / G3 Biology can still be a subject you do well in L1R5.',
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
                    'notes_access_heading' => 'Simply Enter Your Email Below And A Copy Will Be Sent To You.',
                    'notes_access_copy' => "After submitting the form, you'll be automatically redirected to the homepage.",
                    'notes_fit_heading' => 'Hey Pure Bio Student, is this you?',
                    'notes_fit_answer' => "If you answered 'yes' to the above, these notes were made for you. I spent hundreds of hours making these concise notes so that students like you can have an easy time studying.",
                    'notes_download_heading' => 'How do I download it?',
                    'notes_download_copy' => 'Enter your best email address below and a copy will be sent to you',
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
                    'tuitionrecs_heading' => 'Tuition Recommendations',
                ],
            ],
            'intensivefix' => [
                'source' => [
                    'type' => 'supplied-asset-group',
                    'reference' => '2A. intensivefix graphics + 2B. Intensivefix reviews + approved maintained page',
                    'captured' => '2026-11-02',
                ],
                'fields' => [
                    'intensivefix_heading' => 'Fix Your Entire Sec 3 Bio Foundation Before the Chaotic Sec 4 Year Begins — In Just 4 DAYS',
                    'intensivefix_cta_label' => 'Secure My Spot In the Intensive Fix Class',
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
                        'tbt_tutor_short_bio' => 'Ex-MOE biology teacher with 11+ years of Singapore school experience — reviewing our syllabus to match exactly what the O Level exams test.',
                    ],
                ],
            ],
        ],
    ];
}

