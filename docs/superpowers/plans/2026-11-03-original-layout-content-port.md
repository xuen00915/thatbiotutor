# Original Layout Content Port Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Replace the incorrect Inquire-styled static site with a client-editable recreation of the original ThatBioTutor layouts, while applying only the approved matching homepage content and adding a separate IntensiveFix page.

**Architecture:** Continue with the existing Python static generator, semantic page fragments, shared HTML partials, CSS custom properties, and dependency-free JavaScript. The original live/archive site supplies every existing page's section order and visual composition; the Inquire sources supply only approved replacement content and the separate IntensiveFix programme content. Local WebP media, a route manifest, build validation, and GitHub Pages remain the delivery model.

**Tech Stack:** HTML5, CSS custom properties, vanilla JavaScript, Python 3 standard library, `unittest`, Git, GitHub Actions, GitHub Pages

**Spec:** `docs/superpowers/specs/2026-11-03-original-layout-content-port-design.md`

## Global Constraints

- Execute in an isolated worktree based on the latest `origin/main`, which contains the existing static generator and modified site sources.
- Do not edit `/Users/dmsyx/Documents/Projects/thatbiotutor-original`.
- Do not modify `mirror/www.thatbiotutor.com/`; it is a read-only original reference.
- The original homepage section inventory and order are authoritative.
- Inquire homepage content may replace only matching original sections; do not import the Inquire page map or visual design.
- Keep `/sec-3-pure-g3-biology-tuition/` distinct from the separate canonical `/intensivefix/` page.
- Preserve `/s3-intensivefix/` and `/classes/sec-3-pure-g3-biology-tuition/` as aliases.
- Use shared templates for the header, footer, and floating WhatsApp control.
- Keep WhatsApp number `6587597182` in shared configuration, not duplicated in page JavaScript.
- Every local raster website image emitted by the build must be WebP and listed in the asset manifest.
- Keep routine copy, media order, FAQs, statistics, CTA labels/links, and video URLs easy for a non-technical client to find.
- Do not introduce a frontend framework, CMS runtime, WordPress runtime, Elementor-generated markup, or npm production dependency.
- All navigation and assets must work both at a domain root and at the GitHub Pages repository subpath `/thatbiotutor/`.
- Honour `prefers-reduced-motion`; essential content and contact links must work without JavaScript.
- Do not change Squarespace, DNS, or the `thatbiotutor.com` production domain.

## File Structure

- `site/site.json`: shared site identity, contact details, navigation, footer links, and global statistics.
- `site/pages.json`: canonical routes, aliases, page metadata, and small per-page template context.
- `site/templates/base.html`: semantic document shell and shared asset loading.
- `site/partials/header.html`: one original-style site header and navigation implementation.
- `site/partials/footer.html`: one original-style footer implementation.
- `site/partials/floating-whatsapp.html`: one accessible floating contact control.
- `site/partials/sections/*.html`: repeated original-site content patterns only.
- `site/pages/*.html`: one readable, client-editable fragment per canonical page.
- `site/assets/css/base.css`: measured original-site tokens, reset, typography, and layout primitives.
- `site/assets/css/components.css`: shared original-style header, section bar, CTA, media, FAQ, carousel, counter, and footer components.
- `site/assets/css/pages.css`: page-specific composition and responsive rules.
- `site/assets/js/content.js`: ordered supplied media data only.
- `site/assets/js/site.js`: progressive enhancement for navigation, carousels, counters, and FAQs.
- `site/assets/images/asset-manifest.json`: local media provenance, dimensions, alt text, order, fit, and focal position.
- `tests/test_site_builder.py`: generator, route, alias, path, manifest, and failure-safety tests.
- `tests/test_static_site.py`: rendered content, section-order, shared-template, asset, accessibility, and page-specific tests.
- `docs/client-editing-guide.md`: routine static-site editing instructions.
- `docs/elementor-migration-map.md`: mapping from static components to Elementor templates/widgets.

## Review Focus

