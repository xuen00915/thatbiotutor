<?php
declare(strict_types=1);
if (!defined('ABSPATH')) { exit; }
get_header();
?>
<main class="tbt-section tbt-reading-width"><h1>ThatBioTutor</h1><?php if (have_posts()) : while (have_posts()) : the_post(); ?><article><h2><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2><?php the_excerpt(); ?></article><?php endwhile; else : ?><p>Explore our <a href="<?php echo esc_url(home_url('/classes/')); ?>">Classes</a> or <a href="<?php echo esc_url(home_url('/contact/')); ?>">Contact</a> the team.</p><?php endif; ?></main>
<?php get_footer(); ?>
