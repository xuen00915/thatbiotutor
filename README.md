# ThatBioTutor editable static website

This repository contains a plain, client-editable source for all 12 ThatBioTutor public pages. A small dependency-free Python generator adds the shared header, footer, navigation, and floating WhatsApp button, then writes the publishable website to `build/`.

## Preview in one command

From the repository root, run:

```sh
python3 scripts/preview_site.py
```

Open `http://127.0.0.1:8765/` and press `Control-C` when finished. Use `--port 8000` if another port is needed. The command always rebuilds `.preview-site/` first, so the preview reflects the editable source.

## Generated routes

| Page | Route | Editable source |
| --- | --- | --- |
| Home | `/` | `site/pages/home.html` |
| Sec 3 IntensiveFix | `/intensivefix/` | `site/pages/intensivefix.html` |
| About | `/about/` | `site/pages/about.html` |
| Contact | `/contact/` | `site/pages/contact.html` |
| Causes | `/trees/` | `site/pages/trees.html` |
| Tuition recommendations | `/tuitionrecs/` | `site/pages/tuitionrecs.html` |
| Classes | `/classes/` | `site/pages/classes.html` |
| Sec 3 Pure/G3 Biology | `/sec-3-pure-g3-biology-tuition/` | `site/pages/sec-3-pure-g3-biology-tuition.html` |
| Sec 4 Pure/G3 Biology | `/sec-4-pure-g3-biology-tuition/` | `site/pages/sec-4-pure-g3-biology-tuition.html` |
| Free Biology notes | `/notes/` | `site/pages/notes.html` |
| Cell Division archive | `/celldivision/` | `site/pages/celldivision.html` |
| Results and testimonials | `/testimonials/` | `site/pages/testimonials.html` |

The older `/s3-intensivefix/` route is generated as a legacy alias to `/intensivefix/`. The older `/classes/sec-3-pure-g3-biology-tuition/` path is also generated as an accessible legacy alias to the current Sec 3 page.

## Source and generated files

- Edit `site/`, `README.md`, or `docs/`.
- Do not edit `build/` or `.preview-site/`; both are generated and ignored by Git.
- Shared business details and navigation live in `site/site.json`.
- Page route and metadata records live in `site/pages.json`.
- Shared site-wide templates live in `site/partials/`.
- Page content lives in `site/pages/`.
- Every local image is WebP under `site/assets/images/` and documented in its asset manifest.

See the [client editing guide](docs/client-editing-guide.md) for safe edits and the GitHub web-editor workflow. The separate [Elementor migration map](docs/elementor-migration-map.md) maps the native source to WordPress Theme Builder templates and widgets.

## Build and verify

```sh
python3 scripts/build_site.py --output build
python3 -m unittest tests.test_site_builder tests.test_static_site -v
python3 -m py_compile scripts/build_site.py scripts/preview_site.py
node --check site/assets/js/content.js
node --check site/assets/js/site.js
git diff --check
```

Pull requests run the same build and validation. Pushes to `main` additionally publish only the generated `build/` directory to GitHub Pages.

## Preservation boundary

The captured Wix reference remains untouched in `mirror/www.thatbiotutor.com/`. The separate original snapshot at `/Users/dmsyx/Documents/Projects/thatbiotutor-original` is also preservation-only. Public-site changes belong in `site/`; neither preserved copy should be edited unless an archive repair is explicitly requested.
