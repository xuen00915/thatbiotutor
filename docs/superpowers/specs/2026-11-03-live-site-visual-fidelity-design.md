# ThatBioTutor live-site visual fidelity design

## Intent

Bring the native WordPress build into close visual and behavioral parity with the current public `https://www.thatbiotutor.com/` site. The public site, captured on 3 November 2026, is the source of truth for every original page. This supersedes the earlier hybrid direction that mixed the `inquire.thatbiotutor.com` home layout with the public-site styling.

The result is for the ThatBioTutor client and its visitors: visitors should recognize the same site at every supported screen size, while the client continues to edit approved content through WordPress without rebuilding layouts.

## Goals

- Reproduce the public site's page order, section structure, copy, images, typography metrics, colors, spacing, alignment, borders, and interactions on every original route.
- Match the shared sticky header, Classes dropdown, active/hover states, footer, and floating WhatsApp control once as reusable theme templates.
- Match desktop, tablet, and mobile layouts, including the original responsive header behavior.
- Preserve the existing native WordPress architecture: Hello Elementor remains an unmodified parent; the child theme owns protected markup, CSS, and JavaScript; ACF Free owns approved editable content.
- Store owned raster media locally as WebP and remove presentation-time dependencies on Wix.
- Keep the IntensiveFix programme as an additional WordPress page using the closest matching ThatBioTutor visual language; it is not an original-page fidelity target.
- Keep all canonical pages portable to a future WordPress host with no hardcoded local, staging, or production hostname.

## Original routes in scope

The public site is authoritative for these WordPress routes:

- Home: `/`
- Classes: `/classes/`
- Sec 3: `/sec-3-pure-g3-biology-tuition/`
- Sec 4: `/sec-4-pure-g3-biology-tuition/`
- Results & Testimonials: `/testimonials/`
- Free Bio Notes: `/notes/`
- Cell Division notes: `/celldivision/`
- About Us: `/about/`
- Contact: `/contact/`
- Our Causes: `/trees/`
- Tuition Recommendations: `/tuitionrecs/`

The extra route `/intensivefix/` stays present but separate.

## Non-goals

- Do not modify or deploy to the live Squarespace/Wix site, its domain, DNS, email, analytics, or consent configuration.
- Do not copy Wix runtime code into WordPress or depend on Wix-hosted layout scripts.
- Do not make Elementor or Elementor Pro data the source of truth for public layouts.
- Do not turn fixed layout structure into unrestricted client-editable markup.
- Do not redesign, modernize, or reinterpret original pages; fidelity takes priority over aesthetic improvement.
- Do not make IntensiveFix replace either Sec 3 or Sec 4.

## Anti-goals

The work is a failure if it merely contains similar content but has different section ordering, image treatment, heading scale, whitespace, header dimensions, colors, or responsive behavior. It is also a failure if visual fidelity comes at the cost of broken navigation, horizontal overflow, inaccessible controls, uneditable approved content, or reliance on the live site's runtime.

## Constraints

- Use native PHP templates, local CSS, and dependency-free JavaScript in `thatbiotutor-hello-child`.
- Use ACF Free field types only; retain the existing private content types for testimonials, tutors, FAQs, and results.
- Keep Hello Elementor parent files unmodified.
- Use the existing `tbt_` and `tbt-` naming conventions.
- Do not redistribute proprietary font files without a licence. Use the closest documented open/system fallback and tune size, weight, tracking, and line-height to match the live typography. The theme should allow licensed font files to be added later without restructuring templates.
- Convert owned raster images to local WebP while preserving source dimensions, crop, focal point, and appropriate responsive sizes. Third-party YouTube content may remain embedded.
- Preserve semantic HTML, keyboard operation, visible focus, alt text, reduced-motion behavior, and the existing security model.

## Approaches considered

### 1. Native child-theme reconstruction with ACF Free — selected

Reconstruct the live markup and styling in the existing PHP templates, map editable copy/media to ACF, keep repeated content in existing content types, and reproduce interactions with local JavaScript. This is the selected approach because it supports the strongest combination of fidelity, portability, performance, maintainability, and client-friendly editing.

### 2. Direct Wix HTML/CSS/runtime import — rejected

This could initially resemble the source closely, but it would carry opaque generated markup, Wix dependencies, brittle selectors, remote assets, and poor WordPress editability. It conflicts with the portability and native WordPress requirements.

### 3. Elementor Free page-by-page recreation — rejected

