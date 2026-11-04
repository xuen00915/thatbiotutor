#!/usr/bin/env bash
set -euo pipefail
base="${TBT_RUNTIME_URL:-http://localhost:8888}"
routes=(/ /classes/ /sec-3-pure-g3-biology-tuition/ /sec-4-pure-g3-biology-tuition/ /testimonials/ /notes/ /celldivision/ /about/ /contact/ /trees/ /tuitionrecs/ /intensivefix/)
for route in "${routes[@]}"; do
  code="$(curl -sS -o /tmp/tbt-runtime-page.html -w '%{http_code}' "${base}${route}")"
  test "$code" = "200"
  ! rg -qi 'static\.wixstatic\.com|wix\.com/media' /tmp/tbt-runtime-page.html
  rg -q 'site-header' /tmp/tbt-runtime-page.html
done
for route in /classes/ /sec-3-pure-g3-biology-tuition/ /sec-4-pure-g3-biology-tuition/; do
  curl -fsS "${base}${route}" | rg -q 'assets/css/pages-programmes\.css'
done
classes_html="$(curl -fsS "${base}/classes/")"
rg -q 'data-section="hero"' <<<"${classes_html}"
rg -q 'curated_heroes_classes_original' <<<"${classes_html}"
curl -fsS "${base}/sec-3-pure-g3-biology-tuition/" | rg -q 'data-section="stats"'
curl -fsS "${base}/sec-4-pure-g3-biology-tuition/" | rg -q 'Phase 3 — O Levels Intensives'
test "$(curl -sS -o /dev/null -w '%{http_code} %{num_redirects}' --max-redirs 0 "${base}/s3-intensivefix/")" = "301 0"
test "$(curl -sS -o /dev/null -w '%{http_code} %{num_redirects}' --max-redirs 0 "${base}/classes/sec-3-pure-g3-biology-tuition/")" = "301 0"
curl -fsS "${base}/" | rg -q '/wp-content/themes/thatbiotutor-hello-child/assets/images/.+\.webp'
echo 'PASS: canonical runtime routes, redirects, shell, and local media'
