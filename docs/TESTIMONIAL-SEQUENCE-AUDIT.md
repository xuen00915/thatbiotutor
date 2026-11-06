# Testimonials sequence audit — 6 November 2026

Scope: content organisation on `/testimonials/index.html` only. The screenshot requests for centred videos, Google Review labels/portraits and extra spacing are deferred under the final sequence-only instruction.

## References and verification

- Authoritative original: the 3 November 2026 crawl, `pages/html/testimonials.html`.
- Live original checked: https://www.thatbiotutor.com/testimonials
- Existing published page checked before edits: https://xuen00915.github.io/thatbiotutor/testimonials/index.html

The original has 137 entries, including two identical Nicolas entries in 2023. After retaining one Nicolas, all 136 unique names, grade labels and complete quotes match the current content. Comparison normalised whitespace (including invisible Wix characters and the existing literal newline escapes in Jolie's stored quote); no words or grades were rewritten. The live original and the authoritative crawl agree for all 136 unique entries. Gwen's written name and quote are separate rich-text blocks on the original and were compared together.

All 61 student video IDs match the original in sequence and association; the featured video is also unchanged. Separate written and video entries for the same student remain separate. The original video-title shorthand (for example Emily versus Emily Heng) is not used to merge entries.

The fixture `tests/fixtures/testimonial-sequence.json` lists every entry in the verified order, with its stable seed key, full name/grade label and a SHA-256 fingerprint of its pre-change record. Only `menu_order` and `tbt_display_group` are excluded from the fingerprint. The contract test therefore prevents changes to text, grades, titles, source provenance, rating, media fields or record identity.

## Cohort counts

| Cohort | Before | Original | After |
|---|---:|---:|---:|
| 2025 | 16 | 16 | 16 |
| 2024 | 65 | 65 | 65 |
| 2023 | 25 | 26 including duplicate Nicolas | 25 unique |
| 2022 | 19 | 22 | 22 |
| 2021 | 11 | 8 | 8 |
| Total displayed | 136 | 137 including duplicate | 136 |

2025, 2024 and 2023 keep their entire existing sequence. Nicolas remains once, immediately before Edgar. No additional deduplication was performed.

## Before and after

### Class of 2022

Positions 1–19 are unchanged: Emily Chen, Nathaniel, Kaitlyn, Febella, Aspen, Sharilyn, Jessica, Tania, Janine, Tan Ching, Jolene, Tonee, Phyu Phyu, Glenna, Xinyu, Xi Ning, Le Min, Jen Lin, Elena.

| Student | Before | After |
|---|---|---|
| Isa — C5 to A1 in 3 months | 2021 #4 | 2022 #20 |
| Michelle — A2 for Pure Bio | 2021 #9 | 2022 #21 |
| Kwan Quan — B3 for Pure Bio | 2021 #10 | 2022 #22 |

### Class of 2021

Before: Jia Heng → Agnes → Nadya → Isa → Melissa → Shams → Emelia → Jolie → Michelle → Kwan Quan → Jasmine.

After:

1. Jia Heng — B3 for Pure Bio
2. Agnes — C6 to B4 in 1 month
3. Nadya — A2 for N Level Combined Bio
4. Melissa — A2 for Pure Bio
5. Shams — F9 to C6 for Pure Bio
6. Emelia — D7 to B3
7. Jasmine — A2 for Pure Bio
8. Jolie — C5 to B3 in 2 months

The original grade and duration labels are retained in full.

## Outstanding discrepancies, unchanged for manual review

- **Dyanne, 2024:** the video entry says C6 to A1; the separate written entry says B4 to A1. Both agree with their respective original entries. No choice was made between them.
- **Charmaine, 2024:** the video entry says C6 to A2; the separate written entry says E8 to A2. Both agree with the original. No grade was corrected.
- **Commenter portraits:** the original includes profile pictures, but the existing published Testimonials cards have no portrait associations. This predates this correction. No images were added or replaced because the final instruction limits this task to sequence changes.
- **Jolie formatting:** her existing stored quote contains literal `\n\n` characters. Its wording matches the original after whitespace normalisation. The stored quote remains byte-for-byte unchanged rather than mixing a formatting fix into this task.

## Implementation and design preservation

Only five records receive cohort/order overrides in `inc/reference-content.php`. Their seed keys remain unchanged, including Isa's historical `testimonial-2021-isa` key, so reseeding updates existing posts instead of creating duplicates. Normal seed ownership protection remains in place for client-edited fields.

No page template, renderer, CSS, JavaScript, image, video URL, header, footer, navigation, CTA or responsive rule changed. The exported HTML is generated from the existing WordPress template. The only expected visual difference is which entries appear in the two affected yearly panels and their order.

The local rendered before/after comparison also checks that the multiset of all 136 names, quotes, video URLs and portrait associations is unchanged. The browser test checks every cohort's full name/grade sequence at five viewport widths, alongside the existing layout and About Us width contracts.
