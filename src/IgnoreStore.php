<?php

namespace Martinoak\LinkChecker;

/**
 * Persistent list of ignored links (false positives), managed from CP.
 * Ignored links are hidden from the main list and excluded from notifications.
 */
class IgnoreStore
{
    public function __construct(private string $path) {}

    /** @return array<int, string> */
    public function all(): array
    {
        if (! is_file($this->path)) {
            return [];
        }

        $data = json_decode((string) file_get_contents($this->path), true);

        return is_array($data) ? array_values(array_filter($data, 'is_string')) : [];
    }

    public function has(string $url): bool
    {
        return in_array($url, $this->all(), true);
    }

    public function add(string $url): void
    {
        $url = trim($url);

        if ($url === '') {
            return;
        }

        $all = $this->all();

        if (! in_array($url, $all, true)) {
            $all[] = $url;
            $this->write($all);
        }
    }

    public function remove(string $url): void
    {
        $this->write(array_values(array_diff($this->all(), [trim($url)])));
    }

    public function count(): int
    {
        return count($this->all());
    }

    private function write(array $urls): void
    {
        $dir = dirname($this->path);

        if (! is_dir($dir)) {
            @mkdir($dir, 0775, true);
        }

        file_put_contents(
            $this->path,
            json_encode(array_values($urls), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)
        );
    }
}
