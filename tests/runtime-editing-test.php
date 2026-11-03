<?php
if (!defined('ABSPATH')) { exit(1); }
$home=(int)get_option('page_on_front');update_post_meta($home,'home_heading','Runtime ACF-compatible field edit');
if(tbt_page_field('home_heading','',$home)!=='Runtime ACF-compatible field edit'){throw new RuntimeException('Page edit did not render through field helper.');}
$draft=wp_insert_post(['post_type'=>'tbt_testimonial','post_status'=>'draft','post_title'=>'Hidden Runtime Draft']);
update_post_meta((int)$draft,'tbt_student_name','Hidden Runtime Draft');
$published=tbt_get_testimonials(['posts_per_page'=>100]);foreach($published as $record){if(($record['student_name']??'')==='Hidden Runtime Draft')throw new RuntimeException('Draft testimonial leaked publicly.');}
wp_delete_post((int)$draft,true);tbt_setup_site(true);echo "PASS: runtime editing and Draft visibility\n";
