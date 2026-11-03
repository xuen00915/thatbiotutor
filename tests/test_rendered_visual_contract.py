from __future__ import annotations

import os
import subprocess
import time
import unittest
from pathlib import Path

from playwright.sync_api import sync_playwright


ROOT = Path(__file__).resolve().parents[1]
BUILD = ROOT / "build"
BASE_URL = os.environ.get("TBT_BASE_URL", "http://127.0.0.1:8766")


class RenderedOriginalVisualContractTests(unittest.TestCase):
    CANONICAL_PATHS = (
        "/",
        "/classes/",
        "/sec-3-pure-g3-biology-tuition/",
        "/sec-4-pure-g3-biology-tuition/",
        "/testimonials/",
        "/notes/",
        "/celldivision/",
        "/about/",
        "/intensivefix/",
        "/trees/",
        "/tuitionrecs/",
        "/contact/",
    )

    @classmethod
    def setUpClass(cls) -> None:
        subprocess.run(
            ["python3", "scripts/build_site.py", "--output", "build"],
            cwd=ROOT,
            check=True,
            capture_output=True,
            text=True,
        )
        cls.server = subprocess.Popen(
            [
                "python3",
                "-m",
                "http.server",
                "8766",
                "--bind",
                "127.0.0.1",
                "--directory",
                str(BUILD),
            ],
            cwd=ROOT,
            stdout=subprocess.DEVNULL,
            stderr=subprocess.DEVNULL,
        )
        time.sleep(0.3)
        cls.playwright = sync_playwright().start()
        cls.browser = cls.playwright.chromium.launch(headless=True)

    @classmethod
    def tearDownClass(cls) -> None:
        cls.browser.close()
        cls.playwright.stop()
        cls.server.terminate()
        cls.server.wait(timeout=5)

    def open_page(self, path: str, width: int = 1440, height: int = 1000):
        page = self.browser.new_page(viewport={"width": width, "height": height})
        page.goto(f"{BASE_URL}{path}", wait_until="networkidle")
        self.addCleanup(page.close)
        return page

    def test_desktop_header_uses_original_logo_led_segmented_navigation(self) -> None:
        page = self.open_page("/")
        header = page.locator(".site-header").bounding_box()
        logo = page.locator(".site-header .site-brand img").bounding_box()
        brand_text_display = page.locator(".site-header .site-brand span").evaluate(
            "element => getComputedStyle(element).display"
        )
        labels = page.locator(".site-nav__links > .site-nav__link").all_text_contents()
        nav_style = page.locator(".site-nav__links > .site-nav__link").first.evaluate(
            """element => {
                const style = getComputedStyle(element);
                return {
                    borderLeftWidth: parseFloat(style.borderLeftWidth),
                    fontFamily: style.fontFamily,
                    fontSize: parseFloat(style.fontSize),
                    fontWeight: parseInt(style.fontWeight, 10),
                    height: element.getBoundingClientRect().height,
                };
            }"""
        )

        self.assertIsNotNone(header)
        self.assertIsNotNone(logo)
        self.assertGreaterEqual(header["height"], 64)
        self.assertLessEqual(header["height"], 70)
        self.assertGreaterEqual(logo["width"], 54)
        self.assertGreaterEqual(logo["height"], 54)
        self.assertEqual(brand_text_display, "none")
        self.assertEqual(
            labels,
            [
                "Home",
                "Classes",
                "Results & Testimonials",
                "Free Bio Notes",
                "About Us",
                "Our Causes",
            ],
        )
        self.assertGreaterEqual(nav_style["borderLeftWidth"], 1)
        self.assertIn("futura book", nav_style["fontFamily"].lower())
        self.assertGreaterEqual(nav_style["fontSize"], 16)
        self.assertLessEqual(nav_style["fontWeight"], 500)
        self.assertGreaterEqual(nav_style["height"], 64)
        contact = page.locator(".site-header__contact")
        self.assertEqual(contact.count(), 1)
        self.assertEqual(contact.inner_text(), "Contact Us")
        self.assertTrue((contact.get_attribute("href") or "").endswith("contact/"))

    def test_global_typography_and_sections_use_original_light_vocabulary(self) -> None:
        page = self.open_page("/classes/")
        body_style = page.locator("body").evaluate(
            """element => {
                const style = getComputedStyle(element);
                const heading = getComputedStyle(document.querySelector('h1'));
                return {
                    background: style.backgroundColor,
                    family: style.fontFamily,
                    headingFamily: heading.fontFamily,
                    headingWeight: parseInt(heading.fontWeight, 10),
                };
            }"""
        )
        self.assertEqual(body_style["background"], "rgb(255, 255, 255)")
        self.assertIn("montserrat", body_style["family"].lower())
        self.assertIn("montserrat", body_style["headingFamily"].lower())
        self.assertEqual(body_style["headingWeight"], 700)

    def test_classes_and_tuition_pages_use_full_width_original_photo_heroes(self) -> None:
        expected_heights = {
            "/classes/": (560, 600),
            "/sec-3-pure-g3-biology-tuition/": (700, 760),
            "/sec-4-pure-g3-biology-tuition/": (700, 760),
            "/about/": (310, 340),
            "/testimonials/": (430, 470),
        }
        for path, (minimum_height, maximum_height) in expected_heights.items():
            with self.subTest(path=path):
                page = self.open_page(path)
                hero = page.locator('[data-layout-slot="hero"]')
                classes = hero.get_attribute("class") or ""
                box = hero.bounding_box()
                self.assertIn("full-photo-hero", classes)
                self.assertIsNotNone(box)
                self.assertGreaterEqual(box["height"], minimum_height)
                self.assertLessEqual(box["height"], maximum_height)
                self.assertEqual(hero.locator(".hero__media > img").count(), 1)

    def test_free_notes_uses_original_centered_dark_showcase_not_split_campaign_hero(self) -> None:
        page = self.open_page("/notes/")
        hero = page.locator('[data-layout-slot="hero"]')
        hero_style = hero.evaluate(
            """element => {
                const style = getComputedStyle(element);
                return {
                    background: style.backgroundColor,
                    color: style.color,
                    height: element.getBoundingClientRect().height,
                    columns: style.gridTemplateColumns,
                };
            }"""
        )
        main_text = page.locator("main").inner_text()
        self.assertEqual(hero.locator(".notes-device-preview").count(), 1)
        image = hero.locator(".notes-device-preview").bounding_box()
        self.assertEqual(hero_style["background"], "rgb(48, 48, 48)")
        self.assertEqual(hero_style["color"], "rgb(255, 255, 255)")
        self.assertNotIn(" ", hero_style["columns"].strip())
        self.assertIsNotNone(image)
        self.assertGreaterEqual(image["width"], 420)
        self.assertLessEqual(image["width"], 560)
        self.assertIn("How do I download it?", main_text)
        self.assertIn("Hey Pure Bio Student, is this you?", main_text)
        self.assertIn("What's The Catch?", main_text)

    def test_simple_original_routes_do_not_use_generic_campaign_heroes(self) -> None:
        expected = {
            "/celldivision/": "resource-intro",
            "/trees/": "utility-title-bar",
            "/tuitionrecs/": "utility-title-bar",
            "/contact/": "utility-page",
        }
        for path, required_class in expected.items():
            with self.subTest(path=path):
                page = self.open_page(path)
                self.assertEqual(page.locator(f".{required_class}").count(), 1)
                self.assertEqual(page.locator(".original-hero").count(), 0)

    def test_mobile_has_no_horizontal_overflow_and_original_logo_scale(self) -> None:
        page = self.open_page("/", width=390, height=844)
        dimensions = page.evaluate(
            """() => ({
                clientWidth: document.documentElement.clientWidth,
                scrollWidth: document.documentElement.scrollWidth,
            })"""
        )
        logo = page.locator(".site-header .site-brand img").bounding_box()
        self.assertEqual(dimensions["scrollWidth"], dimensions["clientWidth"])
        self.assertIsNotNone(logo)
        self.assertGreaterEqual(logo["width"], 54)
        self.assertLessEqual(logo["width"], 66)
        self.assertTrue(page.locator(".site-menu-toggle").is_visible())

    def test_shared_navigation_carousel_and_faq_behaviors_work_in_browser(self) -> None:
        mobile = self.open_page("/", width=390, height=844)
        toggle = mobile.locator(".site-menu-toggle")
        menu = mobile.locator(".site-nav__links")
        toggle.click()
        self.assertEqual(toggle.get_attribute("aria-expanded"), "true")
        self.assertTrue(menu.is_visible())

        home = self.open_page("/")
        carousel = home.locator(".media-carousel").first
        status = carousel.locator("[data-carousel-status]")
        self.assertIn("Image 1 of", status.inner_text())
        carousel.locator("[data-carousel-next]").click()
        self.assertIn("Image 2 of", status.inner_text())

        classes = self.open_page("/classes/")
        second_faq = classes.locator(".faq").nth(1)
        faq_button = second_faq.locator("[data-faq-button]")
        faq_panel = second_faq.locator("[data-faq-panel]")
        self.assertEqual(faq_button.get_attribute("aria-expanded"), "false")
        self.assertTrue(faq_panel.is_hidden())
        faq_button.click()
        self.assertEqual(faq_button.get_attribute("aria-expanded"), "true")
        self.assertTrue(faq_panel.is_visible())

    def test_mobile_navigation_links_remain_visible_without_javascript(self) -> None:
        context = self.browser.new_context(
            viewport={"width": 390, "height": 844}, java_script_enabled=False
        )
        self.addCleanup(context.close)
        page = context.new_page()
        page.goto(f"{BASE_URL}/", wait_until="networkidle")
        menu = page.locator(".site-nav__links")
        self.assertTrue(menu.is_visible())
        self.assertTrue(menu.locator("a").first.is_visible())
        self.assertTrue(page.locator(".floating-whatsapp").is_visible())

    def test_every_canonical_route_renders_cleanly_at_release_widths(self) -> None:
        for width, height in ((390, 844), (768, 900), (1280, 900)):
            for path in self.CANONICAL_PATHS:
                with self.subTest(path=path, width=width):
                    page = self.open_page(path, width=width, height=height)
                    page.evaluate(
                        """async () => {
                            const images = [...document.images];
                            images.forEach(image => { image.loading = 'eager'; });
                            await Promise.all(images.map(image => {
                                if (image.complete) return Promise.resolve();
                                return new Promise(resolve => {
                                    image.addEventListener('load', resolve, { once: true });
                                    image.addEventListener('error', resolve, { once: true });
                                });
                            }));
                        }"""
                    )
                    health = page.evaluate(
                        """() => ({
                            clientWidth: document.documentElement.clientWidth,
                            scrollWidth: document.documentElement.scrollWidth,
                            brokenImages: [...document.images]
                                .filter(image => !image.complete || image.naturalWidth === 0)
                                .map(image => image.getAttribute('src')),
                            h1Count: document.querySelectorAll('main h1').length,
                            hasHeader: Boolean(document.querySelector('.site-header')),
                            hasFooter: Boolean(document.querySelector('.site-footer')),
                            hasWhatsApp: Boolean(document.querySelector('.floating-whatsapp')),
                        })"""
                    )
                    self.assertEqual(health["scrollWidth"], health["clientWidth"])
                    self.assertEqual(health["brokenImages"], [])
                    self.assertEqual(health["h1Count"], 1)
                    self.assertTrue(health["hasHeader"])
                    self.assertTrue(health["hasFooter"])
                    self.assertTrue(health["hasWhatsApp"])


if __name__ == "__main__":
    unittest.main()

