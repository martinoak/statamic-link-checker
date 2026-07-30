<?php

namespace Martinoak\LinkChecker\Widgets;

use Martinoak\LinkChecker\ResultStore;
use Statamic\Widgets\Widget;

/**
 * Dashboard widget: count of broken links + link to tool.
 * html() returns Blade view; dashboard compiles it as Vue template
 * (DynamicHtmlRenderer), so <ui-*> components work.
 */
class BrokenLinks extends Widget
{
    public function html()
    {
        $store = app(ResultStore::class);
        $report = $store->latest();

        return view('link-checker::widgets.broken-links', [
            'report_exists' => (bool) $report,
            'broken' => $report ? count($store->errorKeys($report)) : 0,
            'total' => $report['summary']['total'] ?? 0,
            'scanned_at' => $report['scanned_at'] ?? null,
            'url' => cp_route('link-checker.index'),
        ]);
    }
}
