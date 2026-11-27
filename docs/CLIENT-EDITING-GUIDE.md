# Client editing guide

The public layout is protected by the ThatBioTutor child theme. Client-safe wording and approved images are edited through **ACF Free**; Elementor Pro, ACF Pro, and Code Snippets are not required.

## Administrators

In WordPress, open **Pages**. The default list shows Home, Classes, Sec 3, Sec 4, Results & Testimonials, Free Bio Notes, About Us, Contact, and the Privacy Policy draft.

- Select **Additional Pages** above the list to edit pages such as Cell Division Crash Course, Our Causes, Tuition Recommendations, and IntensiveFix. Select **All Pages** to see the complete list.
- Use the WordPress sidebar to edit Testimonials, Class Years, Tutors, and FAQs.
- Open **Home** for the shared logo, contact information, and footer.
- For a visual preview, choose **Visual editor** from the page’s row actions or the toolbar on the live page. Select **Edit** on a section in the preview to open its fields. Press **Update** to save and refresh the preview. Use the WordPress sidebar for repeated records such as Tutors and FAQs.
- In each page editor, use the **Section** dropdown. Use **Preview Changes** to review it and **Update** to save. For Privacy Policy, use **Save Draft** to keep it unpublished.

## ThatBioTutor Managers

- **Pages → Free Bio Notes → Email form** opens the GetResponse signup link, the photo above the form, and the text below it. Manage form fields, mailing lists, automation, and the success redirect in GetResponse.
- **Tutors** opens tutor profiles and their display order.

## Page fields

Open **Pages**, select a page, and use its labelled “ThatBioTutor” field panel. Page groups cover Home, Classes, Sec 3, Sec 4, Results & Testimonials, Free Bio Notes, Cell Division, About, Contact, Our Causes, Tuition Recommendations, and IntensiveFix. Home also owns the shared logo, WhatsApp number/message, phone, address, hours, footer description, and copyright.

The Free Bio Notes form is embedded from GetResponse. Edit its fields, mailing list, automation and success redirect in GetResponse. The legacy **Form Shortcode** field is no longer used. See `docs/GETRESPONSE-FORM.md`.

Home has five fixed numbered hero slots, four fixed numbered result-image slots, and three fixed numbered classroom slots. Free Bio Notes has three fixed numbered slides. Cell Division has five fixed numbered Cell Division slides. IntensiveFix has ten fixed numbered programme slots plus one review image. Keep fixed numbered media in the approved order; do not rename, skip, or reorder files to change sequence.

Upload raster images as WebP. Preserve the indicated landscape/portrait fit and the lower focal point on Home hero image 1. Do not paste Wix or other hotlink URLs into image fields.

## How editing works

Open **Pages**, then the page you want. The page opens on a clear screen with a short guide and a **ThatBioTutor** panel with a **Section** dropdown (for example *Our Mission*, *Message From The Founder*). Change the words, pictures and links, then press **Update**.

- **Formatted text boxes** have a short toolbar: **Bold**, *Italic*, <u>Underline</u>, **Highlight** (the green marker used on the About page), link and bullet list. Press Enter for a new paragraph. Anything that could break the layout (headings, colours, scripts, embedded frames) is removed automatically.
- **Links**: select the words, click the link button and type the address. For a page on this site just type its path, for example `/classes/` or `/#faq`. Links to other sites should be set to open in a new tab; they are always opened safely.
- **Pictures** show the current image, including a theme default when one is available. Choose **Replace image** to select a different one. Removing a replacement restores the default. Use WebP or JPG.
- **Plain text boxes** keep their line breaks, and paragraphs wrap across lines. Leave a question or card empty to hide it.
- The page layout, colours, menu and footer are managed by the theme, so they cannot be broken from the edit screen.

### Adding a new class year (Results & Testimonials)

1. **Class Years → Add Class Year**. Enter the year (for example `2026`) and the green panel heading ("Class of 2026").
2. Upload the result poster pictures (tall) and results table pictures (wide), if there are any. Optional: a one-line result headline and the line under "Student Stories" (leave it empty for no line). Empty picture fields show the built-in picture the site is using; add an image only to replace it.
3. Add the students' stories under **Testimonials → Add Testimonial** and set **Display group / year** to the same year.
4. **Publish.** The page and the year menu update automatically, newest year first. To retire a year, move its record to Draft.

### Home page and shared sections

The Home page sections follow the page from top to bottom (*Top of the page*, *Student worries and stories*, *Track record*, *FastTrack system* and so on). The title uses two marker buttons: **Highlight** for green words and **Red** for the red word. Sections that also appear on the IntensiveFix page (FastTrack, proof, tutors, quality control, practical sessions, who it is for, founder, FAQ, classroom photos) are edited here once and update both pages. The FAQ is edited under **FAQs** (collection `home`).

### Classes, Sec 3 and Sec 4

Each section of these pages is listed in the dropdown. Cards come in numbered sets (for example *Problem 1 … Problem 4*): leave a card's title and text empty to hide it. Sec 3 and Sec 4 share one layout, so they always look alike; only their words, dates, topics and pictures differ.

### Tutors (About Us)

Under **Tutors**: display name, qualifications (one per line), LinkedIn link, portrait and up to three certificates. Use the Order number to change who appears first, and Draft to hide a tutor.

## Repeated content

- **Testimonials**: edit student name, quote, rating, optional local portrait, optional YouTube URL, and display group. Use Draft to hide an unfinished item.
- **Tutors**: edit role, biography, portrait, and sections. Long biographies wrap safely on mobile.
- **FAQs**: edit the question, answer, and collection key used by the page.
- **Class Years**: edit the public class-year posters and results tables.

Published items render by menu order and then title/date where applicable. Change the WordPress order value deliberately; do not duplicate a record simply to move it. Draft records are never public.

IntensiveFix is separate from regular Sec 3 tuition. Do not replace the Sec 3 page or navigation destination with IntensiveFix.

## Editing access

WordPress Administrators have the full editing view automatically, including on installs with older editing-scope options. The **ThatBioTutor Manager** role uses the Notes form and Tutors view and lands on **Pages** after signing in. Create a Manager under **Users → Add New** or with `wp user create <login> <email> --role=tbt_manager`. Managers can upload images and manage other Manager accounts.

## Protected visual structure

The original-site layouts are separated into focused stylesheets: `pages-home.css`, `pages-programmes.css`, `pages-testimonials.css`, `pages-resources.css`, `pages-institutional.css`, and `pages-intensivefix.css`. The small `pages.css` file contains shared primitives only. Content editors should not paste CSS or Code Snippets into WordPress; ask a developer to change protected presentation.

The supplied font binaries are not redistributed because their licence was not confirmed. The theme uses its documented font-licensing fallback (Arial, Helvetica, then the system sans-serif) while preserving the measured public-site scale and spacing.

Developers can capture the reference/local visual matrix with `npm run visual:capture -- --reference-base=https://www.thatbiotutor.com --local-base=http://localhost:8888 --output=.artifacts/visual/review --freeze`, then build the comparison with `npm run visual:review -- .artifacts/visual/review`.

## Safe publishing checklist

1. Preview desktop and mobile before publishing.
2. Recheck dates, grade claims, school counts, donation totals, prices, and external links with the business owner.
3. Confirm WhatsApp opens the intended number and message.
4. Keep external recommendation and environmental links current.
5. Ask a developer for layout, navigation, responsive, metadata, redirect, or interaction changes; those are theme-owned.