- Homepage drift: the rendered section slots must exactly follow the original homepage order and must not contain Inquire-only `suitability`, standalone `founder`, or appended `classroom` sections.
- Route confusion: `/intensivefix/` must be canonical, `/s3-intensivefix/` must redirect to it, and the Sec 3 tuition page must keep its own content and canonical URL.
- Repository-subpath breakage: every nested route, alias, local asset, and internal link must work under `/thatbiotutor/` as well as at `/`.
- Media damage: supplied portraits, classroom context, and text-heavy IntensiveFix graphics must remain readable at 390 and 1440 CSS pixels without excessive zoom or horizontal overflow.
- Reference corruption: both the local Wix mirror and the separate preserved original repository must remain unchanged through the implementation and build.

---

### Task 1: Canonical IntensiveFix route and route-contract tests

**Files:**
- Modify: `site/pages.json`
- Modify: `site/site.json`
- Rename: `site/pages/s3-intensivefix.html` to `site/pages/intensivefix.html`
- Modify: `tests/test_site_builder.py`
- Modify: `tests/test_static_site.py`

**Interfaces:**
- Consumes: existing `PageSpec`, route normalisation, alias generation, and navigation rendering.
- Produces: canonical route `intensivefix/`, alias `s3-intensivefix/`, page key `intensivefix`, and unchanged Sec 3 canonical route.

- [ ] **Step 1: Write the failing route-contract tests**

Add `RouteContractTests` asserting:

```python
self.assertEqual(PAGE_CONFIG["intensivefix"]["route"], "intensivefix/")
self.assertIn("s3-intensivefix/", PAGE_CONFIG["intensivefix"]["aliases"])
self.assertEqual(PAGE_CONFIG["sec-3-pure-g3-biology-tuition"]["route"], "sec-3-pure-g3-biology-tuition/")
self.assertNotEqual(PAGE_CONFIG["intensivefix"]["source"], PAGE_CONFIG["sec-3-pure-g3-biology-tuition"]["source"])
```

Extend the alias build test to assert the generated `/s3-intensivefix/index.html` canonical target and relative continuation link both point to `/intensivefix/`.

- [ ] **Step 2: Run the focused tests and verify failure**

Run: `python3 -m unittest tests.test_site_builder tests.test_static_site.RouteContractTests -v`

Expected: FAIL because `s3-intensivefix/` is still canonical and the key/source still use the old name.

- [ ] **Step 3: Rename the page and update route/navigation configuration**

Change the page entry to key `intensivefix`, route `intensivefix/`, source `pages/intensivefix.html`, and alias `s3-intensivefix/`. Update header/footer navigation routes without changing the Sec 3 tuition entry.

- [ ] **Step 4: Build and run the route tests**

Run:

```bash
python3 -m unittest tests.test_site_builder tests.test_static_site.RouteContractTests -v
python3 scripts/build_site.py --output build
```

Expected: all tests PASS; both canonical and alias files exist; only `/intensivefix/` appears in the sitemap.

- [ ] **Step 5: Commit**

```bash
git add site/pages.json site/site.json site/pages/intensivefix.html tests/test_site_builder.py tests/test_static_site.py
git add -u site/pages/s3-intensivefix.html
git commit -m "refactor: separate canonical IntensiveFix route"
```

### Task 2: Original-site shared shell and design system

**Files:**
- Modify: `site/templates/base.html`
- Modify: `site/partials/header.html`
- Modify: `site/partials/footer.html`
- Modify: `site/partials/floating-whatsapp.html`
- Modify: `site/assets/css/base.css`
- Modify: `site/assets/css/components.css`
- Modify: `site/assets/css/pages.css`
- Modify: `site/assets/js/site.js`
- Modify: `tests/test_static_site.py`

**Interfaces:**
- Consumes: `site.json` navigation/contact values and each page's `bodyClass`/`navKey`.
- Produces: `.site-header`, `.site-nav`, `.section-bar`, `.original-hero`, `.compact-cta`, `.original-footer`, `.floating-whatsapp`, and `initNavigation(root = document)`.

- [ ] **Step 1: Write failing shared-shell tests**

