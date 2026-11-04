import tempfile
import unittest
from pathlib import Path

from scripts.export_github_pages import normalize_query_assets, rewrite_origin


class GithubPagesExportTest(unittest.TestCase):
    def test_query_versioned_assets_are_renamed_and_relinked(self):
        with tempfile.TemporaryDirectory() as directory:
            root = Path(directory)
            assets = root / "assets"
            assets.mkdir()
            (assets / "site.css?ver=123.css").write_text("body{color:#000}", encoding="utf-8")
            (assets / "site.js?ver=456").write_text("console.log('ok')", encoding="utf-8")
            page = root / "index.html"
            page.write_text(
                '<link href="assets/site.css%3Fver=123.css"><script src="assets/site.js%3Fver=456"></script>',
                encoding="utf-8",
            )

            normalize_query_assets(root)

            self.assertTrue((assets / "site.css").is_file())
            self.assertTrue((assets / "site.js").is_file())
            self.assertFalse((assets / "site.css?ver=123.css").exists())
            self.assertFalse((assets / "site.js?ver=456").exists())
            self.assertEqual(
                '<link href="assets/site.css"><script src="assets/site.js"></script>',
                page.read_text(encoding="utf-8"),
            )

    def test_local_wordpress_origin_is_replaced_with_pages_origin(self):
        with tempfile.TemporaryDirectory() as directory:
            root = Path(directory)
            page = root / "index.html"
            page.write_text(
                (
                    '<meta property="og:url" content="http://localhost:8888/about/">'
                    '<link href="http%3A%2F%2Flocalhost%3A8888%2Fwp-json%2Foembed">'
                ),
                encoding="utf-8",
            )

            rewrite_origin(root, "http://localhost:8888", "https://xuen00915.github.io/thatbiotutor")

            self.assertEqual(
                (
                    '<meta property="og:url" content="https://xuen00915.github.io/thatbiotutor/about/">'
                    '<link href="https%3A%2F%2Fxuen00915.github.io%2Fthatbiotutor%2Fwp-json%2Foembed">'
                ),
                page.read_text(encoding="utf-8"),
            )

    def test_pages_workflow_builds_and_deploys_the_native_wordpress_export(self):
        workflow = Path(".github/workflows/pages.yml").read_text(encoding="utf-8")
        self.assertIn("scripts/export_github_pages.py", workflow)
        self.assertIn("actions/upload-pages-artifact@v3", workflow)
        self.assertIn("actions/deploy-pages@v4", workflow)
        self.assertIn("npm run wp-env:setup", workflow)


if __name__ == "__main__":
    unittest.main()
