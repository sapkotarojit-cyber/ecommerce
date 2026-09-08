<?php

namespace App\Http\Controllers\Frontend;

use App\Models\Order;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class OrderController extends Controller
{
    /**
     * Display a listing of the logged-in user's orders.
     */
    public function index()
    {
        $orders = Order::with(['order_items.product', 'shipping_address', 'dokan'])
            ->where('user_id', Auth::id())
            ->latest()
            ->paginate(10);

        return view('frontend.orders.index', compact('orders'));
    }

    /**
     * Show tracking search form
     */
    public function trackForm()
    {
        return view('frontend.orders.track');
    }

    /**
     * Search and render tracking details
     */
    public function trackResult(Request $request)
    {
        $request->validate([
            'tracking_number' => 'required|string',
        ]);

        $query = $request->input('tracking_number');

        // Search by tracking_number OR id
        $order = Order::with(['order_items.product', 'shipping_address', 'dokan'])
            ->where('tracking_number', $query)
            ->orWhere('id', $query)
            ->first();

        if (!$order) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'No order found with that tracking number or ID.');
        }

        return view('frontend.orders.track', compact('order'));
    }
}