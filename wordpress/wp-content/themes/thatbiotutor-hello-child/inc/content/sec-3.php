<?php
/** sec3 programme page — everything the client can edit. Both programme pages are declared in inc/programme-pages.php. */
declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}

return ['page' => 'sec-3-pure-g3-biology-tuition', 'sections' => tbt_programme_sections('sec3')];
