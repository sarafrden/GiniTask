<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Product;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Redis;

class PreloadProductCache extends Command
{
    protected $signature = 'cache:preload-products';
    protected $description = 'Preload the top 50 most accessed products into cache.';

    public function handle(): void
    {
        Cache::forget('top_products');

        $key = 'product_accesses:' . now()->subHour()->format('Y-m-d:H');
        $topProductIds = Redis::zrevrange($key, 0, 49);

        if (empty($topProductIds)) {
            $this->info('No products to preload.');
            return;
        }

        // Preload the cache with top products
        Cache::put('top_products', Product::whereIn('id', $topProductIds)->get(), now()->addHours(24));

        $this->info('Product cache preloaded.');
    }
}
