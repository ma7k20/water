#!/usr/bin/env sh
set -e

cd /var/www/html

if [ ! -f ".env" ]; then
  if [ -f ".env.example" ]; then
    cp .env.example .env
  else
    touch .env
  fi
fi

if [ -z "${APP_KEY}" ]; then
  php artisan key:generate --force --no-interaction >/dev/null 2>&1 || true
fi

php artisan config:clear || true
php artisan cache:clear || true

if [ "${RUN_MIGRATIONS:-true}" = "true" ]; then
  php artisan migrate --force --no-interaction
fi

php artisan storage:link || true
php artisan config:cache || true
php artisan route:cache || true
php artisan view:cache || true

exec php artisan serve --host=0.0.0.0 --port="${PORT:-10000}"
