<?php
declare(strict_types=1);
define('ABSPATH', __DIR__ . '/');
$GLOBALS['tbt_allowed'] = true;
$GLOBALS['tbt_nonce_valid'] = true;
$GLOBALS['tbt_seed_calls'] = 0;
$GLOBALS['tbt_hooks'] = [];
function add_action($hook, $callback): void { $GLOBALS['tbt_hooks'][$hook] = $callback; }
function current_user_can($cap): bool { return $cap === 'manage_options' && $GLOBALS['tbt_allowed']; }
function wp_verify_nonce($nonce, $action): bool { return $GLOBALS['tbt_nonce_valid'] && $nonce === 'valid' && $action === 'tbt_setup_site'; }
function wp_unslash($v) { return $v; }
function sanitize_text_field($v) { return $v; }
function tbt_setup_site(bool $refresh = false): array { if ($refresh) throw new RuntimeException('Must preserve client edits'); $GLOBALS['tbt_seed_calls']++; return ['pages'=>range(1,12),'seeded'=>1,'preserved'=>2]; }
function is_wp_error($v): bool { return $v instanceof WP_Error; }
function get_option($key, $default = false) { return $default; }
function update_option($key,$value): void { $GLOBALS['tbt_options'][$key]=$value; }
function flush_rewrite_rules($hard = true): void { if ($hard) throw new RuntimeException('Do not rewrite host files'); }
function acf_add_local_field_group(): void {}
class WP_Error { public function __construct(public string $code, public string $message) {} }
$file = dirname(__DIR__).'/wordpress/wp-content/themes/thatbiotutor-hello-child/inc/admin-setup.php';
if (!is_file($file)) throw new RuntimeException('Missing dashboard installation flow');
require $file;
function check(bool $ok, string $message): void { if (!$ok) throw new RuntimeException($message); }
check(tbt_admin_setup_submit([]) === null, 'GET/empty submissions must not seed');
$GLOBALS['tbt_allowed']=false;
check(tbt_admin_setup_submit(['tbt_setup_nonce'=>'valid']) instanceof WP_Error, 'Non-admin cannot seed');
$GLOBALS['tbt_allowed']=true;$GLOBALS['tbt_nonce_valid']=false;
check(tbt_admin_setup_submit(['tbt_setup_nonce'=>'valid']) instanceof WP_Error, 'Invalid nonce cannot seed');
check($GLOBALS['tbt_seed_calls']===0,'Rejected submissions must not mutate');
$GLOBALS['tbt_nonce_valid']=true;
check(is_array(tbt_admin_setup_submit(['tbt_setup_nonce'=>'valid'])),'Valid administrator can seed');
check($GLOBALS['tbt_seed_calls']===1,'Seed exactly once');
check(($GLOBALS['tbt_options']['permalink_structure']??'')==='/%postname%/','Fresh installation gets working named URLs');
check(isset($GLOBALS['tbt_hooks']['admin_menu']),'Dashboard page must be registered');
echo "PASS: secure dashboard setup contract\n";
