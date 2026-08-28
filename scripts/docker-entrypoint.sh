#!/bin/sh
set -eu
cd /var/www/html

mkdir -p storage/framework/cache storage/framework/sessions storage/framework/views storage/logs /var/www/config
chown -R www-data:www-data storage bootstrap/cache || true

# Keep Laravel's generated APP_KEY in a persistent Docker volume rather than
# making users install PHP/Composer or commit a secret into GitHub.
if [ ! -f /var/www/config/.env ]; then
  cp .env.example /var/www/config/.env
fi
rm -f .env
ln -s /var/www/config/.env .env
if ! grep -q '^APP_KEY=base64:' /var/www/config/.env 2>/dev/null; then
  php artisan key:generate --force
fi

# Ensure the database is ready, then migrate/seed. These operations are idempotent.
i=0
until php artisan migrate --force >/tmp/migrate.log 2>&1; do
  i=$((i + 1))
  if [ "$i" -ge 30 ]; then
    cat /tmp/migrate.log
    exit 1
  fi
  echo "Waiting for PostgreSQL... attempt $i/30"
  sleep 2
done
php artisan db:seed --force
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan storage:link >/dev/null 2>&1 || true

exec "$@"
