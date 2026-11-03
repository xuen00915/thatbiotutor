# Inquire Homepage with Original Header Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Rebuild the homepage body and homepage footer presentation to match the live Inquire homepage while preserving the corrected original shared header and applying every homepage instruction from the supplied DOCX.

**Architecture:** Keep the existing Python-generated static-site architecture, shared partials, local WebP asset manifest, and dependency-free JavaScript enhancements. Replace the homepage fragment with semantic Inquire-ordered sections and add homepage-scoped CSS so other routes retain their existing layouts. Reuse the existing carousel, counter, FAQ, shared footer, and asset contracts where they already satisfy the specification.

**Tech Stack:** Semantic HTML, CSS, vanilla JavaScript, Python static builder, Python `unittest`, Playwright Chromium, GitHub Pages.

**Spec:** `docs/superpowers/specs/2026-11-03-inquire-homepage-original-header-design.md`

## Global Constraints

- Keep the existing original shared header unchanged in geometry, six-link navigation, font treatment, and responsive behaviour.
- Follow the live Inquire homepage body section order and visual system; do not retain original-homepage-only sections.
- Apply every homepage instruction in `Inquire pages to copy over (&what not to).docx`.
- Use folders `1A`, `1B`, and `1C` in numeric order; all emitted raster images remain local WebP files.
- Image 1 must show its lower classroom focal area without excessive zoom.
- Keep the implementation client-editable and one-to-one mappable to future Elementor containers/widgets.
- Do not alter other routes, DNS, Squarespace, or `/Users/dmsyx/Documents/Projects/thatbiotutor-original`.
- All changes use strict TDD: failing test, minimal implementation, full-suite verification.

## Review Focus

- Desktop portrait hero media must be constrained to the carousel frame so the lower `object-position` crop actually renders; Task 2 adds a rendered geometry assertion.
- Narrow screens must stack every comparison/card/gallery without horizontal overflow; Task 3 checks 390px and 768px browser layouts.
- JavaScript-disabled visitors must retain visible carousel media, final statistics, FAQ content, and WhatsApp CTAs; Task 4 adds a no-JavaScript browser check.
- Reduced-motion visitors must see final counter values without animation; Task 4 adds a reduced-motion browser check.
- Homepage-scoped CSS must not change the existing header or non-home routes; Task 3 compares header geometry and smoke-tests canonical routes.

---

### Task 1: Lock the Inquire Homepage Content Contract

**Files:**
- Modify: `tests/test_static_site.py`
- Modify: `site/pages/home.html`
- Modify if required: `site/pages.json`

**Interfaces:**
- Consumes: existing page renderer, `data-layout-slot` section parser, shared header/footer includes, current site configuration.
- Produces: homepage slots `hero`, `pain-points`, `student-results`, `track-record`, `video-transition`, `fasttrack`, `social-proof`, `tutors`, `quality-control`, `practical-sessions`, `trial-guarantee`, `suitability`, `founder`, `faq`, `classroom-life`, and `closing-cta` in that exact order.

- [ ] **Step 1: Write failing static homepage contract tests**

Add tests that assert the exact slot order above, one `h1`, the live Inquire hero/pain/FastTrack/trial/founder/FAQ/closing copy, absence of the embedded trial form, presence of a shield/check icon, and a WhatsApp `Book My Trial Lesson` link.

- [ ] **Step 2: Run the focused tests and verify RED**

Run: `python3 -m unittest tests.test_static_site.HomePageTests -v`

Expected: FAIL because the current original-layout slots and sections do not match the approved Inquire page map.

- [ ] **Step 3: Replace the homepage fragment with the semantic Inquire section map**

Keep copy in `site/pages/home.html`, reuse template variables only for shared values, add concise `CLIENT EDIT` comments, and preserve one section per future Elementor container. Remove original-homepage-only sections from this route.

- [ ] **Step 4: Run the focused static tests and verify GREEN**

Run: `python3 -m unittest tests.test_static_site.HomePageTests -v`

Expected: PASS.

- [ ] **Step 5: Commit the content contract**

```bash
git add tests/test_static_site.py site/pages/home.html site/pages.json
git commit -m "feat: rebuild homepage with Inquire section map"
```

### Task 2: Implement Supplied Media and the Lower Hero Focal Crop

