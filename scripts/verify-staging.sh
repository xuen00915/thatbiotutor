#!/usr/bin/env bash
set -euo pipefail
if [[ $# -ne 1 || ! "$1" =~ ^https?:// ]]; then echo 'Usage: scripts/verify-staging.sh <approved-url>' >&2; exit 2; fi
base="${1%/}"
routes=(/ /classes/ /sec-3-pure-g3-biology-tuition/ /sec-4-pure-g3-biology-tuition/ /testimonials/ /notes/ /about/ /contact/ /trees/ /intensivefix/)
for route in "${routes[@]}"; do
  code="$(curl -sS -o /tmp/tbt-staging-check.html -w '%{http_code}' "${base}${route}")"
  [[ "$code" == 200 ]] || { echo "FAIL ${route}: HTTP ${code}" >&2; exit 1; }
  rg -q 'ThatBioTutor|Thatbiotutor' /tmp/tbt-staging-check.html
done
echo "PASS: read-only staging routes at ${base}"
