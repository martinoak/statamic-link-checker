<?php

namespace Martinoak\LinkChecker;

use Martinoak\LinkChecker\Data\FoundLink;
use Statamic\Facades\Entry;

/**
 * Orchestrates the entire run: extraction → resolve + dedup → HTTP check →
 * report storage. Shared core for Artisan command, CP button, and auto-scan.
 */
class Scanner
{
    public function __construct(
        private LinkExtractor $extractor,
        private LinkResolver $resolver,
        private LinkChecker $checker,
        private ResultStore $store,
    ) {}

    /**
     * Full scan. Before storage, calculates "newly broken" links relative to
     * previous report (for notifications).
     *
     * @param  array<int, string>|null  $collections
     * @param  array<int, string>|null  $sites
     * @return array  stored report (incl. new_errors key)
     */
    public function scan(?array $collections = null, ?array $sites = null, ?callable $onEntry = null, ?callable $onChecked = null): array
    {
        $previousErrorKeys = $this->store->errorKeys();

        $occurrences = $this->extractor->extract($collections, $sites, $onEntry);

        $links = $this->group($occurrences);

        $this->checker->check($links, $onChecked);

        $report = $this->buildReport($links);
        $report['new_errors'] = $this->newErrors($report, $previousErrorKeys);

        $this->store->put($report);

        return $report;
    }

    /**
     * Targeted scan of one entry (auto-scan on publish): checks only
     * links in the given entry and merges them into the latest report.
     */
    public function rescanEntry(string $entryId): ?array
    {
        $entry = Entry::find($entryId);

        if (! $entry) {
            return null;
        }

        $links = $this->group($this->extractor->extractOne($entry));
        $this->checker->check($links);

        $report = $this->store->get() ?? ['scanned_at' => time(), 'summary' => [], 'links' => []];

        // Remove previous occurrences of this entry.
        $byKey = [];
        foreach ($report['links'] ?? [] as $link) {
            $link['occurrences'] = array_values(array_filter(
                $link['occurrences'] ?? [],
                fn ($o) => ($o['entry_id'] ?? null) !== $entryId
            ));
            $byKey[$link['type'].'|'.$link['url']] = $link;
        }

        // Merge freshly verified links from this entry (overwrite state and status).
        foreach ($links as $link) {
            $arr = $link->toArray();
            $key = $arr['type'].'|'.$arr['url'];

            if (isset($byKey[$key])) {
                $arr['occurrences'] = array_merge($byKey[$key]['occurrences'], $arr['occurrences']);
            }

            $byKey[$key] = $arr;
        }

        // Discard links with no occurrences.
        $merged = array_values(array_filter($byKey, fn ($l) => ! empty($l['occurrences'])));

        $report['links'] = $this->sortSerialized($merged);
        $report['summary'] = $this->summarize($merged);
        $report['scanned_at'] = time();

        $this->store->put($report, appendHistory: false);

        return $report;
    }

    public function countEntries(?array $collections = null): int
    {
        return $this->extractor->countEntries($collections);
    }

    private function occurrenceSite(array $occurrence): ?string
    {
        return $occurrence['site'] ?? null;
    }

    /**
     * @param  array<int, array>  $occurrences
     * @return array<int, FoundLink>
     */
    private function group(array $occurrences): array
    {
        /** @var array<string, FoundLink> $links */
        $links = [];

        foreach ($occurrences as $occurrence) {
            $resolved = $this->resolver->resolve($occurrence['raw'], $this->occurrenceSite($occurrence));
            $url = $resolved['url'] ?? $occurrence['raw'];
            $key = $resolved['type'].'|'.$url;

            if (! isset($links[$key])) {
                $links[$key] = new FoundLink(url: $url, type: $resolved['type']);
            }

            $links[$key]->addOccurrence([
                'entry_id' => $occurrence['entry_id'] ?? null,
                'collection' => $occurrence['collection'] ?? null,
                'title' => $occurrence['title'] ?? null,
                'field' => $occurrence['field'] ?? null,
                'site' => $occurrence['site'] ?? null,
                'raw' => $occurrence['raw'],
                'edit_url' => $occurrence['edit_url'] ?? null,
            ]);
        }

        return array_values($links);
    }

    /**
     * @param  array<int, FoundLink>  $links
     */
    private function buildReport(array $links): array
    {
        $serialized = array_map(fn (FoundLink $l) => $l->toArray(), $links);

        return [
            'scanned_at' => time(),
            'summary' => $this->summarize($serialized),
            'links' => $this->sortSerialized($serialized),
        ];
    }

    /** @param array<int, array> $serialized */
    private function summarize(array $serialized): array
    {
        $summary = [
            'total' => count($serialized),
            'ok' => 0, 'error' => 0, 'redirect' => 0, 'skipped' => 0, 'unresolved' => 0,
            'occurrences' => 0,
        ];

        foreach ($serialized as $link) {
            $summary[$link['state']] = ($summary[$link['state']] ?? 0) + 1;
            $summary['occurrences'] += count($link['occurrences'] ?? []);
        }

        return $summary;
    }

    /** @param array<int, array> $serialized */
    private function sortSerialized(array $serialized): array
    {
        usort($serialized, fn ($a, $b) => $this->stateWeight($b['state']) <=> $this->stateWeight($a['state']));

        return $serialized;
    }

    /**
     * Links that are newly in error state versus previous run.
     *
     * @param  array<int, string>  $previousErrorKeys
     * @return array<int, array>
     */
    private function newErrors(array $report, array $previousErrorKeys): array
    {
        $previous = array_flip($previousErrorKeys);
        $ignore = $this->store->ignore();
        $new = [];

        foreach ($report['links'] as $link) {
            if (! in_array($link['state'], [FoundLink::STATE_ERROR, FoundLink::STATE_UNRESOLVED], true)) {
                continue;
            }

            if ($ignore->has($link['url'])) {
                continue;
            }

            if (! isset($previous[$link['type'].'|'.$link['url']])) {
                $new[] = [
                    'url' => $link['url'],
                    'type' => $link['type'],
                    'state' => $link['state'],
                    'status' => $link['status'] ?? null,
                    'error' => $link['error'] ?? null,
                ];
            }
        }

        return $new;
    }

    private function stateWeight(string $state): int
    {
        return match ($state) {
            FoundLink::STATE_ERROR => 4,
            FoundLink::STATE_UNRESOLVED => 3,
            FoundLink::STATE_REDIRECT => 2,
            FoundLink::STATE_OK => 1,
            default => 0,
        };
    }
}
