#!/bin/sh
set -e

# Railway provides PGHOST/PGPORT/etc from the linked Postgres service.
# Fall back to the dotted CI names, then to local defaults.
DB_HOST="${PGHOST:-${DB_HOST:-db}}"
DB_PORT="${PGPORT:-${DB_PORT:-5432}}"
DB_NAME="${PGDATABASE:-hris}"
DB_USER="${PGUSER:-postgres}"
DB_PASS="${PGPASSWORD:-postgres}"

# Hand these to CodeIgniter under the names its config reads.
export database.default.hostname="$DB_HOST"
export database.default.port="$DB_PORT"
export database.default.database="$DB_NAME"
export database.default.username="$DB_USER"
export database.default.password="$DB_PASS"

echo "Waiting for PostgreSQL at $DB_HOST:$DB_PORT ..."
until php -r "
    \$c = @pg_connect(sprintf('host=%s port=%s dbname=%s user=%s password=%s',
        getenv('PGHOST') ?: '$DB_HOST',
        getenv('PGPORT') ?: '$DB_PORT',
        getenv('PGDATABASE') ?: '$DB_NAME',
        getenv('PGUSER') ?: '$DB_USER',
        getenv('PGPASSWORD') ?: '$DB_PASS'
    ));
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