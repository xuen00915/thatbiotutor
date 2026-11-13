# Changelog

## Unreleased

- [main-site] One design across the site, using the homepage and IntensiveFix as the template: every page now uses Poppins everywhere (the Montserrat and Helvetica titles on Sec 3, Sec 4, Results & Testimonials, Free Bio Notes and About are gone); every page title is Poppins 800 at 60px (36px on phones) and every section heading Poppins 800 at 36px (30px on phones); all dark bands and panel headers use the same forest green #0e371d (Sec 3 / Sec 4 teal and the near-black Classes band are replaced); green text on light backgrounds is always #2f8a27 and on dark backgrounds #8fe17e; body text is the site ink, never pure black. Contact, Tuition Recommendations and Our Causes now open with a centred title with a green accent word and no dark bar (no new wording was added); About and Our Causes section headings follow the same style instead of dark bars. Short pages (Tuition Recommendations, Contact) no longer show a white strip under the footer. Content width stays the shared 1120px. Covered by `tests/unified-design-runtime.js`; the older per-page tests that asserted the previous "original look" (Montserrat, 45-65px titles, teal panels, dark title bars) were updated to the new design.

- [main-site] Homepage "Trusted by students from 98 schools": the scrolling school crests are larger (about 140px boxes, seven across on desktop, five or six on tablet, three on phones, up from about 100px) and every crest now sits in an identical square box, never cropped or stretched.

- [main-site] Homepage "Don't just take our word for it": the second video (Alicia's Story) is replaced with https://www.youtube.com/watch?v=V4dj5XYEcow (Gwen's Story). The first video and the Results & Testimonials page are unchanged.

- [main-site] Videos, as instructed by the owner: the IntensiveFix "Here's how we make that happen." video is https://youtu.be/bwMcGJcP0ts; the homepage section of the same name keeps the original video (q_IPfEdODqI). Earlier reverts of these video swaps are intentional history; this is the final intended state.

- [main-site] Videos restored to the originals: IntensiveFix "Here's how we make that happen." plays the original "The missing key to Biology improvement" video again, and the homepage "Don't just take our word for it" second video is Alicia's Story again.

- [main-site] Image sliders: every slider now has 18px rounded corners (the homepage hero slider and the About page certificate slider were square / 12px; the others already matched). Covered by a test across all pages at desktop and phone width.

- [main-site] Header (all pages, IntensiveFix included): on desktop the menu is now centred in the header, with the logo on the left and the Contact Us button on the right.

- [main-site] IntensiveFix, from the client's review comments: the problem bridge now reads "Sound familiar? After The IntensiveFix Program, They're Telling Us:" (the "Many TBT students started exactly here" and "But after our 8-week IntensiveFix" lines are deleted), and the About The Founder, FAQ and Life inside a TBT classroom sections are removed from this page only (the homepage keeps them via the shared `inquire-sections-end.php` skip list). Edited at the user's request outside the usual page ownership; `tests/intensivefix-page-test.php` updated to match.

- [main-site] The Classes dropdown now highlights the page you are on (Sec 3 or Sec 4 gets `aria-current="page"`, the site green text and the light-green tint). Slider dots no longer flash the parent theme's red-pink when hovered, focused or clicked (homepage, IntensiveFix, Free Bio Notes and Cell Division sliders keep their own colours).

- [main-site] IntensiveFix now uses the same shared header and website menu as every other page (the separate Contact Us-only landing header is gone); its Contact Us button still opens WhatsApp with the IntensiveFix message. The floating WhatsApp button now uses the supplied logo (`assets/icons/whatsapp-logo.png`, ring and phone filled white). Updated the header contract/runtime tests.

