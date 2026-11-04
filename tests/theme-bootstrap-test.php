<?php

declare(strict_types=1);

final class ThemeBootstrapTest
{
    private int $assertions = 0;

    public function run(): void
    {
        $root = dirname(__DIR__);
        $theme = $root . '/wordpress/wp-content/themes/thatbiotutor-hello-child';
        $style = $theme . '/style.css';
        $functions = $theme . '/functions.php';

        $this->assertFileExists($style, 'The child-theme stylesheet must exist.');
        $this->assertFileExists($functions, 'The child-theme bootstrap must exist.');

        $metadata = file_get_contents($style);
        $this->assertTrue(is_string($metadata), 'The child-theme stylesheet must be readable.');
        $this->assertMatches('/^Theme Name:\s*ThatBioTutor Hello Child\s*$/mi', $metadata, 'Theme name must be declared.');
        $this->assertMatches('/^Template:\s*hello-elementor\s*$/mi', $metadata, 'Hello Elementor must be the parent theme.');

        $withoutWordPress = $this->runPhpFile($functions, false);
        $this->assertSame(0, $withoutWordPress['exitCode'], 'Loading outside WordPress must exit cleanly.');
        $this->assertSame('', trim($withoutWordPress['output']), 'Loading outside WordPress must not expose output.');

        $GLOBALS['tbt_test_actions'] = [];
        $GLOBALS['tbt_test_supports'] = [];
        $GLOBALS['tbt_test_menus'] = [];

        if (!defined('ABSPATH')) {
            define('ABSPATH', $root . '/.wordpress-test/');
        }

        function add_action(string $hook, callable|string|array $callback, int $priority = 10, int $acceptedArgs = 1): void
        {
            $GLOBALS['tbt_test_actions'][] = [$hook, $callback, $priority, $acceptedArgs];
        }

        function add_theme_support(string $feature, mixed ...$arguments): void
        {
            $GLOBALS['tbt_test_supports'][$feature] = $arguments;
        }

        function register_nav_menus(array $locations): void
        {
            $GLOBALS['tbt_test_menus'] = $locations;
        }

        function __(string $text, string $domain = 'default'): string
        {
            return $text;
        }

        function get_stylesheet_directory(): string
        {
            return dirname(__DIR__) . '/wordpress/wp-content/themes/thatbiotutor-hello-child';
        }

        function get_stylesheet_directory_uri(): string
        {
            return 'https://example.test/wp-content/themes/thatbiotutor-hello-child';
        }

        ob_start();
        require $functions;
        $output = (string) ob_get_clean();

        $this->assertSame('', $output, 'Bootstrap must not render public markup while loading.');
        $this->assertTrue(defined('TBT_THEME_VERSION'), 'TBT_THEME_VERSION must be defined.');
        $this->assertTrue(defined('TBT_THEME_DIR'), 'TBT_THEME_DIR must be defined.');
        $this->assertTrue(defined('TBT_THEME_URI'), 'TBT_THEME_URI must be defined.');
        $this->assertTrue(function_exists('tbt_theme_setup'), 'The prefixed setup function must exist.');
        $this->assertTrue(function_exists('tbt_theme_includes'), 'The focused include registry must exist.');

        $includes = tbt_theme_includes();
        $this->assertTrue(count($includes) >= 8, 'The bootstrap must expose focused include modules.');
        foreach ($includes as $include) {
            $this->assertMatches('/^inc\/[-a-z0-9]+\.php$/', $include, 'Every include must be a focused inc/*.php module.');
        }

        $callbacks = array_column($GLOBALS['tbt_test_actions'], 1);
        $this->assertTrue(in_array('tbt_theme_setup', $callbacks, true), 'Theme setup must be registered.');

        tbt_theme_setup();
        foreach (['title-tag', 'post-thumbnails', 'custom-logo', 'html5'] as $support) {
            $this->assertTrue(array_key_exists($support, $GLOBALS['tbt_test_supports']), "Theme support {$support} must be registered.");
        }

        $html5 = $GLOBALS['tbt_test_supports']['html5'][0] ?? [];
        foreach (['search-form', 'comment-form', 'comment-list', 'gallery', 'caption', 'style', 'script'] as $type) {
            $this->assertTrue(in_array($type, $html5, true), "HTML5 support must include {$type}.");
        }

        fwrite(STDOUT, "PASS: {$this->assertions} theme bootstrap assertions\n");
    }

    private function runPhpFile(string $file, bool $withWordPress): array
    {
        $prefix = $withWordPress ? "define('ABSPATH', '/tmp/wordpress/');" : '';
        $command = escapeshellarg(PHP_BINARY) . ' -r ' . escapeshellarg($prefix . ' require ' . var_export($file, true) . ';');
        $output = [];
        $exitCode = 0;
        exec($command . ' 2>&1', $output, $exitCode);

        return ['output' => implode("\n", $output), 'exitCode' => $exitCode];
    }

    private function assertFileExists(string $path, string $message): void
    {
        $this->assertTrue(is_file($path), $message . " Missing: {$path}");
    }

    private function assertMatches(string $pattern, string $value, string $message): void
    {
        $this->assertTrue(preg_match($pattern, $value) === 1, $message);
    }

    private function assertSame(mixed $expected, mixed $actual, string $message): void
    {
        $this->assertions++;
        if ($expected !== $actual) {
            throw new RuntimeException($message . ' Expected ' . var_export($expected, true) . ', got ' . var_export($actual, true));
        }
    }

    private function assertTrue(bool $condition, string $message): void
    {
        $this->assertions++;
        if (!$condition) {
            throw new RuntimeException($message);
        }
    }
}

try {
    (new ThemeBootstrapTest())->run();
} catch (Throwable $error) {
    fwrite(STDERR, 'FAIL: ' . $error->getMessage() . "\n");
    exit(1);
}
