<?php

namespace App\Services;

use App\Models\Product;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class ProductCacheService
{
    protected int $cacheTTL = 3600; // 1 hour

    public function __construct(private Product $product) {}

    public function getProduct(int $id): ?Product
    {
        return Cache::tags(['products'])->remember("product:{$id}", now()->addMinutes(30), function () use ($id) {
            Log::info("Cache MISS for product {$id}");
            return $this->product->find($id);
        });
    }

    public function preloadTopProducts(): void
    {
        $keys = [];
        for ($i = 0; $i < 24; $i++) {
            $keys[] = 'product:access:' . now()->subHours($i)->format('YmdH');
        }

        $tempKey = 'product:access:rolling';
        Redis::zunionstore($tempKey, $keys);
        Redis::expire($tempKey, 90000);

        $topProducts = Redis::zrevrange($tempKey, 0, 49);
        Cache::tags(['products'])->put('top_products', Product::whereIn('id', $topProducts)->get(), now()->addHours(24));

        Redis::del($tempKey);
    }
}
