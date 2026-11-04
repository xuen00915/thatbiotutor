# Staging-to-final migration

This runbook begins only after the owner names an approved staging site and gives launch approval.

## Before migration

- Record exact staging and final URLs; the production hostname remains pending until approved.
- Take a tested database and uploads backup and retain the previous release/theme.
- Freeze content changes or record a final delta window.
- Inventory DNS TTL, SSL, mail delivery, forms/WhatsApp, analytics, consent, redirects, caches, and search visibility.
- Run a URL-replacement dry-run. Serialized WordPress data must be changed with WordPress-aware tooling, never raw SQL replacement.

## Migration

1. Obtain explicit confirmation for the migration window and target.
2. Copy only approved database/uploads data and deploy the parent theme, child theme, and ACF Free.
3. Replace the staging origin with the final origin using safe tooling.
4. Run `wp tbt setup-site`; it must report no destructive collisions.
5. Flush permalinks and caches, then run `scripts/verify-staging.sh <approved-url>`.
6. Verify Home, all internal routes, legacy 301s, media, YouTube, WhatsApp, mobile navigation, metadata, and admin editing.
7. Enable indexing only after final launch approval.

## Rollback

If a critical check fails, restore the prior database/uploads backup and release, reverse the traffic change, clear all caches, and verify the prior site. Keep the failed release offline until corrected and re-approved.

DNS, production mail, analytics, consent, and final launch approval remain pending external decisions.

