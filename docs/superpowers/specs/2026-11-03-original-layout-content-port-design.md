# Original ThatBioTutor layout with selected Inquire content design

## Intent

Correct the modified ThatBioTutor site so that `thatbiotutor.com` remains the visual and layout authority. The modified site will faithfully recreate the original site's page compositions, navigation treatment, typography, colours, spacing, responsive behaviour, and restrained visual style in clean static templates. Only the approved content and supplied media from `inquire.thatbiotutor.com` will be transferred.

The site is for Singapore parents and students comparing Pure or G3 Biology tuition. Its main conversion action is contacting ThatBioTutor through WhatsApp. The result must remain easy for a client to edit and straightforward to recreate later in WordPress Elementor.

This design supersedes the earlier `2026-11-02-inquire-static-pages-design.md`, whose visual direction incorrectly treated the Inquire site as the design authority.

## Decisions already made

- Use a clean, faithful recreation rather than archived Wix runtime markup.
- Treat the original `thatbiotutor.com` site as the authority for design and layout.
- Treat the Inquire pages, the supplied Word document, and the supplied folders as content sources only.
- Keep the original homepage section inventory and order; replace content only within the corresponding original sections.
- Keep the IntensiveFix programme on a separate page rather than replacing the existing Sec 3 tuition page.
- Preserve every original page and make every page simply editable.
- Use shared templates for the site-wide header, footer, and floating WhatsApp control.
- Store website images as local WebP files.
- Keep the preserved original and the modified implementation separate.

## Goals

- Reproduce the original site's visual identity and page-level layout closely on desktop, tablet, and mobile.
- Keep the original content and layout of all existing pages unless a supplied instruction explicitly changes that content.
- Transfer approved homepage content only into matching original homepage sections, without adopting the Inquire page structure or visual design.
- Use the approved IntensiveFix content to create the separate new programme page without copying the Inquire site's visual design.
- Add a separate canonical `/intensivefix/` page while retaining `/sec-3-pure-g3-biology-tuition/` unchanged in purpose.
- Preserve `/s3-intensivefix/` as a working alias so existing links do not break.
- Keep all content in readable page fragments and shared partials rather than generated Wix or Elementor markup.
- Keep all supplied and retained media local, converted to WebP, and cropped with deliberate focal positioning.
- Retain the current safe static build, validation, and GitHub Pages deployment approach.
- Document the editing model and the one-to-one mapping from static sections to future Elementor containers.

## Non-goals

- Do not copy the Inquire site's green card-based design, section styling, typography, spacing system, or page chrome.
- Do not reuse or patch the original Wix runtime HTML as the editable implementation.
- Do not create a WordPress installation, Elementor export, CMS, enquiry form backend, or analytics integration in this phase.
- Do not rewrite marketing claims beyond the exact supplied changes, punctuation fixes, accessibility labels, or obvious typographical corrections.
- Do not replace the original Sec 3 tuition page with the holiday IntensiveFix page.
- Do not change Squarespace settings, DNS, or the `thatbiotutor.com` production domain in this phase.
- Do not delete or overwrite the preserved original snapshot.

## Anti-goals

- A visually polished site that resembles `inquire.thatbiotutor.com` more than the original site is a failure.
- A pixel-close copy that is difficult for the client to edit is also a failure.
- Do not create one monolithic page, duplicate the header or footer across pages, or hide routine edits inside JavaScript.
- Do not introduce a framework or runtime dependency merely for presentation.
- Do not crop faces, classroom context, text-heavy graphics, or teaching activity too tightly.
- Do not publish an unreviewed visual rewrite over the current GitHub Pages deployment.

## Authoritative sources and precedence

When sources disagree, use this order:

1. The user's latest clarification and this approved design.
2. The supplied Word document for explicit text, removal, ordering, animation, CTA, and spacing instructions.
3. The numbered supplied asset folders for media choice and ordering.
4. The relevant Inquire page for content not reproduced fully in the Word document.
5. The live and locally archived original `thatbiotutor.com` pages for design, layout, and unchanged page content.
6. Existing modified code only as an implementation foundation; its current green visual design has no authority.

Primary source locations:

