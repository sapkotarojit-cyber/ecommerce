<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OrderController extends Controller
{
    public function update(Request $request, $id)
    {
        $validated = $this->validateStrict($request, [
            'order_status' => [
                'required',
                'string',
                'in:pending,confirmed,processing,shipped,delivered,cancelled',
            ],
        ]);

        $vendor = Auth::guard('dokan')->user();

        abort_unless($vendor, 403);

        $order = Order::query()
            ->whereKey($id)
            ->where('dokan_id', $vendor->id)
            ->firstOrFail();

        $order->update([
            'order_status' => $validated['order_status'],
            'status' => $validated['order_status'],
        ]);

        return back()->with(
            'success',
            'Order status updated successfully.'
        );
    }
}
