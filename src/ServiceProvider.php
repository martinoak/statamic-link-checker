<?php

namespace Martinoak\LinkChecker;

use Illuminate\Console\Scheduling\Schedule;
use Martinoak\LinkChecker\Console\ScanCommand;
use Martinoak\LinkChecker\Listeners\ScanEntryLinks;
use Statamic\Events\EntrySaved;
use Statamic\Facades\CP\Nav;
use Statamic\Providers\AddonServiceProvider;

class ServiceProvider extends AddonServiceProvider
{
    protected $viewNamespace = 'link-checker';

    protected $config = true;

    protected $commands = [
        ScanCommand::class,
    ];

    protected $routes = [
        'cp' => __DIR__.'/../routes/cp.php',
    ];

    protected $listen = [
        EntrySaved::class => [ScanEntryLinks::class],
    ];

    public function register()
    {
        parent::register();

        foreach ([LinkExtractor::class, LinkResolver::class, LinkChecker::class, ResultStore::class] as $class) {
            $this->app->bind($class, fn () => new $class($this->linkCheckerConfig()));
        }

        $this->app->bind(Scanner::class, fn ($app) => new Scanner(
            $app->make(LinkExtractor::class),
            $app->make(LinkResolver::class),
            $app->make(LinkChecker::class),
            $app->make(ResultStore::class),
        ));
    }

    public function bootAddon()
    {
        // Nav item under Tools section – same approach as
        // dek-statamic/dev-tools. Target page is full Blade page
        // (statamic::layout), so native <ui-*> components compile in it.
        Nav::extend(function ($nav) {
            $nav->tools(__('statamic-link-checker::messages.nav_title'))
                ->route('link-checker.index')
                ->icon('link');
        });
    }

    protected function schedule(Schedule $schedule)
    {
        if (! config('statamic-link-checker.schedule.enabled', false)) {
            return;
        }

        $event = $schedule->command('link-checker:scan --notify')->withoutOverlapping();

        if ($cron = config('statamic-link-checker.schedule.cron')) {
            $event->cron($cron);

            return;
        }

        match (config('statamic-link-checker.schedule.frequency', 'daily')) {
            'hourly' => $event->hourly(),
            'weekly' => $event->weekly(),
            default => $event->daily(),
        };
    }

    private function linkCheckerConfig(): array
    {
        return (array) config('statamic-link-checker', []);
    }
}
