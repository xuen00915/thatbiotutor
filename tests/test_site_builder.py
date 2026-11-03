import json
import subprocess
import tempfile
import unittest
from pathlib import Path
from unittest import mock

from scripts.build_site import BuildError, relative_root, render_site


class SiteBuilderTests(unittest.TestCase):
    def setUp(self):
        self.temporary_directory = tempfile.TemporaryDirectory()
        self.project_root = Path(self.temporary_directory.name)
        self.site_dir = self.project_root / "site"
        self.output_dir = self.project_root / "build"
        self._write_fixture()

    def tearDown(self):
        self.temporary_directory.cleanup()

    def _write(self, relative_path, text):
        path = self.project_root / relative_path
        path.parent.mkdir(parents=True, exist_ok=True)
        path.write_text(text, encoding="utf-8")

    def _write_json(self, relative_path, value):
        self._write(relative_path, json.dumps(value, indent=2))

    def _site_config(self):
        return {
            "siteName": "ThatBioTutor",
            "siteUrl": "https://www.thatbiotutor.com",
            "logo": "assets/images/logo.webp",
            "favicon": "assets/images/favicon.webp",
            "whatsappNumber": "6587597182",
            "defaultWhatsappMessage": "Hello & welcome",
            "address": "1 Biology Way",
            "hours": "Mon–Fri, 9am–6pm",
            "copyright": "© ThatBioTutor",
            "navigation": [
                {"key": "home", "label": "Home", "route": ""},
                {"key": "about", "label": "About", "route": "about/"},
            ],
        }

    def _pages(self):
        return [
            {
                "key": "home",
                "route": "",
                "source": "pages/home.html",
                "title": "Biology <Tuition> & Support",
                "description": "Clear concepts & kind coaching",
                "bodyClass": "page-home",
                "navKey": "home",
                "whatsappMessage": "I'd like the $95 plan & details.",
                "ogImage": "assets/images/hero.webp",
                "context": {"offer": "$95 — it’s {simple} & useful"},
                "aliases": [],
            },
            {
                "key": "about",
                "route": "about/",
                "source": "pages/about.html",
                "title": "About",
                "description": "About our tutors",
                "bodyClass": "page-about",
                "navKey": "about",
                "whatsappMessage": "Tell me about classes",
                "ogImage": "assets/images/hero.webp",
                "context": {},
                "aliases": ["classes/sec-3/"],
            },
        ]

    def _write_fixture(self):
        self._write_json("site/site.json", self._site_config())
        self._write_json("site/pages.json", self._pages())
        self._write(
            "site/templates/base.html",
            """<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>{{ title }}</title>
  <meta name="description" content="{{ description }}">
  <link rel="canonical" href="{{ canonical_url }}">
  <meta property="og:url" content="{{ canonical_url }}">
  <meta property="og:image" content="{{ og_image_url }}">
  <link rel="icon" href="{{ root }}{{ favicon }}">
</head>
<body class="{{ body_class }}">
<!-- INCLUDE: partials/header.html -->
{{ main_html }}
<!-- INCLUDE: partials/footer.html -->
<!-- INCLUDE: partials/floating-whatsapp.html -->
<script src="{{ root }}assets/js/site.js"></script>
</body>
</html>
""",
        )
        self._write(
            "site/partials/header.html",
            '<header><a href="{{ home_url }}"><img src="{{ root }}{{ logo }}" alt="{{ site_name }}"></a><nav>{{ nav_html }}</nav></header>',
        )
        self._write(
            "site/partials/footer.html",
            '<footer><p>{{ address }}</p><p>{{ hours }}</p>{{ footer_nav_html }}<small>{{ copyright }}</small></footer>',
        )
        self._write(
            "site/partials/floating-whatsapp.html",
            '<a class="floating-whatsapp" href="{{ whatsapp_url }}">WhatsApp us</a>',
        )
        self._write(
            "site/pages/home.html",
            '<main><h1>Biology support</h1><p>{{ offer }}</p><img src="{{ root }}assets/images/hero.webp" alt="Biology lesson"></main>',
        )
        self._write(
            "site/pages/about.html",
            '<main><h1>About</h1><a href="{{ root }}">Home</a></main>',
        )
        self._write("site/assets/js/site.js", "document.documentElement.classList.add('js');\n")
        for name in ("logo.webp", "favicon.webp", "hero.webp"):
            path = self.site_dir / "assets/images" / name
            path.parent.mkdir(parents=True, exist_ok=True)
            path.write_bytes(b"RIFFfixtureWEBP")
        self._write_json(
            "site/assets/images/asset-manifest.json",
            {
                "fixture": [
                    {
                        "source": f"fixture/{name}",
                        "file": f"assets/images/{name}",
                        "alt": f"Fixture {name}",
                        "width": 1,
                        "height": 1,
                    }
                    for name in ("logo.webp", "favicon.webp", "hero.webp")
                ]
            },
        )

    def test_relative_root_supports_root_and_nested_routes(self):
        self.assertEqual(relative_root(""), "")
        self.assertEqual(relative_root("about/"), "../")
        self.assertEqual(relative_root("classes/sec-3/"), "../../")

    def test_renderer_preserves_page_copy_and_escapes_metadata(self):
        render_site(self.project_root, self.output_dir)

        html = (self.output_dir / "index.html").read_text(encoding="utf-8")
        self.assertIn("$95 — it’s {simple} &amp; useful", html)
        self.assertIn("<title>Biology &lt;Tuition&gt; &amp; Support</title>", html)
        self.assertIn('content="Clear concepts &amp; kind coaching"', html)
        self.assertNotIn("&amp;lt;Tuition&amp;gt;", html)

    def test_build_rejects_duplicate_absolute_and_traversal_routes(self):
        invalid_routes = (
            ("about", "about/"),
            ("about", "/about"),
            ("about", "../outside"),
        )
        for label, route in invalid_routes:
            with self.subTest(label=label, route=route):
                pages = self._pages()
                pages[0]["route"] = route
                self._write_json("site/pages.json", pages)
                with self.assertRaises(BuildError):
                    render_site(self.project_root, self.output_dir)

    def test_failed_staged_build_preserves_previous_output(self):
        self.output_dir.mkdir()
        previous = self.output_dir / "index.html"
        previous.write_text("previous good build", encoding="utf-8")
        (self.site_dir / "partials/header.html").unlink()

        with self.assertRaises(BuildError):
            render_site(self.project_root, self.output_dir)

        self.assertEqual(previous.read_text(encoding="utf-8"), "previous good build")

    def test_build_generates_canonical_sitemap_alias_and_manifest(self):
        report = render_site(self.project_root, self.output_dir)

        about = (self.output_dir / "about/index.html").read_text(encoding="utf-8")
        alias = (self.output_dir / "classes/sec-3/index.html").read_text(
            encoding="utf-8"
        )
        sitemap = (self.output_dir / "sitemap.xml").read_text(encoding="utf-8")
        robots = (self.output_dir / "robots.txt").read_text(encoding="utf-8")
        manifest = json.loads(
            (self.output_dir / "build-manifest.json").read_text(encoding="utf-8")
        )

        self.assertIn(
            '<link rel="canonical" href="https://www.thatbiotutor.com/about/">',
            about,
        )
        self.assertIn("https://www.thatbiotutor.com/about/", alias)
        self.assertIn('../../about/">Continue to About</a>', alias)
        self.assertIn("https://www.thatbiotutor.com/about/", sitemap)
        self.assertNotIn("https://www.thatbiotutor.com/classes/sec-3/", sitemap)
        self.assertIn(
            "Sitemap: https://www.thatbiotutor.com/sitemap.xml", robots
        )
        self.assertEqual(report.pages, ("", "about/"))
        self.assertEqual(report.aliases, ("classes/sec-3/",))
        self.assertEqual(manifest["pages"], ["", "about/"])
        self.assertEqual(manifest["aliases"], ["classes/sec-3/"])
        self.assertIn("about/index.html", manifest["files"])
        self.assertIn("assets/images/hero.webp", report.files)

    def test_build_rejects_non_webp_images_and_broken_local_links(self):
        png = self.site_dir / "assets/images/not-allowed.png"
        png.write_bytes(b"png")
        with self.assertRaisesRegex(BuildError, "WebP"):
            render_site(self.project_root, self.output_dir)
        png.unlink()
        self._write(
            "site/pages/about.html",
            '<main><h1>About</h1><img src="{{ root }}assets/images/missing.webp" alt="Missing"></main>',
        )
        with self.assertRaisesRegex(BuildError, "image is missing from manifest"):
            render_site(self.project_root, self.output_dir)

    def test_build_rejects_protected_or_unrecognised_existing_output(self):
        for name in (".git", "scripts", "docs"):
            with self.subTest(name=name):
                sentinel = self.project_root / name / "keep.txt"
                sentinel.parent.mkdir(parents=True, exist_ok=True)
                sentinel.write_text("preserve", encoding="utf-8")
                with self.assertRaisesRegex(BuildError, "output directory"):
                    render_site(self.project_root, sentinel.parent)
                self.assertEqual(sentinel.read_text(encoding="utf-8"), "preserve")

        unrelated = self.project_root / "client-files"
        unrelated.mkdir()
        sentinel = unrelated / "keep.txt"
        sentinel.write_text("preserve", encoding="utf-8")
        with self.assertRaisesRegex(BuildError, "generated output"):
            render_site(self.project_root, unrelated)
        self.assertEqual(sentinel.read_text(encoding="utf-8"), "preserve")

    def test_build_rejects_remote_non_webp_and_unmanifested_page_images(self):
        cases = (
            ('<img src="https://static.wixstatic.com/photo.jpg" alt="Remote">', None),
            ('<img src="assets/images/photo.png" alt="PNG">', "photo.png"),
            ('<img src="assets/images/unlisted.webp" alt="Unlisted">', "unlisted.webp"),
            ('<img srcset="https://static.wixstatic.com/photo.webp 2x" src="assets/images/hero.webp" alt="Remote set">', None),
        )
        for markup, local_file in cases:
            with self.subTest(markup=markup):
                self._write(
                    "site/pages/home.html",
                    f"<main><h1>Biology support</h1>{markup}</main>",
                )
                if local_file:
                    (self.site_dir / "assets/images" / local_file).write_bytes(b"fixture")
                with self.assertRaisesRegex(BuildError, "image"):
                    render_site(self.project_root, self.output_dir)
                if local_file:
                    (self.site_dir / "assets/images" / local_file).unlink()

    def test_site_configuration_can_change_without_javascript_edits(self):
        config = self._site_config()
        config["whatsappNumber"] = "6599998888"
        self._write_json("site/site.json", config)
        render_site(self.project_root, self.output_dir)
        generated = (self.output_dir / "index.html").read_text(encoding="utf-8")
        self.assertIn("https://wa.me/6599998888", generated)


