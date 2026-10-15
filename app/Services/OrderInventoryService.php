<?php

namespace App\Services;

use App\Models\Order;
use App\Models\ProductVarient;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class OrderInventoryService
{
    /**
     * Deduct inventory exactly once for an order.
     * Must be called inside the payment transaction.
     */
    public function deductForPayment(Order $order): void
    {
        if ($order->stock_deducted_at !== null) {
            return;
        }

        if ($order->stock_restored_at !== null) {
            throw new RuntimeException(
                'Inventory for this order was already restored; review is required.'
            );
        }

        $items = $order->order_items()
            ->orderBy('varient_id')
            ->get();

        if ($items->isEmpty()) {
            throw new RuntimeException(
                'Cannot confirm payment for an order without items.'
            );
        }

        // Aggregate quantities so duplicate order lines cannot bypass checks.
        $quantities = $items
            ->groupBy('varient_id')
            ->map(fn ($rows) => (int) $rows->sum('qty'))
            ->sortKeys();

        $variants = [];

        // Lock variants in a consistent order to reduce deadlock risk.
        foreach ($quantities as $variantId => $quantity) {
            if ($quantity < 1) {
                throw new RuntimeException(
                    'Invalid order quantity; inventory review is required.'
                );
            }

            $variant = ProductVarient::query()
                ->whereKey($variantId)
                ->lockForUpdate()
                ->first();

            if (! $variant) {
                throw new RuntimeException(
                    'A product variant is unavailable; inventory review is required.'
                );
            }

            if ((int) $variant->qty < $quantity) {
                throw new RuntimeException(
                    'Insufficient stock to confirm this payment. Contact support for assistance.'
                );
            }

            $variants[] = [$variant, $quantity];
        }

        foreach ($variants as [$variant, $quantity]) {
            $variant->decrement('qty', $quantity);
        }

        $order->forceFill([
            'stock_deducted_at' => now(),
        ])->save();
    }

    /**
     * Delete matching cart quantities only after payment is confirmed.
     * This does not remove a newer cart quantity beyond the purchased amount.
     */
    public function removePurchasedCartItems(Order $order): void
    {
        foreach ($order->order_items as $item) {
            $remaining = (int) $item->qty;

            $cartRows = DB::table('carts')
                ->where('user_id', $order->user_id)
                ->where('varient_id', $item->varient_id)
                ->orderBy('id')
                ->lockForUpdate()
                ->get();

            foreach ($cartRows as $cartRow) {
                if ($remaining <= 0) {
                    break;
                }

                $take = min($remaining, (int) $cartRow->qty);

                if ($take >= (int) $cartRow->qty) {
                    DB::table('carts')
                        ->where('id', $cartRow->id)
                        ->delete();
                } else {
                    DB::table('carts')
                        ->where('id', $cartRow->id)
                        ->update([
                            'qty' => (int) $cartRow->qty - $take,
                            'updated_at' => now(),
                        ]);
                }

                $remaining -= $take;
            }
        }
    }
}