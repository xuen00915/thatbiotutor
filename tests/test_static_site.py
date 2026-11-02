import atexit
import json
import re
import tempfile
import unittest
from html.parser import HTMLParser
from pathlib import Path

from scripts.build_site import render_site


REPO_ROOT = Path(__file__).resolve().parents[1]
MIRROR = REPO_ROOT / "mirror"
SITE_DIR = REPO_ROOT / "site"
MANIFEST_PATH = SITE_DIR / "assets/images/asset-manifest.json"
SITE_CONFIG = json.loads((SITE_DIR / "site.json").read_text(encoding="utf-8"))
PAGE_CONFIG = {
    page["key"]: page
    for page in json.loads((SITE_DIR / "pages.json").read_text(encoding="utf-8"))
}
_BUILD_TEMP = None
_BUILD_ROOT = None


def build_test_site():
    global _BUILD_TEMP, _BUILD_ROOT
    if _BUILD_ROOT is None:
        scratch = REPO_ROOT / ".superpowers"
        scratch.mkdir(exist_ok=True)
        _BUILD_TEMP = tempfile.TemporaryDirectory(prefix="test-build-", dir=scratch)
        atexit.register(_BUILD_TEMP.cleanup)
        _BUILD_ROOT = Path(_BUILD_TEMP.name) / "site"
        render_site(REPO_ROOT, _BUILD_ROOT)
    return _BUILD_ROOT


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
                    self.assertTrue((SITE_DIR / file_path).is_file())
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
        path = SITE_DIR / relative_path
        self.assertTrue(path.is_file(), f"Missing {path}")
        return path.read_text(encoding="utf-8")

    def test_content_config_has_ordered_assets_without_business_data(self):
        source = self.read_asset("assets/js/content.js")
        self.assertNotIn("whatsapp:", source)
        self.assertNotIn("counters:", source)
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
        self.assertIn("@media (max-width: 63.99rem)", components)

    def test_site_script_defines_progressive_enhancements(self):
        source = self.read_asset("assets/js/site.js")
        for function_name in (
            "initCarousels",
            "initCounters",
            "initFaqs",
            "parseCounter",
        ):
            self.assertIn(function_name, source)
        self.assertIn("prefers-reduced-motion", source)
        self.assertIn("IntersectionObserver", source)
        self.assertNotIn("TBT_CONTENT.counters", source)
        self.assertIn("window.TBT_UI", source)


class HomePageTests(unittest.TestCase):
    @classmethod
    def setUpClass(cls):
        cls.build_root = build_test_site()
        cls.path = cls.build_root / "index.html"
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

        for final_value in (
            "912+",
            "91%",
            "2.3",
            PAGE_CONFIG["home"]["context"]["track_fourth_value"],
        ):
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
                self.assertTrue((self.build_root / url).is_file())

        intensive_links = [
            element["attrs"].get("href")
            for element in self.parser.elements
            if element["tag"] == "a"
            and "s3-intensivefix" in element["attrs"].get("href", "")
        ]
        self.assertIn("s3-intensivefix/", intensive_links)


