<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\ProductVarient;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CartController extends Controller
{
    /**
     * Display the authenticated user's cart.
     */
    public function index()
    {
        $cartItems = $this->getCartItems();

        $total = $this->calculateCartTotal($cartItems);

        $vendorTotal = [];

        foreach ($cartItems as $item) {
            $itemTotal = $this->calculateItemSubtotal($item);

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

        /*
        |--------------------------------------------------------------------------
        | API / AJAX response
        |--------------------------------------------------------------------------
        */

        if (request()->wantsJson() || request()->ajax()) {
            return response()->json(
                $this->cartResponsePayload(
                    $cartItems,
                    $total
                )
            );
        }

        return view(
            'frontend.cart.index',
            compact(
                'cartItems',
                'total',
                'vendorTotal'
            )
        );
    }

    /**
     * Add item to cart.
     */
    public function add(Request $request)
    {
        $maximumQuantity = $this->maximumQuantity();

        $validated = $this->validateStrict($request, [
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
                'min:' . $this->minimumQuantity(),
                'max:' . $maximumQuantity,
            ],
        ]);

        try {
            $result = DB::transaction(function () use ($validated) {
                /*
                |--------------------------------------------------------------------------
                | Lock the variant so concurrent cart changes cannot
                | race against the stock value.
                |--------------------------------------------------------------------------
                */

                $variant = ProductVarient::query()
                    ->with('product.dokan')
                    ->whereKey($validated['varient_id'])
                    ->lockForUpdate()
                    ->first();

                if (! $variant) {
                    throw ValidationException::withMessages([
                        'varient_id' =>
                            'The selected product variant does not exist.',
                    ]);
                }

                if (! $variant->product) {
                    throw ValidationException::withMessages([
                        'varient_id' =>
                            'The selected product is invalid.',
                    ]);
                }

                if (! $variant->product->dokan_id) {
                    throw ValidationException::withMessages([
                        'varient_id' =>
                            'The selected product has no valid vendor.',
                    ]);
                }

                $qty = (int) $validated['qty'];

                if ((int) $variant->qty < $qty) {
                    throw ValidationException::withMessages([
                        'qty' =>
                            'Not enough stock available. Only '
                            . $variant->qty
                            . ' left.',
                    ]);
                }

                $existing = Cart::query()
                    ->where('user_id', Auth::id())
                    ->where('varient_id', $variant->id)
                    ->lockForUpdate()
                    ->first();

                if ($existing) {
                    $newQty = (int) $existing->qty + $qty;

                    if ($newQty > $this->maximumQuantity()) {
                        throw ValidationException::withMessages([
                            'qty' =>
                                'The maximum allowed quantity for this item is '
                                . $this->maximumQuantity()
                                . '.',
                        ]);
                    }

                    if ((int) $variant->qty < $newQty) {
                        throw ValidationException::withMessages([
                            'qty' =>
                                'Not enough stock available. You already have '
                                . $existing->qty
                                . ' in your cart and only '
                                . $variant->qty
                                . ' are available.',
                        ]);
                    }

                    $existing->update([
                        'qty' => $newQty,
                    ]);

                    $cartItem = $existing;
                    $message = 'Cart updated successfully!';
                } else {
                    $cartItem = Cart::create([
                        'user_id' => Auth::id(),
                        'varient_id' => $variant->id,
                        'product_id' => $variant->product_id,
                        'dokan_id' => $variant->product->dokan_id,
                        'qty' => $qty,
                    ]);

                    $message = 'Item added to cart successfully!';
                }

                return [
                    'cart_item' => $cartItem,
                    'message' => $message,
                ];
            });

            $cartItems = $this->getCartItems();

            $total = $this->calculateCartTotal($cartItems);

            return response()->json(
                array_merge(
                    $this->cartResponsePayload(
                        $cartItems,
                        $total
                    ),
                    [
                        'message' => $result['message'],
                    ]
                )
            );
        } catch (ValidationException $e) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => $this->validationMessage($e),
                    'errors' => $e->errors(),
                ], 422);
            }

            throw $e;
        }
    }

    /**
     * Buy now.
     */
    public function buyNow(Request $request)
    {
        $maximumQuantity = $this->maximumQuantity();

        $validated = $this->validateStrict($request, [
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
                'min:' . $this->minimumQuantity(),
                'max:' . $maximumQuantity,
            ],
        ]);

        try {
            $cart = DB::transaction(function () use ($validated) {
                $variant = ProductVarient::query()
                    ->with('product.dokan')
                    ->whereKey($validated['varient_id'])
                    ->lockForUpdate()
                    ->first();

                if (! $variant) {
                    throw ValidationException::withMessages([
                        'varient_id' =>
                            'The selected product variant does not exist.',
                    ]);
                }

                if (! $variant->product) {
                    throw ValidationException::withMessages([
                        'varient_id' =>
                            'The selected product is invalid.',
                    ]);
                }

                if (! $variant->product->dokan_id) {
                    throw ValidationException::withMessages([
                        'varient_id' =>
                            'The selected product has no valid vendor.',
                    ]);
                }

                $qty = (int) $validated['qty'];

                $existing = Cart::query()
                    ->where('user_id', Auth::id())
                    ->where('varient_id', $variant->id)
                    ->lockForUpdate()
                    ->first();

                $existingQty = $existing
                    ? (int) $existing->qty
                    : 0;

                $newQty = $existingQty + $qty;

                if ($newQty > $this->maximumQuantity()) {
                    throw ValidationException::withMessages([
                        'qty' =>
                            'The maximum allowed quantity for this item is '
                            . $this->maximumQuantity()
                            . '.',
                    ]);
                }

                if ($newQty > (int) $variant->qty) {
                    throw ValidationException::withMessages([
                        'qty' =>
                            'Only '
                            . $variant->qty
                            . ' items are available in stock. You already have '
                            . $existingQty
                            . ' in your cart.',
                    ]);
                }

                if ($existing) {
                    $existing->update([
                        'qty' => $newQty,
                    ]);

                    $cart = $existing;
                } else {
                    $cart = Cart::create([
                        'user_id' => Auth::id(),
                        'product_id' => $variant->product_id,
                        'varient_id' => $variant->id,
                        'dokan_id' => $variant->product->dokan_id,
                        'qty' => $qty,
                    ]);
                }

                return $cart;
            });

            session([
                'checkout_cart_ids' => [$cart->id],
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Ready for checkout.',
                'cart_id' => $cart->id,
                'redirect_url' => route('orders.checkout'),
            ]);
        } catch (ValidationException $e) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => $this->validationMessage($e),
                    'errors' => $e->errors(),
                ], 422);
            }

            throw $e;
        }
    }

    /**
     * Update cart quantity.
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $validated = $this->validateStrict($request, [
            'qty' => [
                'bail',
                'required',
                'integer',
                'min:' . $this->minimumQuantity(),
                'max:' . $this->maximumQuantity(),
            ],
        ]);

        try {
            $cart = DB::transaction(function () use (
                $validated,
                $id
            ) {
                /*
                |--------------------------------------------------------------------------
                | IMPORTANT:
                | Keep both cart ID and authenticated user ID in this query.
                | This prevents one user from modifying another user's cart.
                |--------------------------------------------------------------------------
                */

                $cart = Cart::query()
                    ->whereKey($id)
                    ->where('user_id', Auth::id())
                    ->lockForUpdate()
                    ->first();

                if (! $cart) {
                    throw ValidationException::withMessages([
                        'cart' =>
                            'Cart item not found or it does not belong to your account.',
                    ]);
                }

                /*
                |--------------------------------------------------------------------------
                | Lock variant before checking stock.
                |--------------------------------------------------------------------------
                */

                $variant = ProductVarient::query()
                    ->whereKey($cart->varient_id)
                    ->lockForUpdate()
                    ->first();

                if (! $variant) {
                    throw ValidationException::withMessages([
                        'cart' =>
                            'The product variant for this cart item no longer exists.',
                    ]);
                }

                $qty = (int) $validated['qty'];

                if ($qty > (int) $variant->qty) {
                    throw ValidationException::withMessages([
                        'qty' =>
                            'Only '
                            . $variant->qty
                            . ' items are available in stock.',
                    ]);
                }

                $cart->update([
                    'qty' => $qty,
                ]);

                return $cart;
            });

            $cartItems = $this->getCartItems();

            $total = $this->calculateCartTotal($cartItems);

            return response()->json(
                array_merge(
                    $this->cartResponsePayload(
                        $cartItems,
                        $total
                    ),
                    [
                        'message' =>
                            'Cart updated successfully.',
                        'updated_cart_id' => $cart->id,
                        'updated_qty' => (int) $cart->qty,
                    ]
                )
            );
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => $this->validationMessage($e),
                'errors' => $e->errors(),
            ], 422);
        }
    }

    /**
     * Remove item from cart.
     */
    public function destroy(Request $request, int $id)
    {
        $deleted = false;

        DB::transaction(function () use (
            $id,
            &$deleted
        ) {
            $cartItem = Cart::query()
                ->whereKey($id)
                ->where('user_id', Auth::id())
                ->lockForUpdate()
                ->first();

            if (! $cartItem) {
                return;
            }

            $cartItem->delete();

            $deleted = true;
        });

        if (! $deleted) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cart item not found or it does not belong to your account.',
                ], 404);
            }

            return redirect()->route('cart.index')
                ->with('error', 'Cart item not found or it does not belong to your account.');
        }

        $cartItems = $this->getCartItems();

        $total = $this->calculateCartTotal($cartItems);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json(
                array_merge(
                    $this->cartResponsePayload($cartItems, $total),
                    ['message' => 'Item removed from cart.']
                )
            );
        }

        return redirect()->route('cart.index')
            ->with('success', 'Item removed from cart.');
    }

    /**
     * Clear the authenticated user's entire cart.
     */
    public function clear(Request $request)
    {
        Cart::query()
            ->where('user_id', Auth::id())
            ->delete();

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Cart cleared successfully.',
                'cart_count' => 0,
                'total' => 0,
                'items' => [],
            ]);
        }

        return redirect()->route('cart.index')
            ->with('success', 'Cart cleared successfully.');
    }

    /**
     * Get cart count.
     */
    public function count()
    {
        $count = $this->cartCount();

        if (request()->ajax() || request()->wantsJson()) {
            return response()->json([
                'success' => true,
                'count' => $count,
                'cart_count' => $count,
            ]);
        }

        return $count;
    }

    /**
     * Get authenticated user's cart.
     */
    private function getCartItems()
    {
        return Cart::query()
            ->where('user_id', Auth::id())
            ->with([
                'product',
                'varient',
                'dokan',
            ])
            ->orderBy('id')
            ->get();
    }

    /**
     * Calculate entire cart total.
     */
    private function calculateCartTotal($cartItems): float
    {
        $total = 0.0;

        foreach ($cartItems as $item) {
            $total += $this->calculateItemSubtotal($item);
        }

        return round($total, 2);
    }

    /**
     * Calculate one cart item's current price.
     */
    private function calculateItemSubtotal($item): float
    {
        $variant = $item->varient;

        $price = (float) ($variant?->price ?? 0);

        $discount = (float) ($variant?->discount ?? 0);

        $discount = max(
            0,
            min(100, $discount)
        );

        $finalPrice = $price - (
            $price * $discount / 100
        );

        $quantity = max(
            0,
            (int) $item->qty
        );

        return round(
            $finalPrice * $quantity,
            2
        );
    }

    /**
     * Build the API cart payload.
     *
     * This is deliberately consistent across:
     * index
     * update
     * delete
     * add
     * clear
     */
    private function cartResponsePayload(
        $cartItems,
        float $total
    ): array {
        return [
            'success' => true,

            'cart_count' => $this->cartCount(),

            'total' => round($total, 2),

            'items' => $cartItems
                ->map(function ($item) {
                    $variant = $item->varient;
                    $product = $item->product;
                    $dokan = $item->dokan;

                    $price = (float) (
                        $variant?->price ?? 0
                    );

                    $discount = (float) (
                        $variant?->discount ?? 0
                    );

                    $discount = max(
                        0,
                        min(100, $discount)
                    );

                    $finalPrice = $price - (
                        $price * $discount / 100
                    );

                    $quantity = max(
                        0,
                        (int) $item->qty
                    );

                    return [
                        'id' => (int) $item->id,

                        'product_id' =>
                            (int) $item->product_id,

                        'varient_id' =>
                            (int) $item->varient_id,

                        'dokan_id' =>
                            $item->dokan_id !== null
                                ? (int) $item->dokan_id
                                : null,

                        'qty' => $quantity,

                        'quantity' => $quantity,

                        'unit_price' =>
                            round($finalPrice, 2),

                        'original_price' =>
                            round($price, 2),

                        'discount' =>
                            round($discount, 2),

                        'subtotal' =>
                            round(
                                $finalPrice * $quantity,
                                2
                            ),

                        'product' => $product,

                        'varient' => $variant,

                        'dokan' => $dokan,

                        /*
                        |--------------------------------------------------------------------------
                        | Some Flutter models may call the vendor "vendor".
                        | Returning both names keeps the API backwards compatible.
                        |--------------------------------------------------------------------------
                        */

                        'vendor' => $dokan,
                    ];
                })
                ->values()
                ->all(),
        ];
    }

    /**
     * Current cart quantity count.
     */
    private function cartCount(): int
    {
        return (int) Cart::query()
            ->where('user_id', Auth::id())
            ->sum('qty');
    }

    /**
     * Configurable minimum quantity.
     */
    private function minimumQuantity(): int
    {
        return max(
            1,
            (int) config(
                'security.validation.cart.quantity_min',
                1
            )
        );
    }

    /**
     * Configurable maximum quantity.
     */
    private function maximumQuantity(): int
    {
        return max(
            $this->minimumQuantity(),
            (int) config(
                'security.validation.cart.quantity_max',
                9999
            )
        );
    }

    /**
     * Extract a safe validation message.
     */
    private function validationMessage(
        ValidationException $exception
    ): string {
        $errors = $exception->errors();

        foreach ($errors as $messages) {
            if (
                is_array($messages) &&
                isset($messages[0]) &&
                is_string($messages[0])
            ) {
                return $messages[0];
            }
        }

        return 'The cart request is invalid.';
    }

    /**
     * Return stock error in a consistent format.
     */
    private function stockError(
        Request $request,
        string $message
    ) {
        if (
            $request->ajax() ||
            $request->wantsJson()
        ) {
            return response()->json([
                'success' => false,
                'message' => $message,
            ], 422);
        }

        return back()->with(
            'error',
            $message
        );
    }
}