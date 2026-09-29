<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Mail\OrderPlacedMail;
use App\Models\Cart;
use App\Models\Order;
use App\Models\ProductVarient;
use App\Models\ShippingAddress;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rule;

class CheckoutController extends Controller
{
    /**
     * Select cart items for checkout.
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

        $ids = Cart::query()
            ->where('user_id', Auth::id())
            ->whereIn(
                'id',
                $validated['selected_items']
            )
            ->pluck('id')
            ->toArray();

        if (
            count($ids) !==
            count(
                array_unique(
                    $validated['selected_items']
                )
            )
        ) {
            return redirect()
                ->route('cart.index')
                ->with(
                    'error',
                    'One or more selected cart items are invalid.'
                );
        }

        session([
            'checkout_cart_ids' => $ids,
        ]);

        return redirect()
            ->route('orders.checkout');
    }

    /**
     * Display checkout page.
     */
    public function checkout()
    {
        $ids = session('checkout_cart_ids');

        if (
            !is_array($ids) ||
            empty($ids)
        ) {
            return redirect()
                ->route('cart.index')
                ->with(
                    'error',
                    'Please select items from your cart.'
                );
        }

        $cartItems = Cart::query()
            ->where('user_id', Auth::id())
            ->whereIn('id', $ids)
            ->with([
                'product',
                'varient',
                'dokan',
            ])
            ->get();

        if ($cartItems->isEmpty()) {
            session()->forget(
                'checkout_cart_ids'
            );

            return redirect()
                ->route('cart.index')
                ->with(
                    'error',
                    'Your selected cart items are empty.'
                );
        }

        $addresses =
            ShippingAddress::query()
                ->where(
                    'user_id',
                    Auth::id()
                )
                ->orderByDesc(
                    'is_default_shipping'
                )
                ->latest()
                ->get();

        $vendorTotal = [];
        $grandTotal = 0.0;

        foreach ($cartItems as $item) {
            if (
                !$item->varient ||
                !$item->product
            ) {
                continue;
            }

            $price =
                (float) $item->varient->price;

            $discount =
                max(
                    0,
                    min(
                        100,
                        (float) $item->varient->discount
                    )
                );

            $finalPrice =
                $price -
                (
                    $price *
                    $discount /
                    100
                );

            $quantity =
                (int) $item->qty;

            $total =
                $finalPrice *
                $quantity;

            $dokanId =
                (int) ($item->dokan_id ?? 0);

            if ($dokanId <= 0) {
                continue;
            }

            if (
                !isset(
                    $vendorTotal[$dokanId]
                )
            ) {
                $vendorTotal[$dokanId] = [
                    'dokan' =>
                        $item->dokan,
                    'subtotal' => 0,
                    'items' => [],
                ];
            }

            $vendorTotal[$dokanId][
                'subtotal'
            ] += $total;

            $vendorTotal[$dokanId][
                'items'
            ][] = $item;

            $grandTotal += $total;
        }

        return view(
            'frontend.orders.checkout',
            compact(
                'cartItems',
                'addresses',
                'vendorTotal',
                'grandTotal'
            )
        );
    }

    /**
     * Create order(s).
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
                Rule::in([
                    'cod',
                    'bank',
                    'esewa',
                ]),
            ],
        ]);

        $ids = session(
            'checkout_cart_ids'
        );

        if (
            !is_array($ids) ||
            empty($ids)
        ) {
            return redirect()
                ->route('cart.index')
                ->with(
                    'error',
                    'Please select items first.'
                );
        }

        /*
        |--------------------------------------------------------------------------
        | Validate shipping address ownership
        |--------------------------------------------------------------------------
        */

        $address =
            ShippingAddress::query()
                ->whereKey(
                    $validated[
                        'shipping_address_id'
                    ]
                )
                ->where(
                    'user_id',
                    Auth::id()
                )
                ->first();

        if (!$address) {
            return redirect()
                ->route('orders.checkout')
                ->with(
                    'error',
                    'The selected shipping address is invalid.'
                );
        }

