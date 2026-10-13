<?php

namespace App\Services;

use App\Models\Order;
use App\Models\ProductVarient;
use App\Models\ReturnRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class OrderService
{
    /**
     * Cancel an order belonging to the authenticated user.
     */
    public function cancelOrder(
        int $orderId,
        string $reason
    ): Order {
        return DB::transaction(function () use (
            $orderId,
            $reason
        ) {
            $order = Order::query()
                ->with('order_items')
                ->where(
                    'user_id',
                    Auth::id()
                )
                ->lockForUpdate()
                ->find($orderId);

            if (! $order) {
                throw new RuntimeException(
                    'ORDER_NOT_FOUND'
                );
            }

            if (! in_array(
                $order->order_status,
                [
                    'pending',
                    'processing',
                ],
                true
            )) {
                throw new RuntimeException(
                    'ORDER_CANNOT_BE_CANCELLED'
                );
            }

            /*
            |--------------------------------------------------------------------------
            | Restore stock
            |--------------------------------------------------------------------------
            */

            foreach ($order->order_items as $item) {
                $variant = ProductVarient::query()
                    ->whereKey(
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
            |--------------------------------------------------------------------------
            | Update order
            |--------------------------------------------------------------------------
            */

            $order->update([
                'order_status' =>
                    'cancelled',

                'payment_status' =>
                    $order->payment_status === 'paid'
                        ? 'paid'
                        : 'failed',
            ]);

            /*
            |--------------------------------------------------------------------------
            | Create cancellation / return request
            |--------------------------------------------------------------------------
            */

            if (
                ! $order
                    ->returnRequests()
                    ->exists()
            ) {
                ReturnRequest::create([
                    'order_id' =>
                        $order->id,

                    'user_id' =>
                        $order->user_id,

                    'dokan_id' =>
                        $order->dokan_id,

                    'reason' =>
                        'Order cancellation: ' .
                        trim($reason),

                    'status' =>
                        'requested',

                    'refund_status' =>
                        $order->payment_status === 'paid'
                            ? 'pending'
                            : 'not_required',

                    'refund_amount' =>
                        $order->payment_status === 'paid'
                            ? $order->total_amount
                            : 0,

                    'admin_note' =>
                        null,
                ]);
            }

            return $order->fresh([
                'order_items.product',
                'order_items.varient.product',
                'shipping_address',
                'dokan',
                'returnRequests',
            ]);
        });
    }
}