<?php
declare(strict_types=1);
if (!defined('ABSPATH')) { exit; }

/** Return portable, unique metadata for every protected page. */
function tbt_page_metadata(): array
{
    $copy = [
        'home' => ['Pure & G3 Biology Tuition Singapore | ThatBioTutor', 'Specialist O-Level Pure and G3 Biology tuition in Singapore. Explore Sec 3 and Sec 4 classes, visual notes, practical sessions and student results.'],
        'classes' => ['O-Level Biology Classes in Singapore | ThatBioTutor', 'Explore Pure and G3 Biology classes for Sec 3 and Sec 4. See how ThatBioTutor teaches concepts, memory skills, exam answering and practical techniques.'],
        'sec-3-pure-g3-biology-tuition' => ['Sec 3 Pure & G3 Biology Tuition | ThatBioTutor', 'Build your Sec 3 Biology foundation with ThatBioTutor in Singapore. Explore concept lessons, visual notes, guided practice and practical sessions.'],
        'sec-4-pure-g3-biology-tuition' => ['Sec 4 O-Level Biology Tuition Singapore | ThatBioTutor', 'Prepare for O-Level Pure and G3 Biology with ThatBioTutor\'s Sec 4 programme. Explore syllabus revision, exam answering skills and practical training.'],
        'testimonials' => ['Biology Tuition Results & Reviews | ThatBioTutor', 'Read ThatBioTutor student reviews and watch their Biology learning stories. Browse O-Level results and testimonials from the Classes of 2021 to 2025.'],
        'notes' => ['Free O-Level Pure & G3 Biology Notes | ThatBioTutor', 'Request free O-Level Pure and G3 Biology notes from ThatBioTutor. Explore concise topic summaries and visual explanations to support your revision.'],
        'celldivision' => ['Cell Division Crash Course Archive | ThatBioTutor', 'Explore ThatBioTutor\'s archived 2023 Cell Division crash course, covering mitosis and meiosis. View course resources, lesson details and student feedback.'],
        'about' => ['Meet Our Specialist Biology Tutors | ThatBioTutor', 'Meet ThatBioTutor\'s specialist Biology tutors and founder. Learn about our teaching standards, mission and approach to supporting students in Singapore.'],
        'contact' => ['Contact & Biology Tuition Enquiries | ThatBioTutor', 'Contact ThatBioTutor about Pure and G3 Biology tuition in Singapore. Find our Bencoolen address, opening hours and WhatsApp details to enquire about classes.'],
        'trees' => ['Tree Planting & Environmental Causes | ThatBioTutor', 'Explore ThatBioTutor\'s tree-planting efforts and support for Singapore environmental causes. See how our community contributes beyond the Biology classroom.'],
        'tuitionrecs' => ['Secondary & JC Tuition Recommendations | ThatBioTutor', 'Browse ThatBioTutor\'s tuition recommendations for secondary school and JC subjects. Explore the recommended centres and guidance on arranging trial lessons.'],
        'intensivefix' => ['IntensiveFix: Sec 3 Biology Revision | ThatBioTutor', 'Explore ThatBioTutor\'s four-day IntensiveFix programme for Sec 3 Biology. Revisit key concepts and build a stronger foundation before starting Sec 4.'],
    ];
    $metadata = [];
    foreach (tbt_page_registry() as $key => $page) {
        [$title, $description] = $copy[$key] ?? [$page['title'] . ' | ThatBioTutor', 'ThatBioTutor O-Level Pure and G3 Biology learning resource.'];
        $canonical = home_url((string) $page['canonical']);
        $metadata[$key] = ['title' => $title, 'description' => $description, 'canonical' => $canonical, 'og_title' => $title, 'og_description' => $description, 'og_url' => $canonical];
    }
    return $metadata;
}

function tbt_current_metadata_key(): string
{
    if (function_exists('is_front_page') && is_front_page()) { return 'home'; }
    foreach (tbt_page_registry() as $key => $page) {
        if ($key !== 'home' && function_exists('is_page') && is_page((string) $page['slug'])) { return $key; }
    }
    return '';
}

