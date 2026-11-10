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
        <div class="tbt-institutional-title-bar"><h1><?php echo esc_html($tbt_field('trees_primary_heading')); ?></h1></div>
        <div class="tbt-institutional-shell tbt-causes-feature">
        <div class="tbt-causes-section__media tbt-causes-section__media--square"><?php tbt_render_media_image('curated_causes_team_trees', '', ['loading' => 'eager']); ?></div>
        <div class="tbt-causes-section__media tbt-causes-section__media--record"><?php tbt_render_media_image('curated_causes_trees_planted', '', ['loading' => 'lazy']); ?></div>
        <div class="tbt-causes-copy">
            <h2><em>Learning Biology isn't enough. We need to Protect it.</em></h2>
            <p>Up till 2025, ThatBioTutor donated a portion of its profits to <strong>global reforestation</strong> efforts through <a href="https://teamtrees.org/search?q=thatbiotutor" target="_blank" rel="noopener noreferrer">teamtrees.org</a>, <strong>which partners with Arbor Day Foundation.</strong></p>
            <p>In total, TBT has planted <strong><u>1982 trees</u></strong>, which you can see on their website <a href="https://teamtrees.org/search?q=thatbiotutor" target="_blank" rel="noopener noreferrer">here</a>.</p>
        </div>
        </div>
    </section>
    <section class="tbt-causes-section" data-section="sg-causes">
        <div class="tbt-institutional-title-bar"><h2><?php echo esc_html($tbt_field('trees_secondary_heading')); ?></h2></div>
        <div class="tbt-institutional-shell tbt-causes-feature">
        <div class="tbt-causes-section__media tbt-causes-section__media--giving"><?php tbt_render_media_image('curated_causes_giving_sg', '', ['loading' => 'lazy']); ?></div>
        <div class="tbt-causes-copy">
            <p>From 2026 onwards, ThatBioTutor will be focusing our giving efforts on causes <strong>closer to home.</strong></p>
            <p>Each year, <strong>our students will vote</strong> on the environmental cause on <a href="https://www.giving.sg/search?tags=%5B5%5D" target="_blank" rel="noopener noreferrer">Giving.sg</a> that matters most to them, and we’ll make a lump-sum donation to the chosen cause.</p>
        </div>
        </div>
    </section>
</article>
<?php get_footer(); ?>
