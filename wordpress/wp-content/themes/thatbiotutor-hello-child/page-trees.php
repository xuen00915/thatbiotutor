<?php
/* Template Name: Our Causes */
declare(strict_types=1);
if (!defined('ABSPATH')) { exit; }
get_header();
$tbt_defaults = tbt_reference_content()['pages']['trees']['fields'];
$tbt_field = static fn (string $name): string => (string) tbt_page_field($name, $tbt_defaults[$name] ?? '');
?>
<article class="tbt-institutional-page tbt-causes-page">
    <header class="tbt-section tbt-utility-hero"><h1><?php echo esc_html($tbt_field('trees_primary_heading')); ?></h1></header>
    <section class="tbt-section tbt-institutional-section tbt-causes-panel"><div class="tbt-reading-width"><?php tbt_render_media_image('curated_causes_team_trees', 'tbt-causes-panel__hero', ['loading' => 'eager']); ?><h2><em>Learning Biology isn't enough. We need to Protect it.</em></h2><p>Up till 2025, ThatBioTutor donated a portion of its profits to <strong>global reforestation</strong> efforts through <a href="https://teamtrees.org/search?q=thatbiotutor" target="_blank" rel="noopener noreferrer">teamtrees.org</a>, <strong>which partners with Arbor Day Foundation.</strong></p><p>In total, TBT has planted <strong>1982 trees.</strong> You can see the record on the <a href="https://teamtrees.org/search?q=thatbiotutor" target="_blank" rel="noopener noreferrer">Team Trees donor search</a>.</p><?php tbt_render_media_image('curated_causes_trees_planted', 'tbt-causes-panel__record', ['loading' => 'lazy']); ?></div></section>
    <section class="tbt-section tbt-section--soft tbt-institutional-section tbt-causes-panel"><div class="tbt-title-bar"><h2><?php echo esc_html($tbt_field('trees_secondary_heading')); ?></h2></div><div class="tbt-reading-width"><?php tbt_render_media_image('curated_causes_giving_sg', 'tbt-causes-panel__giving', ['loading' => 'lazy']); ?><p>From 2026 onwards, ThatBioTutor will focus its giving efforts on causes closer to home.</p><p>Each year, our students will vote on the environmental cause on <a href="https://www.giving.sg/search?tags=%5B5%5D" target="_blank" rel="noopener noreferrer">Giving.sg</a> that matters most to them, and we will make a lump-sum donation to the chosen cause.</p></div></section>
</article>
<?php get_footer(); ?>
