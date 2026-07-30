<?php

namespace Martinoak\LinkChecker\Http\Controllers;

use Illuminate\Http\Request;
use Martinoak\LinkChecker\Jobs\RunScanJob;
use Martinoak\LinkChecker\ResultStore;
use Statamic\Http\Controllers\CP\CpController;

class ScanController extends CpController
{
    public function scan(Request $request)
    {
        RunScanJob::dispatch();

        return redirect(cp_route('link-checker.index'))
            ->with('success', __('statamic-link-checker::messages.scan_queued'));
    }

    public function ignore(Request $request, ResultStore $store)
    {
        if ($url = trim((string) $request->input('url'))) {
            $store->ignore()->add($url);
        }

        return redirect()->back()->with('success', __('statamic-link-checker::messages.ignored'));
    }

    public function unignore(Request $request, ResultStore $store)
    {
        if ($url = trim((string) $request->input('url'))) {
            $store->ignore()->remove($url);
        }

        return redirect()->back()->with('success', __('statamic-link-checker::messages.unignored'));
    }
}
