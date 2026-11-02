# Inquire Static Pages Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Build client-editable static homepage and Sec 3 IntensiveFix pages that match the current inquiry site, apply the supplied change package, deploy from `mirror/`, and map cleanly into Elementor later.

**Architecture:** Use semantic, hand-authored HTML pages with shared dependency-free CSS and JavaScript. Keep all runtime assets local, put shared editable values and ordered asset lists in one content configuration file, and preserve the existing Wix snapshot under `mirror/www.thatbiotutor.com/` unchanged.

**Tech Stack:** HTML5, CSS custom properties, vanilla JavaScript, JSON, Python 3 standard-library tests, GitHub Pages

**Spec:** `docs/superpowers/specs/2026-11-02-inquire-static-pages-design.md`

## Global Constraints

- GitHub Pages deploys `mirror/` directly through `.github/workflows/pages.yml`.
- Do not introduce a framework, npm runtime dependency, build step, WordPress dependency, or Elementor-generated markup.
- Preserve `mirror/www.thatbiotutor.com/` and `/Users/dmsyx/Documents/Projects/thatbiotutor-original` unchanged.
- Store all page images locally and preserve the supplied folder ordering.
- Every Contact Us and booking CTA uses `https://wa.me/6587597182` with an encoded page-specific message.
- All essential copy, galleries, final counter values, and CTAs remain available when JavaScript is disabled.
- Motion honours `prefers-reduced-motion`.
- Use one `h1` per page, logical headings, visible focus, meaningful alt text, and minimum 44 by 44 CSS-pixel interactive targets.
- Keep sections, class names, tokens, and assets understandable to a non-technical client and directly mappable to Elementor containers, global colours, and global fonts.
- The Word document and supplied folders listed in the spec are authoritative for copy changes, removals, image choice, and order.

## Review Focus

- GitHub Pages may serve the site under a repository subpath; all internal pages and local assets must use relative paths that work at both `/` and `/<repo>/`.
- Supplied filenames include spaces, numbering, and misspellings; renamed local assets must retain exact source order through the manifest and rendered HTML.
- JavaScript failure or reduced-motion preference must leave visible final counters, usable galleries, FAQ content, and working WhatsApp links.
- Very narrow mobile screens and portrait supplied graphics must not introduce horizontal overflow, clipped controls, or over-cropped faces and text.
- Keyboard users must be able to reach, operate, and understand carousel and FAQ controls with visible focus and correct accessibility state.

---

### Task 1: Build the local asset library and source manifest

**Files:**
- Create: `mirror/assets/images/asset-manifest.json`
- Create: `mirror/assets/images/shared/*`
- Create: `mirror/assets/images/home/*`
- Create: `mirror/assets/images/intensivefix/*`
- Create: `tests/test_static_site.py`

**Interfaces:**
- Consumes: Authoritative source paths and reference URLs from the approved spec.
- Produces: `asset-manifest.json` with arrays `shared`, `homeHero`, `trackRecord`, `homeClassroom`, `intensiveFixCarousel`, and `intensiveFixReview`; every entry has `source`, `file`, `alt`, `width`, and `height`.

- [ ] **Step 1: Write failing manifest and asset-integrity tests**

Add `StaticSiteAssetTests` to `tests/test_static_site.py` with these assertions:

```python
def test_manifest_has_authoritative_ordered_groups(self):
    self.assertEqual(len(self.manifest["homeHero"]), 5)
    self.assertEqual(len(self.manifest["trackRecord"]), 4)
    self.assertEqual(len(self.manifest["homeClassroom"]), 3)
    self.assertEqual(len(self.manifest["intensiveFixCarousel"]), 10)
    self.assertEqual(len(self.manifest["intensiveFixReview"]), 1)

def test_manifest_files_are_local_and_exist(self):
    # Assert every file is a relative assets/images path, exists under mirror/,
    # has non-zero width and height, and has non-placeholder alt text.

def test_supplied_groups_preserve_numeric_source_order(self):
    # Assert each supplied group source basename begins with 1..N in order.
```

