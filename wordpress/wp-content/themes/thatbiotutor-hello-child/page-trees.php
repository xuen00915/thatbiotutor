<?php
/* Template Name: Our Causes */
declare(strict_types=1);
if (!defined('ABSPATH')) { exit; }
get_header();
$tbt_defaults = tbt_reference_content()['pages']['trees']['fields'];
$tbt_field = static fn (string $name): string => (string) tbt_page_field($name, $tbt_defaults[$name] ?? '');
?>
<article class="tbt-institutional-page tbt-causes-page">
    <section class="tbt-causes-section" data-section="planting">
        <div class="tbt-institutional-title-bar"><h1><?php echo tbt_accent_heading($tbt_field('trees_primary_heading')); ?></h1></div>
        <div class="tbt-institutional-shell tbt-causes-feature">
        <div class="tbt-causes-section__media tbt-causes-section__media--square"><?php tbt_render_content_image('trees_team_image', '', ['loading' => 'eager']); ?></div>
        <div class="tbt-causes-section__media tbt-causes-section__media--record"><?php tbt_render_content_image('trees_planted_image', '', ['loading' => 'lazy']); ?></div>
        <div class="tbt-causes-copy">
            <?php if ($tbt_field('trees_planting_subheading') !== '') : ?><h2><em><?php echo esc_html($tbt_field('trees_planting_subheading')); ?></em></h2><?php endif; ?>
            <?php echo tbt_rich($tbt_field('trees_planting_body')); ?>
        </div>
        </div>
    </section>
    <section class="tbt-causes-section" data-section="sg-causes">
        <div class="tbt-institutional-title-bar"><h2><?php echo tbt_accent_heading($tbt_field('trees_secondary_heading')); ?></h2></div>
        <div class="tbt-institutional-shell tbt-causes-feature">
        <div class="tbt-causes-section__media tbt-causes-section__media--giving"><?php tbt_render_content_image('trees_giving_image', '', ['loading' => 'lazy']); ?></div>
        <div class="tbt-causes-copy">
            <?php echo tbt_rich($tbt_field('trees_sg_body')); ?>
        </div>
        </div>
    </section>
</article>
<?php get_footer(); ?>
