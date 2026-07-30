<?php

namespace Martinoak\LinkChecker;

use Statamic\Facades\Collection;
use Statamic\Facades\Entry;

/**
 * Traverses entries and extracts links from any fields (Bard, Markdown, Link fieldtype, plain
 * text field) with context of where they were found.
 */
class LinkExtractor
{
    /** Field keys where value is a direct link (Bard nodes, Link fieldtype). */
    private const LINK_KEYS = ['href', 'src', 'url', 'link'];

    public function __construct(private array $config = []) {}

    /**
     * @param  array<int, string>|null  $collections  collection handles (null = per config / all)
     * @param  array<int, string>|null  $sites  site handles (null = per config / all)
     * @param  callable|null  $onEntry  callback invoked after each entry (for progress)
     * @return array<int, array{raw: string, entry_id: ?string, collection: ?string, title: ?string, field: ?string, site: ?string, edit_url: ?string}>
     */
    public function extract(?array $collections = null, ?array $sites = null, ?callable $onEntry = null): array
    {
        $handles = $this->resolveCollectionHandles($collections);
        $allowedSites = $this->resolveSiteHandles($sites);
        $occurrences = [];

        foreach ($handles as $handle) {
            Entry::query()
                ->where('collection', $handle)
                ->chunk(100, function ($entries) use (&$occurrences, $allowedSites, $onEntry) {
                    foreach ($entries as $entry) {
                        if ($allowedSites === null || in_array($this->siteHandle($entry), $allowedSites, true)) {
                            $this->extractFromEntry($entry, $occurrences);
                        }

                        if ($onEntry) {
                            $onEntry($entry);
                        }
                    }
                });
        }

        return $occurrences;
    }

    /**
     * Link extraction from one entry (auto-scan on publish).
     *
     * @return array<int, array>
     */
    public function extractOne($entry): array
    {
        $occurrences = [];
        $this->extractFromEntry($entry, $occurrences);

        return $occurrences;
    }

    /** Total number of entries to be scanned (for progress bar). */
    public function countEntries(?array $collections = null): int
    {
        return collect($this->resolveCollectionHandles($collections))
            ->sum(fn ($handle) => Entry::query()->where('collection', $handle)->count());
    }

    private function resolveCollectionHandles(?array $collections): array
    {
        $collections = $collections ?? $this->config['collections'] ?? null;

        if (! empty($collections)) {
            return array_values((array) $collections);
        }

        return Collection::all()->map->handle()->all();
    }

    /** @return array<int, string>|null  null = all sites */
    private function resolveSiteHandles(?array $sites): ?array
    {
        $sites = $sites ?? $this->config['sites'] ?? null;

        return empty($sites) ? null : array_values((array) $sites);
    }

    private function siteHandle($entry): ?string
    {
        if (method_exists($entry, 'site')) {
            return $entry->site()?->handle();
        }

        return method_exists($entry, 'locale') ? $entry->locale() : null;
    }

    private function extractFromEntry($entry, array &$occurrences): void
    {
        $ctx = [
            'entry_id' => $entry->id(),
            'collection' => $entry->collectionHandle(),
            'title' => (string) ($entry->value('title') ?? $entry->id()),
            'site' => $this->siteHandle($entry),
            'edit_url' => method_exists($entry, 'editUrl') ? $entry->editUrl() : null,
        ];

        foreach ($entry->data()->all() as $field => $value) {
            $this->walk($value, (string) $field, $occurrences, $ctx);
        }
    }

    private function walk(mixed $value, string $field, array &$occurrences, array $ctx): void
    {
        if (is_string($value)) {
            foreach ($this->tokensFromString($value) as $raw) {
                $occurrences[] = $ctx + ['field' => $field, 'raw' => $raw];
            }

            return;
        }

        if (! is_array($value)) {
            return;
        }

        foreach ($value as $key => $child) {
            if (is_string($child) && in_array($key, self::LINK_KEYS, true) && $this->looksLikeLink($child)) {
                $occurrences[] = $ctx + ['field' => $field, 'raw' => trim($child)];

                continue;
            }

            $this->walk($child, $field, $occurrences, $ctx);
        }
    }

    /**
     * Extract all link tokens from any text.
     *
     * @return array<int, string>
     */
    private function tokensFromString(string $text): array
    {
        $text = trim($text);

        if ($text === '') {
            return [];
        }

        // Entire value is a Link fieldtype reference (entry::UUID, asset::…, @child).
        if (preg_match('/^(entry|asset|term)::[^\s]+$/i', $text) || $text === '@child') {
            return [$text];
        }

        $tokens = [];

        // statamic:// reference (Bard/Markdown)
        preg_match_all('/statamic:\/\/[a-z]+::[^\s"\'<>)\]]+/i', $text, $m);
        $tokens = array_merge($tokens, $m[0]);

        // HTML href / src
        preg_match_all('/(?:href|src)\s*=\s*["\']([^"\']+)["\']/i', $text, $m);
        $tokens = array_merge($tokens, $m[1]);

        // Markdown links [text](url) and images ![alt](url)
        preg_match_all('/\]\(\s*([^)\s]+)/', $text, $m);
        $tokens = array_merge($tokens, $m[1]);

        // mailto: / tel:
        preg_match_all('/\b(?:mailto|tel):[^\s"\'<>)\]]+/i', $text, $m);
        $tokens = array_merge($tokens, $m[0]);

        // Plain http(s) URLs
        preg_match_all('/https?:\/\/[^\s"\'<>)\]]+/i', $text, $m);
        $tokens = array_merge($tokens, $m[0]);

        $tokens = array_map(fn ($t) => $this->clean($t), $tokens);
        $tokens = array_filter($tokens, fn ($t) => $t !== '');

        return array_values(array_unique($tokens));
    }

    private function clean(string $token): string
    {
        $token = trim($token);

        // Osekat uvozovky a koncovou interpunkci u bare URL.
        $token = trim($token, '\'"');

        if (str_starts_with($token, 'http') || str_starts_with($token, '/')) {
            $token = rtrim($token, '.,;:');
        }

        return $token;
    }

    private function looksLikeLink(string $value): bool
    {
        $value = trim($value);

        return $value !== '' && (bool) preg_match(
            '/^(https?:|statamic:|mailto:|tel:|\/|#|entry::|asset::|term::|@child)/i',
            $value
        );
    }
}