Add `OriginalShellTests` asserting that every canonical rendered page contains exactly one shared header, footer, and floating WhatsApp control; the rightmost header action reads `Contact Us`; IntensiveFix is discoverable; and CSS defines original-layout primitives rather than Inquire card-shell primitives.

Also assert `site.js` exposes `initNavigation`, preserves existing `initCarousels`, `initCounters`, and `initFaqs`, and listens for reduced motion.

- [ ] **Step 2: Run the shell tests and verify failure**

Run: `python3 -m unittest tests.test_static_site.OriginalShellTests tests.test_static_site.StaticSiteFoundationTests -v`

Expected: FAIL on original-shell classes, header action placement, and missing `initNavigation`.

- [ ] **Step 3: Implement the shared original-site shell**

Measure the live/archive reference and replace the current Inquire-derived tokens and components with the original compact white header, constrained content width, charcoal section bars, selective bright-green accents, compact buttons, narrow media blocks, and original-style footer. Keep mobile navigation keyboard operable and progressively enhanced.

- [ ] **Step 4: Verify the shared shell**

Run:

```bash
python3 -m unittest tests.test_static_site.OriginalShellTests tests.test_static_site.StaticSiteFoundationTests -v
node --check site/assets/js/site.js
python3 scripts/build_site.py --output build
```

Expected: PASS with no unresolved placeholders, missing assets, or broken internal links.

- [ ] **Step 5: Commit**

```bash
git add site/templates site/partials/header.html site/partials/footer.html site/partials/floating-whatsapp.html site/assets/css site/assets/js/site.js tests/test_static_site.py
git commit -m "feat: recreate original shared site shell"
```

### Task 3: Homepage original order with matching content replacements

**Files:**
- Modify: `site/pages/home.html`
- Modify: `site/partials/sections/fasttrack.html`
- Modify: `site/partials/sections/track-record.html`
- Modify: `site/partials/sections/testimonial-cards.html`
- Modify: `site/assets/css/pages.css`
- Modify: `tests/test_static_site.py`

**Interfaces:**
- Consumes: `homeHero`, `trackRecord`, and `homeClassroom` manifest groups; shared carousel/counter/FAQ enhancements; original shell components.
- Produces: ordered homepage layout slots and approved `$95` WhatsApp closing guarantee.

- [ ] **Step 1: Replace the old homepage expectations with failing original-order tests**

Assert the exact ordered `data-layout-slot` sequence:

```python
[
    "hero", "missing-key", "fasttrack", "track-record", "hall-of-fame",
    "student-voices", "schools", "advantage", "student-benefits",
    "experience", "causes", "tutors", "faq", "closing-cta",
]
```

Assert the absence of Inquire-only top-level slots `suitability`, `founder`, and `classroom`. Assert the five `homeHero` images are in numeric order inside the original hero frame, the four `trackRecord` images are in the original results region, and the three `homeClassroom` images are in the `experience` slot with the teaching image first.

Keep exact tests for the `$95` refund promise and `Book My Trial Lesson` WhatsApp CTA.

- [ ] **Step 2: Run homepage tests and verify failure**

Run: `python3 -m unittest tests.test_static_site.HomePageTests -v`

Expected: FAIL because the current homepage follows the Inquire page map and uses separate suitability/founder/classroom sections.

- [ ] **Step 3: Rebuild the homepage fragment in the original order**

Use the original homepage as the section and layout baseline. Map approved Inquire copy/media only into matching slots, implement the five-image background hero slideshow without changing hero geometry, place classroom media in `experience`, and replace only the closing conversion treatment with the shield/refund/WhatsApp guarantee.

- [ ] **Step 4: Test and visually verify the homepage**

Run:

```bash
python3 -m unittest tests.test_static_site.HomePageTests tests.test_static_site.StaticSiteFoundationTests -v
python3 scripts/build_site.py --output build
```

Serve `build/` and compare `/` with the original at 390, 768, and 1440 CSS pixels. Expected: original section order and proportions, no Inquire card layout, readable supplied media, no overflow.

- [ ] **Step 5: Commit**

