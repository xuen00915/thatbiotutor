#!/usr/bin/env python3
"""Build the client-editable ThatBioTutor sources into a static website."""

from __future__ import annotations

import argparse
import html
import json
import posixpath
import re
import shutil
import tempfile
from dataclasses import dataclass
from html.parser import HTMLParser
from pathlib import Path, PurePosixPath
from typing import Dict, FrozenSet, Iterable, List, Mapping, Optional, Sequence, Tuple
from urllib.parse import quote, urlencode, urlsplit


PLACEHOLDER_RE = re.compile(r"{{\s*([A-Za-z_][A-Za-z0-9_]*)\s*}}")
INCLUDE_RE = re.compile(r"<!--\s*INCLUDE:\s*([^<>]+?)\s*-->")
RASTER_SUFFIXES = {".avif", ".bmp", ".gif", ".jpeg", ".jpg", ".png", ".tif", ".tiff"}


class BuildError(RuntimeError):
    """A source or output error that makes the site unsafe to publish."""


@dataclass(frozen=True)
class PageSpec:
    key: str
    route: str
    source: str
    title: str
    description: str
    body_class: str
    nav_key: str
    whatsapp_message: str
    og_image: str
    context: Mapping[str, str]
    aliases: Tuple[str, ...]


@dataclass(frozen=True)
class BuildReport:
    pages: Tuple[str, ...]
    aliases: Tuple[str, ...]
    files: Tuple[str, ...]


class _ReferenceParser(HTMLParser):
    def __init__(self) -> None:
        super().__init__(convert_charrefs=True)
        self.references: List[Tuple[str, str]] = []
        self.image_references: List[str] = []

    def handle_starttag(self, tag: str, attrs: List[Tuple[str, Optional[str]]]) -> None:
        for name, value in attrs:
            if name in {"href", "src"} and value is not None:
                self.references.append((name, value))
            if tag == "img" and name == "src" and value is not None:
                self.image_references.append(value)
            if tag == "img" and name == "srcset" and value is not None:
                self.image_references.extend(
                    candidate.strip().split()[0]
                    for candidate in value.split(",")
                    if candidate.strip()
                )

    def handle_startendtag(
        self, tag: str, attrs: List[Tuple[str, Optional[str]]]
    ) -> None:
        self.handle_starttag(tag, attrs)


def _is_within(path: Path, parent: Path) -> bool:
    try:
        path.relative_to(parent)
    except ValueError:
        return False
    return True


def _safe_site_path(site_dir: Path, value: str, label: str) -> Path:
    if not isinstance(value, str) or not value.strip():
        raise BuildError(f"{label} must be a non-empty relative path")
    if "\\" in value:
        raise BuildError(f"{label} must use POSIX separators: {value!r}")
    pure = PurePosixPath(value)
    if pure.is_absolute() or any(part in {"", ".", ".."} for part in pure.parts):
        raise BuildError(f"{label} is outside site/: {value!r}")
    candidate = (site_dir / pure).resolve()
    if not _is_within(candidate, site_dir.resolve()):
        raise BuildError(f"{label} is outside site/: {value!r}")
    return candidate


def _normalise_route(value: object, label: str) -> str:
    if not isinstance(value, str):
        raise BuildError(f"{label} must be a string")
    if value == "":
        return ""
    if value != value.strip() or value.startswith("/") or "\\" in value:
        raise BuildError(f"{label} must be a relative directory route: {value!r}")
    without_slash = value[:-1] if value.endswith("/") else value
    parts = without_slash.split("/")
    if not without_slash or any(part in {"", ".", ".."} for part in parts):
        raise BuildError(f"{label} contains an unsafe segment: {value!r}")
    if any(any(character in part for character in "?#:") for part in parts):
        raise BuildError(f"{label} contains URL control characters: {value!r}")
    return "/".join(parts) + "/"


def relative_root(route: str) -> str:
    """Return the relative prefix from a canonical directory route to the root."""

    normalised = _normalise_route(route, "route")
    if not normalised:
        return ""
    return "../" * len(PurePosixPath(normalised).parts)