class IntensiveFixPageTests(unittest.TestCase):
    @classmethod
    def setUpClass(cls):
        cls.build_root = build_test_site()
        cls.path = cls.build_root / "s3-intensivefix/index.html"
        cls.source = cls.path.read_text(encoding="utf-8") if cls.path.is_file() else ""
        cls.parser = PageParser()
        cls.parser.feed(cls.source)
        cls.manifest = json.loads(MANIFEST_PATH.read_text(encoding="utf-8"))

    def test_intensivefix_has_one_h1_and_required_sections(self):
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
            "program-carousel",
            "reviews",
            "results",
            "analysis-video",
            "fasttrack",
            "suitability",
            "final-cta",
        }
        self.assertTrue(required.issubset(ids), required - ids)

    def test_hero_and_navigation_use_approved_copy(self):
        exact_subtitle = (
            "Limited time: Only during last two weeks of Nov/first two weeks of Dec."
        )
        self.assertIn(exact_subtitle, self.parser.section_text("hero"))
        self.assertIn("Fix My Sec 3 Foundation", self.parser.section_text("hero"))
        header = next(
            element
            for element in self.parser.elements
            if element["tag"] == "header"
        )
        self.assertIn("Contact Us", " ".join(header["text"]))
        self.assertNotIn("Start with a $110 trial lesson", self.source)
        self.assertNotIn("Limited intake", self.source)

    def test_program_and_review_graphics_use_supplied_order(self):
        expected_program = [
            "../" + entry["file"]
            for entry in self.manifest["intensiveFixCarousel"]
        ]
        actual_program = [
            element["attrs"]["src"]
            for element in self.parser.elements_in("program-carousel", "img")
            if element["attrs"].get("data-supplied-group")
            == "intensiveFixCarousel"
        ]
        self.assertEqual(actual_program, expected_program)
        expected_review = [
            "../" + entry["file"] for entry in self.manifest["intensiveFixReview"]
        ]
        actual_review = [
            element["attrs"]["src"]
            for element in self.parser.elements_in("reviews", "img")
            if element["attrs"].get("data-supplied-group") == "intensiveFixReview"
        ]
        self.assertEqual(actual_review, expected_review)

    def test_unapproved_sections_and_testimonials_are_absent(self):
        for testimonial_name in ("Yong Zheng", "Gwen", "Aryan"):
            self.assertNotIn(testimonial_name, self.source)
        self.assertNotIn("<form", self.source.lower())
        ids = {
            element["attrs"].get("id")
            for element in self.parser.elements
            if element["attrs"].get("id")
        }
        self.assertFalse(
            {
                "social-proof",
                "tutors",
                "quality-control",
                "practical",
                "founder",
                "faq",
                "classroom",
            }
            & ids
        )
        self.assertLess(
            self.source.index('id="fasttrack"'),
            self.source.index('id="suitability"'),
        )

    def test_intensivefix_retains_current_analysis_video(self):
        frames = self.parser.elements_in("analysis-video", "iframe")
        self.assertEqual(len(frames), 1)
        self.assertIn("q_IPfEdODqI", frames[0]["attrs"].get("src", ""))
        self.assertGreater(len(frames[0]["attrs"].get("title", "")), 12)

    def test_intensivefix_has_approved_fasttrack_and_closing_copy(self):
        self.assertIn(
            "All IntensiveFix students will receive ThatBioTutor’s FastTrack System",
            self.source,
        )
        closing = self.parser.section_text("final-cta")
        self.assertIn(
            "Your EOY Bio Grade doesn’t have to define your O Levels.",
            self.source,
        )
        for exact_copy in (
            "It can be demoralising to have scored poorer than expected for the Sec 3 end of year bio exam. But there is still time to turn things around.",
            "We’ve had students come in during this period at F9, going all the way to an A1 in O levels.",
            "What you need is a thorough content overhaul, fixing that weak sec 3 foundation, to supercharge and give you confidence heading into sec 4, to score the O level grade you’re truly capable of.",
            "Ready to see what that feels like?",
        ):
            self.assertIn(exact_copy, closing)

    def test_intensivefix_progressive_accessibility_and_links(self):
        for final_value in (
            "912+",
            "91%",
            "2.3",
            PAGE_CONFIG["s3-intensivefix"]["context"]["track_fourth_value"],
        ):
            self.assertIn(final_value, self.parser.section_text("results"))
        controls = [
            element
            for element in self.parser.elements
            if element["tag"] == "button"
            and any(key.startswith("data-carousel-") for key in element["attrs"])
        ]
        self.assertTrue(controls)
        for control in controls:
            self.assertTrue(control["attrs"].get("aria-label") or control["text"])
        for element in self.parser.elements:
            if element["tag"] not in {"img", "script", "link"}:
                continue
            attribute = "href" if element["tag"] == "link" else "src"
            value = element["attrs"].get(attribute, "")
            if value.startswith("../assets/"):
                self.assertTrue(
                    (self.build_root / value.removeprefix("../")).is_file(), value
                )
        self.assertIn(str(SITE_CONFIG["whatsappNumber"]), self.source)
        home_links = [
            element["attrs"].get("href")
            for element in self.parser.elements
            if element["tag"] == "a"
        ]
        self.assertIn("../", home_links)


