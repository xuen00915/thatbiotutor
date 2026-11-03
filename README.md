# ThatBioTutor WordPress

Portable WordPress implementation of the public ThatBioTutor site, based on the 3 November 2026 crawl. The tracked child theme owns the design and public behavior; ACF Free provides client-friendly content editing.

## Stack

- WordPress with PHP 8.1+
- Hello Elementor parent theme
- ThatBioTutor Hello Child theme
- Advanced Custom Fields Free

Elementor Pro, ACF Pro, and Code Snippets are not required. WordPress core, the parent theme, plugins, uploads, and databases are runtime dependencies and are not committed.

## Local verification

```bash
 npm install
 npm run wp-env:start
 npm run wp-env:setup
scripts/verify-local.sh
```

For a full live-reference comparison, run `npm run visual:capture -- --reference-base=https://www.thatbiotutor.com --local-base=http://localhost:8888 --output=.artifacts/visual/final --freeze`, followed by `npm run visual:review -- .artifacts/visual/final`.

Page-family presentation is isolated in `pages-home.css`, `pages-programmes.css`, `pages-testimonials.css`, `pages-resources.css`, `pages-institutional.css`, and `pages-intensivefix.css`. The theme uses an Arial/Helvetica font-licensing fallback because no redistributable licence for the captured proprietary font files was supplied.

See `docs/CLIENT-EDITING-GUIDE.md` for ACF Free editing and `docs/DEPLOYMENT.md` for guarded deployment. The child theme is the source of truth. No production hostname, live domain, DNS, or existing website is changed by this repository.