```bash
git add site/pages/home.html site/partials/sections site/assets/css/pages.css tests/test_static_site.py
git commit -m "feat: restore original homepage layout"
```

### Task 4: Original Classes overview page

**Files:**
- Modify: `site/pages/classes.html`
- Modify: `site/partials/sections/class-results.html`
- Modify: `site/assets/css/pages.css`
- Modify: `tests/test_static_site.py`

**Interfaces:**
- Consumes: original shell, shared results pattern, and existing local Classes media.
- Produces: original ordered slots `hero`, `pure-biology`, `struggles`, `solutions`, `class-difference`, `results`, `level-choice`, and `faq`.

- [ ] **Step 1: Write failing Classes page contract tests**

Assert the ordered slot list above, the original main heading `The AVERAGE TBT Student Makes a 2.3 Grade Jump (~C5 to A2)`, both Sec 3 and Sec 4 links, key result values, one `h1`, and no Inquire-style card-shell wrapper.

- [ ] **Step 2: Run the Classes tests and verify failure**

Run: `python3 -m unittest tests.test_static_site.ClassesPageTests -v`

Expected: FAIL on section order/layout markers and original heading/copy.

- [ ] **Step 3: Rebuild the Classes page from the original reference**

Restore the original section sequence, copy, media relationships, dark section dividers, compact result presentation, and level-choice treatment while retaining semantic HTML and local assets.

- [ ] **Step 4: Test and compare at mobile and desktop widths**

Run: `python3 -m unittest tests.test_static_site.ClassesPageTests -v && python3 scripts/build_site.py --output build`

Expected: PASS; visual comparison at 390 and 1440 pixels matches the original page's hierarchy and stacking.

- [ ] **Step 5: Commit**

```bash
git add site/pages/classes.html site/partials/sections/class-results.html site/assets/css/pages.css tests/test_static_site.py
git commit -m "feat: restore original Classes page"
```

### Task 5: Original Sec 3 and Sec 4 tuition pages

**Files:**
- Modify: `site/pages/sec-3-pure-g3-biology-tuition.html`
- Modify: `site/pages/sec-4-pure-g3-biology-tuition.html`
- Modify: `site/partials/sections/class-resources.html`
- Modify: `site/partials/sections/class-results.html`
- Modify: `site/partials/sections/tutor-team.html`
- Modify: `site/assets/css/pages.css`
- Modify: `tests/test_static_site.py`

**Interfaces:**
- Consumes: shared class resources/results/tutor components and original shell.
- Produces: matching ordered tuition-page slots `hero`, `learning-modes`, `curriculum`, `results`, `track-record`, `student-voices`, `lesson`, `resources`, `team`, and `faq` for each level.

- [ ] **Step 1: Write failing level-page contract tests**

For both pages, assert the ordered slots, exact original hero heading, correct Sec 3/Sec 4 curriculum phases, key result values, resource titles, team names, one `h1`, and distinct canonical content. Assert the Sec 3 source contains no IntensiveFix hero or holiday-program CTA.

- [ ] **Step 2: Run the level-page tests and verify failure**

Run: `python3 -m unittest tests.test_static_site.TuitionPageTests -v`

Expected: FAIL on original copy/order markers and page-specific content.

- [ ] **Step 3: Restore both tuition pages and shared sections**

Recreate the original Sec 3 and Sec 4 page compositions, retaining their level-specific copy, section order, resources, tutors, and FAQs. Use shared partials only where the original layouts genuinely repeat.

- [ ] **Step 4: Test and visually verify both pages**

Run: `python3 -m unittest tests.test_static_site.TuitionPageTests -v && python3 scripts/build_site.py --output build`

Compare both routes at 390 and 1440 pixels. Expected: PASS, correct page distinction, no overflow, and no IntensiveFix content leakage.

- [ ] **Step 5: Commit**

```bash
git add site/pages/sec-3-pure-g3-biology-tuition.html site/pages/sec-4-pure-g3-biology-tuition.html site/partials/sections/class-resources.html site/partials/sections/class-results.html site/partials/sections/tutor-team.html site/assets/css/pages.css tests/test_static_site.py
git commit -m "feat: restore original tuition pages"
```

