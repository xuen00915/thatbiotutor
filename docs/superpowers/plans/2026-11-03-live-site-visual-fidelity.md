# ThatBioTutor Live-Site Visual Fidelity Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Rebuild every original WordPress route so its desktop, tablet, and mobile presentation closely matches the 3 November 2026 public `thatbiotutor.com` site while retaining native templates, ACF Free editing, local WebP media, and a separate IntensiveFix page.

**Architecture:** The Hello Elementor child theme remains the presentation source of truth. Protected PHP templates reproduce the reference section order, ACF Free and existing private content types supply editable content, focused local stylesheets reproduce each page family, and dependency-free JavaScript supplies shared interactions. A Playwright capture utility records same-size public/local screenshots and computed geometry for repeatable visual review without making runtime code depend on Wix.

**Tech Stack:** WordPress 7.1.4, PHP 8.3, Hello Elementor 3.5.1 child theme, ACF Free 6.8.11, vanilla CSS/JavaScript, Node.js, Playwright 1.64.1, WebP media.

**Spec:** `docs/superpowers/specs/2026-11-03-live-site-visual-fidelity-design.md`

## Global Constraints

- The public site captured on 3 November 2026 is authoritative for original-page content, order, styling, and responsive behavior.
- Use native PHP templates, local CSS, and dependency-free JavaScript; keep Hello Elementor parent files unmodified.
- Use ACF Free field types only and preserve structured testimonials, tutors, FAQs, and results.
- Use `tbt_` and `tbt-` prefixes for PHP, options, handles, fields, and CSS.
- Keep owned raster media local and WebP; YouTube may remain embedded.
- Do not redistribute unlicensed proprietary fonts or introduce Wix runtime dependencies.
- Keep page structure and shared components protected; approved content remains client-editable.
- Do not hardcode local, staging, or production hostnames.
- Add a failing contract test before each public behavior change.
- Keep `/intensivefix/` separate from original Sec 3 and Sec 4 routes.

## Review Focus

- A public reference timeout or blocked asset must fail the capture command with a route-specific diagnostic while leaving local application tests runnable; Task 1 tests this.
- Frozen visual capture must stop carousels, marquees, transitions, and autoplay consistently so comparisons are repeatable; Task 1 tests this.
- Navigation must remain correct after switching across desktop/mobile breakpoints with an open menu or dropdown; Task 2 tests this.
- Missing optional media and deliberately blank ACF fields must not restore unintended fallback copy or collapse page geometry; Tasks 3–7 test this.
- Long client-edited headings, testimonial copy, and links must not create horizontal document overflow at 320 px; Task 9 tests this.

---

### Task 1: Repeatable reference capture and visual review tooling

**Files:**
- Create: `resources/visual-reference-manifest.json`
- Create: `scripts/capture-visual-reference.js`
- Create: `scripts/build-visual-review.js`
- Create: `tests/visual-reference-tooling-test.js`
- Modify: `package.json`
- Modify: `.gitignore`

**Interfaces:**
- Consumes: the canonical route list in the approved design specification.
- Produces: `validateManifest(value): void`, `parseArgs(argv: string[]): CaptureOptions`, and `captureMatrix(options: CaptureOptions): Promise<void>` exported from `scripts/capture-visual-reference.js`; `.artifacts/visual/<run>/reference|local/<route>/<width>.png`; `.artifacts/visual/<run>/metrics.json`; and `.artifacts/visual/<run>/review.html`.

- [ ] **Step 1: Write the failing tooling test**

  In `tests/visual-reference-tooling-test.js`, assert that the manifest contains the 11 original routes and viewports `1440`, `768`, `390`, and `320`; invalid or missing base URLs throw route-specific errors; `--freeze` adds a capture attribute and reduced-motion stylesheet; and generated review HTML references both reference and local screenshots for every matrix cell.

- [ ] **Step 2: Run the tooling test and verify it fails**

  Run: `node tests/visual-reference-tooling-test.js`

  Expected: FAIL because the manifest and capture modules do not exist.

