<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\ProductVarient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CartController extends Controller
{
    /**
     * Display user's cart.
     */
    public function index()
    {
        $cartItems = Cart::where('user_id', Auth::id())
            ->with(['product', 'varient', 'dokan'])
            ->get();

        $total = 0;
        $vendorTotal = [];

        foreach ($cartItems as $item) {
            $price = (float) ($item->varient->price ?? 0);
            $discount = (float) ($item->varient->discount ?? 0);

            $discount = max(0, min(100, $discount));

            $finalPrice = $price - ($price * $discount / 100);
            $itemTotal = $finalPrice * (int) $item->qty;

            $total += $itemTotal;

            $vendorId = $item->dokan_id;

            if (! isset($vendorTotal[$vendorId])) {
                $vendorTotal[$vendorId] = [
                    'dokan' => $item->dokan,
                    'subtotal' => 0,
                    'items' => [],
                ];
            }

            $vendorTotal[$vendorId]['subtotal'] += $itemTotal;
            $vendorTotal[$vendorId]['items'][] = $item;
        }

        return view(
            'frontend.cart.index',
            compact('cartItems', 'total', 'vendorTotal')
        );
    }

    /**
     * Add item to cart.
     */
    public function add(Request $request)
    {
        $validated = $request->validate([
            'varient_id' => [
                'bail',
                'required',
                'integer',
                'min:1',
                'exists:product_varients,id',
            ],

            'qty' => [
                'bail',
                'required',
                'integer',
                'min:1',
                'max:9999',
            ],
        ]);

        $variant = ProductVarient::query()
            ->with('product.dokan')
            ->findOrFail($validated['varient_id']);

        if (! $variant->product) {
            throw ValidationException::withMessages([
                'varient_id' => 'The selected product is invalid.',
            ]);
        }

        if (! $variant->product->dokan_id) {
            throw ValidationException::withMessages([
                'varient_id' => 'The selected product has no valid vendor.',
            ]);
        }

        $qty = (int) $validated['qty'];

        if ((int) $variant->qty < $qty) {
            return $this->stockError(
                $request,
                'Not enough stock available. Only '
                . $variant->qty
                . ' left.'
            );
        }

        $existing = Cart::query()
            ->where('user_id', Auth::id())
            ->where('varient_id', $variant->id)
            ->first();

        if ($existing) {
            $newQty = (int) $existing->qty + $qty;

            if ((int) $variant->qty < $newQty) {
                return $this->stockError(
                    $request,
                    'Not enough stock available. You already have '
                    . $existing->qty
                    . ' in cart.'
                );
            }

            $existing->update([
                'qty' => $newQty,
            ]);

            $message = 'Cart updated successfully!';
        } else {
            Cart::create([
                'user_id' => Auth::id(),
                'varient_id' => $variant->id,
                'product_id' => $variant->product_id,
                'dokan_id' => $variant->product->dokan_id,
                'qty' => $qty,
            ]);

            $message = 'Item added to cart successfully!';
        }

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => $message,
                'cart_count' => $this->cartCount(),
            ]);
        }

        return redirect()
            ->route('cart.index')
            ->with('success', $message);
    }

    /**
     * Buy now.
     */
    public function buyNow(Request $request)
    {
        $validated = $request->validate([
            'varient_id' => [
                'bail',
                'required',
                'integer',
                'min:1',
                'exists:product_varients,id',
            ],

            'qty' => [
                'bail',
                'required',
                'integer',
                'min:1',
                'max:9999',
            ],
        ]);

        $variant = ProductVarient::query()
            ->with('product.dokan')
            ->findOrFail($validated['varient_id']);

        if (! $variant->product || ! $variant->product->dokan_id) {
            throw ValidationException::withMessages([
                'varient_id' => 'The selected product is invalid.',
            ]);
        }

        $qty = (int) $validated['qty'];

        $existing = Cart::query()
            ->where('user_id', Auth::id())
            ->where('varient_id', $variant->id)
            ->first();

        $newQty = $existing
            ? ((int) $existing->qty + $qty)
            : $qty;

        if ($newQty > (int) $variant->qty) {
            return response()->json([
                'success' => false,
                'message' => "Only {$variant->qty} items are available "
                    . "in stock. You already have "
                    . ($existing->qty ?? 0)
                    . " in your cart.",
            ], 422);
        }

        if ($existing) {
            $existing->update([
                'qty' => $newQty,
            ]);
        } else {
            Cart::create([
                'user_id' => Auth::id(),
                'product_id' => $variant->product_id,
                'varient_id' => $variant->id,
                'dokan_id' => $variant->product->dokan_id,
                'qty' => $qty,
            ]);
        }

        $cart = Cart::query()
            ->where('user_id', Auth::id())
            ->where('varient_id', $variant->id)
            ->firstOrFail();

        session([
            'checkout_cart_ids' => [$cart->id],
        ]);

        return response()->json([
            'success' => true,
            'redirect_url' => route('orders.checkout'),
        ]);
    }

    /**
     * Update cart quantity.
     */
    public function update(Request $request, int $id)
    {
        $validated = $request->validate([
            'qty' => [
                'bail',
                'required',
                'integer',
                'min:1',
                'max:9999',
            ],
        ]);

        $cart = Cart::query()
            ->where('id', $id)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        $variant = ProductVarient::findOrFail(
            $cart->varient_id
        );

        $qty = (int) $validated['qty'];

        if ($qty > (int) $variant->qty) {
            return response()->json([
                'success' => false,
                'message' => "Only {$variant->qty} items are available in stock.",
            ], 422);
        }

        $cart->update([
            'qty' => $qty,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Cart updated successfully.',
            'qty' => $qty,
            'cart_count' => $this->cartCount(),
        ]);
    }

    /**
     * Remove item from cart.
     */
    public function destroy(int $id)
    {
        $cartItem = Cart::query()
            ->where('id', $id)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        $cartItem->delete();

        if (request()->ajax() || request()->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Item removed from cart!',
                'cart_count' => $this->cartCount(),
            ]);
        }

        return redirect()
            ->route('cart.index')
            ->with('success', 'Item removed from cart.');
    }

    /**
     * Clear entire cart.
     */
    public function clear()
    {
        Cart::where('user_id', Auth::id())->delete();

        return redirect()
            ->route('cart.index')
            ->with('success', 'Cart cleared successfully!');
    }

    /**
     * Get cart count.
     */
    public function count()
    {
        $count = $this->cartCount();

        if (request()->ajax() || request()->wantsJson()) {
            return response()->json([
                'count' => $count,
            ]);
        }

        return $count;
    }

    /**
     * Return current cart quantity.
     */
    private function cartCount(): int
    {
        return (int) Cart::where(
            'user_id',
            Auth::id()
        )->sum('qty');
    }

    /**
     * Return stock error in the correct format.
     */
    private function stockError(
        Request $request,
        string $message
    ) {
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => false,
                'message' => $message,
            ], 422);
        }

        return back()->with('error', $message);
    }
}
