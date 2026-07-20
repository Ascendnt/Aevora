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

echo "Starting server on port ${PORT:-8080}"
exec php spark serve --host 0.0.0.0 --port "${PORT:-8080}"