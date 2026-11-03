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
test "$(curl -sS -o /dev/null -w '%{http_code} %{num_redirects}' --max-redirs 0 "${base}/s3-intensivefix/")" = "301 0"
test "$(curl -sS -o /dev/null -w '%{http_code} %{num_redirects}' --max-redirs 0 "${base}/classes/sec-3-pure-g3-biology-tuition/")" = "301 0"
curl -fsS "${base}/" | rg -q '/wp-content/themes/thatbiotutor-hello-child/assets/images/.+\.webp'
echo 'PASS: canonical runtime routes, redirects, shell, and local media'
