<?php

namespace App\Services;

use App\Models\Product;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;
use Carbon\Carbon;

class ProductCacheService
{
    /**
     * Cache Time-to-Live (in seconds).
     */
    protected int $cacheTTL = 3600; // 1 hour

    /**
     * Retrieve a product by ID using the cache.
     * Logs cache hits/misses and tracks access using Redis time buckets.
     */
    public function getProduct(int $id): ?Product
    {
        $cacheKey = "product:{$id}";
        $product = Cache::get($cacheKey);

        if ($product) {
            Log::info("Cache hit for product {$id}");
            $this->logProductAccess($id);
            return $product;
        }

        Log::info("Cache miss for product {$id}");
        $product = Product::find($id);

        if ($product) {
            // Cache the product for a short period.
            Cache::put($cacheKey, $product, $this->cacheTTL);
            $this->logProductAccess($id);
        }

        return $product;
    }

    /**
     * Record a product access using Redis time buckets.
     *
     * Each access is logged into a bucket based on the current hour (YmdH).
     * For example, at 3 AM on February 4, 2025, the key will be "product:accesses:2025020403".
     * Buckets are set to expire after 25 hours.
     */
    protected function logProductAccess(int $productId): void
    {
        $bucket = Carbon::now()->format('YmdH'); // e.g., "2025020403"
        $key = "product:accesses:{$bucket}";
        Redis::zincrby($key, 1, $productId);
        // Set expiration to ensure the bucket drops off after it’s no longer needed.
        Redis::expire($key, 25 * 3600);
    }

    /**
     * Preload the cache with the top 50 most accessed products in the last 24 hours.
     *
     * This method aggregates the hourly buckets from the last 24 hours
     * using Redis's ZUNIONSTORE to compute a rolling 24‑hour count.
     */
    public function preloadTopProducts(): void
    {
        // Build an array of Redis keys for the last 24 hourly buckets.
        $keys = [];
        $now = Carbon::now();
        for ($i = 0; $i < 24; $i++) {
            $bucketTime = $now->copy()->subHours($i);
            $keys[] = 'product:accesses:' . $bucketTime->format('YmdH');
        }

        // Temporary key to store the aggregated access counts.
        $tempKey = 'product:accesses:rolling';

        // Aggregate counts from the last 24 hours.
        // Redis ZUNIONSTORE will sum the scores for each product across all specified buckets.
        Redis::zunionstore($tempKey, $keys);

        // Retrieve the top 50 products from the aggregated data.
        $topProducts = Redis::zrevrange($tempKey, 0, 49, 'WITHSCORES');

        foreach ($topProducts as $productId => $accesses) {
            // Ensure the product is cached (this call will also log the access).
            $product = $this->getProduct((int)$productId);

            if ($product) {
                Log::info("Preloaded product {$productId} into cache ({$accesses} accesses in the last 24 hours).");
            } else {
                Log::warning("Product {$productId} not found during preload.");
            }
        }

        // Clean up the temporary aggregated key.
        Redis::del($tempKey);
    }
}
