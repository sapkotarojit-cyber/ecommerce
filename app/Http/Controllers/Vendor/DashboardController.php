<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $dokan = Auth::guard('dokan')->user();

        if (!$dokan) {
            return redirect('/vendor/login');
        }

        $products = Product::where('dokan_id', $dokan->id)->count();
        $orders = Order::where('dokan_id', $dokan->id)->count();
        $totalSales = Order::where('dokan_id', $dokan->id)->sum('total_amount');

        return view('vendor.dashboard', compact('dokan', 'products', 'orders', 'totalSales'));
    }
}
