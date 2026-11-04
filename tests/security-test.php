<?php
declare(strict_types=1);
$theme=dirname(__DIR__).'/wordpress/wp-content/themes/thatbiotutor-hello-child';
foreach([$theme.'/inc/security.php',$theme.'/index.php',$theme.'/page.php',$theme.'/404.php'] as $file)if(!is_file($file))throw new RuntimeException("Missing {$file}");
$security=(string)file_get_contents($theme.'/inc/security.php');foreach(['comments_open','pings_open','xmlrpc_methods','wp_generator','the_generator','X-Content-Type-Options','Referrer-Policy','X-Frame-Options'] as $contract)if(!str_contains($security,$contract))throw new RuntimeException("Missing security contract {$contract}");
foreach(['Content-Security-Policy','Strict-Transport-Security'] as $unsafe)if(str_contains($security,$unsafe))throw new RuntimeException("Host-specific hardening must not be guessed: {$unsafe}");
$fallbacks=(string)file_get_contents($theme.'/404.php').(string)file_get_contents($theme.'/page.php').(string)file_get_contents($theme.'/index.php');foreach(['get_header','get_footer','Home','Classes','Contact'] as $contract)if(!str_contains($fallbacks,$contract))throw new RuntimeException("Fallback templates missing {$contract}");
fwrite(STDOUT,"PASS: 17 security/fallback assertions\n");
