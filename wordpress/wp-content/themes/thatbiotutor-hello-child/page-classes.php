<?php
/* Template Name: Classes */
declare(strict_types=1);
if (!defined('ABSPATH')) { exit; }
get_header();
$tbt_defaults = tbt_reference_content()['pages']['classes']['fields'];
$tbt_field = static fn (string $name): string => (string) tbt_page_field($name, $tbt_defaults[$name] ?? '');
$tbt_whatsapp = tbt_whatsapp_url($tbt_field('classes_whatsapp_message'));
$tbt_heading = static function (string $heading): void {
    if ($heading === 'The AVERAGE TBT Student Makes a 2.3 Grade Jump (~C5 to A2)') {
        echo '<span class="tbt-programme-hero__line">The <em>AVERAGE</em> TBT Student</span>';
        echo '<span class="tbt-programme-hero__line">Makes a <em>2.3</em> Grade Jump</span>';
        echo '<span class="tbt-programme-hero__line tbt-programme-hero__line--green">(~C5 to A2)</span>';
        return;
    }
    $parts = preg_split('/(AVERAGE|2\.3|\(~C5 to A2\))/u', $heading, -1, PREG_SPLIT_DELIM_CAPTURE);
    foreach ($parts ?: [$heading] as $part) {
        echo in_array($part, ['AVERAGE', '2.3', '(~C5 to A2)'], true) ? '<span>' . esc_html($part) . '</span>' : esc_html($part);
    }
};
?>
<article class="tbt-programme-page tbt-classes-page">
    <header class="tbt-programme-hero tbt-programme-hero--classes" data-section="hero">
        <div class="tbt-programme-hero__background"><?php tbt_render_content_image('classes_hero_image', '', ['loading' => 'eager']); ?></div>
        <div class="tbt-programme-hero__shade" aria-hidden="true"></div>
        <div class="tbt-programme-hero__content">
            <p class="tbt-programme-hero__eyebrow"><?php echo esc_html($tbt_field('classes_eyebrow')); ?></p>
            <h1><?php $tbt_heading($tbt_field('classes_heading')); ?></h1>
            <p class="tbt-programme-hero__lead"><?php echo esc_html($tbt_field('classes_hero_lead')); ?></p>
            <div class="tbt-programme-hero__actions"><a class="tbt-programme-button" href="<?php echo esc_url(home_url('/sec-3-pure-g3-biology-tuition/')); ?>"><?php echo esc_html($tbt_field('classes_button_sec3_label')); ?>&nbsp; →</a><a class="tbt-programme-button" href="<?php echo esc_url(home_url('/sec-4-pure-g3-biology-tuition/')); ?>"><?php echo esc_html($tbt_field('classes_button_sec4_label')); ?>&nbsp; →</a></div>
        </div>
    </header>

    <section class="tbt-programme-section tbt-programme-definition" data-section="definition">
        <div class="tbt-programme-container">
            <p class="tbt-programme-kicker"><?php echo esc_html($tbt_field('classes_definition_kicker')); ?></p>
            <h2><?php echo esc_html($tbt_field('classes_intro_heading')); ?></h2>
            <div class="tbt-programme-definition__grid">
                <?php tbt_render_content_image('classes_definition_image', 'tbt-programme-artwork', ['loading' => 'lazy']); ?>
                <div class="tbt-programme-copy"><p><?php echo esc_html($tbt_field('classes_intro_copy')); ?></p><div class="tbt-topic-columns"><div><strong><?php echo esc_html($tbt_field('classes_topics_sec3_title')); ?></strong><?php echo tbt_rich($tbt_field('classes_topics_sec3_list')); ?></div><div><strong><?php echo esc_html($tbt_field('classes_topics_sec4_title')); ?></strong><?php echo tbt_rich_attrs(tbt_rich($tbt_field('classes_topics_sec4_list')), 'ol', 'start="11"'); ?></div></div></div>
            </div>
        </div>
    </section>

    <section class="tbt-programme-section tbt-programme-struggles" data-section="struggles">
        <div class="tbt-programme-container"><p class="tbt-programme-kicker"><?php echo esc_html($tbt_field('classes_struggle_kicker')); ?></p><h2><?php echo esc_html($tbt_field('classes_struggle_heading')); ?></h2>
            <div class="tbt-struggle-list">
                <?php foreach ([1, 2, 3, 4] as $tbt_n) : $tbt_title = $tbt_field('classes_struggle_' . $tbt_n . '_title'); $tbt_text = $tbt_field('classes_struggle_' . $tbt_n . '_text'); if ($tbt_title === '' && trim($tbt_text) === '') { continue; } ?>
                <article><h3><?php echo tbt_heading_html($tbt_title); ?></h3><p><?php echo tbt_rich_inline($tbt_text); ?></p></article>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <section class="tbt-programme-section tbt-programme-solutions" data-section="solutions"><div class="tbt-programme-container"><h2><?php echo esc_html($tbt_field('classes_solution_heading')); ?></h2><div class="tbt-solution-list">
        <?php foreach ([1, 2, 3, 4] as $tbt_n) : $tbt_title = $tbt_field('classes_solution_' . $tbt_n . '_title'); $tbt_text = $tbt_field('classes_solution_' . $tbt_n . '_text'); if ($tbt_title === '' && trim($tbt_text) === '') { continue; } ?>
        <article><h3><?php echo esc_html($tbt_title); ?></h3><p><?php echo tbt_rich_inline($tbt_text); ?></p></article>
        <?php endforeach; ?>
    </div></div></section>

    <section class="tbt-programme-section tbt-programme-difference" data-section="difference"><div class="tbt-programme-container"><p class="tbt-programme-kicker"><?php echo esc_html($tbt_field('classes_difference_kicker')); ?></p><h2><?php echo str_replace('unlike any other', '<span>unlike any other</span>', esc_html($tbt_field('classes_difference_heading'))); // phpcs:ignore WordPress.Security.EscapeOutput -- escaped first, only the accent span is added. ?></h2>
        <div class="tbt-difference-layout"><div class="tbt-difference-list"><?php foreach ([1, 2, 3, 4] as $tbt_n) : $tbt_title = $tbt_field('classes_diff_' . $tbt_n . '_title'); $tbt_text = $tbt_field('classes_diff_' . $tbt_n . '_text'); if ($tbt_title === '' && trim($tbt_text) === '') { continue; } ?><article><h3><?php echo esc_html($tbt_title); ?></h3><p><?php echo tbt_rich_inline($tbt_text); ?></p></article><?php endforeach; ?></div><?php tbt_render_content_image('classes_difference_image', 'tbt-programme-artwork', ['loading' => 'lazy']); ?></div>
        <div class="tbt-practical-feature"><h3><?php echo esc_html($tbt_field('classes_practical_title')); ?></h3><p><?php echo tbt_rich_inline($tbt_field('classes_practical_text')); ?></p><div class="tbt-practical-grid"><?php foreach ([1, 2, 3] as $tbt_n) { tbt_render_content_image('classes_practical_image_' . $tbt_n, 'tbt-practical-photo', ['loading' => 'lazy']); } ?></div></div>
    </div></section>

    <section class="tbt-programme-section tbt-programme-results" data-section="results"><div class="tbt-programme-container"><p class="tbt-programme-kicker"><?php echo esc_html($tbt_field('classes_results_kicker')); ?></p><h2><?php echo esc_html($tbt_field('classes_results_heading')); ?></h2><div class="tbt-programme-stats tbt-programme-stats--three tbt-programme-stats--four"><?php foreach ([1, 2, 3, 4] as $tbt_n) : if ($tbt_field('classes_stat_' . $tbt_n . '_value') === '' && $tbt_field('classes_stat_' . $tbt_n . '_label') === '') { continue; } ?><div><strong><?php echo esc_html($tbt_field('classes_stat_' . $tbt_n . '_value')); ?></strong><span><?php echo esc_html($tbt_field('classes_stat_' . $tbt_n . '_label')); ?></span></div><?php endforeach; ?></div></div></section>

    <section class="tbt-programme-section tbt-programme-levels" data-section="levels"><div class="tbt-programme-container"><p class="tbt-programme-kicker"><?php echo esc_html($tbt_field('classes_levels_kicker')); ?></p><h2><?php echo esc_html($tbt_field('classes_level_heading')); ?></h2><p><?php echo esc_html($tbt_field('classes_levels_intro')); ?></p><div class="tbt-programme-levels__grid"><?php foreach ([[1, '/sec-3-pure-g3-biology-tuition/'], [2, '/sec-4-pure-g3-biology-tuition/']] as [$tbt_n, $tbt_path]) : ?><a href="<?php echo esc_url(home_url($tbt_path)); ?>"><strong><?php echo esc_html($tbt_field('classes_level_' . $tbt_n . '_title')); ?></strong><span><?php echo esc_html($tbt_field('classes_level_' . $tbt_n . '_text')); ?></span><b><?php echo esc_html($tbt_field('classes_level_' . $tbt_n . '_link')); ?></b></a><?php endforeach; ?></div><?php if ($tbt_whatsapp !== '') : ?><p class="tbt-programme-levels__help"><?php echo esc_html($tbt_field('classes_levels_help')); ?><br><a href="<?php echo esc_url($tbt_whatsapp); ?>" target="_blank" rel="noopener noreferrer"><?php echo esc_html($tbt_field('classes_levels_help_link')); ?></a></p><a class="tbt-programme-button tbt-programme-levels__button" href="<?php echo esc_url($tbt_whatsapp); ?>" target="_blank" rel="noopener noreferrer"><?php echo esc_html($tbt_field('classes_levels_button')); ?></a><?php endif; ?></div></section>

    <section class="tbt-programme-section tbt-programme-faq" data-section="faq"><div class="tbt-programme-container"><p class="tbt-programme-kicker"><?php echo esc_html($tbt_field('classes_faq_kicker')); ?></p><h2><?php echo esc_html($tbt_field('classes_faq_heading')); ?></h2><?php tbt_render_faqs('classes', 'accordion'); ?></div></section>
</article>
<?php get_footer(); ?>
