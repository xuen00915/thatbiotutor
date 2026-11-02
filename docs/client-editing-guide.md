# ThatBioTutor website editing guide

This guide covers the two new static pages and how to move them into WordPress Elementor later. The working pages are:

- Homepage: `mirror/index.html`
- Sec 3 IntensiveFix: `mirror/s3-intensivefix/index.html`

The older Wix archive remains separate at `mirror/www.thatbiotutor.com/`. Do not edit that folder when updating the new pages.

## Preview the website locally

Open Terminal in the repository folder and run:

```sh
python3 -m http.server 8765 --directory mirror
```

Then visit:

- Homepage: `http://localhost:8765/`
- IntensiveFix: `http://localhost:8765/s3-intensivefix/`

Stop the preview with `Control-C`. If port 8765 is already occupied, replace it in both the command and address with another port, such as 8000.

## Where to make common edits

Look for `CLIENT EDIT` comments in the files. They mark the safest editing points.

| What to change | File | How |
| --- | --- | --- |
| Homepage headings, paragraphs, button labels, FAQ answers | `mirror/index.html` | Edit only the text between the relevant HTML tags. Keep the tags and section IDs intact. |
| IntensiveFix headings, paragraphs, and button labels | `mirror/s3-intensivefix/index.html` | Edit only the visible text. Keep the tags and section IDs intact. |
| WhatsApp number and pre-filled messages | `mirror/assets/js/content.js` | Change the values in `whatsapp`. Also update any existing `wa.me` links in both HTML files so the no-JavaScript fallback matches. |
| Final counter values | `mirror/assets/js/content.js` and both HTML files | Update the matching counter entry and the visible value inside every element with the same `data-counter-key`. The HTML value is the no-JavaScript fallback. |
| Brand colours, fonts, spacing, corner radius, shadows, content widths | `mirror/assets/css/base.css` | Edit the named variables under `:root`; avoid changing component selectors unless a developer is reviewing the change. |
| Image files, source history, dimensions, and alt text | `mirror/assets/images/asset-manifest.json` | Update the matching entry after replacing an image. |
| Carousel image order | `mirror/assets/images/asset-manifest.json`, `mirror/assets/js/content.js`, and the relevant HTML file | Reorder the same images in all three places. Keep the slide buttons/dots in the same order and quantity. |

### Change a CTA

Button wording is written directly in each HTML file. A WhatsApp button should keep a link beginning with `https://wa.me/6587597182?text=`. The text after `text=` must be URL encoded. The shared enhancement reads the page-specific message from `mirror/assets/js/content.js`, but the HTML link must also remain correct for visitors who disable JavaScript.

After changing a CTA, click it in the local preview and confirm WhatsApp opens with the intended phone number and message.

### Change a counter

Each animated number has a key such as `students`, `results`, `gradeJump`, `homeDistinction`, or `intensiveLessons`. Update its number, prefix, or suffix in `mirror/assets/js/content.js`. Then search both HTML files for the matching `data-counter-key` and update the visible number there too.

Keeping the final number in the HTML makes the result visible before JavaScript loads and to visitors using reduced-motion settings.

### Replace an image

1. Export the replacement as WebP. All website images must remain in WebP format.
2. Use the existing descriptive filename and place it in the same folder under `mirror/assets/images/`. Reusing the filename avoids changing the HTML.
3. Keep roughly the same aspect ratio and dimensions so the layout does not crop important text or faces.
4. Check the actual pixel width and height, then update `width` and `height` in `mirror/assets/images/asset-manifest.json`.
5. Update the manifest `source` note and write useful alt text that describes the image's purpose. If the image appears directly in HTML, update its `alt`, `width`, and `height` attributes there too.
6. Preview the page on desktop and mobile. Check that text inside supplied graphics remains readable.

The supplied carousel sequences are intentional. If carousel order changes, update the manifest, `mirror/assets/js/content.js`, and slide order in HTML together. Do not rename files only to imply a new order.

## Before publishing a static-site update

Run the project checks from the repository root:

```sh
python3 -m unittest tests.test_static_site -v
node --check mirror/assets/js/content.js
node --check mirror/assets/js/site.js
```

Then preview both pages at a narrow mobile width and a desktop width. Check the menu, every carousel arrow and dot, FAQ disclosure, video, internal navigation, and every WhatsApp CTA. Use the Tab key to confirm focus is visible.

