<?php
/* Template Name: Tuition Recommendations */
declare(strict_types=1);
if (!defined('ABSPATH')) { exit; }
get_header();
$tbt_defaults = tbt_reference_content()['pages']['tuitionrecs']['fields'];
$tbt_field = static fn (string $name): string => (string) tbt_page_field($name, $tbt_defaults[$name] ?? '');
$tbt_recommendations_url = 'https://docs.google.com/document/d/1Wwn8kzMaT59326gpnidbuGwRWMB6etmh7-Ff6eJiKLw/edit?tab=t.0';
?>
<article class="tbt-institutional-page tbt-recommendations-page">
    <section data-section="recommendations">
        <div class="tbt-institutional-title-bar"><p class="tbt-page-eyebrow">Secondary school + JC</p><h1><?php echo tbt_accent_heading($tbt_field('tuitionrecs_heading')); ?></h1></div>
        <div class="tbt-institutional-shell tbt-recommendations-copy">
            <p>By popular request, I have compiled some secondary school + JC tuition recommendations <a href="<?php echo esc_url($tbt_recommendations_url); ?>" target="_blank" rel="noopener noreferrer"><u>here</u></a>!</p>
            <p>1. For all these recommendations, <strong>I advise you to go for trial lesson</strong> for one/more centres to <u>find the best one for you.</u></p>
            <p>2. <strong>Tell them that u were also referred by ThatBioTutor</strong> - might be able to <u>get a discount/free trial.</u></p>
        </div>
    </section>
</article>
<?php get_footer(); ?>