/** Resolve optional page-editor overrides without changing portable canonical URLs. */
function tbt_current_metadata(): ?array
{
    $key = tbt_current_metadata_key();
    $meta = tbt_page_metadata()[$key] ?? null;
    if (!is_array($meta)) { return null; }
    if (function_exists('get_queried_object_id') && function_exists('get_post_meta')) {
        $prefix = 'tbt_' . str_replace('-', '_', $key) . '_seo_';
        foreach (['title', 'description'] as $field) {
            $value = get_post_meta(get_queried_object_id(), $prefix . $field, true);
            if (!is_string($value)) { continue; }
            $value = trim(preg_replace('/\s+/u', ' ', wp_strip_all_tags($value)) ?? '');
            if ($value !== '') { $meta[$field] = $value; $meta['og_' . $field] = $value; }
        }
    }
    return $meta;
}

function tbt_render_meta_tags(): void
{
    $meta = tbt_current_metadata();
    if (!is_array($meta)) { return; }
    // Core otherwise adds a second canonical for singular pages at priority 10.
    if (function_exists('remove_action')) { remove_action('wp_head', 'rel_canonical'); }
    echo '<meta name="description" content="' . esc_attr((string) $meta['description']) . '">' . "\n";
    echo '<link rel="canonical" href="' . esc_url((string) $meta['canonical']) . '">' . "\n";
    echo '<meta property="og:type" content="website">' . "\n";
    echo '<meta property="og:title" content="' . esc_attr((string) $meta['og_title']) . '">' . "\n";
    echo '<meta property="og:description" content="' . esc_attr((string) $meta['og_description']) . '">' . "\n";
    echo '<meta property="og:url" content="' . esc_url((string) $meta['og_url']) . '">' . "\n";
}

/** Apply the same portable SEO title to WordPress's document title. */
function tbt_filter_document_title_parts(array $parts): array
{
    $meta = tbt_current_metadata();
    if (is_array($meta)) {
        $parts['title'] = (string) $meta['title'];
        unset($parts['site'], $parts['tagline']);
    }
    return $parts;
}

function tbt_retired_route_redirects(): array { return tbt_reference_content()['aliases'] ?? []; }

function tbt_should_bypass_frontend_redirects(): bool
{
    return (function_exists('is_admin') && is_admin())
        || (function_exists('wp_doing_ajax') && wp_doing_ajax())
        || (defined('REST_REQUEST') && REST_REQUEST)
        || (defined('WP_CLI') && WP_CLI);
}

function tbt_legacy_redirect_target(string $requestUri): string
{
    if ($requestUri === '' || str_starts_with($requestUri, '//')) { return ''; }
    $parts = parse_url($requestUri);
    if ($parts === false || isset($parts['host']) || isset($parts['scheme'])) { return ''; }
    $path = '/' . ltrim((string) ($parts['path'] ?? ''), '/');
    $path = rtrim($path, '/') . '/';
    $target = tbt_retired_route_redirects()[$path] ?? '';
    return is_string($target) && str_starts_with($target, '/') ? home_url($target) : '';
}

function tbt_redirect_legacy_routes(): void
{
    if (tbt_should_bypass_frontend_redirects()) { return; }
    $target = tbt_legacy_redirect_target((string) ($_SERVER['REQUEST_URI'] ?? ''));
    if ($target === '' || !function_exists('wp_safe_redirect')) { return; }
    wp_safe_redirect($target, 301, 'ThatBioTutor');
    exit;
}

if (function_exists('add_action')) {
    add_action('wp_head', 'tbt_render_meta_tags', 2);
    add_action('template_redirect', 'tbt_redirect_legacy_routes', 1);
}
if (function_exists('add_filter')) { add_filter('document_title_parts', 'tbt_filter_document_title_parts'); }
