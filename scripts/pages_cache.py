#!/usr/bin/env python3
"""Refresh public assets in an existing static preview without running WordPress."""
import argparse
import hashlib
import re
from pathlib import Path
import shutil

THEME = 'wordpress/wp-content/themes/thatbiotutor-hello-child'
STATIC_TYPES = {'.css', '.js', '.json', '.png', '.jpg', '.jpeg', '.gif', '.webp', '.avif', '.svg', '.ico', '.woff', '.woff2', '.ttf', '.otf', '.mp4', '.webm', '.pdf'}


def refresh_assets(root, output):
    # Include images/fonts too: a CSS edit can reference a pre-existing asset that
    # wget never downloaded. Never publish PHP or other executable source files.
    source = root / THEME / 'assets'
    target = output / THEME.removeprefix('wordpress/') / 'assets'
    versions = {}
    for path in source.rglob('*'):
        if path.is_file() and path.suffix.lower() in STATIC_TYPES:
            destination = target / path.relative_to(source)
            destination.parent.mkdir(parents=True, exist_ok=True)
            shutil.copy2(path, destination)
            if path.suffix.lower() in {'.css', '.js'}:
                versions[destination.relative_to(output).as_posix()] = hashlib.sha256(path.read_bytes()).hexdigest()[:12]
    # Refresh URLs in the cached export; no WordPress rendering is needed.
    def version_url(match):
        prefix, url, quote = match.groups()
        clean = url.split('?', 1)[0].split('#', 1)[0]
        marker = 'wp-content/themes/thatbiotutor-hello-child/assets/'
        index = clean.find(marker)
        version = versions.get(clean[index:]) if index >= 0 else None
        if not version:
            return match.group(0)
        fragment = '#' + url.split('#', 1)[1] if '#' in url else ''
        return f'{prefix}{clean}?v={version}{fragment}{quote}'
    for page in output.rglob('*.html'):
        html = page.read_text()
        updated = re.sub(r"((?:href|src)=[\"'])([^\"']+)([\"'])", version_url, html)
        if updated != html:
            page.write_text(updated)


def main():
    parser = argparse.ArgumentParser(description=__doc__)
    parser.add_argument('action', choices=['refresh'])
    parser.add_argument('--output', type=Path, default=Path('build'))
    args = parser.parse_args()
    root = Path.cwd()
    if not (args.output / 'index.html').is_file():
        raise SystemExit('Existing preview is missing index.html; WordPress must be rebuilt explicitly')
    refresh_assets(root, args.output)


if __name__ == '__main__':
    main()
