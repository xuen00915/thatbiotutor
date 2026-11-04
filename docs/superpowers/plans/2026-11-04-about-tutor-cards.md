# About Tutor Cards Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Replace the overly tall About tutor profiles with compact portrait-left, details-right cards that stack cleanly on mobile.

**Architecture:** Extend only the `about` variant of the shared PHP tutor renderer with explicit visual and details wrappers. Style those wrappers using CSS Grid on desktop and a single-column flow at mobile widths, while leaving all other tutor variants unchanged.

**Tech Stack:** WordPress PHP templates, semantic HTML, vanilla CSS, existing PHP contract tests and Playwright runtime checks.

**Spec:** `docs/superpowers/specs/2026-11-04-about-tutor-cards-design.md`

## Global Constraints

- Preserve every tutor name, qualification, profile link, portrait, and certificate.
- Keep the existing Montserrat typography and green, charcoal, and white palette.
- Add no dependency and do not change tutor layouts outside the About page.
- Stack the card to one column at `max-width: 768px`.

## Review Focus

- Tutors without certificates still align correctly and do not leave an empty visual gap.
- Tutors with multiple certificates keep both images visible without expanding to the old fixed heights.
- Long qualifications wrap within the details column without horizontal overflow.
- Direct portrait URLs and fallback media both receive the About portrait class and meaningful alt text.
- The 768px, 390px, and 320px layouts preserve readable ordering: name, portrait, qualifications, link, credentials.

---

### Task 1: Compact About tutor cards

**Files:**
- Modify: `wordpress/wp-content/themes/thatbiotutor-hello-child/inc/tutors.php`
- Modify: `wordpress/wp-content/themes/thatbiotutor-hello-child/assets/css/pages-institutional.css`
- Modify: `tests/tutors-test.php`
- Modify: `tests/institutional-responsive-contract-test.php`

**Interfaces:**
- Consumes: `tbt_render_tutors(array $args = []): void` with `variant => about`.
- Produces: `.tbt-tutor__visual` and `.tbt-tutor__details` wrappers only for the About variant.

- [ ] **Step 1: Add failing renderer and responsive contract assertions**

Assert that the About renderer outputs the two wrappers, direct portraits receive `tbt-tutor__portrait`, the CSS contains a two-column card grid, no fixed `nth-child` heights remain, and the 768px media query switches the About card to one column.

- [ ] **Step 2: Run focused tests and verify failure**

Run: `php tests/tutors-test.php && php tests/institutional-responsive-contract-test.php`

Expected: FAIL because the wrappers and compact grid contracts do not exist yet.

- [ ] **Step 3: Implement About-only semantic wrappers**

Update `tbt_render_tutors()` so the About variant groups portrait plus supplemental documents in `.tbt-tutor__visual`, and name plus qualifications/profile link in `.tbt-tutor__details`. Preserve the existing output path for other variants.

- [ ] **Step 4: Replace the fixed-height CSS with a responsive card grid**

Use a constrained two-column grid with a roughly `280px / 1fr` split, compact padding and gaps, left-aligned headings, responsive certificate thumbnails, and a single-column mobile layout. Do not hide or truncate content.

- [ ] **Step 5: Run focused tests and verify success**

Run: `php tests/tutors-test.php && php tests/institutional-responsive-contract-test.php`

Expected: both suites PASS.

- [ ] **Step 6: Refresh and visually verify the About page**

Run: `npx wp-env run cli wp tbt setup-site --refresh-approved` and inspect `/about/` at 1440px, 768px, 390px, and 320px. Confirm all four tutors and credentials remain visible and no horizontal overflow occurs.

- [ ] **Step 7: Run full verification**

Run: `scripts/verify-local.sh && node tests/runtime-browser-test.js`

Expected: local verification and all 12 browser routes PASS.

- [ ] **Step 8: Commit**

```bash
git add docs/superpowers/specs/2026-11-04-about-tutor-cards-design.md docs/superpowers/plans/2026-11-04-about-tutor-cards.md tests/tutors-test.php tests/institutional-responsive-contract-test.php wordpress/wp-content/themes/thatbiotutor-hello-child/inc/tutors.php wordpress/wp-content/themes/thatbiotutor-hello-child/assets/css/pages-institutional.css
git commit -m "feat: compact About tutor profiles"
```

