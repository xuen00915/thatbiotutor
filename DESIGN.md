# Design system and architecture

The active ThatBioTutor Hello Child theme recreates the original website without Wix runtime dependencies. Hello Elementor is the unmodified parent; Elementor page-builder data is not the source of truth.

## Architecture

- `functions.php` only boots focused modules and theme support.
- `inc/reference-content.php` owns approved routes, navigation, fallbacks, and seed data.
- `inc/acf-fields.php` registers ACF Free editing fields.
- Private content types own repeated testimonials, tutors, FAQs, and results.
- Protected PHP templates own page structure and consume typed fallback helpers.
- Local CSS and dependency-free JavaScript own responsive layout and progressive interactions.
- Theme-owned raster media is local WebP; third-party YouTube videos remain embeds.

## Fidelity boundary

The 3 November 2026 crawl of `www.thatbiotutor.com` is authoritative for original pages. IntensiveFix is additional approved scope and uses the closest original Classes/Sec 3 visual vocabulary. Proprietary captured fonts are not redistributed without a licence; open Montserrat and documented system fallbacks are used instead.