## WordPress Elementor migration map

The static site is organised to map cleanly into Elementor. Migrate one section at a time rather than importing the HTML as one large widget.

### Site settings and shared parts

- In **Elementor Site Settings → Global Colors**, recreate the `--color-*` values from `mirror/assets/css/base.css`.
- In **Elementor Site Settings → Global Fonts**, recreate the heading, body, and accent families represented by the `--font-*` values.
- Recreate the spacing, radius, shadow, and maximum-width variables as consistent container settings and reusable styles.
- Build the `.site-header` and `.site-footer` as WordPress Theme Builder Header and Footer templates. Use the Site Logo, Nav Menu, and Button widgets.
- Upload every used WebP file from `mirror/assets/images/` to the WordPress Media Library. Replace relative image paths with Media Library selections; do not hotlink the static files.
- Set meaningful WordPress alt text while uploading. Decorative images should use empty alt text; informative images should retain the descriptions from the HTML or manifest.

### Homepage containers

| Stable section ID | Elementor structure |
| --- | --- |
| `#hero` | Two-column Container with Heading, Text Editor, Buttons, and an Image Carousel. |
| `#results` | Heading plus four Counter widgets and a nested image gallery. Enter the final values as visible fallback text where possible. |
| `#fasttrack` | Heading followed by alternating two-column Containers using Image, Heading, and Text Editor widgets. |
| `#tutors` | Heading and a responsive grid of image-and-text tutor cards. Use nested Containers rather than fixed-position columns. |
| `#trial-guarantee` | Call-to-action Container with the exact guarantee copy and a WhatsApp Button; no form is required. |
| `#suitability` | Two-column comparison Container with Icon List widgets for the positive and caution lists. |
| `#founder` | Split Container with Image, Heading, Text Editor, and supporting statistics. |
| `#faq` | Accordion widget. Preserve the question order and make the headings keyboard accessible. |
| `#classroom` | Heading plus Gallery widget using the three supplied classroom images in order. |
| `#final-cta` | Full-width CTA Container with Heading, Text Editor, and WhatsApp Button. |

### IntensiveFix containers

| Stable section ID | Elementor structure |
| --- | --- |
| `#hero` | Two-column Container with Heading, limited-time Text Editor, CTA Buttons, and the programme cover Image. |
| `#program-carousel` | Image Carousel using the ten IntensiveFix WebP graphics in their numbered order. Use contain rather than cover so words are not cropped. |
| `#reviews` | Heading and the one approved supplied review Image. |
| `#results` | Four Counter widgets followed by the shared results Image Gallery. |
| `#analysis-video` | Two-column Container with Heading/Text Editor and a Video widget using the retained YouTube URL. |
| `#fasttrack` | Heading followed by the four alternating feature Containers used on the homepage. |
| `#suitability` | Two-column comparison Container with Icon List widgets. |
| `#final-cta` | Full-width CTA Container containing the approved EOY copy and WhatsApp Button. |

### Interactive behaviour in Elementor

- Use Elementor's Image Carousel or Slides widget for carousels. Enable keyboard controls, provide an accessible label, and keep touch swipe enabled.
- Use Counter widgets only if their final value remains understandable when animation is disabled.
- Use the Accordion widget for FAQ content and check its `aria-expanded` behaviour with a keyboard.
- Use Elementor's Video widget for the YouTube section and supply a descriptive title.
- Configure all buttons with a minimum 44 by 44 CSS-pixel target and a visible focus style.
- Avoid entrance animations for essential content. Any optional animation must respect `prefers-reduced-motion`.

## Post-migration checklist

- Check desktop, tablet, and mobile breakpoints, including a 320-pixel-wide screen.
- Confirm there is exactly one page H1 and that headings follow a logical order.
- Confirm all local assets were replaced with Media Library URLs and every displayed image is WebP.
- Check important faces and text are not cropped.
- Confirm every image has suitable alt text.
- Test the menu, carousel, Accordion, video, internal links, and all WhatsApp links with mouse, touch, and keyboard.
- Confirm focus indicators are visible and controls are at least 44 by 44 CSS pixels.
- Test with reduced motion enabled and with JavaScript blocked where the WordPress setup permits it.
- Check the browser console and network panel for broken requests or unexpected legacy WordPress plugin assets.

