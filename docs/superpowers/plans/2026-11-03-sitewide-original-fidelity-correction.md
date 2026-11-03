# Sitewide Original Fidelity Correction Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:executing-plans. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Correct every modified route so its rendered design follows the live/original ThatBioTutor site while retaining only the approved inquiry-content substitutions.

**Architecture:** Keep the editable static generator, page fragments, shared header/footer/WhatsApp partials, and local WebP media. Add browser-rendered contracts for measurable shell/layout behavior, replace the custom marketing design system with the original Wix-era visual vocabulary, and restore the original page depth and section flow from the local mirror/live references.

**Tech Stack:** HTML5, CSS, vanilla JavaScript, Python static generator, Python `unittest`, Playwright/Chromium, GitHub Pages

**Spec:** `docs/superpowers/specs/2026-11-03-original-layout-content-port-design.md`

## Global Constraints

- Work only in this modified worktree; do not edit the preserved original repository or `mirror/www.thatbiotutor.com/`.
- The live and mirrored `thatbiotutor.com` pages control layout, typography, spacing, section order, and image treatment.
- Inquiry content may replace only the approved matching content.
- Keep the shared header, footer, floating WhatsApp control, local WebP assets, simple page fragments, and later Elementor mapping.
- Validate at 390, 768, 1280, and 1440 CSS pixels.

## Review Focus

- Compact custom header must be replaced by the original tall segmented navigation on desktop and an original-style mobile header.
- Dark split heroes, soft green card grids, and modern rounded marketing sections must not remain on original routes.
- Notes, Testimonials, tuition, About, and utility pages must no longer be abbreviated compared with their original counterparts.
- Homepage substitutions must remain in the original homepage positions and visual patterns.
- All local raster assets remain WebP and all routes/aliases continue to build under the GitHub Pages subpath.

---

### Task 1: Rendered visual contracts and complete route audit

**Files:**
- Create: `tests/rendered_visual_contract.spec.js`
- Create: `docs/original-route-audit.md`

- [ ] Add browser tests for desktop header geometry, segmented navigation, original typography, light page background, hero treatment, and absence of the current dark split Notes hero.
- [ ] Run the tests and confirm they fail against the current build for the expected visual mismatch.
- [ ] Record all original/current route heights, section order, and missing content in the route audit.
- [ ] Commit the red contracts and audit.

### Task 2: Shared original shell and design vocabulary

**Files:**
- Modify: `site/partials/header.html`
- Modify: `site/partials/footer.html`
- Modify: `site/partials/floating-whatsapp.html`
- Modify: `site/site.json`
- Modify: `site/assets/css/base.css`
- Modify: `site/assets/css/components.css`
- Modify: `site/assets/css/pages.css`

- [ ] Implement the original desktop header proportions, logo, segmented navigation, Futura/Avenir typography, restrained green accent, original buttons, dark title bars, white backgrounds, and simple footer.
- [ ] Rebuild and run rendered contracts until the shared-shell tests pass at desktop and mobile widths.
- [ ] Run the complete Python suite and commit the shell correction.

### Task 3: Restore every page composition

**Files:**
- Modify: `site/pages/*.html`
- Modify as needed: `site/partials/sections/*.html`
- Modify: `site/assets/css/pages.css`
- Modify: `tests/test_static_site.py`

- [ ] Add failing route-level tests for original section order and required long-form content on all canonical pages.
- [ ] Restore the homepage, Classes, Sec 3, Sec 4, Testimonials, Notes, Cell Division, About, Causes, Tuition Recommendations, Contact, and IntensiveFix compositions using the original page vocabulary.
- [ ] Keep the approved inquiry content substitutions only where the specification maps them.
- [ ] Rebuild, run all route tests, and compare every route at 390 and 1440 pixels.
- [ ] Commit the page restoration.

### Task 4: Full release verification and deployment

**Files:**
- Modify only if verification finds defects.

- [ ] Run the full Python suite, JavaScript syntax checks, production build, protected-original checks, and rendered browser suite.
- [ ] Capture final full-page screenshots for every canonical route at 390 and 1440 pixels and compare them with the captured originals.
- [ ] Verify keyboard navigation, FAQ/carousel behavior, no horizontal overflow, local WebP assets, links, aliases, and GitHub Pages subpath behavior.
- [ ] Complete a fresh whole-branch review and fix Critical/Important findings through RED-to-GREEN tests.
- [ ] Push the corrected branch and verify the GitHub Pages deployment without touching Squarespace or DNS.