- Original local archive: `mirror/www.thatbiotutor.com/`.
- Preserved original repository snapshot: `/Users/dmsyx/Documents/Projects/thatbiotutor-original`.
- Change specification: `/Users/dmsyx/Downloads/drive-download-20261102T021322Z-1-001/Inquire pages to copy over (&what not to).docx`.
- Homepage media: folders `1A. Homepage top slider images`, `1B. Images under numbers`, and `1C. Life inside classroom` in the supplied download directory.
- IntensiveFix media: folders `2A. intensivefix graphics` and `2B. Intensivefix reviews` in the supplied download directory.
- Homepage content reference: `https://inquire.thatbiotutor.com/`.
- IntensiveFix content reference: `https://inquire.thatbiotutor.com/s3-intensivefix/`.

## Approaches considered

### 1. Clean faithful recreation selected

Keep the existing static generator and editable page-fragment model, but replace the incorrect Inquire-derived styling and page compositions with a careful recreation of the original site. This provides the best combination of original-site fidelity, client editability, local WebP media, accessibility, performance, and later Elementor migration.

### 2. Reuse the archived Wix implementation rejected

Serving and patching the archived Wix HTML would begin close to the original pixels, but the output depends on large generated markup, Wix runtime scripts, remote assets, unstable identifiers, and duplicated page data. It conflicts with the requirement for simple client editing and clean Elementor migration.

### 3. Hybrid Wix styling and clean templates rejected

Reusing fragments of Wix CSS while rebuilding the content could shorten some visual work, but the styling is tightly coupled to generated component identifiers and would produce an inconsistent, brittle implementation. It offers little long-term advantage over recreating the small set of original design patterns directly.

## Technical foundation

- Continue using semantic HTML page fragments, shared HTML partials, CSS custom properties, small dependency-free JavaScript enhancements, and the existing Python static generator.
- Retain the existing route manifest, link validation, output safety checks, tests, and GitHub Pages workflow where they remain correct.
- Replace the current design tokens and page CSS with tokens measured from or visually matched to the original site.
- Keep editable long-form copy in each page fragment. Keep only genuinely shared values, such as contact details, global navigation, and reusable statistics, in shared configuration.
- Use shared partials for the header, footer, floating WhatsApp control, and repeated original-site section patterns.
- Keep the original Wix mirror read-only as a reference; build output must never overwrite it.

## Visual and layout authority

The recreation must reflect the original site, including:

- The compact white header and original logo/navigation proportions.
- Full-width photographic hero areas with centred overlaid copy where the original page uses that pattern.
- White page backgrounds, narrow centred content, and generous empty space.
- Dark charcoal horizontal section-title bars used as strong dividers.
- Bright green used selectively for emphasis, links, statistics, and compact CTA buttons.
- Straightforward image and video placements rather than the Inquire site's large rounded cards and soft green panels.
- The original typography hierarchy, line lengths, alignment, and restrained shadows/radii.
- The original page-specific desktop composition and its mobile stacking behaviour.

For existing pages, the original site supplies both the section inventory/order and the layout vocabulary rather than generated code. Inquire-only homepage sections are not imported wholesale. An explicit supplied change is fitted into the nearest corresponding original section without moving the surrounding sections. The new IntensiveFix page has no original counterpart, so it uses the closest original Sec 3 or Classes page pattern for each approved section. All transferred content must look as though it was authored in the original site, not inserted from another website.

## Information architecture and routes

The modified site contains these canonical routes:

| Route | Treatment |
| --- | --- |
| `/` | Original homepage section inventory, order, and layout with approved matching content changes |
| `/classes/` | Faithful recreation of the original Classes page |
| `/sec-3-pure-g3-biology-tuition/` | Faithful recreation; not replaced by IntensiveFix |
| `/sec-4-pure-g3-biology-tuition/` | Faithful recreation |
| `/intensivefix/` | New separate programme page, built entirely from original-site layout patterns |
| `/testimonials/` | Faithful recreation |
| `/notes/` | Faithful recreation |
| `/celldivision/` | Faithful recreation |
| `/about/` | Faithful recreation |
| `/trees/` | Faithful recreation |
| `/tuitionrecs/` | Faithful recreation |
| `/contact/` | Faithful recreation |

Existing alternate paths, including `/classes/sec-3-pure-g3-biology-tuition/` and `/s3-intensivefix/`, remain working aliases to their canonical routes.

## Shared site structure

### Header

- Recreate the original header dimensions, logo scale, navigation density, mobile menu, and overall white treatment.
- Keep the original information architecture as closely as practical.
- Use `Contact Us` for the site-wide rightmost action, as required by the supplied document.
- Make IntensiveFix discoverable without materially changing the header layout; use the original Classes navigation pattern or the smallest adjacent navigation addition that fits the reference.

