# Editable all-pages site with shared templates

## Intent

Expand the client-editable ThatBioTutor site from two pages to every public page while removing repeated site-wide markup from the editable source. A client should change the header, footer, navigation, WhatsApp floating button, contact details, or opening hours once and have the change appear everywhere. Page copy should remain easy to find and edit without learning a framework.

The output remains a fast static website suitable for GitHub Pages and straightforward later migration into WordPress Elementor.

## Confirmed direction

The selected approach is a lightweight Python template generator using only the Python standard library. It will generate ordinary static HTML from shared templates and page-specific HTML fragments.

This replaces the current two-page hand-authored output as the source of truth. The existing new homepage and IntensiveFix page become page fragments under the generator, not manually maintained copies.

The production origin remains `https://www.thatbiotutor.com`. It is stored once as `siteUrl` in `site/site.json` and drives canonical URLs, Open Graph URLs, the sitemap, and robots metadata. Local previews and GitHub Pages repository-subpath deployments still use relative runtime links, so changing the production origin later requires one configuration edit.

## Goals

- Make all 12 public pages part of the editable site.
- Store the header, footer, primary navigation, floating WhatsApp button, global contact details, address, hours, logo, and copyright in shared sources.
- Keep each page’s title, metadata, long-form copy, sections, images, alt text, and page-specific CTA message easy to edit.
- Generate complete semantic HTML with no runtime template or framework dependency.
- Preserve clean URLs and known legacy aliases.
- Keep every local website image in WebP format.
- Preserve the approved homepage and IntensiveFix changes already implemented.
- Recreate the remaining pages from the preserved Wix source while removing Wix scripts, tracking, duplicated responsive markup, and inaccessible generated structure.
- Keep sections one-to-one with future Elementor containers.
- Allow a client to edit through the GitHub web interface and deploy without running a local build command.
- Preserve `mirror/www.thatbiotutor.com/` and the separate original snapshot unchanged.

## Public page inventory

The generated site contains these canonical pages:

| Page | Canonical route | Primary source |
| --- | --- | --- |
| Homepage | `/` | Approved new homepage |
| Sec 3 IntensiveFix | `/s3-intensivefix/` | Approved new IntensiveFix page |
| About | `/about/` | `mirror/www.thatbiotutor.com/about.html` |
| Classes overview | `/classes/` | `mirror/www.thatbiotutor.com/classes.html` |
| Sec 3 Pure/G3 tuition | `/sec-3-pure-g3-biology-tuition/` | Preserved canonical Wix page |
| Sec 4 Pure/G3 tuition | `/sec-4-pure-g3-biology-tuition/` | Preserved canonical Wix page |
| Contact | `/contact/` | `mirror/www.thatbiotutor.com/contact.html` |
| Free Biology notes | `/notes/` | `mirror/www.thatbiotutor.com/notes.html` |
| Results and testimonials | `/testimonials/` | `mirror/www.thatbiotutor.com/testimonials.html` |
| Cell Division crash course | `/celldivision/` | `mirror/www.thatbiotutor.com/celldivision.html` |
| Causes we support | `/trees/` | `mirror/www.thatbiotutor.com/trees.html` |
| Tuition recommendations | `/tuitionrecs/` | Linked preserved page outside the sitemap |

The duplicate captured path `/classes/sec-3-pure-g3-biology-tuition/` becomes a generated redirect/alias to `/sec-3-pure-g3-biology-tuition/` rather than a second editable copy.

## Editing model

### Global edits

`site/site.json` is the central client-editable settings file. It contains:

- Site name and short description.
- Production origin (`siteUrl`), initially `https://www.thatbiotutor.com`.
- Logo and favicon paths.
- WhatsApp number and default message.
- Address and opening hours.
- Navigation labels and routes.
- Footer link groups.
- Copyright text.
- Default social/open-graph image.

Changing a global value updates every generated page.

### Page registry

`site/pages.json` contains one compact entry per canonical page:

```json
{
  "key": "about",
  "route": "about/",
  "source": "pages/about.html",
  "title": "About ThatBioTutor | Pure Biology Specialists",
  "description": "Meet ThatBioTutor's specialist Biology teaching team.",
  "bodyClass": "page-about",
  "navKey": "about",
  "whatsappMessage": "Hi ThatBioTutor, I'd like to learn more about your Biology classes.",
  "ogImage": "assets/images/about/team.webp"
}
```

The registry controls routes and metadata; the visible long-form content stays in HTML rather than JSON.

### Page content

`site/pages/*.html` contains only the page’s `<main>` content. Files use semantic sections, stable IDs, obvious `CLIENT EDIT` comments, and relative asset placeholders supplied by the renderer. A client can edit headings and paragraphs directly without navigating through a complete document shell.

The files use readable shared component classes. They do not contain copies of the header, footer, floating button, global scripts, or stylesheet links.

### Shared templates

The shared shell is split into:

