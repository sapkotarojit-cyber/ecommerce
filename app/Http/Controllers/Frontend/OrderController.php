<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');

        $orders = Order::with([
            'order_items.product',
            'order_items.varient.product',
            'shipping_address',
            'dokan',
            'returnRequests',
        ])
            ->where('user_id', Auth::id())
            ->when($search, function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('tracking_number', 'like', "%{$search}%")
                        ->orWhere('id', 'like', "%{$search}%");
                });
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('frontend.orders.index', compact('orders', 'search'));
    }

    public function show($id)
    {
        $order = Order::with([
            'order_items.product',
            'order_items.varient.product',
            'shipping_address',
            'dokan',
            'returnRequests',
        ])
            ->where('user_id', Auth::id())
            ->findOrFail($id);

        return view('frontend.orders.show', compact('order'));
    }

    public function cancel($id)
    {
        $order = Order::where('user_id', Auth::id())
            ->findOrFail($id);

        if ($order->order_status !== 'pending') {
            return back()->with(
                'error',
                'Only pending orders can be cancelled.'
            );
        }

        $order->update([
            'order_status' => 'cancelled',
        ]);

        if (!$order->returnRequests()->exists()) {
            $order->returnRequests()->create([
                'user_id' => Auth::id(),
                'dokan_id' => $order->dokan_id,
                'reason' => 'Order cancelled by customer.',
                'status' => 'requested',
                'refund_status' => 'pending',
                'refund_amount' => $order->total_amount,
            ]);
        }

        return back()->with(
            'success',
            'Order cancelled successfully. Refund request submitted.'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | eSewa
    |--------------------------------------------------------------------------
    */

    public function esewaPay(Order $order)
    {
        abort_unless($order->user_id === Auth::id(), 403);

        $uuid = 'ORD-' . $order->id . '-' . time();

        $amount = number_format(
            $order->total_amount,
            2,
            '.',
            ''
        );

        $productCode = env('ESEWA_PRODUCT_CODE', 'EPAYTEST');

        $fields =
            "total_amount={$amount}," .
            "transaction_uuid={$uuid}," .
            "product_code={$productCode}";

        $signature = base64_encode(
            hash_hmac(
                'sha256',
                $fields,
                env('ESEWA_SECRET_KEY'),
                true
            )
        );

        $data = [
            'gateway_url' => env(
                'ESEWA_GATEWAY',
                'https://rc-epay.esewa.com.np/api/epay/main/v2/form'
            ),

            'amount' => $amount,
            'tax_amount' => '0',
            'total_amount' => $amount,
            'transaction_uuid' => $uuid,
            'product_code' => $productCode,
            'product_service_charge' => '0',
            'product_delivery_charge' => '0',

            'success_url' => route('esewa.success'),
            'failure_url' => route('esewa.failure'),

            'signed_field_names' =>
                'total_amount,transaction_uuid,product_code',

            'signature' => $signature,
        ];

        session([
            'esewa_order_id' => $order->id,
            'esewa_transaction_uuid' => $uuid,
        ]);

        return view('frontend.payments.esewa-redirect', compact('data'));
    }

    public function esewaSuccess(Request $request)
    {
        $orderId = session('esewa_order_id');

        if (!$orderId) {
            return redirect()
                ->route('orders.index')
                ->with('error', 'Payment session expired.');
        }

        $order = Order::where('user_id', Auth::id())
            ->findOrFail($orderId);

        $order->update([
            'payment_status' => 'paid',
            'status' => 'paid',
        ]);

        session()->forget([
            'esewa_order_id',
            'esewa_transaction_uuid',
        ]);

        return redirect()
            ->route('orders.show', $order->id)
            ->with('success', 'eSewa payment successful.');
    }

    public function esewaFailure()
    {
        return redirect()
            ->route('orders.index')
            ->with('error', 'eSewa payment failed or was cancelled.');
    }

    /*
    |--------------------------------------------------------------------------
    | Bank Payment
    |--------------------------------------------------------------------------
    */

    public function bankPaymentPage(Order $order)
    {
        abort_unless($order->user_id === Auth::id(), 403);

        return view('frontend.payments.bank-pay', [
            'masterTracking' => $order->tracking_number,
            'totalAmount' => $order->total_amount,
            'order' => $order,
        ]);
    }

    public function bankSuccess(Request $request)
    {
        return redirect()
            ->route('orders.index')
            ->with('success', 'Bank payment submitted successfully.');
    }

    public function bankFailure()
    {
        return redirect()
            ->route('orders.index')
            ->with('error', 'Bank payment failed.');
    }

    /*
    |--------------------------------------------------------------------------
    | Invoice
    |--------------------------------------------------------------------------
    */

    public function invoice($id)
    {
        $order = Order::with([
            'order_items.product',
            'order_items.varient.product',
            'shipping_address',
            'dokan',
        ])
            ->where('user_id', Auth::id())
            ->findOrFail($id);

        return view('frontend.orders.invoice', compact('order'));
    }

    /*
    |--------------------------------------------------------------------------
    | Tracking
    |--------------------------------------------------------------------------
    */

    public function trackForm()
    {
        return view('frontend.orders.track');
    }

    public function trackResult(Request $request)
    {
        $request->validate([
            'tracking_number' => 'required|string',
        ]);

        $query = $request->tracking_number;

        $order = Order::with([
            'order_items.product',
            'order_items.varient.product',
            'shipping_address',
            'dokan',
        ])
            ->where(function ($q) use ($query) {
                $q->where('tracking_number', $query)
                    ->orWhere('id', $query);
            })
            ->first();

        if (!$order) {
            return back()
                ->withInput()
                ->with(
                    'error',
                    'No order found with that tracking number or ID.'
                );
        }

        return view('frontend.orders.track', compact('order'));
    }
}