**Files:**
- Modify: `tests/test_rendered_visual_contract.py`
- Modify: `tests/test_static_site.py`
- Modify: `site/assets/css/pages.css`
- Modify if required: `site/assets/js/content.js`
- Verify: `site/assets/images/home/hero-01.webp` through `hero-05.webp`
- Verify: `site/assets/images/shared/track-record-01.webp` through `track-record-04.webp`
- Verify: `site/assets/images/home/classroom-01.webp` through `classroom-03.webp`

**Interfaces:**
- Consumes: `media-carousel`, `data-supplied-group`, and current manifest order contracts.
- Produces: constrained hero media frame, image 1 `object-position: 50% 68%`, five ordered hero images, four ordered track-record images, and three ordered classroom images.

- [ ] **Step 1: Complete the failing rendered focal-point test**

Assert at 1440px that image 1 and its carousel viewport have equal width/height, the crop is `50% 68%`, and the hero frame does not expand to the image's portrait aspect ratio. Extend static order assertions to the rebuilt slot names.

- [ ] **Step 2: Run the focused tests and verify RED**

Run: `python3 -m unittest tests.test_rendered_visual_contract.RenderedOriginalVisualContractTests.test_home_hero_first_image_uses_its_lower_focal_point_on_desktop tests.test_static_site.HomePageTests.test_home_uses_supplied_images_in_manifest_order -v`

Expected: FAIL until the rebuilt markup and constrained frame use the required contracts.

- [ ] **Step 3: Implement media markup and homepage-scoped crop rules**

Use all supplied WebPs without reconversion when pixel comparison confirms they already match the numbered sources. Constrain slide images to the carousel frame with absolute inset sizing and apply deliberate per-image focal positions without destructive cropping.

- [ ] **Step 4: Run the focused media tests and verify GREEN**

Run the Step 2 command.

Expected: PASS.

- [ ] **Step 5: Commit the media contract**

```bash
git add tests/test_rendered_visual_contract.py tests/test_static_site.py site/assets/css/pages.css site/assets/js/content.js
git commit -m "fix: preserve supplied homepage media focal crops"
```

### Task 3: Match the Inquire Desktop and Responsive Visual System

**Files:**
- Modify: `tests/test_rendered_visual_contract.py`
- Modify: `site/assets/css/pages.css`
- Modify only for shared primitives: `site/assets/css/components.css`

**Interfaces:**
- Consumes: Task 1 section classes/IDs and Task 2 media frames.
- Produces: homepage-scoped Inquire backgrounds, containers, type scale, cards, comparison rows, proof/tutor/practical/trial/suitability/founder/FAQ/gallery/closing layouts, and homepage footer presentation.

- [ ] **Step 1: Write failing rendered visual contract tests**

At 1440px assert the hero/body max-widths, alternating white/pale-green/dark-green section backgrounds, centred hero hierarchy, four-column statistics, five FastTrack comparison rows, four tutor items, three practical/gallery items, two suitability columns, and Inquire-style footer treatment. At 390px and 768px assert correct stacking, readable widths, CTA visibility, and zero horizontal overflow. Reassert the original header geometry unchanged.

- [ ] **Step 2: Run the rendered visual tests and verify RED**

Run: `python3 -m unittest tests.test_rendered_visual_contract.RenderedOriginalVisualContractTests -v`

Expected: FAIL on homepage body geometry/styles while existing header checks remain green.

- [ ] **Step 3: Implement homepage-scoped CSS**

Use `.page-home`-scoped selectors and purpose-named component classes. Match the live Inquire desktop/mobile compositions measured from Playwright screenshots without changing other route layouts or the shared header rules.

- [ ] **Step 4: Capture and inspect local desktop/mobile screenshots**

Render 1440px and 390px full-page screenshots after lazy-loading all media; compare section order, background transitions, spacing, card proportions, and footer against the live Inquire captures, excluding the header.

- [ ] **Step 5: Run rendered visual tests and verify GREEN**

Run the Step 2 command.

Expected: PASS with no overflow at release widths.

- [ ] **Step 6: Commit the visual implementation**

```bash
git add tests/test_rendered_visual_contract.py site/assets/css/pages.css site/assets/css/components.css
git commit -m "feat: match Inquire homepage visual system"
```

### Task 4: Verify Carousel, Counters, FAQ, Reduced Motion, and No JavaScript

**Files:**
- Modify: `tests/test_rendered_visual_contract.py`
- Modify if required: `site/assets/js/site.js`
- Modify if required: `site/pages/home.html`

