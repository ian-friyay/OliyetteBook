#!/bin/sh
set -eu

port="${PORT:-10000}"
export APP_URL="${APP_URL:-${RENDER_EXTERNAL_URL:-http://localhost}}"
schema="${DB_SCHEMA:-oliyettebook}"

mkdir -p public/uploads storage/framework/cache/data storage/framework/sessions storage/framework/views storage/logs
chown -R www-data:www-data storage/framework storage/logs
chown -R www-data:www-data public/uploads

case "$schema" in
    ''|*[!A-Za-z0-9_]*|[0-9]*)
        echo "DB_SCHEMA must be a valid PostgreSQL schema identifier." >&2
        exit 1
        ;;
esac

sed -ri "s/Listen 80/Listen ${port}/" /etc/apache2/ports.conf
sed -ri "s/<VirtualHost \*:80>/<VirtualHost *:${port}>/" /etc/apache2/sites-available/000-default.conf

php docker/create-schema.php
php artisan migrate --force
php artisan db:seed --class='Database\Seeders\DemoAdminSeeder' --force
php artisan db:seed --class='Database\Seeders\SampleCottagesSeeder' --force
php artisan db:seed --class='Database\Seeders\SampleRoomsSeeder' --force
php artisan config:cache
php artisan view:cache

exec apache2-foreground