class PreviewAndWorkflowTests(unittest.TestCase):
    def test_documented_preview_command_is_directly_executable(self):
        result = subprocess.run(
            ["python3", "scripts/preview_site.py", "--help"],
            cwd=Path(__file__).parents[1],
            capture_output=True,
            text=True,
            check=False,
        )
        self.assertEqual(result.returncode, 0, result.stderr)
        self.assertIn("--port", result.stdout)

    def test_preview_builds_before_serving(self):
        try:
            from scripts import preview_site
        except ImportError as error:
            self.fail(f"Preview helper is not importable: {error}")

        events = []

        class FakeServer:
            def __init__(self, address, handler):
                events.append(("server", address, handler))

            def serve_forever(self):
                events.append(("serve",))
                raise KeyboardInterrupt

            def server_close(self):
                events.append(("close",))

        with (
            mock.patch.object(
                preview_site,
                "render_site",
                side_effect=lambda root, output: (
                    events.append(("build", root, output))
                    or mock.Mock(pages=("",), aliases=())
                ),
            ),
            mock.patch.object(preview_site, "ThreadingHTTPServer", FakeServer),
        ):
            result = preview_site.main(["--port", "8766"])

        self.assertEqual(result, 0)
        self.assertEqual(events[0][0], "build")
        self.assertEqual(events[1][0], "server")
        self.assertEqual(events[1][1], ("127.0.0.1", 8766))
        self.assertEqual(events[-1], ("close",))

    def test_preview_rejects_invalid_ports(self):
        try:
            from scripts import preview_site
        except ImportError as error:
            self.fail(f"Preview helper is not importable: {error}")

        for port in ("0", "65536"):
            with self.subTest(port=port), self.assertRaises(SystemExit):
                preview_site.main(["--port", port])

    def test_workflow_builds_from_site_source_and_deploys_only_main(self):
        workflow = (
            Path(__file__).parents[1] / ".github/workflows/pages.yml"
        ).read_text(encoding="utf-8")

        for required_text in (
            "pull_request:",
            "push:",
            "python3 scripts/build_site.py --output build",
            "python3 -m pip install playwright==1.60.0",
            "python3 -m playwright install --with-deps chromium",
            "python3 -m unittest discover -s tests -v",
            "node --check site/assets/js/content.js",
            "node --check site/assets/js/site.js",
            "git diff --check",
            "path: build",
            "github.ref == 'refs/heads/main'",
        ):
            with self.subTest(required_text=required_text):
                self.assertIn(required_text, workflow)

        self.assertNotIn("path: mirror", workflow)
        self.assertIn("group: pages-${{ github.ref }}", workflow)
        self.assertGreaterEqual(
            workflow.count("github.ref == 'refs/heads/main'"), 3
        )


if __name__ == "__main__":
    unittest.main()
