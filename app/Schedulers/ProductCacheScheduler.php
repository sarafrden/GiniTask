<?php

namespace App\Schedulers;

use Illuminate\Console\Scheduling\Schedule;

class ProductCacheScheduler
{
    public function __invoke(Schedule $schedule): void
    {
        $schedule->command('cache:preload-products')->dailyAt('00:00');
    }
}
