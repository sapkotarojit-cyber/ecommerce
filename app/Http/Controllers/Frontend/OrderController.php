<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

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
        ->when($search, fn ($q) => $q->where(function ($q) use ($search) {
            $q->where('tracking_number', 'like', "%$search%")
              ->orWhere('id', 'like', "%$search%");
        }))
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

    public function cancelForm($id)
{
    $order = Order::where('user_id', Auth::id())->findOrFail($id);

    if ($order->order_status !== 'pending') {
        return redirect()->route('orders.show', $order->id)
            ->with('error', 'Only pending orders can be cancelled.');
    }

    return view('frontend.orders.cancel', compact('order'));
}

    public function cancel(Request $request, $id)
{
    $order = Order::where('user_id', Auth::id())->findOrFail($id);

    if ($order->order_status !== 'pending') {
        return redirect()->route('orders.show', $order->id)
            ->with('error', 'Only pending orders can be cancelled.');
    }

    $request->validate([
        'reason' => 'required|string|max:1000',
    ]);

    $order->update([
        'order_status' => 'cancelled',
    ]);

    if (!$order->returnRequests()->exists()) {
        $order->returnRequests()->create([
            'user_id' => Auth::id(),
            'dokan_id' => $order->dokan_id,
            'reason' => 'Order cancellation: ' . $request->reason,
            'status' => 'requested',
            'refund_status' => 'pending',
            'refund_amount' => $order->total_amount,
        ]);
    }

    return redirect()->route('orders.show', $order->id)
        ->with('success', 'Order cancelled successfully. Refund request submitted.');
}
    /* ==================== ESEWA ==================== */

    public function esewaPay(Order $order)
    {
        abort_unless($order->user_id === Auth::id(), 403);

        $amount = number_format($order->total_amount, 2, '.', '');
        $uuid = 'ORD-' . $order->id . '-' . time();
        $code = env('ESEWA_PRODUCT_CODE', 'EPAYTEST');

        $fields = "total_amount=$amount,transaction_uuid=$uuid,product_code=$code";

        $data = [
            'gateway_url' => env(
                'ESEWA_GATEWAY',
                'https://rc-epay.esewa.com.np/api/epay/main/v2/form'
            ),
            'amount' => $amount,
            'tax_amount' => '0',
            'total_amount' => $amount,
            'transaction_uuid' => $uuid,
            'product_code' => $code,
            'product_service_charge' => '0',
            'product_delivery_charge' => '0',
            'success_url' => route('esewa.success'),
            'failure_url' => route('esewa.failure'),
            'signed_field_names' => 'total_amount,transaction_uuid,product_code',
            'signature' => base64_encode(
                hash_hmac('sha256', $fields, env('ESEWA_SECRET_KEY'), true)
            ),
        ];

        session([
            'esewa_order_id' => $order->id,
            'esewa_transaction_uuid' => $uuid,
        ]);

        return view('frontend.payments.esewa-redirect', compact('data'));
    }

    public function esewaSuccess()
    {
        $order = Order::where('user_id', Auth::id())
            ->find(session('esewa_order_id'));

        if (!$order) {
            return redirect()->route('orders.index')
                ->with('error', 'Payment session expired.');
        }

        $order->update([
            'payment_status' => 'paid',
            'status' => 'paid',
            'order_status' => 'confirmed',
        ]);

        session()->forget([
            'esewa_order_id',
            'esewa_transaction_uuid',
        ]);

        return redirect()->route('orders.show', $order->id)
            ->with('success', 'eSewa payment successful. Your order has been placed.');
    }

    public function esewaFailure()
    {
        $order = Order::with('orderItems')
            ->where('user_id', Auth::id())
            ->find(session('esewa_order_id'));

        if (!$order) {
            return redirect()->route('cart.index')
                ->with('error', 'Payment was not completed.');
        }

        $cartIds = DB::transaction(function () use ($order) {
            $ids = [];

            foreach ($order->orderItems as $item) {
                $cart = Cart::create([
                    'user_id' => Auth::id(),
                    'product_id' => $item->product_id,
                    'varient_id' => $item->varient_id,
                    'dokan_id' => $order->dokan_id,
                    'qty' => $item->qty,
                ]);

                $ids[] = $cart->id;
            }

            $order->orderItems()->delete();
            $order->delete();

            return $ids;
        });

        session()->forget([
            'esewa_order_id',
            'esewa_transaction_uuid',
        ]);

        session(['checkout_cart_ids' => $cartIds]);

        return redirect()->route('orders.checkout')
            ->with(
                'error',
                'eSewa payment was not completed. Please choose another payment method.'
            );
    }

    /* ==================== BANK ==================== */


