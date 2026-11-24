import importlib.util
from pathlib import Path
import tempfile
import unittest

spec = importlib.util.spec_from_file_location('pages_cache', Path(__file__).resolve().parents[1] / 'scripts/pages_cache.py')
cache = importlib.util.module_from_spec(spec)
spec.loader.exec_module(cache)


class PagesCacheTests(unittest.TestCase):
    def test_only_css_content_and_documentation_can_reuse_export(self):
        with tempfile.TemporaryDirectory() as temp:
            root = Path(temp)
            files = ['theme.php', 'CHANGELOG.md', f'{cache.THEME}/assets/css/main.css', f'{cache.THEME}/assets/images/photo.png']
            for name in files:
                p = root / name
                p.parent.mkdir(parents=True, exist_ok=True)
                p.write_text('original')
            before = cache.fingerprint(root, files)
            (root / files[2]).write_text('changed CSS')
            (root / files[1]).write_text('new changelog')
            self.assertEqual(before, cache.fingerprint(root, files))
            for name in [files[0], files[3]]:
                (root / name).write_text('changed')
                self.assertNotEqual(before, cache.fingerprint(root, files))
                (root / name).write_text('original')
            self.assertNotEqual(before, cache.fingerprint(root, files[:-1]))
            extra = f'{cache.THEME}/assets/css/new.css'
            (root / extra).write_text('new CSS')
            self.assertNotEqual(before, cache.fingerprint(root, files + [extra]))

    def test_overlay_copies_css_and_newly_referenced_static_assets_without_php(self):
        with tempfile.TemporaryDirectory() as temp:
            root = Path(temp)
            source = root / cache.THEME / 'assets'
            source.mkdir(parents=True)
            (source / 'main.css').write_text('body { background: url(photo.png); }')
            (source / 'photo.png').write_bytes(b'image')
            (source / 'private.php').write_text('<?php secret();')
            output = root / 'build'
            output.mkdir()
            (output / 'index.html').write_text('cached HTML')
            cache.refresh_assets(root, output)
            target = output / cache.THEME.removeprefix('wordpress/') / 'assets'
            self.assertEqual((target / 'main.css').read_text(), (source / 'main.css').read_text())
            self.assertTrue((target / 'photo.png').is_file())
            self.assertFalse((target / 'private.php').exists())
            self.assertEqual((output / 'index.html').read_text(), 'cached HTML')
