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

