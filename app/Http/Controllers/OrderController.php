<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Varient;
use App\Models\ReturnRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'items' => 'required|array',
            'items.*.varient_id' => 'required|exists:varients,id',
            'items.*.quantity' => 'required|integer|min:1',
            'shipping_address_id' => 'nullable|exists:shipping_addresses,id',
        ]);

        try {
            DB::transaction(function () use ($request, &$order) {

                $order = Order::create([
                    'user_id' => Auth::id(),
                    'shipping_address_id' => $request->shipping_address_id,
                    'status' => 'pending',
                    'total_amount' => 0,
                ]);

                $totalAmount = 0;

                foreach ($request->items as $itemData) {

                    $variant = Varient::findOrFail($itemData['varient_id']);
                    $quantity = (int) $itemData['quantity'];

                    if ($variant->qty < $quantity) {
                        throw new \Exception(
                            "Insufficient stock for variant '{$variant->title}'. Only {$variant->qty} items left."
                        );
                    }

                    $price = $variant->price;

                    if ($variant->discount > 0) {
                        $price = $price - (
                            $price * ($variant->discount / 100)
                        );
                    }

                    $totalAmount += $price * $quantity;

                    OrderItem::create([
                        'order_id' => $order->id,
                        'varient_id' => $variant->id,
                        'quantity' => $quantity,
                        'price' => $price,
                    ]);

                    $variant->decrement('qty', $quantity);
                }

                $order->update([
                    'total_amount' => $totalAmount
                ]);
            });

            return redirect()
                ->route('orders.show', $order->id)
                ->with('success', 'Order placed successfully!');

        } catch (\Exception $e) {

            return redirect()
                ->back()
                ->with('error', $e->getMessage());
        }
    }


    /**
     * Cancel order and create refund/return request.
     */
    public function cancel($id)
    {
        try {

            DB::transaction(function () use ($id) {

                $order = Order::with('orderItems')->findOrFail($id);

                // Customer can only cancel their own order
                if ($order->user_id !== Auth::id()) {
                    abort(403);
                }

                // Prevent duplicate cancellation
                if (in_array($order->order_status, [
                    'completed',
                    'cancelled'
                ])) {
                    throw new \Exception(
                        'This order cannot be cancelled.'
                    );
                }

                // Cancel order
                $order->update([
                    'order_status' => 'cancelled',
                    'status' => 'cancelled',
                ]);

                // Restore stock
                foreach ($order->orderItems as $item) {

                    $variant = Varient::find($item->varient_id);

                    if ($variant) {
                        $variant->increment(
                            'qty',
                            $item->quantity
                        );
                    }
                }

                // Create refund / return request
                ReturnRequest::create([
                    'order_id' => $order->id,
                    'user_id' => $order->user_id,
                    'dokan_id' => $order->dokan_id,
                    'reason' => 'Order cancelled by customer.',
                    'status' => 'pending',
                    'refund_status' => 'pending',
                    'refund_amount' => $order->total_amount,
                    'admin_note' => null,
                ]);
            });

            return redirect()
                ->back()
                ->with(
                    'success',
                    'Order cancelled successfully. Refund request has been sent.'
                );

        } catch (\Exception $e) {

            return redirect()
                ->back()
                ->with(
                    'error',
                    $e->getMessage()
                );
        }
    }
}