# Job Collector (جاب‌کالکتور)

A modern Laravel + Livewire + Filament application for aggregating, crawling, and browsing Iranian tech job postings from multiple job boards (E-Estekhdam, JobVision, Jobinja, and custom sources) in a single searchable interface.

---

## Features

### 🌟 Public Job Board (`/`)
- **Modern Persian RTL Interface** — Designed for Persian typography with [Vazirmatn](https://fonts.google.com/specimen/Vazirmatn) font and responsive layouts.
- **Dark / Light Mode** — Instant theme toggle with localStorage persistence and system theme detection (`prefers-color-scheme`).
- **Livewire Real-Time Search & Filters** — Debounced instant search across job titles, company names, and skills.
- **Facet Filtering & Sorting** — Filter by platform/source, employment contract type, location, and sort by newest/oldest.
- **shadcn-Inspired Blade Components (`<x-ui.*>`)** — Reusable, accessible UI components (buttons, badges, cards, inputs, selects, pagination, and skeleton loading states).
- **Smooth Pagination** — Custom Livewire pagination with intelligent URL query sync.

### ⚙️ Crawler Engine & Background Queue
- **Flexible Source Configuration** — Configure sources with HTTP method (`GET`/`POST`), base URL, headers, query parameters, body payload templates, and pagination rules.
- **Multi-Type Crawling** — Supports both JSON APIs (`data_get` dot notation) and server-rendered HTML pages (XPath/DOM selectors).
- **One-Click Manual & Background Crawls** — Run crawls with custom parameters (keywords, cities, `max_pages`) directly from the admin panel.
- **Smart Deduplication** — Bulk-upserts by `(url, platform_id)` and terminates crawls early when pages yield no new jobs.
- **Pre-Configured Sources** — Out-of-the-box seeders for **E-Estekhdam**, **JobVision**, and **Jobinja**.

### 🛠️ Filament Admin Dashboard (`/admin`)
- **Source Management** — Create, edit, test, and run job board crawler sources.
- **Job Postings Manager** — Browse, search, filter, and batch delete collected job records.
- **Live Task Ops** — Monitor running background crawl jobs in real-time (`Running Tasks`) with single or bulk cancellation.
- **Task Logs** — Complete execution history (`Task Logs`) including duration, saved records count, pages traversed, and error traces.

---

## Tech Stack

- **Backend:** PHP 8.3+, Laravel 13, Livewire 3
- **Admin Panel:** Filament 3.2
- **Frontend & Styling:** Tailwind CSS v4, Blade Components, Vazirmatn Font
- **Database:** SQLite (default) / MySQL / PostgreSQL
- **Queue:** Database queue worker (asynchronous background crawls)
- **Containerization:** Docker & Docker Compose

---

## Quickstart

### Option 1: Using Docker (Recommended)

Run the web server and background queue worker with a single command:

```bash
docker compose up -d --build
```

The application will be accessible at:
- **Public Job Board:** [http://localhost:8000](http://localhost:8000)
- **Admin Panel:** [http://localhost:8000/admin](http://localhost:8000/admin)

To create an admin user inside the container:

```bash
docker compose exec app php artisan make:filament-user
```

---

### Option 2: Local Development

#### Prerequisites
- PHP `^8.3` (with `pdo_sqlite`, `dom`, `mbstring`, `curl`)
- Composer
- Node.js & npm

#### Setup Steps

1. **Clone and install dependencies:**
   ```bash
   git clone https://github.com/block-p/job-collector.git
   cd job-collector
   composer setup
   ```
   *(The `composer setup` script copies `.env.example` to `.env`, generates the app key, runs migrations with default seeders, and compiles frontend assets).*

2. **Seed default job sources (if not already seeded):**
   ```bash
   php artisan db:seed --class=PlatformPostingSeeder
   ```

3. **Create an admin user:**
   ```bash
   php artisan make:filament-user
   ```

4. **Start the local server and background queue worker:**
   ```bash
   # Terminal 1: Web server
   php artisan serve

   # Terminal 2: Queue worker (required for crawls to execute)
   php artisan queue:work --sleep=3 --tries=1 --timeout=3600
   ```

5. **(Optional) Run Vite in development mode:**
   ```bash
   npm run dev
   ```

---

## Crawler Architecture

The core crawl engine is handled by `App\Services\JobCrawlerService::crawl(PlatformPosting $source, array $options = [])`:

1. **Request Formulation** — Merges default source configuration with run-time overrides (keywords, locations, max pages).
2. **Execution & Retry** — Fetches pages with automatic HTTP retries (3 attempts, 25s timeout) and inter-request throttle delays (`delay_ms`).
3. **Data Normalization** — Standardizes relative links into absolute URLs, normalizes skill arrays/tags, flattens contract types, and structures locations and salaries.
4. **Deduplication & Storage** — Upserts records matching unique composite keys `(url, platform_id)`.
5. **Observability** — Writes live status updates to `crawl_logs` and tracks queue jobs in `JobQueue`.

---

## Pre-Configured Sources

| Source | Type | Description |
|---|---|---|
| **E-Estekhdam** | JSON API | Crawls via search API with JSON body payloads and pagination query params |
| **JobVision** | JSON API | Queries JobVision candidate API with customizable category and location filters |
| **Jobinja** | HTML Scraping | Parses server-rendered HTML job list cards and extracts attributes |

---

## UI Components (`resources/views/components/ui/`)

The public interface utilizes shadcn-inspired Blade components:

| Component | Tag | Description |
|---|---|---|
| Card | `<x-ui.card>`, `<x-ui.card-header>`, etc. | Container card with border and background tokens |
| Button | `<x-ui.button variant="..." size="...">` | Buttons and anchor buttons with variants (`default`, `outline`, `ghost`, etc.) |
| Badge | `<x-ui.badge variant="...">` | Skill and category tags (`default`, `secondary`, `outline`) |
| Input | `<x-ui.input type="..." />` | Form inputs with focus rings |
| Select | `<x-ui.select>` | Styled dropdown selector |
| Pagination | `<x-ui.pagination :paginator="$jobs" />` | Accessible Livewire pagination with RTL chevron orientation |
| Skeleton | `<x-ui.skeleton class="..." />` | Loading placeholder skeletons during Livewire transitions |

---

## Testing

Run the automated test suite with:

```bash
composer test
# or
./vendor/bin/phpunit
```

---

## License

This project is open-sourced software licensed under the [MIT license](LICENSE).