class SharedTemplateTests(unittest.TestCase):
    @classmethod
    def setUpClass(cls):
        cls.build_root = build_test_site()
        cls.registry = json.loads((SITE_DIR / "pages.json").read_text(encoding="utf-8"))

    def test_approved_pages_are_registered_and_generated(self):
        self.assertTrue(
            {"home", "s3-intensivefix"}.issubset(
                {page["key"] for page in self.registry}
            )
        )
        self.assertTrue((self.build_root / "index.html").is_file())
        self.assertTrue((self.build_root / "s3-intensivefix/index.html").is_file())

    def test_header_footer_and_floating_action_come_from_shared_partials(self):
        for source_name in ("home.html", "s3-intensivefix.html"):
            source = (SITE_DIR / "pages" / source_name).read_text(encoding="utf-8")
            self.assertNotIn("<header", source)
            self.assertNotIn('<footer class="site-footer"', source)
            self.assertNotIn("floating-whatsapp", source)

        for relative_path in ("index.html", "s3-intensivefix/index.html"):
            generated = (self.build_root / relative_path).read_text(encoding="utf-8")
            self.assertIn("<header", generated)
            self.assertIn("<footer", generated)
            self.assertIn("floating-whatsapp", generated)
            self.assertIn(
                f"https://wa.me/{SITE_CONFIG['whatsappNumber']}", generated
            )

    def test_root_and_nested_assets_work_under_repository_subpath(self):
        home = (self.build_root / "index.html").read_text(encoding="utf-8")
        intensive = (self.build_root / "s3-intensivefix/index.html").read_text(
            encoding="utf-8"
        )
        self.assertIn('href="assets/css/base.css"', home)
        self.assertIn('src="assets/js/site.js"', home)
        self.assertIn('href="../assets/css/base.css"', intensive)
        self.assertIn('src="../assets/js/site.js"', intensive)

        for page_path in (
            self.build_root / "index.html",
            self.build_root / "s3-intensivefix/index.html",
        ):
            parser = PageParser()
            parser.feed(page_path.read_text(encoding="utf-8"))
            for element in parser.elements:
                for attribute in ("src", "href"):
                    reference = element["attrs"].get(attribute, "")
                    if not reference or not reference.split("#", 1)[0].endswith(
                        (".css", ".js", ".webp")
                    ):
                        continue
                    target = (page_path.parent / reference.split("#", 1)[0]).resolve()
                    self.assertTrue(target.is_file(), reference)


