<?php

namespace Martinoak\StatamicLinkChecker\Http\Controllers;


use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Artisan;
use Illuminate\View\View;

class LinkCheckerController
{
    public function index(): View
    {
        return view('link-checker::index');
    }

    public function run(): RedirectResponse
    {
        Artisan::call('link:check', ['--mail' => 'martin.dub@dek-cz.com']);

        return back();
    }

    public static function getCodeMessage(string $code): string
    {
        return match ($code) {
            '301' => 'Moved Permanently',
            '302' => 'Found',
            '303' => 'See Other',
            '304' => 'Not Modified',
            '305' => 'Use Proxy',
            '307' => 'Temporary Redirect',

            '400' => 'Bad Request',
            '401' => 'Unauthorized',
            '403' => 'Forbidden',
            '404' => 'Not Found',
            '405' => 'Method Not Allowed',
            '406' => 'Not Acceptable',
            '407' => 'Proxy Authentication Required',
            '408' => 'Request Timeout',
            '409' => 'Conflict',
            '410' => 'Gone',
            '411' => 'Length Required',
            '412' => 'Precondition Failed',
            '413' => 'Request Entity Too Large',
            '414' => 'Request-URI Too Long',
            '415' => 'Unsupported Media Type',

            default => 'Unknown',
        };
    }

}
