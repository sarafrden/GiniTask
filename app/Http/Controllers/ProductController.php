<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\ProductAccessTracker;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use App\Http\Resources\ProductResource;

class ProductController extends Controller
{
    protected $tracker;

    public function __construct(ProductAccessTracker $tracker)
    {
        $this->tracker = $tracker;
    }

    /**
     * Display the specified product.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function show($id): JsonResponse
{
    $product = Product::findOrFail($id);
    $this->tracker->track($product->id);
    return response()->json([
        'success' => true,
        'data' => new ProductResource($product),
    ]);
}
}