### Task 6: Original institutional and utility pages

**Files:**
- Modify: `site/pages/about.html`
- Modify: `site/pages/contact.html`
- Modify: `site/pages/trees.html`
- Modify: `site/pages/tuitionrecs.html`
- Modify: `site/assets/css/pages.css`
- Modify: `tests/test_static_site.py`

**Interfaces:**
- Consumes: original shell, tutor component, local About/Causes media, and shared WhatsApp configuration.
- Produces: faithful About, Contact, Causes, and Tuition Recommendations pages.

- [ ] **Step 1: Write failing page-specific contract tests**

Assert ordered About slots `hero`, `mission`, `quality`, `tutors`, `founder-message`, `beliefs`; Contact's original heading and FAQ-first WhatsApp instruction; Trees headings `1. Planting Trees!` and `2. SG Environmental Causes`; and the original Tuition Recommendations heading/list structure.

Also assert all WhatsApp links use the configured number and no page duplicates header/footer markup.

- [ ] **Step 2: Run the institutional-page tests and verify failure**

Run: `python3 -m unittest tests.test_static_site.InstitutionalPageTests -v`

Expected: FAIL on original section/order markers and original copy requirements.

- [ ] **Step 3: Restore the four pages from the original references**

Keep the pages intentionally simple where the originals are simple. Preserve original content and sequence, use local WebP media, and avoid adding new card grids or promotional sections.

- [ ] **Step 4: Test and visually inspect the four routes**

Run: `python3 -m unittest tests.test_static_site.InstitutionalPageTests -v && python3 scripts/build_site.py --output build`

Expected: PASS at 390 and 1440 pixels with original hierarchy and shared shell.

- [ ] **Step 5: Commit**

```bash
git add site/pages/about.html site/pages/contact.html site/pages/trees.html site/pages/tuitionrecs.html site/assets/css/pages.css tests/test_static_site.py
git commit -m "feat: restore original institutional pages"
```

### Task 7: Original results and resource pages

**Files:**
- Modify: `site/pages/testimonials.html`
- Modify: `site/pages/notes.html`
- Modify: `site/pages/celldivision.html`
- Modify: `site/partials/sections/testimonial-cards.html`
- Modify: `site/assets/css/pages.css`
- Modify: `tests/test_static_site.py`

**Interfaces:**
- Consumes: original shell, local testimonial/notes/cell-division media, and video embed component.
- Produces: faithful Testimonials, Notes, and archived Cell Division pages.

- [ ] **Step 1: Write failing resource-page contract tests**

Assert Testimonials contains the original `Results & Testimonials`, star rating, student-stories groups, and 2022/2021 sections in order; Notes contains its original heading plus `why free`, `catch`, and `download` sections; Cell Division retains `Only For 2023 Sec 4 Pure Bio Students!`, `IMPORTANT:`, access, and student-review sections and is clearly archived.

- [ ] **Step 2: Run the resource-page tests and verify failure**

Run: `python3 -m unittest tests.test_static_site.ResourcePageTests -v`

Expected: FAIL on original section/order markers and copy.

- [ ] **Step 3: Restore the three page fragments**

Recreate original media ordering and section layouts with semantic headings, local posters/images, responsive embeds, and no remote Wix runtime dependency.

- [ ] **Step 4: Test and visually inspect the routes**

Run: `python3 -m unittest tests.test_static_site.ResourcePageTests -v && python3 scripts/build_site.py --output build`

Expected: PASS; media remains contained and pages preserve the original narrow centred composition.

- [ ] **Step 5: Commit**

```bash
git add site/pages/testimonials.html site/pages/notes.html site/pages/celldivision.html site/partials/sections/testimonial-cards.html site/assets/css/pages.css tests/test_static_site.py
git commit -m "feat: restore original results and resources pages"
```

### Task 8: Separate IntensiveFix page in the original visual language

