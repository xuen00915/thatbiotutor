<?php
/** Google reviews strip (Home and IntensiveFix). Every card is edited on the Home page, Google reviews tab. */
if (!defined('ABSPATH')) { exit; }
$tbt_review_snapshot = json_decode((string) file_get_contents(TBT_THEME_DIR . '/assets/content/google-reviews.json'), true) ?: [];
$tbt_review_defaults = tbt_page_content_defaults('home');
$tbt_rv = static fn (string $name): string => trim((string) tbt_home_field($name, $tbt_review_defaults[$name] ?? ''));
$tbt_reviews = [];
for ($tbt_i = 1; $tbt_i <= 12; $tbt_i++) {
    $tbt_name = $tbt_rv('home_google_review_' . $tbt_i . '_name');
    $tbt_text = $tbt_rv('home_google_review_' . $tbt_i . '_text');
    if ($tbt_name === '' && $tbt_text === '') {
        continue;
    }
    $tbt_stars = (int) $tbt_rv('home_google_review_' . $tbt_i . '_stars');
    $tbt_photo = tbt_page_image('home_google_review_' . $tbt_i . '_photo', '', function_exists('get_option') ? (int) get_option('page_on_front', 0) : 0);
    // The bundled photo belongs to the reviewer who shipped in this slot; a different name shows initials instead.
    $tbt_bundled = $tbt_review_snapshot['reviews'][$tbt_i - 1] ?? [];
    if ($tbt_photo['url'] === '' && ($tbt_bundled['name'] ?? null) === $tbt_name && !empty($tbt_bundled['avatar'])) {
        $tbt_photo = ['url' => TBT_THEME_URI . '/assets/images/files/' . $tbt_bundled['avatar'], 'alt' => $tbt_name];
    }
    $tbt_words = preg_split('/\s+/u', trim((string) preg_replace('/[^\p{L}\p{N}\s]+/u', '', $tbt_name))) ?: [];
    $tbt_initials = mb_strtoupper(mb_substr($tbt_words[0] ?? '', 0, 1) . (count($tbt_words) > 1 ? mb_substr((string) end($tbt_words), 0, 1) : ''));
    $tbt_date = $tbt_rv('home_google_review_' . $tbt_i . '_date');
    $tbt_time = $tbt_date !== '' ? strtotime($tbt_date) : false;
    $tbt_reviews[] = [
        'name' => $tbt_name,
        'text' => $tbt_text,
        'stars' => $tbt_stars >= 1 && $tbt_stars <= 5 ? $tbt_stars : 5,
        'photo' => $tbt_photo,
        'initials' => $tbt_initials !== '' ? $tbt_initials : '★',
        'date' => $tbt_time !== false ? date('Y-m-d', $tbt_time) : '',
        'date_label' => $tbt_time !== false ? date('M j, Y', $tbt_time) : $tbt_date,
    ];
}
unset($tbt_i, $tbt_name, $tbt_text, $tbt_stars, $tbt_photo, $tbt_bundled, $tbt_words, $tbt_initials, $tbt_date, $tbt_time);
$tbt_google_url = $tbt_rv('home_google_url');
$tbt_google_heading = $tbt_rv('home_google_heading');
?>
<div class="tbt-google-reviews" aria-label="<?php echo esc_attr($tbt_google_heading !== '' ? $tbt_google_heading : 'Google Reviews'); ?>" data-google-reviews>
    <?php if ($tbt_google_heading !== '') : ?><h3><span class="tbt-google-mark" aria-hidden="true">G</span><?php echo esc_html($tbt_google_heading); ?></h3><?php endif; ?>
    <div class="tbt-google-reviews__track" data-review-track tabindex="0" aria-label="Student reviews; scroll for more">
        <?php foreach ($tbt_reviews as $tbt_index => $tbt_review) : ?>
        <article class="tbt-google-review">
            <div class="tbt-google-review__avatar"><?php if ($tbt_review['photo']['url'] !== '') : ?><img src="<?php echo esc_url($tbt_review['photo']['url']); ?>" alt="<?php echo esc_attr($tbt_review['name']); ?>" width="64" height="64" loading="<?php echo $tbt_index < 3 ? 'eager' : 'lazy'; ?>"><?php else : ?><span class="tbt-google-review__initials" aria-hidden="true"><?php echo esc_html($tbt_review['initials']); ?></span><?php endif; ?><span class="tbt-google-mark" aria-hidden="true">G</span></div>
            <h4><?php echo esc_html($tbt_review['name']); ?></h4>
            <?php if ($tbt_review['date_label'] !== '') : ?><time<?php echo $tbt_review['date'] !== '' ? ' datetime="' . esc_attr($tbt_review['date']) . '"' : ''; ?>><?php echo esc_html($tbt_review['date_label']); ?></time><?php endif; ?>
            <p class="tbt-home-stars" aria-label="<?php echo esc_attr($tbt_review['stars'] . ' out of 5 stars'); ?>"><?php echo esc_html(str_repeat('★', $tbt_review['stars'])); ?></p>
            <p class="tbt-google-review__text" id="tbt-review-<?php echo esc_attr((string) $tbt_index); ?>"><?php echo esc_html($tbt_review['text']); ?></p>
            <?php if (mb_strlen($tbt_review['text']) > 160) : ?><button type="button" data-review-expand aria-controls="tbt-review-<?php echo esc_attr((string) $tbt_index); ?>" aria-expanded="false">Read more</button><?php endif; ?>
        </article>
        <?php endforeach; ?>
    </div>
    <div class="tbt-google-reviews__controls"><button type="button" data-review-prev aria-label="Previous reviews">‹</button><button type="button" data-review-next aria-label="Next reviews">›</button></div>
    <?php if ($tbt_google_url !== '') : ?><a class="tbt-google-reviews__source" href="<?php echo esc_url($tbt_google_url); ?>" target="_blank" rel="noopener noreferrer"><?php echo esc_html($tbt_rv('home_google_link_label')); ?></a><?php endif; ?>
</div>