- [ ] **Step 3: Implement the manifest and capture utilities**

  `resources/visual-reference-manifest.json` maps the route keys `home`, `classes`, `sec3`, `sec4`, `testimonials`, `notes`, `celldivision`, `about`, `contact`, `trees`, and `tuitionrecs` to canonical paths and the four fixed viewport widths. The capture command accepts `--reference-base`, `--local-base`, `--output`, optional `--route`, and `--freeze`; captures full pages; records title, document width/height, body font, header box, section boxes, and overflow state; and exits nonzero with the route and URL on navigation failure. `scripts/build-visual-review.js` generates a no-dependency comparison page with side-by-side and opacity-overlay views.

- [ ] **Step 4: Add scripts and artifact exclusions**

  Add `visual:capture` and `visual:review` package scripts. Ignore `/.artifacts/` so screenshots and live-site captures never enter the portable source package.

- [ ] **Step 5: Run the tooling tests and one smoke capture**

  Run: `node tests/visual-reference-tooling-test.js`

  Run: `node scripts/capture-visual-reference.js --reference-base=https://www.thatbiotutor.com --local-base=http://localhost:8888 --output=.artifacts/visual/smoke --route=home --freeze`

  Expected: PASS and paired home screenshots plus `metrics.json` are created.

- [ ] **Step 6: Commit**

  ```bash
  git add .gitignore package.json resources/visual-reference-manifest.json scripts/capture-visual-reference.js scripts/build-visual-review.js tests/visual-reference-tooling-test.js
  git commit -m "test: add live-site visual comparison tooling"
  ```

### Task 2: Shared typography, header, footer, navigation, and WhatsApp control

**Files:**
- Modify: `wordpress/wp-content/themes/thatbiotutor-hello-child/assets/css/base.css`
- Modify: `wordpress/wp-content/themes/thatbiotutor-hello-child/assets/css/components.css`
- Modify: `wordpress/wp-content/themes/thatbiotutor-hello-child/assets/js/site.js`
- Modify: `wordpress/wp-content/themes/thatbiotutor-hello-child/header.php`
- Modify: `wordpress/wp-content/themes/thatbiotutor-hello-child/footer.php`
- Modify: `wordpress/wp-content/themes/thatbiotutor-hello-child/inc/theme-helpers.php`
- Modify: `tests/shared-shell-test.php`
- Modify: `tests/navigation-test.php`
- Modify: `tests/site-interactions-runtime.js`

**Interfaces:**
- Consumes: capture metrics and screenshots from Task 1; `tbt_navigation_items()`, `tbt_render_navigation()`, `tbt_shared_field()`, and `tbt_whatsapp_url()`.
- Produces: one shared sticky shell with stable `data-site-header`, `data-menu`, `data-menu-toggle`, `data-submenu-toggle`, `data-whatsapp-float`, and active-page state used by every later task.

- [ ] **Step 1: Extend the static shell tests to the reference contract**

  Assert that the body typography overrides Hello Elementor, the desktop header uses reference-measured height/logo/separators, the active link is green, hover/focus is red, Classes has only Sec 3 and Sec 4 children, the footer and WhatsApp control render once, and CSS has explicit desktop/tablet/mobile geometry without arbitrary duplicate shell rules.

- [ ] **Step 2: Extend the browser interaction test**

  Add a sequence that opens the mobile menu and Classes submenu, resizes above and below the breakpoint twice, and verifies hidden/expanded states reset correctly; assert sticky header position, keyboard Escape close, focus return, and no document overflow at `768`, `390`, and `320`.

- [ ] **Step 3: Run the focused tests and verify failure**

  Run: `php tests/shared-shell-test.php && php tests/navigation-test.php && node tests/site-interactions-runtime.js`

  Expected: FAIL on the new reference geometry, icon markup, active state, or resize behavior.

- [ ] **Step 4: Rebuild the shared shell**

  Match computed live-site typography and shell geometry while retaining permitted font fallbacks. Replace the `WA` text placeholder with accessible local SVG/CSS icon markup and notification detail; match halo, position, and mobile offsets. Keep Classes submenu items to Sec 3 and Sec 4 only. Keep markup semantic and all controls keyboard operable.

