# Contact Form 7 — Free Bio Notes email form

The Free Bio Notes page ("Simply Enter Your Email Below…") already has its form in place. Until Contact Form 7 is
installed it shows a stand-in form (first name, email, level of student, "I am a Parent" tick and a SUBMIT button) that opens the visitor's email app. Once you
connect a real Contact Form 7 form, the stand-in is replaced and the design stays the same, because the theme styles
Contact Form 7's own classes (`wpcf7-form`, `wpcf7-email`, `wpcf7-submit`, `wpcf7-not-valid-tip`, `wpcf7-response-output`).

The plugin itself is not stored in this repository; install it on each site (staging and final) from the WordPress admin.

## 1. Install the plugin

Plugins → Add New → search **Contact Form 7** → Install → Activate.

## 2. Create the form

Contact → Add New. Title: `Free notes`. On the **Form** tab paste exactly:

```
<label><span class="screen-reader-text">First name</span>
    [text* first-name autocomplete:given-name placeholder "First Name *"]
</label>

<label><span class="screen-reader-text">Email address</span>
    [email* your-email autocomplete:email placeholder "Email Address *"]
</label>

<fieldset class="tbt-form__fieldset">
    <legend>Level of Student *</legend>
    [radio* level-of-student use_label_element "Sec 4 G3/Pure Biology (In 2026)" "Sec 3 G3/Pure Biology (In 2026)" "Lower Sec, Potential Bio Student" "Other: Combined Bio/N Level/IP/IB/A Level/IGCSE"]
</fieldset>

[checkbox is-parent use_label_element "I am a Parent"]

[submit "SUBMIT"]
```

(The same text is returned by `tbt_cf7_notes_template()` in `inc/forms.php`.)

## 3. Set up the emails (Mail tab)

- **Mail** (goes to the team): To `keefe@thatbiotutor.com`, From `[_site_title] <wordpress@your-domain>`,
  Reply-To `[your-email]`, Subject `Free notes request`, Message body:
  ```
  Please send the free notes to:
  Name: [first-name]
  Email: [your-email]
  Level of student: [level-of-student]
  Parent: [is-parent]
  ```
- **Mail (2)** (automatic reply to the student): tick *Use Mail (2)*, To `[your-email]`, Subject
  `Your free O Level Biology notes`, Message body: a short thank-you with the download link to the notes.
- Messages tab: keep the defaults, or soften the wording ("Thanks! Check your inbox (and your Promotions folder).").

Mail deliverability: WordPress's default mail is often marked as spam. Before launch install an SMTP plugin (for example
WP Mail SMTP) and send from an address on your own domain; see the launch checklist's mail item.

## 4. Connect it to the page

1. Save the form and copy its shortcode from the top of the editor, for example
   `[contact-form-7 id="a1b2c3d" title="Free notes"]`.
2. Pages → **Free Bio Notes** → ThatBioTutor panel → **Form Shortcode** → paste it → Update.

Only a single `[contact-form-7 id="…" title="…"]` shortcode is accepted; anything else is ignored and the stand-in form is
shown. Leave the field empty to go back to the stand-in form.

## 5. After a successful send

The theme returns visitors to the homepage about 1.5 seconds after Contact Form 7 reports success, as the page copy says
("After submitting the form, you'll be automatically redirected to the homepage").

## 6. Spam protection

Contact Form 7 includes a honeypot-free basic check only. Add Akismet or Google reCAPTCHA / Cloudflare Turnstile under
Contact → Integration before launch.
