<?php

namespace Martinoak\LinkChecker\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Martinoak\LinkChecker\Scanner;

/**
 * Targeted link scan of one entry (auto-scan on publish). With
 * QUEUE_CONNECTION=sync runs inline on save.
 */
class ScanEntryJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public string $entryId) {}

    public function handle(Scanner $scanner): void
    {
        $scanner->rescanEntry($this->entryId);
    }
}
