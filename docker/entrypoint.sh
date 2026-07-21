#!/bin/sh
set -e

# Railway's Postgres service provides these dot-free variables.
DB_HOST="${PGHOST:-db}"
DB_PORT="${PGPORT:-5432}"
DB_NAME="${PGDATABASE:-hris}"
DB_USER="${PGUSER:-postgres}"
DB_PASS="${PGPASSWORD:-postgres}"

echo "Waiting for PostgreSQL at $DB_HOST:$DB_PORT ..."
until php -r "
    \$c = @pg_connect('host=$DB_HOST port=$DB_PORT dbname=$DB_NAME user=$DB_USER password=$DB_PASS');
    exit(\$c ? 0 : 1);
"; do
    echo "Waiting for PostgreSQL..."
    sleep 2
done
echo "PostgreSQL is up."

echo "Running migrations..."
php spark migrate --all

echo "Seeding (skips automatically if already seeded)..."
php spark db:seed InitialSeeder

# Resolve the public base URL so links (e.g. the "Log in" button) point to
# wherever this container is actually reachable instead of a hardcoded host.
#   1. An explicit app.baseURL env var (e.g. set via docker-compose) wins.
#   2. An explicit APP_BASE_URL env var (manual override) wins next.
#   3. Railway auto-injects RAILWAY_PUBLIC_DOMAIN for any service with a
#      public domain - use it so Railway deploys need zero extra config.
#   4. Otherwise fall back to localhost for bare local runs.
EXISTING_BASE_URL="$(printenv 'app.baseURL' 2>/dev/null || true)"
if [ -n "$EXISTING_BASE_URL" ]; then
    BASE_URL="$EXISTING_BASE_URL"
elif [ -n "$APP_BASE_URL" ]; then
    BASE_URL="$APP_BASE_URL"
elif [ -n "$RAILWAY_PUBLIC_DOMAIN" ]; then
    BASE_URL="https://${RAILWAY_PUBLIC_DOMAIN}/"
else
    BASE_URL="http://localhost:${PORT:-8080}/"
fi

echo "Starting server on port ${PORT:-8080} (app.baseURL=${BASE_URL})"
exec env "app.baseURL=${BASE_URL}" php spark serve --host 0.0.0.0 --port "${PORT:-8080}"