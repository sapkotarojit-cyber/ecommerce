<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\Order;
use App\Models\ShippingAddress;
use App\Models\ProductVarient;
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
        'payment_method' => 'required|in:esewa,bank,cod',
    ]);

    $ids = session('checkout_cart_ids');

    if (empty($ids)) {
        return redirect()
            ->route('cart.index')
            ->with('error', 'Please select items first.');
    }

    $address = ShippingAddress::where('id', $request->shipping_address_id)
        ->where('user_id', Auth::id())
        ->firstOrFail();

    try {

        $order = DB::transaction(function () use ($ids, $address, $request) {

            /*
             * Get cart items belonging ONLY to this user.
             */
            $items = Cart::where('user_id', Auth::id())
                ->whereIn('id', $ids)
                ->with(['product', 'varient'])
                ->get();

            if ($items->isEmpty()) {
                throw new \Exception('Your selected cart items are empty.');
            }

            $total = 0;

            /*
             * First check stock for every item.
             */
            foreach ($items as $item) {

                $variant = ProductVarient::where('id', $item->varient_id)
                    ->lockForUpdate()
                    ->first();

                if (!$variant) {
                    throw new \Exception(
                        "Product variant not found."
                    );
                }

                if ($variant->qty < $item->qty) {
                    $productName = $item->product->title ?? 'Product';

                    throw new \Exception(
                        "{$productName} does not have enough stock. "
                        . "Only {$variant->qty} available."
                    );
                }

                $price = (float) ($variant->price ?? 0);
                $discount = (float) ($variant->discount ?? 0);

                $finalPrice = $price - ($price * $discount / 100);

                $total += $finalPrice * $item->qty;
            }

            /*
             * Create the order.
             */
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

            /*
             * Create order items AND decrease stock.
             */
            foreach ($items as $item) {

                // Lock the variant again inside the transaction
                $variant = ProductVarient::where('id', $item->varient_id)
                    ->lockForUpdate()
                    ->first();

                if (!$variant) {
                    throw new \Exception(
                        "Product variant no longer exists."
                    );
                }

                /*
                 * Final stock check.
                 */
                if ($variant->qty < $item->qty) {
                    $productName = $item->product->title ?? 'Product';

                    throw new \Exception(
                        "{$productName} is no longer available in the requested quantity."
                    );
                }

                $price = (float) ($variant->price ?? 0);
                $discount = (float) ($variant->discount ?? 0);

                $finalPrice = $price - ($price * $discount / 100);

                /*
                 * Create order item.
                 */
                $order->orderItems()->create([
                    'product_id' => $item->product_id,
                    'varient_id' => $item->varient_id,
                    'qty' => $item->qty,
                    'amount' => $finalPrice * $item->qty,
                ]);

                /*
                 * IMPORTANT:
                 * Decrease product variant stock.
                 */
                $variant->decrement('qty', $item->qty);

                /*
                 * Remove purchased item from cart.
                 */
                $item->delete();
            }

            return $order;
        });

    } catch (\Exception $e) {

        return redirect()
            ->route('cart.index')
            ->with('error', $e->getMessage());
    }

    /*
     * Clear selected checkout items.
     */
    session()->forget('checkout_cart_ids');

    /*
     * Payment redirects.
     */
    if ($request->payment_method === 'esewa') {

        return redirect()->route(
            'orders.esewa.pay',
            $order->id
        );
    }

    if ($request->payment_method === 'bank') {

        return redirect()->route(
            'orders.bank.pay',
            [
                'order' => $order->id,
            ]
        );
    }

    return redirect()
        ->route('orders.show', $order->id)
        ->with(
            'success',
            'Order placed successfully with Cash on Delivery.'
        );
}
}

            