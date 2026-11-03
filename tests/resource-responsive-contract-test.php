<?php
declare(strict_types=1);
$theme = dirname(__DIR__) . '/wordpress/wp-content/themes/thatbiotutor-hello-child';
$css = (string) @file_get_contents($theme.'/assets/css/pages.css');
$testimonials = (string) @file_get_contents($theme.'/page-testimonials.php');
$notes = (string) @file_get_contents($theme.'/page-notes.php');
$contracts = [[$css,'.tbt-review-archive','large archive layout'],[$css,'overflow-wrap: anywhere','narrow review wrapping'],[$css,'@media (max-width: 390px)','390 layout'],[$testimonials,'data-archive-group','group anchors'],[$testimonials,'posts_per_page','bounded grouped queries'],[$notes,'data-carousel-status','slider status'],[$notes,'notes-reviews','Notes review collection']];
foreach($contracts as [$source,$needle,$label]) if($source==='' || !str_contains($source,$needle)){fwrite(STDERR,"FAIL: Missing {$label} contract.\n");exit(1);}
fwrite(STDOUT,'PASS: '.count($contracts)." resource responsive contracts\n");