- `site/templates/base.html` — document structure, metadata slots, stylesheet/script loading, and the main-content slot.
- `site/partials/header.html` — logo, navigation, active-page state, and top-right Contact Us CTA.
- `site/partials/footer.html` — address, WhatsApp number, hours, footer navigation, and copyright.
- `site/partials/floating-whatsapp.html` — one global floating contact control with a page-specific prefilled message.

Shared templates may use a small documented `{{ name }}` placeholder syntax. The renderer fails with a clear error for missing or unknown placeholders so template mistakes never deploy silently.

### Reusable page sections

Repeated non-global content uses optional partials only when the same structure and content contract genuinely appears on several pages. Expected reusable partials include:

- Track-record counters.
- FastTrack feature rows.
- Tutor cards.
- Trial guarantee CTA.
- Suitability comparison.
- Testimonials/results cards.

Page files explicitly include a named partial through the renderer. The source avoids a complex macro language; a client should still be able to understand the final page composition by reading the page fragment.

## Source and generated directories

```text
site/
  site.json
  pages.json
  templates/base.html
  partials/header.html
  partials/footer.html
  partials/floating-whatsapp.html
  partials/sections/*.html
  pages/*.html
  assets/css/*.css
  assets/js/*.js
  assets/images/**/*.webp
scripts/
  build_site.py
  preview_site.py
build/                 # ignored generated output
mirror/www.thatbiotutor.com/  # untouched Wix reference archive
```

Generated output is not an editable source and is not committed. `build/` is recreated from scratch by the generator. This eliminates duplicated editable markup entirely.

The existing new-site assets move from `mirror/assets/` to `site/assets/`. The Wix archive remains where it is and is never used at runtime.

## Generator behavior

`scripts/build_site.py` uses the Python standard library only and:

1. Loads and validates `site/site.json` and `site/pages.json`.
2. Validates unique page keys, routes, canonical URLs, and source files.
3. Loads the base and shared partials once.
4. Resolves section includes in each page fragment.
5. Computes the correct relative root prefix for every output depth so the site works at a domain root and a GitHub Pages repository subpath.
6. Builds absolute canonical, Open Graph, sitemap, and robots URLs from the single configured `siteUrl` value.
7. Escapes metadata/configuration text where appropriate while leaving reviewed HTML fragments as HTML.
8. Renders each canonical page to `build/<route>/index.html`.
9. Generates the legacy Sec 3 alias as an accessible redirect page with a normal fallback link.
10. Copies shared CSS, JavaScript, and WebP assets into `build/assets/`.
11. Generates `sitemap.xml`, `robots.txt`, and a small build manifest.
12. Rejects broken local references, non-WebP local images, unresolved placeholders, unsafe output paths, duplicate routes, and missing required metadata.

The build is deterministic: the same sources produce byte-for-byte equivalent output except for an intentionally recorded build version if one is introduced later.

## Local preview and client workflow

The simplest local command is:

```sh
python3 scripts/preview_site.py
```

The preview helper builds the site, starts a local server, and prints the homepage address. It uses no third-party packages.

A client editing through GitHub can:

1. Open the relevant file under `site/pages/` or edit `site/site.json` for a site-wide value.
2. Use GitHub’s web editor and commit the change.
3. Let the GitHub Pages workflow build, validate, and publish it automatically.

No generated HTML needs to be edited or committed.

## Content migration rules

- The approved new homepage and IntensiveFix content remain authoritative for those two pages.
- The Wix archive is the content source for the remaining pages.
- Preserve the meaning and marketing claims from the source. Do not silently modernise prices, dates, claims, or policies.
- Dated content, including the 2023 Cell Division page, remains editable and is listed in the client handoff as requiring a business review rather than being silently rewritten.
- Preserve useful external destinations such as note downloads, videos, recommendation links, and WhatsApp.
- Replace Wix forms or platform-only actions with a clear WhatsApp CTA or the original accessible external destination; do not recreate a backend form.
- Remove Wix scripts, analytics, tracking pixels, duplicated mobile markup, cookie tooling, and hidden platform content.
- Use one H1 per page, logical headings, meaningful alt text, and stable section IDs.
- Select only assets actually needed by the clean pages. Convert every selected raster image to WebP and add it to the asset manifest with source provenance, dimensions, and alt text.
- Do not hotlink WordPress, Wix, or plugin image assets at runtime.

## Shared header, footer, and floating action

The header is identical across pages except for active navigation state and relative path resolution. It includes:

- ThatBioTutor logo/home link.
- Primary navigation covering Classes, Results, Notes, About, IntensiveFix, and Contact.
- A top-right `Contact Us` WhatsApp button on every page.
- A progressively enhanced mobile menu whose links remain available without JavaScript.

The footer is rendered from global settings and includes:

- Site identity and speciality statement.
- Address.
- WhatsApp number.
- Opening hours.
- Useful page links.
- Copyright.

The floating WhatsApp control is rendered once through its partial. It uses the global number and either the page’s configured message or the default global message. It has an accessible text label and must not obscure essential content at narrow widths.

## Page-specific design direction

