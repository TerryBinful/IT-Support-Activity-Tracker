# IT Activity Tracker

Docker-first Laravel application for personal IT activity tracking, reporting, exports, and monthly reminders.

## Stack

- Laravel 13
- Livewire 4 starter kit
- PHP 8.4
- PostgreSQL 17
- Nginx
- Node.js 22 / Vite
- Docker Compose

The Windows host only needs Docker Desktop and Git (Git can also be supplied through another container if desired).

## First run

From the repository root:

```bash
docker compose up -d --build
```

The `bootstrap` service will create the Laravel + Livewire application in `src/` when it does not exist, create `.env`, and generate the Laravel application key. The remaining services then start and migrations are run automatically.

Open:

```text
http://localhost:8080
```

Vite development server:

```text
http://localhost:5173
```

## Useful commands

```bash
docker compose ps
docker compose logs -f app
docker compose logs -f web
docker compose exec app php artisan migrate
docker compose exec app php artisan tinker
docker compose exec app php artisan test
docker compose down
docker compose down -v   # WARNING: removes PostgreSQL data volume
```

## Source-control model

The intended workflow is:

1. Repository contains Docker configuration and the Laravel application source.
2. Secrets stay out of GitHub.
3. `src/.env.example` is safe to commit; `src/.env` is not.
4. PostgreSQL data lives in the named Docker volume `postgres_data`.
5. A fresh clone can rebuild the environment with `docker compose up -d --build`.

## Planned application features

- Multi-user authentication and per-user activity spaces
- Activity CRUD, search, filtering, categories, priority, status, timing
- Dashboard and monthly reporting
- Custom export column selection and ordering
- Excel, CSV, and PDF export
- In-app monthly report reminder on the last Friday
- Background queue and scheduler containers
- Tests, backups, and production hardening
