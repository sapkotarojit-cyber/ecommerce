<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Varient;
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
                // 1. Create main order record
                $order = Order::create([
                    'user_id' => Auth::id(),
                    'shipping_address_id' => $request->shipping_address_id,
                    'status' => 'pending',
                    'total_amount' => 0,
                ]);

                $totalAmount = 0;

                // 2. Loop through submitted cart items
                foreach ($request->items as $itemData) {
                    $variant = Varient::findOrFail($itemData['varient_id']);
                    $quantity = (int) $itemData['quantity'];

                    // Verify stock availability
                    if ($variant->qty < $quantity) {
                        throw new \Exception("Insufficient stock for variant '{$variant->title}'. Only {$variant->qty} items left.");
                    }

                    // Calculate final price per unit (accounting for discount)
                    $price = $variant->price;
                    if ($variant->discount > 0) {
                        $price = $price - ($price * ($variant->discount / 100));
                    }

                    $totalAmount += ($price * $quantity);

                    // Create order line item
                    OrderItem::create([
                        'order_id' => $order->id,
                        'varient_id' => $variant->id,
                        'quantity' => $quantity,
                        'price' => $price,
                    ]);

                    // 3. Decrease stock
                    $variant->decrement('qty', $quantity);
                }

                // Update total order amount
                $order->update(['total_amount' => $totalAmount]);
            });

            return redirect()->route('orders.show', $order->id)->with('success', 'Order placed successfully!');

        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }
}