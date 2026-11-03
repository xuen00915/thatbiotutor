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
    <header class="tbt-section tbt-utility-hero"><h1><?php echo esc_html($tbt_field('tuitionrecs_heading')); ?></h1></header>
    <section class="tbt-section tbt-institutional-section"><div class="tbt-reading-width"><p class="tbt-lead">By popular request, I have compiled some secondary school + JC tuition recommendations <a href="<?php echo esc_url($tbt_recommendations_url); ?>" target="_blank" rel="noopener noreferrer">here</a>!</p><ul class="tbt-recommendation-list"><li>Secondary school English, Mathematics, and Science tuition</li><li>O Level, IP, and Junior College subject specialists</li><li>For all these recommendations, <strong>I advise you to go for a trial lesson</strong> at one or more centres to find the best one for you.</li></ul><p><strong>Tell them that you were referred by ThatBioTutor</strong> — you might be able to get a discount or free trial.</p><a class="tbt-button" href="<?php echo esc_url($tbt_recommendations_url); ?>" target="_blank" rel="noopener noreferrer">Browse the Recommendation List</a></div></section>
    <section class="tbt-section tbt-section--soft tbt-institutional-section"><div class="tbt-reading-width"><p class="tbt-eyebrow">Please choose thoughtfully</p><h2>Independent providers</h2><p>The recommended tuition providers are independent third parties. ThatBioTutor does not control their teaching, pricing, availability, or policies. Ask current questions and judge each trial lesson for yourself.</p></div></section>
</article>
<?php get_footer(); ?>
