# Changelog

## Unreleased

- Aligned the IntensiveFix page with the reviewed Markup page: red "Chaotic Sec 4 Year" and green "4 DAYS" headline, green 91% sub-headline, "Secure My Spot In the Intensive Fix Class" hero button, all buttons linking to the sign-up band with the steps, refund terms and embedded lead form, the homepage closing copy, and the Inquire section order (programme details now sit just before sign-up).

- Restored the Inquire landing header (logo with ThatBioTutor wordmark and green booking button) on the homepage and IntensiveFix, solid white at the top and translucent white after scrolling; all other pages keep the navigation header.

- Rebuilt the IntensiveFix page in the Inquire design (shared hero, quotes, track record, FastTrack, proof, tutors, quality, practical, offer, suitability, founder, FAQ, classroom and closing sections) and applied the MarkUp review comments: "In Just 4 DAYS" headline, new bridge line, updated CTA labels linking to a new `#details` section with the fees, schedule and programme graphics, refund terms for the first lesson, a 4-Day stat, and spacing above the details button. The homepage's shared sections now live in `template-parts/inquire-*.php`. Existing sites keep stored IntensiveFix heading/button text until edited or re-seeded.

- Made the desktop navigation independent of JavaScript state so the header always shows Home, Classes, Results & Testimonials, Free Bio Notes, About Us and Our Causes above 980px.

- Fixed the homepage FAQ buttons turning Hello's pink after being clicked; focus and active states now keep the green palette.

- Restyled the homepage FAQ as a bordered, rounded list with green plus / dark minus icons and a bordered answer panel that opens and closes with a height animation.
- Added site-wide smooth effects: sections fade up as they scroll into view, links and buttons transition smoothly, and the FAQ animates; all of it is disabled under reduced-motion.

- Replaced the homepage guarantee icon with the supplied solid green shield and white check, without the circular backdrop.

- Replaced the homepage closing copy with the supplied text and moved its now-green booking button close to the paragraphs.

- Reduced the homepage guarantee shield to 88px and changed its booking button to green with white text.

- Changed the homepage content width from 1120px to the 944px FastTrack card width across every section, including the header and footer tracks; other pages stay at 1120px.

- Centered the shared desktop header so the logo and navigation sit in the middle of the page as one group.

- Replaced the homepage's compact landing header with the original site header (logo plus Home, Classes, Results & Testimonials, Free Bio Notes, About Us, Our Causes) shared by every page.

- Narrowed the FastTrack content track to the inquiry site's 992px (944px cards) while keeping the shared 1120px container.

- Rebuilt the homepage classroom gallery as three equal 363:453 cards in one row and matched the closing section to the inquiry site (864px inner width, 330x368 image, 24px heading, 18px copy).

- Restacked the homepage trial guarantee as a centered column: title, shield icon, guarantee text, then the booking button.

- Restored original-site content gaps: Charmaine's E8 to A2 written review (video-transcript matching now respects grade brackets), Sec 3/Sec 4 featured-testimonial grade badges, the original Sec 3/Sec 4 face-to-face FAQ wording, and the Notes "requesting for notes" wording.

- Removed the extra "Results, not promises", trial-guarantee, founder and FAQ eyebrow labels that the original homepage does not have.

- Matched the homepage header (64px, no border or shadow, 40px logo with ThatBioTutor wordmark), the "Fail" red and the body font stack to the original inquiry site without changing content widths.

- Removed the school-logo pause button while retaining automatic scrolling, hover pause and reduced-motion support.

- Expanded the homepage results image to the full 1120px content width while preserving its aspect ratio and centered alignment.

- Aligned homepage photos and circular tutor portraits, restored moving school logos with pause/reduced-motion controls, centered the results artwork, and rebuilt the original Google Reviews presentation with source review snapshots, scrolling and expandable text.
- Restored stacked Quality Control rows and outlined suitability cards, removed extra practical/classroom/tutor eyebrows, and replaced inherited pink FAQ interaction colors with green.

- Removed the remaining width caps from the homepage hero headline, refund text and track-record video so they fill the shared 1120px container.

- Expanded the homepage problem quotes, transition text and testimonial cards to fill the shared 1120px content container.

- Restored the original 1120px track-record width and statistic sizing, and applied that width to every homepage container, carousel, header and footer.

- Applied the 736px track-record content width consistently to every homepage section, hero carousel, header and footer, with 16px mobile gutters.

- Corrected the Home track-record section to Inquire’s 736px content width, with proportionate statistic typography and card spacing.

- Standardized page containers, headers, and footers to the Inquire reference’s 1120px content width and 16px side gutters across all 12 routes. Kept full-width section backgrounds and narrower reading/media limits.

- Rescaled the Home landing page to the reference's compact proportions: 60px/36px desktop typography, 36px/30px mobile typography, a 3:1 hero gallery, tighter section rhythm, and denser mobile result and tutor grids.

