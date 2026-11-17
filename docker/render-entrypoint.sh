#!/bin/sh
set -eu

port="${PORT:-10000}"
export APP_URL="${APP_URL:-${RENDER_EXTERNAL_URL:-http://localhost}}"

mkdir -p public/uploads storage/framework/cache/data storage/framework/sessions storage/framework/views storage/logs
chown -R www-data:www-data storage/framework storage/logs
chown -R www-data:www-data public/uploads

sed -ri "s/Listen 80/Listen ${port}/" /etc/apache2/ports.conf
sed -ri "s/<VirtualHost \\*:80>/<VirtualHost *:${port}>/" /etc/apache2/sites-available/000-default.conf

php docker/create-schema.php
php artisan migrate --force
php artisan config:cache
php artisan view:cache

exec apache2-foreground
