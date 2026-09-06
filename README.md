# Job Collector

A Laravel + Filament app for collecting job postings from multiple job boards into one searchable database.

Define a source once (API endpoint or server-rendered HTML page, request config, and a response mapping), then run crawls from the admin panel — in the background via the queue — and browse the normalized results.

## Features

- **Sources (Platforms)** — configure each job board with method (`GET`/`POST`), base URL + endpoint, headers, query params, body template, pagination rules, and a field mapping.
- **One-click background crawls** — the `Run` action on a source opens a form pre-filled from its saved params, accepts runtime overrides (filters, `max_pages`), and dispatches `FetchJobsFromApi` to the queue.
- **JSON API + HTML crawling** — `JobCrawlerService` handles JSON APIs (via a configurable `list_path` + field map) and server-rendered HTML lists (Jobinja-style `c-jobListView` markup).
- **Deduplication** — results are bulk-upserted on the unique key (`url`, `platform_id`); runs also stop early when a page yields no new URLs.
- **Job browser** — search/filter job postings by platform, contract, company, and location; bulk delete included.
- **Live ops views** — `Running Tasks` (reads the `jobs` table, polls every 2s, cancel single/all) and `Task Logs` (per-run `crawl_logs` with filters used, jobs/pages counts, duration, error; polls every 3s).

## Tech stack

- PHP `^8.3`, Laravel `^13`, Filament `^3.2`
- SQLite by default (`DB_CONNECTION=sqlite`), database queue / cache / session
- Vite + Tailwind (Filament assets)

## Requirements

- PHP `^8.3` with the usual Laravel extensions (`sqlite3`, `dom`, `mbstring`)
- Composer
- Node + npm (only for rebuilding frontend assets)

## Quickstart

```bash
composer setup
# equivalent to:
#   composer install
#   cp .env.example .env (if missing)
#   php artisan key:generate
#   php artisan migrate --force
#   npm install && npm run build

php artisan serve          # app at http://localhost:8000
php artisan queue:work     # required: executes the background crawls
```

Then open `http://localhost:8000/admin`, create an admin user if needed (`php artisan make:filament-user`), and add your first source.

> Crawls are queued (`QUEUE_CONNECTION=database`), so `php artisan queue:work` must be running or `Run` jobs will just sit in `Running Tasks` as `Queued`.

## Usage

### 1. Add a source (`/admin` → Sources)

| Field | What it is |
|---|---|
| `title` | Display name, e.g. `E-Estekhdam` |
| `method` | `GET` or `POST` |
| `url` | Site base URL, used to resolve relative job links |
| `endpoint` | API path or page path, e.g. `/search-api/search` |
| `headers` / `query_params` / `body_template` | Key-value defaults sent with every request |
| `pagination` | `{ type: query\|body, page_key, start_page, max_pages }` |
| `response_mapping` | `{ list_path, fields: { title, company, location, salary, url, contract, skills } }` |
| `delay_ms` | Pause between pages (default `1000`) |

`list_path` uses Laravel `data_get` dot notation (default `data`). Field values are also `data_get` paths into each item, e.g. `contract.0` or `skills.*.title`.

### 2. Run a crawl

On a source row → **Run**. The dialog is generated from that source's saved `query_params` / `body_template`, so per-run overrides (keyword, location, tags, `max_pages`) don't require editing the source. Dispatching shows a `Crawler started in background` notification.

### 3. Browse results (`/admin` → Job Postings)

Searchable/sortable table with platform + contract filters and a company search. Skills render as badges; `Delete All Records` / bulk delete are available for resets.

### 4. Monitor (`/admin` → Running Tasks / Task Logs)

- **Running Tasks**: queued vs. running jobs with their effective filters, cancellable individually or all at once (`queue:clear`).
- **Task Logs**: one row per crawl attempt — source, resolved filters, `success`/`failed`/`running`, jobs saved, pages visited, duration, and error message.

## How the crawler works

`App\Services\JobCrawlerService::crawl(PlatformPosting $source, array $options = [])`:

1. Merges saved `body_template`/`query_params` with per-run `$options['body']` / `$options['query_params']` (empty values ignored); JSON-array strings are decoded to real arrays.
2. Creates a `crawl_logs` row in `running` status capturing the effective filters.
3. Loops `start_page..max_pages`, injecting the page into the query string or body per `pagination.type`, with HTTP retry (3 ×, 25s timeout).
4. Parses the response as JSON (`list_path`) or HTML (DOM/XPath over the job-list markup).
5. Normalizes each item through the field map: fixes relative URLs against `url`, flattens array `contract` values, normalizes `skills` (arrays of strings/objects, JSON strings, or comma-separated strings) into clean string lists, and JSON-encodes any leftover arrays.
6. Breaks early on empty pages or pages with zero unseen URLs; otherwise bulk-upserts by (`url`, `platform_id`).
7. Updates the source's `last_status` / `last_error` / `last_crawled_at` and finalizes the crawl log with counts + `duration_ms`. Failures are recorded on both and logged.

## Project structure

```
app/
  Filament/Resources/
    PlatformPostingResource.php   # source CRUD + Run action
    JobPostingResource.php        # job browser + filters
    RunningTaskResource.php       # live view over `jobs` table
    TaskLogResource.php           # history view over `crawl_logs`
  Jobs/FetchJobsFromApi.php       # queued wrapper (1h timeout, 1 try)
  Services/JobCrawlerService.php  # crawl engine (JSON + HTML)
  Models/
    PlatformPosting.php  JobPosting.php  CrawlLog.php  JobQueue.php
database/migrations/
  *_create_platform_postings_table.php
  *_create_job_postings_table.php
  *_add_http_field_to_platform_postings.php
  *_create_crawl_logs_table.php
  *_add_skills_to_job_postings_table.php
```

## Useful commands

```bash
php artisan migrate          # apply migrations (SQLite file in database/)
php artisan queue:work       # process crawls
php artisan queue:clear      # drop all queued crawls
composer test                # config:clear + artisan test
```

## Configuration notes

- Defaults live in `.env.example`: SQLite database, `database` queue/cache/session, `log` mailer/broadcaster.
- Never commit `.env` or `database/*.sqlite` — both are git-ignored (`database/.gitignore` covers `*.sqlite*`).
- `composer.json` scripts: `setup` (install + key + migrate + build), `dev` (`artisan dev`), `test`.

## License

MIT — same as the Laravel skeleton this project is built on.