### Footer

- Recreate the original footer layout, colours, text hierarchy, contact details, address, and link treatment.
- Centralise it as one shared partial.

### Floating contact control

- Retain one shared WhatsApp control styled and positioned to match the original site.
- It must remain clear of page content, keyboard accessible, and small enough not to dominate mobile screens.

## Homepage content mapping

The homepage preserves the original `thatbiotutor.com` section inventory and order. Inquire and the supplied document provide replacement content only where they correspond to an existing original section. They do not provide a new homepage page map.

- Keep the original hero position, height, text alignment, and overlay treatment. Use the supplied five homepage images in numeric order inside that same hero frame. A background slideshow may change the displayed image without changing the hero layout.
- Map approved pain-point and introductory proof copy into the original `1 MISSING KEY` section; do not insert a separate Inquire pain-point block.
- Keep the original FastTrack section in its original position and layout. Replace only approved text or media within it.
- Keep the original track-record/statistics band in its original position. Apply the running-number enhancement and use the four supplied `Images under numbers` assets in the corresponding original results/media area.
- Keep the original Hall of Fame, testimonial, school-logo, TBT Advantage, Student Benefits, experience, causes, tutor, and FAQ sections in their original order. Replace content only where the supplied instructions clearly identify the matching section.
- Use approved quality-control and practical-session content only within the corresponding original advantage/benefit/experience sections; do not introduce Inquire card layouts.
- Put the three supplied `Life inside a TBT classroom` images, in numeric order with the teaching image first, into the original `Sneak Peek Into The TBT Experience` section instead of adding a new gallery after the FAQ.
- Replace the original closing booking/conversion treatment with the supplied trial guarantee while retaining the original closing-section layout: a local shield/check icon, the exact `$95` refund promise, and a `Book My Trial Lesson` WhatsApp CTA.
- Keep the original FAQ section and its position. Do not import Inquire-only suitability or founder sections unless their copy replaces a clearly corresponding original section.
- Add the requested breathing room above the closing CTA without moving the section.

If an Inquire homepage section has no original counterpart and the Word document does not explicitly identify an original section to replace, omit it. The original homepage remains the page map and screenshot baseline.

## IntensiveFix content mapping

The programme lives at `/intensivefix/` and uses the original Sec 3 and general Classes pages as its closest visual/layout references.

- Use an original-style full-width hero composition.
- Remove the small grey hero subtext.
- Use the approved limited-time subtitle with restrained black and green emphasis.
- Label the primary CTA `Fix My Sec 3 Foundation`.
- Use the ten supplied IntensiveFix graphics in numeric order in a swipeable, keyboard-accessible media region styled like the original site.
- Replace the bold introduction with `Sound familiar? After our 2-week IntensiveFix, they’re telling us:`.
- Replace the unrelated proof video with the supplied IntensiveFix review graphic and remove the three unrelated testimonial cards.
- Reuse the homepage running-number treatment and the same four supplied track-record images.
- Retain the current analysis YouTube link until the promised replacement is provided.
- Begin the FastTrack heading with `All IntensiveFix students will receive ThatBioTutor’s FastTrack…`.
- After the FastTrack section, skip directly to the approved `This is for` and `This isn't for` section, omitting the intervening sections identified in the supplied document.
- Use the supplied final EOY-grade copy and retain the existing CTA destination with the requested spacing.
- Do not alter the purpose, route, or content of the original Sec 3 tuition page to accommodate this programme.

## Unchanged original pages

- Each remaining page must reproduce the corresponding live/archive page's section order, copy, imagery, and layout closely.
- Content may be cleaned only where required for valid semantic HTML, accessibility, responsive behaviour, local asset paths, or obvious typographical errors.
- Existing embedded videos may remain external YouTube embeds, but poster images and surrounding layout should be local and stable where practical.
- Missing, obsolete, or unsafe Wix runtime behaviour must be replaced with simple static equivalents without redesigning the page.

## Content and client editing model

