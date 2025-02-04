<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Jobs\PreloadProductCache;

class PreloadProductCacheCommand extends Command
{
    protected $signature = 'cache:preload-products';
    protected $description = 'Preload top accessed products into cache.';

    public function handle(): void
    {
        dispatch(new PreloadProductCache());
        $this->info('Product cache preloading job dispatched.');
    }
}
