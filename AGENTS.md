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

