<?php

namespace Martinoak\LinkChecker\Listeners;

use Martinoak\LinkChecker\Jobs\ScanEntryJob;
use Statamic\Events\EntrySaved;

/**
 * After a published entry is saved, schedules a targeted scan of its links.
 */
class ScanEntryLinks
{
    public function handle(EntrySaved $event): void
    {
        if (! config('statamic-link-checker.auto_scan', true)) {
            return;
        }

        $entry = $event->entry;

        // Only published entries; only when a previous report exists (has something to merge).
        if (! method_exists($entry, 'published') || ! $entry->published()) {
            return;
        }

        ScanEntryJob::dispatch($entry->id());
    }
}
