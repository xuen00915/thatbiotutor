<?php

declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}

$tbt_logo_fallback = tbt_media('curated_shared_tbt_logo');
$tbt_logo = tbt_page_image('home_shared_logo', (string) ($tbt_logo_fallback['url'] ?? ''), (int) get_option('page_on_front', 0));
?>
<!doctype html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?php wp_head(); ?>
</head>
<body <?php body_class('tbt-site'); ?>>
<?php wp_body_open(); ?>
<a class="skip-link screen-reader-text" href="#main-content">Skip to content</a>
<header class="site-header" data-site-header>
    <div class="site-header__inner">
        <a class="site-brand" href="<?php echo esc_url(home_url('/')); ?>" aria-label="ThatBioTutor home">
            <?php if ($tbt_logo['url'] !== '') : ?>
                <img class="site-brand__logo" src="<?php echo esc_url($tbt_logo['url']); ?>" alt="ThatBioTutor" width="192" height="192">
            <?php else : ?>
                <span class="site-brand__text">ThatBioTutor</span>
            <?php endif; ?>
        </a>
        <button class="site-header__menu-toggle" type="button" data-menu-toggle aria-expanded="false" aria-controls="site-navigation">
            <span class="screen-reader-text">Open navigation</span>
            <span class="site-header__menu-icon" aria-hidden="true"></span>
        </button>
        <nav class="site-nav" id="site-navigation" data-menu aria-label="Primary navigation">
            <?php tbt_render_navigation(); ?>
        </nav>
    </div>
</header>
<main id="main-content" class="site-main">

