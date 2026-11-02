# Editable All-Pages Shared Templates Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Generate all 12 public ThatBioTutor pages from client-editable page fragments and one shared header, footer, navigation, and floating WhatsApp template.

**Architecture:** A Python 3.9 standard-library generator reads `site/site.json`, `site/pages.json`, shared HTML templates, page fragments, and local WebP assets, then writes a complete static site to ignored `build/`. GitHub Actions builds and validates the site before deployment, while the preserved Wix archive remains an untouched content reference.

**Tech Stack:** Python 3.9 standard library, semantic HTML5, shared CSS, vanilla JavaScript, JSON, `unittest`, GitHub Actions/Pages

**Spec:** `docs/superpowers/specs/2026-11-02-editable-all-pages-shared-templates-design.md`

## Global Constraints

- Generate 12 canonical pages: `/`, `/s3-intensivefix/`, `/about/`, `/classes/`, `/sec-3-pure-g3-biology-tuition/`, `/sec-4-pure-g3-biology-tuition/`, `/contact/`, `/notes/`, `/testimonials/`, `/celldivision/`, `/trees/`, and `/tuitionrecs/`.
- Generate `/classes/sec-3-pure-g3-biology-tuition/` only as an accessible redirect alias to the canonical Sec 3 page.
- Store the production origin once as `https://www.thatbiotutor.com` in `site/site.json`; use it for canonical, Open Graph, sitemap, and robots URLs.
- Use relative runtime links that work both at a domain root and a GitHub Pages repository subpath.
- Use Python standard-library modules only; no framework, package installation, Node build step, Jekyll, or runtime fragment loading.
- Keep site-wide header, footer, navigation, contact details, and floating WhatsApp control in shared sources, never repeated in editable page fragments.
- Keep long-form content in semantic `site/pages/*.html` fragments with stable section IDs and `CLIENT EDIT` comments.
- Use `https://wa.me/6587597182` with a page-specific message or the configured default message.
- Every selected local raster image must be WebP and recorded with provenance, dimensions, and alt text.
- Preserve the approved homepage and IntensiveFix requirements, exact copy changes, supplied image order, and accessible interaction fixes already present.
- Preserve `mirror/www.thatbiotutor.com/` and `/Users/dmsyx/Documents/Projects/thatbiotutor-original` unchanged.
- Treat dated legacy copy as editable source requiring client review; never silently change dates, prices, claims, or policies.
- Generated `build/` is ignored and never hand edited or committed.
- Essential navigation, content, counters, galleries, FAQs, and CTAs remain usable without JavaScript and with reduced motion.

## Review Focus

- A malicious or accidental route such as `../outside`, `/absolute`, or a normalized duplicate must fail before writing outside `build/`; Task 1 pins path traversal and duplicate-route tests.
- Copy containing `$95`, apostrophes, ampersands, Unicode punctuation, or literal single braces must render unchanged in page content while metadata remains escaped; Task 1 pins template/escaping tests.
- Root, one-level, and two-level routes must compute correct relative asset and navigation prefixes under a repository subpath; Tasks 1 and 2 pin prefix and generated-link tests.
- A build failure after a previous successful build must leave the previous output intact instead of exposing a partial site; Task 1 pins staged-output preservation.
- A client edit made only under `site/` through GitHub must be sufficient for deployment, with no stale committed output required; Task 6 pins workflow/build-source tests.

---

### Task 1: Build the safe standard-library site generator

**Files:**
- Create: `scripts/__init__.py`
- Create: `scripts/build_site.py`
- Create: `tests/test_site_builder.py`
- Modify: `.gitignore`

**Interfaces:**
- Consumes: A project root containing `site/site.json`, `site/pages.json`, `site/templates/base.html`, `site/partials/*.html`, `site/pages/*.html`, and `site/assets/`.
- Produces:
  - `BuildError(RuntimeError)`
  - `PageSpec(key, route, source, title, description, body_class, nav_key, whatsapp_message, og_image, context, aliases)`
  - `BuildReport(pages: tuple[str, ...], aliases: tuple[str, ...], files: tuple[str, ...])`
  - `render_template(text: str, context: Mapping[str, str], source_name: str) -> str`
  - `expand_includes(text: str, site_dir: Path, source_name: str, stack: tuple[Path, ...] = ()) -> str`
  - `relative_root(route: str) -> str`
  - `render_site(project_root: Path, output_dir: Path) -> BuildReport`
  - CLI: `python3 scripts/build_site.py --output build`

