#!/usr/bin/env python3
"""Build the deterministic, local WebP library used by the child theme."""

from __future__ import annotations

import argparse
import hashlib
import json
import os
import re
import tempfile
from dataclasses import asdict, dataclass
from pathlib import Path
from typing import Iterable, List, Sequence, Tuple

from PIL import Image, ImageOps


RASTER_EXTENSIONS = {".jpg", ".jpeg", ".png", ".webp", ".gif", ".tif", ".tiff"}


@dataclass(frozen=True)
class MediaSource:
    key: str
    source: Path
    alt: str
    kind: str
    pages: Tuple[str, ...]
    object_position: str = "50% 50%"
    license_status: str = "site-owned"


@dataclass(frozen=True)
class MediaRecord:
    key: str
    source: str
    file: str
    sha256: str
    alt: str
    width: int
    height: int
    fit: str
    objectPosition: str
    pages: List[str]
    licenseStatus: str


def _sha256(path: Path) -> str:
    digest = hashlib.sha256()
    with path.open("rb") as stream:
        for chunk in iter(lambda: stream.read(1024 * 1024), b""):
            digest.update(chunk)
    return digest.hexdigest()


def _natural_key(path: Path) -> Tuple[object, ...]:
    return tuple(int(part) if part.isdigit() else part.lower() for part in re.split(r"(\d+)", path.name))


def _slug(value: str) -> str:
    return re.sub(r"[^a-z0-9]+", "_", value.lower()).strip("_")


def _images(root: Path) -> List[Path]:
    if not root.is_dir():
        return []
    return sorted(
        (path for path in root.rglob("*") if path.is_file() and path.suffix.lower() in RASTER_EXTENSIONS),
        key=lambda path: str(path).lower(),
    )


def _source(
    key: str,
    path: Path,
    alt: str,
    kind: str,
    pages: Sequence[str],
    object_position: str = "50% 50%",
    license_status: str = "site-owned",
) -> MediaSource:
    return MediaSource(
        key=key,
        source=path.resolve(),
        alt=alt.strip(),
        kind=kind,
        pages=tuple(pages),
        object_position=object_position,
        license_status=license_status,
    )


def collect_sources(crawl: Path, modified_source: Path, intensivefix_source: Path) -> List[MediaSource]:
    """Collect permitted raster sources in a stable logical order; fonts are never collected."""
    sources: List[MediaSource] = []

    crawl_root = crawl / "assets" / "media-originals"
    for index, path in enumerate(_images(crawl_root), start=1):
        sources.append(
            _source(
                "original_%03d" % index,
                path,
                "ThatBioTutor original website image %d" % index,
                "photo",
                ("original",),
                license_status="public-site-capture",
            )
        )

    curated_root = modified_source / "site" / "assets" / "images"
    for index, path in enumerate(_images(curated_root), start=1):
        relative = path.relative_to(curated_root)
        page = relative.parts[0] if len(relative.parts) > 1 else "shared"
        kind = "text_artwork" if page in {"notes", "intensivefix"} else "photo"
        logical_path = relative.with_suffix("").as_posix()
        sources.append(
            _source(
                "curated_" + (_slug(logical_path) or ("image_%03d" % index)),
                path,
                "ThatBioTutor %s image %d" % (page.replace("-", " "), index),
                kind,
                (page,),
            )
        )

    supplied_groups = [
        ("1A. Homepage top slider images", "home_hero", "Home hero image", "photo", "home"),
        ("1B. Images under numbers", "home_number", "Home results image", "photo", "home"),
        ("1C. Life inside classroom", "home_classroom", "Life inside the ThatBioTutor classroom", "photo", "home"),
    ]
    for folder, key_prefix, alt_prefix, kind, page in supplied_groups:
        paths = sorted(_images(intensivefix_source / folder), key=_natural_key)
        for index, path in enumerate(paths, start=1):
            sources.append(
                _source(
                    "%s_%02d" % (key_prefix, index),
                    path,
                    "%s %d" % (alt_prefix, index),
                    kind,
                    (page,),
                    "50% 75%" if key_prefix == "home_hero" and index == 1 else "50% 50%",
                )
            )

    programme = sorted(_images(intensivefix_source / "2A. intensivefix graphics"), key=_natural_key)
    for index, path in enumerate(programme, start=1):
        sources.append(
            _source(
                "intensivefix_program_%02d" % index,
                path,
                "IntensiveFix programme graphic %d" % index,
                "text_artwork",
                ("intensivefix",),
            )
        )

    reviews = sorted(_images(intensivefix_source / "2B. Intensivefix reviews"), key=_natural_key)
    if reviews:
        sources.append(
            _source(
                "intensivefix_student_reviews",
                reviews[0],
                "IntensiveFix student reviews",
                "text_artwork",
                ("intensivefix",),
            )
        )

    keys = [item.key for item in sources]
    if len(keys) != len(set(keys)):
        raise ValueError("media source keys must be unique")
    return sources


