<?php
/** Portable review snapshot from the original site's Google Reviews widget. */
if (!defined('ABSPATH')) { exit; }
$tbt_review_data = json_decode((string) file_get_contents(TBT_THEME_DIR . '/assets/content/google-reviews.json'), true);
$tbt_reviews = $tbt_review_data['reviews'] ?? [];
?>
<div class="tbt-google-reviews" aria-label="Google Reviews" data-google-reviews>
    <h3><span class="tbt-google-mark" aria-hidden="true">G</span>Google Reviews</h3>
    <div class="tbt-google-reviews__track" data-review-track tabindex="0" aria-label="Student reviews; scroll for more">
        <?php foreach ($tbt_reviews as $tbt_index => $tbt_review) : ?>
        <article class="tbt-google-review">
            <div class="tbt-google-review__avatar"><img src="<?php echo esc_url(TBT_THEME_URI . '/assets/images/files/' . $tbt_review['avatar']); ?>" alt="<?php echo esc_attr($tbt_review['name']); ?>" width="64" height="64" loading="<?php echo $tbt_index < 3 ? 'eager' : 'lazy'; ?>"><span class="tbt-google-mark" aria-hidden="true">G</span></div>
            <h4><?php echo esc_html($tbt_review['name']); ?></h4>
            <time datetime="<?php echo esc_attr($tbt_review['date']); ?>"><?php echo esc_html(date('M j, Y', strtotime($tbt_review['date']))); ?></time>
            <p class="tbt-home-stars" aria-label="5 out of 5 stars">★★★★★</p>
            <p class="tbt-google-review__text" id="tbt-review-<?php echo esc_attr((string) $tbt_index); ?>"><?php echo esc_html($tbt_review['text']); ?></p>
            <?php if (strlen($tbt_review['text']) > 160) : ?><button type="button" data-review-expand aria-controls="tbt-review-<?php echo esc_attr((string) $tbt_index); ?>" aria-expanded="false">Read more</button><?php endif; ?>
        </article>
        <?php endforeach; ?>
    </div>
    <div class="tbt-google-reviews__controls"><button type="button" data-review-prev aria-label="Previous reviews">‹</button><button type="button" data-review-next aria-label="Next reviews">›</button></div>
    <a class="tbt-google-reviews__source" href="<?php echo esc_url($tbt_review_data['googleUrl']); ?>" target="_blank" rel="noopener noreferrer">Read our reviews on Google</a>
</div>
