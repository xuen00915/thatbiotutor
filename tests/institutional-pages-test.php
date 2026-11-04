<?php
declare(strict_types=1);
require_once __DIR__.'/page-render-bootstrap.php';
final class InstitutionalPagesTest{
 private int $n=0;
 public function run():void{
  $files=['page-about.php','page-contact.php','page-trees.php','page-tuitionrecs.php'];$out=[];
  foreach($files as $f){$this->ok(is_file(TBT_THEME_DIR.'/'.$f),"{$f} must exist");$out[$f]=html_entity_decode(tbt_test_render_template($f));$this->same(1,substr_count($out[$f],'<h1'),"{$f} must have one H1");}
  $about=$out['page-about.php'];foreach(['About ThatBioTutor','Our Mission','SUCCESS TO US = TRULY HELPING STUDENTS.','Quality Teaching Only','Our Tutors','A Message From The Founder','Why did you start ThatBioTutor?','What We Believe In','Vision','Mission','Values'] as $m)$this->ok(str_contains($about,$m),"About must contain {$m}");
  preg_match_all('/data-section="([^"]+)"/',$about,$aboutSections);$this->same(['hero','mission','quality','tutors','founder','beliefs'],$aboutSections[1],'About sections must follow the public order');
  foreach(['curated_heroes_about_original','curated_about_lesson_feedback','curated_about_chelsea_certificate','curated_about_chelsea_credentials','curated_about_keefe_certificate','curated_about_founder_message','curated_about_beliefs'] as $key)$this->ok(str_contains($about,$key),"About must use {$key}");
  $this->same(1,substr_count($about,'data-tutor-renderer'),'About must use the shared four-tutor renderer');$this->same(4,count(tbt_reference_content()['records']['tutors']),'About source must seed four tutors');
  $contact=$out['page-contact.php'];foreach(['Contact.','Dear parent/student','read our FAQs first','drop us a message on WhatsApp','WhatsApp Us Now'] as $m)$this->ok(str_contains($contact,$m),"Contact must contain {$m}");$this->same(1,substr_count($contact,'data-section="content"'),'Contact must preserve its single public content composition');$this->ok(!str_contains($contact,'<form'),'Contact must not invent a native form');$this->ok(str_contains($contact,'https://wa.me/6587597182'),'Contact must use shared WhatsApp');
  $trees=$out['page-trees.php'];foreach(['1. Planting Trees!','Learning Biology isn\'t enough. We need to Protect it.','1982 trees','2. SG Environmental Causes','Giving.sg'] as $m)$this->ok(str_contains($trees,$m),"Causes must contain {$m}");preg_match_all('/data-section="([^"]+)"/',$trees,$treeSections);$this->same(['planting','sg-causes'],$treeSections[1],'Causes sections must stay in public order');$this->ok(str_contains($trees,'https://teamtrees.org/search?q=thatbiotutor')&&str_contains($trees,'https://www.giving.sg/search'),'Causes must preserve source destinations');
  $recs=$out['page-tuitionrecs.php'];foreach(['Tuition Recommendations','By popular request','trial lesson','referred by ThatBioTutor'] as $m)$this->ok(str_contains($recs,$m),"Recommendations must contain {$m}");$this->ok(str_contains($recs,'>Tuition Recommendations</h1>'),'Recommendations must reproduce the exact public heading without the retired O Level prefix.');$this->ok(!str_contains($recs,'>O Level Tuition Recommendations</h1>'),'Recommendations must not render the retired heading.');$this->same(1,substr_count($recs,'data-section="recommendations"'),'Recommendations must use the single public composition without invented downstream panels');$this->ok(str_contains($recs,'docs.google.com/document/d/1Wwn8kzMaT59326gpnidbuGwRWMB6etmh7-Ff6eJiKLw'),'Recommendations must preserve independent source document');
  foreach([$trees,$recs] as $html){$this->ok(str_contains($html,'target="_blank"')&&str_contains($html,'rel="noopener noreferrer"'),'External links must open safely');}
  fwrite(STDOUT,"PASS: {$this->n} institutional-page assertions\n");
 }
 private function ok(bool $c,string $m):void{$this->n++;if(!$c)throw new RuntimeException($m);}private function same(mixed $e,mixed $a,string $m):void{$this->n++;if($e!==$a)throw new RuntimeException($m);}
}
try{(new InstitutionalPagesTest())->run();}catch(Throwable $e){fwrite(STDERR,'FAIL: '.$e->getMessage()."\n");exit(1);}

