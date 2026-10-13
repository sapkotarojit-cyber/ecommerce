<?php

namespace App\Observers;

use App\Mail\OrderStatusChangedMail;
use App\Mail\PaymentSuccessfulMail;
use App\Models\Order;
use Illuminate\Contracts\Events\ShouldHandleEventsAfterCommit;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class OrderObserver implements ShouldHandleEventsAfterCommit
{
    public function updated(Order $order): void
    {
        // Send payment-success email when payment changes to paid.
        if (
            $order->wasChanged('payment_status')
            && $order->payment_status === 'paid'
        ) {
            $this->sendPaymentSuccessfulEmail($order);
        }

        if (! $order->wasChanged('order_status')) {
            return;
        }

        $oldStatus = (string) $order->getOriginal('order_status');
        $newStatus = (string) $order->order_status;

        if ($oldStatus === $newStatus) {
            return;
        }

        // Payment confirmation has its own dedicated email.
        if (
            $order->wasChanged('payment_status')
            && $order->payment_status === 'paid'
        ) {
            return;
        }

        try {
            $order->load([
                'user',
                'dokan',
                'shippingAddress',
                'orderItems.product',
                'orderItems.varient',
            ]);

            $email = $order->user?->email;

            if (
                ! is_string($email)
                || ! filter_var($email, FILTER_VALIDATE_EMAIL)
            ) {
                return;
            }

            Mail::to($email)->send(
                new OrderStatusChangedMail(
                    $order,
                    $oldStatus,
                    $newStatus
                )
            );
        } catch (\Throwable $e) {
            Log::error('Order status email failed.', [
                'order_id' => $order->id,
                'exception' => $e->getMessage(),
            ]);
        }
    }

    private function sendPaymentSuccessfulEmail(Order $order): void
    {
        try {
            $query = Order::query()
                ->where('user_id', $order->user_id);

            if ($order->payment_group_id !== null) {
                $query->where(
                    'payment_group_id',
                    $order->payment_group_id
                );
            } else {
                $query->whereKey($order->getKey());
            }

            $paidOrders = $query
                ->with([
                    'user',
                    'dokan',
                    'shippingAddress',
                    'orderItems.product',
                    'orderItems.varient',
                ])
                ->orderBy('id')
                ->get();

            if (
                $paidOrders->isEmpty()
                || ! $paidOrders->every(
                    fn (Order $item) => $item->payment_status === 'paid'
                )
            ) {
                return;
            }

            $email = $paidOrders->first()->user?->email;

            if (
                ! is_string($email)
                || ! filter_var($email, FILTER_VALIDATE_EMAIL)
            ) {
                Log::warning('Payment-success email skipped: invalid customer email.', [
                    'order_id' => $order->id,
                ]);

                return;
            }

            Mail::to($email)->send(
                new PaymentSuccessfulMail($paidOrders)
            );
        } catch (\Throwable $e) {
            Log::error('Payment-success email failed.', [
                'order_id' => $order->id,
                'payment_group_id' => $order->payment_group_id,
                'exception' => $e->getMessage(),
            ]);
        }
    }
}
