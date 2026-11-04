# Launch package

This directory contains the human approval gate for launch. It stores no credentials, keys, databases, backups, uploads, or host configuration.

Local readiness is checked with `scripts/verify-local.sh`. Staging is checked read-only with `scripts/verify-staging.sh <approved-url>`. A passing test is evidence, not launch approval.

The production hostname, DNS destination, mail provider, analytics property, consent requirements, and launch approval are pending until the owner supplies them.

