#!/usr/bin/env python3
"""Reuse a validated export only when every non-CSS build input is unchanged."""
import argparse
import hashlib
from pathlib import Path
import shutil
import subprocess

THEME = 'wordpress/wp-content/themes/thatbiotutor-hello-child'
STATIC_TYPES = {'.css', '.js', '.json', '.png', '.jpg', '.jpeg', '.gif', '.webp', '.avif', '.svg', '.ico', '.woff', '.woff2', '.ttf', '.otf', '.mp4', '.webm', '.pdf'}


def fingerprint(root, files):
    digest = hashlib.sha256()
    for name in sorted(files):
        if name.endswith('.md'):
            continue
        # File names remain in the key: adding/removing a stylesheet requires a rebuild.
        digest.update(name.encode() + b'\0')
        if not (name.startswith(THEME + '/assets/css/') and name.endswith('.css')):
            digest.update(hashlib.sha256((root / name).read_bytes()).digest())
    return digest.hexdigest()


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
    parser.add_argument('action', choices=['key', 'refresh'])
    parser.add_argument('--output', type=Path, default=Path('build'))
    args = parser.parse_args()
    root = Path.cwd()
    if args.action == 'key':
        files = subprocess.check_output(['git', 'ls-files', '-z']).decode().strip('\0').split('\0')
        print(fingerprint(root, files))
    else:
        if not (args.output / 'index.html').is_file():
            raise SystemExit('Cached export is missing index.html; refusing to publish')
        refresh_assets(root, args.output)


if __name__ == '__main__':
    main()
