<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Services\CatalogCache;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    /** Product listing. */
    public function index(Request $request): JsonResponse
    {
        $validated = $this->validateStrict($request, [
            'page' => [
                'sometimes',
                'integer',
                'min:1',
                'max:' . config('security.validation.pagination.max_page', 1000),
            ],
        ]);

        $page = (int) ($validated['page'] ?? 1);
        $perPage = max(1, (int) config('security.validation.pagination.products_per_page', 12));
        $key = 'products:list:' . hash('sha256', $request->fullUrl());

        $payload = app(CatalogCache::class)->remember(
            $key,
            function () use ($page, $perPage): array {
                $products = Product::query()
                    ->with([
                        'category:id,name,slug,is_active',
                        'dokan:id,name,company_name,logo,status',
                        'varients:id,product_id,title,images,price,discount,qty',
                    ])
                    ->latest('products.created_at')
                    ->paginate($perPage, ['*'], 'page', $page);

                return [
                    'success' => true,
                    'current_page' => $products->currentPage(),
                    'data' => $products->getCollection()
                        ->map(fn (Product $product): array => $this->productData($product))
                        ->values()
                        ->all(),
                    'first_page_url' => $products->url(1),
                    'from' => $products->firstItem(),
                    'last_page' => $products->lastPage(),
                    'last_page_url' => $products->url($products->lastPage()),
                    'next_page_url' => $products->nextPageUrl(),
                    'path' => $products->path(),
                    'per_page' => $products->perPage(),
                    'prev_page_url' => $products->previousPageUrl(),
                    'to' => $products->lastItem(),
                    'total' => $products->total(),
                ];
            }
        );

        return response()->json($payload);
    }

    /** Display a single product. */
    public function show(int $id): JsonResponse
    {
        if ($id < 1) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid product ID.',
            ], 422);
        }

        $payload = app(CatalogCache::class)->remember(
            'products:detail:' . $id,
            function () use ($id): ?array {
                $product = Product::query()
                    ->with([
                        'category:id,name,slug,is_active',
                        'dokan:id,name,company_name,logo,status',
                        'varients:id,product_id,title,images,price,discount,qty',
                    ])
                    ->find($id);

                if (! $product) {
                    return null;
                }

                return [
                    'success' => true,
                    'data' => $this->productData($product),
                ];
            }
        );

        if ($payload === null) {
            return response()->json([
                'success' => false,
                'message' => 'Product not found.',
            ], 404);
        }

        return response()->json($payload);
    }

    /** Public-safe product representation. */
    private function productData(Product $product): array
    {
        // The products table has a legacy string column named "category".
        // Use the loaded relationship directly to avoid the property collision.
        $category = $product->getRelation('category');
        $vendor = $product->getRelation('dokan');
        $variants = $product->getRelation('varients');

        return [
            'id' => $product->id,
            'category_id' => $product->category_id,
            'title' => $product->title,
            'description' => $product->description,
            'dokan_id' => $product->dokan_id,
            'category' => $category ? [
                'id' => $category->id,
                'name' => $category->name,
                'slug' => $category->slug,
                'is_active' => (bool) $category->is_active,
            ] : null,
            'dokan' => $vendor ? [
                'id' => $vendor->id,
                'name' => $vendor->name,
                'company_name' => $vendor->company_name,
                'logo' => $vendor->logo,
                'status' => $vendor->status,
            ] : null,
            'varients' => $variants
                ->map(function ($variant): array {
                    return [
                        'id' => $variant->id,
                        'product_id' => $variant->product_id,
                        'title' => $variant->title,
                        'images' => is_array($variant->images) ? $variant->images : [],
                        'price' => (float) $variant->price,
                        'discount' => (float) $variant->discount,
                        'qty' => (int) $variant->qty,
                    ];
                })
                ->values()
                ->all(),
        ];
    }
}