<?php
declare(strict_types=1);
define('ABSPATH', __DIR__ . '/.wp/');
define('TBT_THEME_DIR', dirname(__DIR__) . '/wordpress/wp-content/themes/thatbiotutor-hello-child');
function home_url(string $path='/'):string{return 'https://portable.test/'.ltrim($path,'/');}
function add_action(string $hook, callable|string|array $callback, int $priority=10, int $acceptedArgs=1):void{$GLOBALS['redirect_actions'][]=[$hook,$callback,$priority,$acceptedArgs];}
function is_admin():bool{return (bool)($GLOBALS['redirect_admin']??false);} function wp_doing_ajax():bool{return (bool)($GLOBALS['redirect_ajax']??false);}
require TBT_THEME_DIR.'/inc/reference-content.php';require TBT_THEME_DIR.'/inc/theme-helpers.php';require TBT_THEME_DIR.'/inc/site-meta.php';
$aliases=tbt_retired_route_redirects();if($aliases!==tbt_reference_content()['aliases'])throw new RuntimeException('Redirect registry must use authoritative aliases.');
$cases=['/classes/sec-3-pure-g3-biology-tuition/'=>'https://portable.test/sec-3-pure-g3-biology-tuition/','/classes/sec-3-pure-g3-biology-tuition/?utm_source=test'=>'https://portable.test/sec-3-pure-g3-biology-tuition/','/s3-intensivefix/'=>'https://portable.test/intensivefix/','//evil.test/s3-intensivefix/'=>''];foreach($cases as $request=>$expected){$actual=tbt_legacy_redirect_target($request);if($actual!==$expected)throw new RuntimeException("Unexpected redirect for {$request}: {$actual}");}
foreach([[true,false],[false,true]] as [$admin,$ajax]){$GLOBALS['redirect_admin']=$admin;$GLOBALS['redirect_ajax']=$ajax;if(!tbt_should_bypass_frontend_redirects())throw new RuntimeException('Admin/AJAX requests must bypass redirects.');}$GLOBALS['redirect_admin']=false;$GLOBALS['redirect_ajax']=false;
if(!in_array('tbt_redirect_legacy_routes',array_column($GLOBALS['redirect_actions']??[],1),true))throw new RuntimeException('Redirect hook missing.');
fwrite(STDOUT,"PASS: 9 redirect assertions\n");