- [ ] **Step 5: Run focused tests and capture the shell at all widths**

  Run: `php tests/shared-shell-test.php && php tests/navigation-test.php && node tests/site-interactions-runtime.js`

  Run: `node scripts/capture-visual-reference.js --reference-base=https://www.thatbiotutor.com --local-base=http://localhost:8888 --output=.artifacts/visual/shell --route=home --freeze`

  Expected: tests PASS; header and floating control have no material geometry/color mismatch in the review page.

- [ ] **Step 6: Commit**

  ```bash
  git add wordpress/wp-content/themes/thatbiotutor-hello-child tests/shared-shell-test.php tests/navigation-test.php tests/site-interactions-runtime.js
  git commit -m "fix: match the original shared site shell"
  ```

### Task 3: Home page structure, content, media, and interactions

**Files:**
- Create: `wordpress/wp-content/themes/thatbiotutor-hello-child/assets/css/pages-home.css`
- Modify: `wordpress/wp-content/themes/thatbiotutor-hello-child/front-page.php`
- Modify: `wordpress/wp-content/themes/thatbiotutor-hello-child/inc/reference-content.php`
- Modify: `wordpress/wp-content/themes/thatbiotutor-hello-child/inc/acf-fields.php`
- Modify: `wordpress/wp-content/themes/thatbiotutor-hello-child/inc/theme-helpers.php`
- Modify: `wordpress/wp-content/themes/thatbiotutor-hello-child/assets/js/site.js`
- Modify: `wordpress/wp-content/themes/thatbiotutor-hello-child/assets/images/media-manifest.json`
- Modify: `wordpress/wp-content/themes/thatbiotutor-hello-child/assets/images/`
- Modify: `tests/home-page-test.php`
- Modify: `tests/home-responsive-contract-test.php`
- Modify: `tests/runtime-browser-test.js`

**Interfaces:**
- Consumes: shared shell from Task 2; `tbt_page_field()`, `tbt_page_image()`, `tbt_render_testimonials()`, `tbt_render_tutors()`, `tbt_render_faqs()`, and manifest-backed `tbt_media()`.
- Produces: live-order home markup and `pages-home.css`; reusable carousel state contract `[data-carousel]`, `[data-carousel-slide]`, `[data-carousel-prev]`, `[data-carousel-next]`, and `[data-carousel-dot]` retained for Task 6.

- [ ] **Step 1: Replace the home tests with the live-order contract**

  Assert the exact ordered section keys `hero`, `missing-key`, `fasttrack`, `trust`, `hall-of-fame`, `testimonials`, `schools`, `advantage`, `benefits`, `experience`, `causes`, `tutors`, `faq`, and `closing`; assert the image-overlay hero contains the grade-jump headline and specialist line; assert every referenced media key exists; and assert blank ACF headings/images remain blank without silently restoring seed values.

- [ ] **Step 2: Add responsive and carousel browser assertions**

  Assert reference-matched hero height/crop/overlay, first-slide focal position, arrow/dot geometry, keyboard/touch-safe controls, running school panel, reduced-motion behavior, video aspect ratio, and no overflow at all four widths.

- [ ] **Step 3: Run the focused tests and verify failure**

  Run: `php tests/home-page-test.php && php tests/home-responsive-contract-test.php && node tests/runtime-browser-test.js`

  Expected: FAIL because the carousel currently precedes a separate locally invented hero and section order/geometry differ.

- [ ] **Step 4: Reconstruct `front-page.php` and home data**

  Put the carousel image, overlay, grade-jump headline, and specialist line in one public-site hero. Reproduce each subsequent live section with semantic text where the source uses text and local WebP artwork where the source uses authored artwork. Register only approved editable fields and preserve existing structured renderers for testimonials, tutors, FAQs, and results.

