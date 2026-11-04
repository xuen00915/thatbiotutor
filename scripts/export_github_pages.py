#!/usr/bin/env python3
"""Export the local native WordPress site as a GitHub Pages artifact."""

from __future__ import annotations

import argparse
import shutil
import subprocess
from pathlib import Path
from urllib.parse import quote


ROUTES = (
    "/",
    "/classes/",
    "/sec-3-pure-g3-biology-tuition/",
    "/sec-4-pure-g3-biology-tuition/",
    "/testimonials/",
    "/notes/",
    "/celldivision/",
    "/about/",
    "/contact/",
    "/trees/",
    "/tuitionrecs/",
    "/intensivefix/",
)


def _text_files(root: Path):
    for path in root.rglob("*"):
        if not path.is_file():
            continue
        try:
            path.read_text(encoding="utf-8")
        except (UnicodeDecodeError, OSError):
            continue
        yield path


def normalize_query_assets(root: Path) -> None:
    """Rename wget's query-versioned files and update converted references."""
    replacements: dict[str, str] = {}
    for path in sorted(root.rglob("*"), key=lambda item: len(item.parts), reverse=True):
        if not path.is_file() or "?" not in path.name:
            continue
        clean_name = path.name.split("?", 1)[0]
        target = path.with_name(clean_name)
        if target.exists():
            if target.read_bytes() != path.read_bytes():
                raise RuntimeError(f"Conflicting exported assets: {path} and {target}")
            path.unlink()
        else:
            path.rename(target)
        replacements[path.name] = clean_name

    if not replacements:
        return

    for path in _text_files(root):
        content = path.read_text(encoding="utf-8")
        updated = content
        for old_name, clean_name in replacements.items():
            candidates = {
                old_name,
                old_name.replace("?", "%3F"),
                quote(old_name, safe="/.="),
            }
            for candidate in candidates:
                updated = updated.replace(candidate, clean_name)
        if updated != content:
            path.write_text(updated, encoding="utf-8")


def rewrite_origin(root: Path, local_origin: str, public_origin: str) -> None:
    """Replace uncrawled absolute local URLs in exported text files."""
    local_origin = local_origin.rstrip("/")
    public_origin = public_origin.rstrip("/")
    replacements = {
        local_origin: public_origin,
        quote(local_origin, safe=""): quote(public_origin, safe=""),
    }
    for path in _text_files(root):
        content = path.read_text(encoding="utf-8")
        updated = content
        for old_origin, new_origin in replacements.items():
            updated = updated.replace(old_origin, new_origin)
        if updated != content:
            path.write_text(updated, encoding="utf-8")


def export_site(output: Path, local_origin: str, public_origin: str) -> None:
    output = output.resolve()
    cwd = Path.cwd().resolve()
    if output in {Path("/"), cwd}:
        raise ValueError("Refusing to replace a broad export directory")
    if output.exists():
        shutil.rmtree(output)
    output.mkdir(parents=True)

    urls = [f"{local_origin.rstrip('/')}{route}" for route in ROUTES]
    subprocess.run(
        [
            "wget",
            "--quiet",
            "--recursive",
            "--level=1",
            "--page-requisites",
            "--convert-links",
            "--adjust-extension",
            "--no-host-directories",
            "--execute=robots=off",
            "--reject-regex=(wp-json|/feed/|/comments/|xmlrpc\\.php|[?]p=)",
            f"--directory-prefix={output}",
            *urls,
        ],
        check=True,
    )

    normalize_query_assets(output)
    rewrite_origin(output, local_origin, public_origin)
    (output / ".nojekyll").write_text("", encoding="utf-8")

    missing = [route for route in ROUTES if not (output / route.lstrip("/") / "index.html").is_file()]
    if missing:
        raise RuntimeError(f"Missing exported routes: {', '.join(missing)}")


def main() -> None:
    parser = argparse.ArgumentParser(description=__doc__)
    parser.add_argument("--output", type=Path, default=Path("build"))
    parser.add_argument("--local-origin", default="http://localhost:8888")
    parser.add_argument("--public-origin", required=True)
    args = parser.parse_args()
    export_site(args.output, args.local_origin, args.public_origin)


if __name__ == "__main__":
    main()
