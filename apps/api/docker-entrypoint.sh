#!/usr/bin/env sh
set -e

php artisan migrate --force
php artisan db:seed

exec php artisan serve --host=0.0.0.0 --port=8000
