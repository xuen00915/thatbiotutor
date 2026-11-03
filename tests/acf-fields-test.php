<?php

declare(strict_types=1);

if (!defined('ABSPATH')) {
    define('ABSPATH', dirname(__DIR__) . '/.wordpress-test/');
}

$GLOBALS['tbt_acf_actions'] = [];
$GLOBALS['tbt_acf_groups'] = [];

function add_action(string $hook, callable|string|array $callback, int $priority = 10, int $acceptedArgs = 1): void
{
    $GLOBALS['tbt_acf_actions'][] = [$hook, $callback, $priority, $acceptedArgs];
}

function acf_add_local_field_group(array $group): void
{
    $GLOBALS['tbt_acf_groups'][] = $group;
}

require_once dirname(__DIR__) . '/wordpress/wp-content/themes/thatbiotutor-hello-child/inc/reference-content.php';

final class AcfFieldsTest
{
    private int $assertions = 0;

    public function run(): void
    {
        $module = dirname(__DIR__) . '/wordpress/wp-content/themes/thatbiotutor-hello-child/inc/acf-fields.php';
        $this->assertTrue(is_file($module), 'ACF field module must exist.');
        require_once $module;

        foreach (['tbt_register_acf_fields', 'tbt_acf_field', 'tbt_acf_tab', 'tbt_acf_textarea', 'tbt_acf_url', 'tbt_acf_image'] as $function) {
            $this->assertTrue(function_exists($function), "{$function} must exist.");
        }

        $callbacks = array_column($GLOBALS['tbt_acf_actions'], 1);
        $this->assertTrue(in_array('tbt_register_acf_fields', $callbacks, true), 'Field registration must run on acf/init.');

        tbt_register_acf_fields();
        $groups = $GLOBALS['tbt_acf_groups'];
        $registry = tbt_page_registry();
        $this->assertSame(count($registry), count($groups), 'Every protected page template must have one field group.');

        $allowedTypes = ['message', 'tab', 'text', 'textarea', 'url', 'image', 'number', 'true_false', 'select'];
        $proTypes = ['repeater', 'gallery', 'clone', 'flexible_content'];
        $allKeys = [];
        $allNames = [];
        $templates = [];
        $fieldsByTemplate = [];

        foreach ($groups as $group) {
            $this->assertTrue(isset($group['key'], $group['title'], $group['fields'], $group['location']), 'Each ACF group must be complete.');
            $template = $group['location'][0][0]['value'] ?? '';
            $this->assertTrue($template !== '', 'Each ACF group must target a page template.');
            $templates[] = $template;
            $fieldsByTemplate[$template] = array_column($group['fields'], null, 'name');

            $types = array_column($group['fields'], 'type');
            $this->assertSame('message', $types[0] ?? null, "{$template} must begin with editing instructions.");
            $this->assertTrue(in_array('tab', $types, true), "{$template} must expose section tabs.");

            foreach ($group['fields'] as $field) {
                $this->assertTrue(in_array($field['type'], $allowedTypes, true), "{$field['key']} uses an unsupported ACF Free field type.");
                $this->assertTrue(!in_array($field['type'], $proTypes, true), "{$field['key']} must not use an ACF Pro-only type.");
                $allKeys[] = $field['key'];
                if (($field['name'] ?? '') !== '') {
                    $allNames[] = $field['name'];
                }
            }
            $allKeys[] = $group['key'];
        }

        $this->assertSame(count($allKeys), count(array_unique($allKeys)), 'All group and field keys must be globally unique.');
        $this->assertSame(count($allNames), count(array_unique($allNames)), 'All non-empty field names must be globally unique.');
        $this->assertSame(count($templates), count(array_unique($templates)), 'A protected template may have only one field group.');

        $expectedTemplates = array_column($registry, 'template');
        $this->assertSame($expectedTemplates, $templates, 'ACF groups must follow the protected registry order.');

        $home = $fieldsByTemplate['front-page.php'] ?? [];
        foreach (['home_shared_whatsapp_number', 'home_shared_whatsapp_message', 'home_shared_address', 'home_shared_map_url', 'home_shared_opening_hours', 'home_shared_footer_description', 'home_shared_copyright', 'home_shared_logo'] as $name) {
            $this->assertTrue(isset($home[$name]), "Home must own shared field {$name}.");
        }
        foreach (range(1, 5) as $index) {
            $this->assertTrue(isset($home[sprintf('home_hero_image_%02d', $index)]), "Home hero image slot {$index} must exist.");
        }
        foreach (range(1, 4) as $index) {
            $this->assertTrue(isset($home[sprintf('home_number_image_%02d', $index)]), "Home number-image slot {$index} must exist.");
        }
        foreach (range(1, 3) as $index) {
            $this->assertTrue(isset($home[sprintf('home_classroom_image_%02d', $index)]), "Home classroom image slot {$index} must exist.");
        }

        $notes = $fieldsByTemplate['page-notes.php'] ?? [];
        foreach (range(1, 3) as $index) {
            $this->assertTrue(isset($notes[sprintf('notes_slider_image_%02d', $index)]), "Notes slider image slot {$index} must exist.");
        }

        $intensive = $fieldsByTemplate['page-intensivefix.php'] ?? [];
        foreach (range(1, 10) as $index) {
            $this->assertTrue(isset($intensive[sprintf('intensivefix_program_image_%02d', $index)]), "IntensiveFix programme slot {$index} must exist.");
        }
        $this->assertTrue(isset($intensive['intensivefix_student_reviews_image']), 'IntensiveFix review-artwork slot must exist.');

        fwrite(STDOUT, "PASS: {$this->assertions} ACF field-schema assertions\n");
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
    (new AcfFieldsTest())->run();
} catch (Throwable $error) {
    fwrite(STDERR, 'FAIL: ' . $error->getMessage() . "\n");
    exit(1);
}

