<?php
declare(strict_types=1);
if (!defined('ABSPATH')) { exit; }

/** Return portable, unique metadata for every protected page. */
function tbt_page_metadata(): array
{
    // [SEO title (max 60), description (110-155), keywords]. Keep these defaults in sync with the site’s search and social metadata.
    $copy = [
        'home' => ['O-Level Pure & G3 Biology Tuition Singapore | ThatBioTutor', 'Singapore\'s only O-Level Pure and G3 Biology specialist. Sec 3 and Sec 4 Biology tuition with visual notes, exam answering skills and proven results.', 'O Level Biology tuition Singapore, Pure Biology tuition, G3 Biology tuition, Sec 3 Biology tuition, Sec 4 Biology tuition, Biology tutor Singapore, ThatBioTutor'],
        'classes' => ['Sec 3 & Sec 4 Pure and G3 Biology Classes | ThatBioTutor', 'Compare ThatBioTutor\'s Sec 3 and Sec 4 Pure and G3 Biology classes in Singapore: concept lessons, memory techniques, exam answering and practicals.', 'Biology classes Singapore, O Level Biology classes, Pure Biology classes, G3 Biology classes, Sec 3 Biology class, Sec 4 Biology class, Biology tuition centre'],
        'sec-3-pure-g3-biology-tuition' => ['Sec 3 Pure & G3 Biology Tuition Singapore | ThatBioTutor', 'Build a strong Sec 3 Pure or G3 Biology foundation in Singapore with ThatBioTutor: clear concept lessons, visual notes, guided practice and practicals.', 'Sec 3 Biology tuition, Sec 3 Pure Biology, Sec 3 G3 Biology, Secondary 3 Biology tutor, Biology tuition Singapore, Sec 3 Biology notes'],
        'sec-4-pure-g3-biology-tuition' => ['Sec 4 O-Level Pure & G3 Biology Tuition | ThatBioTutor', 'Prepare for the O-Level Pure or G3 Biology exam with ThatBioTutor\'s Sec 4 programme: full syllabus revision, exam answering techniques and practicals.', 'Sec 4 Biology tuition, O Level Biology tuition, O Level Pure Biology, G3 Biology exam, Biology exam preparation, Sec 4 Biology tutor Singapore'],
        'testimonials' => ['O-Level Biology Results & Student Reviews | ThatBioTutor', 'See ThatBioTutor\'s O-Level Pure and G3 Biology results and read student reviews, stories and advice from the Classes of 2021 to 2025 in Singapore.', 'O Level Biology results, Biology tuition reviews, ThatBioTutor reviews, Biology tutor testimonials, A1 Biology Singapore'],
        'notes' => ['Free O-Level Pure & G3 Biology Notes | ThatBioTutor', 'Get free O-Level Pure and G3 Biology notes from ThatBioTutor: visual, concise notes accurate to the 2027 syllabus, with 60+ memory shortcuts.', 'free O Level Biology notes, Pure Biology notes, G3 Biology notes, Biology notes Singapore, O Level Biology revision notes, Biology memory shortcuts'],
        'celldivision' => ['Cell Division Crash Course Archive | ThatBioTutor', 'Explore ThatBioTutor\'s archived 2023 Cell Division crash course on mitosis and meiosis, with course resources, lesson details and student feedback.', 'cell division, mitosis and meiosis, O Level Biology cell division, Biology crash course, ThatBioTutor crash course'],
        'about' => ['Meet Our O-Level Biology Tutors & Founder | ThatBioTutor', 'Meet ThatBioTutor\'s specialist O-Level Biology tutors and founder. Learn about our teaching standards, mission and how we support students in Singapore.', 'Biology tutors Singapore, O Level Biology tutor, specialist Biology tutor, ThatBioTutor founder, about ThatBioTutor'],
        'contact' => ['Contact & Biology Tuition Enquiries | ThatBioTutor', 'Contact ThatBioTutor about O-Level Pure and G3 Biology tuition. Find our Bencoolen address, opening hours and WhatsApp to enquire about classes.', 'contact ThatBioTutor, Biology tuition enquiry, Bencoolen tuition centre, Biology tuition WhatsApp, Biology tuition Singapore'],
        'trees' => ['Tree Planting & Environmental Causes | ThatBioTutor', 'See ThatBioTutor\'s tree-planting efforts and support for Singapore environmental causes, and how our community gives back beyond the classroom.', 'ThatBioTutor tree planting, environmental causes Singapore, tree planting Singapore, community giving, ThatBioTutor causes'],
        'tuitionrecs' => ['Secondary & JC Tuition Recommendations | ThatBioTutor', 'Browse ThatBioTutor\'s tuition recommendations for secondary school and JC subjects in Singapore, with guidance on arranging trial lessons.', 'tuition recommendations Singapore, secondary school tuition, JC tuition, tuition centre recommendations, trial lessons'],
        'intensivefix' => ['IntensiveFix: Sec 3 Biology Holiday Revision | ThatBioTutor', 'Fix your Sec 3 Biology foundation in ThatBioTutor\'s four-day IntensiveFix holiday programme, taught by our founder, before you start Sec 4.', 'Sec 3 Biology revision, Biology holiday programme, IntensiveFix, Sec 3 Biology crash course, Sec 3 Biology foundation, Biology tuition Singapore'],
    ];
    $metadata = [];
    foreach (tbt_page_registry() as $key => $page) {
        [$title, $description, $keywords] = $copy[$key] ?? [$page['title'] . ' | ThatBioTutor', 'ThatBioTutor O-Level Pure and G3 Biology learning resource.', 'O Level Biology, Pure Biology, G3 Biology, Biology tuition Singapore, ThatBioTutor'];
        $canonical = home_url((string) $page['canonical']);
        $metadata[$key] = ['title' => $title, 'description' => $description, 'keywords' => $keywords, 'canonical' => $canonical, 'og_title' => $title, 'og_description' => $description, 'og_url' => $canonical];
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

/** Resolve previously saved overrides without changing portable canonical URLs. */
function tbt_current_metadata(): ?array
{
    $key = tbt_current_metadata_key();
    $meta = tbt_page_metadata()[$key] ?? null;
    if (!is_array($meta)) { return null; }
    if (function_exists('get_queried_object_id') && function_exists('get_post_meta')) {
        $prefix = 'tbt_' . str_replace('-', '_', $key) . '_seo_';
        foreach (['title', 'description', 'keywords'] as $field) {
            $value = get_post_meta(get_queried_object_id(), $prefix . $field, true);
            if (!is_string($value)) { continue; }
            $value = trim(preg_replace('/\s+/u', ' ', wp_strip_all_tags($value)) ?? '');
            if ($field === 'keywords') {
                // Tidy "a ,  b,,c" into "a, b, c".
                $value = implode(', ', array_filter(array_map('trim', explode(',', $value)), static fn (string $k): bool => $k !== ''));
                if ($value !== '') { $meta['keywords'] = $value; }
                continue;
            }
            if ($value !== '') { $meta[$field] = $value; $meta['og_' . $field] = $value; }
        }
    }
    return $meta;
}

/** Keep the legacy SEO values usable by the theme and hidden from WordPress Custom Fields. */
function tbt_protect_page_seo_metadata(bool $protected, string $key, string $type): bool
{
    return $protected || ($type === 'post' && preg_match('/^tbt_[a-z0-9_]+_seo_(title|description|keywords)$/iD', $key) === 1);
}

function tbt_render_meta_tags(): void
{
    $meta = tbt_current_metadata();
    if (!is_array($meta)) { return; }
    // Core otherwise adds a second canonical for singular pages at priority 10.
    if (function_exists('remove_action')) { remove_action('wp_head', 'rel_canonical'); }
    echo '<meta name="description" content="' . esc_attr((string) $meta['description']) . '">' . "\n";
    echo '<meta name="keywords" content="' . esc_attr((string) $meta['keywords']) . '">' . "\n";
    echo '<link rel="canonical" href="' . esc_url((string) $meta['canonical']) . '">' . "\n";
    echo '<meta property="og:type" content="website">' . "\n";
    echo '<meta property="og:title" content="' . esc_attr((string) $meta['og_title']) . '">' . "\n";
    echo '<meta property="og:description" content="' . esc_attr((string) $meta['og_description']) . '">' . "\n";
    echo '<meta property="og:url" content="' . esc_url((string) $meta['og_url']) . '">' . "\n";
    // Link previews (WhatsApp, Facebook, LinkedIn, X): one 1200x630 brand share image, served from the theme so it follows the host.
    $themeUri = defined('TBT_THEME_URI') ? (string) TBT_THEME_URI : (function_exists('get_stylesheet_directory_uri') ? (string) get_stylesheet_directory_uri() : '');
    $image = rtrim($themeUri, '/') . '/assets/images/og/thatbiotutor-share.jpg';
    $imageAlt = 'ThatBioTutor: O Level Pure & G3 Biology tuition in Singapore, a class in session';
    echo '<meta property="og:site_name" content="ThatBioTutor">' . "\n";
    echo '<meta property="og:locale" content="en_SG">' . "\n";
    echo '<meta property="og:image" content="' . esc_url($image) . '">' . "\n";
    echo '<meta property="og:image:type" content="image/jpeg">' . "\n";
    echo '<meta property="og:image:width" content="1200">' . "\n";
    echo '<meta property="og:image:height" content="630">' . "\n";
    echo '<meta property="og:image:alt" content="' . esc_attr($imageAlt) . '">' . "\n";
    echo '<meta name="twitter:card" content="summary_large_image">' . "\n";
    echo '<meta name="twitter:title" content="' . esc_attr((string) $meta['og_title']) . '">' . "\n";
    echo '<meta name="twitter:description" content="' . esc_attr((string) $meta['og_description']) . '">' . "\n";
    echo '<meta name="twitter:image" content="' . esc_url($image) . '">' . "\n";
    echo '<meta name="twitter:image:alt" content="' . esc_attr($imageAlt) . '">' . "\n";
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

/** The circular TBT logo is the browser-tab (favicon) and home-screen icon on every page. */
function tbt_render_favicon(): void
{
    $base = TBT_THEME_URI . '/assets/icons/';
    echo '<link rel="icon" type="image/png" sizes="32x32" href="' . esc_url($base . 'favicon-32.png') . '">' . "\n";
    echo '<link rel="icon" type="image/png" sizes="192x192" href="' . esc_url($base . 'favicon-192.png') . '">' . "\n";
    echo '<link rel="apple-touch-icon" sizes="180x180" href="' . esc_url($base . 'apple-touch-icon.png') . '">' . "\n";
}

if (function_exists('add_action')) {
    add_action('wp_head', 'tbt_render_favicon', 1);
    add_action('wp_head', 'tbt_render_meta_tags', 2);
    add_action('template_redirect', 'tbt_redirect_legacy_routes', 1);
}
if (function_exists('add_filter')) {
    add_filter('document_title_parts', 'tbt_filter_document_title_parts');
    add_filter('is_protected_meta', 'tbt_protect_page_seo_metadata', 10, 3);
}
