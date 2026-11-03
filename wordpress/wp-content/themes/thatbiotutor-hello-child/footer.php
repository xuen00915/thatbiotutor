<?php

declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}

$tbt_footer_description = (string) tbt_shared_field('footer_description');
$tbt_address = (string) tbt_shared_field('address');
$tbt_hours = (string) tbt_shared_field('opening_hours');
$tbt_copyright = (string) tbt_shared_field('copyright');
$tbt_phone = (string) tbt_shared_field('phone');
$tbt_phone_url = tbt_safe_tel_url($tbt_phone);
$tbt_map_url = tbt_safe_external_url((string) tbt_shared_field('map_url'));
$tbt_whatsapp_url = tbt_whatsapp_url();
?>
</main>
<footer class="site-footer">
    <div class="site-footer__inner">
        <div class="site-footer__about">
            <strong class="site-footer__brand">ThatBioTutor</strong>
            <?php if ($tbt_footer_description !== '') : ?><p><?php echo nl2br(esc_html($tbt_footer_description)); ?></p><?php endif; ?>
        </div>
        <div class="site-footer__contact">
            <h2>Contact</h2>
            <?php if ($tbt_address !== '') : ?>
                <p><?php if ($tbt_map_url !== '') : ?><a href="<?php echo esc_url($tbt_map_url); ?>" target="_blank" rel="noopener noreferrer"><?php endif; ?><?php echo nl2br(esc_html($tbt_address)); ?><?php if ($tbt_map_url !== '') : ?></a><?php endif; ?></p>
            <?php endif; ?>
            <?php if ($tbt_phone_url !== '') : ?><p><a href="<?php echo esc_url($tbt_phone_url); ?>"><?php echo esc_html($tbt_phone); ?></a></p><?php endif; ?>
            <?php if ($tbt_hours !== '') : ?><p><?php echo nl2br(esc_html($tbt_hours)); ?></p><?php endif; ?>
        </div>
        <nav class="site-footer__nav" aria-label="Footer navigation">
            <a href="<?php echo esc_url(home_url('/contact/')); ?>">Contact</a>
            <a href="<?php echo esc_url(home_url('/tuitionrecs/')); ?>">Tuition Recommendations</a>
            <a href="<?php echo esc_url(home_url('/intensivefix/')); ?>">IntensiveFix</a>
        </nav>
    </div>
    <div class="site-footer__legal">&copy; <?php echo esc_html((string) gmdate('Y')); ?> <?php echo esc_html($tbt_copyright); ?></div>
</footer>
<?php if ($tbt_whatsapp_url !== '') : ?>
    <a class="whatsapp-float" data-whatsapp-float href="<?php echo esc_url($tbt_whatsapp_url); ?>" target="_blank" rel="noopener noreferrer" aria-label="Chat with ThatBioTutor on WhatsApp">
        <span aria-hidden="true">WA</span>
    </a>
<?php endif; ?>
<?php wp_footer(); ?>
</body>
</html>

