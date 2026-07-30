<?php

namespace Martinoak\LinkChecker;

use Statamic\Facades\Data;
use Statamic\Facades\Site;

/**
 * Converts raw link token to absolute URL verifiable via HTTP
 * request, and classifies its type. Supports multisite – relative and
 * statamic:// links are resolved in the context of their origin site.
 */
class LinkResolver
{
    public function __construct(private array $config = []) {}

    /**
     * @param  string  $raw  raw token
     * @param  string|null  $site  site handle where the link originates
     * @return array{url: ?string, type: string}
     */
    public function resolve(string $raw, ?string $site = null): array
    {
        $raw = trim($raw);

        if ($raw === '' || str_starts_with($raw, '#')) {
            return ['url' => null, 'type' => 'anchor'];
        }

        $scheme = $this->scheme($raw);

        if ($scheme === 'mailto' || $scheme === 'tel') {
            return ['url' => $raw, 'type' => $scheme];
        }

        if ($scheme !== null && in_array($scheme, (array) ($this->config['skip_schemes'] ?? []), true)) {
            return ['url' => $raw, 'type' => 'skipped'];
        }

        // Statamic reference: statamic://entry::UUID nebo bare entry::/asset::/term::
        $ref = null;

        if (stripos($raw, 'statamic://') === 0) {
            $ref = substr($raw, strlen('statamic://'));
        } elseif (preg_match('/^(entry|asset|term)::/i', $raw)) {
            $ref = $raw;
        }

        if ($ref !== null) {
            return $this->resolveStatamicRef($ref, $raw, $site);
        }

        if ($raw === '@child') {
            return ['url' => null, 'type' => 'skipped'];
        }

        // Absolute http(s) URL
        if (preg_match('#^https?://#i', $raw)) {
            return ['url' => $raw, 'type' => $this->isExternal($raw, $site) ? 'external' : 'internal'];
        }

        // Protocol-relative //host/...
        if (str_starts_with($raw, '//')) {
            $url = 'https:'.$raw;

            return ['url' => $url, 'type' => $this->isExternal($url, $site) ? 'external' : 'internal'];
        }

        // Relative path
        if (str_starts_with($raw, '/')) {
            return ['url' => $this->absolute($raw, $site), 'type' => 'internal'];
        }

        // Other schemes (ftp, ...) are left as unverified
        if ($scheme !== null) {
            return ['url' => $raw, 'type' => 'skipped'];
        }

        return ['url' => $this->absolute('/'.$raw, $site), 'type' => 'internal'];
    }

    /**
     * @return array{url: ?string, type: string}
     */
    private function resolveStatamicRef(string $ref, string $raw, ?string $site): array
    {
        $kind = strtolower(explode('::', $ref, 2)[0]);

        try {
            $data = Data::find($ref);

            // Localize to site context if type allows (entry, term).
            if ($data && $site && method_exists($data, 'in')) {
                $data = $data->in($site) ?? $data;
            }

            // Asset existence: broken reference even if URL formally exists.
            if ($kind === 'asset'
                && ($this->config['check_assets'] ?? true)
                && $data && method_exists($data, 'exists') && ! $data->exists()) {
                return ['url' => $raw, 'type' => 'unresolved'];
            }

            $url = $data?->url();
        } catch (\Throwable) {
            $url = null;
        }

        if (! $url) {
            return ['url' => $raw, 'type' => 'unresolved'];
        }

        return ['url' => $this->absolute($url, $site), 'type' => $kind];
    }

    private function scheme(string $value): ?string
    {
        if (preg_match('/^([a-z][a-z0-9+.-]*):/i', $value, $m)) {
            return strtolower($m[1]);
        }

        return null;
    }

    private function absolute(string $url, ?string $site): string
    {
        if (preg_match('#^https?://#i', $url)) {
            return $url;
        }

        return rtrim($this->baseUrl($site), '/').'/'.ltrim($url, '/');
    }

    private function isExternal(string $url, ?string $site): bool
    {
        $host = parse_url($url, PHP_URL_HOST);
        $baseHost = parse_url($this->baseUrl($site), PHP_URL_HOST);

        if (! $host || ! $baseHost) {
            return true;
        }

        return strcasecmp($host, $baseHost) !== 0;
    }

    /** Base URL for a site: explicit config > site absoluteUrl > APP_URL. */
    private function baseUrl(?string $site): string
    {
        if (! empty($this->config['base_url'])) {
            return (string) $this->config['base_url'];
        }

        if ($site) {
            try {
                if ($resolved = Site::get($site)?->absoluteUrl()) {
                    return $resolved;
                }
            } catch (\Throwable) {
                // fallthrough
            }
        }

        try {
            return Site::current()->absoluteUrl();
        } catch (\Throwable) {
            return (string) config('app.url');
        }
    }
}
