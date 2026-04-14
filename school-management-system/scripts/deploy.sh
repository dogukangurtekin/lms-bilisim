#!/usr/bin/env bash
set -euo pipefail

php artisan down || true
php artisan migrate --force
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan queue:restart
php artisan up

echo "Deploy completed"
