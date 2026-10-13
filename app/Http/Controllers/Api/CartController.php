<?php

namespace App\Http\Controllers\Api;

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
    public function index(): JsonResponse
    {
        $items = $this->getCartItems();
        return response()->json($this->cartResponsePayload($items, $this->calculateCartTotal($items)));
    }

    public function add(Request $request): JsonResponse
    {
        $validated = $this->validateStrict($request, [
            'varient_id' => ['bail', 'required', 'integer', 'min:1', 'exists:product_varients,id'],
            'qty' => ['bail', 'required', 'integer', 'min:' . $this->minimumQuantity(), 'max:' . $this->maximumQuantity()],
        ]);

        try {
            $result = $this->upsertCartItem((int) $validated['varient_id'], (int) $validated['qty']);
            $items = $this->getCartItems();
            return response()->json(array_merge(
                $this->cartResponsePayload($items, $this->calculateCartTotal($items)),
                ['message' => $result['message']]
            ));
        } catch (ValidationException $e) {
            return $this->validationResponse($e);
        }
    }

    public function buyNow(Request $request): JsonResponse
    {
        $validated = $this->validateStrict($request, [
            'varient_id' => ['bail', 'required', 'integer', 'min:1', 'exists:product_varients,id'],
            'qty' => ['bail', 'required', 'integer', 'min:' . $this->minimumQuantity(), 'max:' . $this->maximumQuantity()],
        ]);

        try {
            $result = $this->upsertCartItem((int) $validated['varient_id'], (int) $validated['qty']);
            $items = $this->getCartItems();
            $payload = $this->cartResponsePayload($items, $this->calculateCartTotal($items));

            return response()->json([
                'success' => true,
                'message' => 'Ready for checkout.',
                'cart_id' => $result['cart_id'],
                'redirect_url' => route('orders.checkout'),
                'cart_count' => $payload['cart_count'],
                'total' => $payload['total'],
                'items' => $payload['items'],
            ]);
        } catch (ValidationException $e) {
            return $this->validationResponse($e);
        }
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $validated = $this->validateStrict($request, [
            'qty' => ['bail', 'required', 'integer', 'min:' . $this->minimumQuantity(), 'max:' . $this->maximumQuantity()],
        ]);

        try {
            DB::transaction(function () use ($validated, $id) {
                $cart = Cart::query()->whereKey($id)->where('user_id', Auth::id())->lockForUpdate()->first();
                if (!$cart) {
                    throw ValidationException::withMessages(['cart' => ['Cart item not found or it does not belong to your account.']]);
                }

                $variant = ProductVarient::query()->whereKey($cart->varient_id)->lockForUpdate()->first();
                if (!$variant) {
                    throw ValidationException::withMessages(['cart' => ['The product variant for this cart item no longer exists.']]);
                }

                $qty = (int) $validated['qty'];
                if ($qty > (int) $variant->qty) {
                    throw ValidationException::withMessages(['qty' => ['Only ' . $variant->qty . ' items are available in stock.']]);
                }

                $cart->update(['qty' => $qty]);
            });

            $items = $this->getCartItems();
            $total = $this->calculateCartTotal($items);
            return response()->json(array_merge(
                $this->cartResponsePayload($items, $total),
                ['message' => 'Cart updated successfully.', 'updated_cart_id' => $id, 'updated_qty' => (int) $validated['qty']]
            ));
        } catch (ValidationException $e) {
            return $this->validationResponse($e);
        }
    }

    public function destroy(int $id): JsonResponse
    {
        $deleted = DB::transaction(function () use ($id) {
            $cart = Cart::query()->whereKey($id)->where('user_id', Auth::id())->lockForUpdate()->first();
            if (!$cart) return false;
            $cart->delete();
            return true;
        });

        if (!$deleted) {
            return response()->json(['success' => false, 'message' => 'Cart item not found or it does not belong to your account.'], 404);
        }

        $items = $this->getCartItems();
        return response()->json(array_merge(
            $this->cartResponsePayload($items, $this->calculateCartTotal($items)),
            ['message' => 'Item removed from cart.']
        ));
    }

    public function clear(): JsonResponse
    {
        Cart::query()->where('user_id', Auth::id())->delete();
        return response()->json(['success' => true, 'message' => 'Cart cleared successfully.', 'cart_count' => 0, 'total' => 0, 'items' => []]);
    }

    public function count(): JsonResponse
    {
        $count = $this->cartCount();
        return response()->json(['success' => true, 'count' => $count, 'cart_count' => $count]);
    }

    private function upsertCartItem(int $variantId, int $qty): array
    {
        return DB::transaction(function () use ($variantId, $qty) {
            $variant = ProductVarient::query()->with('product.dokan')->whereKey($variantId)->lockForUpdate()->first();

            if (!$variant) {
                throw ValidationException::withMessages(['varient_id' => ['The selected product variant does not exist.']]);
            }
            if (!$variant->product) {
                throw ValidationException::withMessages(['varient_id' => ['The selected product is invalid.']]);
            }
            if (!$variant->product->dokan_id) {
                throw ValidationException::withMessages(['varient_id' => ['The selected product has no valid vendor.']]);
            }

            $existing = Cart::query()
                ->where('user_id', Auth::id())
                ->where('varient_id', $variant->id)
                ->lockForUpdate()
                ->first();

            $existingQty = $existing ? (int) $existing->qty : 0;
            $newQty = $existingQty + $qty;

            if ($newQty > $this->maximumQuantity()) {
                throw ValidationException::withMessages(['qty' => ['The maximum allowed quantity for this item is ' . $this->maximumQuantity() . '.']]);
            }
            if ($newQty > (int) $variant->qty) {
                throw ValidationException::withMessages(['qty' => ['Only ' . $variant->qty . ' items are available in stock. You already have ' . $existingQty . ' in your cart.']]);
            }

            if ($existing) {
                $existing->update(['qty' => $newQty]);
                $cart = $existing->fresh();
                $message = 'Cart updated successfully!';
            } else {
                $cart = Cart::create([
                    'user_id' => Auth::id(),
                    'product_id' => $variant->product_id,
                    'varient_id' => $variant->id,
                    'dokan_id' => $variant->product->dokan_id,
                    'qty' => $qty,
                ]);
                $message = 'Item added to cart successfully!';
            }

            return ['cart_id' => (int) $cart->id, 'message' => $message];
        });
    }

    private function getCartItems()
    {
        return Cart::query()
            ->where('user_id', Auth::id())
            ->where('qty', '>', 0)
            ->with(['product', 'varient', 'dokan'])
            ->orderBy('id')
            ->get();
    }

    private function calculateCartTotal($cartItems): float
    {
        return round($cartItems->sum(fn ($item) => $this->calculateItemSubtotal($item)), 2);
    }

    private function calculateItemSubtotal($item): float
    {
        $variant = $item->varient;
        $price = (float) ($variant?->price ?? 0);
        $discount = max(0, min(100, (float) ($variant?->discount ?? 0)));
        $finalPrice = $price - ($price * $discount / 100);
        return round($finalPrice * max(0, (int) $item->qty), 2);
    }

    private function cartResponsePayload($cartItems, float $total): array
    {
        return [
            'success' => true,
            'cart_count' => $this->cartCount(),
            'total' => round($total, 2),
            'items' => $cartItems->map(function ($item) {
                $variant = $item->varient;
                $product = $item->product;
                $dokan = $item->dokan;
                $price = (float) ($variant?->price ?? 0);
                $discount = max(0, min(100, (float) ($variant?->discount ?? 0)));
                $finalPrice = $price - ($price * $discount / 100);
                $quantity = max(0, (int) $item->qty);

                return [
                    'id' => (int) $item->id,
                    'product_id' => (int) $item->product_id,
                    'varient_id' => $item->varient_id !== null ? (int) $item->varient_id : null,
                    'dokan_id' => $item->dokan_id !== null ? (int) $item->dokan_id : null,
                    'qty' => $quantity,
                    'quantity' => $quantity,
                    'unit_price' => round($finalPrice, 2),
                    'original_price' => round($price, 2),
                    'discount' => round($discount, 2),
                    'subtotal' => round($finalPrice * $quantity, 2),
                    'product' => $product,
                    'varient' => $variant,
                    'dokan' => $dokan,
                    'vendor' => $dokan,
                ];
            })->values()->all(),
        ];
    }

    private function cartCount(): int
    {
        return (int) Cart::query()->where('user_id', Auth::id())->where('qty', '>', 0)->sum('qty');
    }

    private function minimumQuantity(): int
    {
        return max(1, (int) config('security.validation.cart.quantity_min', 1));
    }

    private function maximumQuantity(): int
    {
        return max($this->minimumQuantity(), (int) config('security.validation.cart.quantity_max', 9999));
    }

    private function validationResponse(ValidationException $e): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => $this->validationMessage($e),
            'errors' => $e->errors(),
        ], 422);
    }

    private function validationMessage(ValidationException $exception): string
    {
        foreach ($exception->errors() as $messages) {
            if (is_array($messages) && isset($messages[0]) && is_string($messages[0])) {
                return $messages[0];
            }
        }
        return 'The cart request is invalid.';
    }

}
