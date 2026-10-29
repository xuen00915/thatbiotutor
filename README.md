# ThatBioTutor website backup

Static mirror of [www.thatbiotutor.com](https://www.thatbiotutor.com/), captured on 29 October 2026 (Asia/Singapore).

## Contents

- All pages listed in the Wix XML sitemap
- The linked tuition recommendations page
- Publicly accessible HTML, images, fonts, JavaScript, CSS, feeds, robots file, and sitemaps required by those pages
- Local link conversion so the archived pages can be browsed without contacting the original asset hosts

The homepage is at `mirror/www.thatbiotutor.com/index.html`.

## View locally

From the repository root:

```sh
python3 -m http.server 8765 --directory mirror
```

Then open:

```text
http://localhost:8765/www.thatbiotutor.com/
```

## Backup boundary

This repository is a mirror of material available to an ordinary web browser. Wix server-side source code, private databases, CMS/admin content, form submissions, analytics data, and third-party services are not exposed by the public site and therefore cannot be included in a static crawl. Links to services such as WhatsApp, Google Forms, and Giving.sg remain external.
