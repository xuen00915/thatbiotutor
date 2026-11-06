<?php

declare(strict_types=1);

// The Free Bio Notes email form is ready for Contact Form 7: it renders the editor's [contact-form-7] shortcode when the plugin
// is active, and otherwise a stand-in form built from the same markup so the design is identical before and after.

if (!defined('ABSPATH')) { define('ABSPATH', dirname(__DIR__) . '/.wordpress-test/'); }
if (!defined('TBT_THEME_DIR')) { define('TBT_THEME_DIR', dirname(__DIR__) . '/wordpress/wp-content/themes/thatbiotutor-hello-child'); }

$GLOBALS['tbt_form_field'] = null;
$GLOBALS['tbt_form_plugin'] = false;
$GLOBALS['tbt_form_shortcodes'] = [];
function get_field(string $name, int $postId = 0): mixed { return $name === 'notes_form_shortcode' ? $GLOBALS['tbt_form_field'] : null; }
function get_the_ID(): int { return 99; }
function home_url(string $path = '/'): string { return 'https://local.test/' . ltrim($path, '/'); }
function esc_url(string $value): string { return htmlspecialchars($value, ENT_QUOTES, 'UTF-8'); }
function esc_html(string $value): string { return htmlspecialchars($value, ENT_QUOTES, 'UTF-8'); }
function esc_attr(string $value): string { return htmlspecialchars($value, ENT_QUOTES, 'UTF-8'); }
function shortcode_exists(string $tag): bool { return $tag === 'contact-form-7' && $GLOBALS['tbt_form_plugin']; }
function do_shortcode(string $content): string { $GLOBALS['tbt_form_shortcodes'][] = $content; return '<div class="wpcf7"><form class="wpcf7-form">CF7-OUTPUT</form></div>'; }

require_once TBT_THEME_DIR . '/inc/theme-helpers.php';
$formsFile = TBT_THEME_DIR . '/inc/forms.php';
if (!is_file($formsFile)) { fwrite(STDERR, "FAIL: inc/forms.php must exist.\n"); exit(1); }
require_once $formsFile;

$count = 0;
$check = static function (bool $ok, string $message) use (&$count): void {
    $count++;
    if (!$ok) { fwrite(STDERR, "FAIL: {$message}\n"); exit(1); }
};
$render = static function (): string { ob_start(); tbt_render_notes_form(); return (string) ob_get_clean(); };

// 1. No shortcode set, no plugin: stand-in form with Contact Form 7 markup.
$html = $render();
$check(str_contains($html, 'data-tbt-form="fallback"'), 'Without a shortcode the stand-in form must render.');
foreach (['class="wpcf7-form"', 'wpcf7-form-control-wrap', 'wpcf7-email', 'wpcf7-submit', 'wpcf7-response-output'] as $needle) {
    $check(str_contains($html, $needle), "Stand-in form must use the Contact Form 7 class {$needle}.");
}
$check(preg_match('/<input[^>]+name="first-name"[^>]*>/', $html, $first) === 1 && str_contains($first[0], 'type="text"') && str_contains($first[0], 'required') && str_contains($first[0], 'placeholder="First Name *"'), 'Stand-in form needs a required First Name text input.');
$check(preg_match('/<input[^>]+name="your-email"[^>]*>/', $html, $email) === 1 && str_contains($email[0], 'type="email"') && str_contains($email[0], 'required') && str_contains($email[0], 'autocomplete="email"') && str_contains($email[0], 'placeholder="Email Address *"'), 'Stand-in form needs a required Email Address input.');
$check(preg_match('/<label[^>]*>.*<input[^>]+name="your-email"/s', $html) === 1 && preg_match('/<label[^>]*>.*<input[^>]+name="first-name"/s', $html) === 1, 'Text inputs must sit inside labels.');
$check(preg_match_all('/<input[^>]+type="radio"[^>]+name="level-of-student"/', $html) === 4, 'Level of Student must offer four radio choices.');
foreach (['Sec 4 G3/Pure Biology (In 2026)', 'Sec 3 G3/Pure Biology (In 2026)', 'Lower Sec, Potential Bio Student', 'Other: Combined Bio/N Level/IP/IB/A Level/IGCSE'] as $choice) {
    $check(str_contains($html, 'value="' . $choice . '"'), "Level choice missing: {$choice}");
}
$check(str_contains($html, '<legend') && str_contains($html, 'Level of Student *'), 'The radio group needs a legend.');
$check(preg_match('/<input[^>]+type="checkbox"[^>]+name="is-parent"/', $html) === 1 && str_contains($html, 'I am a Parent'), 'The form needs the I am a Parent checkbox.');
$check(str_contains($html, 'value="SUBMIT"'), 'The submit button must say SUBMIT.');
$check(str_contains($html, 'data-tbt-redirect="https://local.test/"'), 'The form must redirect to the homepage after sending.');
$check($GLOBALS['tbt_form_shortcodes'] === [], 'No shortcode may run without a configured form.');

