<?php

namespace Martinoak\LinkChecker\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Martinoak\LinkChecker\Notifier;
use Martinoak\LinkChecker\ResultStore;
use Martinoak\LinkChecker\Scanner;

/**
 * Full scan in the background. With QUEUE_CONNECTION=sync runs inline.
 */
class RunScanJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 3600;

    /**
     * @param  array<int, string>|null  $collections
     * @param  array<int, string>|null  $sites
     */
    public function __construct(
        public ?array $collections = null,
        public ?array $sites = null,
        public bool $notify = false,
    ) {}

    public function handle(Scanner $scanner, ResultStore $store, Notifier $notifier): void
    {
        $store->setRunning(true);

        try {
            $report = $scanner->scan($this->collections, $this->sites);
        } finally {
            $store->setRunning(false);
        }

        if ($this->notify) {
            $notifier->maybeSend($report);
        }
    }
}