        try {
            $orders = DB::transaction(
                function () use (
                    $ids,
                    $address,
                    $validated
                ) {
                    $items =
                        Cart::query()
                            ->where(
                                'user_id',
                                Auth::id()
                            )
                            ->whereIn(
                                'id',
                                $ids
                            )
                            ->lockForUpdate()
                            ->with([
                                'product',
                                'varient',
                                'dokan',
                            ])
                            ->get();

                    $requestedCount =
                        count(
                            array_unique(
                                array_map(
                                    'intval',
                                    $ids
                                )
                            )
                        );

                    if (
                        $items->count() !==
                        $requestedCount
                    ) {
                        throw new \RuntimeException(
                            'One or more selected cart items are invalid.'
                        );
                    }

                    if (
                        $items->isEmpty()
                    ) {
                        throw new \RuntimeException(
                            'Your selected cart items are empty.'
                        );
                    }

                    /*
                    |--------------------------------------------------------------------------
                    | Group by vendor
                    |--------------------------------------------------------------------------
                    */

                    $itemsByVendor =
                        $items->groupBy(
                            fn ($item) =>
                                (string)
                                ($item->dokan_id ?? 0)
                        );

                    $createdOrders = [];

                    foreach (
                        $itemsByVendor
                        as $dokanId =>
                        $vendorItems
                    ) {
                        $dokanId =
                            (int) $dokanId;

                        if (
                            $dokanId <= 0
                        ) {
                            throw new \RuntimeException(
                                'One or more products do not have a valid vendor.'
                            );
                        }

                        $total = 0.0;

                        $lockedVariants = [];

                        foreach (
                            $vendorItems
                            as $item
                        ) {
                            if (
                                !is_numeric(
                                    $item->qty
                                ) ||
                                (int) $item->qty < 1 ||
                                (int) $item->qty > 100
                            ) {
                                throw new \RuntimeException(
                                    'Invalid cart quantity.'
                                );
                            }

                            $quantity =
                                (int) $item->qty;

                            $variant =
                                ProductVarient::query()
                                    ->whereKey(
                                        $item->varient_id
                                    )
                                    ->lockForUpdate()
                                    ->first();

                            if (!$variant) {
                                throw new \RuntimeException(
                                    'Product variant not found.'
                                );
                            }

                            /*
                            |--------------------------------------------------------------------------
                            | Product ownership/integrity
                            |--------------------------------------------------------------------------
                            */

                            if (
                                (int)
                                    $variant->product_id !==
                                (int)
                                    $item->product_id
                            ) {
                                throw new \RuntimeException(
                                    'Invalid product configuration detected.'
                                );
                            }

                            if (
                                $variant->qty <
                                $quantity
                            ) {
                                $productName =
                                    $item->product?->title
                                    ?? 'Product';

                                throw new \RuntimeException(
                                    "{$productName} does not have enough stock. "
                                    . "Only {$variant->qty} available."
                                );
                            }

                            $price =
                                (float)
                                $variant->price;

                            $discount =
                                max(
                                    0,
                                    min(
                                        100,
                                        (float)
                                        $variant->discount
                                    )
                                );

                            if (
                                $price < 0
                            ) {
                                throw new \RuntimeException(
                                    'Invalid product price.'
                                );
                            }

                            $finalPrice =
                                $price -
                                (
                                    $price *
                                    $discount /
                                    100
                                );

                            $lineTotal =
                                $finalPrice *
                                $quantity;

                            $total +=
                                $lineTotal;

                            $lockedVariants[
                                $item->id
                            ] = [
                                'variant' =>
                                    $variant,
                                'final_price' =>
                                    $finalPrice,
                            ];
                        }

                        $total =
                            round(
                                $total,
                                2
                            );

                        if (
                            $total <= 0
                        ) {
                            throw new \RuntimeException(
                                'Order total must be greater than zero.'
                            );
                        }

                        /*
                        |--------------------------------------------------------------------------
                        | Create vendor order
                        |--------------------------------------------------------------------------
                        */

                        $order =
                            Order::create([
                                'user_id' =>
                                    Auth::id(),

                                'dokan_id' =>
                                    $dokanId,

                                'shipping_address_id' =>
                                    $address->id,

                                'total_amount' =>
                                    $total,

                                'status' =>
                                    'pending',

                                'order_status' =>
                                    'pending',

                                'payment_method' =>
                                    $validated[
                                        'payment_method'
                                    ],

                                'payment_status' =>
                                    'pending',
                            ]);

                        foreach (
                            $vendorItems
                            as $item
                        ) {
                            $locked =
                                $lockedVariants[
                                    $item->id
                                ];

                            /** @var ProductVarient $variant */
                            $variant =
                                $locked[
                                    'variant'
                                ];

                            $finalPrice =
                                (float)
                                $locked[
                                    'final_price'
                                ];

                            $quantity =
                                (int) $item->qty;

                            $order
                                ->orderItems()
                                ->create([
                                    'product_id' =>
                                        $item->product_id,

                                    'varient_id' =>
                                        $item->varient_id,

                                    'qty' =>
                                        $quantity,

                                    'amount' =>
                                        round(
                                            $finalPrice *
                                            $quantity,
                                            2
                                        ),
                                ]);

                            $variant->decrement(
                                'qty',
                                $quantity
                            );

                            $item->delete();
                        }

                        $createdOrders[] =
                            $order;
                    }

                    return $createdOrders;
                }
            );
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

        session()->forget(
            'checkout_cart_ids'
        );

        /*
        |--------------------------------------------------------------------------
        | Order confirmation emails
        |--------------------------------------------------------------------------
        */

        foreach ($orders as $order) {
            $order->load([
                'user',
                'dokan',
                'shippingAddress',
                'orderItems.product',
                'orderItems.varient',
            ]);

            if (
                $order->user &&
                filter_var(
                    $order->user->email,
                    FILTER_VALIDATE_EMAIL
                )
            ) {
                try {
                    Mail::to(
                        $order->user->email
                    )->send(
                        new OrderPlacedMail(
                            $order
                        )
                    );
                } catch (\Throwable $e) {
                    report($e);
                }
            }
        }

        /*
        |--------------------------------------------------------------------------
        | Multiple vendor orders
        |--------------------------------------------------------------------------
        */

        if (
            count($orders) > 1
        ) {
            return redirect()
                ->route('orders.index')
                ->with(
                    'success',
                    'Your orders were created successfully.'
                );
        }

        $order =
            $orders[0];

        if (
            $validated[
                'payment_method'
            ] === 'esewa'
        ) {
            return redirect()
                ->route(
                    'orders.esewa.pay',
                    $order->id
                );
        }

        if (
            $validated[
                'payment_method'
            ] === 'bank'
        ) {
            return redirect()
                ->route(
                    'orders.bank.pay',
                    $order->id
                );
        }

        return redirect()
            ->route(
                'orders.show',
                $order->id
            )
            ->with(
                'success',
                'Order placed successfully with Cash on Delivery.'
            );
    }
}