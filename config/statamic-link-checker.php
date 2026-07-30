<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Scan Scope
    |--------------------------------------------------------------------------
    |
    | Collection handles to scan. `null` = all collections.
    | Example: ['pages', 'news'].
    |
    */
    'collections' => null,

    /*
    |--------------------------------------------------------------------------
    | Sites (Multisite)
    |--------------------------------------------------------------------------
    |
    | Site handles to scan. `null` = all sites.
    | Relative and statamic:// links are resolved in the context of their
    | origin site (by absolute URL).
    |
    */
    'sites' => null,

    /*
    |--------------------------------------------------------------------------
    | Link Types to Check
    |--------------------------------------------------------------------------
    |
    | Whether to check external (different domain) and internal (own domain /
    | relative) links. Unresolved `statamic://entry::…` references are always
    | reported as broken.
    |
    */
    'check_external' => true,
    'check_internal' => true,

    /*
    |--------------------------------------------------------------------------
    | Base URL for Relative Links
    |--------------------------------------------------------------------------
    |
    | Override the base URL for relative links across all sites. Leave `null`
    | to auto-derive the base from each site's absolute URL (recommended for multisite).
    |
    */
    'base_url' => env('LINK_CHECKER_BASE_URL'),

    /*
    |--------------------------------------------------------------------------
    | HTTP Client
    |--------------------------------------------------------------------------
    */
    'timeout' => 10,          // seconds per request
    'concurrency' => 10,      // concurrent requests (Guzzle pool)
    'follow_redirects' => true,
    'max_redirects' => 5,
    'verify_ssl' => true,
    'user_agent' => 'Statamic-LinkChecker/1.0 (+https://statamic.com)',

    /*
    |--------------------------------------------------------------------------
    | Error Classification
    |--------------------------------------------------------------------------
    |
    | First digit of HTTP status codes to treat as errors. Default [4, 5] = 4XX
    | and 5XX. Request failure (timeout, DNS) and unresolved internal references
    | are always errors.
    |
    */
    'error_statuses' => [4, 5],

    /*
    |--------------------------------------------------------------------------
    | Excluded Links
    |--------------------------------------------------------------------------
    |
    | List of regular expressions (without delimiters). URLs matching any of
    | them are skipped. Example: '^https://localhost', 'example\.com'.
    |
    */
    'exclude_patterns' => [
        // '^mailto:',
        // '^tel:',
    ],

    /*
    |--------------------------------------------------------------------------
    | Skip Schemes
    |--------------------------------------------------------------------------
    |
    | Links with these schemes are not checked via HTTP (only recorded).
    |
    */
    'skip_schemes' => ['mailto', 'tel', 'javascript', 'data'],

    /*
    |--------------------------------------------------------------------------
    | Result Persistence
    |--------------------------------------------------------------------------
    |
    | Where to store the latest report. `cache_store` = null uses the app's
    | default cache store. `results_ttl` is in seconds (null = forever).
    |
    */
    'cache_store' => null,
    'cache_key' => 'statamic-link-checker.report',
    'results_ttl' => null,

    /*
    |--------------------------------------------------------------------------
    | Run History
    |--------------------------------------------------------------------------
    |
    | Number of past runs (summary + timestamp) to retain for trend analysis.
    |
    */
    'history_limit' => 30,

    /*
    |--------------------------------------------------------------------------
    | Deep Validation
    |--------------------------------------------------------------------------
    |
    | check_anchors – for links with #fragment, verify the anchor exists on page.
    | check_assets  – verify file existence for statamic://asset:: references.
    |
    */
    'check_anchors' => true,
    'check_assets' => true,

    /*
    |--------------------------------------------------------------------------
    | Auto-scan on Publish
    |--------------------------------------------------------------------------
    |
    | When a published entry is saved, check only its links and merge the
    | results into the existing report.
    |
    */
    'auto_scan' => true,

    /*
    |--------------------------------------------------------------------------
    | Ignored Links
    |--------------------------------------------------------------------------
    |
    | Path to JSON file storing links ignored from the CP (false positives).
    | Ignored links are hidden from the list and excluded from notifications.
    |
    */
    'ignore_file' => storage_path('statamic-link-checker/ignored.json'),

    /*
    |--------------------------------------------------------------------------
    | Scheduled Checks
    |--------------------------------------------------------------------------
    |
    | enabled   – enable scheduled run (requires running scheduler / cron).
    | frequency – hourly | daily | weekly (ignored if cron is set).
    | cron      – custom cron expression (e.g. '0 6 * * 1'); takes precedence.
    |
    */
    'schedule' => [
        'enabled' => false,
        'frequency' => 'daily',
        'cron' => null,
    ],

    /*
    |--------------------------------------------------------------------------
    | Notifications
    |--------------------------------------------------------------------------
    |
    | enabled – send email alerts after scheduled runs.
    | mail    – array of recipient addresses.
    | on      – 'new' (newly broken only) | 'all' (all broken links).
    |
    | Requires MAIL_FROM_ADDRESS / MAIL_FROM_NAME to be set.
    |
    */
    'notifications' => [
        'enabled' => false,
        'mail' => [],
        'on' => 'new',
    ],

    /*
    |--------------------------------------------------------------------------
    | CP Display
    |--------------------------------------------------------------------------
    */
    'per_page' => 25,
    'default_filter' => 'errors', // 'errors' | 'all'

];
