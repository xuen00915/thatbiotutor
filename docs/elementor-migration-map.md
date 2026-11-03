# ThatBioTutor Elementor migration map

This map translates the native website into maintainable WordPress Elementor templates. Rebuild each semantic section with Elementor widgets; do not paste a whole page into one HTML widget.

## Global foundation

| Native source | Elementor destination |
| --- | --- |
| `site/assets/css/base.css` colour and font tokens | Elementor Site Settings → Global Colors and Global Fonts |
| content and wide container widths | Site Settings layout widths and reusable Container presets |
| `site/assets/images/` WebP files | WordPress Media Library with the same alt text |
| `site/site.json` | Site identity, contact details, menus, and global link settings |
| `site/pages.json` | WordPress page titles, permalinks, SEO descriptions, and page-specific WhatsApp messages |

Keep the native desktop, tablet, and mobile spacing relationships. Text-heavy artwork must use `contain`; photographs may use `cover` with the manifest focal position.

Set Elementor Global Fonts to match the original site: **Montserrat Regular (400)** for body copy, **Montserrat Bold (700)** for headings and actions, **Montserrat Italic (400/700)** where emphasis is used, and **Futura LT Book (400)** for the main navigation. The native build vendors the exact webfont files locally under `site/assets/fonts/`.

## Theme Builder templates

| Native shared template | Elementor implementation |
| --- | --- |
| `site/partials/header.html` | Theme Builder **Header**, displayed site-wide |
| `site/partials/footer.html` | Theme Builder **Footer**, displayed site-wide |
| `site/partials/floating-whatsapp.html` | Site-wide floating WhatsApp saved Container or Global Widget |

The Header contains the logo, navigation, mobile toggle, IntensiveFix link, and rightmost Contact Us CTA. The Footer owns the address, hours, footer navigation, and copyright. Do not duplicate these inside page templates.

## Reusable component mapping

| Native pattern | Elementor equivalent |
| --- | --- |
| `.section-bar` | Full-width dark Container saved as a **section bar** preset |
| `.stats` and `.stat` | Nested Containers with **Counter** widgets and static labels |
| `.media-carousel` | **Image Carousel** with arrows, dots, keyboard access, and touch swipe |
| `.faq-list` | Accessible **Accordion** retaining the original question order |
| `.feature-row` | Repeating two-column Container with Image, Heading, and Text Editor widgets |
| `.tutor-grid` | Responsive nested Containers with Image, Heading, and Text Editor widgets |
| `.video-frame` | Video widget with the existing title and embed URL |
| `.compact-cta` | Button widget using the global green button style |
| each `<blockquote>` in an `original-*-archive.html` partial | One **Testimonial** or Text Editor widget, preserving its attribution |

Create reusable saved Containers for FastTrack rows, results, class resources, tutor profiles, and testimonial summaries. Use a Global Widget only when every instance must update together. Import the long review archives as separate repeated widgets; do not combine hundreds of reviews into one HTML widget.

## Page sections

Each `<section data-layout-slot="…">` becomes one named top-level **Elementor Container**. Preserve one H1 per page and the exact section order.

| Page | Ordered Elementor Containers |
| --- | --- |
| Home | hero, missing-key, fasttrack, track-record, hall-of-fame, student-voices, schools, advantage, student-benefits, experience, causes, tutors, faq, closing-cta |
| Classes | hero, pure-biology, struggles, solutions, class-difference, results, level-choice, faq |
| Sec 3 and Sec 4 tuition | hero, learning-modes, curriculum, results, track-record, student-voices, lesson, resources, team, faq |
| IntensiveFix | hero, programme-media, student-proof, track-record, analysis-video, fasttrack, suitability, closing-cta |
| About | hero, mission, quality, tutors, founder-message, beliefs |
| Contact | hero, faq-pointer, contact |
| Causes | hero, tree-planting, local-causes |
| Tuition Recommendations | hero, recommendations, disclosure |
| Testimonials | hero, results, reviews, student-stories, class-2022, class-2021 |
| Notes | hero, student-feedback, why-free, catch, download |
| Cell Division archive | hero, dated-notice, important, access, student-feedback |

## Media and behaviour

- Upload the WebP files; do not hotlink the static website.
- Preserve carousel order from `asset-manifest.json`.
- Set the five homepage hero images as one Image Carousel without changing hero geometry.
- Set all ten IntensiveFix programme graphics to `contain` so words are never cropped.
- Keep carousel arrows and dots keyboard accessible and touch enabled.
- Keep Counter final values visible even if animation fails.
- Keep Accordion panels usable with keyboard and screen readers.
- Use the Video widget for `#analysis-video` and copy the current embed URL and descriptive title.
- Avoid essential entrance animations. Optional motion must respect `prefers-reduced-motion`.

## Responsive verification

Check every page at approximately 390, 768, and 1440 CSS pixels. Confirm:

- no horizontal scrolling;
- navigation remains available without animation;
- controls are at least 44 by 44 CSS pixels;
- image focal points remain correct;
- portrait graphics and text artwork remain fully readable;
- headings retain a logical H1–H3 order;
- the shared Header, Footer, and floating WhatsApp control appear once.

## Migration sequence

1. Configure Site Settings and upload Media Library assets.
2. Build and publish the Header, Footer, and floating control in Theme Builder.
3. Create the reusable section bar, Counter, Image Carousel, Accordion, FastTrack, result, resource, and tutor patterns.
4. Rebuild each page using the ordered Container table above.
5. Assign canonical permalinks. Keep `/s3-intensivefix/` as a redirect to `/intensivefix/`.
6. Test forms and links without changing DNS.
7. Only after full approval, point the domain to the WordPress host.
