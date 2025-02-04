<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Redis;

class Product extends Model
{
    protected $fillable = ['name', 'price'];

    protected static function booted(): void
    {
        static::saved(fn() => self::clearCache());
        static::deleted(fn() => self::clearCache());
    }

    private static function clearCache(): void
    {
        Cache::tags(['products'])->flush(); // Clears all related cache
    }

    public static function trackAccess(int $productId): void
    {
        $key = 'product:access:' . now()->format('YmdH'); // Hourly tracking key
        Redis::zincrby($key, 1, $productId);
        Redis::expire($key, 90000); // 25 hours expiration
    }

    public static function getTopProducts()
    {
        return Cache::tags(['products'])->remember('top_products', now()->addHours(24), function () {
            $key = 'product:access:' . now()->subHour()->format('YmdH');
            $topProductIds = Redis::zrevrange($key, 0, 49);

            return empty($topProductIds) ? collect([]) : self::whereIn('id', $topProductIds)->get();
        });
    }
}
