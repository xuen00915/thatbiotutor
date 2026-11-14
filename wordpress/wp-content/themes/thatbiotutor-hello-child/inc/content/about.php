<?php
/** About Us — everything the client can edit on this page. Layout and section order stay in page-about.php. */
declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}

return [
    'page' => 'about',
    'sections' => [
        tbt_f_section('hero', 'Top banner', [
            tbt_f_image('about_hero_image', 'Banner picture', 'curated_heroes_about_original'),
        ], 'The page title ("About ThatBioTutor") is edited in the Page content tab.'),

        tbt_f_section('mission', 'Our Mission', [
            tbt_f_rich('about_mission_tagline', 'Big line', 'SUCCESS TO US = <mark>TRULY HELPING STUDENTS.</mark>', 'One line. Select words and press Highlight to mark them in green.'),
            tbt_f_rich('about_mission_body', 'Text', <<<'HTML'
<p>There is a <strong>severe need</strong> among <u>O Level Pure/G3 Biology</u> students in the country.</p>
<p>Many struggle due to a lack of <mark>high-quality, engaging teaching,</mark> leaving them feeling lost and discouraged.</p>
<p>Students deserve a tuition centre that provides <mark>reliable, accurate instruction tailored to the latest MOE syllabus.</mark></p>
<p>This is what ThatBioTutor was created for — <mark>specialising in O Level Pure/G3 Bio since 2021.</mark></p>
<p>We equip students with proven <mark>memorisation techniques, answering skills, and exam-smart habits.</mark></p>
<p>As Singapore’s premiere centre for O Level Biology tuition, we not only deliver <u>outstanding results...</u></p>
<p>But also inspire a genuine <strong>love for the subject.</strong></p>
HTML, 'Each paragraph is one block of the page. Use Bold, Underline and Highlight to emphasise words.'),
        ]),

        tbt_f_section('quality', 'Quality Teaching', [
            tbt_f_rich('about_quality_body', 'Text and bullet list', <<<'HTML'
<p><strong>Quality in teaching is our upmost priority,</strong> which we ensure in the following ways:</p>
<ul>
<li>All tutors at TBT go through <mark>rigorous screening and testing</mark> that can take up to a month, before ever teaching their first formal class.</li>
<li><mark>A tutors' coaching session</mark> is held weekly, where tutors are mentored by the founder (Mr Keefe).</li>
<li>Every single class ends with an <u>after-lesson feedback form</u>, for students to give feedback and point out any errors. During our weekly tutors' coaching session, we <mark>review and act upon the feedback weekly</mark> to make sure every class is better than the last.</li>
</ul>
HTML, 'The bullet list uses the bullet button in the toolbar.'),
            tbt_f_image('about_quality_image', 'Picture under the list', 'curated_about_lesson_feedback'),
        ]),

        tbt_f_section('tutors', 'Our Tutors', [], 'Tutors are edited under <strong>Tutors</strong> in the left-hand menu: name, picture, qualifications, LinkedIn link and certificates. Use the Order number to change who appears first and Draft to hide a tutor.'),

        tbt_f_section('founder', 'Message From The Founder', [
            tbt_f_image('about_founder_image', 'Founder picture', 'curated_about_founder_message'),
            tbt_f_text('about_founder_q1', 'Question 1', 'Q: Why did you start ThatBioTutor?'),
            tbt_f_rich('about_founder_a1', 'Answer 1', <<<'HTML'
<p>A: Like many of you, I struggled with certain subjects throughout my school journey. I wanted to improve, but <u>didn't get enough help from my teachers.</u> Sometimes they're too busy, and other times… well, let's just say they weren't too enthusiastic about the subject themselves.</p>
<p>In JC, I took up Econs tuition when I realised my grades were falling. That was when I first experienced the power of <u>a truly capable tutor.</u> I learnt how to memorise content easier and how to answer questions better. The subject became so much easier... It even became one of my favourite!</p>
<p>I want to give you that <u>transformative experience</u> as well. Many students are struggling with bio, and you might be too. But it doesn't have to be that way. I wish to change that by giving high quality lessons, taught in an enthusiastic and clear manner. Because you guys deserve the best.</p>
HTML),
            tbt_f_text('about_founder_q2', 'Question 2', 'Q: Why is it important that students who really want to do well can do so?'),
            tbt_f_rich('about_founder_a2', 'Answer 2', <<<'HTML'
<p>A: I’ve experienced what it’s like to always struggle in a subject. It made me feel inadequate, stupid and worthless. I don’t want them to feel that way.</p>
<p>I want them to see that whatever they put their mind to, they can achieve. They can change and improve themselves, wherever their starting point may be. I want them to see that they are inherently highly capable people, increasing their self-confidence and self-worth.</p>
HTML),
            tbt_f_text('about_founder_q3', 'Question 3', 'Q: Why is it that students having high self-confidence important to you?'),
            tbt_f_rich('about_founder_a3', 'Answer 3', <<<'HTML'
<p>A: I came from a rocky childhood, and I’m not a psychologist, but I guess that was a big reason for my feelings of low self-worth during childhood.</p>
<p>I don’t want my students to feel that way too: that they’re not good enough, not smart enough, or they’re inadequate. I don’t want them to lead defeated lives.</p>
<p>What set me free of that was proving to myself that I can achieve whatever I set my mind to. Whether it was learning how to solve a Rubik’s cube in 15 seconds, building up my body and physique from the scrawny kid I was, or even building this tuition business… each event showed me that we humans are <u><em>capable of change and improvement.</em></u></p>
<p>Through transforming their bio grades, I want my students to see that <u><em>they can achieve whatever they set their mind to.</em></u> I want them to leave our class having a higher self-esteem and confidence in themselves than before they entered.</p>
HTML),
            tbt_f_text('about_founder_signature', 'Signature', '-Mr Keefe'),
        ], 'Each question and answer is one card. Leave a question empty to hide that card.'),

        tbt_f_section('beliefs', 'What We Believe In', [
            tbt_f_text('about_belief_1_title', 'Belief 1 — title', 'Vision'),
            tbt_f_area('about_belief_1_text', 'Belief 1 — text', "To provide high quality biology education to students who genuinely want to improve, but can't because they're not getting the help they need from school. Our slots will be highly sought-after, our tutors passionate, and the causes we support meaningful."),
            tbt_f_text('about_belief_2_title', 'Belief 2 — title', 'Mission'),
            tbt_f_area('about_belief_2_text', 'Belief 2 — text', 'Empower students to realise their inherent worth by showing them they can conquer biology... and by treating them like royalty.'),
            tbt_f_text('about_belief_3_title', 'Belief 3 — title', 'Values'),
            tbt_f_area('about_belief_3_text', 'Belief 3 — text', 'Obsessive greatness. Speed is king. Always do right by the customer.'),
            tbt_f_image('about_beliefs_image', 'Picture under the beliefs', 'curated_about_beliefs'),
        ]),
    ],
];
