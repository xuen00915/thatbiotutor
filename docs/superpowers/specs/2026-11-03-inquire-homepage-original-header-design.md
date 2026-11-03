# Inquire homepage with original header design

## Intent

Rebuild the deployed homepage so that its body follows `https://inquire.thatbiotutor.com/` section order, layout, visual system, copy, and responsive behaviour. The only visual exception is the shared header, which remains the corrected original `thatbiotutor.com` header. Apply every homepage instruction in the supplied DOCX and retain the clean static, client-editable, Elementor-ready implementation.

This specification supersedes the homepage direction in `2026-11-03-original-layout-content-port-design.md`. Other routes continue to use their existing approved layouts unless the user separately changes them.

## Authoritative sources

Use these sources in this order:

1. The user's latest instructions.
2. `Inquire pages to copy over (&what not to).docx` for additions, removals, copy, ordering, and spacing.
3. The numbered folders `1A. Homepage top slider images`, `1B. Images under numbers`, and `1C. Life inside classroom` for media and display order.
4. The live Inquire homepage for body layout, responsive behaviour, colours, typography, spacing, and section composition.
5. The existing modified site only as the technical foundation and source of the shared original header.

## Selected approach

Rebuild the homepage body in the existing semantic static-site system. Preserve the Python builder, shared partials, local WebP assets, page fragments, and small dependency-free JavaScript enhancements. Reproduce the Inquire homepage body with purpose-named classes rather than copying generated Elementor markup.

This approach is selected because it provides the requested visual fidelity while keeping routine edits simple and preserving a clear one-to-one mapping to future Elementor containers and widgets.

## Rejected approaches

- Restyling the existing original-layout homepage is rejected because its page map and composition differ from the Inquire homepage and caused the current mismatch.
- Copying or embedding live Elementor output is rejected because it would introduce generated markup, remote dependencies, unstable selectors, and poor client editability.
- An iframe or runtime dependency on the Inquire site is rejected because it would not be an independent deployable site.

## Scope

- Rebuild only the homepage body and its homepage-specific footer presentation.
- Keep the original shared header geometry, six navigation links, responsive menu, and original font treatment unchanged.
- Keep the footer as one shared partial. Homepage-specific CSS may present it like the Inquire footer without duplicating its markup or changing other pages.
- Keep the floating WhatsApp action shared and usable.
- Do not change Squarespace, DNS, the production domain, or the preserved original copy.

## Homepage structure

Follow the live Inquire homepage in this order:

1. Hero headline, supporting proof, refund note, and primary trial CTA.
2. Five-image hero carousel using folder `1A` in numeric order.
3. Three student pain statements.
4. Introductory student-result testimonials.
5. Track-record heading, four animated statistics, and four images from folder `1B` in numeric order.
6. The existing Inquire transition/video region.
7. Dark-green FastTrack system section with its five comparison rows.
8. Social-proof region, school crests, grade-jump media, and reviews treatment from Inquire.
9. Meet Your Tutors.
10. Quality-control proof.
11. Real Practical Sessions.
12. Trial lesson guarantee, replacing the embedded form.
13. `This is for` and `This isn't for` suitability columns.
14. About the Founder.
15. FAQ.
16. Life inside a TBT classroom using folder `1C` in numeric order.
17. Final dark-green closing section with additional space above its CTA.
18. Footer presented in the Inquire homepage style.

Do not retain original-homepage-only sections that are absent from the Inquire page map.

## Required DOCX changes

- Use all five folder `1A` images in numeric order.
- Crop each carousel image deliberately without excessive zoom.
- Constrain each hero image to the carousel frame so `object-position` controls the crop.
- Give image 1 a lower focal position that keeps the practical activity and students visible instead of centring on the empty wall.
- Use the four folder `1B` images below the animated statistics in numeric order.
- Animate the statistic values once when they enter the viewport. Honour reduced motion and show final values without JavaScript.
- Replace the trial form with a local shield/check icon, the exact `$95` refund promise, and a `Book My Trial Lesson` WhatsApp CTA.
- Use the three folder `1C` images after the FAQ in numeric order, with the teaching image first.
- Add visible breathing room between the final closing copy and its CTA.

## Visual system

- Match the live Inquire body: warm white surfaces, dark forest-green feature sections, fresh-green accents, soft pale-green panels, rounded cards, restrained shadows, and centred conversion-focused rhythm.
- Match the Inquire body container widths, type scale, line lengths, section spacing, card proportions, and desktop/mobile stacking as closely as practical.
- Do not carry the original site's dark divider-bar layout into the homepage body.
- The boundary between the original white header and the Inquire-style hero should feel intentional without altering the header itself.
- Use locally hosted existing fonts and assets; do not add a new remote runtime dependency.

## Content and editing model

- Keep long-form homepage copy in `site/pages/home.html` with concise `CLIENT EDIT` comments.
- Keep shared contact details and navigation in `site/site.json`.
- Keep image inventory, dimensions, alt text, and ordering in the existing content/asset manifest.
- Keep all local raster assets in WebP format.
- Use stable section IDs and one semantic section per future Elementor container.
- Update the client editing and Elementor migration documentation for any changed homepage sections or widgets.

## Interaction and accessibility

- The carousel supports buttons, keyboard navigation, swipe, visible position status, and a usable no-JavaScript fallback.
- Counters expose final values in HTML, animate only as enhancement, and honour `prefers-reduced-motion`.
- FAQ controls remain real buttons with correct expanded state and keyboard support.
- Keep one `h1`, a logical heading hierarchy, descriptive alt text, visible focus, accessible contrast, and at least 44px interaction targets.
- Prevent horizontal overflow at 390px, 768px, 1024px, 1280px, and 1440px.

## Verification

- Compare the rebuilt homepage side-by-side with the live Inquire homepage at desktop and mobile widths, excluding the header.
- Verify section order, background transitions, container widths, typography, imagery, cards, CTA spacing, footer presentation, and responsive stacking.
- Verify every DOCX homepage instruction and all three numbered folder orders.
- Verify image 1 shows its lower focal area at desktop and mobile sizes.
- Verify carousel, swipe, keyboard, counters, reduced motion, FAQ, WhatsApp links, and no-JavaScript behaviour.
- Run the complete Python/browser test suite, JavaScript syntax checks, production build, asset validation, and patch validation.
- Deploy through the existing GitHub Pages workflow only after local and CI verification pass, then inspect the published page.