- [ ] **Step 1: Write failing generator unit tests**

Add temporary-fixture tests covering:

```python
def test_relative_root_supports_root_and_nested_routes(self):
    self.assertEqual(relative_root(""), "")
    self.assertEqual(relative_root("about/"), "../")
    self.assertEqual(relative_root("classes/sec-3/"), "../../")

def test_renderer_preserves_page_copy_and_escapes_metadata(self):
    # `$95`, curly punctuation, and literal single braces survive page HTML.
    # `<` and `&` in title/description are escaped in head metadata.

def test_build_rejects_duplicate_absolute_and_traversal_routes(self):
    # duplicate normalized route, `/about`, and `../outside` each raise BuildError.

def test_failed_staged_build_preserves_previous_output(self):
    # Seed output/index.html, force a missing partial, assert old output still exists.

def test_build_generates_canonical_sitemap_alias_and_manifest(self):
    # Canonical uses configured siteUrl; alias has canonical + fallback link;
    # sitemap excludes alias; build-manifest lists generated files.
```

- [ ] **Step 2: Run generator tests and verify they fail**

Run: `python3 -m unittest tests.test_site_builder -v`

Expected: FAIL because `scripts.build_site` does not exist.

- [ ] **Step 3: Implement configuration loading and validation**

In `scripts/build_site.py`, define the interfaces above using `dataclasses`, `json`, `pathlib`, `html`, `urllib.parse`, `tempfile`, and `shutil`. Validate required site keys (`siteName`, `siteUrl`, `logo`, `favicon`, `whatsappNumber`, `defaultWhatsappMessage`, `address`, `hours`, `copyright`, `navigation`) and required page keys. Normalize routes as POSIX-relative directory routes and reject empty non-home segments, absolute paths, `.`/`..`, duplicate canonical routes, duplicate aliases, alias/canonical collisions, and source paths outside `site/`.

- [ ] **Step 4: Implement template rendering, includes, and per-route context**

Support placeholders matching `{{ identifier }}` and include comments matching:

```html
<!-- INCLUDE: partials/sections/track-record.html -->
```

Fail on unresolved/unknown placeholders, missing includes, recursive includes, or includes outside `site/`. Merge escaped global/page metadata and escaped page `context` text with raw reviewed HTML slots (`main_html`, `nav_html`, `footer_nav_html`). Compute `root`, canonical URL, Open Graph URL, active navigation, and URL-encoded WhatsApp URL per page.

- [ ] **Step 5: Implement staged output, assets, sitemap, robots, aliases, and CLI**

Render into a temporary sibling directory, validate all output, then replace only the explicit output directory. Copy `site/assets/`, create `.nojekyll`, `sitemap.xml`, `robots.txt`, `build-manifest.json`, canonical page `index.html` files, and alias pages. Reject non-WebP raster files under `site/assets/images/`, broken local `src`/`href` targets, and output destinations equal to or above the repository root.

- [ ] **Step 6: Run generator tests and syntax checks**

Run:

```bash
python3 -m unittest tests.test_site_builder -v
python3 -m py_compile scripts/build_site.py
```

Expected: all generator tests PASS and syntax check exits 0.

- [ ] **Step 7: Commit the generator**

```bash
git add .gitignore scripts tests/test_site_builder.py
git commit -m "feat: add safe static site generator"
```

### Task 2: Move the approved two pages into shared templates

**Files:**
- Create: `site/site.json`
- Create: `site/pages.json`
- Create: `site/templates/base.html`
- Create: `site/partials/header.html`
- Create: `site/partials/footer.html`
- Create: `site/partials/floating-whatsapp.html`
- Create: `site/partials/sections/track-record.html`
- Create: `site/partials/sections/fasttrack.html`
- Create: `site/pages/home.html`
- Create: `site/pages/s3-intensivefix.html`
- Move: `mirror/assets/css/*` -> `site/assets/css/*`
- Move: `mirror/assets/js/*` -> `site/assets/js/*`
- Move: `mirror/assets/images/*` -> `site/assets/images/*`
- Delete after migration: `mirror/index.html`, `mirror/s3-intensivefix/index.html`, `mirror/assets/`
- Modify: `tests/test_static_site.py`

