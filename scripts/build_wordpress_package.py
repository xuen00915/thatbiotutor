#!/usr/bin/env python3
"""Build an uploadable child-theme ZIP from tracked theme files, never a database dump."""
import argparse
import hashlib
import pathlib
import subprocess
import zipfile

THEME = pathlib.Path('wordpress/wp-content/themes/thatbiotutor-hello-child')

def build_package(root: pathlib.Path, output: pathlib.Path) -> pathlib.Path:
    tracked = subprocess.check_output(['git','-C',str(root),'ls-files','-z','--',str(THEME)]).decode().split('\0')
    files = sorted(pathlib.Path(name) for name in tracked if name)
    for required in ('style.css', 'index.php', 'functions.php'):
        if THEME / required not in files:
            raise ValueError(f'Missing tracked theme entry: {required}')
    if 'Template: hello-elementor' not in (root / THEME / 'style.css').read_text():
        raise ValueError('Child theme must declare the Hello Elementor parent')
    output.mkdir(parents=True, exist_ok=True)
    target = output / 'thatbiotutor-hello-child.zip'
    with zipfile.ZipFile(target, 'w', compression=zipfile.ZIP_DEFLATED, compresslevel=9) as archive:
        for relative in files:
            source = root / relative
            if source.is_symlink() or not source.is_file():
                raise ValueError(f'Invalid theme file: {relative}')
            if any(part.startswith('.') for part in relative.parts) or source.suffix.lower() in ('.sql','.zip','.log','.sqlite'):
                raise ValueError(f'Forbidden package file: {relative}')
            name = pathlib.Path(THEME.name) / relative.relative_to(THEME)
            entry = zipfile.ZipInfo(name.as_posix(), date_time=(2020,1,1,0,0,0))
            entry.compress_type = zipfile.ZIP_DEFLATED
            entry.external_attr = 0o100644 << 16
            archive.writestr(entry, source.read_bytes())
    digest = hashlib.sha256(target.read_bytes()).hexdigest()
    (output / 'SHA256SUMS.txt').write_text(f'{digest}  {target.name}\n')
    guide = root / 'docs/WORDPRESS-INSTALL.md'
    if guide.is_file():
        (output / 'INSTALL-WORDPRESS.md').write_text(guide.read_text())
    for name in ('CONTACT-FORM-7.md', 'CLIENT-EDITING-GUIDE.md'):
        source = root / 'docs' / name
        if source.is_file():
            (output / 'docs').mkdir(exist_ok=True)
            (output / 'docs' / name).write_text(source.read_text())
    return target

if __name__ == '__main__':
    parser = argparse.ArgumentParser(description=__doc__)
    parser.add_argument('--output', type=pathlib.Path, default=pathlib.Path('.artifacts/wordpress'))
    args = parser.parse_args()
    archive = build_package(pathlib.Path(__file__).resolve().parents[1], args.output.resolve())
    print(f'Created {archive} ({archive.stat().st_size / 1024 / 1024:.1f} MiB)')
