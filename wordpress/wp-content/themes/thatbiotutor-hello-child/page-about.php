<?php
/* Template Name: About Us */
declare(strict_types=1);
if (!defined('ABSPATH')) { exit; }
get_header();
$tbt_defaults = tbt_reference_content()['pages']['about']['fields'];
$tbt_field = static fn (string $name): string => (string) tbt_page_field($name, $tbt_defaults[$name] ?? '');
?>
<article class="tbt-institutional-page tbt-about-page">
    <header class="tbt-about-hero">
        <?php tbt_render_media_image('curated_heroes_about_original', 'tbt-about-hero__image', ['loading' => 'eager']); ?>
        <div class="tbt-about-hero__title"><h1 aria-label="About ThatBioTutor"><span>About</span> <strong>ThatBioTutor</strong></h1></div>
    </header>

    <section class="tbt-section tbt-institutional-section tbt-about-mission">
        <div class="tbt-title-bar"><h2><?php echo esc_html($tbt_field('about_mission_heading')); ?></h2></div>
        <div class="tbt-reading-width">
            <h3 aria-label="SUCCESS TO US = TRULY HELPING STUDENTS.">SUCCESS TO US = <mark>TRULY HELPING STUDENTS.</mark></h3>
            <p>There is a <strong>severe need</strong> among <u>O Level Pure/G3 Biology</u> students in the country.</p>
            <p>Many struggle due to a lack of <mark>high-quality, engaging teaching,</mark> leaving them feeling lost and discouraged.</p>
            <p>Students deserve a tuition centre that provides <mark>reliable, accurate instruction tailored to the latest MOE syllabus.</mark></p>
            <p>This is what ThatBioTutor was created for — <mark>specialising in O Level Pure/G3 Bio since 2021.</mark></p>
            <p>We equip students with proven <mark>memorisation techniques, answering skills, and exam-smart habits.</mark></p>
            <p>As Singapore’s premiere centre for O Level Biology tuition, we not only deliver <u>outstanding results...</u></p>
            <p>But also inspire a genuine <strong>love for the subject.</strong></p>
        </div>
    </section>

    <section class="tbt-section tbt-section--soft tbt-institutional-section tbt-about-quality">
        <div class="tbt-title-bar"><h2><?php echo esc_html($tbt_field('about_quality_heading')); ?></h2></div>
        <div class="tbt-reading-width">
            <p><strong>Quality in teaching is our upmost priority,</strong> which we ensure in the following ways:</p>
            <ul><li>All tutors at TBT go through <mark>rigorous screening and testing</mark> that can take up to a month before teaching their first formal class.</li><li><mark>A tutors' coaching session</mark> is held weekly, where tutors are mentored by the founder, Mr Keefe.</li><li>Every class ends with an <u>after-lesson feedback form</u>. During weekly coaching, we <mark>review and act upon the feedback weekly</mark> to make sure every class is better than the last.</li></ul>
            <?php tbt_render_media_image('curated_about_lesson_feedback', 'tbt-about-quality__image', ['loading' => 'lazy']); ?>
        </div>
    </section>

    <section class="tbt-section tbt-institutional-section"><p class="tbt-eyebrow">Specialist guidance</p><h2><?php echo esc_html($tbt_field('about_tutors_heading')); ?></h2><?php tbt_render_tutors(); ?></section>

    <section class="tbt-section tbt-section--soft tbt-founder-message">
        <div class="tbt-reading-width">
            <?php tbt_render_media_image('curated_heroes_about_original', 'tbt-founder-message__image', ['loading' => 'lazy']); ?>
            <p class="tbt-eyebrow">Why ThatBioTutor began</p><h2><?php echo esc_html($tbt_field('about_founder_heading')); ?></h2>
            <h3>Q: Why did you start ThatBioTutor?</h3>
            <p>A: Like many of you, I struggled with certain subjects throughout my school journey. I wanted to improve, but didn't get enough help from my teachers. Sometimes they're too busy, and other times… well, let's just say they weren't too enthusiastic about the subject themselves.</p>
            <p>In JC, I took up Econs tuition when I realised my grades were falling. That was when I first experienced the power of a truly capable tutor. I learnt how to memorise content easier and how to answer questions better. The subject became so much easier... It even became one of my favourite!</p>
            <p>I want to give you that transformative experience as well. Many students are struggling with Bio, and you might be too. But it doesn't have to be that way. I wish to change that by giving high-quality lessons, taught in an enthusiastic and clear manner. Because you guys deserve the best.</p>
            <h3>Q: Why is it important that students who really want to do well can do so?</h3>
            <p>A: I’ve experienced what it’s like to always struggle in a subject. It made me feel inadequate, stupid and worthless. I don’t want them to feel that way.</p>
            <p>I want them to see that whatever they put their mind to, they can achieve. They can change and improve themselves, wherever their starting point may be. I want them to see that they are inherently highly capable people, increasing their self-confidence and self-worth.</p>
            <h3>Q: Why is it that students having high self-confidence important to you?</h3>
            <p>A: I came from a rocky childhood, and I’m not a psychologist, but I guess that was a big reason for my feelings of low self-worth during childhood.</p>
            <p>I don’t want my students to feel that way too: that they’re not good enough, not smart enough, or they’re inadequate. I don’t want them to lead defeated lives.</p>
            <p>What set me free of that was proving to myself that I can achieve whatever I set my mind to. Whether it was learning how to solve a Rubik’s cube in 15 seconds, building up my body and physique from the scrawny kid I was, or even building this tuition business… each event showed me that we humans are capable of change and improvement.</p>
            <p>Through transforming their Bio grades, I want my students to see that they can achieve whatever they set their mind to. I want them to leave our class having a higher self-esteem and confidence in themselves than before they entered.</p>
            <p><strong>— Mr Keefe</strong></p>
        </div>
    </section>

    <section class="tbt-section tbt-institutional-section tbt-about-beliefs"><div class="tbt-reading-width"><p class="tbt-eyebrow">The principles behind our work</p><h2><?php echo esc_html($tbt_field('about_beliefs_heading')); ?></h2><p class="tbt-lead">Strong Biology teaching can change more than a grade. It can help students recognise their own ability to learn, grow, and improve.</p><div class="tbt-beliefs-grid"><article><h3>Vision</h3><p>Provide high-quality Biology education to students who genuinely want to improve but are not getting the help they need from school.</p></article><article><h3>Mission</h3><p>Empower students to realise their inherent worth by showing them they can conquer Biology, while treating them with care and respect.</p></article><article><h3>Values</h3><p>Obsessive greatness. Speed is king. Always do right by the customer.</p></article></div><a class="tbt-button" href="<?php echo esc_url(tbt_whatsapp_url()); ?>">Contact ThatBioTutor</a></div></section>
</article>
<?php get_footer(); ?>
