# Client editing guide

The public layout is protected by the ThatBioTutor child theme. Client-safe wording and approved images are edited through **ACF Free**; Elementor Pro, ACF Pro, and Code Snippets are not required.

## Page fields

Open **Pages**, select a page, and use its labelled “ThatBioTutor” field panel. Page groups cover Home, Classes, Sec 3, Sec 4, Results & Testimonials, Free Bio Notes, Cell Division, About, Contact, Our Causes, Tuition Recommendations, and IntensiveFix. Home also owns the shared logo, WhatsApp number/message, phone, address, hours, footer description, and copyright.

The Free Bio Notes email form is ready for Contact Form 7: paste a form shortcode into the page's **Form Shortcode** field (steps in `docs/CONTACT-FORM-7.md`); until then a stand-in form with the same design is shown.

Home has five fixed numbered hero slots, four fixed numbered result-image slots, and three fixed numbered classroom slots. Free Bio Notes has three fixed numbered slides. Cell Division has five fixed numbered Cell Division slides. IntensiveFix has ten fixed numbered programme slots plus one review image. Keep fixed numbered media in the approved order; do not rename, skip, or reorder files to change sequence.

Upload raster images as WebP. Preserve the indicated landscape/portrait fit and the lower focal point on Home hero image 1. Do not paste Wix or other hotlink URLs into image fields.

## Repeated content

- **Testimonials**: edit student name, quote, rating, optional local portrait, optional YouTube URL, and display group. Use Draft to hide an unfinished item.
- **Tutors**: edit role, biography, portrait, and sections. Long biographies wrap safely on mobile.
- **FAQs**: edit the question, answer, and collection key used by the page.
- **Results**: edit the structured result fields and approved local artwork.

Published items render by menu order and then title/date where applicable. Change the WordPress order value deliberately; do not duplicate a record simply to move it. Draft records are never public.

IntensiveFix is separate from regular Sec 3 tuition. Do not replace the Sec 3 page or navigation destination with IntensiveFix.

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

