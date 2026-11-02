import json
import re
import unittest
from html.parser import HTMLParser
from pathlib import Path


REPO_ROOT = Path(__file__).resolve().parents[1]
MIRROR = REPO_ROOT / "mirror"
MANIFEST_PATH = MIRROR / "assets/images/asset-manifest.json"


class PageParser(HTMLParser):
    def __init__(self):
        super().__init__()
        self.stack = []
        self.elements = []
        self.text_by_section = {}

    def handle_starttag(self, tag, attrs):
        attributes = dict(attrs)
        parent_section = self.stack[-1][1] if self.stack else None
        section = attributes.get("id", parent_section)
        element = {"tag": tag, "attrs": attributes, "section": section, "text": []}
        self.elements.append(element)
        self.stack.append((tag, section, element))

    def handle_startendtag(self, tag, attrs):
        attributes = dict(attrs)
        parent_section = self.stack[-1][1] if self.stack else None
        section = attributes.get("id", parent_section)
        self.elements.append(
            {"tag": tag, "attrs": attributes, "section": section, "text": []}
        )

    def handle_endtag(self, tag):
        for index in range(len(self.stack) - 1, -1, -1):
            if self.stack[index][0] == tag:
                del self.stack[index:]
                break

    def handle_data(self, data):
        if not self.stack or not data.strip():
            return
        section = self.stack[-1][1]
        for _, _, element in self.stack:
            element["text"].append(data)
        if section:
            self.text_by_section.setdefault(section, []).append(data)

    def elements_in(self, section, tag=None):
        return [
            element
            for element in self.elements
            if element["section"] == section and (tag is None or element["tag"] == tag)
        ]

    def section_text(self, section):
        return " ".join(" ".join(self.text_by_section.get(section, [])).split())


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
        self.assertRegex(
            pages,
            r"\.page-home\s+\.hero__media\s*\{[^}]*width:\s*100%",
        )
        self.assertRegex(
            components,
            r"\.media-carousel\.is-enhanced\s+\.media-carousel__track\s*\{[^}]*height:\s*100%",
        )

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


class HomePageTests(unittest.TestCase):
    @classmethod
    def setUpClass(cls):
        cls.path = MIRROR / "index.html"
        cls.source = cls.path.read_text(encoding="utf-8")
        cls.parser = PageParser()
        cls.parser.feed(cls.source)
        cls.manifest = json.loads(MANIFEST_PATH.read_text(encoding="utf-8"))

    def test_home_has_one_h1_and_required_sections(self):
        headings = [
            element for element in self.parser.elements if element["tag"] == "h1"
        ]
        self.assertEqual(len(headings), 1)
        ids = {
            element["attrs"].get("id")
            for element in self.parser.elements
            if element["attrs"].get("id")
        }
        required = {
            "hero",
            "results",
            "fasttrack",
            "tutors",
            "trial-guarantee",
            "suitability",
            "founder",
            "faq",
            "classroom",
            "final-cta",
        }
        self.assertTrue(required.issubset(ids), required - ids)

    def test_home_uses_supplied_images_in_manifest_order(self):
        expectations = {
            "hero": ("homeHero", 5),
            "results": ("trackRecord", 4),
            "classroom": ("homeClassroom", 3),
        }
        for section, (group, count) in expectations.items():
            expected = [entry["file"] for entry in self.manifest[group]]
            actual = [
                element["attrs"]["src"]
                for element in self.parser.elements_in(section, "img")
                if element["attrs"].get("data-supplied-group") == group
            ]
            self.assertEqual(len(actual), count, section)
            self.assertEqual(actual, expected, section)

    def test_trial_form_is_replaced_by_guarantee_and_whatsapp_cta(self):
        exact_copy = (
            "A trial lesson is $95. We’re so confident of our lesson quality that "
            "if you didn’t absolutely love it, simply ask for a refund and you’ll "
            "get your money back, no questions asked."
        )
        trial_text = self.parser.section_text("trial-guarantee")
        self.assertIn(exact_copy, trial_text)
        self.assertIn("Book My Trial Lesson", trial_text)
        self.assertFalse(self.parser.elements_in("trial-guarantee", "form"))
        self.assertFalse(self.parser.elements_in("trial-guarantee", "iframe"))
        links = self.parser.elements_in("trial-guarantee", "a")
        self.assertTrue(
            any(link["attrs"].get("href", "").startswith("https://wa.me/") for link in links)
        )

    def test_home_is_progressively_accessible(self):
        meaningful_images = [
            element
            for element in self.parser.elements
            if element["tag"] == "img"
            and element["attrs"].get("role") != "presentation"
        ]
        for image in meaningful_images:
            with self.subTest(src=image["attrs"].get("src")):
                self.assertGreater(len(image["attrs"].get("alt", "").strip()), 5)

        carousel_buttons = [
            element
            for element in self.parser.elements
            if element["tag"] == "button"
            and any(key.startswith("data-carousel-") for key in element["attrs"])
        ]
        self.assertTrue(carousel_buttons)
        for button in carousel_buttons:
            self.assertTrue(
                button["attrs"].get("aria-label") or button["text"],
                button["attrs"],
            )

        for final_value in ("912+", "91%", "2.3", "1 in 2"):
            self.assertIn(final_value, self.source)
        self.assertTrue(self.parser.elements_in("faq"))
        self.assertTrue(
            any(
                "data-faq-panel" in element["attrs"]
                and element["text"]
                for element in self.parser.elements
            )
        )
        skip_links = [
            element
            for element in self.parser.elements
            if element["tag"] == "a"
            and element["attrs"].get("class") == "skip-link"
        ]
        self.assertEqual(skip_links[0]["attrs"].get("href"), "#main")

    def test_home_uses_relative_local_assets_and_valid_internal_links(self):
        self.assertNotIn("wp-content", self.source)
        self.assertNotIn("localhost", self.source)
        self.assertNotRegex(self.source, r"https?://[^\s\"']+\.css")

        local_asset_urls = []
        for element in self.parser.elements:
            attribute = "src" if element["tag"] in {"img", "script"} else "href"
            value = element["attrs"].get(attribute, "")
            if value.startswith("assets/"):
                local_asset_urls.append(value)
        self.assertTrue(local_asset_urls)
        for url in local_asset_urls:
            with self.subTest(url=url):
                self.assertTrue((MIRROR / url).is_file())

        intensive_links = [
            element["attrs"].get("href")
            for element in self.parser.elements
            if element["tag"] == "a"
            and "s3-intensivefix" in element["attrs"].get("href", "")
        ]
        self.assertIn("s3-intensivefix/", intensive_links)


if __name__ == "__main__":
    unittest.main()
