<?php

namespace App\Models;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Redis;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

class Product extends Model
{
    protected $fillable = ['name', 'price'];


    protected static function booted()
    {
        // Clear cache when a product is created, updated, or deleted
        static::saved(function () {
            Cache::forget('top_products');
        });

        static::deleted(function () {
            Cache::forget('top_products');
        });
    }

    public static function trackAccess(int $productId): void
    {
        // Increment product access count in Redis
        $key = 'product_accesses:' . now()->format('Y-m-d:H'); // Hourly key
        Redis::zincrby($key, 1, $productId);

        // Set Redis key to expire after 25 hours to free up memory
        Redis::expire($key, 90000);
    }

    public static function getTopProducts()
{
    return Cache::remember('top_products', now()->addHours(24), function () {
        Log::info('Cache MISS: Fetching products from Redis.');

        $key = 'product_accesses:' . now()->subHour()->format('Y-m-d:H');
        $topProductIds = Redis::zrevrange($key, 0, 49);

        if (empty($topProductIds)) {
            Log::info('No products found in Redis.');
            return collect([]);
        }

        return self::query()->whereIn('id', $topProductIds)->get();
    });
}
}
