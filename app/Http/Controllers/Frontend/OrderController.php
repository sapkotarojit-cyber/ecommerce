<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\ProductVarient;
use App\Models\ReturnRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    /**
     * Display customer's orders.
     */
    public function index(Request $request)
    {
        $orders = Order::with([
            'order_items.product',
            'order_items.varient',
            'shipping_address',
            'dokan',
        ])
            ->where('user_id', Auth::id())
            ->when(
                $request->filled('search'),
                function ($query) use ($request) {
                    $search = trim(
                        (string) $request->input('search')
                    );

                    $query->where(function ($q) use ($search) {
                        $q->where(
                            'tracking_number',
                            'like',
                            '%' . $search . '%'
                        )
                            ->orWhere(
                                'id',
                                is_numeric($search)
                                    ? (int) $search
                                    : -1
                            );
                    });
                }
            )
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view(
            'frontend.orders.index',
            compact('orders')
        );
    }

    /**
     * Cancel an order.
     */
    public function cancel($id)
    {
        try {
            DB::transaction(function () use ($id) {
                $order = Order::with('order_items')
                    ->where(
                        'user_id',
                        Auth::id()
                    )
                    ->lockForUpdate()
                    ->findOrFail($id);

                /*
                 * Cancellation must be idempotent.
                 */
                if ($order->order_status === 'cancelled') {
                    throw new \RuntimeException(
                        'This order has already been cancelled.'
                    );
                }

                if ($order->order_status === 'completed') {
                    throw new \RuntimeException(
                        'A completed order cannot be cancelled.'
                    );
                }

                /*
                 * Only these states can be cancelled.
                 */
                if (! in_array(
                    $order->order_status,
                    [
                        'pending',
                        'processing',
                    ],
                    true
                )) {
                    throw new \RuntimeException(
                        'This order cannot be cancelled at its current status.'
                    );
                }

                /*
                 * Cancel the order first.
                 */
                $order->update([
                    'order_status' => 'cancelled',
                    'status' => 'cancelled',
                ]);

                /*
                 * Restore stock exactly once.
                 */
                foreach ($order->order_items as $item) {
                    $variant = ProductVarient::whereKey(
                        $item->varient_id
                    )
                        ->lockForUpdate()
                        ->first();

                    if ($variant) {
                        $variant->increment(
                            'qty',
                            (int) $item->qty
                        );
                    }
                }

                /*
                 * Do not create duplicate refund requests.
                 */
                if (! $order->returnRequests()->exists()) {
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
                }
            });

            return redirect()
                ->back()
                ->with(
                    'success',
                    'Order cancelled successfully. Refund request has been sent.'
                );
        } catch (\RuntimeException $e) {
            return redirect()
                ->back()
                ->with(
                    'error',
                    $e->getMessage()
                );
        }
    }
}