<?php

namespace App\Services;

use Illuminate\Support\Facades\Redis;

class ProductAccessTracker
{
    public function track($productId)
    {
        // Increment product access count in Redis
        $key = 'product_accesses:' . now()->format('Y-m-d:H'); // Hourly key
        Redis::zincrby($key, 1, $productId);

        // Set Redis key to expire after 25 hours to free up memory
        Redis::expire($key, 90000);
    }
}
