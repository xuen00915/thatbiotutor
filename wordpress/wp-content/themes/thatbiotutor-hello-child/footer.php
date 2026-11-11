<?php

declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}

$tbt_logo_fallback = tbt_media('curated_shared_tbt_logo');
$tbt_logo = tbt_page_image('home_shared_logo', (string) ($tbt_logo_fallback['url'] ?? ''), (int) get_option('page_on_front', 0));
$tbt_footer_description = (string) tbt_shared_field('footer_description');
$tbt_address = (string) tbt_shared_field('address');
$tbt_map_url = tbt_safe_external_url((string) tbt_shared_field('map_url'));
$tbt_opening_hours = (string) tbt_shared_field('opening_hours');
$tbt_phone = (string) tbt_shared_field('phone', '+65 8759 7182');
$tbt_phone_display = preg_replace('/^\+?65\s*/', '', trim($tbt_phone));
$tbt_copyright = (string) tbt_shared_field('copyright');
$tbt_whatsapp_url = tbt_whatsapp_url();
?>
</main>
<footer class="site-footer">
    <div class="site-footer__inner">
        <section class="site-footer__brand" aria-label="ThatBioTutor">
            <a class="site-footer__brand-link" href="<?php echo esc_url(home_url('/')); ?>">
                <?php if ($tbt_logo['url'] !== '') : ?>
                    <img class="site-footer__logo" src="<?php echo esc_url($tbt_logo['url']); ?>" alt="ThatBioTutor" width="192" height="192" loading="lazy">
                <?php endif; ?>
                <span>ThatBioTutor</span>
            </a>
            <?php if ($tbt_footer_description !== '') : ?>
                <p><?php echo nl2br(esc_html($tbt_footer_description)); ?></p>
            <?php endif; ?>
        </section>
        <section class="site-footer__column">
            <h2>VISIT</h2>
            <?php if ($tbt_address !== '') : ?>
                <address>
                    <?php if ($tbt_map_url !== '') : ?><a href="<?php echo esc_url($tbt_map_url); ?>" target="_blank" rel="noopener noreferrer"><?php endif; ?>
                    <?php echo nl2br(esc_html($tbt_address)); ?>
                    <?php if ($tbt_map_url !== '') : ?></a><?php endif; ?>
                </address>
            <?php endif; ?>
        </section>
        <section class="site-footer__column">
            <h2>WHATSAPP</h2>
            <?php if ($tbt_whatsapp_url !== '') : ?>
                <p><a href="<?php echo esc_url($tbt_whatsapp_url); ?>" target="_blank" rel="noopener noreferrer"><?php echo esc_html((string) $tbt_phone_display); ?></a></p>
            <?php endif; ?>
        </section>
        <section class="site-footer__column site-footer__hours">
            <h2>HOURS</h2>
            <?php if ($tbt_opening_hours !== '') : ?><p><?php echo nl2br(esc_html($tbt_opening_hours)); ?></p><?php endif; ?>
        </section>
    </div>
    <p class="site-footer__copyright">&copy; <?php echo esc_html((string) gmdate('Y')); ?> <?php echo esc_html($tbt_copyright); ?></p>
</footer>
<?php if ($tbt_whatsapp_url !== '') : ?>
    <a class="whatsapp-float" data-whatsapp-float href="<?php echo esc_url($tbt_whatsapp_url); ?>" target="_blank" rel="noopener noreferrer" aria-label="Chat with ThatBioTutor on WhatsApp">
        <img class="whatsapp-float__icon" src="<?php echo esc_url(TBT_THEME_URI . '/assets/icons/whatsapp-logo.png'); ?>" alt="" width="56" height="56" decoding="async">
        <span class="whatsapp-float__notification" aria-hidden="true"></span>
    </a>
<?php endif; ?>
<?php wp_footer(); ?>
</body>
</html>

