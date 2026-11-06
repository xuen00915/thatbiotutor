# Repository rules

This repository contains the portable ThatBioTutor WordPress source package.

- Treat the 3 November 2026 public crawl as the original-site reference.
- Keep the Hello Elementor parent theme unmodified.
- Put public presentation and behavior in `thatbiotutor-hello-child`.
- Use the `tbt_`/`tbt-` prefixes for PHP, options, handles, fields, and CSS.
- Register editable fields in PHP using ACF Free field types only.
- Keep page structure, navigation, responsive rules, and security in protected code.
- Do not commit WordPress core, plugins, uploads, databases, caches, credentials, backups, or generated runtime containers.
- Do not hardcode a preview, staging, or production hostname.
- Add a failing contract test before changing public behavior and run `scripts/verify-local.sh` before committing.
- Update `CHANGELOG.md` whenever behavior, content ownership, or operations change.


## Parallel sessions (two agents, one repository)

Two agent sessions may work at the same time. They must never share a working directory.

- **Session A — "fix page"** owns IntensiveFix only: `page-intensivefix.php`, `assets/css/pages-intensivefix.css`, `tests/intensivefix-page-test.php`, and the `intensivefix` entries in `inc/reference-content.php`.
- **Session B — "main website"** owns everything else: homepage, Classes, Sec 3, Sec 4, Results & Testimonials, Free Bio Notes, About, Our Causes, Contact, header, footer, navigation and the shared template parts.
- **Shared files** (change only the lines you need, rebase first): `assets/css/standard.css`, `components.css`, `base.css`, `layout.css`, `assets/js/site.js`, `inc/theme-helpers.php`, `inc/reference-content.php`, `template-parts/inquire-*.php`, the runtime tests under `tests/`, `CHANGELOG.md`, `.github/`. If you need a change in the other session's files, do not edit them: tell the user.
- Each session works in its **own git worktree and branch**. Session A uses the main checkout; Session B uses `../wordpress-main-site` on branch `site/main-website`.
- Before every commit run `git fetch origin && git rebase origin/main`, rerun the tests, then publish with `git push origin HEAD:main`. The push must be a fast-forward; never force-push. If it is rejected, fetch, rebase and test again.
- Stage only your own paths (`git add <paths>`, never `git add -A`). Leave `resources/source-manifest.json` untouched.
- `CHANGELOG.md`: add your entry at the top of **Unreleased** prefixed `[fix-page]` or `[main-site]`. If both sessions edited it, keep both entries.
- Local WordPress: Session A uses the default port 8888. Session B starts its own with `WP_ENV_PORT=8890 WP_ENV_TESTS_PORT=8891 npx wp-env start` and runs the browser tests with `TBT_RUNTIME_URL=http://localhost:8890`.
- Only `main` deploys. After pushing, wait for the GitHub Actions run to succeed and verify the live site. If a failure comes from the other session's commit, report it to the user instead of fixing their files.
