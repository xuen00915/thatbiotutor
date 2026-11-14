<?php
/* Template Name: About Us */
declare(strict_types=1);
if (!defined('ABSPATH')) { exit; }
get_header();
$tbt_defaults = tbt_reference_content()['pages']['about']['fields'];
$tbt_field = static fn (string $name): string => (string) tbt_page_field($name, $tbt_defaults[$name] ?? '');
$tbt_title = preg_split('/\s+/', trim($tbt_field('about_heading')), 2) ?: [''];
?>
<article class="tbt-institutional-page tbt-about-page">
    <header class="tbt-about-hero" data-section="hero">
        <?php tbt_render_content_image('about_hero_image', 'tbt-about-hero__image', ['loading' => 'eager']); ?>
        <div class="tbt-about-hero__title"><h1><span><?php echo esc_html($tbt_title[0]); ?></span><?php if (($tbt_title[1] ?? '') !== '') : ?> <strong><?php echo esc_html($tbt_title[1]); ?></strong><?php endif; ?></h1></div>
    </header>

    <section class="tbt-institutional-section tbt-about-mission" data-section="mission">
        <div class="tbt-institutional-title-bar"><h2><?php echo tbt_accent_heading($tbt_field('about_mission_heading')); ?></h2></div>
        <div class="tbt-reading-width">
            <h3><?php echo tbt_rich_inline($tbt_field('about_mission_tagline')); ?></h3>
            <?php echo tbt_rich($tbt_field('about_mission_body')); ?>
        </div>
    </section>

    <section class="tbt-institutional-section tbt-about-quality" data-section="quality">
        <div class="tbt-institutional-title-bar"><h2><?php echo tbt_accent_heading($tbt_field('about_quality_heading')); ?></h2></div>
        <div class="tbt-reading-width">
            <?php echo tbt_rich($tbt_field('about_quality_body')); ?>
            <?php tbt_render_content_image('about_quality_image', 'tbt-about-quality__image', ['loading' => 'lazy']); ?>
        </div>
    </section>

    <section class="tbt-institutional-section tbt-about-tutors" data-section="tutors" data-tutor-media="curated_about_tutor_chelsea curated_about_tutor_jamie curated_about_tutor_keefe curated_about_tutor_ng curated_about_chelsea_certificate curated_about_chelsea_credentials curated_about_keefe_certificate">
        <div class="tbt-institutional-title-bar"><h2><?php echo tbt_accent_heading($tbt_field('about_tutors_heading')); ?></h2></div>
        <?php
        // Names, qualifications, LinkedIn links and certificates are edited on each Tutor record; the bundled pictures below are only the fallbacks.
        tbt_render_tutors([
            'variant' => 'about',
            'show_roles' => false,
            'fallback_portraits' => [
                'Ms Chelsea' => 'curated_about_tutor_chelsea',
                'Ms Jamie' => 'curated_about_tutor_jamie',
                'Mr Keefe' => 'curated_about_tutor_keefe',
                'Ms Ng' => 'curated_about_tutor_ng',
            ],
            'supplemental_media' => [
                'Ms Chelsea' => ['curated_about_chelsea_certificate', 'curated_about_chelsea_credentials'],
                'Mr Keefe' => ['curated_about_keefe_certificate'],
            ],
        ]);
        ?>
    </section>

    <section class="tbt-institutional-section tbt-founder-message" data-section="founder">
        <div class="tbt-institutional-title-bar"><h2><?php echo tbt_accent_heading($tbt_field('about_founder_heading')); ?></h2></div>
        <div class="tbt-reading-width">
            <?php tbt_render_content_image('about_founder_image', 'tbt-founder-message__image', ['loading' => 'lazy']); ?>
            <div class="tbt-founder-cards">
                <?php foreach ([1, 2, 3] as $tbt_n) :
                    $tbt_question = $tbt_field('about_founder_q' . $tbt_n);
                    $tbt_answer = $tbt_field('about_founder_a' . $tbt_n);
                    if ($tbt_question === '' && trim($tbt_answer) === '') { continue; } ?>
                <article class="tbt-founder-card<?php echo $tbt_n === 3 ? ' tbt-founder-card--wide' : ''; ?>">
                    <?php if ($tbt_question !== '') : ?><p class="tbt-founder-question"><?php echo esc_html($tbt_question); ?></p><?php endif; ?>
                    <?php echo tbt_rich($tbt_answer); ?>
                    <?php if ($tbt_n === 3 && $tbt_field('about_founder_signature') !== '') : ?><p class="tbt-founder-signature"><?php echo esc_html($tbt_field('about_founder_signature')); ?></p><?php endif; ?>
                </article>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <section class="tbt-institutional-section tbt-about-beliefs" data-section="beliefs"><div class="tbt-institutional-title-bar"><h2><?php echo tbt_accent_heading($tbt_field('about_beliefs_heading')); ?></h2></div><div class="tbt-reading-width"><div class="tbt-beliefs-grid">
        <?php foreach ([1, 2, 3] as $tbt_n) :
            $tbt_belief_title = $tbt_field('about_belief_' . $tbt_n . '_title');
            $tbt_belief_text = $tbt_field('about_belief_' . $tbt_n . '_text');
            if ($tbt_belief_title === '' && $tbt_belief_text === '') { continue; } ?>
        <article><?php if ($tbt_belief_title !== '') : ?><h3><?php echo esc_html($tbt_belief_title); ?></h3><?php endif; ?><p><?php echo tbt_multiline($tbt_belief_text); ?></p></article>
        <?php endforeach; ?>
    </div><?php tbt_render_content_image('about_beliefs_image', 'tbt-about-beliefs__image', ['loading' => 'lazy']); ?></div></section>
</article>
<?php get_footer(); ?>
