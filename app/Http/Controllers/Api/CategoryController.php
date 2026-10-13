<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\CategoryResource;
use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function index(Request $request)
    {
        $this->validateStrict($request, []);

        return CategoryResource::collection(
            Category::query()
                ->where('is_active', true)
                ->orderBy('name')
                ->get()
        );
    }
}