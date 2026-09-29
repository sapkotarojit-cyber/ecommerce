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
        /*
        |--------------------------------------------------------------------------
        | Strict validation
        |--------------------------------------------------------------------------
        */

        $validated = $this->validateStrict($request, [
            'order_status' => [
                'required',
                'string',
                'in:pending,confirmed,processing,shipped,delivered,cancelled',
            ],

            'payment_status' => [
                'required',
                'string',
                'in:pending,pending_verification,paid,failed,refunded',
            ],
        ]);

        /*
        |--------------------------------------------------------------------------
        | Get authenticated vendor
        |--------------------------------------------------------------------------
        */

        $vendor = Auth::guard('dokan')->user();

        abort_unless($vendor, 403);

        /*
        |--------------------------------------------------------------------------
        | Only allow vendor to modify THEIR orders
        |--------------------------------------------------------------------------
        */

        $order = Order::where('id', $id)
            ->where('dokan_id', $vendor->id)
            ->firstOrFail();

        /*
        |--------------------------------------------------------------------------
        | Payment verification rules
        |--------------------------------------------------------------------------
        */

        if (
            $validated['payment_status'] === 'paid' &&
            $order->payment_method === 'bank' &&
            empty($order->payment_receipt)
        ) {
            return back()
                ->with('error', 'A bank payment cannot be marked as paid without a payment receipt.');
        }

        /*
        |--------------------------------------------------------------------------
        | Update
        |--------------------------------------------------------------------------
        */

        $order->update([
            'order_status' => $validated['order_status'],
            'status' => $validated['order_status'],
            'payment_status' => $validated['payment_status'],
        ]);

        return back()
            ->with(
                'success',
                'Order status updated successfully.'
            );
    }
}