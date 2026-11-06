<?php
/** Run with wp eval-file on the disposable installation, with ACF, AIOS and CF7 active. */
if (!defined('ABSPATH') || wp_get_environment_type() !== 'local') { throw new RuntimeException('Local WordPress only'); }
require_once ABSPATH . 'wp-admin/includes/plugin.php';
foreach (['advanced-custom-fields/acf.php','all-in-one-wp-security-and-firewall/wp-security.php','contact-form-7/wp-contact-form-7.php'] as $plugin) {
    if (!is_plugin_active($plugin)) { throw new RuntimeException('Required compatibility plugin inactive: ' . $plugin); }
}
$form = WPCF7_ContactForm::get_template(['title' => 'ThatBioTutor compatibility check']);
$form->set_properties(['form' => tbt_cf7_notes_template()]);
$tags = $form->scan_form_tags();
$levels = array_values(array_filter($tags, static fn($tag) => $tag->name === 'level-of-student'));
if (count($levels) !== 1 || $levels[0]->type !== 'radio' || count($levels[0]->values) !== 4) {
    throw new RuntimeException('CF7 must recognize all four student level options');
}
if (!is_plugin_active('advanced-custom-fields/acf.php') || !function_exists('acf_get_field_groups') || count(acf_get_field_groups()) < 1) {
    throw new RuntimeException('ACF editing fields must remain registered');
}
$ids = get_posts(['post_type'=>'page','post_status'=>'publish','meta_key'=>'_tbt_reference_page','meta_value'=>'1','fields'=>'ids','numberposts'=>-1]);
if (count($ids) !== 12) { throw new RuntimeException('All twelve protected pages must exist'); }
echo "PASS: AIOS, ACF editing fields, 12 pages, and valid CF7 student-level form tags; no mail sent\n";
