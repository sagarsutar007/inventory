# Inventory Management System

A Laravel web application for managing materials, bills of materials (BOMs), warehouse stock, production orders, kitting, vendors, users, and operational reports.

## Technology

- PHP 8.1+ and Laravel 10
- MariaDB/MySQL and Redis
- Blade, Bootstrap 4, and Vite
- PHPUnit and Laravel Pint
- Docker Compose for containerized local development

## Features

- Raw, semi-finished, and finished material management
- Categories, commodities, vendors, units of measure, and attachments
- BOM creation, costing, import, and export
- Warehouse receipts, issues, stock transactions, and kitting
- Production orders, reserved quantities, and shortage reporting
- Role-, permission-, and user-management workflows

## Local setup

### Prerequisites

Install PHP 8.1 or newer, Composer, Node.js/npm, and a MariaDB/MySQL database. Redis is required when the configured queue or cache driver uses it.

### Install and configure

```bash
composer install
npm install
php artisan key:generate
```

Create and configure `.env` with the database and other application settings, then run:

```bash
php artisan migrate
npm run dev
php artisan serve
```

Visit `http://127.0.0.1:8000`. The application routes are protected by authentication; registration is disabled by default.

> This repository does not include an `.env.example` file. Obtain the expected environment values from your deployment configuration or maintainers; do not commit `.env`.

## Docker setup

Docker Compose starts the application, migrations, queue worker, Vite, MariaDB, Redis, and phpMyAdmin.

```bash
docker compose up --build
```

Services are available at:

- Application: `http://localhost:8000`
- Vite dev server: `http://localhost:5173`
- phpMyAdmin: `http://localhost:8080`
- MariaDB: `localhost:3306`
- Redis: `localhost:6379`

Set `DB_HOST=db`, `REDIS_HOST=redis`, and the matching database credentials in `.env` when using Docker. The `migrate` service applies migrations automatically during startup.

## Development commands

```bash
# Run backend tests
php artisan test

# Format PHP code
./vendor/bin/pint

# Build production frontend assets
npm run build

# Run the queue worker locally, when needed
php artisan queue:work
```

On Windows, use `vendor\\bin\\pint` if the Unix-style Pint path is not available.

## Project layout

| Path | Purpose |
| --- | --- |
| `app/Http/Controllers` | HTTP controllers for inventory workflows |
| `app/Models` | Eloquent models |
| `app/Services` | Application service classes |
| `database/migrations` | Database schema changes |
| `resources/views` | Blade views |
| `resources/js` | Frontend JavaScript entry points |
| `routes/web.php` | Browser routes, primarily under `/app` |
| `tests` | Unit and feature tests |

## Contributing

Read [AGENTS.md](AGENTS.md) before making changes. Keep migrations additive, cover behavior changes with tests where practical, run Pint for changed PHP code, and do not commit `.env` files or generated runtime data.

## License

This project is distributed under the MIT license unless your organization has specified otherwise.
