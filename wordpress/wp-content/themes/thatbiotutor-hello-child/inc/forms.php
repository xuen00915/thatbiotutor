<?php

declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}

/** The four "Level of Student" choices, in the order shown on the form. */
function tbt_notes_form_levels(): array
{
    return [
        'Sec 4 G3/Pure Biology (In 2026)',
        'Sec 3 G3/Pure Biology (In 2026)',
        'Lower Sec, Potential Bio Student',
        'Other: Combined Bio/N Level/IP/IB/A Level/IGCSE',
    ];
}

/**
 * Template to paste into Contact Form 7 (Contact > Add New > "Form" tab) for the Free Bio Notes form.
 * Its field names match the stand-in form rendered while the plugin is not installed.
 */
function tbt_cf7_notes_template(): string
{
    $levels = implode(' ', array_map(static fn (string $level): string => '"' . $level . '"', tbt_notes_form_levels()));

    return "<label><span class=\"screen-reader-text\">First name</span>\n"
        . "    [text* first-name autocomplete:given-name placeholder \"First Name *\"]\n"
        . "</label>\n\n"
        . "<label><span class=\"screen-reader-text\">Email address</span>\n"
        . "    [email* your-email autocomplete:email placeholder \"Email Address *\"]\n"
        . "</label>\n\n"
        . "<fieldset class=\"tbt-form__fieldset\">\n"
        . "    <legend>Level of Student *</legend>\n"
        . "    [radio* level-of-student use_label_element " . $levels . "]\n"
        . "</fieldset>\n\n"
        . "[checkbox is-parent use_label_element \"I am a Parent\"]\n\n"
        . "[submit \"SUBMIT\"]";
}

/** Return the configured Contact Form 7 shortcode only when it is exactly one [contact-form-7 ...] tag. */
function tbt_notes_form_shortcode(): string
{
    $value = trim((string) tbt_page_field('notes_form_shortcode', ''));
    if (preg_match('/^\[contact-form-7(?:\s+(?:id|title|html_id|html_class)="[^"\[\]]*")+\s*\]$/', $value) !== 1) {
        return '';
    }

    return $value;
}

/**
 * Render the Free Bio Notes email form: the editor's Contact Form 7 shortcode when the plugin is active,
 * otherwise a stand-in form that uses the same markup and classes, so the design does not change on install.
 */
function tbt_render_notes_form(): void
{
    $redirect = esc_attr(home_url('/'));
    $shortcode = tbt_notes_form_shortcode();

    if ($shortcode !== '' && function_exists('shortcode_exists') && shortcode_exists('contact-form-7')) {
        echo '<div class="tbt-notes-form" data-tbt-form="cf7" data-tbt-redirect="' . $redirect . '">';
        echo do_shortcode($shortcode);
        echo '</div>';
        return;
    }

    $mailto = 'mailto:keefe@thatbiotutor.com?subject=' . rawurlencode('Free O Level Biology Notes request');
    echo '<div class="tbt-notes-form wpcf7" data-tbt-form="fallback" data-tbt-redirect="' . $redirect . '">';
    echo '<form class="wpcf7-form" action="' . esc_attr($mailto) . '" method="post" enctype="text/plain">';
    echo '<p><label><span class="screen-reader-text">First name</span><span class="wpcf7-form-control-wrap" data-name="first-name"><input class="wpcf7-form-control wpcf7-text wpcf7-validates-as-required" type="text" name="first-name" autocomplete="given-name" placeholder="First Name *" required aria-required="true"></span></label></p>';
    echo '<p><label><span class="screen-reader-text">Email address</span><span class="wpcf7-form-control-wrap" data-name="your-email"><input class="wpcf7-form-control wpcf7-email wpcf7-validates-as-required" type="email" name="your-email" autocomplete="email" placeholder="Email Address *" required aria-required="true"></span></label></p>';
    echo '<fieldset class="tbt-form__fieldset"><legend>Level of Student *</legend>';
    echo '<span class="wpcf7-form-control-wrap" data-name="level-of-student"><span class="wpcf7-form-control wpcf7-radio wpcf7-validates-as-required">';
    foreach (tbt_notes_form_levels() as $index => $level) {
        $position = $index === 0 ? ' first' : ($index === count(tbt_notes_form_levels()) - 1 ? ' last' : '');
        echo '<span class="wpcf7-list-item' . $position . '"><label><input type="radio" name="level-of-student" value="' . esc_attr($level) . '"' . ($index === 0 ? ' required' : '') . '><span class="wpcf7-list-item-label">' . esc_html($level) . '</span></label></span>';
    }
    echo '</span></span></fieldset>';
    echo '<p><span class="wpcf7-form-control-wrap" data-name="is-parent"><span class="wpcf7-form-control wpcf7-checkbox"><span class="wpcf7-list-item first last"><label><input type="checkbox" name="is-parent" value="I am a Parent"><span class="wpcf7-list-item-label">I am a Parent</span></label></span></span></span></p>';
    echo '<p><input class="wpcf7-form-control wpcf7-submit" type="submit" value="SUBMIT"></p>';
    echo '<div class="wpcf7-response-output" aria-hidden="true"></div>';
    echo '</form></div>';
}
