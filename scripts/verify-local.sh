#!/bin/sh

set -eu

project_root=$(CDPATH='' cd -- "$(dirname "$0")/.." && pwd)
cd "$project_root"

find wordpress tests -type f -name '*.php' -print | sort | while IFS= read -r php_file; do
    php -l "$php_file" >/dev/null
done

find tests -maxdepth 1 -type f -name '*-test.php' ! -name 'runtime-editing-test.php' -print | sort | while IFS= read -r php_test; do
    php "$php_test"
done

if [ -d wordpress ]; then
    find wordpress -type f -name '*.js' -print | sort | while IFS= read -r js_file; do
        node --check "$js_file"
    done
fi

if find tests -maxdepth 1 -type f -name 'test_*.py' -print -quit | grep -q .; then
    python3 -m unittest discover -s tests -p 'test_*.py'
fi

git diff --check
printf '%s\n' 'PASS: local verification'
