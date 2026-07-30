<?php

namespace Martinoak\LinkChecker\Http\Controllers;

use Illuminate\Http\Request;
use Martinoak\LinkChecker\ResultStore;
use Statamic\Http\Controllers\CP\CpController;

class UtilityController extends CpController
{
    /**
     * Renders full Blade page (statamic::layout) with <ui-*> components.
     * As "NonInertiaPage", the CP Vue app compiles it and components come alive.
     */
    public function index(Request $request, ResultStore $store)
    {
        return view('link-checker::utility', $store->forView($request));
    }
}
