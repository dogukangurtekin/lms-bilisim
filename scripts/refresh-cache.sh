#!/usr/bin/env bash
# Canlıda ayar ve rota önbelleği AÇIK (bootstrap/cache/config.php ve routes-v7.php).
# routes/*.php, config/*.php veya .env değiştiğinde (ve her deploy sonrası) bu betik çalıştırılmalıdır;
# aksi halde değişiklikler uygulamaya yansımaz.
set -euo pipefail
cd "$(dirname "$0")/.."
php artisan config:clear
php artisan route:clear
php artisan config:cache
php artisan route:cache
php artisan view:clear
echo "Önbellek yenilendi."