- Use one clearly named HTML fragment per page under `site/pages/`.
- Keep shared header, footer, floating contact control, and repeated section patterns under `site/partials/`.
- Put global contact details, navigation entries, canonical site URL, and common statistics in `site/site.json` or an equally obvious shared configuration file.
- Mark common edit points with concise `CLIENT EDIT` comments: headings, paragraphs, CTA labels/links, statistics, FAQs, image order, and video URLs.
- Use descriptive WebP filenames grouped by page and purpose.
- Maintain an asset manifest recording the original source file or URL, output filename, dimensions, intended crop, alt text, and display order.
- Update the client editing guide with the exact files to edit for common tasks.
- Document Elementor equivalents for global colours, typography, containers, image carousels, counters, FAQ accordions, header/footer templates, and the floating WhatsApp control.

## Media handling

- Convert all retained raster website images to WebP. SVG is allowed for simple icons and logos when it is the appropriate original-quality format.
- Preserve original source files outside generated output; conversion must not destroy or overwrite them.
- Use responsive image dimensions and `object-fit`/`object-position` only where the original layout crops an image.
- Set per-image focal positions so faces, classroom activity, and text remain visible.
- Do not zoom supplied images more than needed to fill the intended original-style region.
- Text-heavy IntensiveFix graphics should normally use `contain` rather than destructive cropping.

## Interactions and accessibility

- Carousels support touch swipe, visible previous/next controls, keyboard navigation, meaningful status text, and a no-JavaScript scrollable fallback.
- Running statistics show final values in HTML and animate once when visible. Reduced-motion users see final values immediately.
- FAQ controls use real buttons, correct expanded state, and visible focus.
- Keep one `h1` per page and a logical heading hierarchy.
- Preserve at least 44-by-44 CSS-pixel interactive targets, sufficient colour contrast, useful alt text, skip navigation, and landmark structure.
- Essential content, navigation, contact links, and final statistics remain usable without JavaScript.

## Responsive behaviour

- Verify the recreation at approximately 390, 768, 1280, and 1440 CSS pixels.
- Match the original site's compact centred desktop layout rather than stretching content to modern full-width card grids.
- Stack columns and media in the same order as the original mobile layouts.
- Prevent horizontal overflow from text-heavy supplied graphics and carousel controls.
- Preserve declared image dimensions or aspect ratios to minimise layout shift.

## Deployment and separation

- Work only in the modified repository/worktree. Do not edit `/Users/dmsyx/Documents/Projects/thatbiotutor-original`.
- Keep `mirror/www.thatbiotutor.com/` unchanged as the local original reference.
- Generate the deployable site into the existing build/output directory; never make the reference mirror itself the build target.
- After approval, implementation, and verification, push the corrected modified version to GitHub and allow the existing GitHub Pages workflow to deploy it.
- Do not point `thatbiotutor.com`, change Squarespace configuration, or alter DNS without a separate explicit request.

## Error handling and resilience

- Build validation fails on broken internal links, missing local assets, non-WebP raster output, unsafe paths, duplicate canonical routes, or missing required shared partials.
- Missing media preserves stable layout space and descriptive fallback text rather than collapsing the page.
- External YouTube and WhatsApp links remain ordinary usable links if JavaScript fails.
- The build remains deterministic and does not fetch live WordPress or Wix content at runtime.

## Verification

- Compare every route side-by-side with its original live/archive counterpart at desktop and mobile widths.
- Use screenshot regression or structured visual checklists for header, hero, section order, section-title bars, media sizing, CTA sizing, footer, and mobile stacking.
- Confirm the homepage and IntensiveFix page content against every applicable Word-document instruction and supplied folder order.
- Confirm all original routes and aliases build and return successfully.
- Confirm the Sec 3 tuition page remains distinct from IntensiveFix.
- Confirm all site-wide header/footer/floating-control instances come from shared templates.
- Confirm every local raster image emitted by the site is WebP and every referenced asset exists.
- Test keyboard navigation, visible focus, carousel controls, FAQ disclosure, counters, reduced motion, and no-JavaScript fallbacks.
- Run the existing Python test suite plus HTML/link/asset validation.
- Verify the preserved original snapshot and local original mirror have no changes.
- Inspect the GitHub Pages deployment after the final push; deployment does not include any domain or Squarespace changes.

## Builder calls

The builder may decide exact CSS values, breakpoints, focus styling details, animation timing, and per-image focal positions after measuring the original reference. The builder may choose a local SVG or CSS shield icon. The builder may omit carousel auto-advance; manual controls and swipe are required. The builder may place the IntensiveFix navigation entry within the closest original navigation pattern, provided the header's original proportions are preserved and the page remains discoverable.

The builder must not decide to adopt Inquire styling, replace an original route's purpose, remove original page content, or publish domain/DNS changes.