- [ ] **Step 5: Implement home-specific styling and interaction details**

  Enqueue `pages-home.css` after shared CSS. Match the live desktop/tablet/mobile layout, including heading scales, section padding, dark/white/green surfaces, video widths, card/container shapes, school-logo motion, media crops, arrows, and dots. Ensure missing optional media leaves a stable placeholder wrapper only when the public layout needs its geometry.

- [ ] **Step 6: Run tests and inspect all home comparisons**

  Run: `php tests/home-page-test.php && php tests/home-responsive-contract-test.php && node tests/runtime-browser-test.js`

  Run: `node scripts/capture-visual-reference.js --reference-base=https://www.thatbiotutor.com --local-base=http://localhost:8888 --output=.artifacts/visual/home --route=home --freeze && node scripts/build-visual-review.js .artifacts/visual/home`

  Expected: PASS and no obvious mismatch in section order, major geometry, typography scale, crop, color, or control placement.

- [ ] **Step 7: Commit**

  ```bash
  git add wordpress/wp-content/themes/thatbiotutor-hello-child tests/home-page-test.php tests/home-responsive-contract-test.php tests/runtime-browser-test.js
  git commit -m "feat: reproduce the original home page"
  ```

### Task 4: Classes, Sec 3, and Sec 4 programme pages

**Files:**
- Create: `wordpress/wp-content/themes/thatbiotutor-hello-child/assets/css/pages-programmes.css`
- Modify: `wordpress/wp-content/themes/thatbiotutor-hello-child/page-classes.php`
- Modify: `wordpress/wp-content/themes/thatbiotutor-hello-child/page-sec-3.php`
- Modify: `wordpress/wp-content/themes/thatbiotutor-hello-child/page-sec-4.php`
- Modify: `wordpress/wp-content/themes/thatbiotutor-hello-child/inc/reference-content.php`
- Modify: `wordpress/wp-content/themes/thatbiotutor-hello-child/inc/acf-fields.php`
- Modify: `wordpress/wp-content/themes/thatbiotutor-hello-child/inc/theme-helpers.php`
- Modify: `wordpress/wp-content/themes/thatbiotutor-hello-child/assets/images/media-manifest.json`
- Modify: `wordpress/wp-content/themes/thatbiotutor-hello-child/assets/images/`
- Modify: `tests/classes-pages-test.php`
- Modify: `tests/tuition-responsive-contract-test.php`
- Modify: `tests/runtime-pages-test.sh`

**Interfaces:**
- Consumes: shared shell, page field/image helpers, local media manifest, and shared FAQ renderer.
- Produces: three public-site-faithful programme routes and `pages-programmes.css`; original Classes navigation destinations remain unchanged.

- [ ] **Step 1: Write failing ordered-section and content tests for all three routes**

  Assert each template's public section sequence, exact programme distinctions, CTA destinations, public artwork/media keys, and correct FAQ scope. Assert Sec 3 and Sec 4 are not interchangeable and Classes contains no invented generic problem/feature cards where the public site uses different compositions.

- [ ] **Step 2: Write failing responsive layout assertions**

  Pin the public hero composition, image/text column order, typography scale, surfaces, cards, tables, CTA widths, and mobile stacking at `1440`, `768`, `390`, and `320`; assert deliberately blank client fields stay blank.

- [ ] **Step 3: Run the focused tests and verify failure**

  Run: `php tests/classes-pages-test.php && php tests/tuition-responsive-contract-test.php && bash tests/runtime-pages-test.sh`

  Expected: FAIL on locally invented sections and public geometry/content absent from current templates.

- [ ] **Step 4: Reconstruct all three templates and their editable data**

  Follow the captured public section order page by page. Use shared helpers only for truly repeated patterns; keep page-specific compositions explicit. Update ACF groups and fallback content to the current crawl, and add/convert required local WebP assets with accurate alt text and focal points.

- [ ] **Step 5: Implement `pages-programmes.css` and enqueue it only for programme routes**

  Match measured layout and breakpoints; avoid generic equal-card substitutions. Keep CTA/link behavior and semantic heading order intact.

