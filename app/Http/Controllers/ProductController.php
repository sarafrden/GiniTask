<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Services\ProductAccessTracker;
use App\Http\Resources\ProductResource;
use Illuminate\Http\JsonResponse;

class ProductController extends Controller
{
    public function __construct(private ProductAccessTracker $tracker) {}

    public function show(int $id): JsonResponse
    {
        $product = Product::findOrFail($id);
        $this->tracker->track($product->id);

        return response()->json([
            'success' => true,
            'data' => new ProductResource($product),
        ]);
    }
}
