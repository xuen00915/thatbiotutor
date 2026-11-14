<?php
/* Template Name: Tuition Recommendations */
declare(strict_types=1);
if (!defined('ABSPATH')) { exit; }
get_header();
$tbt_defaults = tbt_reference_content()['pages']['tuitionrecs']['fields'];
$tbt_field = static fn (string $name): string => (string) tbt_page_field($name, $tbt_defaults[$name] ?? '');
?>
<article class="tbt-institutional-page tbt-recommendations-page">
    <section data-section="recommendations">
        <div class="tbt-institutional-title-bar"><h1><?php echo tbt_accent_heading($tbt_field('tuitionrecs_heading')); ?></h1></div>
        <div class="tbt-institutional-shell tbt-recommendations-copy">
            <?php echo tbt_rich($tbt_field('tuitionrecs_body')); ?>
        </div>
    </section>
</article>
<?php get_footer(); ?>