**Interfaces:**
- Consumes: Task 1 `render_site()` and approved page implementation at commit `6cee595`.
- Produces: Two canonical pages generated from one shared shell; page context fields used by section partials; test helper `build_test_site() -> Path` used by later page tests.

- [ ] **Step 1: Refactor static-site tests to build from source and add shared-template assertions**

Use a module-scoped temporary output produced by `render_site(REPO_ROOT, output)`. Update existing homepage/IntensiveFix tests to parse generated files. Add failing assertions that:

```python
def test_approved_pages_are_registered_and_generated(self):
    self.assertEqual(set(self.registry_keys), {"home", "s3-intensivefix"})

def test_header_footer_and_floating_action_come_from_shared_partials(self):
    # Page fragments contain no <header>, <footer>, or floating-whatsapp markup.
    # Generated pages contain all three with correct relative URLs.

def test_root_and_nested_assets_work_under_repository_subpath(self):
    # Home uses assets/...; IntensiveFix uses ../assets/...;
    # every local target exists in generated output.
```

Keep all existing approved-copy, supplied-order, accessibility, interaction-source, and WebP manifest assertions.

- [ ] **Step 2: Run the approved-page tests and verify they fail**

Run: `python3 -m unittest tests.test_static_site -v`

Expected: FAIL because `site/` and its registry do not exist.

- [ ] **Step 3: Create global configuration and the two-page registry**

Set `siteUrl` to exactly `https://www.thatbiotutor.com`, WhatsApp number to `6587597182`, shared address/hours/copyright from the approved pages, and one navigation list. Register `home` at `""` and `s3-intensivefix` at `"s3-intensivefix/"`. Store page-specific WhatsApp messages and section-partial context in each entry.

- [ ] **Step 4: Create the shared document shell and global partials**

Move the existing header, footer, and floating control into their partials. `base.html` owns one skip link, metadata, shared CSS/JS links, body class, the shared partial slots, and `main_html`. Preserve the progressive mobile menu, visible focus states, reduced-motion behavior, and no-JavaScript navigation.

- [ ] **Step 5: Migrate approved page content, reusable sections, and assets**

Move the current page `<main>` sections into `site/pages/home.html` and `site/pages/s3-intensivefix.html`. Extract the repeated track-record and five-row FastTrack markup into section partials using page context for the differing heading/fourth counter. Move all approved CSS/JS/WebP assets and manifest under `site/assets/`; keep public asset paths as `assets/...`. Remove only the obsolete new-site files from `mirror/`, never `mirror/www.thatbiotutor.com/`.

- [ ] **Step 6: Build and verify both pages**

Run:

```bash
python3 scripts/build_site.py --output build
python3 -m unittest tests.test_site_builder tests.test_static_site -v
node --check site/assets/js/content.js
node --check site/assets/js/site.js
git diff --check
```

Expected: all tests PASS; two canonical pages generated; JavaScript checks exit 0.

- [ ] **Step 7: Commit the shared two-page source**

```bash
git add site scripts tests mirror/index.html mirror/s3-intensivefix mirror/assets
git commit -m "refactor: generate approved pages from shared templates"
```

### Task 3: Migrate institutional and supporting pages

**Files:**
- Create: `site/pages/about.html`
- Create: `site/pages/contact.html`
- Create: `site/pages/trees.html`
- Create: `site/pages/tuitionrecs.html`
- Create: `site/assets/images/about/*.webp`
- Create: `site/assets/images/causes/*.webp`
- Create: `site/assets/images/recommendations/*.webp`
- Modify: `site/pages.json`
- Modify: `site/assets/images/asset-manifest.json`
- Modify: `tests/test_static_site.py`

**Interfaces:**
- Consumes: Task 2 shared shell/components and the four named Wix archive pages.
- Produces: Generated `/about/`, `/contact/`, `/trees/`, and `/tuitionrecs/` pages; registry size 6; migrated WebP assets with provenance.