class InstitutionalPageTests(unittest.TestCase):
    ROUTES = {
        "about": "about/",
        "contact": "contact/",
        "trees": "trees/",
        "tuitionrecs": "tuitionrecs/",
    }
    REQUIRED_SECTIONS = {
        "about": {
            "hero",
            "mission",
            "quality",
            "tutors",
            "founder-message",
            "beliefs",
            "vision-mission-values",
            "final-cta",
        },
        "contact": {"hero", "faq-pointer", "contact-details", "final-cta"},
        "trees": {"hero", "tree-planting", "local-causes", "final-cta"},
        "tuitionrecs": {
            "hero",
            "recommendations",
            "independence-disclosure",
            "final-cta",
        },
    }

    @classmethod
    def setUpClass(cls):
        cls.build_root = build_test_site()
        cls.registry = json.loads((SITE_DIR / "pages.json").read_text(encoding="utf-8"))

    def read_page(self, key):
        route = self.ROUTES[key]
        source = (self.build_root / route / "index.html").read_text(encoding="utf-8")
        parser = PageParser()
        parser.feed(source)
        return source, parser

    def test_registry_contains_six_pages_and_institutional_routes(self):
        self.assertGreaterEqual(len(self.registry), 6)
        routes = {page["key"]: page["route"] for page in self.registry}
        for key, route in self.ROUTES.items():
            self.assertEqual(routes.get(key), route)

    def test_pages_have_one_h1_shared_landmarks_and_stable_sections(self):
        for key, required_ids in self.REQUIRED_SECTIONS.items():
            with self.subTest(page=key):
                source, parser = self.read_page(key)
                self.assertEqual(
                    len([element for element in parser.elements if element["tag"] == "h1"]),
                    1,
                )
                ids = {
                    element["attrs"].get("id")
                    for element in parser.elements
                    if element["attrs"].get("id")
                }
                self.assertTrue(required_ids.issubset(ids), required_ids - ids)
                self.assertIn('<header class="site-header">', source)
                self.assertIn('<footer class="site-footer">', source)
                self.assertIn('class="floating-whatsapp"', source)

    def test_archive_headings_and_meaning_are_preserved(self):
        expectations = {
            "about": (
                "About ThatBiotutor",
                "Our Mission",
                "Quality Teaching Only",
                "Our Tutors",
                "A Message From The Founder",
                "What We Believe In",
            ),
            "contact": ("Contact.", "Dear parent/student", "FAQs"),
            "trees": ("1. Planting Trees!", "2. SG Environmental Causes", "1982"),
            "tuitionrecs": ("Tuition Recommendations", "trial lesson"),
        }
        for key, text_values in expectations.items():
            source, _ = self.read_page(key)
            for text_value in text_values:
                with self.subTest(page=key, text=text_value):
                    self.assertIn(text_value, source)

    def test_contact_uses_whatsapp_without_a_form_or_duplicated_details(self):
        source, parser = self.read_page("contact")
        self.assertNotIn("<form", source.lower())
        whatsapp_links = [
            element["attrs"].get("href", "")
            for element in parser.elements_in("final-cta", "a")
        ]
        self.assertTrue(
            any(
                link.startswith(f"https://wa.me/{SITE_CONFIG['whatsappNumber']}")
                for link in whatsapp_links
            )
        )
        fragment = (SITE_DIR / "pages/contact.html").read_text(encoding="utf-8")
        self.assertNotIn("175 Bencoolen", fragment)
        self.assertNotIn("11am", fragment)

    def test_recommendation_links_are_external_and_disclosed_as_independent(self):
        source, parser = self.read_page("tuitionrecs")
        links = parser.elements_in("recommendations", "a")
        self.assertTrue(links)
        self.assertTrue(
            all(link["attrs"].get("href", "").startswith("https://") for link in links)
        )
        self.assertIn("independent", parser.section_text("independence-disclosure").lower())
        self.assertIn("docs.google.com/document/d/1Wwn8kzMaT59326gpnidbuGwRWMB6etmh7-Ff6eJiKLw", source)


class ClassesPageTests(unittest.TestCase):
    ROUTES = {
        "classes": "classes/",
        "sec-3-pure-g3-biology-tuition": "sec-3-pure-g3-biology-tuition/",
        "sec-4-pure-g3-biology-tuition": "sec-4-pure-g3-biology-tuition/",
    }
    CLASS_SECTIONS = {
        "hero",
        "pure-biology",
        "problems",
        "solutions",
        "differences",
        "practical",
        "results",
        "levels",
        "faq",
        "final-cta",
    }
    LEVEL_SECTIONS = {
        "hero",
        "learning-options",
        "curriculum",
        "topics",
        "exam-techniques",
        "results",
        "testimonials",
        "lesson-experience",
        "resources",
        "tutors",
        "suitability",
        "faq",
        "final-cta",
    }

    @classmethod
    def setUpClass(cls):
        cls.build_root = build_test_site()
        cls.registry = json.loads((SITE_DIR / "pages.json").read_text(encoding="utf-8"))

    def read_page(self, key):
        path = self.build_root / self.ROUTES[key] / "index.html"
        source = path.read_text(encoding="utf-8")
        parser = PageParser()
        parser.feed(source)
        return source, parser

    def test_registry_contains_nine_canonical_pages_and_sec3_alias(self):
        self.assertGreaterEqual(len(self.registry), 9)
        pages = {page["key"]: page for page in self.registry}
        for key, route in self.ROUTES.items():
            self.assertEqual(pages[key]["route"], route)
        self.assertEqual(
            pages["sec-3-pure-g3-biology-tuition"]["aliases"],
            ["classes/sec-3-pure-g3-biology-tuition/"],
        )

    def test_class_pages_have_one_h1_and_required_sections(self):
        for key in self.ROUTES:
            with self.subTest(page=key):
                _, parser = self.read_page(key)
                self.assertEqual(
                    len([element for element in parser.elements if element["tag"] == "h1"]),
                    1,
                )
                ids = {
                    element["attrs"].get("id")
                    for element in parser.elements
                    if element["attrs"].get("id")
                }
                required = self.CLASS_SECTIONS if key == "classes" else self.LEVEL_SECTIONS
                self.assertTrue(required.issubset(ids), required - ids)

    def test_curriculum_exam_method_and_results_copy_match_sources(self):
        classes, _ = self.read_page("classes")
        sec3, _ = self.read_page("sec-3-pure-g3-biology-tuition")
        sec4, _ = self.read_page("sec-4-pure-g3-biology-tuition")
        self.assertIn("Phase 1 — Teaching the Syllabus (Mar – Aug)", sec3)
        self.assertIn("Phase 3 — O Levels Intensives (Aug – Nov)", sec4)
        for source in (classes, sec3, sec4):
            self.assertIn("FIRED method", source)
            for value in ("98", "912+", "91%", "69.21%", "2.3", "1 in 2"):
                with self.subTest(value=value):
                    self.assertIn(value, source)

    def test_sec3_alias_is_accessible_relative_and_canonical(self):
        alias_path = (
            self.build_root
            / "classes/sec-3-pure-g3-biology-tuition/index.html"
        )
        source = alias_path.read_text(encoding="utf-8")
        self.assertIn(
            'href="https://www.thatbiotutor.com/sec-3-pure-g3-biology-tuition/"',
            source,
        )
        self.assertIn('href="../../sec-3-pure-g3-biology-tuition/"', source)
        sitemap = (self.build_root / "sitemap.xml").read_text(encoding="utf-8")
        self.assertNotIn("/classes/sec-3-pure-g3-biology-tuition/", sitemap)


