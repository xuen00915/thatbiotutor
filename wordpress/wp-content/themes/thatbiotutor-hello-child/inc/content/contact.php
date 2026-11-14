<?php
/** Contact — everything the client can edit on this page. Layout stays in page-contact.php. */
declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}

return [
    'page' => 'contact',
    'sections' => [
        tbt_f_section('message', 'Message', [
            tbt_f_rich('contact_body', 'Text', <<<'HTML'
<p>Dear parent/student,</p>
<p>If you have a question, please read our <a href="/#faq"><strong><u>FAQs</u></strong></a> first!</p>
<p>If you’re planning to sign up for our lessons and have any other questions, just drop us a message on WhatsApp.</p>
<p>Our friendly team will get back to you as soon as possible!</p>
HTML, 'The link on "FAQs" goes to the FAQ section of the home page. The WhatsApp number is edited on the Home page under Shared site details.'),
            tbt_f_text('contact_button_label', 'Button text', 'WhatsApp Us Now'),
            tbt_f_text('contact_whatsapp_message', 'Message the button pre-fills in WhatsApp', "Hi ThatBioTutor, I'm a Student/Parent and I'd like to enquire about classes."),
        ], 'The page title is edited in the Page content tab.'),
    ],
];
