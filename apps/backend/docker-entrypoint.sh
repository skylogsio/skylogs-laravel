#!/bin/sh

set -e

if [ ! -f .env ]; then
    cp .env.example .env
fi

composer install --no-dev --optimize-autoloader

php artisan migrate --force

php artisan optimize:clear
php artisan config:cache
php artisan route:cache

exec php-fpm
