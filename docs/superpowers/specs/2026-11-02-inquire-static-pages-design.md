# Inquire static pages design

## Intent

Create a separate, production-ready static version of the ThatBioTutor inquiry website in the modification workspace. The new homepage and Sec 3 IntensiveFix page will follow the current `inquire.thatbiotutor.com` visual direction, incorporate every change in the supplied Word document and image folders, and remain straightforward to migrate into WordPress Elementor later.

The pages serve Singapore students and parents evaluating Pure or G3 Biology tuition. Their primary action is to contact ThatBioTutor through WhatsApp at `+65 8759 7182`.

## Goals

- Replace the deployed root page with a clean recreation of the current inquiry homepage.
- Add a clean Sec 3 IntensiveFix page at `/s3-intensivefix/`.
- Apply the supplied copy, image ordering, carousel, counter animation, section removals, CTA, and spacing instructions.
- Store all supplied and retained page assets locally so the site does not depend on WordPress or Elementor at runtime.
- Make the pages responsive, keyboard accessible, touch friendly, readable, and fast.
- Make routine client edits easy without requiring a build tool or framework.
- Organise each page so its sections can later be recreated as Elementor containers and widgets with minimal interpretation.
- Preserve the existing Wix mirror under `mirror/www.thatbiotutor.com/` as a reference and fallback; do not rewrite it.

## Non-goals

- Recreate WordPress, Elementor, forms, analytics, tracking pixels, or a CMS inside this repository.
- Change the supplied marketing claims or rewrite approved copy beyond punctuation and minor accessibility labels.
- Publish changes to the live WordPress site.
- Replace or delete the separately preserved original repository snapshot.
- Redesign unrelated pages in the Wix mirror.

## Anti-goals

- Do not produce one monolithic HTML file with inline styles and scripts.
- Do not use generated Elementor markup, remote plugin assets, or brittle WordPress selectors.
- Do not crop people, classroom context, or supplied graphics so tightly that their meaning is lost.
- Do not hide essential content behind motion or make the carousels unusable without dragging.
- Do not make client updates depend on Node, npm, a framework, or command-line compilation.

## Constraints

- GitHub Pages deploys the contents of `mirror/` directly.
- The project currently has no application framework or build pipeline.
- The current homepage source is a Wix mirror, while the requested design reference is a WordPress Elementor site.
- The supplied source package contains five homepage hero images, four track-record images, three classroom images, ten IntensiveFix graphics, and one IntensiveFix review graphic.
- All booking and Contact Us CTAs link to WhatsApp number `+65 8759 7182`.
- The supplied image order is authoritative.
- Motion must honour `prefers-reduced-motion`.

## Approaches considered

### Clean static recreation selected

Recreate the two pages in semantic HTML, shared CSS, and small dependency-free JavaScript modules. This produces compact, maintainable pages that work on GitHub Pages and map predictably into Elementor later. It requires careful visual verification against the current inquiry pages but avoids inheriting plugin-generated complexity.

### Frozen Elementor copy rejected

Save the generated WordPress HTML and patch it. This starts closer to the current pixels, but it retains large generated markup, remote plugin dependencies, unstable selectors, and poor client editability. It also makes later Elementor migration harder because the frozen output is not the editable Elementor source.

## Information architecture

### Shared structure

- Skip link and semantic document landmarks.
- Header with local logo, page-aware primary CTA, and consistent Contact Us action.
- Main content composed of explicitly named sections with stable IDs.
- Footer with existing contact, address, opening-hours, and copyright information.
- Persistent mobile-friendly WhatsApp CTA only if it does not obscure content.

### Homepage

1. Hero and primary trial CTA.
2. Swipeable five-image classroom and student-life carousel using folder `1A` in filename order.
3. Student pain points and introductory testimonials retained from the inquiry page.
4. Track record with four animated counters and the four images from folder `1B` in order.
5. FastTrack system sections retained from the inquiry page.
6. Social proof, tutor, quality-control, and practical-session content retained.
7. Trial lesson guarantee replacing the embedded form: shield icon, approved refund copy, and WhatsApp CTA.
8. Suitability, founder, and FAQ content retained.
9. Life inside a TBT classroom gallery using folder `1C` in order, with the teaching image first.
10. Closing section with additional breathing room above the CTA.

### Sec 3 IntensiveFix

