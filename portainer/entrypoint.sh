#!/bin/bash
set -e

# ── Defaults ──────────────────────────────────────────────────────────
MYSQL_HOST="${MYSQL_HOST:-db}"
MYSQL_PORT="${MYSQL_PORT:-3306}"
MYSQL_DATABASE="${MYSQL_DATABASE:-cloudlog}"
MYSQL_USER="${MYSQL_USER:-cloudlog}"
MYSQL_PASSWORD="${MYSQL_PASSWORD:-cloudlogpassword}"
BASE_LOCATOR="${BASE_LOCATOR:-IO91WM}"
WEBSITE_URL="${WEBSITE_URL:-http://localhost}"
CI_ENV="${CI_ENV:-production}"

# ── CodeIgniter environment (index.php reads CI_ENV from $_SERVER) ─────
case "$CI_ENV" in
    development|testing|production) ;;
    *) echo "Invalid CI_ENV '${CI_ENV}' (use development, testing or production)"; exit 1 ;;
esac
echo "SetEnv CI_ENV ${CI_ENV}" > /etc/apache2/conf-enabled/cloudlog-env.conf

# ── Generate database.php from environment variables ──────────────────
echo "Generating application/config/database.php ..."
cat > /var/www/html/application/config/database.php <<DBEOF
<?php
defined('BASEPATH') OR exit('No direct script access allowed');

\$active_group = 'default';
\$query_builder = TRUE;

\$db['default'] = array(
	'dsn'      => '',
	'hostname' => '${MYSQL_HOST}',
	'port'     => '${MYSQL_PORT}',
	'username' => '${MYSQL_USER}',
	'password' => '${MYSQL_PASSWORD}',
	'database' => '${MYSQL_DATABASE}',
	'dbdriver' => 'mysqli',
	'dbprefix' => '',
	'pconnect' => FALSE,
	'db_debug' => (ENVIRONMENT !== 'production'),
	'cache_on' => FALSE,
	'cachedir' => '',
	'char_set' => 'utf8mb4',
	'dbcollat' => 'utf8mb4_general_ci',
	'swap_pre' => '',
	'encrypt'  => FALSE,
	'compress' => FALSE,
	'stricton' => FALSE,
	'failover' => array(),
	'save_queries' => TRUE
);
DBEOF

# ── Generate config.php from template + environment variables ─────────
echo "Generating application/config/config.php ..."
cp /usr/local/share/cloudlog/config.php.template /var/www/html/application/config/config.php
sed -i "s|%directory%|/var/www/html|g"       /var/www/html/application/config/config.php
sed -i "s|%baselocator%|${BASE_LOCATOR}|g"   /var/www/html/application/config/config.php
sed -i "s|%websiteurl%|${WEBSITE_URL}|g"     /var/www/html/application/config/config.php
# Enable clean URLs (mod_rewrite active)
sed -i "s|\$config\['index_page'\] = 'index.php';|\$config['index_page'] = '';|g" /var/www/html/application/config/config.php

# ── Wait for database ─────────────────────────────────────────────────
echo "Waiting for database at ${MYSQL_HOST}:${MYSQL_PORT} ..."
attempts=0
max_attempts=30
until mariadb -h"${MYSQL_HOST}" -P"${MYSQL_PORT}" -u"${MYSQL_USER}" -p"${MYSQL_PASSWORD}" \
      -D"${MYSQL_DATABASE}" -e "SELECT 1;" >/dev/null 2>&1; do
    attempts=$((attempts + 1))
    if [ "$attempts" -ge "$max_attempts" ]; then
        echo "WARNING: Database not reachable after ${max_attempts} attempts, starting Apache anyway."
        break
    fi
    echo "  attempt ${attempts}/${max_attempts} — retrying in 2s ..."
    sleep 2
done

if [ "$attempts" -lt "$max_attempts" ]; then
    echo "Database is ready."
fi

# ── Set ownership on writable directories ─────────────────────────────
for dir in application/config application/logs assets/qslcard backup updates uploads images/eqsl_card_images assets/json; do
    if [ -d "/var/www/html/${dir}" ]; then
        chown -R www-data:www-data "/var/www/html/${dir}"
        chmod -R g+rw "/var/www/html/${dir}"
    fi
done

# ── Hand off to Apache ────────────────────────────────────────────────
echo "Starting Apache ..."
exec apache2-foreground