- [ ] **Step 2: Run the asset tests and verify they fail**

Run: `python3 -m unittest tests.test_static_site.StaticSiteAssetTests -v`

Expected: FAIL because `mirror/assets/images/asset-manifest.json` does not exist.

- [ ] **Step 3: Create the manifest and copy the supplied assets with descriptive names**

Use these exact ordered mappings:

- `1A/1.jpg` through `1A/5.png` -> `home/hero-01` through `home/hero-05`, preserving extensions.
- `1B/1.jpg` through `1B/4.jpg` -> `shared/track-record-01.jpg` through `shared/track-record-04.jpg`.
- `1C/1.jpg` through `1C/3.jpg` -> `home/classroom-01.jpg` through `home/classroom-03.jpg`.
- `2A/1 cover.jpg`, numbered 2 through 10 -> `intensivefix/program-01.jpg` through `intensivefix/program-10.jpg`.
- `2B/7 testims.jpg` -> `intensivefix/student-reviews.jpg`.

Record the original absolute source path in each `source` field. Read actual image dimensions with `sips` and record them in the manifest.

- [ ] **Step 4: Download and record retained reference-page assets**

Store the retained reference-page assets under these stable local names:

- `shared/tbt-logo.png` and `shared/favicon.png`.
- `shared/testimonial-yongzheng.jpg`, `shared/testimonial-gwen.png`, and `shared/testimonial-aryan.jpg`.
- `shared/fasttrack-content.avif`, `shared/fasttrack-memory.png`, `shared/fasttrack-answering.png`, `shared/fasttrack-practical.png`, and `shared/fasttrack-support.png`.
- `shared/school-crest-01.png` through `shared/school-crest-12.png` in live-page order and `shared/hall-of-fame.png`.
- `shared/tutor-keefe.png`, `shared/tutor-chelsea.png`, `shared/tutor-jamie.png`, and `shared/tutor-ng.png`.
- `shared/practical-01.png`, `shared/practical-02.jpeg`, and `shared/practical-03.png` in live-page order.
- `home/founder-standing.png` and `home/founder-about.png`.

Use the exact live URLs from the two WordPress reference pages in manifest `source` fields; do not copy Trustindex avatars, remote tracking pixels, WordPress plugin assets, or superseded hero/classroom/track-record images.

- [ ] **Step 5: Run asset tests and inspect image contact sheets**

Run: `python3 -m unittest tests.test_static_site.StaticSiteAssetTests -v`

Expected: PASS.

Generate temporary contact sheets for each ordered supplied group and visually confirm order, orientation, legibility, and that the files are not corrupt. Do not commit the contact sheets.

- [ ] **Step 6: Commit the asset library**

```bash
git add mirror/assets/images tests/test_static_site.py
git commit -m "feat: add local inquiry site assets"
```

### Task 2: Create the shared design system and progressive enhancements

**Files:**
- Create: `mirror/assets/css/base.css`
- Create: `mirror/assets/css/components.css`
- Create: `mirror/assets/css/pages.css`
- Create: `mirror/assets/js/content.js`
- Create: `mirror/assets/js/site.js`
- Modify: `tests/test_static_site.py`

**Interfaces:**
- Consumes: Manifest group names and relative asset paths from Task 1.
- Produces: `window.TBT_CONTENT`, `window.TBT_UI.init()`, shared classes `.site-header`, `.button`, `.media-carousel`, `.stat`, `.faq`, `.split-section`, and CSS tokens under `:root`.

- [ ] **Step 1: Write failing shared-foundation tests**

Add `StaticSiteFoundationTests` with assertions that:

```python
def test_content_config_has_whatsapp_and_ordered_assets(self):
    # Source contains number "6587597182", separate home/intensiveFix messages,
    # and all five manifest group names.

def test_css_defines_elementor_ready_tokens_and_accessibility_states(self):
    # base.css defines colour, typography, spacing, radius, shadow, and container tokens.
    # components.css contains :focus-visible and 44px minimum target rules.
    # pages.css contains narrow-mobile and wide-layout media queries.

def test_site_script_defines_progressive_enhancements(self):
    # site.js defines initCarousels, initCounters, initFaqs, and reduced-motion handling.
```

- [ ] **Step 2: Run the foundation tests and verify they fail**

Run: `python3 -m unittest tests.test_static_site.StaticSiteFoundationTests -v`

Expected: FAIL because the shared files do not exist.

- [ ] **Step 3: Implement the shared content contract**

In `content.js`, assign one frozen object to `window.TBT_CONTENT` with this public shape:

```javascript
{
  assetBase: new URL("../images/", document.currentScript.src).href,
  whatsapp: {
    number: "6587597182",
    homeMessage: "Hi ThatBioTutor, I'd like to book a $95 trial lesson.",
    intensiveFixMessage: "Hi ThatBioTutor, I'd like to fix my Sec 3 Biology foundation with IntensiveFix."
  },
  counters: [
    { key: "students", value: 912, suffix: "+" },
    { key: "results", value: 91, suffix: "%" },
    { key: "gradeJump", value: 2.3, suffix: "" },
    { key: "homeDistinction", value: 2, prefix: "1 in " },
    { key: "intensiveLessons", value: 8, suffix: "" }
  ],
  assets: { homeHero: [], trackRecord: [], homeClassroom: [], intensiveFixCarousel: [], intensiveFixReview: [] }
}
```

Populate the asset arrays from Task 1 with paths relative to `assetBase`, not relative to either HTML page. Resolve runtime asset URLs with `new URL(file, assetBase)` so deployment works at both a domain root and a GitHub Pages repository subpath. Add `CLIENT EDIT` comments around values a client may safely change.

- [ ] **Step 4: Implement shared styling**

Create:

- `base.css` for reset, tokens, typography, layout primitives, skip link, and reduced-motion defaults.
- `components.css` for header, buttons, carousels, counters, comparison rows, testimonials, FAQ disclosure, video, galleries, CTA sections, and footer.
- `pages.css` only for homepage and IntensiveFix section-specific composition and per-image `object-position` hooks.

Use relative units and container queries or media queries without hardcoded full-page widths. Keep each selector rooted in a stable component class, not DOM depth.

- [ ] **Step 5: Implement progressive enhancement**

In `site.js`, expose `window.TBT_UI.init()` and define:

```javascript
initCarousels(root = document): void
initCounters(root = document): void
initFaqs(root = document): void
buildWhatsAppUrl(message: string): string
```

Carousels wrap indices, update `aria-current` and status text, support ArrowLeft/ArrowRight, pointer swipe, and manual controls. Counters animate once with `IntersectionObserver` but immediately show final values when reduced motion is requested or the observer is unavailable. FAQ buttons toggle `aria-expanded` and `hidden`. `buildWhatsAppUrl` uses `URLSearchParams` and the configured number.

- [ ] **Step 6: Run foundation tests and JavaScript syntax checks**

Run:

```bash
python3 -m unittest tests.test_static_site.StaticSiteFoundationTests -v
node --check mirror/assets/js/content.js
node --check mirror/assets/js/site.js
```

Expected: all tests PASS and both syntax checks exit 0.

- [ ] **Step 7: Commit the shared foundation**

```bash
git add mirror/assets/css mirror/assets/js tests/test_static_site.py
git commit -m "feat: add static site design system"
```

### Task 3: Build the client-editable homepage

**Files:**
- Modify: `mirror/index.html`
- Modify: `tests/test_static_site.py`

**Interfaces:**
- Consumes: Task 1 local assets; Task 2 CSS classes, `window.TBT_CONTENT`, and `window.TBT_UI.init()`.
- Produces: GitHub Pages root homepage with stable section IDs and all approved homepage changes.

