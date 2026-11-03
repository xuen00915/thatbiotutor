# ThatBioTutor client editing guide

The native website keeps all 12 pages editable without repeating site-wide content. Edit source files under `site/`, preview the result, and let the builder generate the publishable `build/` folder.

## Preview and build

From the repository root, preview locally with:

```sh
python3 scripts/preview_site.py
```

Open `http://127.0.0.1:8765/` and stop the preview with `Control-C`. Build the publishable output with:

```sh
python3 scripts/build_site.py --output build
```

Never edit `build/` or `.preview-site/`; both are generated. GitHub checks rebuild the site before deployment.

## Common edits

| Edit | Source | What to do |
| --- | --- | --- |
| shared contact details | `site/site.json` | Update the WhatsApp number, address, hours, default message, or copyright once. |
| navigation | `site/site.json` | Edit the ordered `navigation` or `footerNavigation` list. |
| page copy | `site/pages/*.html` | Change visible headings, paragraphs, and button labels while keeping tags, IDs, and placeholders. |
| page metadata and routes | `site/pages.json` | Find the page `key`; update its title, description, route, body class, or WhatsApp message. |
| FAQs | the matching file in `site/pages/` | Keep each button, panel ID, and question order paired. |
| statistics | `site/pages.json`, a page fragment, or a shared partial | Update the displayed value and its label together; verify the claim before publishing. |
| carousel order | the matching page or partial | Move complete `<figure>` elements; keep supplied manifest groups in numeric order. |
| original testimonial archive | `site/partials/sections/original-testimonial-archive.html` | Edit one complete `<blockquote>` at a time; keep the student name/year attribution with its text. |
| original Notes reviews | `site/partials/sections/original-notes-review-archive.html` | Edit one complete `<blockquote>` at a time; preserve the reviewer attribution. |
| original Cell Division reviews | `site/partials/sections/original-cell-review-archive.html` | Edit one complete `<blockquote>` at a time; preserve the reviewer attribution. |
| video URLs | the relevant `<iframe>` in `site/pages/` | Replace only the embed `src`; retain a descriptive `title`. |
| image focal positions | `site/assets/images/asset-manifest.json` and `site/assets/css/pages.css` | Update `objectPosition` and the matching CSS rule, then check mobile and desktop. |

`CLIENT EDIT` comments highlight copy that deserves periodic review. Dates, fees, schedules, statistics, availability, and external links must be confirmed before publishing. The Cell Division page names **2023** intentionally and remains an archived offer.

## Homepage editing map

The homepage body follows the Inquire page for section order, spacing, colour, and presentation, while the shared header and shared footer remain the single site-wide templates. Its desktop typography uses the original ThatBioTutor scale (60px hero, 36px primary headings, 30px secondary headings, 22px lead text, 16px body text, and 14px small text), with responsive reductions on mobile. Edit `site/pages/home.html` one named section at a time; each section is deliberately independent so it can later become one Elementor Container.

| Section ID | Client-friendly edit |
| --- | --- |
| `hero` | Headline, grade-jump line, refund note, WhatsApp CTA, and the five-image carousel. |
| `pain-points` | The three short student frustration statements. |
| `student-results` | Introductory student quotes and result labels. |
| `track-record` | Counter values and labels in the shared track-record partial. |
| `video-transition` | The first YouTube embed URL and title. |
| `fasttrack` | Heading labels in `site/pages.json`; comparison-row wording in the shared FastTrack partial. |
| `social-proof` | Videos, school crests, Hall of Fame image, and three review cards. |
| `tutors` | Tutor portraits, names, and roles. |
| `quality-control` | The three teaching-quality checks. |
| `practical-sessions` | Description and three practical-session images. |
| `trial-guarantee` | Shield, exact refund promise, and WhatsApp CTA; there is intentionally no embedded form. |
| `suitability` | The “This is for” and “This isn't for” lists. |
| `founder` | Founder image and story. |
| `faq` | Nine question-and-answer pairs. |
| `classroom-life` | The three supplied classroom images. |
| `closing-cta` | Closing image, copy, and WhatsApp CTA. |

The supplied homepage asset groups in `site/assets/images/asset-manifest.json` are `homeHero` (folder 1A, five images), `trackRecord` (folder 1B, four images), and `homeClassroom` (folder 1C, three images). Keep each group in numeric order. The first hero image deliberately uses `object-position: 50% 56%` in `site/assets/css/pages.css` so the students and lower practical activity remain visible together. Preview that crop at 1440, 768, and 390 pixels whenever the image or frame changes.

Counters keep their final text in the HTML, and FAQ content remains readable without JavaScript. When changing a Counter value, keep its `data-counter-key`; when changing an FAQ, keep the paired `aria-controls`, panel ID, and `data-faq-*` attributes.

## Shared templates

