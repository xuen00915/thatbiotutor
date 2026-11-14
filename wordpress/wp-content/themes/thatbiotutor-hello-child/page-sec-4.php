<?php
/* Template Name: Sec 4 Pure G3 Biology Tuition */
declare(strict_types=1);
if (!defined('ABSPATH')) { exit; }
get_header();
// Layout and copy for both programme pages: inc/programme-pages.php (fields declared in inc/content/).
tbt_render_programme_page('sec4');
get_footer();