def render_template(text: str, context: Mapping[str, str], source_name: str) -> str:
    """Replace documented placeholders, rejecting missing or malformed ones."""

    def replace(match: re.Match[str]) -> str:
        key = match.group(1)
        if key not in context:
            raise BuildError(f"{source_name}: unknown placeholder {key!r}")
        value = context[key]
        if not isinstance(value, str):
            raise BuildError(f"{source_name}: placeholder {key!r} is not text")
        return value

    rendered = PLACEHOLDER_RE.sub(replace, text)
    if "{{" in rendered or "}}" in rendered:
        raise BuildError(f"{source_name}: unresolved or malformed placeholder")
    return rendered


def expand_includes(
    text: str,
    site_dir: Path,
    source_name: str,
    stack: Tuple[Path, ...] = (),
) -> str:
    """Expand site-root-relative include comments with traversal/cycle checks."""

    site_dir = site_dir.resolve()

    def replace(match: re.Match[str]) -> str:
        include_name = match.group(1).strip()
        include_path = _safe_site_path(site_dir, include_name, "include")
        if include_path in stack:
            chain = " -> ".join(str(path.relative_to(site_dir)) for path in stack)
            raise BuildError(f"{source_name}: recursive include {chain} -> {include_name}")
        if not include_path.is_file():
            raise BuildError(f"{source_name}: missing include {include_name!r}")
        included = include_path.read_text(encoding="utf-8")
        return expand_includes(
            included,
            site_dir,
            include_name,
            stack + (include_path,),
        )

    expanded = INCLUDE_RE.sub(replace, text)
    if "<!-- INCLUDE:" in expanded:
        raise BuildError(f"{source_name}: malformed or unresolved include")
    return expanded


def _read_json(path: Path, label: str) -> object:
    try:
        return json.loads(path.read_text(encoding="utf-8"))
    except FileNotFoundError as error:
        raise BuildError(f"missing {label}: {path}") from error
    except json.JSONDecodeError as error:
        raise BuildError(f"invalid JSON in {label} at line {error.lineno}: {error.msg}") from error


def _require_text(mapping: Mapping[str, object], key: str, label: str) -> str:
    value = mapping.get(key)
    if not isinstance(value, str) or not value.strip():
        raise BuildError(f"{label}.{key} must be non-empty text")
    return value


def _load_site(site_dir: Path) -> Dict[str, object]:
    value = _read_json(site_dir / "site.json", "site/site.json")
    if not isinstance(value, dict):
        raise BuildError("site/site.json must contain an object")
    required = (
        "siteName",
        "siteUrl",
        "logo",
        "favicon",
        "whatsappNumber",
        "defaultWhatsappMessage",
        "address",
        "hours",
        "copyright",
    )
    for key in required:
        _require_text(value, key, "site")
    parsed_url = urlsplit(str(value["siteUrl"]))
    if (
        parsed_url.scheme not in {"http", "https"}
        or not parsed_url.netloc
        or parsed_url.path not in {"", "/"}
        or parsed_url.query
        or parsed_url.fragment
    ):
        raise BuildError("site.siteUrl must be an http(s) origin without a path")
    value["siteUrl"] = str(value["siteUrl"]).rstrip("/")
    navigation = value.get("navigation")
    if not isinstance(navigation, list) or not navigation:
        raise BuildError("site.navigation must be a non-empty list")
    seen_navigation = set()
    for index, item in enumerate(navigation):
        if not isinstance(item, dict):
            raise BuildError(f"site.navigation[{index}] must be an object")
        key = _require_text(item, "key", f"site.navigation[{index}]")
        _require_text(item, "label", f"site.navigation[{index}]")
        item["route"] = _normalise_route(item.get("route"), f"site.navigation[{index}].route")
        if key in seen_navigation:
            raise BuildError(f"duplicate navigation key {key!r}")
        seen_navigation.add(key)
    footer_navigation = value.get("footerNavigation", navigation)
    if not isinstance(footer_navigation, list):
        raise BuildError("site.footerNavigation must be a list")
    for index, item in enumerate(footer_navigation):
        if not isinstance(item, dict):
            raise BuildError(f"site.footerNavigation[{index}] must be an object")
        _require_text(item, "label", f"site.footerNavigation[{index}]")
        item["route"] = _normalise_route(
            item.get("route"), f"site.footerNavigation[{index}].route"
        )
    value["footerNavigation"] = footer_navigation
    return value