- [ ] **Step 1: Write failing structure/content tests for the four pages**

Assert registry routes, exactly one H1, shared landmarks, local links, and these stable section IDs:

- About: `hero`, `mission`, `quality`, `tutors`, `founder-message`, `beliefs`, `vision-mission-values`, `final-cta`.
- Contact: `hero`, `faq-pointer`, `contact-details`, `final-cta`.
- Causes: `hero`, `tree-planting`, `local-causes`, `final-cta`.
- Tuition recommendations: `hero`, `recommendations`, `independence-disclosure`, `final-cta`.

Add source-parity assertions for the archive headings and ensure Contact has a WhatsApp CTA but no form. Assert recommendation links remain external and include an independence disclosure.

- [ ] **Step 2: Run the new page tests and verify they fail**

Run: `python3 -m unittest tests.test_static_site.InstitutionalPageTests -v`

Expected: FAIL because the registry entries and fragments are missing.

- [ ] **Step 3: Create semantic page fragments from the preserved sources**

Retain the source meaning and link destinations while removing Wix scaffolding. Use shared tutor cards/CTA components where appropriate. Keep address, hours, and WhatsApp details global rather than duplicating them inside Contact; Contact may reference them through a shared contact-details section partial.

- [ ] **Step 4: Select, convert, document, and inspect required assets**

Select only images that support the migrated content, convert them to WebP, record archive source path/dimensions/alt text in the manifest, and generate temporary contact sheets for visual inspection. Do not modify the source archive.

- [ ] **Step 5: Build and run institutional-page plus full regression tests**

Run:

```bash
python3 scripts/build_site.py --output build
python3 -m unittest tests.test_static_site.InstitutionalPageTests -v
python3 -m unittest tests.test_site_builder tests.test_static_site -v
git diff --check
```

Expected: all tests PASS and six canonical pages are in the build manifest.

- [ ] **Step 6: Commit the institutional pages**

```bash
git add site tests/test_static_site.py
git commit -m "feat: add editable institutional pages"
```

### Task 4: Migrate the classes and level-specific tuition pages

**Files:**
- Create: `site/pages/classes.html`
- Create: `site/pages/sec-3-pure-g3-biology-tuition.html`
- Create: `site/pages/sec-4-pure-g3-biology-tuition.html`
- Create: `site/partials/sections/class-results.html`
- Create: `site/partials/sections/class-resources.html`
- Create: `site/partials/sections/tutor-team.html`
- Create: `site/assets/images/classes/*.webp`
- Modify: `site/pages.json`
- Modify: `site/assets/images/asset-manifest.json`
- Modify: `site/assets/css/components.css`
- Modify: `site/assets/css/pages.css`
- Modify: `tests/test_static_site.py`

**Interfaces:**
- Consumes: Task 2 template context/includes and approved shared components; three canonical Wix tuition sources.
- Produces: `/classes/`, `/sec-3-pure-g3-biology-tuition/`, `/sec-4-pure-g3-biology-tuition/`, plus the one Sec 3 alias; registry size 9.

- [ ] **Step 1: Write failing classes-page tests**

Assert the three routes, Sec 3 alias/canonical behavior, one H1 per page, and section IDs:

- Classes: `hero`, `pure-biology`, `problems`, `solutions`, `differences`, `practical`, `results`, `levels`, `faq`, `final-cta`.
- Sec 3 and Sec 4: `hero`, `learning-options`, `curriculum`, `topics`, `exam-techniques`, `results`, `testimonials`, `lesson-experience`, `resources`, `tutors`, `suitability`, `faq`, `final-cta`.

Pin source headings including Sec 3 “Phase 1 — Teaching the Syllabus (Mar – Aug)”, Sec 4 “Phase 3 — O Levels Intensives (Aug – Nov)”, “FIRED method”, and the six approved results values. Assert alias output uses `../../` local prefix and points to the configured production canonical URL.

- [ ] **Step 2: Run classes tests and verify they fail**

Run: `python3 -m unittest tests.test_static_site.ClassesPageTests -v`

Expected: FAIL because class routes are not registered.

- [ ] **Step 3: Build shared class sections and the overview page**