- [ ] **Step 6: Run tests and inspect three-route comparisons**

  Run: `php tests/classes-pages-test.php && php tests/tuition-responsive-contract-test.php && bash tests/runtime-pages-test.sh`

  Run the visual capture utility for `classes`, `sec3`, and `sec4` and inspect all four viewport comparisons.

  Expected: PASS and no material mismatch in section order, artwork treatment, spacing, or responsive stacking.

- [ ] **Step 7: Commit**

  ```bash
  git add wordpress/wp-content/themes/thatbiotutor-hello-child tests/classes-pages-test.php tests/tuition-responsive-contract-test.php tests/runtime-pages-test.sh
  git commit -m "feat: reproduce the original class pages"
  ```

### Task 5: Results & Testimonials archive

**Files:**
- Create: `wordpress/wp-content/themes/thatbiotutor-hello-child/assets/css/pages-testimonials.css`
- Modify: `wordpress/wp-content/themes/thatbiotutor-hello-child/page-testimonials.php`
- Modify: `wordpress/wp-content/themes/thatbiotutor-hello-child/inc/reference-content.php`
- Modify: `wordpress/wp-content/themes/thatbiotutor-hello-child/inc/acf-fields.php`
- Modify: `wordpress/wp-content/themes/thatbiotutor-hello-child/inc/testimonials.php`
- Modify: `wordpress/wp-content/themes/thatbiotutor-hello-child/inc/results.php`
- Modify: `wordpress/wp-content/themes/thatbiotutor-hello-child/inc/theme-helpers.php`
- Modify: `wordpress/wp-content/themes/thatbiotutor-hello-child/assets/images/media-manifest.json`
- Modify: `wordpress/wp-content/themes/thatbiotutor-hello-child/assets/images/`
- Modify: `tests/testimonials-test.php`
- Modify: `tests/results-test.php`
- Modify: `tests/testimonial-archive-test.php`
- Modify: `tests/runtime-browser-test.js`

**Interfaces:**
- Consumes: `tbt_render_testimonials(array $args): void`, `tbt_render_results(): void`, shared shell, and local media helpers.
- Produces: public-order result artwork, video stories, grouped testimonial content, and a fixed-aspect non-scrolling YouTube container styled by `pages-testimonials.css`.

- [ ] **Step 1: Write failing archive completeness and order tests**

  Assert the live page's section order and headings, every current year/group represented by the 3 November crawl, required results artwork/video/testimonial media, and the absence of locally invented archive navigation or empty year containers not present on the public site.

- [ ] **Step 2: Add failing video and responsive browser assertions**

  Assert YouTube iframes have `scrolling="no"`, correct aspect-ratio wrappers, no inner document scrollbar, no horizontal overflow, and reference-matched mobile stacking and type sizes.

- [ ] **Step 3: Run focused tests and verify failure**

  Run: `php tests/testimonials-test.php && php tests/results-test.php && php tests/testimonial-archive-test.php && node tests/runtime-browser-test.js`

  Expected: FAIL because the current archive is a simplified year-card layout and omits public compositions.

- [ ] **Step 4: Reconstruct the template, renderers, and data**

  Preserve structured testimonial/result editing but emit the public visual composition and complete content groups. Add public video/poster/media data as local WebP or third-party embeds as appropriate. Keep long quotes semantic and accessible.

- [ ] **Step 5: Implement and enqueue `pages-testimonials.css`**

  Match public widths, type hierarchy, year/group separation, artwork scaling, quote presentation, media aspect ratios, and whitespace at all reference widths.

- [ ] **Step 6: Run tests and inspect the testimonial comparison**

  Run all four focused tests, capture `testimonials` at all widths, and inspect the review overlay.

  Expected: PASS with no missing sections or internal video scrolling.

- [ ] **Step 7: Commit**

  ```bash
  git add wordpress/wp-content/themes/thatbiotutor-hello-child tests/testimonials-test.php tests/results-test.php tests/testimonial-archive-test.php tests/runtime-browser-test.js
  git commit -m "feat: reproduce results and testimonials"
  ```

### Task 6: Free Bio Notes and Cell Division pages