- [ ] **Step 1: Write failing homepage structure and content tests**

Add `HomePageTests` that parse `mirror/index.html` with `html.parser.HTMLParser` and assert:

```python
def test_home_has_one_h1_and_required_sections(self):
    # Exactly one h1 and IDs: hero, results, fasttrack, tutors, trial-guarantee,
    # suitability, founder, faq, classroom, final-cta.

def test_home_uses_supplied_images_in_manifest_order(self):
    # hero has five homeHero files; results has four trackRecord files;
    # classroom has three homeClassroom files in exact manifest order.

def test_trial_form_is_replaced_by_guarantee_and_whatsapp_cta(self):
    # Contains the exact $95 refund paragraph and "Book My Trial Lesson";
    # contains no iframe or form inside #trial-guarantee.

def test_home_is_progressively_accessible(self):
    # Every meaningful image has alt text; carousel buttons have accessible names;
    # final stat values exist in HTML; FAQ answers exist in HTML; skip link targets main.

def test_home_uses_relative_local_assets_and_valid_internal_links(self):
    # No wp-content image, plugin CSS, or absolute localhost links;
    # all assets exist and s3-intensivefix link resolves under mirror/.
```

- [ ] **Step 2: Run homepage tests and verify they fail**

Run: `python3 -m unittest tests.test_static_site.HomePageTests -v`

Expected: FAIL because `mirror/index.html` is still the old redirect page.

- [ ] **Step 3: Replace the redirect with the semantic homepage shell**

Set title, description, local favicon, social metadata, skip link, header, main, and footer. Load `base.css`, `components.css`, `pages.css`, then deferred `content.js` and `site.js`. Use a relative link to `s3-intensivefix/` and WhatsApp anchors that work without JavaScript.

- [ ] **Step 4: Add homepage content sections in approved order**

Recreate the current inquiry page copy and hierarchy, then apply the spec changes:

- Five supplied hero slides from `homeHero`.
- Four animated result values and four supplied track-record images.
- Retained FastTrack, proof, tutor, quality-control, practical-session, suitability, founder, and FAQ content.
- `#trial-guarantee` with a local shield icon and exact copy: `A trial lesson is $95. We’re so confident of our lesson quality that if you didn’t absolutely love it, simply ask for a refund and you’ll get your money back, no questions asked.`
- `#classroom` with the three supplied images in order and the teaching image first.
- Extra spacing before the final `Book My Trial Lesson` CTA.

Place `CLIENT EDIT` comments around headings, long-form copy, CTA labels/links, counters, and gallery blocks. Use stable one-section-per-Elementor-container class names.

- [ ] **Step 5: Run homepage and foundation tests**

Run: `python3 -m unittest tests.test_static_site.HomePageTests tests.test_static_site.StaticSiteFoundationTests -v`

Expected: PASS.

- [ ] **Step 6: Serve and visually verify the homepage**

Run: `python3 -m http.server 8765 --directory mirror`

Inspect `http://localhost:8765/` at 375, 768, 1280, and 1440 CSS-pixel widths. Verify no horizontal overflow, no overly tight crop, readable supplied graphics, visible focus, carousel swipe/buttons/keyboard, reduced motion, counters, FAQ, and WhatsApp destination.

- [ ] **Step 7: Commit the homepage**

```bash
git add mirror/index.html tests/test_static_site.py
git commit -m "feat: build inquiry homepage"
```

### Task 4: Build the Sec 3 IntensiveFix page

**Files:**
- Create: `mirror/s3-intensivefix/index.html`
- Modify: `tests/test_static_site.py`

**Interfaces:**
- Consumes: Task 1 IntensiveFix and shared assets; Task 2 shared design system and interaction API; Task 3 shared header/footer markup pattern.
- Produces: `/s3-intensivefix/` page with approved copy, omissions, carousels, video, and CTA behaviour.

- [ ] **Step 1: Write failing IntensiveFix tests**

