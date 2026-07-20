# HRIS — CodeIgniter 4 + PostgreSQL

A starter HRIS with a working login, dashboard, and full CRUD for **Companies** and **Branches**.
Employees, Time & attendance, Leave, and Payroll are hardcoded placeholder pages for now.


## Quick start with Docker (recommended)

Requires only [Docker Desktop](https://www.docker.com/products/docker-desktop/). No PHP, no PostgreSQL install needed.

```bash
docker compose up --build
```

That's it. The container waits for the database, runs migrations, seeds the demo
data (only once), and starts the server.

Open **http://localhost:8080** and sign in with `admin@hris.test` / `password123`.

- Stop: `Ctrl+C` (or `docker compose down`)
- Reset the database completely: `docker compose down -v` then `up` again
- Your code in `app/` and `public/` is mounted into the container, so edits
  show up on refresh without rebuilding
- The database is reachable from your desktop at `localhost:5433`
  (user `postgres`, password `postgres`, db `hris`) if you want to browse it
  with pgAdmin

---

## Manual setup (without Docker)

## Requirements

- PHP 8.1+ with extensions: `intl`, `mbstring`, `pgsql`, `json`
- PostgreSQL 12+
- No Composer needed — the CodeIgniter 4.7.4 framework (`system/`) is bundled.

Check your PHP extensions with `php -m`. On Windows (XAMPP/Laragon), enable
`extension=pgsql` and `extension=pdo_pgsql` in `php.ini` if they're commented out.

## Setup (step by step)

### 1. Create the database

```sql
CREATE DATABASE hris;
```

### 2. Configure the connection

Open `.env` in the project root and set your PostgreSQL credentials:

```
database.default.hostname = localhost
database.default.database = hris
database.default.username = postgres
database.default.password = your_password
database.default.port     = 5432
```

### 3. Run migrations and seed data

From the project root:

```bash
php spark migrate
php spark db:seed InitialSeeder
```

This creates the `users`, `companies`, and `branches` tables, one admin user,
and a demo company (Acme Corp) with 3 branches.

### 4. Start the app

```bash
php spark serve
```

Open http://localhost:8080

### 5. Sign in

| Email            | Password      |
|------------------|---------------|
| admin@hris.test  | password123   |

**Change this password before using the app for anything real** (update the row
in the `users` table with a new `password_hash` from PHP's `password_hash()`).

## What's functional vs hardcoded

| Module              | Status                                              |
|---------------------|-----------------------------------------------------|
| Login / logout      | Functional (session-based, seeded admin)            |
| Dashboard           | Branch/company counts are live; other stats static  |
| Company settings    | Functional CRUD (add / edit / delete companies)     |
| Branches            | Functional CRUD, per-company filter, single-HQ rule |
| Employees           | Hardcoded sample data                               |
| Time & attendance   | Hardcoded sample data                               |
| Leave               | Hardcoded sample data                               |
| Payroll             | Hardcoded sample data                               |

## Notes

- Deleting a company also deletes its branches (FK cascade).
- Marking a branch as HQ automatically un-flags any other HQ branch in the same company.
- CSRF protection is enabled globally; all forms include `csrf_field()`.
- To deploy under Apache, point the document root at `public/`.

## Where things live

```
app/Controllers/   Auth, Dashboard, Companies, Branches, Pages
app/Models/        UserModel, CompanyModel, BranchModel
app/Views/         layouts/, auth/, dashboard/, companies/, branches/, pages/
app/Database/      Migrations + InitialSeeder
app/Filters/       AuthFilter (protects all app routes)
public/assets/css/ app.css (theme)
```
