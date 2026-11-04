<?php
declare(strict_types=1);
if (!defined('ABSPATH')) { exit; }
get_header();
?>
<div class="tbt-section tbt-reading-width"><p class="tbt-eyebrow">404</p><h1>We could not find that page.</h1><p>The page may have moved. Return to <a href="<?php echo esc_url(home_url('/')); ?>">Home</a>, browse our <a href="<?php echo esc_url(home_url('/classes/')); ?>">Classes</a>, or <a href="<?php echo esc_url(home_url('/contact/')); ?>">Contact</a> the team.</p></div>
<?php get_footer(); ?>