Add `IntensiveFixPageTests` that assert:

```python
def test_intensivefix_has_one_h1_and_required_sections(self):
    # Exactly one h1 and IDs: hero, program-carousel, reviews, results,
    # analysis-video, fasttrack, suitability, final-cta.

def test_hero_and_navigation_use_approved_copy(self):
    # Contains "Limited time: Only during last two weeks of Nov/first two weeks of Dec."
    # and "Fix My Sec 3 Foundation"; top-right CTA says "Contact Us".
    # Does not contain the old grey trial/limited-intake subtext.

def test_program_and_review_graphics_use_supplied_order(self):
    # Ten intensiveFixCarousel images in manifest order and one review graphic.

def test_unapproved_sections_and_testimonials_are_absent(self):
    # No three Yong Zheng/Gwen/Aryan testimonial cards, no trial form,
    # no social-proof/tutor/quality-control/practical/founder/FAQ/classroom sections
    # between FastTrack and suitability.

def test_intensivefix_retains_current_analysis_video(self):
    # Contains q_IPfEdODqI and a descriptive iframe title.

def test_intensivefix_has_approved_fasttrack_and_closing_copy(self):
    # Heading starts "All IntensiveFix students will receive ThatBioTutor’s FastTrack";
    # exact EOY closing paragraphs and "Ready to see what that feels like?" are present.

def test_intensivefix_progressive_accessibility_and_links(self):
    # Local relative assets exist, final counter values are in HTML, controls are labelled,
    # WhatsApp links contain 6587597182, and home link resolves to ../.
```

- [ ] **Step 2: Run IntensiveFix tests and verify they fail**

Run: `python3 -m unittest tests.test_static_site.IntensiveFixPageTests -v`

Expected: FAIL because `mirror/s3-intensivefix/index.html` does not exist.

- [ ] **Step 3: Create the IntensiveFix shell and hero**

Reuse the shared metadata, skip link, header, and footer patterns. Set the top-right CTA to `Contact Us`, link it to the configured IntensiveFix WhatsApp message, remove the grey subtext, apply black/green emphasis to the exact limited-time subtitle, and label the primary CTA `Fix My Sec 3 Foundation`.

- [ ] **Step 4: Add the IntensiveFix conversion sequence**

Implement in this order:

1. Ten supplied program graphics in a swipeable carousel.
2. Existing pain-point quotes and approved lead-in: `Sound familiar? After our 2-week IntensiveFix, they’re telling us:`.
3. One supplied IntensiveFix review graphic, with no unrelated testimonial cards or testimonial video.
4. Shared running counters and four shared images; the fourth statistic remains `8 Lessons to rebuild your entire Sec 3 foundation`.
5. Current analysis video `https://youtu.be/q_IPfEdODqI?si=ItSNRWYN7uWZ20mL`.
6. FastTrack section headed `All IntensiveFix students will receive ThatBioTutor’s FastTrack System` and retaining the relevant FastTrack comparison content.
7. Jump directly to the retained `This is for` and `This isn't for` suitability columns.
8. Approved EOY closing copy, a deliberate gap, and the existing WhatsApp CTA.

Use `CLIENT EDIT` comments and the same Elementor-oriented section conventions as the homepage.

- [ ] **Step 5: Run all static tests and JavaScript syntax checks**

Run:

```bash
python3 -m unittest tests.test_static_site -v
node --check mirror/assets/js/content.js
node --check mirror/assets/js/site.js
```

Expected: all tests PASS and syntax checks exit 0.

- [ ] **Step 6: Serve and visually verify IntensiveFix**

With the local server running, inspect `http://localhost:8765/s3-intensivefix/` at 375, 768, 1280, and 1440 CSS-pixel widths. Verify supplied portrait graphics remain legible, section omissions create no abrupt gap, video loads responsively, all controls work by keyboard and touch, reduced motion works, and WhatsApp opens with the IntensiveFix message.

- [ ] **Step 7: Commit IntensiveFix**

