#!/usr/bin/env bash
# Lint PHP via Docker (no local PHP needed).
#   scripts/lint.sh               lint everything in phpcs.xml.dist
#   scripts/lint.sh file.php ...  lint only the given files
# Runs `php -l` on PHP 7.4 and 8.2, then PHPCompatibility (testVersion 7.4-8.2).
set -euo pipefail

cd "$(dirname "$0")/.."
TOOLS_DIR=.tools/phpcs

if [ "$#" -gt 0 ]; then
	FILES=("$@")
else
	mapfile -t FILES < <(find application src index.php -name '*.php' \
		-not -path 'application/third_party/*' \
		-not -path 'application/cache/*' \
		-not -path 'application/logs/*')
fi

status=0

for ver in 7.4 8.2; do
	echo "== php -l (PHP $ver) =="
	if ! printf '%s\0' "${FILES[@]}" | docker run --rm -i -v "$PWD":/app -w /app "php:$ver-cli" \
		sh -c 'xargs -0 -n1 php -l 2>&1 | grep -v "^No syntax errors"'; then
		: # grep found nothing to print -> all files clean
	else
		status=1
	fi
done

if [ ! -x "$TOOLS_DIR/vendor/bin/phpcs" ]; then
	echo "== installing phpcs + PHPCompatibility into $TOOLS_DIR =="
	mkdir -p "$TOOLS_DIR"
	docker run --rm -u "$(id -u):$(id -g)" -v "$PWD/$TOOLS_DIR":/app -w /app composer:2 sh -c '
		composer config --no-interaction allow-plugins.dealerdirect/phpcodesniffer-composer-installer true &&
		composer require --no-interaction --quiet squizlabs/php_codesniffer:^3 phpcompatibility/php-compatibility:^9.3 dealerdirect/phpcodesniffer-composer-installer'
fi

echo "== PHPCompatibility (7.4-8.2) =="
docker run --rm -v "$PWD":/app -w /app php:8.2-cli \
	"$TOOLS_DIR/vendor/bin/phpcs" --standard=phpcs.xml.dist "${@}" || status=1

exit $status