**Files:**
- Create: `wordpress/wp-content/themes/thatbiotutor-hello-child/assets/css/pages-resources.css`
- Modify: `wordpress/wp-content/themes/thatbiotutor-hello-child/page-notes.php`
- Modify: `wordpress/wp-content/themes/thatbiotutor-hello-child/page-celldivision.php`
- Modify: `wordpress/wp-content/themes/thatbiotutor-hello-child/inc/reference-content.php`
- Modify: `wordpress/wp-content/themes/thatbiotutor-hello-child/inc/acf-fields.php`
- Modify: `wordpress/wp-content/themes/thatbiotutor-hello-child/inc/theme-helpers.php`
- Modify: `wordpress/wp-content/themes/thatbiotutor-hello-child/assets/js/site.js`
- Modify: `wordpress/wp-content/themes/thatbiotutor-hello-child/assets/images/media-manifest.json`
- Modify: `wordpress/wp-content/themes/thatbiotutor-hello-child/assets/images/`
- Modify: `tests/resource-pages-test.php`
- Modify: `tests/resource-responsive-contract-test.php`
- Modify: `tests/runtime-browser-test.js`

**Interfaces:**
- Consumes: carousel contract from Task 3, structured notes-review testimonials, page fields/images, and shared shell.
- Produces: public Notes hero slider/CTA, complete downstream Notes sections, public Cell Division resource content, and `pages-resources.css`.

- [ ] **Step 1: Write failing route-completeness tests**

  Assert the Notes hero contains the public slider, heading, supporting text, and button; assert every public Notes section appears in order; assert Cell Division uses the public resource composition; assert all slider media keys exist and blank ACF values remain blank.

- [ ] **Step 2: Write failing responsive and carousel assertions**

  Pin the public slider crop, arrows, dots, button placement, benefit artwork, review typography, download/form CTA layout, and mobile stacking. Assert the shared carousel state reports the correct slide and pauses under reduced motion.

- [ ] **Step 3: Run focused tests and verify failure**

  Run: `php tests/resource-pages-test.php && php tests/resource-responsive-contract-test.php && node tests/runtime-browser-test.js`

  Expected: FAIL on missing public sections and mismatched hero/slider geometry.

- [ ] **Step 4: Reconstruct both templates, content, and media**

  Match the public page structure exactly without inventing a new data-collection form. Use the current approved CTA/link behavior from the public site or shared contact route when the external endpoint cannot be made portable. Register approved fields and local WebP media.

- [ ] **Step 5: Implement and enqueue `pages-resources.css`**

  Match public colors, whitespace, text widths, artwork size, controls, and responsive behavior. Reuse carousel behavior without duplicating event logic.

- [ ] **Step 6: Run tests and inspect Notes/Cell Division comparisons**

  Run focused tests, capture `notes` and `celldivision` at all widths, and inspect the review overlays.

  Expected: PASS with complete public sections, working slider/CTA, and no overflow.

- [ ] **Step 7: Commit**

  ```bash
  git add wordpress/wp-content/themes/thatbiotutor-hello-child tests/resource-pages-test.php tests/resource-responsive-contract-test.php tests/runtime-browser-test.js
  git commit -m "feat: reproduce the original resource pages"
  ```

### Task 7: About, Contact, Our Causes, and Tuition Recommendations

**Files:**
- Create: `wordpress/wp-content/themes/thatbiotutor-hello-child/assets/css/pages-institutional.css`
- Modify: `wordpress/wp-content/themes/thatbiotutor-hello-child/page-about.php`
- Modify: `wordpress/wp-content/themes/thatbiotutor-hello-child/page-contact.php`
- Modify: `wordpress/wp-content/themes/thatbiotutor-hello-child/page-trees.php`
- Modify: `wordpress/wp-content/themes/thatbiotutor-hello-child/page-tuitionrecs.php`
- Modify: `wordpress/wp-content/themes/thatbiotutor-hello-child/inc/reference-content.php`
- Modify: `wordpress/wp-content/themes/thatbiotutor-hello-child/inc/acf-fields.php`
- Modify: `wordpress/wp-content/themes/thatbiotutor-hello-child/inc/theme-helpers.php`
- Modify: `wordpress/wp-content/themes/thatbiotutor-hello-child/assets/images/media-manifest.json`
- Modify: `wordpress/wp-content/themes/thatbiotutor-hello-child/assets/images/`
- Modify: `tests/institutional-pages-test.php`
- Modify: `tests/institutional-responsive-contract-test.php`
- Modify: `tests/runtime-pages-test.sh`