- [main-site] Client feedback round: (1) every page now has the green "Contact Us" button at the top right of the header (same WhatsApp link as the IntensiveFix header button; on phones it sits beside the menu button). (2) The Classes dropdown now matches the menu: same font and ink colour, soft green-grey border, 12px rounded corners, soft shadow and a light-green hover. (3) The selected menu item is now the site green (#2f8a27, same as the green text on the pages) instead of the light lime. (4) Green text on dark green sections is one shared light green (`--tbt-green-on-dark`, #8fe17e, the "Full Refund" colour) on the homepage, IntensiveFix, Classes, Sec 3, Sec 4, Free Bio Notes, Cell Division and the footer headings. (5) The homepage slider keeps the teacher's whole head in view (photo 2 now crops lower down the picture).

- [main-site] Track-record section: cut the gap above "Here's the track record:" (16px top padding instead of 80px) and smoothed the stat count-up (2.2s ease-in-out timed from the first painted frame instead of a front-loaded 1.2s cubic ease-out). Reduced-motion users still see final values immediately.

- [main-site] Homepage: reduced the space between the header and the hero title (top padding 96px to 24px, and 64px to 16px on phones up to 520px), so the title now sits about 72px closer. The rule is scoped to the homepage; IntensiveFix keeps its own hero spacing. Added a browser check for the header-to-title gap.

- [main-site] Header: "Free Bio Notes" now wears a hand-drawn red double-stroke circle on desktop to draw attention to the free resource. It is a decorative inline vector (no image file, no pointer events), shown on every page; the link gets a `site-nav__link--circled` class and 20px side padding on desktop. The mobile menu list is unchanged. Added PHP and browser checks for the class, the circle and header height/overflow.

- [main-site] Our Causes, section 1: on desktop the heading and paragraphs under the two pictures are now centred (paragraphs capped at 860px for readable lines; phones and tablets stay left-aligned), and the empty space below the text was cut from 128px to 64px on desktop and from 96px to 48px on tablets and phones. Wording, images and links are unchanged.

- [main-site] Our Causes, section 1 (Planting Trees): on desktop the planet picture and the donor-search picture now share one row at the same height, with the heading and paragraphs underneath both. Phones and tablets stack planet picture, donor-search picture, then text. Wording, images and links are unchanged; the browser test now expects the new picture sizes.

- [main-site] Our Causes, section 2 (SG Environmental Causes): on desktop the Giving.sg picture now sits beside the wording, matching section 1; on phones and tablets the order stays text first, picture last. Wording, images and links are unchanged; the browser test now expects the 550px-wide picture.

- [main-site] Our Causes, section 1 (Planting Trees): on desktop the planet picture now sits beside the text with the donor-search picture centred below; heading and paragraphs are all left-aligned (the heading was centred over left-aligned paragraphs). Phones and tablets keep the stacked order. The donor-search picture moved out of the text container in `page-trees.php`; wording, images and links are unchanged.

- [main-site] Aligned the shared desktop header menu to the right edge of the content track (logo stays left) instead of centring it. Mobile menu and the IntensiveFix landing header are unchanged; the header browser test now checks the menu's right edge instead of its centre.
- [main-site] Extended the programme polish across all public pages through shared CSS: consistent tactile buttons, navigation underlines, keyboard focus, branded selection and form caret/focus states, highlighted open FAQs and carousel feedback. Preserved page content, geometry and still tutor cards; applied reduced-motion alternatives. Added desktop/mobile coverage for all 12 routes.

- [main-site] Polished Sec 3 and Sec 4 with a one-time outcome underline, tactile pill and carousel controls, explicit keyboard focus, and softly highlighted open FAQ answers with animated SVG chevrons. Preserved original copy, layouts, colours and still tutor profiles; reduced-motion users receive a static outcome highlight. Added keyboard and motion-preference browser coverage.

- [main-site] Increased standard and IntensiveFix headers to a shared 80px height on desktop, tablet and mobile. Removed the narrower-screen height override and connected the landing header to the shared height setting; added browser checks for equal heights before and after scrolling.

- [main-site] With the owner’s explicit approval for this scoped IntensiveFix change, made the final enrolment button fit tablet and phone widths with centred 16px text, 20px side padding and a 52px minimum tap height. Kept the label, destination and desktop styling unchanged. Added seven-width browser coverage.

- [main-site] Added distinct, page-specific SEO titles and descriptions for all 12 public pages, with optional ACF Free SEO overrides and matching Open Graph metadata. Removed duplicate brand suffixes and core canonical tags on protected pages. Documented the complete copy in `docs/SEO-METADATA.md` and included it in the WordPress handoff; added live metadata checks to CI.

- [main-site] Prepared a native WordPress upload package: reproducible tracked-theme ZIP builder, CI download artifact and installation guide, plus an administrator-only, nonce-protected Appearance → ThatBioTutor Setup screen that preserves client edits. Documented separate AIOS installation and host-specific security/email steps. Corrected the Contact Form 7 radio field tag in the setup template and guide, and prevented the plugin from initializing the unconfigured fallback form with an invalid form ID. Added package-isolation and dashboard-authorization contracts.

- [main-site] Reduced the desktop Classes dropdown from 260px to 180px wide, rounded its corners to 10px and tightened each link while retaining 44px touch targets. Mobile navigation is unchanged; the navigation runtime test covers the compact dimensions.

- [main-site] Removed the standard header menu’s vertical divider lines. Testimonials now separates each new cohort’s results with 48px above (32px on phones/tablets), plus 32px below the portrait result artwork (24px on phones/tablets), without changing content or widths. Existing runtime tests guard both refinements.

- [main-site] Testimonials: added a collapsed, accessible floating year navigator with active-year tracking, centred the student videos, and restored 58 source reviewer portraits with a Google Reviews heading per cohort; older reviews without source photos use initials. All 136 reviews retain their text, grades, media and sequence. Standard headers now place the logo at the left content edge and centre the desktop menu (IntensiveFix unchanged). Classes now places its definition image beside the paragraph and paired Sec 3 / Sec 4 topics. Responsive runtime contracts cover these changes.

- [main-site] Classes: centred the “Why students struggle” label and heading, and widened the heading to the full shared content width. The Classes runtime test checks centring and title width at five viewport sizes.

- [main-site] Sec 4 now uses the finished Sec 3 presentation while retaining the original Sec 4 copy, three syllabus phases, eight topics, lesson points, reviews and 12 FAQs. Restored the original ADH lesson video, result tables, classroom statistics photo, review portraits, three practical photos and eight benefit icons. Moved the joining note inside Phase 3, joined the topics card, centered the option headings, highlighted the hero copy, and aligned widths, colours, tutor layout and controls with Sec 3.
- [main-site] Testimonials sequence only: moved Isa, Michelle and Kwan Quan after Elena in Class of 2022 (22 entries), and placed Jasmine before Jolie in Class of 2021 (8 entries). Preserved all 136 records, text, grades, media and existing design; retained one Nicolas in 2023. Added full-cohort sequence/content-integrity contracts and the audit in `docs/TESTIMONIAL-SEQUENCE-AUDIT.md`, including unresolved source grade conflicts and pre-existing missing portraits.

- [main-site] Sec 3 mobile FAQ carousel replaces the two rows of dots with a single current / total counter between the arrow controls; desktop retains its dots.

- [main-site] Sec 3 FAQ background matches the supplied warm off-white (#faf9f5); tutor profiles no longer lift or show shadows on hover; Book a Trial has no arrow.

- [main-site] Sec 3 follow-up: shared pill style for More Testimonials without an arrow, extra breathing room before the practical heading, four tutor profiles per desktop row, and FAQ slider controls outside the artwork (below it on mobile).

- [main-site] Refined Sec 3 from supplied screenshots: padded natural-height class headers, single-line included benefits, compact black-outlined reviews with aligned portraits and bottom-aligned names, larger practical heading and smaller lesson card headings. Results, video, benefits, quality control and FAQ now fill the shared About Us content width. Added all 12 supplied FAQ graphics in order after the answers, with carousel navigation and responsive checks.
- [main-site] Testimonials now uses the About Us content width and margins throughout: hero, featured video, every result graphic and yearly review panel. The page test compares all section widths directly with About Us at five screen sizes. Moved the legacy review-expansion interaction checks to Cell Division, which still uses those buttons.

- [main-site] Sec 3 page round 2, from the original-site screenshots: Class Options, What We Cover and FAQ have a centred eyebrow and title; every list uses a drawn tick icon instead of a text check glyph; the "students can join any time" note is inside the Phase 2 card; Sec 3 Topics title and list share one card; Exam Techniques has ticks, bold keywords and its note in the card; "TBT By The Numbers" uses the original class photo (lighter overlay, light-green numbers); the three reviews show their profile pictures (and the Dayan review's paragraph break is fixed); the eight "Everything your child gets" cards have their original icons (cut from the original icon sheet); lesson and card titles use Montserrat like the original; the team shows name then round photo with no role or bio lines (Mr Keefe (Founder), Ms Ng (Ex-MOE Teacher & Syllabus Reviewer)); the quality-control box matches the original; the video iframe carries its own 960x540 size; the FAQ has an image slider (one slide for now, the other slides are not in our media library yet). Excluded the cropped benefit icon sheet from shared photo rounding. `tests/sec3-page-runtime.js` covers these updates.
- [main-site] Results & Testimonials: restored the Montserrat hero title with aligned gold stars, tightened section spacing, added growth icons to the 2022/2021 headings, and styled Student Stories headings. All five yearly review lists now show every review inside a keyboard-accessible scrolling panel that opens at four full reviews. Fixed horizontal column spill in those panels and retained white transcript-card backgrounds. Added `tests/testimonials-page-runtime.js` to CI with desktop, tablet and phone coverage down to 320px; documented the page-specific typography and spacing in the shared design checks.

- [main-site] Free Bio Notes review panel fix: the first review list was a flex column, so the panel's height limit squashed the reviews to ~38px and their text overlapped. Panel lists are now plain blocks whose reviews keep their full height (the panel opens at exactly four full reviews), and the panel re-measures if the first reviews change size later. `tests/review-panel-runtime.js` now also checks for squashed/overlapping reviews and a 1250px window.

- [fix-page] Sec 3 page matched to the original site (done in this tab while tab 2 was busy): the hero uses the original Montserrat type, a sentence-case label, the light green (#8de15e) highlights on "Start of Sec 3.", "Now!" and two phrases of the lead line, the original heading wrap, and a lighter overlay; Class Options has teal header bars (#085d49), check marks with bold keywords, and a teal "Both formats include" block; What We Cover and Everything Your Child Gets use the original teal, with bold keywords, topics 1-5 down the left column and no notch between the Sec 3 Topics title and list; the Hall of Fame now shows the two original result tables (2024 left, 2025 right, uncropped) instead of classroom photos; the lesson section now embeds the original YouTube video (e55iPGQL6ng, privacy-friendly youtube-nocookie) instead of leaving a 730px blank gap, with its three practical photos shown; light-green accents on the dark sections. New `tests/sec3-page-runtime.js` (also in CI). Not changed: Sec 4 (it still uses the shared hall images), and the testimonial cards' avatars and grade headings.

- [fix-page] Classes "What makes our classes unlike any other": big heading (up to 48px) with "unlike any other" in green, italic label, much less space down to the picture, and the picture sits above the four points again (it had dropped below them); "Real Practical Sessions. Real Practice." is up to 40px. `tests/classes-page-runtime.js` covers size, colour, spacing and the heading text.
- [main-site] Free Bio Notes: the closing DOWNLOAD button no longer sits right under the "Show 12 more / Show all 211 reviews" buttons (it looked like a third review button). It is now its own dark band after the reviews, with the line "Get the free notes and start studying smarter." and a centred DOWNLOAD button (still linking to the download block). `tests/notes-sections-runtime.js` guards it.
- [main-site] Free Bio Notes reviews now live in a card-style panel that scrolls on its own: both review sections hold every review, open on the first four (the script measures four reviews), keep the page still while you scroll inside (a scroll shadow shows when there is more, the panel is keyboard-focusable and labelled), and no longer use the "Show 12 more / Show all" buttons on this page. A plain centred DOWNLOAD button closes the last section (no extra phrase). Other pages keep their "Show more" buttons, now forest green. `tests/review-panel-runtime.js` guards the panel (and runs in CI).

- [main-site] The review expand buttons ("Show 12 more reviews" / "Show all 211 reviews"), on every page that has them, are now forest green (filled and outlined) instead of the bright green used by DOWNLOAD and the other action buttons, so they no longer look alike; hover lightens to a mid green. `tests/notes-sections-runtime.js` guards it.

- [fix-page] Classes "What makes our classes unlike any other": big heading (up to 48px) with "unlike any other" in green, italic label, much less space down to the picture, and the picture sits above the four points again (it had dropped below them); "Real Practical Sessions. Real Practice." is up to 40px. `tests/classes-page-runtime.js` covers size, colour, spacing and the heading text.
- [fix-page] Classes page round 3: "Common questions" has a white background; section padding is 52px (40px on tablet/phone, was 64/48) and the "What makes our classes unlike any other" section now flows top to bottom instead of sitting in a tall fixed box, which removes the ~120px holes between its heading, picture and list; the FAQ questions no longer get cut off on phones (the parent theme's buttons never wrap) and show a green plus/minus icon, with no pink on hover; the WhatsApp help link has a taller tap area. `tests/classes-page-runtime.js` checks white FAQ, padding, biggest empty gap per section, FAQ fit and hover colour at 1440/1024/768/390/320.
- [main-site] Free Bio Notes: a centred DOWNLOAD button (linking to the "How do I download it?" block, like the other two) now closes the last "What Students Are Saying..." review section. `tests/notes-sections-runtime.js` guards it.

- [main-site] Free Bio Notes: the hero's DOWNLOAD button and "[7,000+ downloads]" are centred and stacked again (not side by side) and only the title and the tablet picture must share the first screen (the button may sit just below the fold; the picture still grows with the window, up to 760px tall); the DOWNLOAD button under "How do I download it?" is centred. `tests/notes-hero-runtime.js` and `tests/notes-sections-runtime.js` guard it.

- [fix-page] Classes page round 2: lighter hero overlay (56% to 30% black); the Sec 3 / Sec 4 topic lists are as wide as the paragraph above them; "Why students struggle" has a left-aligned eyebrow and title, bigger italic headings with green keywords (Too much content / unpredictable / disconnected / limited) and its own near-black background so it no longer blends into "How TBT solves each one"; the four solution cards use the shared 1120px width in two columns; the practical text uses the same 920px measure as the paragraph above; the result numbers are white; the Sec 3 / Sec 4 level cards are white with dark text instead of green on pale green. `tests/classes-page-runtime.js` covers each.
- [main-site] Free Bio Notes polish: (1) the tablet picture is bigger — the button and "[7,000+ downloads]" share one row on wider screens and the slider can grow up to 760px tall (600px wide) with the free window height; (2) italic text is now really italic site-wide (`base.css` `font-synthesis: style`; italics were declared but not drawn, e.g. the "If you answered 'yes'…" line, the About founder signature); (3) the four "ThatBioTutor's Notes" benefits are cards, two to a row (one per row on small phones); (4) the dark "Why free / catch / download" headings are lighter (30px bold, 24px on phones, they were 36px extra bold); (5) the email form follows the original look and fields: First Name *, Email Address *, Level of Student * (four radio choices), "I am a Parent" checkbox and a centred SUBMIT button, in Montserrat with thin outlines. `tbt_cf7_notes_template()` and `docs/CONTACT-FORM-7.md` carry the matching Contact Form 7 template and mail fields (first-name, your-email, level-of-student, is-parent).

- [main-site] Free Bio Notes hero slider: the white card behind the tablet picture is gone (the picture sits straight on the dark hero), the slider arrows are 44px circles placed beside the picture, and the dots are small (10px, they were 44px white discs); the title no longer overflows on mid-size windows (4.4vw, max 45px) and the title, picture and DOWNLOAD button now share one screen down to about 510px of window height. `tests/notes-hero-runtime.js` covers 1792x1056 down to 360x740 and 864x510.

- [fix-page] Classes page (done in this tab while tab 2 was busy): the "Why students struggle" and "By the numbers" labels were dark green on dark green (contrast 1.9) and are now bright green; every section label and heading is centred (some were left-aligned); the "WhatsApp Us Now!" button is centred under its text; the three practical-session photos, which were hidden and left a ~300px blank gap, now show with rounded corners. New `tests/classes-page-runtime.js` (also in CI) checks label contrast, centring, photo visibility and no sideways scroll at 1440/1024/768/390/320.
- [main-site] Free Bio Notes now has its email form, ready for Contact Form 7: a roomy brand-styled email field and "Send me the notes" button replace the blank 510px gap under "Simply Enter Your Email Below". New "Form Shortcode" field on the page (ACF text field `notes_form_shortcode`): paste a `[contact-form-7 id="…" title="…"]` shortcode and the plugin's form is shown instead of the stand-in (only a single contact-form-7 shortcode is accepted; otherwise the stand-in stays). The styles target Contact Form 7's own classes, so the look is identical before and after installing it, and a successful send returns to the homepage (`wpcf7mailsent` listener in `site.js`). The stand-in opens the visitor's email app until the plugin is connected. Setup steps and the paste-in form template are in `docs/CONTACT-FORM-7.md` (also `tbt_cf7_notes_template()`); `tests/notes-form-test.php` and `tests/notes-form-runtime.js` guard it (the runtime test runs in CI). The plugin is not committed.

- [main-site] Free Bio Notes: the "Hey Pure Bio Student, is this you?" title is now a contained, centred rounded panel at the page content width (it ran edge to edge with off-centre text), and the sections around it and the dark "Why are you giving your notes for FREE? / What's the catch? / How do I download it?" blocks keep about 32px (20px on phones) of space instead of 64px, so each gap fell from ~128px to under 64px. `tests/notes-sections-runtime.js` guards it (and runs in CI). The blank 510px area under "Simply Enter Your Email Below" is untouched.

- [main-site] Free Bio Notes hero restored to the original look and made to fit one screen: Montserrat 45px title, italic "For Pure / G3 Biology" line with only the audience in green, and a preview slider that shrinks with the window height so the whole hero (DOWNLOAD button and downloads count included) sits in the first screen on laptops and phones. The Free Bio Notes title is a fourth documented exception in `tests/standard-pages-runtime.js`; `tests/notes-hero-runtime.js` guards it (and runs in CI).

- [main-site] The header's Classes item now shows a clear SVG chevron drop-down arrow (it was a tiny thin text glyph), on desktop and in the phone menu; it turns over while the menu is open. `tests/nav-arrow-runtime.js` and `tests/navigation-test.php` guard it.

- [fix-page] Phone and tablet pass over the whole IntensiveFix page (the shared sections also apply to the homepage): FAQ questions were cut off on phones with their plus icons pushed off-screen (the parent theme's buttons never wrap), so they now wrap inside their row; the closing photo has clear space above its heading; the Contact Us button text is 14px; the review link has a comfortable touch height (the footer keeps the original Inquire layout). `tests/home-polish-runtime.js` checks FAQ fit and the closing gap at 1440/768/390/320.
- [main-site] About spacing tightened: about 56px between sections (it added up to ~200px) and 32px under each title bar on desktop; about 40px between sections and 20px under each title bar on phones, with smaller phone heroes, tutor cards and founder cards (17px text) so visitors scroll about 12% less. About is a third exception to the standard section padding in `tests/standard-pages-runtime.js`; `tests/about-page-runtime.js` guards the gaps.

- [main-site] About: Ms Chelsea's LinkedIn link is now a LinkedIn-blue icon button ("LinkedIn" with the logo, 44px tall, labelled "Ms Chelsea on LinkedIn" for screen readers) instead of the raw address. Other profile links stay plain text links. `tests/tutors-test.php` guards it.

- [fix-page] FastTrack icons made smaller again (42px wide on every screen, at most 48px tall) while still starting level with their phrase.
- [main-site] About tutor cards: the certificate viewer column is now exactly as tall as that tutor's words (desktop) and the certificate picture fills it, so it is larger (it was a fixed 286px box); on phones the viewer is 340px tall. `tests/about-page-runtime.js` guards it.

- [main-site] About: "Quality Teaching Only" and "What We Believe In" now show the words on the left and the photo on the right (desktop), with the photo exactly as tall as the words; they stack on tablets and phones. The founder's three answers sit in cards: questions 1 and 2 side by side, question 3 across the full width underneath (stacked on small screens). On "What We Believe In" the photo sits closer to the words (a wider photo column). `tests/about-page-runtime.js` guards the layout.
- [fix-page] FastTrack icons made smaller (88px wide, 72px on phones, at most 96px tall) while still starting level with their phrase.

- [fix-page] FastTrack icons now start level with the first line of their phrase: the five artwork files (content, memory, answering, practical, support) had 25-35% empty margin built in, which I trimmed so the visible icon begins at the top of the phrase (homepage and IntensiveFix). `tests/home-polish-runtime.js` checks the artwork has no empty margin. The founder section is back to its previous layout (the photo-balancing attempt cropped the photo too much).

- [fix-page] Pictures can no longer be dragged on any page (it was only IntensiveFix, so the homepage FastTrack pictures still dragged). (The founder-photo balancing tried in this change cropped the photo too much and was reverted.)
- [main-site] About: "Quality Teaching Only" and "What We Believe In" now show the words on the left and the photo on the right (desktop), with the photo exactly as tall as the words; they stack on tablets and phones. The founder's three answers sit in cards: questions 1 and 2 side by side, question 3 across the full width underneath (stacked on small screens). `tests/about-page-runtime.js` guards the layout.

- [fix-page] FastTrack pictures are larger and each is as tall as its own phrase, starting level with it; pictures on the IntensiveFix page can no longer be dragged (non-draggable images, cancelled dragstart, no touch-callout or selection).
- [main-site] About certificate viewers lose their frame line and the arrow/enlarge button borders, and the arrows now sit beside the certificate instead of on top of it. The "A Message From The Founder" photo is now as wide as its title bar and sits 40px below it (28px on tablets and phones), the same gap as the Our Tutors cards. `tests/about-page-runtime.js` guards both.

- [fix-page] IntensiveFix bridge text: bold "Sound familiar? Many TBT students started exactly here." with the grey line "But after our 8-week IntensiveFix, they’re telling us:" beneath it. The FastTrack heading follows the reference colours on the homepage and IntensiveFix (green eyebrow, white headline and lead-in, brand-green A1/A2).

- [main-site] Restored the original About page look that the site standard had flattened: Montserrat instead of Poppins, the 65px hero heading, a 40px "SUCCESS TO US" headline and 22px reading text on desktop, and the hero photo back at its original 78% opacity (the standard's image fade-in was ending at 100% and darkening it; hero brightness now matches the 3 November crawl). The reading width stays the shared content width and the soft highlight colour is unchanged. `tests/standard-pages-runtime.js` allows these About values; new `tests/about-page-runtime.js` guards them.

- [main-site] On Our Causes the "1. Planting Trees!" bar now uses the same 36px size as "2. SG Environmental Causes" (it was the 60px page heading), so both bars match. This is the one page where the standard h1 size is relaxed; `tests/standard-pages-runtime.js` expects 36px there and `tests/causes-page-runtime.js` checks the two bars match.

- [fix-page] Restored the grey bridge line under the IntensiveFix "Sound familiar?" title ("But after learning the hard way that it pays to fix your foundation early, they’re saying:"), as on the homepage.

- [fix-page] IntensiveFix programme graphics now change every 3 seconds and have rounded corners on the pictures themselves (frame no longer letterboxes them); the three phrases under the slider are now the supplied "There's WAY too much to memorise..." quotes.

- [main-site] Tidied the Our Causes page: removed the ~490px of dead space above the footer and gave the "1. Planting Trees!" bar breathing room above the first picture. New `tests/causes-page-runtime.js` guards the spacing and no horizontal overflow at five widths.

- [fix-page] Raised the shared content width from 944px to 1120px for the header, content and footer on every page (`--tbt-page`), keeping the 864px text measure for hero headlines. Widths now follow the one token instead of hard-coded values.

- Bound the em dash in the IntensiveFix headline to the following word so it can never end a line.

- The hero headlines on the homepage and IntensiveFix now use the original 864px text measure with normal wrapping inside the shared 944px container, so "Bio" stays with "Sec 3" on the IntensiveFix headline as on the original.

- Dropdown menus now open on hover (desktop pointers) and the dropdown arrow never turns Hello's pink; the floating WhatsApp glow is smaller and gently pulses; FastTrack pictures share one larger size; Google review cards share one height with arrows in their own gutters (below the cards on phones); FAQ rows keep dark readable text on hover; the closing photo matches its text height on the homepage too; and edge-to-edge pictures keep square corners.

- Gave every page one rounded, soft style: 18px corners on cards, pictures, videos and section bars; soft green outlines instead of black borders; forest green (#0e371d) replacing the near-black and pure-black bands, overlays and title bars; softer highlight colour in About; photo frames that fill their box. Rebuilt the Results & Testimonials page as clean rounded review cards in two balanced columns with tidy paragraphs (blank and zero-width paragraphs dropped, text unchanged). The standard test now also fails on sharp media, black outlines and near-black fills.

- Shortened the long pages without removing any content: review lists (testimonials, Free Notes, Cell Division) open with four reviews and offer "Show more" / "Show all" controls that keep every review on the page, fixed pixel section heights copied from the original Wix layout now follow their content, and the huge gaps between reviews are a steady 64px rhythm. The testimonials page drops from about 132,000px to about 29,000px, Free Notes from about 48,000px to 7,500px. New `tests/content-length-runtime.js` guards the review counts and page lengths.

- Standardised the whole site on one design system (new `standard.css`, loaded last): a single 944px content width for header, content and footer; Poppins everywhere; one type scale (h1 60px, h2 36px, h3 24px, body 16-18px); one brand palette (ink #19221a, green #2f8a27 / #43b43a, forest #0e371d, soft #f0f8ee) replacing the per-page olives, teals and charcoals; one 64px (48px on mobile) section rhythm; and one green pill button. Touch targets are at least 44px (carousel arrows and dots, review controls, FAQ rows, document controls), body text never drops below 16px on phones, and cards, menus and images now animate smoothly (all disabled under reduced-motion). A new `tests/standard-pages-runtime.js` enforces the standard on all twelve public pages at five viewport sizes.

- The IntensiveFix "Contact Us" header button now opens the WhatsApp number, and the floating WhatsApp button follows the supplied design (light-green disc, white glyph, red dot, two translucent rings).

- Reduced the space between the IntensiveFix header and hero from 96px to 40px (28px on phones).

- Made the IntensiveFix graphics and homepage hero sliders crossfade with every slide preloaded (no blank flash when switching), added automatic sliding to the IntensiveFix graphics, coloured the FastTrack title lead-in green, and enlarged the IntensiveFix closing image to match the height of its text.

- Removed the border around the IntensiveFix graphics carousel and stopped its arrows (and the homepage hero arrows) turning Hello's pink after a click.

- The homepage uses the same menu header as the other pages again; only IntensiveFix keeps the Inquire header with the "Contact Us" button.

- Applied the client's IntensiveFix change list: deleted the small grey hero subtext, replaced the hero subtitle with a black-and-green "Limited time" line, set the hero button to "Fix My Sec 3 Foundation", replaced the hero photos with the swipeable IntensiveFix graphics in supplied order, changed the bridge line to "Sound familiar? After our 2-week IntensiveFix, they're telling us:", replaced the video with the single student-review graphic, deleted the three off-topic testimonial cards, ran the count-up animation on all four numbers, prefixed the FastTrack title with "All IntensiveFix students will receive", skipped every section between FastTrack and "This is for / This isn't for", and replaced the closing copy. The top-right header button on the homepage and IntensiveFix is now "Contact Us". The shared FastTrack section is its own template part.

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