def _load_pages(site_dir: Path, site: Mapping[str, object]) -> Tuple[PageSpec, ...]:
    value = _read_json(site_dir / "pages.json", "site/pages.json")
    if not isinstance(value, list) or not value:
        raise BuildError("site/pages.json must contain a non-empty list")
    pages: List[PageSpec] = []
    keys = set()
    canonical_routes = set()
    aliases = set()
    for index, item in enumerate(value):
        label = f"pages[{index}]"
        if not isinstance(item, dict):
            raise BuildError(f"{label} must be an object")
        key = _require_text(item, "key", label)
        route = _normalise_route(item.get("route"), f"{label}.route")
        source = _require_text(item, "source", label)
        source_path = _safe_site_path(site_dir, source, f"{label}.source")
        if not source_path.is_file():
            raise BuildError(f"{label}.source does not exist: {source!r}")
        if key in keys:
            raise BuildError(f"duplicate page key {key!r}")
        if route in canonical_routes:
            raise BuildError(f"duplicate canonical route {route!r}")
        if route in aliases:
            raise BuildError(f"canonical route collides with alias {route!r}")
        keys.add(key)
        canonical_routes.add(route)
        raw_aliases = item.get("aliases", [])
        if not isinstance(raw_aliases, list):
            raise BuildError(f"{label}.aliases must be a list")
        page_aliases = []
        for alias_index, raw_alias in enumerate(raw_aliases):
            alias = _normalise_route(raw_alias, f"{label}.aliases[{alias_index}]")
            if not alias:
                raise BuildError(f"{label}.aliases[{alias_index}] cannot be the home route")
            if alias in aliases or alias in canonical_routes:
                raise BuildError(f"duplicate or colliding alias route {alias!r}")
            aliases.add(alias)
            page_aliases.append(alias)
        raw_context = item.get("context", {})
        if not isinstance(raw_context, dict) or not all(
            isinstance(name, str) and isinstance(text, str)
            for name, text in raw_context.items()
        ):
            raise BuildError(f"{label}.context must map text keys to text values")
        whatsapp_message = item.get("whatsappMessage", site["defaultWhatsappMessage"])
        if not isinstance(whatsapp_message, str) or not whatsapp_message.strip():
            raise BuildError(f"{label}.whatsappMessage must be non-empty text")
        og_image = item.get("ogImage", site.get("defaultOgImage", site["logo"]))
        if not isinstance(og_image, str) or not og_image.strip():
            raise BuildError(f"{label}.ogImage must be non-empty text")
        pages.append(
            PageSpec(
                key=key,
                route=route,
                source=source,
                title=_require_text(item, "title", label),
                description=_require_text(item, "description", label),
                body_class=_require_text(item, "bodyClass", label),
                nav_key=_require_text(item, "navKey", label),
                whatsapp_message=whatsapp_message,
                og_image=og_image,
                context=dict(raw_context),
                aliases=tuple(page_aliases),
            )
        )
    return tuple(pages)


def _route_href(root: str, route: str) -> str:
    return (root + route) if route else (root or "./")


def _navigation_html(
    entries: Iterable[Mapping[str, object]], root: str, active_key: str, css_class: str
) -> str:
    links = []
    for entry in entries:
        key = str(entry.get("key", ""))
        current = ' aria-current="page"' if key == active_key else ""
        if css_class == "site-nav__link" and key == "classes":
            classes_href = html.escape(
                _route_href(root, str(entry["route"])), quote=True
            )
            sec3_href = html.escape(
                _route_href(root, "sec-3-pure-g3-biology-tuition/"), quote=True
            )
            sec4_href = html.escape(
                _route_href(root, "sec-4-pure-g3-biology-tuition/"), quote=True
            )
            links.append(
                '<div class="site-nav__item site-nav__item--classes">'
                f'<a class="{css_class}" href="{classes_href}"{current}>Classes</a>'
                '<button class="site-submenu-toggle" type="button" '
                'aria-expanded="false" aria-controls="classes-submenu" '
                'aria-label="Show Classes submenu" data-submenu-toggle>'
                '<span aria-hidden="true">⌄</span></button>'
                '<div class="site-submenu" id="classes-submenu" data-submenu hidden>'
                f'<a href="{classes_href}">Classes</a>'
                f'<a href="{sec3_href}">Sec 3</a>'
                f'<a href="{sec4_href}">Sec 4</a>'
                '</div></div>'
            )
            continue
        links.append(
            f'<a class="{css_class}" href="{html.escape(_route_href(root, str(entry["route"])), quote=True)}"{current}>'
            f'{html.escape(str(entry["label"]))}</a>'
        )
    return "\n".join(links)


