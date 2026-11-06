<?php

declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Template to paste into Contact Form 7 (Contact > Add New > "Form" tab) for the Free Bio Notes email form.
 * Its field names match the stand-in form rendered while the plugin is not installed.
 */
function tbt_cf7_notes_template(): string
{
    return "<label class=\"tbt-form__label\">Your email address\n"
        . "    [email* your-email autocomplete:email placeholder \"Enter your best email address\"]\n"
        . "</label>\n"
        . "[submit \"Send me the notes\"]";
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
    echo '<p><label class="tbt-form__label">Your email address';
    echo '<span class="wpcf7-form-control-wrap" data-name="your-email"><input class="wpcf7-form-control wpcf7-email wpcf7-validates-as-required" type="email" name="your-email" autocomplete="email" placeholder="Enter your best email address" required aria-required="true"></span>';
    echo '</label></p>';
    echo '<p><input class="wpcf7-form-control wpcf7-submit" type="submit" value="Send me the notes"></p>';
    echo '<div class="wpcf7-response-output" aria-hidden="true"></div>';
    echo '</form></div>';
}
