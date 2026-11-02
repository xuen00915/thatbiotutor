# ThatBioTutor client editing and Elementor guide

All 12 public pages are editable as plain text files. Shared site-wide items are defined once, so a phone number, menu item, header, footer, or floating WhatsApp change does not need to be repeated on every page.

## Preview before editing

Open Terminal in the project folder and run:

```sh
python3 scripts/preview_site.py
```

Visit `http://127.0.0.1:8765/`. The command first rebuilds `.preview-site/`, serves it only on your computer, and stops with `Control-C`. To use another port, run `python3 scripts/preview_site.py --port 8000`.

Never edit `build/` or `.preview-site/`. They are disposable generated output. Make changes under `site/`, preview, and let the generator recreate the output.

## Where to edit

| What you want to change | Source file | Safe approach |
| --- | --- | --- |
| Business name, address, hours, WhatsApp number, default message, copyright | `site/site.json` | Change only the value after the colon; preserve quotes and commas. |
| Main and footer menu items | `site/site.json` | Edit the ordered `navigation` or `footerNavigation` list once. |
| Page title, search description, route, page class, WhatsApp message | `site/pages.json` | Find the page by its `key`; do not duplicate a route. |
| Visible page headings, paragraphs, links, and buttons | `site/pages/` | Open the matching `.html` fragment and edit visible text between tags. Keep section IDs and template placeholders intact. |
| Site-wide header | `site/partials/header.html` | Edit once; the generator adds it to every page. |
| Site-wide footer | `site/partials/footer.html` | Edit once; the generator adds it to every page. |
| Floating WhatsApp button | `site/partials/floating-whatsapp.html` | Edit once; keep `{{ whatsapp_url }}` as the link. |
| Repeated content sections | `site/partials/sections/` | Edit the shared section instead of every page that includes it. |
| Colours, type, spacing, radius, shadows, widths | `site/assets/css/base.css` | Prefer the named values under `:root`. |
| Page-specific layout | `site/assets/css/pages.css` | Change only the relevant class and check mobile plus desktop. |
| Image provenance, alt text, width, and height | `site/assets/images/asset-manifest.json` | Keep the manifest entry aligned with the file and its HTML attributes. |

`CLIENT EDIT` comments mark content that needs occasional human review. The Cell Division offer still names **2023** and is intentionally presented as an archived item, not a current enrolment offer. Review all dates, prices, schedules, statistics, external links, and availability before publishing.

## Editing a page safely

1. Find the page in `site/pages.json` and note its `source` file.
2. Open that file under `site/pages/`.
3. Edit visible wording only. Keep tags such as `<section>`, `<h1>`, `<a>`, and their closing tags.
4. Keep exactly one `<h1>` and preserve stable section IDs such as `id="hero"` and `id="final-cta"`.
5. Preserve placeholders including `{{ root }}` and `{{ whatsapp_url }}`. The generator replaces them correctly for root and nested routes.
6. Run `python3 scripts/preview_site.py` and inspect the changed page on a narrow and wide window.

For a new page, copy the structure of the most similar fragment, add one complete record to `site/pages.json`, and add navigation only if the page belongs in a site-wide menu. Routes must be relative, unique, and normally end in `/`.

## Replacing an image

1. Export the replacement in **WebP** format. PNG, JPEG, AVIF, and remote image URLs are rejected by the build.
2. Give it a short descriptive filename and place it in the relevant folder under `site/assets/images/`.
3. Prefer a similar aspect ratio so faces, diagrams, and words do not crop unexpectedly.
4. Update the image path, `width`, `height`, and useful `alt text` in the page or partial that displays it.
5. Add or update its entry in `site/assets/images/asset-manifest.json`, including the original source note.
6. Preview at mobile and desktop sizes. For graphics containing words, verify every word remains readable.

## Updating WhatsApp and other links

The main number and default message are in `site/site.json`. Each page can override the message in `site/pages.json`. Page buttons should retain `href="{{ whatsapp_url }}"`; the generator creates the encoded `wa.me` URL.

External resources should use a full `https://` address. Email links begin with `mailto:`. Open each changed link in the preview before publishing.

## Edit through the GitHub website

For a small copy change without local tools:

1. Open the source file on GitHub and choose the pencil **Edit** button.
2. Change only the intended value or visible words.
3. Choose **Create a new branch** and open a pull request; do not edit generated `build/` files.
4. Wait for the GitHub checks. They rebuild all routes, run tests, check JavaScript, and reject broken local links or non-WebP images.
5. Review the file diff. Merge only after the checks pass and the previewed result is approved.

Deployment runs only from `main`. Pull requests validate without publishing.

## Routes and source fragments

