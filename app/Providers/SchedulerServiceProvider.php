<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Console\Scheduling\Schedule;

class SchedulerServiceProvider extends ServiceProvider
{
    public function boot(Schedule $schedule): void
    {
        $schedule->job(new \App\Jobs\PreloadProductCache())->dailyAt('00:00')->onOneServer();
    }
}
