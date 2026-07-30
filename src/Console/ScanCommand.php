<?php

namespace Martinoak\LinkChecker\Console;

use Illuminate\Console\Command;
use Martinoak\LinkChecker\Jobs\RunScanJob;
use Martinoak\LinkChecker\Scanner;

class ScanCommand extends Command
{
    protected $signature = 'link-checker:scan
        {--collection=* : Collection handles to scan (repeatable; default = all)}
        {--site=* : Site handles to scan (repeatable; default = all)}
        {--queue : Run scan in background via queue (no progress bar)}
        {--notify : Send email notification after completion (per config)}';

    protected $description = 'Scans content and checks link availability (internal, external, statamic://).';

    public function handle(Scanner $scanner): int
    {
        @set_time_limit(0);

        $collections = $this->option('collection') ?: null;
        $sites = $this->option('site') ?: null;

        if ($this->option('queue')) {
            RunScanJob::dispatch($collections, $sites, (bool) $this->option('notify'));
            $this->info(__('statamic-link-checker::messages.cmd_queued'));

            return self::SUCCESS;
        }

        $this->info(__('statamic-link-checker::messages.cmd_extracting'));
        $entryBar = $this->output->createProgressBar($scanner->countEntries($collections));
        $entryBar->start();

        $checking = false;
        $checked = 0;

        $report = $scanner->scan(
            $collections,
            $sites,
            onEntry: fn () => $entryBar->advance(),
            onChecked: function () use (&$checking, &$checked, $entryBar) {
                if (! $checking) {
                    $entryBar->finish();
                    $this->newLine(2);
                    $this->info(__('statamic-link-checker::messages.cmd_checking'));
                    $checking = true;
                }
                $checked++;
                if ($checked % 25 === 0) {
                    $this->line(__('statamic-link-checker::messages.cmd_checked', ['count' => $checked]));
                }
            },
        );

        if (! $checking) {
            $entryBar->finish();
        }

        $this->newLine(2);
        $this->summary($report['summary']);

        if ($this->option('notify')) {
            app(\Martinoak\LinkChecker\Notifier::class)->maybeSend($report);
        }

        return self::SUCCESS;
    }

    private function summary(array $summary): void
    {
        $this->table(
            [
                __('statamic-link-checker::messages.sum_total'),
                __('statamic-link-checker::messages.sum_ok'),
                __('statamic-link-checker::messages.sum_error'),
                __('statamic-link-checker::messages.sum_redirect'),
                __('statamic-link-checker::messages.sum_unresolved'),
                __('statamic-link-checker::messages.sum_skipped'),
                __('statamic-link-checker::messages.sum_occurrences'),
            ],
            [[
                $summary['total'] ?? 0,
                $summary['ok'] ?? 0,
                $summary['error'] ?? 0,
                $summary['redirect'] ?? 0,
                $summary['unresolved'] ?? 0,
                $summary['skipped'] ?? 0,
                $summary['occurrences'] ?? 0,
            ]],
        );

        $errors = ($summary['error'] ?? 0) + ($summary['unresolved'] ?? 0);

        $errors > 0
            ? $this->warn(__('statamic-link-checker::messages.cmd_found_problems', ['count' => $errors]))
            : $this->info(__('statamic-link-checker::messages.cmd_no_broken'));
    }
}
