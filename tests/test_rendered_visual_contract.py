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
        inner = page.locator(".site-header__inner").bounding_box()
        brand = page.locator(".site-header .site-brand").bounding_box()
        nav = page.locator(".site-header .site-nav").bounding_box()
        logo = page.locator(".site-header .site-brand img").bounding_box()
        brand_text_display = page.locator(".site-header .site-brand span").evaluate(
            "element => getComputedStyle(element).display"
        )
        labels = page.locator(
            ".site-nav__links > .site-nav__link, .site-nav__item > .site-nav__link"
        ).all_text_contents()
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
        self.assertIsNotNone(inner)
        self.assertIsNotNone(brand)
        self.assertIsNotNone(nav)
        self.assertIsNotNone(logo)
        self.assertGreaterEqual(header["height"], 64)
        self.assertLessEqual(header["height"], 70)
        self.assertGreaterEqual(logo["width"], 54)
        self.assertGreaterEqual(logo["height"], 54)
        self.assertAlmostEqual(inner["x"], 230, delta=1)
        self.assertAlmostEqual(inner["width"], 980, delta=1)
        self.assertAlmostEqual(brand["x"], 278, delta=1)
        self.assertAlmostEqual(brand["width"], 65, delta=1)
        self.assertAlmostEqual(nav["x"], 366, delta=1)
        self.assertAlmostEqual(nav["width"], 705, delta=2)
        self.assertAlmostEqual(nav["y"], 15, delta=1)
        self.assertAlmostEqual(nav["height"], 50, delta=1)
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
        self.assertEqual(nav_style["borderLeftWidth"], 0)
        self.assertIn("futura book", nav_style["fontFamily"].lower())
        self.assertGreaterEqual(nav_style["fontSize"], 16)
        self.assertLessEqual(nav_style["fontWeight"], 500)
        self.assertAlmostEqual(nav_style["height"], 50, delta=1)
        links = page.locator(".site-nav__links > .site-nav__link")
        first_link = links.first.bounding_box()
        last_link = links.last.bounding_box()
        self.assertIsNotNone(first_link)
        self.assertIsNotNone(last_link)
        self.assertAlmostEqual(first_link["x"], 366, delta=1)
        self.assertAlmostEqual(first_link["width"], 77, delta=1)
        self.assertAlmostEqual(last_link["x"] + last_link["width"], 1071, delta=2)
        self.assertEqual(
            links.first.evaluate("element => getComputedStyle(element).borderLeftWidth"),
            "0px",
        )
        self.assertIn(
            "inset",
            links.nth(1).evaluate("element => getComputedStyle(element).boxShadow"),
        )
        self.assertEqual(
            links.last.evaluate("element => getComputedStyle(element).borderRightWidth"),
            "0px",
        )
        self.assertEqual(page.locator(".site-header__contact").count(), 0)

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
        self.assertGreaterEqual(image["width"], 1000)
        self.assertLessEqual(image["width"], 1080)
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

    def test_mobile_and_tablet_navigation_panels_use_the_available_width(self) -> None:
        for width in (390, 768, 1024):
            with self.subTest(width=width):
                page = self.open_page("/", width=width, height=844)
                toggle = page.locator(".site-menu-toggle")
                menu = page.locator(".site-nav__links")
                toggle_box = toggle.bounding_box()
                self.assertIsNotNone(toggle_box)
                self.assertAlmostEqual(
                    toggle_box["x"] + toggle_box["width"], width - 16, delta=2
                )
                toggle.click()

                menu_box = menu.bounding_box()
                first_link_box = menu.locator("a").first.bounding_box()
                last_link_box = menu.locator("a").last.bounding_box()
                self.assertIsNotNone(menu_box)
                self.assertIsNotNone(first_link_box)
                self.assertIsNotNone(last_link_box)
                self.assertAlmostEqual(menu_box["x"] + menu_box["width"], width, delta=2)
                self.assertAlmostEqual(first_link_box["x"], menu_box["x"], delta=2)
                self.assertAlmostEqual(first_link_box["width"], menu_box["width"], delta=2)
                self.assertAlmostEqual(last_link_box["width"], menu_box["width"], delta=2)
                if width < 768:
                    self.assertAlmostEqual(menu_box["width"], width, delta=2)
                else:
                    self.assertGreaterEqual(menu_box["width"], 320)
                    self.assertLessEqual(menu_box["width"], 384)
                self.assertEqual(
                    page.evaluate(
                        "document.documentElement.scrollWidth - document.documentElement.clientWidth"
                    ),
                    0,
                )

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

    def test_shared_classes_navigation_exposes_level_dropdown(self) -> None:
        page = self.open_page("/", width=390, height=844)
        page.locator(".site-menu-toggle").click()
        classes_item = page.locator(".site-nav__item--classes")
        self.assertEqual(classes_item.count(), 1)
        submenu = classes_item.locator(".site-submenu")
        toggle = classes_item.locator("[data-submenu-toggle]")
        self.assertEqual(
            submenu.locator("a").evaluate_all(
                "links => links.map(link => new URL(link.href).pathname)"
            ),
            [
                "/classes/",
                "/sec-3-pure-g3-biology-tuition/",
                "/sec-4-pure-g3-biology-tuition/",
            ],
        )
        self.assertEqual(toggle.get_attribute("aria-expanded"), "false")
        self.assertTrue(submenu.is_hidden())
        toggle.click()
        self.assertEqual(toggle.get_attribute("aria-expanded"), "true")
        self.assertTrue(submenu.is_visible())

    def test_notes_reviews_match_original_single_column_archive(self) -> None:
        page = self.open_page("/notes/", width=1920, height=1000)
        section = page.locator("#student-feedback")
        heading = section.locator("h2")
        reviews = section.locator(".notes-review")
        first = reviews.first
        first_box = first.bounding_box()
        styles = page.evaluate(
            """() => {
                const css = selector => getComputedStyle(document.querySelector(selector));
                return {
                    headingSize: parseFloat(css('#student-feedback h2').fontSize),
                    eyebrowDisplay: css('#student-feedback .eyebrow').display,
                    introDisplay: css('#student-feedback .section-heading > p:last-child').display,
                    columns: css('#student-feedback .notes-review-wall').gridTemplateColumns.split(' ').length,
                    borderWidth: parseFloat(css('#student-feedback .notes-review').borderTopWidth),
                    bodySize: parseFloat(css('#student-feedback .notes-review p').fontSize),
                    bodyStyle: css('#student-feedback .notes-review p').fontStyle,
                    nameSize: parseFloat(css('#student-feedback .notes-review strong').fontSize),
                    nameColor: css('#student-feedback .notes-review strong').color,
                };
            }"""
        )
        self.assertGreaterEqual(reviews.count(), 16)
        self.assertIn("Jezelle | ⭐⭐⭐⭐⭐", first.inner_text())
        self.assertIn("lifesaver", first.inner_text())
        self.assertIsNotNone(first_box)
        self.assertAlmostEqual(first_box["width"], 980, delta=2)
        self.assertEqual(styles["headingSize"], 30)
        self.assertEqual(styles["eyebrowDisplay"], "none")
        self.assertEqual(styles["introDisplay"], "none")
        self.assertEqual(styles["columns"], 1)
        self.assertEqual(styles["borderWidth"], 0)
        self.assertEqual(styles["bodySize"], 22)
        self.assertEqual(styles["bodyStyle"], "italic")
        self.assertEqual(styles["nameSize"], 22)
        self.assertEqual(styles["nameColor"], "rgb(43, 178, 35)")

    def test_testimonials_restores_original_video_story_archive(self) -> None:
        page = self.open_page("/testimonials/", width=1920, height=1000)
        stories = page.locator(".testimonial-story")
        self.assertEqual(page.locator("#results").count(), 0)
        self.assertEqual(stories.count(), 61)
        first = stories.first
        video_box = first.locator("iframe").bounding_box()
        quote_style = first.locator(".testimonial-story__quote").evaluate(
            """element => ({
                size: parseFloat(getComputedStyle(element).fontSize),
                style: getComputedStyle(element).fontStyle,
                align: getComputedStyle(element).textAlign,
            })"""
        )
        self.assertIn("RJxR3ONbylQ", first.locator("iframe").get_attribute("src"))
        self.assertIn("Alicia", first.inner_text())
        self.assertIsNotNone(video_box)
        self.assertAlmostEqual(video_box["width"], 694, delta=2)
        self.assertAlmostEqual(video_box["height"], 390, delta=2)
        self.assertEqual(quote_style, {"size": 22, "style": "italic", "align": "center"})
        self.assertEqual(page.locator(".testimonial-stories-title").count(), 5)
        self.assertEqual(
            page.locator(".testimonial-era-heading").all_text_contents(),
            ["Class of 2022", "Class of 2021"],
        )

    def test_rebuilt_home_carousel_counters_and_faq_are_fully_interactive(self) -> None:
        page = self.open_page("/")
        carousel = page.locator(".home-hero")
        status = carousel.locator("[data-carousel-status]")
        carousel.focus()
        carousel.press("End")
        self.assertEqual(status.inner_text(), "Image 5 of 5")
        carousel.press("ArrowRight")
        self.assertEqual(status.inner_text(), "Image 1 of 5")
        carousel.press("ArrowLeft")
        self.assertEqual(status.inner_text(), "Image 5 of 5")

        stats = page.locator("#track-record .stats")
        stats.scroll_into_view_if_needed()
        page.wait_for_timeout(1100)
        self.assertEqual(
            page.locator("#track-record [data-counter-key]").all_text_contents(),
            ["912+", "91%", "2.3", "1 in 2"],
        )

        first_faq = page.locator("#faq .faq").first
        faq_button = first_faq.locator("[data-faq-button]")
        faq_panel = first_faq.locator("[data-faq-panel]")
        self.assertEqual(faq_button.get_attribute("aria-expanded"), "false")
        self.assertTrue(faq_panel.is_hidden())
        faq_button.click()
        self.assertEqual(faq_button.get_attribute("aria-expanded"), "true")
        self.assertTrue(faq_panel.is_visible())

    def test_home_reduced_motion_shows_final_counter_values_immediately(self) -> None:
        page = self.browser.new_page(viewport={"width": 1440, "height": 1000})
        self.addCleanup(page.close)
        page.emulate_media(reduced_motion="reduce")
        page.goto(f"{BASE_URL}/", wait_until="networkidle")
        self.assertEqual(
            page.locator("#track-record [data-counter-key]").all_text_contents(),
            ["912+", "91%", "2.3", "1 in 2"],
        )

    def test_home_remains_complete_and_readable_without_javascript(self) -> None:
        context = self.browser.new_context(
            viewport={"width": 390, "height": 844}, java_script_enabled=False
        )
        self.addCleanup(context.close)
        page = context.new_page()
        page.goto(f"{BASE_URL}/", wait_until="networkidle")

        self.assertEqual(page.locator(".home-hero [data-carousel-slide]").count(), 5)
        self.assertTrue(
            page.locator(".home-hero [data-carousel-slide]").first.is_visible()
        )
        self.assertEqual(
            page.locator("#track-record [data-counter-key]").all_text_contents(),
            ["912+", "91%", "2.3", "1 in 2"],
        )
        self.assertEqual(page.locator("#faq [data-faq-panel]:visible").count(), 9)
        self.assertGreaterEqual(
            page.locator('main a[href^="https://wa.me/"]:visible').count(), 2
        )
        self.assertEqual(
            page.evaluate(
                "document.documentElement.scrollWidth - document.documentElement.clientWidth"
            ),
            0,
        )

    def test_home_hero_first_image_uses_its_lower_focal_point_on_desktop(self) -> None:
        page = self.open_page("/", width=1440, height=900)
        hero_media = page.locator(".home-hero .media-carousel__viewport").bounding_box()
        first_image = page.locator(
            ".home-hero .media-carousel__slide:nth-child(1) img"
        )
        first_image_box = first_image.bounding_box()
        object_position = first_image.evaluate(
            "element => getComputedStyle(element).objectPosition"
        )

        self.assertIsNotNone(hero_media)
        self.assertIsNotNone(first_image_box)
        self.assertGreaterEqual(hero_media["height"], 500)
        self.assertLessEqual(hero_media["height"], 560)
        self.assertGreaterEqual(hero_media["width"] / hero_media["height"], 2.55)
        self.assertLessEqual(hero_media["width"] / hero_media["height"], 2.75)
        self.assertAlmostEqual(first_image_box["width"], hero_media["width"], delta=1)
        self.assertAlmostEqual(first_image_box["height"], hero_media["height"], delta=1)
        self.assertEqual(object_position, "50% 56%")

    def test_home_carousel_controls_match_inquire_geometry(self) -> None:
        for width, height in ((1440, 1000), (390, 844)):
            with self.subTest(width=width):
                page = self.open_page("/", width=width, height=height)
                hero = page.locator(".home-hero").bounding_box()
                previous = page.locator(".home-hero [data-carousel-prev]")
                next_button = page.locator(".home-hero [data-carousel-next]")
                previous_box = previous.bounding_box()
                next_box = next_button.bounding_box()
                dots = page.locator(".home-hero [data-carousel-dot]")
                active_dot = dots.first.evaluate(
                    """element => {
                        const style = getComputedStyle(element, '::before');
                        return {
                            width: parseFloat(style.width),
                            height: parseFloat(style.height),
                            background: style.backgroundColor,
                        };
                    }"""
                )
                inactive_dot = dots.nth(1).evaluate(
                    "element => getComputedStyle(element, '::before').backgroundColor"
                )

                self.assertIsNotNone(hero)
                self.assertIsNotNone(previous_box)
                self.assertIsNotNone(next_box)
                self.assertAlmostEqual(previous_box["width"], 30, delta=1)
                self.assertAlmostEqual(previous_box["height"], 30, delta=1)
                self.assertAlmostEqual(previous_box["x"] - hero["x"], 10, delta=1)
                self.assertAlmostEqual(hero["x"] + hero["width"] - next_box["x"] - next_box["width"], 10, delta=1)
                self.assertEqual(previous.locator("svg").count(), 1)
                self.assertEqual(next_button.locator("svg").count(), 1)
                self.assertEqual(dots.count(), 5)
                self.assertTrue(dots.first.is_visible())
                self.assertEqual(active_dot["width"], 6)
                self.assertEqual(active_dot["height"], 6)
                self.assertEqual(active_dot["background"], "rgb(47, 138, 39)")
                self.assertEqual(inactive_dot, "rgb(255, 255, 255)")

    def test_home_matches_inquire_desktop_section_system(self) -> None:
        page = self.open_page("/", width=1440, height=1000)
        hero_intro = page.locator(".inquire-hero__intro").bounding_box()
        hero_heading = page.locator("#home-title").bounding_box()
        colors = page.evaluate(
            """() => Object.fromEntries([
                ['hero', '.inquire-hero'],
                ['fasttrack', '#fasttrack'],
                ['tutors', '#tutors'],
                ['trial', '#trial-guarantee'],
                ['closing', '#closing-cta'],
                ['footer', '.site-footer'],
            ].map(([key, selector]) => [
                key,
                getComputedStyle(document.querySelector(selector)).backgroundColor,
            ]))"""
        )
        stats_columns = page.locator("#track-record .stats").evaluate(
            "element => getComputedStyle(element).gridTemplateColumns.split(' ').length"
        )
        suitability_columns = page.locator(".inquire-suitability__grid").evaluate(
            "element => getComputedStyle(element).gridTemplateColumns.split(' ').length"
        )

        self.assertIsNotNone(hero_intro)
        self.assertIsNotNone(hero_heading)
        self.assertLessEqual(hero_intro["width"], 980)
        self.assertAlmostEqual(
            hero_intro["x"] + hero_intro["width"] / 2, 720, delta=2
        )
        self.assertAlmostEqual(
            hero_heading["x"] + hero_heading["width"] / 2, 720, delta=2
        )
        self.assertEqual(colors["hero"], "rgb(255, 255, 255)")
        self.assertNotEqual(colors["fasttrack"], colors["hero"])
        self.assertNotEqual(colors["tutors"], colors["hero"])
        self.assertEqual(colors["trial"], colors["closing"])
        self.assertEqual(colors["footer"], colors["closing"])
        self.assertEqual(stats_columns, 4)
        self.assertEqual(suitability_columns, 2)
        self.assertEqual(page.locator("#fasttrack .feature-row").count(), 5)
        self.assertEqual(page.locator("#tutors .tutor-card").count(), 4)
        self.assertEqual(page.locator("#practical-sessions .gallery__item").count(), 3)
        self.assertEqual(page.locator("#classroom-life .gallery__item").count(), 3)

        closing_prompt = page.locator("#closing-cta p").last.bounding_box()
        closing_button = page.locator("#closing-cta .button").bounding_box()
        self.assertIsNotNone(closing_prompt)
        self.assertIsNotNone(closing_button)
        self.assertGreaterEqual(
            closing_button["y"] - (closing_prompt["y"] + closing_prompt["height"]),
            24,
        )

    def test_home_keeps_inquire_layout_with_original_type_scale(self) -> None:
        desktop = self.open_page("/", width=1440, height=1000)
        desktop_sizes = desktop.evaluate(
            """() => Object.fromEntries([
                ['hero', '#home-title'],
                ['section', '#student-results-title'],
                ['subsection', '#results-title'],
                ['proof', '.inquire-hero__proof'],
                ['refund', '.inquire-hero__refund'],
                ['stat', '.home-track-record .stat__value'],
            ].map(([key, selector]) => [
                key,
                parseFloat(getComputedStyle(document.querySelector(selector)).fontSize),
            ]))"""
        )
        self.assertEqual(desktop_sizes["hero"], 60)
        self.assertEqual(desktop_sizes["section"], 36)
        self.assertEqual(desktop_sizes["subsection"], 30)
        self.assertEqual(desktop_sizes["proof"], 22)
        self.assertEqual(desktop_sizes["refund"], 16)
        self.assertEqual(desktop_sizes["stat"], 40)

        mobile = self.open_page("/", width=390, height=844)
        mobile_sizes = mobile.evaluate(
            """() => Object.fromEntries([
                ['hero', '#home-title'],
                ['section', '#student-results-title'],
                ['subsection', '#results-title'],
                ['proof', '.inquire-hero__proof'],
            ].map(([key, selector]) => [
                key,
                parseFloat(getComputedStyle(document.querySelector(selector)).fontSize),
            ]))"""
        )
        self.assertEqual(mobile_sizes["hero"], 36)
        self.assertEqual(mobile_sizes["section"], 24)
        self.assertEqual(mobile_sizes["subsection"], 20)
        self.assertEqual(mobile_sizes["proof"], 18)

    def test_home_eyebrow_and_floating_whatsapp_match_original_alignment(self) -> None:
        for width, height in ((1440, 1000), (768, 900), (390, 844)):
            with self.subTest(width=width):
                page = self.open_page("/", width=width, height=height)
                eyebrow = page.locator(".inquire-hero .eyebrow")
                eyebrow_box = eyebrow.bounding_box()
                eyebrow_style = eyebrow.evaluate(
                    "element => getComputedStyle(element).textAlign"
                )
                whatsapp = page.locator(".floating-whatsapp")
                whatsapp_box = whatsapp.bounding_box()
                whatsapp_style = whatsapp.evaluate(
                    """element => {
                        const style = getComputedStyle(element);
                        return {
                            background: style.backgroundColor,
                            radius: style.borderRadius,
                            zIndex: parseInt(style.zIndex, 10),
                        };
                    }"""
                )

                self.assertIsNotNone(eyebrow_box)
                self.assertEqual(eyebrow_style, "center")
                self.assertAlmostEqual(
                    eyebrow_box["x"] + eyebrow_box["width"] / 2,
                    width / 2,
                    delta=2,
                )
                self.assertIsNotNone(whatsapp_box)
                self.assertAlmostEqual(whatsapp_box["width"], 60, delta=1)
                self.assertAlmostEqual(whatsapp_box["height"], 60, delta=1)
                self.assertAlmostEqual(width - whatsapp_box["x"] - whatsapp_box["width"], 25, delta=1)
                self.assertAlmostEqual(height - whatsapp_box["y"] - whatsapp_box["height"], 25, delta=1)
                self.assertEqual(whatsapp_style["background"], "rgb(48, 191, 57)")
                self.assertEqual(whatsapp_style["radius"], "50%")
                self.assertGreaterEqual(whatsapp_style["zIndex"], 100000)
                self.assertEqual(whatsapp.locator("svg").count(), 1)

    def test_home_transition_video_fasttrack_and_proof_match_inquire_fidelity(self) -> None:
        page = self.open_page("/", width=1440, height=1000)
        fidelity = page.evaluate(
            """() => {
                const style = selector => getComputedStyle(document.querySelector(selector));
                const rect = selector => document.querySelector(selector).getBoundingClientRect();
                return {
                    quoteBackground: style('.inquire-pain__list p').backgroundColor,
                    quoteColor: style('.inquire-pain__list p').color,
                    quoteSize: parseFloat(style('.inquire-pain__list p').fontSize),
                    quoteStyle: style('.inquire-pain__list p').fontStyle,
                    quoteRadius: parseFloat(style('.inquire-pain__list p').borderRadius),
                    resultHeadingSize: parseFloat(style('#student-results-title').fontSize),
                    resultHeadingColor: style('#student-results-title').color,
                    resultCopySize: parseFloat(style('.inquire-results .section-heading p').fontSize),
                    videoBackground: style('#video-transition').backgroundColor,
                    videoTitleSize: parseFloat(style('#video-transition-title').fontSize),
                    videoWidth: rect('#video-transition .video-embed').width,
                    fasttrackBackground: style('#fasttrack').backgroundColor,
                    featureRadius: parseFloat(style('#fasttrack .feature-row').borderRadius),
                    featureTitleBackground: style('#fasttrack .feature-row__title').backgroundColor,
                    featureTitleColor: style('#fasttrack .feature-row__title').color,
                    featureColumns: style('#fasttrack .feature-row__comparison').gridTemplateColumns.split(' ').length,
                    featureRightBackground: style('#fasttrack .feature-row__fasttrack').backgroundColor,
                    schoolHeadingSize: parseFloat(style('.inquire-proof__schools-title').fontSize),
                    gradeHeadingSize: parseFloat(style('.inquire-proof__grade-title').fontSize),
                    marqueeAnimation: style('.logo-cloud').animationName,
                    crestWidth: rect('.logo-cloud img').width,
                    reviewColumns: style('.inquire-reviews').gridTemplateColumns.split(' ').length,
                    reviewSize: parseFloat(style('.inquire-review').fontSize),
                    googleWidth: rect('.inquire-google-review img').width,
                };
            }"""
        )

        self.assertEqual(fidelity["quoteBackground"], "rgb(239, 243, 238)")
        self.assertEqual(fidelity["quoteColor"], "rgb(97, 108, 99)")
        self.assertEqual(fidelity["quoteSize"], 18)
        self.assertEqual(fidelity["quoteStyle"], "italic")
        self.assertEqual(fidelity["quoteRadius"], 18)
        self.assertEqual(fidelity["resultHeadingSize"], 36)
        self.assertEqual(fidelity["resultHeadingColor"], "rgb(25, 34, 26)")
        self.assertEqual(fidelity["resultCopySize"], 18)
        self.assertEqual(fidelity["videoBackground"], "rgb(255, 255, 255)")
        self.assertEqual(fidelity["videoTitleSize"], 60)
        self.assertGreaterEqual(fidelity["videoWidth"], 1100)
        self.assertNotEqual(fidelity["fasttrackBackground"], "rgb(255, 255, 255)")
        self.assertEqual(fidelity["featureRadius"], 18)
        self.assertEqual(fidelity["featureTitleBackground"], "rgb(67, 180, 58)")
        self.assertEqual(fidelity["featureTitleColor"], "rgb(255, 255, 255)")
        self.assertEqual(fidelity["featureColumns"], 2)
        self.assertEqual(fidelity["featureRightBackground"], "rgba(240, 248, 238, 0.6)")
        self.assertEqual(fidelity["schoolHeadingSize"], 24)
        self.assertEqual(fidelity["gradeHeadingSize"], 24)
        self.assertEqual(fidelity["marqueeAnimation"], "school-logo-marquee")
        self.assertGreaterEqual(fidelity["crestWidth"], 90)
        self.assertEqual(page.locator(".logo-cloud img").count(), 24)
        self.assertEqual(fidelity["reviewColumns"], 3)
        self.assertEqual(fidelity["reviewSize"], 18)
        self.assertGreaterEqual(fidelity["googleWidth"], 500)

    def test_home_new_inquire_sections_remain_readable_on_mobile(self) -> None:
        page = self.open_page("/", width=390, height=844)
        mobile = page.evaluate(
            """() => ({
                overflow: document.documentElement.scrollWidth - document.documentElement.clientWidth,
                videoTitleSize: parseFloat(getComputedStyle(document.querySelector('#video-transition-title')).fontSize),
                featureColumns: getComputedStyle(document.querySelector('#fasttrack .feature-row__comparison')).gridTemplateColumns.split(' ').length,
                reviewColumns: getComputedStyle(document.querySelector('.inquire-reviews')).gridTemplateColumns.split(' ').length,
                googleWidth: document.querySelector('.inquire-google-review img').getBoundingClientRect().width,
            })"""
        )
        self.assertEqual(mobile["overflow"], 0)
        self.assertEqual(mobile["videoTitleSize"], 36)
        self.assertEqual(mobile["featureColumns"], 1)
        self.assertEqual(mobile["reviewColumns"], 1)
        self.assertLessEqual(mobile["googleWidth"], 358)

    def test_home_inquire_sections_stack_without_overflow_on_tablet_and_mobile(self) -> None:
        for width in (390, 768):
            with self.subTest(width=width):
                page = self.open_page("/", width=width, height=900)
                page.locator("#closing-cta").scroll_into_view_if_needed()
                layout = page.evaluate(
                    """() => ({
                        overflow: document.documentElement.scrollWidth - document.documentElement.clientWidth,
                        stats: getComputedStyle(document.querySelector('#track-record .stats')).gridTemplateColumns.split(' ').length,
                        feature: getComputedStyle(document.querySelector('#fasttrack .feature-row')).gridTemplateColumns.split(' ').length,
                        suitability: getComputedStyle(document.querySelector('.inquire-suitability__grid')).gridTemplateColumns.split(' ').length,
                        classroom: getComputedStyle(document.querySelector('#classroom-life .gallery')).gridTemplateColumns.split(' ').length,
                    })"""
                )
                self.assertEqual(layout["overflow"], 0)
                self.assertLessEqual(layout["stats"], 2)
                self.assertEqual(layout["feature"], 1)
                self.assertEqual(layout["suitability"], 1)
                self.assertEqual(layout["classroom"], 1)
                self.assertTrue(
                    page.locator("#closing-cta .button").is_visible()
                )

    def test_mobile_navigation_links_remain_visible_without_javascript(self) -> None:
        context = self.browser.new_context(
            viewport={"width": 390, "height": 844}, java_script_enabled=False
        )
        self.addCleanup(context.close)
        page = context.new_page()
        page.goto(f"{BASE_URL}/", wait_until="networkidle")
        menu = page.locator(".site-nav__links")
        self.assertTrue(menu.is_visible())
        menu_box = menu.bounding_box()
        self.assertIsNotNone(menu_box)
        self.assertAlmostEqual(menu_box["x"], 16, delta=2)
        self.assertAlmostEqual(menu_box["width"], 358, delta=2)
        self.assertEqual(
            page.evaluate(
                "document.documentElement.scrollWidth - document.documentElement.clientWidth"
            ),
            0,
        )
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

