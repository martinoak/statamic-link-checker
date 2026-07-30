<?php

namespace Martinoak\LinkChecker;

use Illuminate\Support\Facades\Notification;
use Martinoak\LinkChecker\Data\FoundLink;
use Martinoak\LinkChecker\Notifications\BrokenLinksReport;

/**
 * Determines and sends email notification about broken links per config.
 * Shared core for RunScanJob and Artisan command (--notify).
 */
class Notifier
{
    public function maybeSend(array $report): void
    {
        $config = (array) config('statamic-link-checker.notifications', []);

        if (! ($config['enabled'] ?? false)) {
            return;
        }

        $recipients = array_values(array_filter((array) ($config['mail'] ?? [])));

        if (empty($recipients)) {
            return;
        }

        $onlyNew = ($config['on'] ?? 'new') === 'new';

        $links = $onlyNew
            ? ($report['new_errors'] ?? [])
            : array_values(array_filter(
                $report['links'] ?? [],
                fn ($l) => in_array($l['state'], [FoundLink::STATE_ERROR, FoundLink::STATE_UNRESOLVED], true)
            ));

        if (empty($links)) {
            return;
        }

        Notification::route('mail', $recipients)->notify(
            new BrokenLinksReport($links, $report['summary'] ?? [], cp_route('link-checker.index'), $onlyNew)
        );
    }
}
