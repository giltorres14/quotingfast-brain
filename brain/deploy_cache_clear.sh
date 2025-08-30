#!/usr/bin/env bash
set -euo pipefail

echo "Clearing Laravel caches..."
php artisan view:clear || true
php artisan cache:clear || true
php artisan config:clear || true
php artisan route:clear || true
php artisan optimize:clear || true

echo "Running pre-deploy checks..."
php pre_deploy_check.php || exit 1

echo "Done."