public function bankPaymentPage(Order $order)
{
    abort_unless($order->user_id === Auth::id(), 403);

    session(['bank_order_id' => $order->id]);

    return view('frontend.payments.bank-pay', [
        'masterTracking' => $order->tracking_number,
        'totalAmount' => $order->total_amount,
        'order' => $order,
    ]);
}

public function bankSuccess(Request $request)
{
    $orderId = session('bank_order_id');

    if (!$orderId) {
        return redirect()->route('orders.index')
            ->with('error', 'Payment session expired.');
    }

    $order = Order::where('user_id', Auth::id())->findOrFail($orderId);

    $receipt = null;

    if ($request->hasFile('payment_receipt')) {
        $receipt = $request->file('payment_receipt')
            ->store('payment_receipts', 'public');
    }

    $order->update([
        'payment_status' => 'paid',
        'status' => 'paid',
        'order_status' => 'confirmed',
        'payment_receipt' => $receipt,
    ]);

    session()->forget('bank_order_id');

    return redirect()
        ->route('orders.show', $order->id)
        ->with('success', 'Bank payment successful. Your order has been placed.');
}

public function bankFailure()
{
    $orderId = session('bank_order_id');

    if (!$orderId) {
        return redirect()->route('orders.checkout')
            ->with('error', 'Bank payment was not completed.');
    }

    $order = Order::with('orderItems')
        ->where('id', $orderId)
        ->where('user_id', Auth::id())
        ->first();

    if (!$order) {
        session()->forget('bank_order_id');

        return redirect()->route('orders.checkout')
            ->with('error', 'Bank payment was not completed.');
    }

    $cartIds = DB::transaction(function () use ($order) {
        $ids = [];

        foreach ($order->orderItems as $item) {
            $cart = Cart::create([
                'user_id' => Auth::id(),
                'product_id' => $item->product_id,
                'varient_id' => $item->varient_id,
                'dokan_id' => $order->dokan_id,
                'qty' => $item->qty,
            ]);

            $ids[] = $cart->id;
        }

        $order->orderItems()->delete();
        $order->delete();

        return $ids;
    });

    session()->forget('bank_order_id');
    session(['checkout_cart_ids' => $cartIds]);

    return redirect()->route('orders.checkout')
        ->with(
            'error',
            'Bank payment was not completed. Your items have been returned to checkout. Please choose another payment method.'
        );
}

    /* ==================== INVOICE ==================== */

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

    /* ==================== TRACKING ==================== */

    public function trackForm()
    {
        return view('frontend.orders.track');
    }

    public function trackResult(Request $request)
    {
        $request->validate([
            'tracking_number' => 'required|string',
        ]);

        $order = Order::with([
            'order_items.product',
            'order_items.varient.product',
            'shipping_address',
            'dokan',
        ])
        ->where(function ($q) use ($request) {
            $q->where('tracking_number', $request->tracking_number)
              ->orWhere('id', $request->tracking_number);
        })
        ->first();

        if (!$order) {
            return back()->withInput()
                ->with('error', 'No order found with that tracking number or ID.');
        }

        return view('frontend.orders.track', compact('order'));
    }
}