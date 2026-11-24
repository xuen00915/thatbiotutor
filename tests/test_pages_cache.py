import importlib.util
from pathlib import Path
import tempfile
import unittest

spec = importlib.util.spec_from_file_location('pages_cache', Path(__file__).resolve().parents[1] / 'scripts/pages_cache.py')
cache = importlib.util.module_from_spec(spec)
spec.loader.exec_module(cache)


class PagesCacheTests(unittest.TestCase):
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

    def test_css_and_js_urls_change_with_content_and_preserve_relative_paths(self):
        with tempfile.TemporaryDirectory() as temp:
            root = Path(temp)
            source = root / cache.THEME / 'assets'
            source.mkdir(parents=True)
            css = source / 'main.css'
            css.write_text('body {color: green}')
            output = root / 'build'
            output.mkdir()
            page = output / 'index.html'
            asset = 'wp-content/themes/thatbiotutor-hello-child/assets/main.css'
            page.write_text(f'<link href="../{asset}?ver=old"><p>Unchanged content</p>')
            cache.refresh_assets(root, output)
            first = page.read_text()
            self.assertIn(f'../{asset}?v=', first)
            self.assertNotIn('ver=old', first)
            cache.refresh_assets(root, output)
            self.assertEqual(page.read_text(), first)
            css.write_text('body {color: blue}')
            cache.refresh_assets(root, output)
            self.assertNotEqual(page.read_text(), first)
            self.assertIn('<p>Unchanged content</p>', page.read_text())