1. Hero without the small grey subtext; approved limited-time subtitle with black and green emphasis.
2. Primary CTA labelled `Fix My Sec 3 Foundation`.
3. Swipeable ten-image IntensiveFix carousel using folder `2A` in numeric order.
4. Pain-point introduction with the approved `Sound familiar` replacement copy.
5. One IntensiveFix review graphic from folder `2B`; remove the unrelated video and three unrelated testimonial cards.
6. Track record with the same counters and four images used on the homepage.
7. Retain the current YouTube link until the replacement video is supplied.
8. FastTrack heading beginning with the approved `All IntensiveFix students will receive` text.
9. Move directly from the FastTrack section to the `This is for` and `This isn't for` section, omitting the sections identified in the specification.
10. Closing section using the supplied EOY grade copy, spacing, and existing CTA destination.

## Content and client editing model

- Put shared editable values in `mirror/assets/js/content.js`: WhatsApp number, prefilled message text, headline fragments, counters, and asset lists.
- Keep long-form section copy in the relevant semantic HTML so a non-technical client can find and edit it directly.
- Use obvious comments such as `CLIENT EDIT: heading`, `CLIENT EDIT: paragraph`, and `CLIENT EDIT: image order` only around genuinely editable content.
- Group assets by page and purpose under `mirror/assets/images/home/`, `mirror/assets/images/shared/`, and `mirror/assets/images/intensivefix/`.
- Use readable, descriptive filenames instead of the supplied numeric or misspelled names while preserving an asset manifest that records the original source name and display order.
- Keep section boundaries one-to-one with future Elementor containers. Avoid CSS selectors that depend on DOM depth.
- Add a short client editing guide describing common text, link, image-order, and counter changes plus an Elementor migration map.

## Visual system

- Preserve the inquiry page's confident biology-tuition identity: dark green, fresh green, warm white, black, and restrained grey.
- Use CSS custom properties for colours, type scale, spacing, radii, shadows, and container widths. These tokens will be documented with values suitable for Elementor global colours and fonts.
- Retain the current page hierarchy and conversion-focused rhythm rather than introducing a new visual concept.
- Use responsive `object-fit` and `object-position` rules per image; never destructively crop source files.
- Use one consistent button system with clear hover, pressed, and focus-visible states.
- Prefer native system or locally available fonts unless the reference typography requires a safely hosted web font.

## Interaction design

- Carousels support touch swipe, previous and next controls, keyboard navigation, visible position indicators, and meaningful image alt text.
- Carousels do not auto-advance while focused or hovered. If auto-advance is used, it pauses for interaction and is disabled for reduced motion.
- Track-record counters animate once when entering the viewport, retain the final value in the HTML for no-JavaScript access, and render immediately when reduced motion is requested.
- FAQ controls use native buttons with correct expanded state and remain usable from the keyboard.
- WhatsApp links use `https://wa.me/6587597182` with short page-specific prefilled messages.

## Accessibility and responsive behaviour

- Use one `h1` per page and a logical heading sequence.
- Provide visible focus styles, sufficient colour contrast, descriptive link text, and useful image alt text.
- Ensure tap targets are at least 44 by 44 CSS pixels.
- Test layouts at narrow mobile, tablet, laptop, and wide desktop widths.
- Prevent horizontal overflow and cumulative layout shift by declaring image dimensions or aspect ratios.
- Ensure all essential content and CTAs work with JavaScript disabled; JavaScript enhances carousels, counters, and FAQ disclosure only.

## Error handling and resilience

- If JavaScript fails, carousel items appear as a scrollable gallery and counters show their final values.
- Missing images leave stable layout space and descriptive alt text rather than collapsing a section.
- External YouTube and WhatsApp destinations remain ordinary links.
- No runtime request is required for page content.

## Verification

- Validate all internal and external links, including every WhatsApp CTA.
- Confirm image order against every supplied folder.
- Compare both pages visually with the current inquiry pages and the supplied screenshots at desktop and mobile widths.
- Test swipe, keyboard carousel controls, counters, FAQ disclosure, reduced-motion behaviour, and no-JavaScript fallbacks.
- Run an accessibility-oriented HTML check and inspect focus order manually.
- Serve the `mirror/` directory locally and smoke-test the GitHub Pages paths.
- Confirm the original Wix mirror and the separate `thatbiotutor-original` snapshot remain unchanged.

## Builder decisions

- Exact CSS values, breakpoints, animation timings, and per-image object positions are left to the implementer, provided they satisfy the visual and accessibility constraints.
- The implementer may choose SVG or CSS for the shield icon; it must be local, accessible, and visually consistent.
- The implementer may omit automatic carousel advancement if manual swipe and controls provide the better experience.