class ResourcePageTests(unittest.TestCase):
    ROUTES = {
        "notes": "notes/",
        "celldivision": "celldivision/",
        "testimonials": "testimonials/",
    }
    REQUIRED_SECTIONS = {
        "notes": {"hero", "student-feedback", "why-free", "download", "faq", "final-cta"},
        "celldivision": {
            "hero",
            "dated-notice",
            "important",
            "access",
            "student-feedback",
            "final-cta",
        },
        "testimonials": {
            "hero",
            "results",
            "reviews",
            "student-stories",
            "class-2022",
            "class-2021",
            "final-cta",
        },
    }

    @classmethod
    def setUpClass(cls):
        cls.build_root = build_test_site()
        cls.registry = json.loads((SITE_DIR / "pages.json").read_text(encoding="utf-8"))

    def read_page(self, key):
        source = (
            self.build_root / self.ROUTES[key] / "index.html"
        ).read_text(encoding="utf-8")
        parser = PageParser()
        parser.feed(source)
        return source, parser

    def test_registry_and_manifest_contain_twelve_pages_plus_one_alias(self):
        self.assertEqual(len(self.registry), 12)
        routes = {page["key"]: page["route"] for page in self.registry}
        for key, route in self.ROUTES.items():
            self.assertEqual(routes.get(key), route)
        manifest = json.loads(
            (self.build_root / "build-manifest.json").read_text(encoding="utf-8")
        )
        self.assertEqual(len(manifest["pages"]), 12)
        self.assertEqual(
            manifest["aliases"], ["classes/sec-3-pure-g3-biology-tuition/"]
        )

    def test_pages_have_one_h1_shared_landmarks_and_required_sections(self):
        for key, required in self.REQUIRED_SECTIONS.items():
            with self.subTest(page=key):
                source, parser = self.read_page(key)
                self.assertEqual(
                    len([element for element in parser.elements if element["tag"] == "h1"]),
                    1,
                )
                ids = {
                    element["attrs"].get("id")
                    for element in parser.elements
                    if element["attrs"].get("id")
                }
                self.assertTrue(required.issubset(ids), required - ids)
                self.assertIn('<header class="site-header">', source)
                self.assertIn('<footer class="site-footer">', source)

    def test_archive_headings_and_dated_offer_are_preserved_honestly(self):
        notes, _ = self.read_page("notes")
        cell_division, parser = self.read_page("celldivision")
        testimonials, _ = self.read_page("testimonials")
        self.assertIn("Free O Level Biology Notes Singapore", notes)
        self.assertIn("Only For 2023 Sec 4 Pure Bio Students!", cell_division)
        self.assertIn("Results &amp; Testimonials", testimonials)
        dated_notice = parser.section_text("dated-notice")
        self.assertIn("2023", dated_notice)
        self.assertIn("archived", dated_notice.lower())
        self.assertNotIn("currently available", dated_notice.lower())

    def test_access_links_are_clear_and_external(self):
        notes, notes_parser = self.read_page("notes")
        cell, cell_parser = self.read_page("celldivision")
        testimonials, testimonials_parser = self.read_page("testimonials")
        self.assertIn("mailto:keefe@thatbiotutor.com", notes)
        self.assertIn("mailto:keefe@thatbiotutor.com", cell)
        review_links = testimonials_parser.elements_in("reviews", "a")
        self.assertTrue(
            any(
                link["attrs"].get("href", "").startswith("https://www.google.com/maps/")
                for link in review_links
            )
        )
        self.assertTrue(notes_parser.elements_in("download", "a"))
        self.assertTrue(cell_parser.elements_in("access", "a"))

    def test_migrated_pages_have_no_wix_forms_tracking_or_remote_images(self):
        for key in self.ROUTES:
            source, parser = self.read_page(key)
            with self.subTest(page=key):
                lowered = source.lower()
                self.assertNotIn("<form", lowered)
                self.assertNotIn("wixstatic", lowered)
                self.assertNotIn("google-analytics", lowered)
                self.assertNotIn("facebook.com/tr", lowered)
                for image in [e for e in parser.elements if e["tag"] == "img"]:
                    self.assertFalse(
                        image["attrs"].get("src", "").startswith(("http://", "https://"))
                    )

    def test_testimonial_summaries_are_not_presented_as_direct_quotes(self):
        sources = [
            (SITE_DIR / "pages/testimonials.html").read_text(encoding="utf-8"),
            (SITE_DIR / "partials/sections/testimonial-cards.html").read_text(
                encoding="utf-8"
            ),
        ]
        combined = "\n".join(sources)
        self.assertIn("A2 for N Level Combined Bio", combined)
        self.assertIn("A1 for Pure Bio", combined)
        self.assertIn("Summary of student feedback", combined)
        self.assertNotIn("<blockquote", combined)
        self.assertNotIn("“", combined)