```bash
git add mirror/s3-intensivefix/index.html tests/test_static_site.py
git commit -m "feat: add Sec 3 IntensiveFix page"
```

### Task 5: Add client handoff documentation and run release verification

**Files:**
- Create: `docs/client-editing-guide.md`
- Modify: `README.md`
- Modify: `tests/test_static_site.py`

**Interfaces:**
- Consumes: Final file structure and public editing points from Tasks 1-4.
- Produces: Client editing instructions, Elementor migration map, and a verified deployable `mirror/` artifact.

- [ ] **Step 1: Write failing documentation and preservation tests**

Add `ProjectHandoffTests` asserting:

```python
def test_editing_guide_covers_common_client_tasks(self):
    # Guide names content.js, manifest, both HTML files, CSS tokens, image replacement,
    # image order, CTA copy/link, counters, and local preview commands.

def test_editing_guide_has_elementor_migration_map(self):
    # Guide maps CSS tokens to Elementor global colours/fonts and each stable section ID
    # to an Elementor container/widget pattern.

def test_legacy_wix_mirror_still_exists(self):
    self.assertTrue((MIRROR / "www.thatbiotutor.com/index.html").exists())

def test_pages_have_no_broken_local_references(self):
    # Parse src/href references from both new pages and assert each local target exists.
```

- [ ] **Step 2: Run handoff tests and verify they fail**

Run: `python3 -m unittest tests.test_static_site.ProjectHandoffTests -v`

Expected: FAIL because `docs/client-editing-guide.md` is missing.

- [ ] **Step 3: Write the client editing and Elementor migration guide**

Explain, with exact paths:

- Previewing the site locally.
- Changing headlines, paragraphs, CTA labels, WhatsApp messages, counter values, image files, alt text, and carousel order.
- Keeping original dimensions and updating manifest width/height.
- Using `CLIENT EDIT` markers safely.
- Mapping `:root` tokens into Elementor Site Settings.
- Mapping header/footer, each stable section ID, carousels, counters, FAQ, split comparisons, video, and CTA blocks into Elementor containers and widgets.
- Copying local assets into the WordPress Media Library and replacing relative paths.
- Rechecking mobile layouts, focus, alt text, and reduced-motion behaviour after migration.

- [ ] **Step 4: Update the project README**

Lead with the new site entry points and local-preview command, link the client guide and approved design/plan, explain that the Wix mirror remains under `mirror/www.thatbiotutor.com/`, and retain the backup-boundary explanation.

- [ ] **Step 5: Run the complete automated verification**

Run:

```bash
python3 -m unittest tests.test_static_site -v
node --check mirror/assets/js/content.js
node --check mirror/assets/js/site.js
git diff --check
```

Expected: all tests PASS, syntax checks exit 0, and `git diff --check` prints nothing.

- [ ] **Step 6: Run final browser and link verification**

Serve `mirror/` and verify both paths at mobile and desktop widths. Test keyboard-only navigation, touch swipe, FAQ disclosure, reduced motion, no-JavaScript fallbacks, image loading, relative navigation, video, and every WhatsApp CTA. Confirm there are no console errors or requests to WordPress plugin assets.

- [ ] **Step 7: Confirm preservation boundaries**

Run:

```bash
git status --short
git diff 2607d7c4ebe866f719d00f1aaf105e3c61a9269b -- mirror/www.thatbiotutor.com
git -C /Users/dmsyx/Documents/Projects/thatbiotutor-original status --short
git -C /Users/dmsyx/Documents/Projects/thatbiotutor-original rev-parse HEAD
```

Expected: no diff under `mirror/www.thatbiotutor.com`; original snapshot remains clean at `2607d7c4ebe866f719d00f1aaf105e3c61a9269b`.

- [ ] **Step 8: Commit the handoff and verification work**

```bash
git add README.md docs/client-editing-guide.md tests/test_static_site.py
git commit -m "docs: add static site client handoff"
```

