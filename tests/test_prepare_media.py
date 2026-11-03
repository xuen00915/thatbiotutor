from __future__ import annotations

import json
import importlib.util
import sys
import tempfile
import unittest
from pathlib import Path

from PIL import Image

MODULE_PATH = Path(__file__).resolve().parents[1] / "scripts" / "prepare-media.py"
SPEC = importlib.util.spec_from_file_location("tbt_prepare_media", MODULE_PATH)
if SPEC is None or SPEC.loader is None:
    raise RuntimeError(f"Cannot load media tool from {MODULE_PATH}")
MEDIA_MODULE = importlib.util.module_from_spec(SPEC)
sys.modules[SPEC.name] = MEDIA_MODULE
SPEC.loader.exec_module(MEDIA_MODULE)

MediaSource = MEDIA_MODULE.MediaSource
build_manifest = MEDIA_MODULE.build_manifest
collect_sources = MEDIA_MODULE.collect_sources
convert_media = MEDIA_MODULE.convert_media


class PrepareMediaTest(unittest.TestCase):
    def setUp(self) -> None:
        self.temp = tempfile.TemporaryDirectory()
        self.root = Path(self.temp.name)
        self.output = self.root / "output"

    def tearDown(self) -> None:
        self.temp.cleanup()

    def make_image(
        self,
        path: Path,
        colour: tuple[int, ...],
        mode: str = "RGB",
        size: tuple[int, int] = (40, 30),
    ) -> Path:
        path.parent.mkdir(parents=True, exist_ok=True)
        Image.new(mode, size, colour).save(path)
        return path

    def source(self, path: Path, **overrides: object) -> MediaSource:
        values: dict[str, object] = {
            "key": "sample_photo",
            "source": path,
            "alt": "Students learning Biology",
            "kind": "photo",
            "pages": ("home",),
            "object_position": "50% 50%",
            "license_status": "site-owned",
        }
        values.update(overrides)
        return MediaSource(**values)

    def test_duplicate_bytes_share_one_output_file(self) -> None:
        first = self.make_image(self.root / "first.jpg", (10, 80, 20))
        duplicate = self.root / "duplicate.jpg"
        duplicate.write_bytes(first.read_bytes())

        one = convert_media(self.source(first, key="first"), self.output)
        two = convert_media(self.source(duplicate, key="duplicate"), self.output)

        self.assertEqual(one.file, two.file)
        self.assertEqual(1, len(list(self.output.rglob("*.webp"))))

    def test_same_basename_with_different_bytes_stays_distinct(self) -> None:
        first = self.make_image(self.root / "a" / "photo.png", (255, 0, 0))
        second = self.make_image(self.root / "b" / "photo.png", (0, 0, 255))

        one = convert_media(self.source(first, key="red"), self.output)
        two = convert_media(self.source(second, key="blue"), self.output)

        self.assertNotEqual(one.file, two.file)

    def test_alpha_png_preserves_transparency(self) -> None:
        source = self.make_image(self.root / "logo.png", (20, 120, 80, 0), mode="RGBA")
        record = convert_media(self.source(source, key="logo", kind="text_artwork"), self.output)
        with Image.open(self.output / record.file) as converted:
            self.assertIn(converted.mode, {"RGBA", "LA"})
            self.assertEqual(0, converted.getpixel((0, 0))[-1])

    def test_animated_input_is_rejected(self) -> None:
        path = self.root / "animated.gif"
        frames = [Image.new("RGB", (20, 20), colour) for colour in ((255, 0, 0), (0, 0, 255))]
        frames[0].save(path, save_all=True, append_images=frames[1:], duration=100, loop=0)
        with self.assertRaisesRegex(ValueError, "animated"):
            convert_media(self.source(path), self.output)

    def test_multiframe_nonanimated_document_uses_first_frame(self) -> None:
        path = self.root / "scan.tiff"
        frames = [Image.new("RGB", (20, 20), colour) for colour in ((20, 40, 60), (80, 100, 120))]
        frames[0].save(path, save_all=True, append_images=frames[1:])
        record = convert_media(self.source(path), self.output)
        self.assertEqual(20, record.width)
        self.assertEqual(20, record.height)

    def test_alt_text_is_required(self) -> None:
        source = self.make_image(self.root / "photo.jpg", (1, 2, 3))
        with self.assertRaisesRegex(ValueError, "alt"):
            convert_media(self.source(source, alt=""), self.output)

    def test_fit_and_home_focal_position_are_manifest_data(self) -> None:
        photo = self.make_image(self.root / "photo.jpg", (1, 2, 3))
        artwork = self.make_image(self.root / "art.png", (255, 255, 255, 0), mode="RGBA")
        photo_record = convert_media(
            self.source(photo, key="home_hero_01", object_position="50% 75%"), self.output
        )
        art_record = convert_media(
            self.source(artwork, key="intensivefix_program_01", kind="text_artwork"), self.output
        )
        self.assertEqual("cover", photo_record.fit)
        self.assertEqual("50% 75%", photo_record.objectPosition)
        self.assertEqual("contain", art_record.fit)

    def test_collect_sources_orders_intensivefix_and_excludes_fonts(self) -> None:
        crawl = self.root / "crawl"
        modified = self.root / "modified"
        supplied = self.root / "supplied"
        self.make_image(crawl / "assets/media-originals/original.jpg", (1, 1, 1))
        font = crawl / "assets/font/Futura.woff2"
        font.parent.mkdir(parents=True, exist_ok=True)
        font.write_bytes(b"unlicensed")
        self.make_image(modified / "site/assets/images/notes/slide.webp", (2, 2, 2))
        for index in range(10, 0, -1):
            self.make_image(
                supplied / "2A. intensivefix graphics" / f"{index} graphic.jpg",
                (index, index, index),
            )
        self.make_image(supplied / "2B. Intensivefix reviews/7 testims.jpg", (9, 9, 9))

        sources = collect_sources(crawl, modified, supplied)
        self.assertIn("curated_notes_slide", [item.key for item in sources])
        intensive_keys = [item.key for item in sources if item.key.startswith("intensivefix_program_")]
        self.assertEqual([f"intensivefix_program_{index:02d}" for index in range(1, 11)], intensive_keys)
        self.assertFalse(any(item.source.suffix.lower().startswith(".woff") for item in sources))
        self.assertIn("intensivefix_student_reviews", [item.key for item in sources])

    def test_manifest_contains_required_portable_fields(self) -> None:
        source = self.make_image(self.root / "photo.jpg", (1, 2, 3), size=(80, 60))
        record = convert_media(self.source(source), self.output)
        manifest_path = build_manifest([record], self.output)
        payload = json.loads(manifest_path.read_text())
        item = payload["items"][0]
        self.assertEqual(
            {
                "key",
                "source",
                "file",
                "sha256",
                "alt",
                "width",
                "height",
                "fit",
                "objectPosition",
                "pages",
                "licenseStatus",
            },
            set(item),
        )
        self.assertEqual(80, item["width"])
        self.assertEqual(60, item["height"])


if __name__ == "__main__":
    unittest.main()