def _escaped(value: object) -> str:
    return html.escape(str(value), quote=True)


def _page_context(site: Mapping[str, object], page: PageSpec) -> Dict[str, str]:
    root = relative_root(page.route)
    site_url = str(site["siteUrl"])
    canonical_url = f"{site_url}/{page.route}"
    og_image = page.og_image.lstrip("/")
    context = {name: _escaped(value) for name, value in page.context.items()}
    context.update(
        {
            "title": _escaped(page.title),
            "description": _escaped(page.description),
            "body_class": _escaped(page.body_class),
            "root": root,
            "canonical_url": _escaped(canonical_url),
            "og_image_url": _escaped(f"{site_url}/{og_image}"),
            "site_name": _escaped(site["siteName"]),
            "logo": _escaped(site["logo"]),
            "favicon": _escaped(site["favicon"]),
            "address": _escaped(site["address"]),
            "hours": _escaped(site["hours"]),
            "copyright": _escaped(site["copyright"]),
            "home_url": _escaped(root or "./"),
            "contact_url": _escaped(_route_href(root, "contact/")),
            "intensivefix_url": _escaped(_route_href(root, "intensivefix/")),
            "whatsapp_url": _escaped(
                "https://wa.me/{}?{}".format(
                    quote(str(site["whatsappNumber"]), safe=""),
                    urlencode({"text": page.whatsapp_message}),
                )
            ),
            "nav_html": _navigation_html(
                site["navigation"], root, page.nav_key, "site-nav__link"
            ),
            "footer_nav_html": _navigation_html(
                site["footerNavigation"], root, page.nav_key, "footer-nav__link"
            ),
        }
    )
    return context


def _output_file(stage: Path, route: str) -> Path:
    return stage.joinpath(*PurePosixPath(route).parts, "index.html")


def _relative_alias_target(alias: str, target: str) -> str:
    alias_directory = alias.rstrip("/") or "."
    target_directory = target.rstrip("/") or "."
    relative = posixpath.relpath(target_directory, alias_directory)
    if target:
        return relative.rstrip("/") + "/"
    return relative.rstrip("/") + "/"


def _render_alias(site_url: str, alias: str, page: PageSpec) -> str:
    canonical = f"{site_url}/{page.route}"
    target = _relative_alias_target(alias, page.route)
    label = html.escape(page.title.split("|")[0].strip())
    return f"""<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Redirecting to {label}</title>
  <link rel="canonical" href="{html.escape(canonical, quote=True)}">
  <meta http-equiv="refresh" content="0; url={html.escape(target, quote=True)}">
</head>
<body>
  <main><h1>This page has moved</h1><p><a href="{html.escape(target, quote=True)}">Continue to {label}</a></p></main>
</body>
</html>
"""


def _validate_assets(
    site_dir: Path, pages: Sequence[PageSpec], site: Mapping[str, object]
) -> FrozenSet[str]:
    assets = site_dir / "assets"
    if not assets.is_dir():
        raise BuildError("missing site/assets directory")
    images = assets / "images"
    if images.is_dir():
        for path in images.rglob("*"):
            if path.is_file() and path.suffix.lower() in RASTER_SUFFIXES:
                raise BuildError(
                    f"local raster images must use WebP: {path.relative_to(site_dir)}"
                )
    manifest = _read_json(images / "asset-manifest.json", "image asset manifest")
    if not isinstance(manifest, dict):
        raise BuildError("image asset manifest must contain an object")
    manifest_files = set()
    for group, entries in manifest.items():
        if not isinstance(entries, list):
            raise BuildError(f"image asset manifest group {group!r} must be a list")
        for index, entry in enumerate(entries):
            if not isinstance(entry, dict) or not isinstance(entry.get("file"), str):
                raise BuildError(
                    f"image asset manifest {group}[{index}] must have a file path"
                )
            image = str(entry["file"])
            path = _safe_site_path(site_dir, image, "manifest image")
            if path.suffix.lower() != ".webp":
                raise BuildError(f"manifest image must use WebP: {image!r}")
            if not path.is_file():
                raise BuildError(f"manifest image does not exist: {image!r}")
            manifest_files.add(PurePosixPath(image).as_posix())
    referenced_images = [str(site["logo"]), str(site["favicon"])] + [
        page.og_image for page in pages
    ]
    for image in referenced_images:
        path = _safe_site_path(site_dir, image, "configured image")
        if not path.is_file():
            raise BuildError(f"configured image does not exist: {image!r}")
        if path.suffix.lower() != ".webp":
            raise BuildError(f"configured raster image must use WebP: {image!r}")
        if PurePosixPath(image).as_posix() not in manifest_files:
            raise BuildError(f"configured image is missing from manifest: {image!r}")
    return frozenset(manifest_files)


