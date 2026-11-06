# Install ThatBioTutor in WordPress

## What to upload

Upload **thatbiotutor-hello-child.zip** under **Appearance → Themes → Add New → Upload Theme**.
This is a native WordPress child theme, not a `.wpress` migration backup, Elementor template, plugin ZIP or static HTML export.
It contains the templates, protected layout/behavior, ACF Free field definitions, reference content and local media.
The database, WordPress core, third-party plugins, users/passwords and host configuration are deliberately excluded.

## Requirements

- A WordPress installation with PHP 8.1 or newer, the PHP `mbstring` extension (used for reviewer initials), and HTTPS on the final host.
- The unmodified **Hello Elementor** parent theme installed from Appearance → Themes → Add New.
- **Advanced Custom Fields** (Free) installed and activated from Plugins → Add New.
- Enough PHP upload capacity for the ZIP (approximately 40–45 MB); use the actual ZIP size as the minimum. If the host rejects the upload, increase its upload/post limits or ask the host to upload the extracted `thatbiotutor-hello-child` folder into `wp-content/themes/` using SFTP.

Elementor and Elementor Pro are not needed to render this site. The parent theme must remain installed even when the child theme is active.

## Dashboard installation

1. Start on a staging site or a new installation. Back up an existing site before changing themes or running setup.
2. Install Hello Elementor and activate ACF Free.
3. Upload `thatbiotutor-hello-child.zip` and activate **ThatBioTutor Hello Child**.
4. Open **Appearance → ThatBioTutor Setup**. Click **Set up ThatBioTutor pages and content** once.
5. Setup creates the 12 owned pages and the reference records, selects Home as the front page and sets named permalinks if they were previously blank. Re-running setup preserves client edits. Conflicting existing page slugs are reported rather than overwritten; resolve a conflict explicitly before retrying.
6. Open **Settings → Permalinks** and save. Check **Settings → General** for the site title, timezone, HTTPS URLs and administrator email.
7. Edit content in Pages and the Testimonials, Tutors, FAQs and Results sections. ACF Free owns editable fields; layout remains protected in the child theme.
8. Complete the form, security and final-host checks below before launch.

Setup does not import accounts, install plugins, change the domain/DNS or enable search indexing. Do not import the GitHub Pages HTML into WordPress.

## AIOS security plugin

Install **All-In-One Security (AIOS) – Security and Firewall**, by **Team Updraft**, from **Plugins → Add New**. Its directory slug is `all-in-one-wp-security-and-firewall`:
https://wordpress.org/plugins/all-in-one-wp-security-and-firewall/

AIOS is the security plugin, not the similarly named AIOSEO SEO plugin. Install and activate it independently of the theme. AIOS 5.5.1 was activated for the local compatibility check; use the current supported version from WordPress.org on the target site.

After installation, open WP Security and review its dashboard with the hosting administrator. Enable administrator two-factor authentication only after enrolling the actual administrator and storing recovery methods. Test login protection and firewall settings on staging with the host's proxy/IP configuration. Retest login, ACF editing, form submission and public pages after enabling rules. Do not import local test-site security settings into production. The package does not force a login URL change, firewall rewrite, database-prefix change or IP restriction.

The local compatibility check covers activation and normal operation with plugin defaults. It does not certify the final host's firewall configuration or mail delivery.

## Free Bio Notes email form

The default form opens the visitor's email application; it is not an automatic notes-delivery service.
To receive submissions and send the notes automatically:

1. Install **Contact Form 7** from WordPress.org.
2. Create the form using `docs/CONTACT-FORM-7.md` from this repository.
3. Configure the real sender address, recipient, automatic-reply download link and authenticated email service on the host.
4. Paste the saved form shortcode into Pages → Free Bio Notes → Form Shortcode.
5. Send a real test to the owner's address and a separate student test address; check spam folders and the homepage redirect.

A working inbox, sender-domain authentication, notes download URL and consent text require the owner's final settings. No credentials or mailing-list subscriptions are embedded in this ZIP.

## Final-host checks

Review Home, Classes, Sec 3, Sec 4, Results & Testimonials, Free Bio Notes, Cell Division, About, Contact, Our Causes, Tuition Recommendations and IntensiveFix on desktop and mobile. Check the 404 page, legacy redirects, all images, video embeds, year navigation, FAQs, note downloads, WhatsApp links and footer details. IntensiveFix is separate from regular Sec 3.

Use a staging site's search-engine discouragement setting until launch. Only enable indexing after the final domain, HTTPS, mail delivery, contact details and owner approval are settled. Choose the host's backup, update and rollback process before switching traffic.

## Optional command-line installation

With WP-CLI on the target WordPress installation:

```sh
wp theme install hello-elementor
wp plugin install advanced-custom-fields --activate
wp theme install /path/to/thatbiotutor-hello-child.zip --activate
wp tbt setup-site
wp rewrite structure '/%postname%/'
wp rewrite flush
wp plugin install all-in-one-wp-security-and-firewall --activate
```

Use the dashboard guide for AIOS rules and email configuration. Avoid `--force` on an existing installation without a backup and a reviewed upgrade plan.

## Rebuild the upload package

From a verified checkout, stage any intended new theme files, run `scripts/verify-local.sh`, then:

```sh
python3 scripts/build_wordpress_package.py
```

Outputs go to `.artifacts/wordpress/`: the theme ZIP, `SHA256SUMS.txt`, and this installation guide. The builder includes only tracked child-theme files, sorted with fixed archive timestamps, so stray runtime files cannot enter the ZIP. Generated packages are not committed. Successful GitHub Actions runs also publish the `thatbiotutor-wordpress-install` download artifact for 30 days.
