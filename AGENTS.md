# Contributor Guide for Agents

## Project context

This is a Laravel 10 inventory-management application. Its main domains are materials, BOMs, warehouse transactions, production orders, kitting, reporting, vendors, and access control. Browser routes are defined mainly in `routes/web.php` under `/app` and require authentication plus the `checkStatus` middleware.

## Repository map

| Path | Responsibility |
| --- | --- |
| `app/Http/Controllers` | Request handling and inventory workflows |
| `app/Models` | Eloquent models and relationships |
| `app/Services` | Reusable domain/application services |
| `app/helpers.php` | Globally autoloaded helper functions |
| `database/migrations` | Schema migrations |
| `database/seeders`, `database/factories` | Test and development data |
| `resources/views` | Blade templates |
| `resources/js` | Vite-managed frontend scripts |
| `routes/web.php`, `routes/api.php` | Web and API routes |
| `tests/Feature`, `tests/Unit` | PHPUnit tests |
| `docker-compose.yml`, `Dockerfile` | Containerized development stack |

## Working conventions

- Preserve existing user changes. Inspect `git status` before editing and avoid unrelated formatting or refactors.
- Follow Laravel conventions: use form requests or explicit validation, route-model binding where appropriate, Eloquent relationships, and named routes.
- Keep controllers focused; put reusable or multi-step business logic in `app/Services`.
- Make authorization explicit for protected or role-sensitive workflows. Maintain the `auth` and `checkStatus` protections on `/app` routes unless the task calls for a deliberate change.
- Update Blade views and route names together when changing a user-facing workflow.
- Do not modify generated dependencies (`vendor/`, `node_modules/`) or commit secrets from `.env`.

## Database rules

- Never alter a migration that may already have been applied outside a local-only change. Create a new, reversible migration instead.
- Use descriptive migration names and provide `down()` logic whenever a safe reversal exists.
- Preserve inventory consistency: review the effects of material, BOM, warehouse, production-order, and kitting changes on quantities and reservations.
- Use transactions for operations that update multiple related records and must succeed or fail together.

## Frontend rules

- Frontend assets are managed with Vite. Use `npm run dev` during development and `npm run build` to verify production compilation.
- Keep JavaScript changes within `resources/js` and styles/assets in the appropriate `resources` or `public` location; do not edit compiled assets by hand.
- Retain the existing Bootstrap 4/AdminLTE visual conventions unless a task explicitly requests a redesign.

## Verification

Run the smallest relevant checks first, then broader checks when the change warrants them:

```bash
php artisan test
./vendor/bin/pint
npm run build
```

On Windows, use `vendor\\bin\\pint` if needed. For database-related work, run the relevant migrations in a disposable database and add or update feature tests where practical.

## Local environments

- Native: configure `.env`, run `php artisan migrate`, `npm run dev`, and `php artisan serve`.
- Docker: `docker compose up --build`. Use `db` and `redis` as the database and Redis hosts from within containers. The Compose stack runs migrations automatically.

## Change handoff

In the final handoff, summarize the files changed, behavior affected, and verification run. Call out any command that could not be run and why.
