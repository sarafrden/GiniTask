<?php

declare(strict_types=1);

namespace App\Observers;

use App\Models\Product;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class ProductObserver
{
    /**
     * Handle the Product "saved" event (create or update).
     */
    public function saved(Product $product): void
    {
        // Invalidate the individual product cache.
        Cache::forget($this->cacheKey($product->id));
        // Invalidate the top products cache.
        Cache::forget('top_products');

        Log::info("Cache invalidated for product {$product->id} (saved).");
    }

    /**
     * Handle the Product "deleted" event.
     */
    public function deleted(Product $product): void
    {
        Cache::forget($this->cacheKey($product->id));
        Cache::forget('top_products');

        Log::info("Cache invalidated for product {$product->id} (deleted).");
    }

    /**
     * Generate the cache key for a given product.
     */
    protected function cacheKey(int $productId): string
    {
        return "product:{$productId}";
    }
}
