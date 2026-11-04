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
        <svg class="whatsapp-float__icon" aria-hidden="true" viewBox="0 0 32 32" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M16 4.2a11.5 11.5 0 0 0-9.9 17.4L4.5 27.5l6-1.6A11.5 11.5 0 1 0 16 4.2Z" stroke="currentColor" stroke-width="2.2" stroke-linejoin="round"/>
            <path d="M12.1 10.4c.3-.6.6-.6 1-.6h.6c.2 0 .5.1.6.5l1 2.4c.1.3.1.6-.1.8l-.8 1c-.2.2-.2.5-.1.7.7 1.5 1.8 2.7 3.3 3.5.3.1.5.1.7-.1l1.1-1.3c.2-.3.5-.3.8-.2l2.3 1.1c.3.2.5.3.5.6 0 .3-.2 1.7-1.1 2.4-.8.7-1.9 1.1-3.1.8-1.3-.3-3.8-1.2-6.1-3.3-1.8-1.7-3.1-3.8-3.4-5.1-.3-1.3.2-2.5.8-3.2Z" fill="currentColor"/>
        </svg>
        <span class="whatsapp-float__notification" aria-hidden="true"></span>
    </a>
<?php endif; ?>
<?php wp_footer(); ?>
</body>
</html>

