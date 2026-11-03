<?php

declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Create an approved page or update the protected template on an owned page.
 */
function tbt_upsert_reference_page(string $slug, string $title, string $template): int|WP_Error
{
    $existing = get_page_by_path($slug, OBJECT, 'page');

    if ($existing !== null) {
        $postId = (int) $existing->ID;
        if ((string) get_post_meta($postId, '_tbt_reference_page', true) !== '1') {
            return new WP_Error(
                'tbt_slug_collision',
                sprintf('Cannot create the ThatBioTutor page “%s”: that slug belongs to an unowned page.', $slug)
            );
        }
    } else {
        $created = wp_insert_post([
            'post_title' => $title,
            'post_name' => $slug,
            'post_type' => 'page',
            'post_status' => 'publish',
        ], true);
        if (is_wp_error($created)) {
            return $created;
        }
        $postId = (int) $created;
        update_post_meta($postId, '_tbt_reference_page', '1');
    }

    update_post_meta($postId, '_wp_page_template', $template);

    return $postId;
}

/** Return a stable hash for one approved seed value. */
function tbt_seed_value_hash(mixed $value): string
{
    $encoded = function_exists('wp_json_encode') ? wp_json_encode($value) : json_encode($value);
    return hash('sha256', $encoded === false ? serialize($value) : $encoded);
}

/**
 * Update a seed-owned field while preserving values changed by a client.
 */
function tbt_update_seeded_field(
    int $postId,
    string $fieldKey,
    string $fieldName,
    mixed $value,
    bool $refresh = false
): bool {
    $hashMeta = '_tbt_seed_hash_' . $fieldName;
    $previousHash = (string) get_post_meta($postId, $hashMeta, true);
    $currentValue = get_post_meta($postId, $fieldName, true);
    $hasCurrentValue = $currentValue !== '' && $currentValue !== null;

    if (!$refresh && $hasCurrentValue) {
        if ($previousHash === '' || !hash_equals($previousHash, tbt_seed_value_hash($currentValue))) {
            return false;
        }
    }

    update_post_meta($postId, $fieldName, $value);
    update_post_meta($postId, '_' . $fieldName, $fieldKey);
    update_post_meta($postId, $hashMeta, tbt_seed_value_hash($value));

    $reference = tbt_reference_content();
    update_post_meta($postId, '_tbt_seed_version', (string) ($reference['version'] ?? 'unknown'));

    return true;
}

/**
 * Create the protected pages and seed only owned or missing values.
 *
 * @return array{pages:array<string,int>,seeded:int,preserved:int}|WP_Error
 */
function tbt_setup_site(bool $refreshApproved = false): array|WP_Error
{
    $registry = tbt_page_registry();
    $reference = tbt_reference_content();
    $pageIds = [];
    $seeded = 0;
    $preserved = 0;

    foreach ($registry as $pageKey => $page) {
        $pageId = tbt_upsert_reference_page($page['slug'], $page['title'], $page['template']);
        if (is_wp_error($pageId)) {
            return $pageId;
        }
        $pageIds[$pageKey] = $pageId;

        foreach (($reference['pages'][$pageKey]['fields'] ?? []) as $fieldName => $value) {
            $updated = tbt_update_seeded_field(
                $pageId,
                tbt_reference_field_key($pageKey, (string) $fieldName),
                (string) $fieldName,
                $value,
                $refreshApproved
            );
            $updated ? $seeded++ : $preserved++;
        }
    }

    update_option('show_on_front', 'page');
    update_option('page_on_front', $pageIds['home']);

    return [
        'pages' => $pageIds,
        'seeded' => $seeded,
        'preserved' => $preserved,
    ];
}

/** WP-CLI adapter for the idempotent setup operation. */
final class TBT_Setup_Site_Command
{
    /**
     * @param list<string> $args
     * @param array<string,mixed> $assocArgs
     */
    public function __invoke(array $args, array $assocArgs): void
    {
        $refresh = array_key_exists('refresh-approved', $assocArgs);
        $result = tbt_setup_site($refresh);

        if (is_wp_error($result)) {
            WP_CLI::error($result->message);
            return;
        }

        WP_CLI::success(sprintf(
            'ThatBioTutor setup complete: %d pages, %d seeded values, %d preserved client values.',
            count($result['pages']),
            $result['seeded'],
            $result['preserved']
        ));
    }
}

if (defined('WP_CLI') && WP_CLI && class_exists('WP_CLI')) {
    WP_CLI::add_command('tbt setup-site', TBT_Setup_Site_Command::class);
}

