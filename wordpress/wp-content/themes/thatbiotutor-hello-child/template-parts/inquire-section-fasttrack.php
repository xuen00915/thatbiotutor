<?php

if (!defined('ABSPATH')) {
    exit;
}
?>
    <section class="tbt-home-section tbt-home-fasttrack" data-section="fasttrack">
        <div class="tbt-home-container">
            <p class="tbt-home-eyebrow"><?php echo esc_html($tbt_h('home_fasttrack_eyebrow')); ?></p>
            <h2><?php echo isset($tbt_fasttrack_heading) ? $tbt_fasttrack_heading : tbt_heading_html($tbt_h('home_fasttrack_heading')); // phpcs:ignore WordPress.Security.EscapeOutput ?></h2>
            <p class="tbt-home-lead"><?php echo esc_html($tbt_h('home_fasttrack_lead')); ?></p>
            <div class="tbt-home-fasttrack__list">
                <?php foreach ($tbt_comparisons as $tbt_item) : ?>
                    <article class="tbt-home-fasttrack__comparison">
                        <h3><?php echo esc_html($tbt_item['title']); ?></h3>
                        <div class="tbt-home-fasttrack__other"><strong><?php echo esc_html($tbt_h('home_fasttrack_other_label')); ?></strong><p><?php echo esc_html($tbt_item['other']); ?></p></div>
                        <div class="tbt-home-fasttrack__tbt"><strong><?php echo esc_html($tbt_h('home_fasttrack_tbt_label')); ?></strong><div class="tbt-home-fasttrack__tbt-content"><?php $tbt_media_image($tbt_item['media'], '', 'loading="lazy"'); ?><p><?php echo esc_html($tbt_item['tbt']); ?></p></div></div>
                    </article>
                <?php endforeach; ?>
            </div>
            <p class="tbt-home-fasttrack__close"><?php echo tbt_rich_inline($tbt_h('home_fasttrack_close')); ?></p>
        </div>
    </section>