class ProjectHandoffTests(unittest.TestCase):
    def test_client_guide_covers_global_and_page_edits(self):
        guide_path = REPO_ROOT / "docs/client-editing-guide.md"
        self.assertTrue(guide_path.is_file(), f"Missing {guide_path}")
        guide = guide_path.read_text(encoding="utf-8")
        for required_text in (
            "site/site.json",
            "site/pages.json",
            "site/pages/",
            "site/partials/header.html",
            "site/partials/footer.html",
            "site/partials/floating-whatsapp.html",
            "site/assets/images/asset-manifest.json",
            "site/assets/css/base.css",
            "CLIENT EDIT",
            "WebP",
            "alt text",
            "WhatsApp",
            "python3 scripts/preview_site.py",
            "GitHub",
            "build/",
            "2023",
        ):
            with self.subTest(required_text=required_text):
                self.assertIn(required_text, guide)

    def test_editing_guide_has_elementor_migration_map(self):
        guide_path = REPO_ROOT / "docs/client-editing-guide.md"
        self.assertTrue(guide_path.is_file(), f"Missing {guide_path}")
        guide = guide_path.read_text(encoding="utf-8")
        for required_text in (
            "Elementor Site Settings",
            "Global Colors",
            "Global Fonts",
            "Media Library",
            "Theme Builder",
            "Global Widget",
            "Elementor Container",
            "prefers-reduced-motion",
            "#hero",
            "#results",
            "#fasttrack",
            "#trial-guarantee",
            "#suitability",
            "#founder",
            "#faq",
            "#classroom",
            "#analysis-video",
            "#final-cta",
        ):
            with self.subTest(required_text=required_text):
                self.assertIn(required_text, guide)

    def test_legacy_wix_mirror_still_exists(self):
        self.assertTrue((MIRROR / "www.thatbiotutor.com/index.html").exists())

    def test_pages_have_no_broken_local_references(self):
        build_root = build_test_site()
        registry = json.loads((SITE_DIR / "pages.json").read_text(encoding="utf-8"))
        page_paths = [
            build_root / page["route"] / "index.html" if page["route"]
            else build_root / "index.html"
            for page in registry
        ]
        for page_path in page_paths:
            parser = PageParser()
            parser.feed(page_path.read_text(encoding="utf-8"))
            for element in parser.elements:
                for attribute in ("src", "href"):
                    value = element["attrs"].get(attribute, "")
                    if (
                        not value
                        or value.startswith(("#", "http://", "https://", "mailto:", "tel:"))
                    ):
                        continue
                    target = (page_path.parent / value.split("#", 1)[0]).resolve()
                    with self.subTest(page=page_path.name, reference=value):
                        self.assertTrue(target.exists(), f"Broken local reference: {value}")


