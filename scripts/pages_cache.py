#!/usr/bin/env python3
"""Refresh public assets in an existing static preview without running WordPress."""
import argparse
from pathlib import Path
import shutil

THEME = 'wordpress/wp-content/themes/thatbiotutor-hello-child'
STATIC_TYPES = {'.css', '.js', '.json', '.png', '.jpg', '.jpeg', '.gif', '.webp', '.avif', '.svg', '.ico', '.woff', '.woff2', '.ttf', '.otf', '.mp4', '.webm', '.pdf'}


def refresh_assets(root, output):
    # Include images/fonts too: a CSS edit can reference a pre-existing asset that
    # wget never downloaded. Never publish PHP or other executable source files.
    source = root / THEME / 'assets'
    target = output / THEME.removeprefix('wordpress/') / 'assets'
    for path in source.rglob('*'):
        if path.is_file() and path.suffix.lower() in STATIC_TYPES:
            destination = target / path.relative_to(source)
            destination.parent.mkdir(parents=True, exist_ok=True)
            shutil.copy2(path, destination)


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
