<?php

use Illuminate\Support\Facades\Route;
use Martinoak\LinkChecker\Http\Controllers\ScanController;
use Martinoak\LinkChecker\Http\Controllers\UtilityController;

Route::get('link-checker', [UtilityController::class, 'index'])->name('link-checker.index');
Route::post('link-checker/scan', [ScanController::class, 'scan'])->name('link-checker.scan');
Route::post('link-checker/ignore', [ScanController::class, 'ignore'])->name('link-checker.ignore');
Route::post('link-checker/unignore', [ScanController::class, 'unignore'])->name('link-checker.unignore');
