# ThatBioTutor static website

This repository contains the client-editable ThatBioTutor homepage and Sec 3 IntensiveFix page, plus an untouched archive of the earlier Wix site.

## New site entry points

- Homepage: `mirror/index.html`
- Sec 3 IntensiveFix: `mirror/s3-intensivefix/index.html`

All runtime images are local WebP files. Shared styles and dependency-free JavaScript live under `mirror/assets/`. The pages use relative links so they work at a domain root or GitHub Pages repository subpath.

## View locally

From the repository root:

```sh
python3 -m http.server 8765 --directory mirror
```

Then open:

```text
http://localhost:8765/
http://localhost:8765/s3-intensivefix/
```

## Documentation

- [Client editing and Elementor migration guide](docs/client-editing-guide.md)
- [Approved design specification](docs/superpowers/specs/2026-11-02-inquire-static-pages-design.md)
- [Implementation plan](docs/superpowers/plans/2026-11-02-inquire-static-pages.md)

Run the automated checks with:

```sh
python3 -m unittest tests.test_static_site -v
node --check mirror/assets/js/content.js
node --check mirror/assets/js/site.js
```

## Archived Wix mirror

The earlier static Wix capture remains under `mirror/www.thatbiotutor.com/`. It includes the publicly accessible pages, images, fonts, JavaScript, CSS, feeds, robots file, and sitemaps captured on 29 October 2026 (Asia/Singapore). It is retained for reference and is not the new site entry point.

## Backup boundary

The archive contains only material that was available to an ordinary web browser. Wix server-side source code, private databases, CMS/admin content, form submissions, analytics data, and third-party services were not exposed by the public site and therefore are not included. Links to services such as WhatsApp, Google Forms, and Giving.sg remain external.

The separate original snapshot at `/Users/dmsyx/Documents/Projects/thatbiotutor-original` is the preservation copy and should remain unchanged. Website changes belong in this repository, outside `mirror/www.thatbiotutor.com/` unless an archive repair is explicitly requested.