| Route | Fragment |
| --- | --- |
| `/` | `site/pages/home.html` |
| `/s3-intensivefix/` | `site/pages/s3-intensivefix.html` |
| `/about/` | `site/pages/about.html` |
| `/contact/` | `site/pages/contact.html` |
| `/trees/` | `site/pages/trees.html` |
| `/tuitionrecs/` | `site/pages/tuitionrecs.html` |
| `/classes/` | `site/pages/classes.html` |
| `/sec-3-pure-g3-biology-tuition/` | `site/pages/sec-3-pure-g3-biology-tuition.html` |
| `/sec-4-pure-g3-biology-tuition/` | `site/pages/sec-4-pure-g3-biology-tuition.html` |
| `/notes/` | `site/pages/notes.html` |
| `/celldivision/` | `site/pages/celldivision.html` |
| `/testimonials/` | `site/pages/testimonials.html` |

## Before publishing

Run:

```sh
python3 scripts/build_site.py --output build
python3 -m unittest tests.test_site_builder tests.test_static_site -v
python3 -m py_compile scripts/build_site.py scripts/preview_site.py
node --check site/assets/js/content.js
node --check site/assets/js/site.js
git diff --check
```

Then check navigation, active menu state, shared header/footer/floating control, every changed image, WhatsApp message, external resource, keyboard focus, mobile overflow, and dated wording. Also check content with JavaScript disabled and reduced motion enabled.

## WordPress Elementor migration map

The source is deliberately divided into the same layers you should use in Elementor. Rebuild semantically rather than pasting each whole HTML file into an HTML widget.

### Global styles and shared templates

- Recreate the `--color-*` and `--font-*` values from `site/assets/css/base.css` in **Elementor Site Settings** using **Global Colors** and **Global Fonts**.
- Recreate spacing, maximum widths, radius, and shadows as consistent Container and widget styles.
- Turn `site/partials/header.html` into an Elementor **Theme Builder** Header template.
- Turn `site/partials/footer.html` into a Theme Builder Footer template.
- Turn `site/partials/floating-whatsapp.html` and useful files under `site/partials/sections/` into a **Global Widget** or saved global Container where the installed Elementor version supports it.
- Upload every WebP from `site/assets/images/` to the WordPress **Media Library** and preserve informative alt text. Do not hotlink the static site.

### Page fragments and sections

Each `<section id="…">` in `site/pages/` maps to one top-level **Elementor Container**. Nested `.container`, `.hero__grid`, `.card-grid`, and `.feature-row` elements map to nested Containers. Use Heading, Text Editor, Image, Button, Icon List, Counter, Gallery, Video, Accordion, and Image Carousel widgets as appropriate.

Important reusable mappings include:

| Stable section | Elementor structure |
| --- | --- |
| `#hero` | Responsive Container with one H1, supporting text, CTA Button, and optional Image/Carousel. |
| `#results` | Heading, Counter widgets, proof images, or result cards. |
| `#fasttrack` | Repeating nested Containers using Image, Heading, and Text Editor widgets. |
| `#trial-guarantee` | CTA Container and WhatsApp Button; no form. |
| `#suitability` | Two-column Container with Icon Lists. |
| `#founder` and `#tutors` | Image-and-text cards in responsive Containers. |
| `#faq` | Accessible Accordion with the existing question order. |
| `#classroom` | Gallery or Image Carousel using the supplied order. |
| `#analysis-video` | Video widget with a descriptive title beside its text. |
| `#program-carousel` | Image Carousel using contain sizing so words are not cropped. |
| `#reviews` and `#student-stories` | Testimonial cards plus review artwork where present. |
| `#final-cta` | Full-width CTA Container and WhatsApp Button. |

For the remaining institutional, class, notes, Cell Division, and testimonial sections, keep each existing section ID as one named Container. This makes future comparison and maintenance straightforward.

### Elementor accessibility and behaviour

- Keep one H1 per page and a logical heading order.
- Use Elementor carousel controls with keyboard support and touch swipe.
- Ensure Accordion controls report their expanded state.
- Keep controls at least 44 by 44 CSS pixels with visible keyboard focus.
- Use Counter widgets only when the final value is also understandable without animation.
- Avoid essential entrance animation and ensure optional movement respects `prefers-reduced-motion`.
- Test desktop, tablet, and a 320-pixel-wide screen; ensure images containing text use contain sizing where needed.

## Preservation boundary

Do not edit `mirror/www.thatbiotutor.com/`; it is the untouched Wix reference archive. Do not edit `/Users/dmsyx/Documents/Projects/thatbiotutor-original`; it is the separate original snapshot. The editable website source is `site/`, and generated output belongs only in ignored `build/` or `.preview-site/` directories.
