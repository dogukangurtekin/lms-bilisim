#!/usr/bin/env bash
set -euo pipefail

STAMP=$(date +%Y%m%d_%H%M%S)
mkdir -p storage/backups
mysqldump -h "${DB_HOST:-127.0.0.1}" -P "${DB_PORT:-3306}" -u "${DB_USERNAME:-root}" -p"${DB_PASSWORD:-}" "${DB_DATABASE:-school_management}" > "storage/backups/db_${STAMP}.sql"

echo "Backup created: storage/backups/db_${STAMP}.sql"
