#!/bin/sh
set -e

echo "Waiting for PostgreSQL..."
until php -r '
    $c = @pg_connect(sprintf(
        "host=%s port=%s dbname=%s user=%s password=%s",
        getenv("database.default.hostname") ?: "db",
        getenv("database.default.port") ?: "5432",
        getenv("database.default.database") ?: "hris",
        getenv("database.default.username") ?: "postgres",
        getenv("database.default.password") ?: "postgres"
    ));
    exit($c ? 0 : 1);
'; do
    sleep 1
done
echo "PostgreSQL is up."

echo "Running migrations..."
php spark migrate --all

echo "Seeding (skips automatically if already seeded)..."
php spark db:seed InitialSeeder

echo "Starting server on http://localhost:8080"
exec php spark serve --host 0.0.0.0 --port 8080