**Interfaces:**
- Consumes: shared shell, tutor renderer, shared contact values, and page field/media helpers.
- Produces: four faithful institutional routes and `pages-institutional.css`.

- [ ] **Step 1: Write failing per-route ordered-section tests**

  Assert every live heading, section, tutor/mission/cause/recommendation block, CTA destination, and public media key for all four routes. Assert Contact uses shared site-wide contact values rather than duplicated page values and blank editable fields do not restore fallback text.

- [ ] **Step 2: Write failing responsive contract assertions**

  Pin public hero treatments, title bars, reading widths, image sizes, surfaces, text alignment, list rhythm, CTA panels, and mobile order at all four reference widths.

- [ ] **Step 3: Run focused tests and verify failure**

  Run: `php tests/institutional-pages-test.php && php tests/institutional-responsive-contract-test.php && bash tests/runtime-pages-test.sh`

  Expected: FAIL because current templates use generalized institutional layouts and omit or rearrange public content.

- [ ] **Step 4: Reconstruct the four templates and editing fields**

  Follow the public page order and visual composition explicitly. Reuse only truly shared institutional primitives. Add current local WebP media and accurate alt text; preserve safe external-link handling for maps, donations, and recommendations.

- [ ] **Step 5: Implement and enqueue `pages-institutional.css`**

  Match the source geometry and responsive behavior without generic card substitutions.

- [ ] **Step 6: Run tests and inspect four-route comparisons**

  Run focused tests, capture `about`, `contact`, `trees`, and `tuitionrecs` at all widths, and inspect the overlays.

  Expected: PASS with no missing/reordered public content or overflow.

- [ ] **Step 7: Commit**

  ```bash
  git add wordpress/wp-content/themes/thatbiotutor-hello-child tests/institutional-pages-test.php tests/institutional-responsive-contract-test.php tests/runtime-pages-test.sh
  git commit -m "feat: reproduce institutional pages"
  ```

### Task 8: IntensiveFix integration and stylesheet cleanup

**Files:**
- Create: `wordpress/wp-content/themes/thatbiotutor-hello-child/assets/css/pages-intensivefix.css`
- Modify: `wordpress/wp-content/themes/thatbiotutor-hello-child/page-intensivefix.php`
- Modify: `wordpress/wp-content/themes/thatbiotutor-hello-child/assets/css/pages.css`
- Modify: `wordpress/wp-content/themes/thatbiotutor-hello-child/inc/theme-helpers.php`
- Modify: `tests/intensivefix-page-test.php`
- Modify: `tests/shared-shell-test.php`

**Interfaces:**
- Consumes: shared shell and programme visual vocabulary from Tasks 2 and 4.
- Produces: separate IntensiveFix styling with no selector collisions; `pages.css` contains only cross-page primitives while route-family files own their layouts.

- [ ] **Step 1: Write failing isolation tests**

  Assert IntensiveFix remains a separate route/navigation destination, never replaces Sec 3/4, loads its own stylesheet, and route-family CSS does not contain unscoped selectors that alter unrelated pages.

- [ ] **Step 2: Run focused tests and verify failure**

  Run: `php tests/intensivefix-page-test.php && php tests/shared-shell-test.php`

  Expected: FAIL because current IntensiveFix and original page families share broad `pages.css` rules.

- [ ] **Step 3: Move route-specific rules and align IntensiveFix**

  Keep only shared section/button/video primitives in `pages.css`; move page-family rules to their focused files. Use the Classes/Sec 3 vocabulary for IntensiveFix without claiming it is an original-site fidelity page.

