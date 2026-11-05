<?php

if (!defined('ABSPATH')) {
    exit;
}
?>
    <section class="tbt-home-section tbt-home-fit" data-section="fit">
        <div class="tbt-home-container tbt-home-fit__grid">
            <article class="tbt-home-fit__yes"><h2>This is for:</h2><ul><li>Students taking O Level Pure/G3 Biology in Sec 3 or Sec 4.</li><li>Students who feel very lost in the content, or just need a few concepts strengthened.</li><li>Students who know the content but blank out on OEQs and lose marks anyway.</li><li>Students who genuinely want to improve in Bio, out of their own drive, not because someone else is forcing them.</li><li>Students based in Singapore who can attend physical or online classes.</li></ul></article>
            <article class="tbt-home-fit__no"><h2>This isn't for:</h2><ul><li>Students taking Combined Science Biology, not Pure/G3.</li><li>Students based outside Singapore.</li><li>Students only joining because their parents are making them, without wanting to improve themselves.</li><li>Students who aren't committed to putting in the work required to ace the subject.</li></ul></article>
        </div>
    </section>

    <section class="tbt-home-section tbt-home-founder" data-section="founder">
        <div class="tbt-home-container tbt-home-founder__inner">
            <figure><?php $tbt_media_image('curated_home_founder_standing', '', 'loading="lazy"'); ?></figure>
            <div><h2>About The Founder</h2><p>I was a GEP student, went through Raffles Institution, and was offered a scholarship by NUS to study Life Sciences. On paper, that probably sounds like someone who never struggled in school.</p><p>In JC, my grades started slipping and I finally took Econs tuition. That's when I understood what a genuinely good tutor could do: help me memorise smarter and answer questions in a way that actually scores.</p><p>That inspired me to build ThatBioTutor, because I wanted other students to have that same experience with Biology. That feeling of trying hard and still not getting anywhere is exactly why I built the <strong>FastTrack System</strong>.</p></div>
        </div>
    </section>

    <section class="tbt-home-section tbt-home-faq" data-section="faq">
        <div class="tbt-home-container">
            <h2>FAQ</h2>
            <div class="tbt-home-faq__list">
                <?php foreach ($tbt_faqs as $tbt_index => [$tbt_question, $tbt_answer]) : $tbt_panel_id = 'tbt-home-faq-' . ($tbt_index + 1); ?>
                    <article class="tbt-home-faq__item" data-home-inquire-faq><h3><button type="button" data-faq-button aria-expanded="false" aria-controls="<?php echo esc_attr($tbt_panel_id); ?>"><span><?php echo esc_html((string) ($tbt_index + 1) . '. ' . $tbt_question); ?></span><span class="tbt-home-faq__icon" aria-hidden="true"></span></button></h3><div id="<?php echo esc_attr($tbt_panel_id); ?>" class="tbt-home-faq__answer" hidden><p><?php echo esc_html($tbt_answer); ?></p></div></article>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <section class="tbt-home-section tbt-home-classroom" data-section="classroom">
        <div class="tbt-home-container">
            <h2>Life inside a TBT classroom</h2>
            <div class="tbt-home-classroom__grid"><?php foreach (range(1, 3) as $tbt_index) : ?><figure><?php $tbt_media_image(sprintf('home_classroom_%02d', $tbt_index), '', 'loading="lazy"'); ?></figure><?php endforeach; ?></div>
        </div>
    </section>

