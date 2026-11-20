# Deployment

## Local verification

Requirements: Docker Desktop, Node.js/npm, PHP 8.1+, and Git.

```bash
npm install
npm run wp-env:start
npm run wp-env:setup
scripts/verify-local.sh
```

The setup command used inside WordPress is `wp tbt setup-site`. It is idempotent: it creates missing protected pages and seed records, reports slug collisions, and does not overwrite later client edits.

## WordPress upload package

See [WORDPRESS-INSTALL.md](WORDPRESS-INSTALL.md) for the theme ZIP, administrator-only dashboard setup and AIOS installation. Build with `python3 scripts/build_wordpress_package.py` after local verification. Third-party plugins are installed from WordPress.org, never bundled into the theme.

## Guarded staging deployment

1. Create and verify a full database and uploads backup.
2. Confirm the approved staging URL, credentials, maintenance window, and responsible approver.
3. Run a dry-run inventory of existing pages, plugins, users, uploads, and conflicting slugs.
4. Install the unmodified Hello Elementor parent, the tracked child theme, and ACF Free.
5. Activate the child theme, run `wp tbt setup-site`, then clear caches.
6. Run `scripts/verify-staging.sh <approved-url>` read-only.
7. Obtain explicit confirmation before any production migration.

Never copy WordPress core, plugin directories, credentials, `.env` files, databases, backups, cache, or runtime uploads into Git.

## Rollback

If verification fails, stop traffic changes, restore the verified database/uploads backup, reactivate the previously active theme, clear cache, and verify the prior site. Record the failed step and do not retry production without fresh confirmation.

The production hostname, DNS, transactional mail, analytics, cookie consent, and launch approval are pending. No production domain or DNS change is authorized by this repository.


## Fast browser verification and Pages deployment

Pages still runs all 29 exported-site browser suites before publishing. The runner
starts long suites first and runs up to three independent browser processes at once.
The WordPress admin-editing test runs separately after these read-only suites.
A failure in any suite blocks deployment. Logs and per-suite durations are uploaded
as `browser-test-results`; the Actions summary shows the slowest suites first.

Browser downloads are cached by OS, architecture and lockfile. CI installs only the
Chromium headless shell used by the tests, probes it, and installs system packages
only when that probe fails. A failed second probe blocks deployment.

For a small local change, run its relevant test before the full deployment suite:

```bash
TBT_RUNTIME_URL=http://localhost:8890 npm run test:browser -- notes-sections-runtime.js
```

For links, forms or navigation changes, check the **static export locally** before
pushing: export rewrites URLs and may behave differently from native WordPress.
Use a dedicated output directory (the exporter replaces its contents):

```bash
python3 scripts/export_github_pages.py --output .artifacts/pages-preview --local-origin http://localhost:8890 --public-origin http://localhost:4173
python3 -m http.server 4173 --directory .artifacts/pages-preview
# In a second terminal:
TBT_RUNTIME_URL=http://localhost:4173 npm run test:browser -- notes-sections-runtime.js
# Full read-only browser suite, identical to CI:
TBT_RUNTIME_URL=http://localhost:4173 TBT_RUNTIME_WORKERS=3 npm run test:browser
```

Set `TBT_RUNTIME_WORKERS=1` on a resource-constrained laptop. The runner accepts 1–8
workers and only registered read-only suites. Do not include mutable admin tests.
Keep all existing suites in `scripts/run-browser-tests.js` when changing the workflow.

The layout runner serves a minimal fixture for external YouTube embed documents.
Original iframe URLs, titles and dimensions remain tested; local scripts, images and
fonts are never intercepted. These suites do not test YouTube playback. Set
`TBT_LIVE_EMBEDS=1` to include live players when diagnosing an integration issue.
This isolation is loaded only in runner subprocesses, never in the website.

The GetResponse signup document is also isolated in layout-runner subprocesses. The form-container test verifies its exact provider URL and fallback link. Verify the live provider form separately without creating a test subscriber; `TBT_LIVE_EMBEDS=1` enables both real provider and video frames.
