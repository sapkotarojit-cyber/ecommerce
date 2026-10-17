<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\Order;
use App\Models\ProductVarient;
use App\Models\ReturnRequest;
use App\Services\OrderInventoryService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $validated = $this->validateStrict($request, [
            'search' => [
                'sometimes',
                'nullable',
                'string',
                'max:100',
            ],

            'page' => [
                'sometimes',
                'integer',
                'min:1',
                'max:100000',
            ],
        ]);

        $search = isset($validated['search'])
            ? trim((string) $validated['search'])
            : '';

        $orders = Order::with([
            'order_items.product',
            'order_items.varient.product',
            'shipping_address',
            'dokan',
            'returnRequests',
        ])
            ->where('user_id', Auth::id())
            ->when(
                $search !== '',
                function ($query) use ($search) {
                    $query->where(function ($q) use ($search) {
                        $escapedSearch = addcslashes(
                            $search,
                            '%_\\'
                        );

                        $q->where(
                            'tracking_number',
                            'like',
                            '%' . $escapedSearch . '%'
                        );

                        if (ctype_digit($search)) {
                          $q->orWhere('id', (int) $search);
                        }
                    });
                }
            )
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view(
            'frontend.orders.index',
            compact(
                'orders',
                'search'
            )
        );
    }

    public function show(int $id)
    {
        $order = Order::with([
            'order_items.product',
            'order_items.varient.product',
            'shipping_address',
            'dokan',
            'returnRequests',
        ])
            ->where(
                'user_id',
                Auth::id()
            )
            ->findOrFail($id);

        return view(
            'frontend.orders.show',
            compact('order')
        );
    }

    public function cancelForm(int $id)
    {
        $order = Order::where(
            'user_id',
            Auth::id()
        )->findOrFail($id);

        if (
            ! in_array(
                $order->order_status,
                ['pending', 'processing'],
                true
            )
        ) {
            return redirect()
                ->route(
                    'orders.show',
                    $order->id
                )
                ->with(
                    'error',
                    'This order cannot be cancelled at its current status.'
                );
        }

        return view(
            'frontend.orders.cancel',
            compact('order')
        );
    }

    public function cancel(
        Request $request,
        int $id
    ) {
        $validated = $this->validateStrict($request, [
            'reason' => [
                'required',
                'string',
                'min:5',
                'max:1000',
            ],
        ]);

        try {
            DB::transaction(
                function () use (
                    $id,
                    $validated
                ) {
                    $order = Order::with(
                        'order_items'
                    )
                        ->where(
                            'user_id',
                            Auth::id()
                        )
                        ->lockForUpdate()
                        ->findOrFail($id);

                    if (
                        in_array(
                            $order->order_status,
                            ['cancelled', 'completed'],
                            true
                        )
                    ) {
                        throw new \RuntimeException(
                            'This order cannot be cancelled.'
                        );
                    }

                    if (
                        ! in_array(
                            $order->order_status,
                            ['pending', 'processing'],
                            true
                        )
                    ) {
                        throw new \RuntimeException(
                            'This order cannot be cancelled at its current status.'
                        );
                    }

                    $order->update([
                        'order_status' => 'cancelled',

                        'payment_status' =>
                            $order->payment_status === 'paid'
                                ? 'paid'
                                : 'failed',
                    ]);

                    // Restore inventory only when this order actually
                    // deducted it, and never restore it twice.
                    if (
                        $order->stock_deducted_at !== null
                        && $order->stock_restored_at === null
                    ) {
                        foreach ($order->order_items as $item) {
                            $variant = ProductVarient::query()
                                ->whereKey($item->varient_id)
                                ->lockForUpdate()
                                ->first();

                            if ($variant) {
                                $variant->increment('qty', (int) $item->qty);
                            }
                        }

                        $order->forceFill([
                            'stock_restored_at' => now(),
                        ])->save();
                    }

                    if (
                        ! $order
                            ->returnRequests()
                            ->exists()
                    ) {
                        ReturnRequest::create([
                            'order_id' =>
                                $order->id,

                            'user_id' =>
                                $order->user_id,

                            'dokan_id' =>
                                $order->dokan_id,

                            'reason' =>
                                'Order cancellation: ' .
                                trim(
                                    $validated['reason']
                                ),

                            'status' =>
                                'requested',

                            'refund_status' =>
                                $order->payment_status === 'paid'
                                    ? 'pending'
                                    : 'not_required',

                            'refund_amount' =>
                                $order->payment_status === 'paid'
                                    ? $order->total_amount
                                    : 0,

                            'admin_note' => null,
                        ]);
                    }
                }
            );

            // The cancellation transaction committed successfully.
            app(\App\Services\CatalogCache::class)->invalidate();

            return redirect()
                ->route(
                    'orders.show',
                    $id
                )
                ->with(
                    'success',
                    'Order cancelled successfully.'
                );
        } catch (\RuntimeException $e) {
            return redirect()
                ->route(
                    'orders.show',
                    $id
                )
                ->with(
                    'error',
                    $e->getMessage()
                );
        }
    }

    /* -----------------------------------------------------------------
     * eSewa
     * ----------------------------------------------------------------- */

    public function esewaPay(int $order)
    {
        $firstOrder = Order::query()
            ->where(
                'user_id',
                Auth::id()
            )
            ->findOrFail($order);

        if (
            $firstOrder->payment_method !== 'esewa' ||
            $firstOrder->payment_status !== 'pending'
        ) {
            return redirect()
                ->route(
                    'orders.show',
                    $firstOrder->id
                )
                ->with(
                    'error',
                    'This order is not awaiting eSewa payment.'
                );
        }

        /*
        |--------------------------------------------------------------------------
        | All orders created by the checkout share payment_group_id.
        |--------------------------------------------------------------------------
        */

        $groupId =
            (string) $firstOrder->payment_group_id;

        if ($groupId === '') {
            /*
            |--------------------------------------------------------------------------
            | Backward compatibility for older orders.
            |--------------------------------------------------------------------------
            */

            $groupId =
                (string) $firstOrder->id;

            $firstOrder->update([
                'payment_group_id' =>
                    $groupId,
            ]);
        }

        $orders = Order::query()
            ->where(
                'user_id',
                Auth::id()
            )
            ->where(
                'payment_group_id',
                $groupId
            )
            ->where(
                'payment_method',
                'esewa'
            )
            ->where(
                'payment_status',
                'pending'
            )
            ->get();

        /*
        |--------------------------------------------------------------------------
        | Backward compatibility if the payment group wasn't present.
        |--------------------------------------------------------------------------
        */

        if ($orders->isEmpty()) {
            $orders = collect([
                $firstOrder,
            ]);
        }

        $total = round(
            (float) $orders->sum(
                fn (Order $item) =>
                    (float) $item->total_amount
            ),
            2
        );

        if ($total <= 0) {
            return redirect()
                ->route('orders.index')
                ->with(
                    'error',
                    'Invalid payment amount.'
                );
        }

        $productCode = (string) config(
            'services.esewa.merchant_code',
            env(
                'ESEWA_PRODUCT_CODE',
                'EPAYTEST'
            )
        );

        $secret = (string) config(
            'services.esewa.secret_key'
        );

        if (
            $productCode === '' ||
            $secret === ''
        ) {
            return redirect()
                ->route('orders.index')
                ->with(
                    'error',
                    'eSewa payment is not configured.'
                );
        }

        $uuid =
            'ORD-' .
            Str::upper(
                Str::random(20)
            );

        $amount = number_format(
            $total,
            2,
            '.',
            ''
        );

        $signedFields =
            'total_amount,transaction_uuid,product_code';

        $message =
            "total_amount={$amount}," .
            "transaction_uuid={$uuid}," .
            "product_code={$productCode}";

        $signature = base64_encode(
            hash_hmac(
                'sha256',
                $message,
                $secret,
                true
            )
        );

        /*
        |--------------------------------------------------------------------------
        | Store transaction UUID on every order in the payment group.
        |--------------------------------------------------------------------------
        */

        Order::query()
            ->where(
                'user_id',
                Auth::id()
            )
            ->where(
                'payment_group_id',
                $groupId
            )
            ->where(
                'payment_method',
                'esewa'
            )
            ->where(
                'payment_status',
                'pending'
            )
            ->update([
                'payment_transaction_id' =>
                    $uuid,
            ]);

        /*
        |--------------------------------------------------------------------------
        | Ensure first order is also associated when handling legacy data.
        |--------------------------------------------------------------------------
        */

        $firstOrder->update([
            'payment_group_id' =>
                $groupId,

            'payment_transaction_id' =>
                $uuid,
        ]);

        session([
            'esewa_order_id' =>
                $firstOrder->id,

            'esewa_payment_group_id' =>
                $groupId,

            'esewa_transaction_uuid' =>
                $uuid,
        ]);

        $data = [
            'gateway_url' =>
                config('services.esewa.url'),

            'amount' =>
                $amount,

            'tax_amount' =>
                '0',

            'total_amount' =>
                $amount,

            'transaction_uuid' =>
                $uuid,

            'product_code' =>
                $productCode,

            'product_service_charge' =>
                '0',

            'product_delivery_charge' =>
                '0',

            'success_url' =>
                route('esewa.success'),

            'failure_url' =>
                route('esewa.failure'),

            'signed_field_names' =>
                $signedFields,

            'signature' =>
                $signature,
        ];

        return view(
            'frontend.payments.esewa-redirect',
            compact('data')
        );
    }

    public function esewaSuccess(
        Request $request
    ) {
        $data =
            $this->decodeEsewaResponse(
                $request
            );

        if (! $data) {
            return redirect()
                ->route('orders.index')
                ->with(
                    'error',
                    'Invalid eSewa payment response.'
                );
        }

        $status =
            (string) ($data['status'] ?? '');

        $uuid =
            (string) (
                $data['transaction_uuid'] ?? ''
            );

        $productCode =
            (string) (
                $data['product_code'] ?? ''
            );

        $totalAmount =
            (string) (
                $data['total_amount'] ?? ''
            );

        $signature =
            (string) (
                $data['signature'] ?? ''
            );

        $signedFieldNames =
            (string) (
                $data['signed_field_names'] ?? ''
            );

        if (
            $status !== 'COMPLETE' ||
            $uuid === '' ||
            $signature === '' ||
            $signedFieldNames === ''
        ) {
            return redirect()
                ->route('orders.index')
                ->with(
                    'error',
                    'eSewa payment could not be verified.'
                );
        }

        if (
            ! preg_match(
                '/^[A-Za-z0-9,-]+$/',
                $signedFieldNames
            )
        ) {
            return redirect()
                ->route('orders.index')
                ->with(
                    'error',
                    'Invalid eSewa signature fields.'
                );
        }

        $secret =
            (string) config(
                'services.esewa.secret_key'
            );

        $parts = [];

        foreach (
            explode(
                ',',
                $signedFieldNames
            ) as $field
        ) {
            if (
                ! array_key_exists(
                    $field,
                    $data
                )
            ) {
                return redirect()
                    ->route('orders.index')
                    ->with(
                        'error',
                        'Incomplete eSewa payment response.'
                    );
            }

            $parts[] =
                $field .
                '=' .
                (string) $data[$field];
        }

        $expected = base64_encode(
            hash_hmac(
                'sha256',
                implode(',', $parts),
                $secret,
                true
            )
        );

        if (
            ! hash_equals(
                $expected,
                $signature
            )
        ) {
            return redirect()
                ->route('orders.index')
                ->with(
                    'error',
                    'eSewa payment verification failed.'
                );
        }

        $groupId =
            (string) session(
                'esewa_payment_group_id',
                ''
            );

        $orderId =
            (int) session(
                'esewa_order_id',
                0
            );

        $sessionTransactionUuid =
            (string) session(
                'esewa_transaction_uuid',
                ''
            );

        if (
            $sessionTransactionUuid === '' ||
            ! hash_equals(
                $sessionTransactionUuid,
                $uuid
            )
        ) {
            return redirect()
                ->route('orders.index')
                ->with(
                    'error',
                    'eSewa transaction could not be matched to this payment session.'
                );
        }

        $order = Order::query()
            ->where(
                'user_id',
                Auth::id()
            )
            ->when(
                $groupId !== '',
                fn ($q) =>
                    $q->where(
                        'payment_group_id',
                        $groupId
                    ),
                fn ($q) =>
                    $q->whereKey(
                        $orderId
                    )
            )
            ->where(
                'payment_method',
                'esewa'
            )
            ->where(
                'payment_status',
                'pending'
            )
            ->first();

        if (! $order) {
            return redirect()
                ->route('orders.index')
                ->with(
                    'error',
                    'Payment session expired or order was already processed.'
                );
        }

        /*
        |--------------------------------------------------------------------------
        | Transaction UUID must belong to this order/group.
        |--------------------------------------------------------------------------
        */

        if (
            $order->payment_transaction_id === null ||
            ! hash_equals(
                (string) $order->payment_transaction_id,
                $uuid
            )
        ) {
            return redirect()
                ->route('orders.index')
                ->with(
                    'error',
                    'eSewa transaction could not be matched to this order.'
                );
        }

        $orders = Order::query()
            ->where(
                'user_id',
                Auth::id()
            )
            ->where(
                'payment_group_id',
                $order->payment_group_id ?: $order->id
            )
            ->where(
                'payment_method',
                'esewa'
            )
            ->where(
                'payment_status',
                'pending'
            )
            ->get();

        if ($orders->isEmpty()) {
            $orders = collect([
                $order,
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | Every order in the payment group must use the same UUID.
        |--------------------------------------------------------------------------
        */

        foreach ($orders as $groupOrder) {
            if (
                $groupOrder->payment_transaction_id === null ||
                ! hash_equals(
                    (string) $groupOrder->payment_transaction_id,
                    $uuid
                )
            ) {
                return redirect()
                    ->route('orders.index')
                    ->with(
                        'error',
                        'eSewa transaction does not match the complete payment group.'
                    );
            }
        }

        $expectedTotal = round(
            (float) $orders->sum(
                fn (Order $item) =>
                    (float) $item->total_amount
            ),
            2
        );

        $responseTotal =
            round(
                (float) $totalAmount,
                2
            );

        $configuredProductCode =
            (string) config(
                'services.esewa.merchant_code',
                env(
                    'ESEWA_PRODUCT_CODE',
                    'EPAYTEST'
                )
            );

        if (
            $productCode !==
                $configuredProductCode ||
            abs(
                $expectedTotal -
                $responseTotal
            ) > 0.009
        ) {
            return redirect()
                ->route('orders.index')
                ->with(
                    'error',
                    'eSewa payment amount or merchant could not be verified.'
                );
        }

        /*
        |--------------------------------------------------------------------------
        | Verify directly against eSewa.
        |--------------------------------------------------------------------------
        */

        $statusResult =
            $this->verifyEsewaTransaction(
                $uuid,
                $responseTotal,
                $productCode
            );

        if (! $statusResult['verified']) {
            return redirect()
                ->route('orders.index')
                ->with(
                    'error',
                    'eSewa could not confirm the payment. Please contact support if your account was charged.'
                );
        }

        /*
        |--------------------------------------------------------------------------
        | Final atomic payment transition.
        |--------------------------------------------------------------------------
        */

        DB::transaction(
            function () use (
                $orders,
                $uuid
            ) {
                $inventory = app(OrderInventoryService::class);

                $lockedOrders =
                    Order::query()
                        ->whereIn(
                            'id',
                            $orders->pluck('id')
                        )
                        ->where(
                            'payment_status',
                            'pending'
                        )
                        ->lockForUpdate()
                        ->get();

                if ($lockedOrders->isEmpty()) {
                    throw new \RuntimeException(
                        'No pending orders remain in this payment group.'
                    );
                }

                foreach ($lockedOrders as $lockedOrder) {
                    if (
                        $lockedOrder->payment_transaction_id === null ||
                        ! hash_equals(
                            (string) $lockedOrder->payment_transaction_id,
                            $uuid
                        )
                    ) {
                        throw new \RuntimeException(
                            'Payment transaction mismatch.'
                        );
                    }

                    if ($lockedOrder->payment_method !== 'esewa') {
                        throw new \RuntimeException(
                            'Payment group method mismatch.'
                        );
                    }
                }

                // All deductions and payment updates roll back together
                // if any variant has insufficient stock.
                foreach ($lockedOrders as $lockedOrder) {
                    $inventory->deductForPayment($lockedOrder);
                }

                // Payment is confirmed: remove only purchased quantities from cart.
                foreach ($lockedOrders as $lockedOrder) {
                    $inventory->removePurchasedCartItems($lockedOrder);
                }

                foreach ($lockedOrders as $lockedOrder) {
                    $lockedOrder->update([
                        'payment_status' =>
                            'paid',

                        'order_status' =>
                            'confirmed',

                        'payment_transaction_id' =>
                            $uuid,
                    ]);
                }
            }
        );

        session()->forget([
            'esewa_order_id',
            'esewa_payment_group_id',
            'esewa_transaction_uuid',
        ]);

        return redirect()
            ->route('orders.index')
            ->with(
                'success',
                'eSewa payment verified successfully.'
            );
    }

    public function esewaFailure(
        Request $request
    ) {
        return $this->failPendingPayment(
            (int) session(
                'esewa_order_id',
                0
            ),
            (string) session(
                'esewa_payment_group_id',
                ''
            ),
            'eSewa payment was cancelled or failed.'
        );
    }

    /* -----------------------------------------------------------------
     * Bank transfer
     * ----------------------------------------------------------------- */

    public function bankPaymentPage(
        int $order
    ) {
        $firstOrder = Order::query()
            ->where(
                'user_id',
                Auth::id()
            )
            ->findOrFail($order);

        if (
            $firstOrder->payment_method !== 'bank' ||
            $firstOrder->payment_status !== 'pending'
        ) {
            return redirect()
                ->route(
                    'orders.show',
                    $firstOrder->id
                )
                ->with(
                    'error',
                    'This order is not awaiting bank payment.'
                );
        }

        $groupId =
            (string) $firstOrder->payment_group_id;

        if ($groupId === '') {
            $groupId =
                (string) $firstOrder->id;

            $firstOrder->update([
                'payment_group_id' =>
                    $groupId,
            ]);
        }

        $orders = Order::query()
            ->where(
                'user_id',
                Auth::id()
            )
            ->where(
                'payment_group_id',
                $groupId
            )
            ->where(
                'payment_method',
                'bank'
            )
            ->where(
                'payment_status',
                'pending'
            )
            ->get();

        if ($orders->isEmpty()) {
            $orders = collect([
                $firstOrder,
            ]);
        }

        $totalAmount = round(
            (float) $orders->sum(
                fn (Order $item) =>
                    (float) $item->total_amount
            ),
            2
        );

        if ($totalAmount <= 0) {
            return redirect()
                ->route('orders.index')
                ->with(
                    'error',
                    'Invalid bank payment amount.'
                );
        }

        session([
            'bank_order_id' =>
                $firstOrder->id,

            'bank_payment_group_id' =>
                $groupId,
        ]);

        $masterTracking =
            $firstOrder->tracking_number;

        return view(
            'frontend.payments.bank-pay',
            compact(
                'masterTracking',
                'totalAmount',
                'firstOrder'
            )
        );
    }

    public function bankSuccess(
        Request $request
    ) {
        $validated = $this->validateStrict(
            $request,
            [
                'payment_receipt' => [
                    'required',
                    'file',
                    'mimetypes:image/jpeg,image/png,image/webp,application/pdf',
                    'max:' .
                        (int) env(
                            'UPLOAD_PAYMENT_RECEIPT_MAX_KB',
                            2048
                        ),
                ],

                'terms' => [
                    'required',
                    'accepted',
                ],
            ]
        );

        $groupId =
            (string) session(
                'bank_payment_group_id',
                ''
            );

        $orderId =
            (int) session(
                'bank_order_id',
                0
            );

        if (
            $groupId === '' &&
            $orderId <= 0
        ) {
            return redirect()
                ->route('orders.index')
                ->with(
                    'error',
                    'Your payment session has expired. Please try again.'
                );
        }

        $order = Order::query()
            ->where(
                'user_id',
                Auth::id()
            )
            ->when(
                $groupId !== '',
                fn ($q) =>
                    $q->where(
                        'payment_group_id',
                        $groupId
                    ),
                fn ($q) =>
                    $q->whereKey(
                        $orderId
                    )
            )
            ->where(
                'payment_method',
                'bank'
            )
            ->where(
                'payment_status',
                'pending'
            )
            ->first();

        if (! $order) {
            return redirect()
                ->route('orders.index')
                ->with(
                    'error',
                    'No pending bank payment was found.'
                );
        }

        /*
        |--------------------------------------------------------------------------
        | Store receipt on the local/private filesystem.
        |--------------------------------------------------------------------------
        */

        try {
            $path = $request
                ->file('payment_receipt')
                ->store(
                    'payment_receipts',
                    'local'
                );
        } catch (\Throwable $e) {
            report($e);

            return redirect()
                ->route(
                    'orders.show',
                    $order->id
                )
                ->with(
                    'error',
                    'Unable to upload the payment receipt. Please try again.'
                );
        }

        try {
            DB::transaction(
                function () use (
                    $order,
                    $path
                ) {
                    $paymentGroupId =
                        $order->payment_group_id
                        ?: (string) $order->id;

                    $orders = Order::query()
                        ->where(
                            'user_id',
                            Auth::id()
                        )
                        ->where(
                            function ($query) use (
                                $paymentGroupId,
                                $order
                            ) {
                                $query
                                    ->where(
                                        'payment_group_id',
                                        $paymentGroupId
                                    )
                                    ->orWhere(
                                        'id',
                                        $order->id
                                    );
                            }
                        )
                        ->where(
                            'payment_method',
                            'bank'
                        )
                        ->where(
                            'payment_status',
                            'pending'
                        )
                        ->lockForUpdate()
                        ->get();

                    if ($orders->isEmpty()) {
                        throw new \RuntimeException(
                            'No pending orders found for this payment group.'
                        );
                    }

                    foreach (
                        $orders as $item
                    ) {
                        $item->update([
                            'order_status' =>
                                'pending',

                            'payment_receipt' =>
                                $path,
                        ]);
                    }
                }
            );
        } catch (\Throwable $e) {
            Storage::disk('local')
                ->delete($path);

            report($e);

            return redirect()
                ->route(
                    'orders.show',
                    $order->id
                )
                ->with(
                    'error',
                    'Unable to submit the payment receipt. Please try again.'
                );
        }

        session()->forget([
            'bank_order_id',
            'bank_payment_group_id',
        ]);

        return redirect()
            ->route(
                'orders.show',
                $order->id
            )
            ->with(
                'success',
                'Receipt submitted. Your payment will remain pending until an administrator verifies it.'
            );
    }

    public function bankFailure()
    {
        return $this->failPendingPayment(
            (int) session(
                'bank_order_id',
                0
            ),
            (string) session(
                'bank_payment_group_id',
                ''
            ),
            'Bank payment was cancelled or failed.'
        );
    }

    /* -----------------------------------------------------------------
     * Invoice / tracking
     * ----------------------------------------------------------------- */

    public function invoice(int $id)
    {
        $order = Order::with([
            'order_items.product',
            'order_items.varient.product',
            'shipping_address',
            'dokan',
        ])
            ->where(
                'user_id',
                Auth::id()
            )
            ->findOrFail($id);

        return view(
            'frontend.orders.invoice',
            compact('order')
        );
    }

    public function trackForm()
    {
        return view(
            'frontend.orders.track'
        );
    }

    public function trackResult(
        Request $request
    ) {
        $validated = $this->validateStrict(
            $request,
            [
                'tracking_number' => [
                    'required',
                    'string',
                    'min:3',
                    'max:100',
                    'regex:/^[A-Za-z0-9._-]+$/',
                ],
            ]
        );

        $tracking =
            trim(
                $validated['tracking_number']
            );

        $order = Order::with([
            'order_items.product',
            'order_items.varient.product',
            'shipping_address',
            'dokan',
        ])
            ->where(
                function ($q) use (
                    $tracking
                ) {
                    $q->where(
                        'tracking_number',
                        $tracking
                    );

                    if (
                        ctype_digit(
                            $tracking
                        )
                    ) {
                        $q->orWhere('id', (int) $tracking);
                    }
                }
            )
            ->first();

        if (! $order) {
            return back()
                ->withInput()
                ->with(
                    'error',
                    'No order found with that tracking number or ID.'
                );
        }

        return view(
            'frontend.orders.track',
            compact('order')
        );
    }

    /* -----------------------------------------------------------------
     * Protected receipt download
     * ----------------------------------------------------------------- */

    public function paymentReceipt(
        Request $request,
        int $id
    ) {
        $order =
            Order::findOrFail($id);

        if (
            Auth::guard('admin')->check()
        ) {
            // Admin may review any order receipt.
        } elseif (
            $vendor =
                Auth::guard('dokan')->user()
        ) {
            abort_unless(
                (int) $order->dokan_id ===
                    (int) $vendor->id,
                403
            );
        } else {
            abort(403);
        }

        abort_unless(
            filled(
                $order->payment_receipt
            ),
            404
        );

        /*
        |--------------------------------------------------------------------------
        | IMPORTANT:
        | bankSuccess() stores receipts on the local disk, so use the same
        | disk here.
        |--------------------------------------------------------------------------
        */

        $disk =
            Storage::disk('local');

        abort_unless(
            $disk->exists(
                $order->payment_receipt
            ),
            404
        );

        $mime =
            $disk->mimeType(
                $order->payment_receipt
            ) ?: 'application/octet-stream';

        return response()->file(
            $disk->path(
                $order->payment_receipt
            ),
            [
                'Content-Type' =>
                    $mime,

                'X-Content-Type-Options' =>
                    'nosniff',

                'Content-Disposition' =>
                    'inline; filename="payment-receipt-' .
                    $order->id .
                    '"',
            ]
        );
    }

    private function decodeEsewaResponse(
        Request $request
    ): ?array {
        $allowed = [
            'transaction_code',
            'status',
            'total_amount',
            'transaction_uuid',
            'product_code',
            'signed_field_names',
            'signature',
        ];

        $rules = [
            'transaction_code' => [
                'required',
                'string',
                'max:100',
                'regex:/^[A-Za-z0-9._-]+$/',
            ],

            'status' => [
                'required',
                'string',
                'max:30',
                'in:COMPLETE,PENDING,FAILED,CANCELED,FULL_REFUND,PARTIAL_REFUND,AMBIGUOUS,NOT_FOUND',
            ],

            'total_amount' => [
                'required',
                'numeric',
                'min:0',
                'max:999999999.99',
            ],

            'transaction_uuid' => [
                'required',
                'string',
                'min:1',
                'max:100',
                'regex:/^[A-Za-z0-9-]+$/',
            ],

            'product_code' => [
                'required',
                'string',
                'min:1',
                'max:100',
                'regex:/^[A-Za-z0-9_-]+$/',
            ],

            'signed_field_names' => [
                'required',
                'string',
                'max:500',
                'regex:/^[A-Za-z0-9_,]+$/',
            ],

            'signature' => [
                'required',
                'string',
                'max:512',
                'regex:/^[A-Za-z0-9+\/=_-]+$/',
            ],
        ];

        $payload =
            $request->input('data');

        if (
            is_string($payload) &&
            $payload !== ''
        ) {
            if (
                strlen($payload) > 32768
            ) {
                return null;
            }

            $decoded =
                base64_decode(
                    $payload,
                    true
                );

            if (
                $decoded === false ||
                strlen($decoded) > 16384
            ) {
                return null;
            }

            $json =
                json_decode(
                    $decoded,
                    true
                );

            if (
                ! is_array($json) ||
                array_is_list($json)
            ) {
                return null;
            }

            $unexpected =
                array_diff(
                    array_keys($json),
                    $allowed
                );

            if (
                $unexpected !== []
            ) {
                return null;
            }

            $validator =
                Validator::make(
                    $json,
                    $rules
                );

            if (
                $validator->fails()
            ) {
                return null;
            }

            return $validator->validated();
        }

        $unexpected =
            array_diff(
                array_keys(
                    $request->all()
                ),
                array_merge(
                    $allowed,
                    ['data', '_token']
                )
            );

        if (
            $unexpected !== []
        ) {
            return null;
        }

        $data = [];

        foreach (
            $allowed as $key
        ) {
            if (
                $request->has($key)
            ) {
                $data[$key] =
                    $request->input(
                        $key
                    );
            }
        }

        if ($data === []) {
            return null;
        }

        $validator =
            Validator::make(
                $data,
                $rules
            );

        if (
            $validator->fails()
        ) {
            return null;
        }

        return $validator->validated();
    }

    private function verifyEsewaTransaction(
        string $uuid,
        float $amount,
        string $productCode
    ): array {
        $url =
            (string) config(
                'services.esewa.status_url'
            );

        if ($url === '') {
            return [
                'verified' => false,
            ];
        }

        try {
            $response =
                Http::acceptJson()
                    ->timeout(10)
                    ->get(
                        $url,
                        [
                            'product_code' =>
                                $productCode,

                            'total_amount' =>
                                number_format(
                                    $amount,
                                    2,
                                    '.',
                                    ''
                                ),

                            'transaction_uuid' =>
                                $uuid,
                        ]
                    );

            if (
                ! $response->successful()
            ) {
                return [
                    'verified' => false,
                ];
            }

            $body =
                $response->json();

            if (
                ! is_array($body)
            ) {
                return [
                    'verified' => false,
                ];
            }

            $status =
                (string) (
                    $body['status'] ?? ''
                );

            $responseUuid =
                (string) (
                    $body['transaction_uuid'] ??
                    $body['pid'] ??
                    ''
                );

            $responseCode =
                (string) (
                    $body['product_code'] ??
                    $body['scd'] ??
                    ''
                );

            $responseAmount =
                $body['total_amount'] ??
                $body['totalAmount'] ??
                null;

            if (
                $status !== 'COMPLETE' ||
                $responseUuid === '' ||
                $responseCode === '' ||
                ! is_numeric(
                    $responseAmount
                )
            ) {
                return [
                    'verified' => false,
                ];
            }

            $verified =
                hash_equals(
                    $uuid,
                    $responseUuid
                ) &&
                hash_equals(
                    $productCode,
                    $responseCode
                ) &&
                abs(
                    (float) $responseAmount -
                    $amount
                ) <= 0.009;

            return [
                'verified' =>
                    $verified,

                'reference' =>
                    $body['ref_id'] ??
                    $body['refId'] ??
                    null,
            ];
        } catch (\Throwable $e) {
            report($e);

            return [
                'verified' => false,
            ];
        }
    }

    private function failPendingPayment(
        int $orderId,
        string $groupId,
        string $message
    ) {
        $query = Order::query()
            ->where(
                'user_id',
                Auth::id()
            )
            ->where(
                'payment_status',
                'pending'
            );

        if (
            $groupId !== ''
        ) {
            $query->where(
                'payment_group_id',
                $groupId
            );
        } else {
            $query->whereKey(
                $orderId
            );
        }

        $orders =
            $query
                ->with('order_items')
                ->get();

        if (
            $orders->isEmpty()
        ) {
            session()->forget([
                'esewa_order_id',
                'esewa_payment_group_id',
                'esewa_transaction_uuid',
                'bank_order_id',
                'bank_payment_group_id',
            ]);

            return redirect()
                ->route('orders.index')
                ->with(
                    'error',
                    'Payment session expired.'
                );
        }

        DB::transaction(
            function () use (
                $orders
            ) {
                foreach (
                    $orders as $order
                ) {
                    $locked =
                        Order::with(
                            'order_items'
                        )
                            ->whereKey(
                                $order->id
                            )
                            ->where(
                                'payment_status',
                                'pending'
                            )
                            ->lockForUpdate()
                            ->first();

                    if (! $locked) {
                        continue;
                    }

                    if (empty($locked->cart_restored_at)) {
                        // Aggregate duplicate order lines for the same variant.
                        // Online checkout leaves cart rows in place, so restore by
                        // REPLACING the matching quantity, never incrementing it.
                        $quantities = $locked->order_items
                            ->groupBy('varient_id')
                            ->map(fn ($rows) => (int) $rows->sum('qty'));

                        foreach ($quantities as $variantId => $quantity) {
                            if ($quantity < 1) {
                                continue;
                            }

                            $variant = ProductVarient::query()
                                ->whereKey($variantId)
                                ->lockForUpdate()
                                ->first();

                            // A deleted variant cannot be re-added to the cart.
                            if (! $variant) {
                                continue;
                            }

                            $existing = Cart::query()
                                ->where('user_id', $locked->user_id)
                                ->where('varient_id', $variantId)
                                ->orderBy('id')
                                ->lockForUpdate()
                                ->first();

                            if ($existing) {
                                // Replace instead of incrementing; the cart row
                                // usually already exists from online checkout.
                                $existing->update([
                                    'product_id' => $variant->product_id,
                                    'dokan_id' => $locked->dokan_id,
                                    'qty' => $quantity,
                                ]);
                            } else {
                                Cart::create([
                                    'user_id' => $locked->user_id,
                                    'product_id' => $variant->product_id,
                                    'varient_id' => $variant->id,
                                    'dokan_id' => $locked->dokan_id,
                                    'qty' => $quantity,
                                ]);
                            }
                        }

                        // This timestamp guards against duplicate restoration.
                        $locked->forceFill([
                            'cart_restored_at' => now(),
                        ])->save();
                    }

                    $locked->update([
                        'payment_status' =>
                            'failed',

                        'order_status' =>
                            'cancelled',
                    ]);
                }
            }
        );

        session()->forget([
            'esewa_order_id',
            'esewa_payment_group_id',
            'esewa_transaction_uuid',
            'bank_order_id',
            'bank_payment_group_id',
        ]);

        return redirect()
            ->route('orders.checkout')
            ->with(
                'error',
                $message .
                ' Your items have been returned to your cart.'
            );
    }
}