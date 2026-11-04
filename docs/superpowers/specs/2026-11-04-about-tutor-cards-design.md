# About Tutor Cards Design

## Intent

Shorten the About page’s tutor section and make each profile easier to scan without removing names, qualifications, links, portraits, or supporting credentials.

## Approved layout

- Render each tutor as one compact horizontal profile card.
- Keep the portrait and any certificate images in a left visual column.
- Keep the tutor name, qualification list, and profile link in a right details column.
- Use the existing Montserrat typography and green, charcoal, and white palette.
- Remove the fixed per-card heights and excessive vertical gaps.
- At mobile widths, stack the portrait above the details and retain every item of content.

## Approaches considered

- **Consistent portrait-left cards — selected.** Fastest to scan and visually stable as qualification lengths vary.
- Alternating portrait sides. More decorative, but creates an inconsistent reading path.
- Two-column tutor grid. Shorter overall, but makes long qualifications and certificates cramped.

## Constraints

- Reuse the existing PHP tutor renderer and vanilla CSS; add no dependencies.
- Preserve semantic headings, lists, safe external-link attributes, and meaningful image alt text.
- Do not alter the compact tutor presentation used on Home or programme pages.

