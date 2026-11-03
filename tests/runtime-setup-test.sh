#!/usr/bin/env bash
set -euo pipefail
WP_ENV=(npx wp-env run cli wp)
"${WP_ENV[@]}" core is-installed >/dev/null
"${WP_ENV[@]}" theme is-active thatbiotutor-hello-child
"${WP_ENV[@]}" plugin is-active advanced-custom-fields
"${WP_ENV[@]}" tbt setup-site >/dev/null
"${WP_ENV[@]}" tbt setup-site >/dev/null
test "$("${WP_ENV[@]}" post list --post_type=page --meta_key=_tbt_reference_page --meta_value=1 --format=count)" = "12"

home_id="$("${WP_ENV[@]}" option get page_on_front)"
unrelated_id="$("${WP_ENV[@]}" post list --post_type=page --name=unrelated-runtime-check --field=ID 2>/dev/null | head -1)"
if [[ -z "$unrelated_id" ]]; then unrelated_id="$("${WP_ENV[@]}" post create --post_type=page --post_status=publish --post_title='Unrelated Runtime Check' --post_name=unrelated-runtime-check --porcelain)"; fi
"${WP_ENV[@]}" post meta update "$home_id" home_heading 'Runtime client edit survives' >/dev/null
testimonial_id="$("${WP_ENV[@]}" post list --post_type=tbt_testimonial --meta_key=_tbt_seed_key --meta_value=testimonial-javier-tee --field=ID | head -1)"
"${WP_ENV[@]}" post meta update "$testimonial_id" tbt_quote 'Runtime testimonial edit survives' >/dev/null
"${WP_ENV[@]}" tbt setup-site >/dev/null
test "$("${WP_ENV[@]}" post meta get "$home_id" home_heading)" = "Runtime client edit survives"
test "$("${WP_ENV[@]}" post meta get "$testimonial_id" tbt_quote)" = "Runtime testimonial edit survives"
test "$("${WP_ENV[@]}" post get "$unrelated_id" --field=post_status)" = "publish"
"${WP_ENV[@]}" tbt setup-site --refresh-approved >/dev/null
echo 'PASS: clean runtime setup is idempotent and preserves client/unrelated data'
