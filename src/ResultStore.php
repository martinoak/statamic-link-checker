<?php

namespace Martinoak\LinkChecker;

use Illuminate\Contracts\Cache\Repository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Martinoak\LinkChecker\Data\FoundLink;

/**
 * Report persistence, run history and scan status; data preparation for CP table
 * (filtering + server-side pagination + ignore-list).
 */
class ResultStore
{
    /** Link category for filter internal / external / unresolved. */
    public const CATEGORIES = ['internal', 'external', 'unresolved'];

    public function __construct(private array $config = []) {}

    /**
     * Stores report. By default appends to history; when merging
     * (auto-scan of one entry) history is preserved unchanged.
     */
    public function put(array $report, bool $appendHistory = true): void
    {
        $existing = $this->get();
        $history = $existing['history'] ?? [];

        if ($appendHistory) {
            $history[] = ['scanned_at' => $report['scanned_at'] ?? time(), 'summary' => $report['summary'] ?? []];
            $history = array_slice($history, -max(1, (int) ($this->config['history_limit'] ?? 30)));
        }

        $report['history'] = $history;

        $ttl = $this->config['results_ttl'] ?? null;

        $ttl === null
            ? $this->cache()->forever($this->key(), $report)
            : $this->cache()->put($this->key(), $report, (int) $ttl);
    }

    public function get(): ?array
    {
        return $this->cache()->get($this->key());
    }

    public function latest(): ?array
    {
        return $this->get();
    }

    /** @return array<int, array{scanned_at: int, summary: array}> */
    public function history(): array
    {
        return $this->get()['history'] ?? [];
    }

    public function forget(): void
    {
        $this->cache()->forget($this->key());
        $this->cache()->forget($this->statusKey());
    }

    /** Set of keys (type|url) of broken links (excluding ignored) – for diff. */
    public function errorKeys(?array $report = null): array
    {
        $report ??= $this->get();
        $ignore = $this->ignore();
        $keys = [];

        foreach ($report['links'] ?? [] as $link) {
            if (in_array($link['state'], [FoundLink::STATE_ERROR, FoundLink::STATE_UNRESOLVED], true)
                && ! $ignore->has($link['url'])) {
                $keys[$link['type'].'|'.$link['url']] = true;
            }
        }

        return array_keys($keys);
    }

    /** Scan run status (for "scan in progress" banner). */
    public function status(): array
    {
        return $this->cache()->get($this->statusKey(), ['running' => false, 'started_at' => null]);
    }

    public function setRunning(bool $running): void
    {
        $running
            ? $this->cache()->forever($this->statusKey(), ['running' => true, 'started_at' => time()])
            : $this->cache()->forget($this->statusKey());
    }

    public function ignore(): IgnoreStore
    {
        return new IgnoreStore(
            $this->config['ignore_file'] ?? storage_path('statamic-link-checker/ignored.json')
        );
    }

    /**
     * Data for Blade view utility: report, summary, filtered and paginated data.
     */
    public function forView(Request $request): array
    {
        $report = $this->get();
        $ignore = $this->ignore();
        $perPage = max(1, (int) ($this->config['per_page'] ?? 25));

        $filter = $request->query('filter', $this->config['default_filter'] ?? 'errors');
        $filter = in_array($filter, ['errors', 'all', 'ignored'], true) ? $filter : 'errors';

        $types = array_values(array_intersect(
            self::CATEGORIES,
            array_filter(explode(',', (string) $request->query('types', '')))
        ));

        $base = [
            'report_exists' => (bool) $report,
            'scanned_at' => $report['scanned_at'] ?? null,
            'summary' => $report['summary'] ?? null,
            'running' => $this->status()['running'] ?? false,
            'filter' => $filter,
            'types' => $types,
            'type_counts' => array_fill_keys(self::CATEGORIES, 0),
            'ignored_count' => 0,
            'per_page' => $perPage,
            'scan_url' => cp_route('link-checker.scan'),
            'ignore_url' => cp_route('link-checker.ignore'),
            'unignore_url' => cp_route('link-checker.unignore'),
            'base_url' => $request->url(),
        ];

        if (! $report) {
            return $base + ['links' => [], 'current_page' => 1, 'total' => 0, 'last_page' => 1];
        }

        $allLinks = $report['links'] ?? [];
        $ignoredCount = count(array_filter($allLinks, fn ($l) => $ignore->has($l['url'])));

        if ($filter === 'ignored') {
            $links = array_values(array_filter($allLinks, fn ($l) => $ignore->has($l['url'])));
        } else {
            // exclude ignored
            $links = array_values(array_filter($allLinks, fn ($l) => ! $ignore->has($l['url'])));

            if ($filter === 'errors') {
                $links = array_values(array_filter($links, fn ($l) => in_array(
                    $l['state'],
                    [FoundLink::STATE_ERROR, FoundLink::STATE_UNRESOLVED],
                    true
                )));
            }
        }

        // counts per category (above filtered list, before type filter)
        $counts = array_fill_keys(self::CATEGORIES, 0);
        foreach ($links as $link) {
            $counts[$this->category($link)]++;
        }

        if (! empty($types) && $filter !== 'ignored') {
            $links = array_values(array_filter($links, fn ($l) => in_array($this->category($l), $types, true)));
        }

        $total = count($links);
        $lastPage = max(1, (int) ceil($total / $perPage));
        $page = min($lastPage, max(1, (int) $request->query('page', 1)));
        $slice = array_slice($links, ($page - 1) * $perPage, $perPage);

        return array_merge($base, [
            'type_counts' => $counts,
            'ignored_count' => $ignoredCount,
            'links' => $slice,
            'current_page' => $page,
            'total' => $total,
            'last_page' => $lastPage,
        ]);
    }

    private function category(array $link): string
    {
        if (($link['state'] ?? null) === FoundLink::STATE_UNRESOLVED || ($link['type'] ?? null) === 'unresolved') {
            return 'unresolved';
        }

        return ($link['type'] ?? null) === 'external' ? 'external' : 'internal';
    }

    private function cache(): Repository
    {
        $store = $this->config['cache_store'] ?? null;

        return $store ? Cache::store($store) : Cache::store();
    }

    private function key(): string
    {
        return $this->config['cache_key'] ?? 'statamic-link-checker.report';
    }

    private function statusKey(): string
    {
        return $this->key().'.status';
    }
}