// 2. Valid shortcode and plugin active: the plugin output is used.
$GLOBALS['tbt_form_field'] = '[contact-form-7 id="a1b2c3d" title="Free notes"]';
$GLOBALS['tbt_form_plugin'] = true;
$html = $render();
$check(str_contains($html, 'CF7-OUTPUT') && str_contains($html, 'data-tbt-form="cf7"'), 'An active plugin with a valid shortcode must render the plugin form.');
$check(!str_contains($html, 'data-tbt-form="fallback"'), 'The stand-in must not render beside the real form.');
$check($GLOBALS['tbt_form_shortcodes'] === ['[contact-form-7 id="a1b2c3d" title="Free notes"]'], 'The configured shortcode must run exactly as entered.');
$check(str_contains($html, 'data-tbt-redirect="https://local.test/"'), 'The real form must also redirect to the homepage after sending.');

// 3. Valid shortcode but plugin missing: stand-in.
$GLOBALS['tbt_form_plugin'] = false;
$html = $render();
$check(str_contains($html, 'data-tbt-form="fallback"') && !str_contains($html, 'CF7-OUTPUT'), 'A missing plugin must fall back to the stand-in form.');

// 4. Anything that is not a single contact-form-7 shortcode is ignored.
$GLOBALS['tbt_form_plugin'] = true;
$GLOBALS['tbt_form_shortcodes'] = [];
foreach (['[gallery ids="1,2"]', '<script>alert(1)</script>', '[contact-form-7 id="1"] [gallery]', 'contact-form-7'] as $bad) {
    $GLOBALS['tbt_form_field'] = $bad;
    $html = $render();
    $check(str_contains($html, 'data-tbt-form="fallback"'), "Invalid form shortcode must be ignored: {$bad}");
}
$check($GLOBALS['tbt_form_shortcodes'] === [], 'Invalid shortcodes must never be executed.');

// 5. The paste-in Contact Form 7 template matches the stand-in fields.
$template = tbt_cf7_notes_template();
foreach (['[text* first-name', '[email* your-email', '[radio* level-of-student', '[checkbox is-parent', '[submit "SUBMIT"]', 'Sec 4 G3/Pure Biology (In 2026)', 'I am a Parent'] as $needle) {
    $check(str_contains($template, $needle), "The Contact Form 7 template must contain {$needle}.");
}

// 6. The setup guide exists and carries the exact template and the field name editors use.
$guide = (string) @file_get_contents(dirname(__DIR__) . '/docs/CONTACT-FORM-7.md');
$check(str_contains($guide, '[radio* level-of-student') && str_contains($guide, 'Form Shortcode') && str_contains($guide, 'Mail (2)'), 'docs/CONTACT-FORM-7.md must carry the template, the Form Shortcode field and the auto-reply setup.');

fwrite(STDOUT, "PASS: {$count} notes-form assertions\n");
