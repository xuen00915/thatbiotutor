<?php

declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}

$tbt_copyright = (string) tbt_shared_field('copyright');
$tbt_whatsapp_url = tbt_whatsapp_url();
?>
</main>
<footer class="site-footer">
    <span class="screen-reader-text">&copy; <?php echo esc_html((string) gmdate('Y')); ?> <?php echo esc_html($tbt_copyright); ?></span>
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