- Replaced the Home page with the Inquire landing-page structure, the client-supplied ordered hero, track-record, and classroom photography, animated figures, compact booking header, WhatsApp trial guarantee, and revised responsive layouts.

- Restored the About page's original Wix composition, typography, section spacing, exact tutor labels, founder-message rhythm, and missing beliefs photograph.

- Completed the full public-site visual fidelity pass with focused page-family stylesheets, four-width browser acceptance, local WebP-only media checks, and reusable reference-versus-local capture reports.
- Recovered the live year-by-year Results archive with all 61 student-story videos and restored all 15 full Cell Division reviews.
- Restored the exact transcript wording beneath all 61 student-story videos and the five missing text-only Class of 2021 reviews.
- Reconciled the Home, Classes, Sec 3, Sec 4, and Free Notes copy against the complete 4 November public-site crawl, restoring abbreviated programme details, FAQs, and reviews.

- Scaffolded the portable ThatBioTutor Hello Elementor child-theme repository.
- Defined protected-code and ACF Free content-ownership boundaries.
- Added the authoritative route/reference registry and source provenance manifest.
- Added an idempotent `wp tbt setup-site` command that preserves client edits and reports slug collisions.
- Added one protected ACF Free field group per page template, including fixed numbered media slots and Home-owned shared details.
- Added typed field and local-image fallback helpers that keep pages available when ACF is missing.
- Added private admin content types for testimonials, tutors, FAQs, and structured results.
- Added deterministic published-record queries and escaped reusable renderers for reviews, tutors, and FAQ accordions.
- Added a deterministic WebP conversion pipeline with SHA-256 deduplication, preserved transparency, explicit fit/focal metadata, and source provenance.
- Documented the Montserrat/system fallback and excluded captured proprietary font binaries pending licence confirmation.
- Added one sticky responsive header, protected Classes submenu, shared footer, and floating WhatsApp control.
- Matched the shared footer to the compact Inquire layout with its exact responsive height, 1120px desktop content width, four-column desktop grid, three-column mobile information row, and reference typography.
- Matched the Home page to the Inquire Poppins type system, pill CTA sizing, six-pixel green/white carousel pagination, transparent arrow controls, section surface sequence, heading alignment, and responsive title scale.
- Restored the Home problem-to-proof transition copy and matched the Inquire single-column prompt and testimonial-card layout across desktop and mobile.
- Matched the Home FastTrack and Student Stories sections to the Inquire 1024px container, green comparison headers, illustrated comparison cards, paired tablet videos, and single-row school crest treatment.
- Added dependency-free accessible mobile navigation, carousel, swipe, FAQ, focus-return, and reduced-motion behavior.
- Rebuilt the original Home section sequence with the supplied five-image hero, lower first-slide focal point, FastTrack, trust metrics, results, crest marquee, videos, experience media, tutors, FAQs, and CTAs.
- Added distinct original-style Classes, Sec 3, and Sec 4 templates with phase, topic, results, team, and FAQ content.
- Added IntensiveFix as a separate four-day recovery programme with ten ordered supplied graphics, review artwork, analysis video, FastTrack, suitability, and WhatsApp enquiry flow.
- Added grouped Results & Testimonials archives with the 61 public year-archive testimonial videos preserved from the authoritative crawl.
- Rebuilt Free Bio Notes with its slider, four benefits, proof, original Q&A sequence, editable reviews, and explicit access-form boundary.
- Preserved Cell Division as a clearly dated historical 2023 resource without inventing current access claims.
- Added native About, Contact, Our Causes, and Tuition Recommendations templates with authoritative copy, safe external links, shared tutors, and responsive institutional layouts.
- Added portable per-page metadata, same-site legacy redirects, conservative security headers, disabled comments/pingbacks, and clear fallback templates.
- Added client editing, collaborator onboarding, guarded deployment, staging migration, rollback, and launch-approval documentation.
- Added a pinned clean WordPress runtime, idempotency/route/editing/browser checks, and a read-only staging verifier.

### Verified local release environment

- WordPress 7.1.4, PHP 8.3, Hello Elementor 3.5.1, ACF Free 6.8.11.
- Node.js 22.23.4, `@wordpress/env` 12.0.0, Playwright 1.64.1, Docker 29.8.2.
- Static/unit verification passed; clean setup remained idempotent and preserved client/unrelated data.
- All 12 canonical routes returned 200, both aliases returned a single 301, and browser checks passed at 1440, 768, 390, and 320 pixels.
- Local preview: `http://localhost:8888` (while `npm run wp-env:start` is running).
- Final review fixes connected numbered ACF media to public templates, populated FAQ/result and preserved review collections, preserved intentionally blank edits, repaired WP-CLI collision reporting, synchronized navigation across viewport changes, removed archive record caps, and stripped workstation paths from the public media manifest.

