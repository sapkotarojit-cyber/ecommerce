<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Mail\OrderPlacedMail;
use App\Models\Cart;
use App\Models\Order;
use App\Models\ProductVarient;
use App\Models\ShippingAddress;
use App\Services\ShippingFeeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class CheckoutController extends Controller
{
    /**
     * Create vendor orders from the authenticated user's selected cart items.
     *
     * COD:
     * - Validate stock.
     * - Deduct stock.
     * - Delete the purchased cart rows.
     * - Send OrderPlacedMail after the transaction commits.
     *
     * Bank/eSewa:
     * - Validate current stock.
     * - Keep stock unchanged.
     * - Keep the cart rows unchanged.
     * - Do not send a success email before payment confirmation.
     *
     * Online payment confirmation must be handled by the corresponding
     * verified payment callback/admin approval endpoint.
     */
    public function shippingQuote(Request $request, ShippingFeeService $shippingFees): JsonResponse
    {
        $validated = $this->validateStrict($request, [
            'shipping_address_id' => ['required', 'integer', 'min:1'],
        ]);
        $address = ShippingAddress::query()
            ->whereKey($validated['shipping_address_id'])
            ->where('user_id', (int) $request->user()->id)
            ->first();
        if (!$address) {
            return response()->json(['message' => 'The selected shipping address is invalid.'], 422);
        }
        try {
            return response()->json($shippingFees->quote($address));
        } catch (\RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $this->validateStrict($request, [
            'shipping_address_id' => [
                'required',
                'integer',
                'min:1',
            ],

            'payment_method' => [
                'required',
                'string',
                'max:20',
                Rule::in([
                    'cod',
                    'bank',
                    'esewa',
                ]),
            ],

            'cart_item_ids' => [
                'required',
                'array',
                'min:1',
                'max:' . max(
                    1,
                    (int) config(
                        'security.validation.cart.selection_max',
                        100
                    )
                ),
            ],

            'cart_item_ids.*' => [
                'required',
                'integer',
                'min:1',
                'distinct',
            ],
        ]);

        $userId = (int) Auth::id();

        if ($userId < 1) {
            return response()->json([
                'message' => 'Authentication is required.',
            ], 401);
        }

        $selectedCartItemIds = array_values(
            array_map(
                'intval',
                $validated['cart_item_ids']
            )
        );

        if (
            $selectedCartItemIds === [] ||
            count($selectedCartItemIds) !== count(
                array_unique($selectedCartItemIds)
            )
        ) {
            return response()->json([
                'message' => 'The selected cart items are invalid.',
            ], 422);
        }

        /*
         * Confirm that the shipping address belongs to the authenticated user.
         */
        $address = ShippingAddress::query()
            ->whereKey($validated['shipping_address_id'])
            ->where('user_id', $userId)
            ->first();

        if (!$address) {
            return response()->json([
                'message' => 'The selected shipping address is invalid.',
            ], 422);
        }

        try {
            $shippingQuote = app(ShippingFeeService::class)->quote($address);
        } catch (\RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        try {
            $result = DB::transaction(function () use (
                $userId,
                $address,
                $validated,
                $selectedCartItemIds,
                $shippingQuote
            ) {
                /*
                 * Lock only this user's selected cart rows.
                 */
                $items = Cart::query()
                    ->where('user_id', $userId)
                    ->whereIn('id', $selectedCartItemIds)
                    ->orderBy('id')
                    ->lockForUpdate()
                    ->with([
                        'product',
                        'varient',
                        'dokan',
                    ])
                    ->get();

                if (
                    $items->count() !== count($selectedCartItemIds) ||
                    $items->isEmpty()
                ) {
                    throw new \RuntimeException(
                        'One or more selected cart items are invalid, unavailable, or no longer in your cart.'
                    );
                }

                /*
                 * Every vendor order from this checkout shares one payment group.
                 */
                $paymentGroupId = (string) Str::uuid();

                $itemsByVendor = $items->groupBy(
                    fn ($item) => (string) ($item->dokan_id ?? 0)
                );

                $createdOrders = [];
                $shippingAssigned = false;

                $minimumQuantity = max(
                    1,
                    (int) config(
                        'security.validation.cart.quantity_min',
                        1
                    )
                );

                $maximumQuantity = max(
                    $minimumQuantity,
                    (int) config(
                        'security.validation.cart.quantity_max',
                        9999
                    )
                );

                foreach ($itemsByVendor as $dokanId => $vendorItems) {
                    $dokanId = (int) $dokanId;

                    if ($dokanId < 1) {
                        throw new \RuntimeException(
                            'One or more selected products do not have a valid vendor.'
                        );
                    }

                    $total = 0.0;
                    $lockedVariants = [];

                    /*
                     * Validate each cart item, variant, price, and stock quantity.
                     */
                    foreach ($vendorItems as $item) {
                        $quantity = filter_var(
                            $item->qty,
                            FILTER_VALIDATE_INT
                        );

                        if (
                            $quantity === false ||
                            $quantity < $minimumQuantity ||
                            $quantity > $maximumQuantity
                        ) {
                            throw new \RuntimeException(
                                'One or more cart quantities are invalid.'
                            );
                        }

                        if (
                            !$item->product ||
                            !$item->varient ||
                            !$item->varient_id
                        ) {
                            throw new \RuntimeException(
                                'A selected product or variant is no longer available.'
                            );
                        }

                        $variant = ProductVarient::query()
                            ->whereKey($item->varient_id)
                            ->lockForUpdate()
                            ->first();

                        if (!$variant) {
                            throw new \RuntimeException(
                                'A selected product variant could not be found.'
                            );
                        }

                        if (
                            (int) $variant->product_id !==
                            (int) $item->product_id
                        ) {
                            throw new \RuntimeException(
                                'Invalid product configuration detected.'
                            );
                        }

                        if ((int) $variant->qty < $quantity) {
                            $productName =
                                $item->product->title ?: 'Product';

                            throw new \RuntimeException(
                                "{$productName} does not have enough stock. "
                                . "Only {$variant->qty} available."
                            );
                        }

                        $price = (float) $variant->price;

                        if (!is_finite($price) || $price < 0) {
                            throw new \RuntimeException(
                                'A selected product has an invalid price.'
                            );
                        }

                        $discount = max(
                            0,
                            min(100, (float) $variant->discount)
                        );

                        $finalPrice = $price - (
                            $price * $discount / 100
                        );

                        $lineTotal = round(
                            $finalPrice * $quantity,
                            2
                        );

                        $total += $lineTotal;

                        $lockedVariants[$item->id] = [
                            'variant' => $variant,
                            'quantity' => $quantity,
                            'final_price' => $finalPrice,
                        ];
                    }

                    $total = round($total, 2);

                    if (!is_finite($total) || $total <= 0) {
                        throw new \RuntimeException(
                            'Order total must be greater than zero.'
                        );
                    }

                    /*
                     * Create the vendor-specific order.
                     */
                    // One shipping charge per checkout: attach it to the first vendor order only.
                    $orderShippingFee = $shippingAssigned ? 0.00 : (float) $shippingQuote['shipping_fee'];
                    $shippingAssigned = true;
                    $order = Order::create([
                        'user_id' => $userId,
                        'dokan_id' => $dokanId,
                        'shipping_address_id' => $address->id,
                        'shipping_fee' => $orderShippingFee,
                        'total_amount' => round($total + $orderShippingFee, 2),
                        'status' => 'pending',
                        'order_status' => 'pending',
                        'payment_method' => $validated['payment_method'],
                        'payment_status' => 'pending',
                        'payment_group_id' => $paymentGroupId,
                    ]);

                    foreach ($vendorItems as $item) {
                        $locked = $lockedVariants[$item->id];

                        /** @var ProductVarient $variant */
                        $variant = $locked['variant'];

                        $quantity = $locked['quantity'];
                        $finalPrice = $locked['final_price'];

                        $order->orderItems()->create([
                            'product_id' => $item->product_id,
                            'varient_id' => $item->varient_id,
                            'qty' => $quantity,
                            'amount' => round(
                                $finalPrice * $quantity,
                                2
                            ),
                        ]);

                        /*
                         * Remove the selected cart row for every payment method.
                         * Pending bank/eSewa orders are restored to the cart only
                         * if the order is explicitly cancelled.
                         *
                         * COD deducts inventory immediately; online payment
                         * inventory is deducted only after server-side verification.
                         */
                        if ($validated['payment_method'] === 'cod') {
                            $updated = ProductVarient::query()
                                ->whereKey($variant->id)
                                ->where('qty', '>=', $quantity)
                                ->decrement('qty', $quantity);

                            if ($updated !== 1) {
                                throw new \RuntimeException(
                                    'Stock changed during checkout. Please review your cart and try again.'
                                );
                            }
                        }

                        $deleted = Cart::query()
                            ->whereKey($item->id)
                            ->where('user_id', $userId)
                            ->delete();

                        if ($deleted !== 1) {
                            throw new \RuntimeException(
                                'A selected cart item changed during checkout. Please try again.'
                            );
                        }
                    }

                    $order->forceFill([
                        'cart_removed_at' => now(),
                    ])->save();

                    if ($validated['payment_method'] === 'cod') {
                        $order->forceFill([
                            'stock_deducted_at' => now(),
                        ])->save();
                    }

                    $createdOrders[] = $order;
                }

                return [
                    'payment_group_id' => $paymentGroupId,
                    'orders' => $createdOrders,
                ];
            });
        } catch (\RuntimeException $e) {
            /*
             * Validation/business-rule failures are client-correctable.
             * Do not log the complete request, which may contain personal data.
             */
            return response()->json([
                'message' => $e->getMessage(),
            ], 422);
        } catch (\Throwable $e) {
            Log::error('Flutter checkout failed.', [
                'user_id' => $userId,
                'payment_method' => $validated['payment_method'],
                'exception' => $e->getMessage(),
            ]);

            return response()->json([
                'message' => 'Unable to create your order. Please try again.',
            ], 500);
        }

        // The checkout transaction has committed. Query-builder stock
        // deductions bypass Eloquent observers, so invalidate explicitly.
        if ($validated['payment_method'] === 'cod') {
            app(\App\Services\CatalogCache::class)->invalidateAfterCommit();
        }

        /*
         * Only COD has been finalized at this point.
         * Email errors must not undo a committed order.
         */
        if ($validated['payment_method'] === 'cod') {
            foreach ($result['orders'] as $createdOrder) {
                try {
                    $order = Order::query()
                        ->with([
                            'user',
                            'dokan',
                            'shippingAddress',
                            'orderItems.product',
                            'orderItems.varient',
                        ])
                        ->find($createdOrder->id);

                    $email = $order?->user?->email;

                    if (
                        !is_string($email) ||
                        !filter_var($email, FILTER_VALIDATE_EMAIL)
                    ) {
                        continue;
                    }

                    Mail::to($email)->send(
                        new OrderPlacedMail($order)
                    );
                } catch (\Throwable $e) {
                    Log::error(
                        'Flutter COD order email failed.',
                        [
                            'order_id' => $createdOrder->id,
                            'exception' => $e->getMessage(),
                        ]
                    );
                }
            }
        }

        $orders = collect($result['orders'])
            ->map(function (Order $order) {
                return $order->load([
                    'orderItems.product',
                    'orderItems.varient',
                    'shippingAddress',
                    'dokan',
                ]);
            });

        $isCod = $validated['payment_method'] === 'cod';

        return response()->json([
            'message' => $isCod
                ? 'COD order placed successfully.'
                : 'Order created. Payment is still pending.',
            'payment_required' => !$isCod,
            'payment_method' => $validated['payment_method'],
            'payment_group_id' => $result['payment_group_id'],
            'shipping_fee' => (float) $orders->sum(fn (Order $order) => (float) $order->shipping_fee),
            'subtotal' => round((float) $orders->sum(fn (Order $order) => (float) $order->total_amount) - (float) $orders->sum(fn (Order $order) => (float) $order->shipping_fee), 2),
            'total_amount' => round((float) $orders->sum(fn (Order $order) => (float) $order->total_amount), 2),
            'shipping_zone' => $shippingQuote['zone'],
            'orders' => $orders,
        ], 201);
    }
}