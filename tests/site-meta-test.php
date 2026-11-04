<?php
declare(strict_types=1);
define('ABSPATH', __DIR__ . '/.wp/');
define('TBT_THEME_DIR', dirname(__DIR__) . '/wordpress/wp-content/themes/thatbiotutor-hello-child');
function home_url(string $path = '/'): string { return 'https://portable.test/' . ltrim($path, '/'); }
function esc_url(string $value): string { return htmlspecialchars($value, ENT_QUOTES, 'UTF-8'); }
function esc_attr(string $value): string { return htmlspecialchars($value, ENT_QUOTES, 'UTF-8'); }
function is_front_page(): bool { return ($GLOBALS['meta_page'] ?? '') === 'home'; }
function is_page(string $slug = ''): bool { return ($GLOBALS['meta_page'] ?? '') === $slug; }
function add_action(string $hook, callable|string|array $callback, int $priority = 10, int $acceptedArgs = 1): void { $GLOBALS['meta_actions'][] = [$hook, $callback, $priority, $acceptedArgs]; }
require TBT_THEME_DIR . '/inc/reference-content.php';
require TBT_THEME_DIR . '/inc/site-meta.php';
$meta = tbt_page_metadata();
if (count($meta) !== count(tbt_page_registry())) { throw new RuntimeException('Every canonical page must have metadata.'); }
$titles=[];$descriptions=[];
foreach(tbt_page_registry() as $key=>$page){$item=$meta[$key]??[];foreach(['title','description','canonical','og_title','og_description','og_url'] as $field){if(trim((string)($item[$field]??''))==='')throw new RuntimeException("{$key} missing {$field}");}$titles[]=$item['title'];$descriptions[]=$item['description'];$expected=home_url($page['canonical']);if($item['canonical']!==$expected||$item['og_url']!==$expected)throw new RuntimeException("{$key} canonical is not portable");}
if(count($titles)!==count(array_unique($titles))||count($descriptions)!==count(array_unique($descriptions)))throw new RuntimeException('Titles and descriptions must be unique.');
$serialized=json_encode($meta);foreach(['thatbiotutor.com','localhost:8888','staging.'] as $host)if(str_contains((string)$serialized,$host))throw new RuntimeException('Metadata must not hard-code a host.');
$GLOBALS['meta_page']='notes';ob_start();tbt_render_meta_tags();$html=(string)ob_get_clean();
if(substr_count($html,'name="description"')!==1||substr_count($html,'rel="canonical"')!==1||substr_count($html,'property="og:')!==4)throw new RuntimeException('Meta output must render one complete set.');
fwrite(STDOUT,'PASS: '.(count($meta)*6+5)." site-meta assertions\n");
