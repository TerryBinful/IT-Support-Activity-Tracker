# IT Activity Tracker

Docker-first Laravel 13 application for personal IT activity tracking and reporting.

## Host requirement

Docker Desktop is the only application required to run the application. PHP, Composer, Node.js, Apache and PostgreSQL run inside Docker.

You can obtain the repository either by cloning it with Git or by downloading the GitHub repository as a ZIP; no Git installation is required if you use the ZIP route.

## First run

From the project directory:

```bash
docker compose up -d --build
```

Open **http://localhost:8080**.

On first startup, Laravel creates and persists its `APP_KEY` in a Docker volume, waits for PostgreSQL, runs migrations and seeds the default IT categories.

## Main services

- `app`: Laravel 13 / PHP 8.4 / Apache
- `postgres`: PostgreSQL 17
- `scheduler`: Laravel scheduler for monthly report reminders

## Data persistence

- `postgres_data`: application database
- `laravel_storage`: uploaded files and runtime storage
- `laravel_config`: generated `.env` and Laravel `APP_KEY`

`docker compose down` keeps your data. `docker compose down -v` deletes the Docker volumes and therefore deletes application data and the generated key.

## Scheduled reports

The scheduler runs every Friday at 08:00 using the application's configured timezone (`Africa/Accra`). It creates an in-app monthly-report reminder only when that Friday is the final Friday of the month.

## Current features

- Multi-user registration and login
- Each user sees and manages only their own activities
- Activity CRUD with category, priority, status, timing, outcome, blockers and references
- Dashboard with daily/monthly statistics
- Search and filters
- Configurable report columns and ordering
- Saved report layouts
- Excel, CSV and PDF export foundations
- Last-Friday monthly report reminder foundation

## GitHub safety

Do not commit local secrets. The project does not require a host `.env` file. The runtime `.env` and `APP_KEY` are kept in Docker's persistent `laravel_config` volume.
