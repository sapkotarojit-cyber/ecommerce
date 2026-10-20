<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Mail\OrderPlacedMail;
use App\Models\Cart;
use App\Models\Order;
use App\Models\ProductVarient;
use App\Models\ShippingAddress;
use App\Services\ShippingFeeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class CheckoutController extends Controller
{
    /**
     * Save the authenticated user's selected cart items.
     */
    public function postCheckoutSelected(Request $request)
    {
        $validated = $this->validateStrict($request, [
            'selected_items' => [
                'required',
                'array',
                'min:1',
                'max:100',
            ],
            'selected_items.*' => [
                'required',
                'integer',
                'min:1',
                'distinct',
                'exists:carts,id',
            ],
        ]);

        $selectedIds = array_map(
            'intval',
            $validated['selected_items']
        );

        $ids = Cart::query()
            ->where('user_id', Auth::id())
            ->whereIn('id', $selectedIds)
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->all();

        if (
            count($ids) !== count($selectedIds) ||
            count(array_unique($ids)) !== count($ids)
        ) {
            return redirect()
                ->route('cart.index')
                ->with('error', 'One or more selected cart items are invalid.');
        }

        sort($ids);

        session([
            'checkout_cart_ids' => $ids,
        ]);

        /*
         * A new selection is a new checkout attempt.
         * Do not reuse the previous attempt's idempotency marker.
         */
        session()->forget([
            'checkout_pending_group_id',
            'checkout_pending_cart_ids',
            'checkout_pending_payment_method',
        ]);

        return redirect()->route('orders.checkout');
    }

    /**
     * Display checkout.
     */
    public function checkout()
    {
        $ids = session('checkout_cart_ids');

        if (! is_array($ids) || $ids === []) {
            return redirect()
                ->route('cart.index')
                ->with('error', 'Please select items from your cart.');
        }

        $cartItems = Cart::query()
            ->where('user_id', Auth::id())
            ->whereIn('id', $ids)
            ->with(['product', 'varient', 'dokan'])
            ->get();

        if ($cartItems->isEmpty()) {
            session()->forget([
                'checkout_cart_ids',
                'checkout_pending_group_id',
                'checkout_pending_cart_ids',
                'checkout_pending_payment_method',
            ]);

            return redirect()
                ->route('cart.index')
                ->with('error', 'Your selected cart items are empty.');
        }

        $addresses = ShippingAddress::query()
            ->where('user_id', Auth::id())
            ->orderByDesc('is_default_shipping')
            ->latest()
            ->get();

        $vendorTotal = [];
        $grandTotal = 0.0;
        $shippingFee = 0.0;
        $shippingZone = null;
        $selectedAddressId = request('shipping_address_id')
            ?: optional($addresses->firstWhere('is_default_shipping', true))->id
            ?: optional($addresses->first())->id;
        if ($selectedAddressId) {
            $selectedAddress = $addresses->firstWhere('id', (int) $selectedAddressId);
            if ($selectedAddress) {
                try {
                    $quote = app(ShippingFeeService::class)->quote($selectedAddress);
                    $shippingFee = (float) $quote['shipping_fee'];
                    $shippingZone = $quote['zone'];
                } catch (\RuntimeException $e) {
                    session()->flash('shipping_quote_error', $e->getMessage());
                }
            }
        }

        foreach ($cartItems as $item) {
            if (! $item->product || ! $item->varient) {
                continue;
            }

            $price = (float) $item->varient->price;
            $discount = max(
                0,
                min(100, (float) $item->varient->discount)
            );

            $finalPrice = $price - ($price * $discount / 100);
            $quantity = (int) $item->qty;
            $lineTotal = $finalPrice * $quantity;
            $dokanId = (int) ($item->dokan_id ?? 0);

            if ($dokanId <= 0) {
                continue;
            }

            if (! isset($vendorTotal[$dokanId])) {
                $vendorTotal[$dokanId] = [
                    'dokan' => $item->dokan,
                    'subtotal' => 0.0,
                    'items' => [],
                ];
            }

            $vendorTotal[$dokanId]['subtotal'] += $lineTotal;
            $vendorTotal[$dokanId]['items'][] = $item;
            $grandTotal += $lineTotal;
        }

        return view(
            'frontend.orders.checkout',
            compact(
                'cartItems',
                'addresses',
                'vendorTotal',
                'grandTotal',
                'shippingFee',
                'shippingZone',
                'selectedAddressId'
            )
        );
    }

    /** Return a server-calculated shipping quote for the current user's address. */
    public function shippingQuote(Request $request)
    {
        $validated = $this->validateStrict($request, [
            'shipping_address_id' => ['required', 'integer', 'min:1'],
        ]);
        $address = ShippingAddress::query()
            ->whereKey($validated['shipping_address_id'])
            ->where('user_id', (int) Auth::id())
            ->first();
        if (! $address) {
            return response()->json(['message' => 'The selected shipping address is invalid.'], 422);
        }
        try {
            return response()->json(app(ShippingFeeService::class)->quote($address));
        } catch (\RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    /**
     * Create one order per vendor.
     *
     * COD:
     * - Deduct stock in the transaction.
     * - Remove purchased cart rows.
     * - Send order-placed email after successful creation.
     *
     * Bank/eSewa:
     * - Keep cart rows.
     * - Do not deduct stock here.
     * - Keep the checkout selection so the customer can continue payment.
     * - Payment confirmation must finalize stock/cart handling separately.
     */
    public function store(Request $request)
    {
        $validated = $this->validateStrict($request, [
            'shipping_address_id' => [
                'required',
                'integer',
                'min:1',
                'exists:shipping_addresses,id',
            ],
            'payment_method' => [
                'required',
                'string',
                Rule::in(['cod', 'bank', 'esewa']),
            ],
        ]);

        $userId = (int) Auth::id();
        $ids = session('checkout_cart_ids');

        if (! is_array($ids) || $ids === []) {
            return redirect()
                ->route('cart.index')
                ->with('error', 'Please select items first.');
        }

        $ids = array_values(array_unique(array_map('intval', $ids)));

        if (
            count($ids) > 100 ||
            in_array(0, $ids, true) ||
            min($ids) < 1
        ) {
            session()->forget('checkout_cart_ids');

            return redirect()
                ->route('cart.index')
                ->with('error', 'Your checkout selection is invalid. Please select your items again.');
        }

        sort($ids);

        $address = ShippingAddress::query()
            ->whereKey($validated['shipping_address_id'])
            ->where('user_id', $userId)
            ->first();

        if (! $address) {
            return redirect()
                ->route('orders.checkout')
                ->with('error', 'The selected shipping address is invalid.');
        }
        try {
            $shippingQuote = app(ShippingFeeService::class)->quote($address);
        } catch (\RuntimeException $e) {
            return redirect()->route('orders.checkout')->with('error', $e->getMessage());
        }

        /*
         * Reuse an existing pending online checkout when the customer
         * resubmits the same selection in the same session.
         *
         * This prevents ordinary double-clicks and refresh resubmissions
         * from creating another set of pending orders.
         */
        if ($validated['payment_method'] !== 'cod') {
            $existingGroupId = (string) session(
                'checkout_pending_group_id',
                ''
            );

            $existingCartIds = session(
                'checkout_pending_cart_ids',
                []
            );

            $existingMethod = (string) session(
                'checkout_pending_payment_method',
                ''
            );

            if (
                $existingGroupId !== '' &&
                is_array($existingCartIds) &&
                $existingMethod === $validated['payment_method'] &&
                $this->sameIds($ids, $existingCartIds)
            ) {
                $existingOrders = Order::query()
                    ->where('user_id', $userId)
                    ->where('payment_group_id', $existingGroupId)
                    ->where('payment_method', $validated['payment_method'])
                    ->where('shipping_address_id', $address->id)
                    ->where('payment_status', 'pending')
                    ->orderBy('id')
                    ->get();

                if ($existingOrders->isNotEmpty()) {
                    return $this->redirectToPayment(
                        $validated['payment_method'],
                        $existingOrders->first()->id
                    );
                }
            }
        }

        try {
            $orders = DB::transaction(function () use (
                $ids,
                $address,
                $validated,
                $userId,
                $shippingQuote
            ) {
                $items = Cart::query()
                    ->where('user_id', $userId)
                    ->whereIn('id', $ids)
                    ->orderBy('id')
                    ->lockForUpdate()
                    ->with(['product', 'varient', 'dokan'])
                    ->get();

                if (
                    $items->count() !== count($ids) ||
                    $items->isEmpty()
                ) {
                    throw new \RuntimeException(
                        'One or more selected cart items are no longer available. Please review your cart.'
                    );
                }

                $paymentGroupId = (string) Str::uuid();

                $itemsByVendor = $items->groupBy(
                    fn ($item) => (string) ($item->dokan_id ?? 0)
                );

                $createdOrders = [];
                $shippingAssigned = false;

                foreach ($itemsByVendor as $dokanId => $vendorItems) {
                    $dokanId = (int) $dokanId;

                    if ($dokanId < 1) {
                        throw new \RuntimeException(
                            'One or more products do not have a valid vendor.'
                        );
                    }

                    $total = 0.0;
                    $lockedVariants = [];

                    /*
                     * Lock and validate each variant before creating its order.
                     */
                    foreach ($vendorItems as $item) {
                        if (
                            ! is_numeric($item->qty) ||
                            (int) $item->qty < 1 ||
                            (int) $item->qty > 100
                        ) {
                            throw new \RuntimeException(
                                'One or more cart quantities are invalid.'
                            );
                        }

                        if (! $item->product || ! $item->varient) {
                            throw new \RuntimeException(
                                'A selected product or variant is no longer available.'
                            );
                        }

                        $quantity = (int) $item->qty;

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
                            $productName = $item->product->title ?: 'Product';

                            throw new \RuntimeException(
                                "{$productName} does not have enough stock. "
                                . "Only {$variant->qty} available."
                            );
                        }

                        $price = (float) $variant->price;

                        if (! is_finite($price) || $price < 0) {
                            throw new \RuntimeException(
                                'A selected product has an invalid price.'
                            );
                        }

                        $discount = max(
                            0,
                            min(100, (float) $variant->discount)
                        );

                        $finalPrice = $price - ($price * $discount / 100);
                        $lineTotal = round($finalPrice * $quantity, 2);

                        $total += $lineTotal;

                        $lockedVariants[$item->id] = [
                            'variant' => $variant,
                            'quantity' => $quantity,
                            'final_price' => $finalPrice,
                        ];
                    }

                    $total = round($total, 2);

                    if (! is_finite($total) || $total <= 0) {
                        throw new \RuntimeException(
                            'Order total must be greater than zero.'
                        );
                    }

                    // Charge shipping once across the vendor orders in this checkout.
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
                         * Only COD finalizes stock/cart during checkout.
                         * Online payment is finalized after gateway/admin
                         * verification in the corresponding payment handler.
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

                            $item->delete();
                        }
                    }

                    if ($validated['payment_method'] === 'cod') {
                        $order->forceFill([
                            'stock_deducted_at' => now(),
                        ])->save();
                    }

                    $createdOrders[] = $order;
                }

                return $createdOrders;
            });
        } catch (\Throwable $e) {
            report($e);

            return redirect()
                ->route('orders.checkout')
                ->with(
                    'error',
                    $e instanceof \RuntimeException
                        ? $e->getMessage()
                        : 'Unable to create your order. Please try again.'
                );
        }

        if ($orders === []) {
            return redirect()
                ->route('orders.checkout')
                ->with('error', 'No orders were created. Please try again.');
        }

        // The transaction succeeded; query-builder stock deductions
        // require explicit catalog-cache invalidation.
        if ($validated['payment_method'] === 'cod') {
            app(\App\Services\CatalogCache::class)->invalidateAfterCommit();
        }

        $order = $orders[0];

        /*
         * COD has been finalized in the transaction. Send emails only after
         * the transaction commits; a mail failure must not undo the order.
         */
        if ($validated['payment_method'] === 'cod') {
            session()->forget([
                'checkout_cart_ids',
                'checkout_pending_group_id',
                'checkout_pending_cart_ids',
                'checkout_pending_payment_method',
            ]);

            foreach ($orders as $placedOrder) {
                $placedOrder->load([
                    'user',
                    'dokan',
                    'shippingAddress',
                    'orderItems.product',
                    'orderItems.varient',
                ]);

                if (
                    $placedOrder->user &&
                    filter_var(
                        $placedOrder->user->email,
                        FILTER_VALIDATE_EMAIL
                    )
                ) {
                    try {
                        Mail::to($placedOrder->user->email)
                            ->send(new OrderPlacedMail($placedOrder));
                    } catch (\Throwable $e) {
                        report($e);
                    }
                }
            }

            if (count($orders) > 1) {
                return redirect()
                    ->route('orders.index')
                    ->with('success', 'Your COD orders were placed successfully.');
            }

            return redirect()
                ->route('orders.show', $order->id)
                ->with('success', 'Order placed successfully with Cash on Delivery.');
        }

        /*
         * Preserve cart rows, stock, and checkout selection for online payment.
         * Keep a session marker so resubmitting the same checkout redirects
         * to the existing pending group instead of creating another group.
         */
        session([
            'checkout_pending_group_id' => (string) $order->payment_group_id,
            'checkout_pending_cart_ids' => $ids,
            'checkout_pending_payment_method' => $validated['payment_method'],
        ]);

        return $this->redirectToPayment(
            $validated['payment_method'],
            $order->id
        );
    }

    /**
     * Redirect to the appropriate payment page.
     */
    private function redirectToPayment(string $paymentMethod, int $orderId)
    {
        if ($paymentMethod === 'esewa') {
            return redirect()->route('orders.esewa.pay', $orderId);
        }

        if ($paymentMethod === 'bank') {
            return redirect()->route('orders.bank.pay', $orderId);
        }

        return redirect()->route('orders.show', $orderId);
    }

    /**
     * Compare selected cart IDs without relying on order.
     */
    private function sameIds(array $first, array $second): bool
    {
        $first = array_values(array_unique(array_map('intval', $first)));
        $second = array_values(array_unique(array_map('intval', $second)));

        sort($first);
        sort($second);

        return $first === $second;
    }
}