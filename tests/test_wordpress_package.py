import importlib.util
import pathlib
import subprocess
import tempfile
import unittest
import zipfile

SCRIPT = pathlib.Path(__file__).parents[1] / 'scripts/build_wordpress_package.py'

class PackageTest(unittest.TestCase):
    def test_theme_zip_has_one_root_and_only_tracked_theme_files(self):
        self.assertTrue(SCRIPT.is_file(), 'Missing WordPress upload package builder')
        spec = importlib.util.spec_from_file_location('package', SCRIPT)
        module = importlib.util.module_from_spec(spec)
        spec.loader.exec_module(module)
        with tempfile.TemporaryDirectory() as tmp:
            root = pathlib.Path(tmp)
            theme = root / 'wordpress/wp-content/themes/thatbiotutor-hello-child'
            theme.mkdir(parents=True)
            for name in ('style.css', 'index.php', 'functions.php'):
                (theme / name).write_text('Template: hello-elementor\n' if name=='style.css' else '<?php')
            subprocess.run(['git','init','-q',str(root)],check=True)
            subprocess.run(['git','-C',str(root),'add','wordpress'],check=True)
            (theme / '.env').write_text('never package')
            (theme / 'backup.sql').write_text('never package')
            (root / 'wp-config.php').write_text('never package')
            output = root / 'release'
            archive = module.build_package(root, output)
            with zipfile.ZipFile(archive) as z:
                self.assertEqual(sorted(z.namelist()), ['thatbiotutor-hello-child/functions.php','thatbiotutor-hello-child/index.php','thatbiotutor-hello-child/style.css'])
                self.assertIsNone(z.testzip())
            original = archive.read_bytes()
            module.build_package(root, output)
            self.assertEqual(original, archive.read_bytes())

if __name__ == '__main__': unittest.main()
