<?php

use Illuminate\Support\Facades\Route;
use Martinoak\StatamicLinkChecker\Http\Controllers\LinkCheckerController;

/*
|--------------------------------------------------------------------------
| CP routes
|--------------------------------------------------------------------------
*/

Route::prefix('tools/link-checker')->group(function () {
    Route::get('/', [LinkCheckerController::class, 'index'])->name('link-checker.index');
    Route::get('run', [LinkCheckerController::class, 'run'])->name('link-checker.run');
});

