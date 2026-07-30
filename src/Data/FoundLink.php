<?php

namespace Martinoak\LinkChecker\Data;

use Illuminate\Contracts\Support\Arrayable;

/**
 * One unique link found in content, together with all occurrences
 * and HTTP check results.
 */
class FoundLink implements Arrayable
{
    public const STATE_OK = 'ok';
    public const STATE_ERROR = 'error';
    public const STATE_REDIRECT = 'redirect';
    public const STATE_SKIPPED = 'skipped';
    public const STATE_UNRESOLVED = 'unresolved';

    /** @var array<int, array{entry_id: ?string, collection: ?string, title: ?string, field: ?string, raw: string, edit_url: ?string}> */
    public array $occurrences = [];

    public function __construct(
        public string $url,
        public string $type,
        public ?int $status = null,
        public string $state = self::STATE_SKIPPED,
        public ?string $error = null,
        public ?string $finalUrl = null,
    ) {}

    public function addOccurrence(array $occurrence): void
    {
        $this->occurrences[] = $occurrence;
    }

    public function isError(): bool
    {
        return $this->state === self::STATE_ERROR || $this->state === self::STATE_UNRESOLVED;
    }

    public function toArray(): array
    {
        return [
            'url' => $this->url,
            'type' => $this->type,
            'status' => $this->status,
            'state' => $this->state,
            'error' => $this->error,
            'final_url' => $this->finalUrl,
            'occurrences' => $this->occurrences,
        ];
    }

    public static function fromArray(array $data): self
    {
        $link = new self(
            url: $data['url'],
            type: $data['type'],
            status: $data['status'] ?? null,
            state: $data['state'] ?? self::STATE_SKIPPED,
            error: $data['error'] ?? null,
            finalUrl: $data['final_url'] ?? null,
        );

        $link->occurrences = $data['occurrences'] ?? [];

        return $link;
    }
}
