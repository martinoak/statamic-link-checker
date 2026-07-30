# Statamic Link Checker

Universal link availability checker for **Statamic v6**. Scans your content,
checks HTTP status of links, and displays a paginated table in the control panel
(**Tools → Link Checker**) with emphasis on errors (4XX/5XX).

## Features

- Traverses all entries and extracts links from any field type (Bard, Markdown,
  Link fieldtype, plain text fields).
- Distinguishes **external**, **internal/relative**, and `statamic://entry::…` / `entry::…`
  references (unresolved references are reported as broken).
- Concurrent HTTP checking (Guzzle pool, HEAD → GET fallback), redirect tracking.
- **Fragment anchor validation** — verifies that `#anchor` targets exist on the page.
- **Asset existence checking** — detects broken `statamic://asset::` references.
- **Run history** and **newly broken links detection** (diff against previous run).
- **Ignore-list** via the CP to suppress false positives.
- **Dashboard widget** showing broken link count.
- **Auto-scan on publish** and **scheduled runs with email notifications**.
- Background execution via **queue**.
- Results are cached — the table simply pages through the stored report.
- Fully configurable via `config/statamic-link-checker.php`.

## Installation

```bash
composer require martinoak/statamic-link-checker
php artisan vendor:publish --tag=statamic-link-checker-config
```

## Usage

- **CP:** Tools → Link Checker → *Run check* button.
  Filter by *Problems only / All* and toggle categories
  *Internal / External / Unresolved*.
- **CLI:** `php artisan link-checker:scan`
  (optionally `--collection=pages --collection=news`, `--site=en --site=cs`,
  `--queue` to queue the job, `--notify` to email a report when done).

## Automation

- **Scheduled run + notifications:** Set `schedule.enabled = true` in config
  (`frequency: hourly|daily|weekly` or custom `cron`) and `notifications.enabled = true`
  with recipients in `notifications.mail`. The addon registers `link-checker:scan --notify` automatically;
  just ensure Laravel scheduler is running (`* * * * * php artisan schedule:run`).
  Notifications require `MAIL_FROM_ADDRESS` / `MAIL_FROM_NAME` to be set.
  `notifications.on = new` sends only **newly** broken links; `all` sends every broken link.
- **Auto-scan on publish:** With `auto_scan = true`, whenever a published entry is saved,
  its links are checked and merged into the report (via `EntrySaved` listener).

## Background execution (queue)

Both full scan and auto-scan run through queue jobs. With the default `QUEUE_CONNECTION=sync`,
they execute inline. For true background processing:

```bash
php artisan queue:table && php artisan migrate   # database driver only
# .env: QUEUE_CONNECTION=database (or redis)
php artisan queue:work
```

## Ignore-list

Each link in the table has an *Ignore* button; ignored links are hidden from the main
list and excluded from notifications. The *Ignored* filter shows them and allows
removal. Data is stored in `storage/statamic-link-checker/ignored.json` (path: `ignore_file`).

## Dashboard widget

Add the widget handle to `config/statamic/cp.php` → `widgets`:

```php
['type' => 'broken_links', 'width' => 50],
```

## Multisite

The addon is site-aware. It scans entries across all sites; relative and `statamic://`
links are resolved in the context of their origin site (per absolute URL).
Limit the scope via config `sites` or the `--site` flag.

## Localization

The UI defaults to English, with Czech available (`lang/en`, `lang/cs`),
controlled by the CP's locale setting. Publish custom translations:

```bash
php artisan vendor:publish --tag=statamic-link-checker-translations
```

## Configuration

See `config/statamic-link-checker.php` — collection scope (`collections`), site scope
(`sites`), external/internal check flags, `base_url` (override base for relative links; `null` = per-site),
timeout, concurrency, redirect following, error status codes (`error_statuses`),
excluded patterns (`exclude_patterns`), `check_anchors`, `check_assets`, `auto_scan`,
`history_limit`, `ignore_file`, `schedule` (`enabled`/`frequency`/`cron`),
`notifications` (`enabled`/`mail`/`on`), cache store, and pagination.
