<?php

namespace Martinoak\StatamicLinkChecker;

use Livewire\Livewire;
use Martinoak\StatamicLinkChecker\Livewire\Links;
use Statamic\Facades\CP\Nav;
use Statamic\Providers\AddonServiceProvider;
use Illuminate\Console\Application;

class ServiceProvider extends AddonServiceProvider
{
    protected $vite = [
        'input' => [
            'resources/js/cp.js',
            'resources/css/cp.css'
        ],
        'publicDirectory' => 'resources/dist',
    ];

    protected $commands = [
        Commands\LinkCheckerCommand::class,
    ];

    public function bootAddon(): void
    {
        $this->loadViewsFrom(__DIR__ . '/../resources/views/', 'link-checker');
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');

        Application::starting(function ($artisan) {
            $artisan->resolveCommands($this->commands);
        });

        Livewire::component('link-checker::links', Links::class);
        Livewire::component('link-checker::run-button', \Martinoak\StatamicLinkChecker\Livewire\RunButton::class);

        Nav::extend(function ($nav) {
            $nav->tools('Link Checker')
                ->url('/cp/tools/link-checker')
                ->icon('link');
        });

        parent::boot();
    }
}