- [ ] **Step 4: Run focused and complete static tests**

  Run: `php tests/intensivefix-page-test.php && php tests/shared-shell-test.php && scripts/verify-local.sh`

  Expected: PASS with no stylesheet regression on original routes.

- [ ] **Step 5: Commit**

  ```bash
  git add wordpress/wp-content/themes/thatbiotutor-hello-child tests/intensivefix-page-test.php tests/shared-shell-test.php
  git commit -m "refactor: isolate page-family presentation"
  ```

### Task 9: Whole-site responsive, editing, installation, and visual acceptance

**Files:**
- Modify: `tests/runtime-browser-test.js`
- Modify: `tests/runtime-editing-test.php`
- Modify: `tests/runtime-setup-test.sh`
- Modify: `tests/site-setup-test.php`
- Modify: `scripts/verify-local.sh`
- Modify: `README.md`
- Modify: `docs/CLIENT-EDITING-GUIDE.md`
- Modify: `CHANGELOG.md`

**Interfaces:**
- Consumes: all route templates, focused stylesheets, content/media seed data, capture tooling, and shared interactions from Tasks 1–8.
- Produces: final repeatable acceptance suite and client documentation for the visually faithful WordPress build.

- [ ] **Step 1: Add failing whole-site regression cases**

  For every original route and each width `1440`, `768`, `390`, and `320`, assert HTTP success, `scrollWidth <= innerWidth`, visible header/main/footer, valid image sources, and no browser errors. Add edited long headings/testimonial copy/links and missing optional media cases at `320`; assert blank ACF fields remain blank; assert install/setup remains idempotent and all required seeded records/media keys exist.

- [ ] **Step 2: Run the complete suite and record failures**

  Run: `scripts/verify-local.sh && npm run test:runtime`

  Expected: FAIL only on newly added whole-site cases, if any.

- [ ] **Step 3: Fix only acceptance regressions**

  Make the smallest template, CSS, seed, or interaction corrections needed. Do not introduce new layouts or content during acceptance.

- [ ] **Step 4: Capture and inspect the complete visual matrix**

  Run: `node scripts/capture-visual-reference.js --reference-base=https://www.thatbiotutor.com --local-base=http://localhost:8888 --output=.artifacts/visual/final --freeze`

  Run: `node scripts/build-visual-review.js .artifacts/visual/final`

  Inspect all 44 route/viewport pairs. Record any intentional exclusions for dynamic YouTube pixels only; correct every visible structural, typography-scale, color, spacing, alignment, crop, and shell mismatch before continuing.

- [ ] **Step 5: Run final verification twice**

  Run: `scripts/verify-local.sh && npm run test:runtime`

  Run the same command a second time without cleaning the environment.

  Expected: both runs PASS, proving clean setup plus idempotent steady state.

- [ ] **Step 6: Update documentation and changelog**

  Document the current public-site fidelity boundary, ACF editing ownership, focused stylesheet structure, visual capture command, WebP policy, font-licensing fallback, and IntensiveFix exception. Add the fidelity rebuild to `CHANGELOG.md`.

- [ ] **Step 7: Commit**

  ```bash
  git add tests scripts/verify-local.sh README.md docs/CLIENT-EDITING-GUIDE.md CHANGELOG.md wordpress/wp-content/themes/thatbiotutor-hello-child
  git commit -m "test: verify live-site visual fidelity"
  ```

## Self-review record

- Spec coverage: every original route, shared shell, editing boundary, WebP policy, font constraint, IntensiveFix exception, responsive behavior, and verification requirement maps to Tasks 1–9.
- Step scan: every task follows failing test, verified failure, focused implementation, verified pass, visual inspection where applicable, and commit.
- Interface consistency: Tasks 3 and 6 share the same `data-carousel*` contract; all page families consume the Task 2 shell and Task 1 capture matrix.
- Review Focus coverage: capture failures/freeze are in Task 1, navigation resize in Task 2, blank/missing content in Tasks 3–7, and long-content narrow-screen overflow in Task 9.
- Proportion: the plan specifies decisions, files, assertions, commands, and interfaces without transcribing template or stylesheet bodies.