def _validate_local_references(
    stage: Path, manifest_images: FrozenSet[str]
) -> None:
    stage = stage.resolve()
    for html_path in sorted(stage.rglob("*.html")):
        parser = _ReferenceParser()
        parser.feed(html_path.read_text(encoding="utf-8"))
        for reference in parser.image_references:
            parsed = urlsplit(reference)
            if (
                not reference
                or reference.startswith("//")
                or parsed.scheme
                or parsed.netloc
                or not parsed.path
                or Path(parsed.path).suffix.lower() != ".webp"
            ):
                raise BuildError(
                    f"{html_path.relative_to(stage)}: image sources must be local WebP files: {reference!r}"
                )
            target = (
                stage / parsed.path.lstrip("/")
                if parsed.path.startswith("/")
                else html_path.parent / parsed.path
            ).resolve()
            if not _is_within(target, stage):
                raise BuildError(
                    f"{html_path.relative_to(stage)}: image source escapes output: {reference!r}"
                )
            manifest_path = target.relative_to(stage).as_posix()
            if manifest_path not in manifest_images:
                raise BuildError(
                    f"{html_path.relative_to(stage)}: image is missing from manifest: {reference!r}"
                )
        for attribute, reference in parser.references:
            if not reference or reference.startswith(("#", "//")):
                continue
            parsed = urlsplit(reference)
            if parsed.scheme or parsed.netloc or not parsed.path:
                continue
            decoded_path = quote(parsed.path, safe="/%._-~")
            # quote() above normalises unsafe characters; links should already be URL-safe.
            if decoded_path != parsed.path:
                raise BuildError(
                    f"{html_path.relative_to(stage)}: unsafe local {attribute} {reference!r}"
                )
            if parsed.path.startswith("/"):
                target = stage / parsed.path.lstrip("/")
            else:
                target = html_path.parent / parsed.path
            target = target.resolve()
            if not _is_within(target, stage):
                raise BuildError(
                    f"{html_path.relative_to(stage)}: local reference escapes output: {reference!r}"
                )
            if parsed.path.endswith("/") or target.is_dir():
                target = target / "index.html"
            if not target.is_file():
                raise BuildError(
                    f"{html_path.relative_to(stage)}: broken local reference {reference!r}"
                )


def _remove_path(path: Path) -> None:
    if path.is_dir() and not path.is_symlink():
        shutil.rmtree(str(path))
    elif path.exists() or path.is_symlink():
        path.unlink()


def _replace_output(stage: Path, output_dir: Path) -> None:
    backup_parent = Path(tempfile.mkdtemp(prefix=f".{output_dir.name}.backup-", dir=str(output_dir.parent)))
    backup = backup_parent / "previous"
    try:
        if output_dir.exists() or output_dir.is_symlink():
            output_dir.replace(backup)
        try:
            stage.replace(output_dir)
        except Exception:
            if backup.exists() or backup.is_symlink():
                backup.replace(output_dir)
            raise
        _remove_path(backup)
    finally:
        _remove_path(backup_parent)


def _safe_output(project_root: Path, output_dir: Path) -> Path:
    project_root = project_root.resolve()
    if not output_dir.is_absolute():
        output_dir = project_root / output_dir
    output = output_dir.resolve()
    if output == project_root or not _is_within(output, project_root):
        raise BuildError("output directory must be a child of the project root")
    protected_names = {
        ".git",
        ".github",
        "docs",
        "mirror",
        "scripts",
        "site",
        "tests",
    }
    for name in protected_names:
        protected = project_root / name
        if output == protected or _is_within(output, protected):
            raise BuildError(f"output directory cannot overwrite {name}/")
    if output.exists() or output.is_symlink():
        if not output.is_dir() or output.is_symlink():
            raise BuildError("output directory must not be an existing file or symlink")
        known_generated = output.name in {"build", ".preview-site"}
        has_manifest = (output / "build-manifest.json").is_file()
        if not known_generated and not has_manifest:
            raise BuildError(
                "existing output directory is not recognised as generated output"
            )
    return output


