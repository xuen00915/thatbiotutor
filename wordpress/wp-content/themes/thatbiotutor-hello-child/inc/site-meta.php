<?php
declare(strict_types=1);
if (!defined('ABSPATH')) { exit; }

/** Return portable, unique metadata for every protected page. */
function tbt_page_metadata(): array
{
    $copy = [
        'home' => ['ThatBioTutor | O-Level Pure & G3 Biology Tuition Singapore', 'Specialist O-Level Pure and G3 Biology tuition with clear teaching, memorable notes, practical experience, and proven student results.'],
        'classes' => ['Biology Classes | ThatBioTutor', 'Explore ThatBioTutor O-Level Pure and G3 Biology classes, our teaching approach, practical sessions, and level-specific programmes.'],
        'sec-3-pure-g3-biology-tuition' => ['Sec 3 Pure & G3 Biology Tuition | ThatBioTutor', 'Build a strong Sec 3 Biology foundation with concept-first lessons, memory techniques, answering skills, and guided practice.'],
        'sec-4-pure-g3-biology-tuition' => ['Sec 4 Pure & G3 Biology Tuition | ThatBioTutor', 'Prepare for O-Level Biology with focused Sec 4 revision, exam answering methods, practical confidence, and structured support.'],
        'testimonials' => ['Results & Testimonials | ThatBioTutor', 'View ThatBioTutor student results, written reviews, and video stories across graduating classes.'],
        'notes' => ['Free O-Level Biology Notes Singapore | ThatBioTutor', 'Access concise visual O-Level Pure and G3 Biology notes designed for clearer understanding, recall, and exam revision.'],
        'celldivision' => ['Cell Division Crash Course Archive | ThatBioTutor', 'View the archived 2023 ThatBioTutor Cell Division crash-course resource and student feedback.'],
        'about' => ['About Us | ThatBioTutor', 'Learn about ThatBioTutor’s mission, teaching standards, specialist Biology tutors, founder story, vision, and values.'],
        'contact' => ['Contact ThatBioTutor', 'Contact the ThatBioTutor team on WhatsApp for O-Level Pure and G3 Biology lesson enquiries.'],
        'trees' => ['Our Environmental Causes | ThatBioTutor', 'See ThatBioTutor’s verified tree-planting record and its student-led support for Singapore environmental causes.'],
        'tuitionrecs' => ['Tuition Recommendations | ThatBioTutor', 'Browse independent secondary-school and JC tuition recommendations compiled by ThatBioTutor, with trial-lesson guidance.'],
        'intensivefix' => ['IntensiveFix Biology Programme | ThatBioTutor', 'Review the four-day IntensiveFix programme for repairing Sec 3 Biology foundations before Sec 4.'],
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

function tbt_render_meta_tags(): void
{
    $meta = tbt_page_metadata()[tbt_current_metadata_key()] ?? null;
    if (!is_array($meta)) { return; }
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
    $meta = tbt_page_metadata()[tbt_current_metadata_key()] ?? null;
    if (is_array($meta)) { $parts['title'] = (string) $meta['title']; }
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