class FinalReviewRegressionTests(unittest.TestCase):
    @classmethod
    def setUpClass(cls):
        build_root = build_test_site()
        cls.base_css = (SITE_DIR / "assets/css/base.css").read_text(encoding="utf-8")
        cls.components_css = (SITE_DIR / "assets/css/components.css").read_text(
            encoding="utf-8"
        )
        cls.pages_css = (SITE_DIR / "assets/css/pages.css").read_text(encoding="utf-8")
        cls.site_js = (SITE_DIR / "assets/js/site.js").read_text(encoding="utf-8")
        cls.home = (build_root / "index.html").read_text(encoding="utf-8")
        cls.intensive = (build_root / "s3-intensivefix/index.html").read_text(
            encoding="utf-8"
        )

    def test_light_cards_reset_dark_section_text_colour(self):
        self.assertRegex(
            self.components_css,
            r"\.card\s*\{[^}]*color:\s*var\(--color-ink\)",
        )

    def test_body_typography_is_not_overridden_by_form_control_reset(self):
        self.assertNotRegex(self.base_css, r"body,\s*\nbutton,\s*\ninput")
        self.assertIn("font-family: var(--font-sans)", self.base_css)
        self.assertIn("line-height: var(--line-body)", self.base_css)

    def test_carousels_preserve_keyboard_focus_and_real_touch_swipe(self):
        self.assertIn("carousel.tabIndex = 0", self.site_js)
        self.assertIn("pointerStartY", self.site_js)
        self.assertIn("Math.abs(distanceY)", self.site_js)
        self.assertRegex(
            self.components_css,
            r"\.media-carousel\.is-enhanced\s+\.media-carousel__viewport\s*\{[^}]*touch-action:\s*pan-y",
        )

    def test_mobile_navigation_remains_available_without_javascript(self):
        self.assertIn("document.documentElement.classList.add(\"menu-enhanced\")", self.site_js)
        self.assertRegex(
            self.components_css,
            r"\.menu-enhanced\s+\.site-nav__links\s*\{[^}]*display:\s*none",
        )

    def test_retained_copy_and_explicit_ctas_match_sources(self):
        for exact_home_copy in (
            ">Contact Us</a>",
            "completing it earns Homework Points for rewards",
            "Sec 3 classes open in March each year",
            "Read, reread, blurt out, copy out again and again",
            "out of their own drive, not because someone else is forcing them",
        ):
            self.assertIn(exact_home_copy, self.home)
        for exact_intensive_copy in (
            "Practical Sessions (20% of Grade)",
            "Students taking O Level Pure/G3 Biology in Sec 3 or Sec 4",
            "Students who genuinely want to improve in Bio, out of their own drive",
            ">Fix My Sec 3 Foundation Now</a>",
        ):
            self.assertIn(exact_intensive_copy, self.intensive)

    def test_carousel_figures_and_group_photo_are_not_overcropped(self):
        self.assertRegex(self.base_css, r"figure\s*\{[^}]*margin:\s*0")
        self.assertRegex(
            self.pages_css,
            r"\.home-hero\s+\.media-carousel__slide:nth-child\(3\)\s+img\s*\{[^}]*object-fit:\s*contain",
        )

    def test_programme_graphics_have_accessible_html_details(self):
        parser = PageParser()
        parser.feed(self.intensive)
        details = parser.section_text("program-details")
        for required_detail in (
            "$110 per 2-hour lesson",
            "$90 per 2-hour lesson",
            "$5 off per lesson",
            "16 November 2026",
            "30 November 2026",
            "1pm–6pm",
            "Cells, movement of substances, and biological molecules",
        ):
            self.assertIn(required_detail, details)


if __name__ == "__main__":
    unittest.main()
