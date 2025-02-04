<?php

namespace App\Jobs;

use App\Services\ProductCacheService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class PreloadProductCache implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct() {}

    public function handle(ProductCacheService $cacheService): void
    {
        Log::info("Starting product cache preloading.");
        $cacheService->preloadTopProducts();
        Log::info("Product cache preloading completed.");
    }
}
