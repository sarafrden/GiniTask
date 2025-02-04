<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Console\Scheduling\Schedule;

class SchedulerServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(Schedule $schedule): void
    {
        // Schedule the AggregateProductAccesses job to run every hour
        // $schedule->job(new AggregateProductAccesses())->hourly();

        // Schedule product cache preloading daily at midnight
        $schedule->command('cache:preload-products')->dailyAt('00:00');
    }
}
