<?php

declare(strict_types=1);
if (!defined('ABSPATH')) { exit; }

/** Embed the client's live GetResponse form; subscriptions and redirects stay provider-owned. */
function tbt_render_notes_form(): void
{
    $url = 'https://12-dec-2025-updated-signup-form-02264.getresponsesite.com/';
    echo '<div class="tbt-notes-form tbt-notes-form--getresponse" data-tbt-form="getresponse">';
    echo '<iframe class="tbt-notes-form__embed" src="' . esc_url($url) . '" title="Free Bio Notes signup form" width="720" height="540" scrolling="no" loading="lazy"></iframe>';
    echo '</div>';
}
