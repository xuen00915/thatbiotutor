<?php

if (!defined('ABSPATH')) {
    exit;
}
?>
    <section class="tbt-home-section tbt-home-fasttrack" data-section="fasttrack">
        <div class="tbt-home-container">
            <p class="tbt-home-eyebrow">O Level G3/Pure Bio</p>
            <h2><?php echo isset($tbt_fasttrack_heading) ? $tbt_fasttrack_heading : 'ThatBioTutor\'s FastTrack System Helps Students Score <span>A1/A2</span> Without the Endless Revision Grind'; // phpcs:ignore WordPress.Security.EscapeOutput ?></h2>
            <p class="tbt-home-lead">Why should students write their own notes, make their own flashcards, and hunt for exam keywords alone, when it’s already done for them?</p>
            <div class="tbt-home-fasttrack__list">
                <?php foreach ($tbt_comparisons as $tbt_item) : ?>
                    <article class="tbt-home-fasttrack__comparison">
                        <h3><?php echo esc_html($tbt_item['title']); ?></h3>
                        <div class="tbt-home-fasttrack__other"><strong>Other Bio Students</strong><p><?php echo esc_html($tbt_item['other']); ?></p></div>
                        <div class="tbt-home-fasttrack__tbt"><strong>TBT Students + FastTrack</strong><div class="tbt-home-fasttrack__tbt-content"><?php $tbt_media_image($tbt_item['media'], '', 'loading="lazy"'); ?><p><?php echo esc_html($tbt_item['tbt']); ?></p></div></div>
                    </article>
                <?php endforeach; ?>
            </div>
            <p class="tbt-home-fasttrack__close">This is the FastTrack System: the one missing key. Everything students would normally have to build themselves, <strong>already built for them.</strong></p>
        </div>
    </section>
