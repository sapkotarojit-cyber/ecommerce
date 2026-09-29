<?php

namespace App\Http\Controllers\Frontend;

use App\Models\Product;
use App\Models\Category;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    /**
     * Display all products on the shop page with filtering and sorting.
     */
    public function index(Request $request)
    {
        $query = Product::with(['dokan', 'varients']);

        // Filter by Category
        if ($request->filled('category')) {
            $query->where('category_id', $request->category);
        }

        // Filter by Price Range (looking at the first variant)
        if ($request->filled('min_price')) {
            $query->whereHas('varients', function ($q) use ($request) {
                $q->where('price', '>=', $request->min_price);
            });
        }

        if ($request->filled('max_price')) {
            $query->whereHas('varients', function ($q) use ($request) {
                $q->where('price', '<=', $request->max_price);
            });
        }

        // Sorting
        if ($request->sort === 'price_asc') {
            $query->whereHas('varients')->orderBy(
                \App\Models\Varient::select('price')
                    ->whereColumn('varients.product_id', 'products.id')
                    ->limit(1),
                'asc'
            );
        } elseif ($request->sort === 'price_desc') {
            $query->whereHas('varients')->orderBy(
                \App\Models\Varient::select('price')
                    ->whereColumn('varients.product_id', 'products.id')
                    ->limit(1),
                'desc'
            );
        } else {
            $query->latest();
        }

        $products = $query->paginate(12);
        $categories = Category::all();

        return view('frontend.products', compact('products', 'categories'));
    }

    /**
     * Display single product details.
     */
    public function show($id)
    {
        $product = Product::with(['dokan', 'varients'])->findOrFail($id);

        return view('frontend.product-details', compact('product'));
    }
}