def convert_media(source: MediaSource, output_root: Path) -> MediaRecord:
    """Convert one source to WebP without resizing; identical bytes share a file."""
    if not source.alt.strip():
        raise ValueError("alt text is required for %s" % source.key)
    if not source.source.is_file():
        raise FileNotFoundError(source.source)

    source_digest = _sha256(source.source)
    relative = Path("files") / (source_digest[:32] + ".webp")
    destination = output_root / relative
    destination.parent.mkdir(parents=True, exist_ok=True)

    with Image.open(source.source) as opened:
        animated_formats = {"GIF", "PNG", "WEBP"}
        if int(getattr(opened, "n_frames", 1)) > 1 and str(opened.format).upper() in animated_formats:
            raise ValueError("animated input is not permitted: %s" % source.source)
        opened.seek(0)
        image = ImageOps.exif_transpose(opened).copy()

    has_alpha = image.mode in {"RGBA", "LA"} or (image.mode == "P" and "transparency" in image.info)
    if has_alpha:
        image = image.convert("RGBA")
    elif image.mode != "RGB":
        image = image.convert("RGB")

    if not destination.exists():
        handle, temporary_name = tempfile.mkstemp(prefix=".tbt-media-", suffix=".webp", dir=str(destination.parent))
        os.close(handle)
        temporary = Path(temporary_name)
        try:
            save_options = {"format": "WEBP", "method": 6, "exact": True}
            if source.kind == "text_artwork":
                save_options.update({"lossless": True})
            else:
                save_options.update({"quality": 86})
            image.save(temporary, **save_options)
            os.replace(str(temporary), str(destination))
        finally:
            if temporary.exists():
                temporary.unlink()

    with Image.open(destination) as converted:
        width, height = converted.size

    return MediaRecord(
        key=source.key,
        source="provenance://" + source.source.name,
        file=relative.as_posix(),
        sha256=_sha256(destination),
        alt=source.alt,
        width=int(width),
        height=int(height),
        fit="contain" if source.kind == "text_artwork" else "cover",
        objectPosition=source.object_position,
        pages=list(source.pages),
        licenseStatus=source.license_status,
    )


def build_manifest(records: Iterable[MediaRecord], output_root: Path) -> Path:
    """Write the portable manifest atomically and return its path."""
    output_root.mkdir(parents=True, exist_ok=True)
    destination = output_root / "media-manifest.json"
    payload = {"schemaVersion": 1, "items": [asdict(record) for record in records]}
    handle, temporary_name = tempfile.mkstemp(prefix=".tbt-manifest-", suffix=".json", dir=str(output_root))
    os.close(handle)
    temporary = Path(temporary_name)
    try:
        temporary.write_text(json.dumps(payload, indent=2, ensure_ascii=False) + "\n", encoding="utf-8")
        os.replace(str(temporary), str(destination))
    finally:
        if temporary.exists():
            temporary.unlink()
    return destination


def parse_args() -> argparse.Namespace:
    parser = argparse.ArgumentParser(description=__doc__)
    parser.add_argument("--crawl", type=Path, required=True)
    parser.add_argument("--modified-source", type=Path, required=True)
    parser.add_argument("--intensivefix-source", type=Path, required=True)
    parser.add_argument("--output", type=Path, required=True)
    return parser.parse_args()


def main() -> int:
    args = parse_args()
    sources = collect_sources(args.crawl, args.modified_source, args.intensivefix_source)
    records = [convert_media(source, args.output) for source in sources]
    manifest = build_manifest(records, args.output)
    print("Prepared %d media records at %s" % (len(records), manifest))
    return 0


if __name__ == "__main__":
    raise SystemExit(main())