**Files:**
- Modify: `site/pages/intensivefix.html`
- Modify: `site/pages.json`
- Modify: `site/partials/sections/fasttrack.html`
- Modify: `site/partials/sections/track-record.html`
- Modify: `site/assets/css/pages.css`
- Modify: `tests/test_static_site.py`

**Interfaces:**
- Consumes: `intensiveFixCarousel`, `intensiveFixReview`, and shared `trackRecord` media; shared carousel/counter components; original Sec 3/Classes layout patterns.
- Produces: complete `/intensivefix/` page and compatible `/s3-intensivefix/` alias.

- [ ] **Step 1: Write failing IntensiveFix content and omission tests**

Assert one `h1`; original-style slots `hero`, `programme-media`, `student-proof`, `track-record`, `analysis-video`, `fasttrack`, `suitability`, and `closing-cta`; approved limited-time subtitle; `Fix My Sec 3 Foundation`; ten ordered programme graphics; one supplied review graphic; exact `Sound familiar?` and FastTrack heading changes; and the supplied EOY closing copy.

Assert the unrelated three testimonial cards and intervening post-FastTrack sections are absent. Assert text-heavy programme graphics use the manifest's `contain` fit.

- [ ] **Step 2: Run IntensiveFix tests and verify failure**

Run: `python3 -m unittest tests.test_static_site.IntensiveFixPageTests -v`

Expected: FAIL because the current page uses the old route key and incorrect green Inquire layout composition.

- [ ] **Step 3: Rebuild IntensiveFix using original-site patterns**

Use the original Sec 3 full-width hero, charcoal section bars, white narrow media sections, compact green CTAs, original statistics band, and simple suitability/closing layouts. Apply all DOCX copy, ordering, removals, and spacing instructions without changing the Sec 3 tuition page.

- [ ] **Step 4: Test and visually verify the programme page**

Run:

```bash
python3 -m unittest tests.test_static_site.IntensiveFixPageTests tests.test_static_site.RouteContractTests -v
python3 scripts/build_site.py --output build
```

Compare `/intensivefix/` at 390, 768, and 1440 pixels. Expected: original ThatBioTutor visual language, readable portrait graphics, working swipe/keyboard controls, and no horizontal overflow.

- [ ] **Step 5: Commit**

```bash
git add site/pages/intensivefix.html site/pages.json site/partials/sections/fasttrack.html site/partials/sections/track-record.html site/assets/css/pages.css tests/test_static_site.py
git commit -m "feat: rebuild IntensiveFix in original style"
```

### Task 9: WebP manifest, client editing guide, and Elementor mapping

**Files:**
- Modify: `site/assets/images/asset-manifest.json`
- Modify: `tests/test_static_site.py`
- Modify: `README.md`
- Modify: `docs/client-editing-guide.md`
- Create: `docs/elementor-migration-map.md`

**Interfaces:**
- Consumes: final page fragments, shared components, routes, and local media.
- Produces: manifest entries with `source`, `file`, `alt`, `width`, `height`, `fit`, and `objectPosition`; client editing instructions; Elementor component map.

- [ ] **Step 1: Write failing manifest and documentation tests**

Update asset tests to require the seven fields above, verify every emitted local raster is `.webp`, ensure every page image is manifested, and assert `fit` is one of `cover` or `contain` with a non-empty focal position.

Add documentation tests that require instructions for editing shared contact details, navigation, page copy, FAQs, statistics, carousel order, video URLs, image focal positions, building, previewing, and mapping shared templates to Elementor Theme Builder.

- [ ] **Step 2: Run the asset/documentation tests and verify failure**

Run: `python3 -m unittest tests.test_static_site.StaticSiteAssetTests tests.test_static_site.ClientDocumentationTests -v`

Expected: FAIL because manifest crop metadata and the Elementor map are incomplete.

- [ ] **Step 3: Complete media metadata and client documentation**

Add crop/fit metadata for every image without changing the authoritative supplied order. Document the exact editable files and map the header, footer, floating WhatsApp control, section bar, counter, carousel, FAQ, and page sections to Elementor equivalents.