All pages share the existing dark-green, fresh-green, warm-white visual system and components. The remaining Wix pages are clean recreations, not pixel-for-pixel copies.

- About: mission, quality standards, tutor team, founder message, vision, mission, and values.
- Classes: Pure/G3 Biology explanation, common problems, TBT solutions, class differentiators, results, level choice, and FAQ.
- Sec 3 and Sec 4 tuition: level-specific hero, delivery options, curriculum phases, topics, exam techniques, results, testimonials, lesson experience, resources, team, suitability, FAQ, and CTA.
- Contact: concise contact guidance, FAQ pointer, address/hours, and prominent WhatsApp action.
- Notes: free-notes value proposition, student feedback, download instructions, FAQs, and accessible external download action.
- Testimonials: results summary, student stories, review/video groups where source links remain valid, and CTA.
- Cell Division: clearly editable dated crash-course content, access instructions, student feedback, and valid external resource links.
- Causes: tree-planting and Singapore environmental causes with retained source links.
- Tuition recommendations: organised recommendation cards/links with a clear disclosure that external providers are independent.

## Elementor migration

- `base.html` maps to the WordPress page shell.
- Header and footer partials map to Elementor Theme Builder templates.
- `site.json` colours, fonts, contact details, and global links map to Elementor Site Settings and global widgets.
- Each page fragment’s top-level section maps to one Elementor Container.
- Shared section partials map to saved Elementor templates or global widgets.
- Local WebP assets move into the WordPress Media Library.
- The page registry becomes the migration checklist for titles, slugs, descriptions, canonical links, and page-specific WhatsApp messages.

## Deployment

The GitHub Pages workflow changes from uploading `mirror/` directly to:

1. Check out the repository.
2. Run `python3 scripts/build_site.py --output build`.
3. Run the complete static-site tests against both source and generated output.
4. Upload `build/` as the Pages artifact.
5. Deploy normally.

The workflow still deploys only from `main`. Pull requests run validation without publishing.

## Verification

Automated checks cover:

- Registry/schema validation and unique routes.
- All 12 canonical pages and the one legacy alias generated.
- One H1 and required landmarks per page.
- Shared header/footer/floating-control parity across every generated page.
- Active navigation and relative links at multiple route depths.
- All local references exist and all local raster images are WebP.
- No Wix, WordPress plugin, tracking, or localhost runtime dependencies.
- WhatsApp links use `6587597182` and the correct page/default message.
- Generated sitemap contains every canonical route but not the redirect alias.
- Canonical, Open Graph, sitemap, and robots URLs use the one configured `https://www.thatbiotutor.com` production origin.
- Generator rejects path traversal, duplicate output paths, unresolved placeholders, and malformed configuration.
- The Wix archive and separate original snapshot remain unchanged.

Browser checks cover every canonical route at narrow mobile and desktop widths for:

- No horizontal overflow.
- Visible header, footer, and floating control.
- Working navigation and active state.
- Image loading and sensible cropping.
- Keyboard focus and minimum target sizes.
- JavaScript-disabled navigation and essential content.
- Reduced-motion behavior.
- Page-specific interactive components such as carousels and FAQs.

## Error handling

- Build errors name the source file, page key, and invalid value, then exit non-zero.
- The build writes to a temporary directory and replaces the output only after complete validation, preventing partial deployments.
- Missing optional metadata uses documented global defaults.
- Missing required metadata, source files, partials, or assets is a hard failure.
- External links remain normal links; their availability is reported during verification but a temporary third-party outage does not corrupt the build.

## Non-goals

- No browser-based CMS or custom admin panel.
- No runtime Python, server-side rendering, database, WordPress, Elementor-generated markup, analytics, or form backend.
- No automatic rewriting of dated business content or marketing claims.
- No pixel-perfect recreation of Wix-generated layouts.
- No repeated hand-edited header, footer, navigation, or floating-button HTML.

## Approaches considered

### Selected: Python standard-library generator

This offers one shared template, no runtime dependency, deterministic static output, GitHub-web editing, and clean Elementor-ready fragments. It adds a small build step, which GitHub Actions hides from ordinary client publishing.

### Rejected: Jekyll layouts and includes

Jekyll provides native GitHub Pages templating and Markdown, but local preview introduces Ruby/Jekyll tooling and its Liquid conventions are less approachable for this client than plain HTML plus a tiny documented generator.

### Rejected: JavaScript-loaded shared fragments

Runtime injection is initially quick, but the header and footer can disappear when JavaScript fails, page layout shifts during load, SEO/accessibility are weaker, and local `file://` or restrictive hosting can block fragment loading.

## Builder decisions

- Exact internal renderer class/function names.
- Exact visual composition of each migrated legacy section, provided content hierarchy and meaning are preserved.
- Which source images best support each legacy section, provided only relevant assets are migrated and all output images are WebP.
- Exact responsive breakpoints and per-image `object-position` after browser inspection.
- Whether repeated section inclusion uses a single include comment syntax or explicit registry declarations, provided page fragments remain easy to read.