Create the results, resources, and tutor-team partials from content repeated in the Sec 3/Sec 4 sources. Recreate the classes overview with its Pure/G3 explanation, four problems/solutions, differentiators, practical content, results, level links, and FAQ.

- [ ] **Step 4: Build Sec 3 and Sec 4 fragments with page-specific context**

Preserve each level’s curriculum phases, topic list, exam-technique copy, testimonials, learning modes, resources, suitability criteria, and CTA. Use registry context for level-specific labels and phase data without hiding long-form content in JSON. Register the captured nested Sec 3 path only as an alias.

- [ ] **Step 5: Convert/inspect assets and run full tests**

Run:

```bash
python3 scripts/build_site.py --output build
python3 -m unittest tests.test_static_site.ClassesPageTests -v
python3 -m unittest tests.test_site_builder tests.test_static_site -v
git diff --check
```

Expected: all tests PASS; nine canonical pages and one alias are generated; asset contact sheets show readable faces/text without aggressive cropping.

- [ ] **Step 6: Commit the class pages**

```bash
git add site tests/test_static_site.py
git commit -m "feat: add editable Biology class pages"
```

### Task 5: Migrate resources, crash course, and testimonials

**Files:**
- Create: `site/pages/notes.html`
- Create: `site/pages/celldivision.html`
- Create: `site/pages/testimonials.html`
- Create: `site/partials/sections/testimonial-cards.html`
- Create: `site/assets/images/notes/*.webp`
- Create: `site/assets/images/celldivision/*.webp`
- Create: `site/assets/images/testimonials/*.webp`
- Modify: `site/pages.json`
- Modify: `site/assets/images/asset-manifest.json`
- Modify: `site/assets/css/pages.css`
- Modify: `tests/test_static_site.py`

**Interfaces:**
- Consumes: Task 2 shell and Task 4 proof/testimonial components; three Wix resource/proof sources.
- Produces: `/notes/`, `/celldivision/`, and `/testimonials/`; final registry size 12.

- [ ] **Step 1: Write failing resource/proof page tests**

Assert routes, one H1, shared landmarks, external-link preservation, and section IDs:

- Notes: `hero`, `student-feedback`, `why-free`, `download`, `faq`, `final-cta`.
- Cell Division: `hero`, `dated-notice`, `important`, `access`, `student-feedback`, `final-cta`.
- Testimonials: `hero`, `results`, `reviews`, `student-stories`, `class-2022`, `class-2021`, `final-cta`.

Pin the archive headings “Free O Level Biology Notes Singapore”, “Only For 2023 Sec 4 Pure Bio Students!”, and “Results & Testimonials”. Assert the 2023 text is visible and editable rather than silently updated, and no migrated page contains a Wix form or tracking asset.

- [ ] **Step 2: Run resource/proof tests and verify they fail**

Run: `python3 -m unittest tests.test_static_site.ResourcePageTests -v`

Expected: FAIL because the final three registry entries do not exist.

- [ ] **Step 3: Create Notes and Cell Division fragments**

Retain useful external download/access destinations and explain them with accessible link text. Preserve the dated Cell Division claim and add an adjacent source comment for client review; do not imply the 2023 offer is currently active. Replace Wix-only form behavior with the original accessible external destination or WhatsApp CTA.

- [ ] **Step 4: Create the Testimonials fragment and shared proof cards**

Preserve results/testimonial groupings and valid video/review destinations from the source. Avoid remote review widgets, tracking scripts, and inaccessible profile-avatar hotlinks. Reuse local WebP portraits and semantic blockquotes.

- [ ] **Step 5: Convert/inspect assets and run the full 12-page suite**

Run:

```bash
python3 scripts/build_site.py --output build
python3 -m unittest tests.test_static_site.ResourcePageTests -v
python3 -m unittest tests.test_site_builder tests.test_static_site -v
node --check site/assets/js/content.js
node --check site/assets/js/site.js
git diff --check
```

Expected: all tests PASS; the build manifest lists 12 canonical pages plus one alias; every selected local image is WebP.

- [ ] **Step 6: Commit the final content pages**

```bash
git add site tests/test_static_site.py
git commit -m "feat: add editable resource and results pages"
```

