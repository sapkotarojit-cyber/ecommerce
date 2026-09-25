<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\Product;
use App\Models\ProductVarient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CartController extends Controller
{
    /**
     * Display user's cart
     */
    public function index()
    {
        $cartItems = Cart::where('user_id', Auth::id())
            ->with(['product', 'varient', 'dokan'])
            ->get();

        $total = 0;
        $vendorTotal = [];

        foreach ($cartItems as $item) {
            $price = $item->varient->price ?? 0;
            $discount = $item->varient->discount ?? 0;
            $finalPrice = $price - ($price * $discount / 100);
            $itemTotal = $finalPrice * $item->qty;
            $total += $itemTotal;

            // Group by vendor
            $vendorId = $item->dokan_id;
            if (!isset($vendorTotal[$vendorId])) {
                $vendorTotal[$vendorId] = [
                    'dokan' => $item->dokan,
                    'subtotal' => 0,
                    'items' => []
                ];
            }
            $vendorTotal[$vendorId]['subtotal'] += $itemTotal;
            $vendorTotal[$vendorId]['items'][] = $item;
        }

        return view('frontend.cart.index', compact('cartItems', 'total', 'vendorTotal'));
    }

    /**
     * Add item to cart
     */
    public function add(Request $request)
    {
        $request->validate([
            'varient_id' => 'required|exists:product_varients,id',
            'qty' => 'required|integer|min:1',
        ]);

        $varient = ProductVarient::with('product.dokan')->findOrFail($request->varient_id);

        // Check stock
        if ($varient->qty < $request->qty) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'Not enough stock available. Only ' . $varient->qty . ' left.'], 422);
            }
            return back()->with('error', 'Not enough stock available. Only ' . $varient->qty . ' left.');
        }

        // Check if item already in cart
        $existing = Cart::where('user_id', Auth::id())
            ->where('varient_id', $request->varient_id)
            ->first();

        if ($existing) {
            $newQty = $existing->qty + $request->qty;
            if ($varient->qty < $newQty) {
                if ($request->ajax() || $request->wantsJson()) {
                    return response()->json(['success' => false, 'message' => 'Not enough stock available. You already have ' . $existing->qty . ' in cart.'], 422);
                }
                return back()->with('error', 'Not enough stock available. You already have ' . $existing->qty . ' in cart.');
            }
            $existing->qty = $newQty;
            $existing->save();
            $message = 'Cart updated successfully!';
        } else {
            Cart::create([
                'user_id' => Auth::id(),
                'varient_id' => $request->varient_id,
                'product_id' => $varient->product_id,
                'dokan_id' => $varient->product->dokan_id,
                'qty' => $request->qty,
            ]);
            $message = 'Item added to cart successfully!';
        }

        // Return JSON response if requested via Fetch/AJAX
        if ($request->ajax() || $request->wantsJson()) {
            $cartCount = Cart::where('user_id', Auth::id())->sum('qty');
            return response()->json([
                'success' => true,
                'message' => $message,
                'cart_count' => $cartCount
            ]);
        }

        return redirect()->route('cart.index')->with('success', $message);
    }

    /**
     * Buy now: Add item to cart and redirect straight to checkout.
     */
   public function buyNow(Request $request)
{
    $request->validate([
        'varient_id' => 'required|exists:product_varients,id',
        'qty' => 'required|integer|min:1',
    ]);

    $variant = ProductVarient::with('product.dokan')
        ->findOrFail($request->varient_id);

    $existing = Cart::where('user_id', Auth::id())
        ->where('varient_id', $variant->id)
        ->first();

    $newQty = $request->qty;

    if ($existing) {
        $newQty = $existing->qty + $request->qty;
    }

    /*
     * Make sure total cart quantity doesn't exceed stock.
     */
    if ($newQty > $variant->qty) {

        return response()->json([
            'success' => false,
            'message' => "Only {$variant->qty} items are available in stock. "
                . "You already have "
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
            'dokan_id' => $variant->product->dokan_id ?? null,
            'qty' => $request->qty,
        ]);
    }

    /*
     * Select only this item for checkout.
     */
    $cart = Cart::where('user_id', Auth::id())
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
     * Update cart item quantity
     */
   public function update(Request $request, $id)
{
    $request->validate([
        'qty' => ['required', 'integer', 'min:1'],
    ]);

    $cart = Cart::where('id', $id)
        ->where('user_id', Auth::id())
        ->firstOrFail();

    $variant = ProductVarient::findOrFail($cart->varient_id);

    $qty = (int) $request->qty;

    // Check available stock
    if ($qty > $variant->qty) {
        return response()->json([
            'success' => false,
            'message' => "Only {$variant->qty} items are available in stock.",
        ], 422);
    }

    $cart->update([
        'qty' => $qty,
    ]);

    $cartCount = Cart::where('user_id', Auth::id())->sum('qty');

    return response()->json([
        'success' => true,
        'message' => 'Cart updated successfully.',
        'qty' => $qty,
        'cart_count' => $cartCount,
    ]);
}

    /**
     * Remove item from cart
     */
    public function destroy($id)
    {
        $cartItem = Cart::where('id', $id)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        $cartItem->delete();

        if (request()->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Item removed from cart!'
            ]);
        }

        return redirect()->route('cart.index')->with('success', 'Item removed from cart.');
    }

    /**
     * Clear entire cart
     */
    public function clear()
    {
        Cart::where('user_id', Auth::id())->delete();

        return redirect()->route('cart.index')->with('success', 'Cart cleared successfully!');
    }

    /**
     * Get cart count (for AJAX/API)
     */
    public function count()
    {
        $count = Cart::where('user_id', Auth::id())->sum('qty');

        if (request()->ajax()) {
            return response()->json(['count' => $count]);
        }

        return $count;
    }
}