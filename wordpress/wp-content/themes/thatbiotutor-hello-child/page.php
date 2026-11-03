<?php
declare(strict_types=1);
if (!defined('ABSPATH')) { exit; }
get_header();
?>
<main class="tbt-section tbt-reading-width"><?php while (have_posts()) : the_post(); ?><article><h1><?php the_title(); ?></h1><?php the_content(); ?></article><?php endwhile; ?><nav aria-label="Helpful pages"><a href="<?php echo esc_url(home_url('/')); ?>">Home</a> · <a href="<?php echo esc_url(home_url('/classes/')); ?>">Classes</a> · <a href="<?php echo esc_url(home_url('/contact/')); ?>">Contact</a></nav></main>
<?php get_footer(); ?>
