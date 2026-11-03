# Original route audit

Captured on 2026-11-03 from the live original and the local modified build at 1440 × 1000 and 390 × 844 CSS-pixel viewports. Full-page captures are stored outside the repository in `/tmp/thatbio-visual-20261103/`.

| Route | Original desktop height | Current desktop height | Primary mismatch |
| --- | ---: | ---: | --- |
| `/` | 19,973 | 15,399 | Header, hero, cards, section geometry, imagery, and large content gaps differ. |
| `/classes/` | 8,936 | 7,181 | Modern split/card sections replace the original narrow centred content and bars. |
| `/sec-3-pure-g3-biology-tuition/` | 15,342 | 8,707 | About half of the original long-form proof/resources flow is missing. |
| `/sec-4-pure-g3-biology-tuition/` | 15,533 | 8,610 | About half of the original long-form proof/resources flow is missing. |
| `/testimonials/` | 134,053 | 5,669 | The original extensive student story archive is almost entirely absent. |
| `/notes/` | 46,612 | 3,678 | Dark split campaign hero and four short sections replace the long original download/review page. |
| `/celldivision/` | 10,422 | 3,368 | Archived page has been heavily condensed. |
| `/about/` | 10,452 | 5,110 | Biography, tutor and belief content is abbreviated and restyled. |
| `/trees/` | 2,966 | 2,568 | Structure is closer, but typography, header, spacing, and media treatment differ. |
| `/tuitionrecs/` | 1,013 | 2,030 | A simple original utility page became a larger promotional layout. |
| `/contact/` | 1,216 | 1,897 | A simple original contact/FAQ page became a larger modern form-like page. |
| `/intensivefix/` | No original route | 11,343 | Must use the Sec 3/Classes visual vocabulary, not the current inquiry-style components. |

## Site-wide findings

- The modified header is a 60px modern header with a 34px icon, bold compact navigation, brand text, and a separate contact button. The original uses the full logo artwork and tall navigation segments with vertical rules.
- Arial replaced the original Futura/Avenir family.
- Dark heroes, pale-green panels, rounded cards, constrained 62rem containers, and large marketing headlines were introduced across the site.
- The original instead uses white space, narrow centred page content, full-width photography where present, charcoal title strips, small bright-green calls to action, and minimal radii/shadows.
- Many tests asserted only `data-layout-slot` labels, so abbreviated or visually unrelated sections still passed.
- All existing routes need rendered comparison, not just homepage and Notes.

## Correction status

The table above records the pre-correction baseline that triggered the site-wide rebuild; it is not the release state. The corrected build restores the original route-specific compositions, type family, segmented header, image treatment, and simple utility-page layouts. It also preserves the source site's complete identifiable review material in editable partials: 137 Testimonials entries, 228 Notes reviews, and 15 Cell Division reviews. Remaining full-page height differences are primarily the result of removing Wix runtime spacing and duplicated empty regions, not omitted review entries.

The release is browser-checked at 390, 768, 1280, and 1440 CSS pixels. Automated contracts cover shared header/footer/WhatsApp rendering, JavaScript-disabled mobile navigation, overflow, image loading, archive counts, accessibility contrast, and shared interactions.

