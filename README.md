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
scripts/verify-local.sh
```

The clean local WordPress runtime and browser-preview commands are added by the release-verification phase. No live domain, DNS, or existing website is changed by this repository.