- `site/partials/header.html` is the single site-wide header.
- `site/partials/footer.html` is the single site-wide footer.
- `site/partials/floating-whatsapp.html` is the shared floating WhatsApp control; retain `{{ whatsapp_url }}`.
- `site/partials/sections/` contains repeated result, FastTrack, resource, testimonial, and tutor sections.
- `site/assets/css/base.css` contains global colours, fonts, spacing, widths, radii, and shadows.
- `site/assets/css/components.css` styles reusable UI.
- `site/assets/css/pages.css` contains page-specific layout and image positioning.

Change a shared partial once. The builder applies it to every relevant page.

The three `original-*-archive.html` files are deliberately separate from their page shells because they contain the long, source-preserved review collections. They are plain editable HTML—not generated output. In Elementor, map each archive item to an individual Testimonial or Text Editor widget so a client can add, remove, or reorder entries without touching the surrounding page design.

## Editing a page safely

1. Find the page record in `site/pages.json` and note its `source`.
2. Open the matching fragment in `site/pages/`.
3. Edit visible wording between tags. Keep exactly one `<h1>`.
4. Preserve `data-layout-slot`, stable section IDs, `{{ root }}`, and `{{ whatsapp_url }}`.
5. Preserve accessible `alt text`, button labels, and iframe titles.
6. Run `python3 scripts/preview_site.py` and inspect a narrow and wide window.
7. Run the full build and tests before publishing.

## Images and WebP

Every local raster image must be **WebP**. PNG, JPEG, AVIF, and remote raster URLs are rejected by the build.

To replace an image:

1. Export a WebP with a similar aspect ratio.
2. Put it in the relevant folder under `site/assets/images/`.
3. Update the page or partial path, intrinsic `width`, `height`, and meaningful `alt text`.
4. Update `site/assets/images/asset-manifest.json` with `source`, `file`, `alt`, `width`, `height`, `fit`, and `objectPosition`.
5. Use `fit: "contain"` for graphics with text or artwork that must not crop. Use `fit: "cover"` for photographs intended to fill a frame.
6. Preview image focal positions at mobile, tablet, and desktop sizes.

## WhatsApp and links

The main WhatsApp number and default message live in `site/site.json`. A page-specific message may be set in `site/pages.json`. Page CTAs should keep `href="{{ whatsapp_url }}"` so the builder creates the encoded `wa.me` link.

Use full `https://` links for external pages and `mailto:` for email. Do not repeat the address or hours in individual pages; the shared footer is the source of truth.

## Routes

| Route | Editable fragment |
| --- | --- |
| `/` | `site/pages/home.html` |
| `/intensivefix/` | `site/pages/intensivefix.html` |
| `/classes/` | `site/pages/classes.html` |
| `/sec-3-pure-g3-biology-tuition/` | `site/pages/sec-3-pure-g3-biology-tuition.html` |
| `/sec-4-pure-g3-biology-tuition/` | `site/pages/sec-4-pure-g3-biology-tuition.html` |
| `/testimonials/` | `site/pages/testimonials.html` |
| `/notes/` | `site/pages/notes.html` |
| `/celldivision/` | `site/pages/celldivision.html` |
| `/about/` | `site/pages/about.html` |
| `/contact/` | `site/pages/contact.html` |
| `/trees/` | `site/pages/trees.html` |
| `/tuitionrecs/` | `site/pages/tuitionrecs.html` |

`/s3-intensivefix/` is a legacy alias for `/intensivefix/`. `/classes/sec-3-pure-g3-biology-tuition/` is a legacy alias for the canonical Sec 3 route.

## Edit through GitHub

1. Open the source file on GitHub and choose the pencil **Edit** button.
2. Change only the intended value or page copy.
3. Create a branch and pull request; never edit generated `build/` files.
4. Wait for the build and test checks.
5. Review the diff and merge only after the result is approved.

Deployment runs only from `main`.

## WordPress Elementor handoff

Use [the Elementor migration map](elementor-migration-map.md) when moving this native site to WordPress. It covers Elementor Site Settings, Global Colors, Global Fonts, the Media Library, Theme Builder, reusable Global Widget or saved Container patterns, and the mapping from each native section to an Elementor Container.

The map also covers `prefers-reduced-motion`, carousel keyboard support, Counter widgets, Accordion FAQs, video embeds, and responsive image fitting.

## Before publishing

```sh
python3 scripts/build_site.py --output build
python3 -m unittest tests.test_site_builder tests.test_static_site -v
python3 -m py_compile scripts/build_site.py scripts/preview_site.py
node --check site/assets/js/content.js
node --check site/assets/js/site.js
git diff --check
```

Then inspect navigation, the shared header/footer/floating control, every changed image, external links, WhatsApp messages, keyboard focus, mobile overflow, JavaScript-disabled behaviour, and reduced-motion behaviour.

## Preservation boundary

Do not edit `mirror/www.thatbiotutor.com/`; it is the untouched Wix reference archive. Do not edit `/Users/dmsyx/Documents/Projects/thatbiotutor-original`; it is the separate original snapshot.
