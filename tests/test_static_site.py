import json
import re
import unittest
from pathlib import Path


REPO_ROOT = Path(__file__).resolve().parents[1]
MIRROR = REPO_ROOT / "mirror"
MANIFEST_PATH = MIRROR / "assets/images/asset-manifest.json"


class StaticSiteAssetTests(unittest.TestCase):
    def load_manifest(self):
        self.assertTrue(MANIFEST_PATH.exists(), f"Missing {MANIFEST_PATH}")
        return json.loads(MANIFEST_PATH.read_text(encoding="utf-8"))

    def test_manifest_has_authoritative_ordered_groups(self):
        manifest = self.load_manifest()
        self.assertEqual(len(manifest["homeHero"]), 5)
        self.assertEqual(len(manifest["trackRecord"]), 4)
        self.assertEqual(len(manifest["homeClassroom"]), 3)
        self.assertEqual(len(manifest["intensiveFixCarousel"]), 10)
        self.assertEqual(len(manifest["intensiveFixReview"]), 1)

    def test_manifest_files_are_local_and_exist(self):
        manifest = self.load_manifest()
        for group_name, entries in manifest.items():
            self.assertIsInstance(entries, list, group_name)
            for entry in entries:
                with self.subTest(group=group_name, file=entry.get("file")):
                    self.assertEqual(
                        set(entry), {"source", "file", "alt", "width", "height"}
                    )
                    file_path = Path(entry["file"])
                    self.assertFalse(file_path.is_absolute())
                    self.assertEqual(file_path.parts[:2], ("assets", "images"))
                    self.assertTrue((MIRROR / file_path).is_file())
                    self.assertGreater(entry["width"], 0)
                    self.assertGreater(entry["height"], 0)
                    self.assertGreater(len(entry["alt"].strip()), 8)
                    self.assertNotIn(entry["alt"].strip().lower(), {"image", "photo"})

    def test_supplied_groups_preserve_numeric_source_order(self):
        manifest = self.load_manifest()
        expected = {
            "homeHero": [1, 2, 3, 4, 5],
            "trackRecord": [1, 2, 3, 4],
            "homeClassroom": [1, 2, 3],
            "intensiveFixCarousel": list(range(1, 11)),
            "intensiveFixReview": [7],
        }
        for group_name, expected_order in expected.items():
            actual = []
            for entry in manifest[group_name]:
                match = re.match(r"(\d+)", Path(entry["source"]).name)
                self.assertIsNotNone(match, entry["source"])
                actual.append(int(match.group(1)))
            self.assertEqual(actual, expected_order, group_name)

    def test_manifest_uses_webp_for_every_local_image(self):
        manifest = self.load_manifest()
        for group_name, entries in manifest.items():
            for entry in entries:
                with self.subTest(group=group_name, file=entry["file"]):
                    self.assertEqual(Path(entry["file"]).suffix.lower(), ".webp")


class StaticSiteFoundationTests(unittest.TestCase):
    def read_asset(self, relative_path):
        path = MIRROR / relative_path
        self.assertTrue(path.is_file(), f"Missing {path}")
        return path.read_text(encoding="utf-8")

    def test_content_config_has_whatsapp_and_ordered_assets(self):
        source = self.read_asset("assets/js/content.js")
        self.assertIn("6587597182", source)
        self.assertIn("homeMessage", source)
        self.assertIn("intensiveFixMessage", source)
        self.assertIn('new URL("../images/", document.currentScript.src)', source)
        for group_name in (
            "homeHero",
            "trackRecord",
            "homeClassroom",
            "intensiveFixCarousel",
            "intensiveFixReview",
        ):
            self.assertIn(group_name, source)

    def test_css_defines_elementor_ready_tokens_and_accessibility_states(self):
        base = self.read_asset("assets/css/base.css")
        components = self.read_asset("assets/css/components.css")
        pages = self.read_asset("assets/css/pages.css")

        for token_family in (
            "--color-",
            "--font-",
            "--space-",
            "--radius-",
            "--shadow-",
            "--container-",
        ):
            self.assertIn(token_family, base)
        self.assertIn(":focus-visible", components)
        self.assertRegex(components, r"min-(?:height|width):\s*44px")
        self.assertRegex(pages, r"@media\s*\([^)]*max-width")
        self.assertRegex(pages, r"@media\s*\([^)]*min-width")

    def test_site_script_defines_progressive_enhancements(self):
        source = self.read_asset("assets/js/site.js")
        for function_name in (
            "initCarousels",
            "initCounters",
            "initFaqs",
            "buildWhatsAppUrl",
        ):
            self.assertIn(function_name, source)
        self.assertIn("prefers-reduced-motion", source)
        self.assertIn("IntersectionObserver", source)
        self.assertIn("URLSearchParams", source)
        self.assertIn("window.TBT_UI", source)


if __name__ == "__main__":
    unittest.main()
