<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\Order;
use App\Models\ShippingAddress;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CheckoutController extends Controller
{
    public function postCheckoutSelected(Request $request)
    {
        $request->validate([
            'selected_items' => 'required|array|min:1',
            'selected_items.*' => 'exists:carts,id',
        ]);

        $ids = Cart::where('user_id', Auth::id())
            ->whereIn('id', $request->selected_items)
            ->pluck('id')
            ->toArray();

        if (empty($ids)) {
            return redirect()
                ->route('cart.index')
                ->with('error', 'Please select at least one valid cart item.');
        }

        session(['checkout_cart_ids' => $ids]);

        return redirect()->route('orders.checkout');
    }

    public function checkout()
    {
        $ids = session('checkout_cart_ids');

        if (empty($ids)) {
            return redirect()
                ->route('cart.index')
                ->with('error', 'Please select items from your cart.');
        }

        $cartItems = Cart::where('user_id', Auth::id())
            ->whereIn('id', $ids)
            ->with(['product', 'varient', 'dokan'])
            ->get();

        if ($cartItems->isEmpty()) {
            session()->forget('checkout_cart_ids');

            return redirect()
                ->route('cart.index')
                ->with('error', 'Your selected cart items are empty.');
        }

        $addresses = ShippingAddress::where('user_id', Auth::id())->get();

        $vendorTotal = [];
        $grandTotal = 0;

        foreach ($cartItems as $item) {
            $price = (float) ($item->varient->price ?? 0);
            $discount = (float) ($item->varient->discount ?? 0);

            $finalPrice = $price - ($price * $discount / 100);
            $total = $finalPrice * $item->qty;

            $dokanId = $item->dokan_id ?? 0;

            if (!isset($vendorTotal[$dokanId])) {
                $vendorTotal[$dokanId] = [
                    'dokan' => $item->dokan,
                    'subtotal' => 0,
                    'items' => [],
                ];
            }

            $vendorTotal[$dokanId]['subtotal'] += $total;
            $vendorTotal[$dokanId]['items'][] = $item;

            $grandTotal += $total;
        }

        return view('frontend.orders.checkout', compact(
            'cartItems',
            'addresses',
            'vendorTotal',
            'grandTotal'
        ));
    }

    public function store(Request $request)
    {
        $request->validate([
            'shipping_address_id' => 'required|exists:shipping_addresses,id',
            'payment_method' => 'required|in:esewa,bank',
        ]);

        $ids = session('checkout_cart_ids');

        if (empty($ids)) {
            return redirect()
                ->route('cart.index')
                ->with('error', 'Please select items first.');
        }

        $items = Cart::where('user_id', Auth::id())
            ->whereIn('id', $ids)
            ->with(['varient'])
            ->get();

        if ($items->isEmpty()) {
            return redirect()
                ->route('cart.index')
                ->with('error', 'Cart is empty.');
        }

        $address = ShippingAddress::where('id', $request->shipping_address_id)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        $order = DB::transaction(function () use ($items, $address, $request) {

            $total = 0;

            foreach ($items as $item) {
                $price = (float) ($item->varient->price ?? 0);
                $discount = (float) ($item->varient->discount ?? 0);

                $finalPrice = $price - ($price * $discount / 100);

                $total += $finalPrice * $item->qty;
            }

            $order = Order::create([
                'user_id' => Auth::id(),
                'dokan_id' => $items->first()->dokan_id,
                'shipping_address_id' => $address->id,
                'total_amount' => $total,
                'status' => 'pending',
                'order_status' => 'pending',
                'payment_method' => $request->payment_method,
                'payment_status' => 'pending',
            ]);

            foreach ($items as $item) {
                $price = (float) ($item->varient->price ?? 0);
                $discount = (float) ($item->varient->discount ?? 0);

                $finalPrice = $price - ($price * $discount / 100);

                $order->orderItems()->create([
                    'product_id' => $item->product_id,
                    'varient_id' => $item->varient_id,
                    'qty' => $item->qty,
                    'amount' => $finalPrice * $item->qty,
                ]);

                $item->delete();
            }

            return $order;
        });

        session()->forget('checkout_cart_ids');

        if ($request->payment_method === 'esewa') {
            return redirect()->route('orders.esewa.pay', $order->id);
        }

        return redirect()->route('orders.bank.pay', $order->id);
    }
}