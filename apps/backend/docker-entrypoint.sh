#!/bin/sh

set -e

composer install --no-dev --optimize-autoloader

php artisan migrate --force

php artisan config:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache

exec php-fpm