def render_site(project_root: Path, output_dir: Path) -> BuildReport:
    """Render and atomically publish a complete static site build."""

    project_root = Path(project_root).resolve()
    site_dir = project_root / "site"
    output = _safe_output(project_root, Path(output_dir))
    site = _load_site(site_dir)
    pages = _load_pages(site_dir, site)
    manifest_images = _validate_assets(site_dir, pages, site)
    try:
        base_source = (site_dir / "templates/base.html").read_text(encoding="utf-8")
    except FileNotFoundError as error:
        raise BuildError("missing site/templates/base.html") from error
    expanded_base = expand_includes(base_source, site_dir, "templates/base.html")

    output.parent.mkdir(parents=True, exist_ok=True)
    stage = Path(tempfile.mkdtemp(prefix=f".{output.name}.stage-", dir=str(output.parent)))
    aliases: List[str] = []
    try:
        shutil.copytree(str(site_dir / "assets"), str(stage / "assets"))
        for page in pages:
            context = _page_context(site, page)
            source_path = _safe_site_path(site_dir, page.source, f"page {page.key} source")
            main_source = source_path.read_text(encoding="utf-8")
            main_expanded = expand_includes(main_source, site_dir, page.source)
            main_html = render_template(main_expanded, context, page.source)
            final_context = dict(context)
            final_context["main_html"] = main_html
            document = render_template(expanded_base, final_context, "templates/base.html")
            destination = _output_file(stage, page.route)
            destination.parent.mkdir(parents=True, exist_ok=True)
            destination.write_text(document, encoding="utf-8")
            for alias in page.aliases:
                alias_destination = _output_file(stage, alias)
                alias_destination.parent.mkdir(parents=True, exist_ok=True)
                alias_destination.write_text(
                    _render_alias(str(site["siteUrl"]), alias, page), encoding="utf-8"
                )
                aliases.append(alias)

        routes = [page.route for page in pages]
        sitemap_urls = "\n".join(
            f"  <url><loc>{html.escape(str(site['siteUrl']) + '/' + route)}</loc></url>"
            for route in routes
        )
        (stage / "sitemap.xml").write_text(
            '<?xml version="1.0" encoding="UTF-8"?>\n'
            '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">\n'
            f"{sitemap_urls}\n</urlset>\n",
            encoding="utf-8",
        )
        (stage / "robots.txt").write_text(
            f"User-agent: *\nAllow: /\nSitemap: {site['siteUrl']}/sitemap.xml\n",
            encoding="utf-8",
        )
        (stage / ".nojekyll").write_text("", encoding="utf-8")
        files = sorted(
            [str(path.relative_to(stage).as_posix()) for path in stage.rglob("*") if path.is_file()]
            + ["build-manifest.json"]
        )
        manifest = {"pages": routes, "aliases": aliases, "files": files}
        (stage / "build-manifest.json").write_text(
            json.dumps(manifest, indent=2, ensure_ascii=False) + "\n", encoding="utf-8"
        )
        _validate_local_references(stage, manifest_images)
        _replace_output(stage, output)
        return BuildReport(tuple(routes), tuple(aliases), tuple(files))
    except BuildError:
        _remove_path(stage)
        raise
    except (OSError, ValueError) as error:
        _remove_path(stage)
        raise BuildError(f"build failed: {error}") from error


def main(argv: Optional[Sequence[str]] = None) -> int:
    parser = argparse.ArgumentParser(description=__doc__)
    parser.add_argument("--output", default="build", help="output directory (default: build)")
    args = parser.parse_args(argv)
    project_root = Path(__file__).resolve().parents[1]
    try:
        report = render_site(project_root, Path(args.output))
    except BuildError as error:
        parser.exit(1, f"Build failed: {error}\n")
    print(
        f"Built {len(report.pages)} pages and {len(report.aliases)} aliases "
        f"to {args.output} ({len(report.files)} files)."
    )
    return 0


if __name__ == "__main__":
    raise SystemExit(main())