### Task 6: Add client preview, deployment, documentation, and release verification

**Files:**
- Create: `scripts/preview_site.py`
- Modify: `.github/workflows/pages.yml`
- Modify: `README.md`
- Modify: `docs/client-editing-guide.md`
- Modify: `tests/test_site_builder.py`
- Modify: `tests/test_static_site.py`

**Interfaces:**
- Consumes: Final 12-page registry and Task 1 CLI.
- Produces: `python3 scripts/preview_site.py`; GitHub PR validation and main-only Pages deployment from `build/`; complete client/Elementor handoff.

- [ ] **Step 1: Write failing preview/workflow/handoff tests**

Add assertions that:

```python
def test_preview_builds_before_serving(self):
    # preview module calls render_site(project_root, preview_output) before HTTP server.

def test_workflow_builds_from_site_source_and_deploys_only_main(self):
    # workflow runs build_site.py and unittest, uploads build/, validates PRs,
    # and gates configure/upload/deploy steps to main.

def test_client_guide_covers_global_and_page_edits(self):
    # guide names site.json, pages.json, page fragments, shared partials,
    # WebP replacement, GitHub web editing, preview command, and Elementor mapping.
```

- [ ] **Step 2: Run handoff tests and verify they fail**

Run: `python3 -m unittest tests.test_site_builder.PreviewAndWorkflowTests tests.test_static_site.ProjectHandoffTests -v`

Expected: FAIL because preview helper/workflow/docs still describe the two-page direct mirror.

- [ ] **Step 3: Implement the preview helper**

Define `main(argv: Optional[Sequence[str]] = None) -> int` in `scripts/preview_site.py`. Build into ignored `.preview-site/`, bind `ThreadingHTTPServer` to `127.0.0.1`, default port `8765`, print both local URL and stop instructions, and exit cleanly on `KeyboardInterrupt`. Support `--port` and reject ports outside 1–65535.

- [ ] **Step 4: Update GitHub Actions for build-first validation/deployment**

Trigger on pull requests plus pushes to `main`. Every event checks out, runs `python3 scripts/build_site.py --output build`, runs the complete unittest suite and JavaScript syntax checks, and verifies `git diff --check`. Only `main` configures Pages, uploads `build/`, and deploys. Keep Pages permissions no broader than required.

- [ ] **Step 5: Rewrite client and Elementor documentation**

Lead README with `python3 scripts/preview_site.py`. Explain global edits, page content, registry metadata, WebP assets, source-versus-build boundaries, the GitHub web-editor flow, generated-route table, dated-content review, and preservation boundaries. Map shared partials to Elementor Theme Builder/global widgets and each page fragment section to Elementor Containers.

- [ ] **Step 6: Run complete automated verification**

Run:

```bash
python3 scripts/build_site.py --output build
python3 -m unittest tests.test_site_builder tests.test_static_site -v
python3 -m py_compile scripts/build_site.py scripts/preview_site.py
node --check site/assets/js/content.js
node --check site/assets/js/site.js
git diff --check
```

Expected: all tests PASS; syntax checks exit 0; diff check prints nothing.

- [ ] **Step 7: Run browser and preservation verification**

Serve `build/` and inspect every canonical page at 320px and 1440px. Check navigation/active state, shared header/footer/floating control, overflow, WebP image loading/cropping, WhatsApp messages, external resources, keyboard focus, JavaScript-disabled navigation/content, reduced motion, carousel/FAQ behavior, and console/network errors. Then run:

```bash
git diff 2607d7c4ebe866f719d00f1aaf105e3c61a9269b -- mirror/www.thatbiotutor.com
git -C /Users/dmsyx/Documents/Projects/thatbiotutor-original status --short
git -C /Users/dmsyx/Documents/Projects/thatbiotutor-original rev-parse HEAD
```

Expected: no Wix-archive diff; original snapshot clean at `2607d7c4ebe866f719d00f1aaf105e3c61a9269b`.

- [ ] **Step 8: Commit the deployment and handoff**

```bash
git add .github/workflows/pages.yml scripts/preview_site.py README.md docs/client-editing-guide.md tests
git commit -m "docs: complete all-pages template handoff"
```
