<?php
declare(strict_types=1);
define('ABSPATH', __DIR__ . '/.wp/');
define('TBT_THEME_DIR', dirname(__DIR__) . '/wordpress/wp-content/themes/thatbiotutor-hello-child');
function home_url(string $path = '/'): string { return 'https://portable.test/' . ltrim($path, '/'); }
define('TBT_THEME_URI', 'https://portable.test/wp-content/themes/thatbiotutor-hello-child');
function esc_url(string $value): string { return htmlspecialchars($value, ENT_QUOTES, 'UTF-8'); }
function esc_attr(string $value): string { return htmlspecialchars($value, ENT_QUOTES, 'UTF-8'); }
function get_queried_object_id(): int { return 123; }
function get_post_meta($id, $key, $single) { return $GLOBALS['seo_overrides'][$key] ?? ''; }
function wp_strip_all_tags($value): string { return strip_tags($value); }
function is_front_page(): bool { return ($GLOBALS['meta_page'] ?? '') === 'home'; }
function is_page(string $slug = ''): bool { return ($GLOBALS['meta_page'] ?? '') === $slug; }
function add_action(string $hook, callable|string|array $callback, int $priority = 10, int $acceptedArgs = 1): void { $GLOBALS['meta_actions'][] = [$hook, $callback, $priority, $acceptedArgs]; }
require TBT_THEME_DIR . '/inc/reference-content.php';
require TBT_THEME_DIR . '/inc/site-meta.php';
$meta = tbt_page_metadata();
if (count($meta) !== count(tbt_page_registry())) { throw new RuntimeException('Every canonical page must have metadata.'); }
$titles=[];$descriptions=[];
foreach(tbt_page_registry() as $key=>$page){$item=$meta[$key]??[];foreach(['title','description','keywords','canonical','og_title','og_description','og_url'] as $field){if(trim((string)($item[$field]??''))==='')throw new RuntimeException("{$key} missing {$field}");}$titles[]=$item['title'];$descriptions[]=$item['description'];$expected=home_url($page['canonical']);if($item['canonical']!==$expected||$item['og_url']!==$expected)throw new RuntimeException("{$key} canonical is not portable");}
foreach($meta as $key=>$item){if(mb_strlen($item['title'])>60)throw new RuntimeException("{$key} title is over 60 characters");if(mb_strlen($item['description'])<110||mb_strlen($item['description'])>155)throw new RuntimeException("{$key} description must be 110-155 characters");$kw=array_map('trim',explode(',',$item['keywords']));if(count($kw)<4||count($kw)>8||in_array('',$kw,true))throw new RuntimeException("{$key} needs 4-8 comma-separated keywords");}
if(count($titles)!==count(array_unique($titles))||count($descriptions)!==count(array_unique($descriptions)))throw new RuntimeException('Titles and descriptions must be unique.');
$serialized=json_encode($meta);foreach(['thatbiotutor.com','localhost:8888','staging.'] as $host)if(str_contains((string)$serialized,$host))throw new RuntimeException('Metadata must not hard-code a host.');
$GLOBALS['meta_page']='notes';ob_start();tbt_render_meta_tags();$html=(string)ob_get_clean();
if(substr_count($html,'name="description"')!==1||substr_count($html,'name="keywords"')!==1||substr_count($html,'rel="canonical"')!==1||substr_count($html,'property="og:')!==11||substr_count($html,'name="twitter:')!==5)throw new RuntimeException('Meta output must render one complete set (11 Open Graph, 5 Twitter tags).');
// Link previews (WhatsApp, Facebook, LinkedIn, X) need a large share image, the site name and locale.
$shareImage='https://portable.test/wp-content/themes/thatbiotutor-hello-child/assets/images/og/thatbiotutor-share.jpg';
foreach(['<meta property="og:site_name" content="ThatBioTutor">','<meta property="og:locale" content="en_SG">','<meta property="og:image" content="'.$shareImage.'">','<meta property="og:image:type" content="image/jpeg">','<meta property="og:image:width" content="1200">','<meta property="og:image:height" content="630">','<meta name="twitter:card" content="summary_large_image">','<meta name="twitter:image" content="'.$shareImage.'">','<meta name="twitter:title" content="Free O-Level Pure &amp; G3 Biology Notes | ThatBioTutor">'] as $tag)if(!str_contains($html,$tag))throw new RuntimeException("Missing share tag: {$tag}");
if(!preg_match('/<meta property="og:image:alt" content="[^"]{20,}">/',$html)||!preg_match('/<meta name="twitter:image:alt" content="[^"]{20,}">/',$html))throw new RuntimeException('The share image needs alt text.');
$size=getimagesize(TBT_THEME_DIR.'/assets/images/og/thatbiotutor-share.jpg');if(!$size||$size[0]!==1200||$size[1]!==630||filesize(TBT_THEME_DIR.'/assets/images/og/thatbiotutor-share.jpg')>300000)throw new RuntimeException('Share image must be a 1200x630 JPEG under 300 KB.');
fwrite(STDOUT,'PASS: '.(count($meta)*6+5)." site-meta assertions\n");

$parts=tbt_filter_document_title_parts(['title'=>'Notes','site'=>'ThatBioTutor','tagline'=>'Biology tuition']);
if(isset($parts['site'])||isset($parts['tagline']))throw new RuntimeException('Full SEO titles must not repeat the site name or tagline');
$GLOBALS['seo_overrides']=['tbt_notes_seo_title'=>'Custom Notes Title','tbt_notes_seo_description'=>'A custom description.','tbt_notes_seo_keywords'=>' biology notes ,  free notes '];
$custom=tbt_current_metadata();
if($custom['keywords']!=='biology notes, free notes'||$custom['title']!=='Custom Notes Title'||$custom['og_title']!==$custom['title']||$custom['description']!=='A custom description.')throw new RuntimeException('Previously saved overrides must apply to SEO and Open Graph');
$GLOBALS['seo_overrides']=['tbt_notes_seo_title'=>'  ','tbt_notes_seo_description'=>['invalid']];
if(tbt_current_metadata()['title']!==$meta['notes']['title']||tbt_current_metadata()['description']!==$meta['notes']['description'])throw new RuntimeException('Empty or invalid overrides must use defaults');
$GLOBALS['meta_page']='unregistered';
if(tbt_current_metadata()!==null||tbt_filter_document_title_parts(['title'=>'Unrelated','site'=>'Site'])!==['title'=>'Unrelated','site'=>'Site'])throw new RuntimeException('Unrelated pages retain WordPress metadata');
echo "PASS: SEO overrides, fallback and title composition\n";

foreach (['title', 'description', 'keywords'] as $field) {
    if (!tbt_protect_page_seo_metadata(false, 'tbt_notes_seo_' . $field, 'post')
        || !tbt_protect_page_seo_metadata(false, 'TBT_NOTES_SEO_' . strtoupper($field), 'post')) {
        throw new RuntimeException('The WordPress custom-fields editor must not expose saved SEO values.');
    }
}
if (tbt_protect_page_seo_metadata(false, 'notes_heading', 'post')) {
    throw new RuntimeException('Ordinary page copy must remain editable.');
}