This would expose more layout controls to the client, but exact shared behavior and responsive parity would be harder to guarantee, generated markup would be noisier, and global fixes would need repetition across pages. It also conflicts with the existing protected-template architecture and the requirement that shared parts not be rebuilt repeatedly.

## Reference capture and comparison

Before changing each route, capture the public page and the local WordPress route at the same viewport and browser settings. Use the current public site as the primary reference and the dated project crawl only when a live asset is temporarily unavailable.

Reference viewports:

- 1440 px desktop
- 768 px tablet
- 390 px mobile
- 320 px narrow mobile regression check

For long pages, compare full-page captures and named section captures. Freeze or normalize carousels, marquees, videos, and other time-dependent content before comparison. Inspect computed font, color, box, gap, crop, and position values for material mismatches rather than relying only on visual memory.

## Shared shell

- The header remains sticky at the top and matches the public site's desktop height, logo size, separators, navigation widths, active green state, and red hover state.
- Classes opens a dropdown containing only Sec 3 and Sec 4. The parent label is not repeated inside the dropdown.
- Tablet and mobile use the public site's responsive navigation pattern, with a keyboard-operable menu and submenu.
- Footer structure, colors, spacing, links, contact details, and legal row match the public site while remaining a single shared template.
- The WhatsApp control matches the public site's size, icon, halo, position, notification detail, and responsive offsets and appears once globally.

## Page reconstruction

For every original route, the template must follow the public page's exact section sequence. Text that appears as live text remains semantic text. Image-based content remains an image when the public site presents it as authored artwork. Repeated elements should be rendered from structured data, but their visual composition must match the public page rather than a generic card system.

Home receives a complete structural correction: the public image-overlay hero comes first, with its grade-jump headline, specialist line, crop, dark overlay, and slider behavior. The missing-key video, FastTrack, trust statistics, hall of fame, testimonials, schools, advantage, student benefits, classroom experience, causes, tutors, FAQ, and closing content follow in the live order and visual treatment.

Classes, Sec 3, Sec 4, Results & Testimonials, Free Bio Notes, Cell Division, About Us, Contact, Our Causes, and Tuition Recommendations receive the same page-by-page treatment. No locally invented section may substitute for missing public content.

## Content ownership and editing

- ACF page fields expose approved headings, body copy, CTA labels/links, and fixed media with client-facing labels and instructions.
- Testimonials, tutors, FAQs, and results remain manageable as structured WordPress entries.
- Shared contact and footer values remain site-wide options or protected shared data, not duplicated per page.
- Templates retain section order, required wrappers, responsive behavior, and accessibility attributes so ordinary client edits cannot break layout.
- Empty editable fields continue to render intentionally empty rather than silently restoring fallback content.

## Responsive and interaction behavior

- Reproduce source breakpoints by observed behavior, not by blindly copying Wix-generated breakpoint names.
- Preserve the live image focal point at every viewport, especially the first home hero slide.
- Carousels match source arrow geometry, dot geometry, placement, current-state styling, transition, autoplay, pause, touch, and keyboard behavior.
- YouTube embeds maintain the public aspect ratio and never create an internal page scrollbar.
- School logos use the public running-panel presentation and scale.
- FAQs, navigation dropdowns, sliders, and mobile menus remain usable without a mouse.
- No supported viewport may introduce horizontal document overflow.

## Verification and completion criteria

- Add a failing contract test before each public behavior change, following repository rules.
- Add a repeatable reference screenshot script or test that captures every original route at the four reference widths.
- Run static tests, setup/idempotency tests, runtime page tests, editing tests, and browser interaction tests after changes.
- Inspect same-size reference/local screenshots and overlays page by page. Completion requires no obvious structural mismatch and no material mismatch in typography scale, colors, spacing, alignment, image crop, or shared-shell geometry.
- Dynamic or third-party video pixels are excluded from pixel comparison, but their containers and surrounding layout are not.
- Confirm every original route returns successfully, every navigation destination works, the Classes dropdown contains Sec 3 and Sec 4 only, and IntensiveFix remains separate.
- Confirm approved fields remain editable in WordPress admin and a clean installation still seeds the complete site.
- Run `scripts/verify-local.sh` before committing and update `CHANGELOG.md` for the fidelity rebuild.

## Builder decisions

The builder may choose exact CSS organization, helper boundaries, capture tooling, and perceptual-diff thresholds as long as the implementation remains reviewable and satisfies the comparison criteria. The builder may also consolidate repeated visual primitives when consolidation does not alter page-specific fidelity.
