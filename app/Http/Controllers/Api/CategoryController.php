<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Services\CatalogCache;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $this->validateStrict($request, []);

        $categories = app(CatalogCache::class)->remember(
            'categories:active',
            fn (): array => Category::query()
                ->where('is_active', true)
                ->orderBy('name')
                ->get()
                ->map(fn (Category $category): array => [
                    'id' => $category->id,
                    'name' => $category->name,
                    'slug' => $category->slug,
                    'is_active' => (bool) $category->is_active,
                    'created_at' => $category->created_at?->toISOString(),
                    'updated_at' => $category->updated_at?->toISOString(),
                ])
                ->all()
        );

        return response()->json([
            'data' => $categories,
        ]);
    }
}