**Interfaces:**
- Consumes: existing `TBT_UI.init`, `data-carousel-*`, `data-counter-key`, and `data-faq-*` APIs.
- Produces: swipe/keyboard carousel behaviour, one-time counter animation, reduced-motion final values, functional FAQ disclosures, and progressive no-JavaScript content.

- [ ] **Step 1: Write failing interaction/accessibility tests**

Test carousel next/previous/keyboard/status on the rebuilt hero; counter final values after scrolling; reduced-motion final values; FAQ toggle states; and a JavaScript-disabled page with visible carousel images, statistics, FAQ answers, and WhatsApp CTA.

- [ ] **Step 2: Run focused interaction tests and verify RED**

Run the named new tests from `tests.test_rendered_visual_contract` with `python3 -m unittest -v`.

Expected: FAIL where rebuilt markup has not yet retained the enhancement hooks or fallback visibility.

- [ ] **Step 3: Implement only missing enhancement/fallback hooks**

Reuse existing JavaScript unless a tested requirement is absent. Do not introduce autoplay or a framework.

- [ ] **Step 4: Run the focused interaction tests and verify GREEN**

Run the Step 2 command.

Expected: PASS.

- [ ] **Step 5: Commit interaction coverage**

```bash
git add tests/test_rendered_visual_contract.py site/assets/js/site.js site/pages/home.html
git commit -m "test: protect homepage progressive interactions"
```

### Task 5: Update Client and Elementor Editing Documentation

**Files:**
- Modify: `docs/client-editing-guide.md`
- Modify: `docs/elementor-migration-map.md`
- Modify: `tests/test_static_site.py`

**Interfaces:**
- Consumes: final homepage section IDs, content locations, asset groups, and interaction hooks.
- Produces: accurate client editing instructions and Elementor container/widget mapping.

- [ ] **Step 1: Write failing documentation contract tests**

Assert that the guide names the rebuilt homepage slots, three supplied asset groups, hero focal-position edit point, counters, FAQ, trial guarantee, shared header, shared footer, and Elementor carousel/counter/accordion equivalents.

- [ ] **Step 2: Run documentation tests and verify RED**

Run: `python3 -m unittest tests.test_static_site.ClientDocumentationTests -v`

Expected: FAIL because the guides describe the previous original-layout homepage.

- [ ] **Step 3: Update both guides**

Document common text/image/order/focal/CTA edits and the exact future Elementor widget mapping without duplicating technical implementation detail.

- [ ] **Step 4: Run documentation tests and verify GREEN**

Run the Step 2 command.

Expected: PASS.

- [ ] **Step 5: Commit documentation**

```bash
git add docs/client-editing-guide.md docs/elementor-migration-map.md tests/test_static_site.py
git commit -m "docs: map rebuilt homepage editing workflow"
```

### Task 6: Full Release Verification and GitHub Pages Deployment

**Files:**
- Verify only: complete branch diff, generated build, published site.

**Interfaces:**
- Consumes: all previous tasks.
- Produces: reviewed, merged, and live GitHub Pages homepage.

- [ ] **Step 1: Run the complete local release gate**

Run: `python3 -m unittest discover -s tests -v && node --check site/assets/js/site.js && node --check site/assets/js/content.js && python3 scripts/build_site.py --output build && git diff --check`

Expected: all tests PASS, JavaScript checks exit 0, production build completes, and patch validation is clean.

- [ ] **Step 2: Inspect final local screenshots and source separation**

Compare 1440px and 390px screenshots with the live Inquire page excluding the header; confirm `thatbiotutor-original` and the original mirror have no changes.

- [ ] **Step 3: Commit any final verified corrections and push the branch**

Create a pull request describing the exact layout authority, DOCX changes, WebP asset orders, responsive checks, and test evidence.

- [ ] **Step 4: Require GitHub CI success before merge**

Watch the PR check; diagnose and correct any CI-only failure before merging.

- [ ] **Step 5: Merge and watch the `main` Pages workflow**

Wait for both validation and deployment jobs to succeed.

- [ ] **Step 6: Verify the published homepage**

At 1440px, 1024px, 768px, and 390px verify original header geometry, Inquire body/footer layout, supplied image order and image-1 lower crop, CTA destinations, carousel/counters/FAQ behaviour, and zero overflow.