- [ ] **Step 4: Run tests and build from clean source**

Run:

```bash
python3 -m unittest tests.test_static_site.StaticSiteAssetTests tests.test_static_site.ClientDocumentationTests -v
python3 scripts/build_site.py --output build
```

Expected: PASS; the build contains no non-WebP raster files and no unmanifested page images.

- [ ] **Step 5: Commit**

```bash
git add site/assets/images/asset-manifest.json tests/test_static_site.py README.md docs/client-editing-guide.md docs/elementor-migration-map.md
git commit -m "docs: complete client and Elementor handoff"
```

### Task 10: Full verification, review, and GitHub Pages deployment

**Files:**
- Modify only if verification finds defects: affected `site/`, `tests/`, or documentation files
- Regenerate: `build/`

**Interfaces:**
- Consumes: all canonical pages, aliases, tests, assets, docs, and GitHub Pages workflow.
- Produces: reviewed branch, merged GitHub change, and verified Pages deployment at `https://xuen00915.github.io/thatbiotutor/`.

- [ ] **Step 1: Run the complete automated suite and production build**

Run:

```bash
python3 -m unittest discover -s tests -v
node --check site/assets/js/content.js
node --check site/assets/js/site.js
python3 scripts/build_site.py --output build
git diff --exit-code -- mirror/www.thatbiotutor.com
git -C /Users/dmsyx/Documents/Projects/thatbiotutor-original status --short
```

Expected: all tests PASS, JavaScript checks exit 0, build succeeds, local mirror diff is empty, and preserved original status is empty.

- [ ] **Step 2: Perform desktop/mobile visual and interaction QA**

Serve `build/` and inspect every canonical route at 390 and 1440 CSS pixels; inspect homepage and IntensiveFix additionally at 768 and 1280. Compare existing pages side-by-side with live/archive originals. Verify layout slots, crop/focal position, no overflow, shared shell, keyboard navigation, focus, FAQs, carousels, counters, reduced motion, no-JavaScript fallbacks, and WhatsApp destinations.

- [ ] **Step 3: Verify repository-subpath behaviour**

Serve the build beneath a `/thatbiotutor/` prefix or use the GitHub Pages preview and crawl every canonical route, alias, internal link, stylesheet, script, and image. Expected: no 404 responses and aliases resolve to their canonical pages.

- [ ] **Step 4: Request final branch review and fix findings**

Review against the approved spec with special attention to homepage section order, original-vs-Inquire visual identity, separate Sec 3/IntensiveFix content, shared templates, WebP compliance, accessibility, and protected originals. Re-run Steps 1–3 after any fix.

- [ ] **Step 5: Commit verification fixes, push, and deploy**

```bash
git add site tests docs README.md build
git commit -m "fix: complete original-layout release verification"
git push -u origin feature/original-layout-content-port
```

Open and merge the pull request after its checks pass. Watch the existing Pages workflow to success; do not change repository visibility, DNS, Squarespace, or the custom domain.

- [ ] **Step 6: Verify the live GitHub Pages deployment**

Check `https://xuen00915.github.io/thatbiotutor/` and every canonical/alias route for HTTP success, local assets, correct canonical URLs, original-style desktop/mobile rendering, and working WhatsApp links. Record the deployed commit SHA and deployment URL in the handoff.

## Self-Review Results

- Spec coverage: every design section maps to Tasks 1–10; protected originals and no-DNS constraints are explicitly verified in Task 10.
- Step scan: each task has one failing-test action, one failure verification, one implementation action, one passing verification, and one commit action.
- Interface consistency: `intensivefix` is the canonical key/route throughout; supplied manifest group names remain unchanged; shared enhancement names match existing JavaScript interfaces.
- Review Focus coverage: homepage order is pinned in Task 3; route separation in Tasks 1 and 8; subpath behaviour in Tasks 1 and 10; media fit in Tasks 8–10; protected originals in Task 10.
- Proportion: implementation bodies are left to the executor; the plan records only file ownership, exact contracts, acceptance tests, commands, and fixed content decisions.
