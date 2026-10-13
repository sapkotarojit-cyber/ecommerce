<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    /**
     * Display paginated products.
     */
    public function index(Request $request)
    {
        $validated = $this->validateStrict($request, [
            'page' => [
                'sometimes',
                'integer',
                'min:1',
            ],
            'per_page' => [
                'sometimes',
                'integer',
                'min:1',
                'max:50',
            ],
        ]);

        $perPage = $validated['per_page'] ?? 12;

        $products = Product::query()
            ->with([
                'category',
                'dokan',
                'varients',
            ])
            ->latest('products.created_at')
            ->paginate($perPage);

        return ProductResource::collection($products);
    }

    /**
     * Display a single product.
     */
    public function show(int $id): ProductResource
    {
        $product = Product::query()
            ->with([
                'category',
                'dokan',
                'varients',
            ])
            ->findOrFail($id);

        return new ProductResource($product);
    }
}