<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Mail\PaymentSuccessfulMail;
use App\Models\Order;
use App\Services\OrderInventoryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class PaymentController extends Controller
{
    /**
     * Return bank-transfer instructions.
     */
    public function bankDetails(): JsonResponse
    {
        $details = [
            'bank_name' => config('services.bank.name'),
            'account_name' => config('services.bank.account_name'),
            'account_number' => config('services.bank.account_number'),
            'branch' => config('services.bank.branch'),
        ];

        $configured = collect($details)->every(
            static fn ($value) =>
                is_string($value) && trim($value) !== ''
        );

        return response()->json([
            'configured' => $configured,
            'message' => $configured
                ? 'Use these details to make your bank transfer.'
                : 'Bank-transfer details have not been configured yet.',
            'details' => $configured ? $details : null,
        ]);
    }

    /**
     * Prepare an eSewa payment for an authenticated payment group.
     */
    public function initiateEsewa(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'payment_group_id' => ['required', 'string', 'max:64'],
        ]);

        $groupId = $validated['payment_group_id'];

        $orders = Order::query()
            ->where('user_id', $request->user()->id)
            ->where('payment_group_id', $groupId)
            ->orderBy('id')
            ->get();

        if ($orders->isEmpty()) {
            return response()->json([
                'message' => 'Payment group not found.',
            ], 404);
        }

        if ($orders->contains(
            fn (Order $order) => $order->payment_method !== 'esewa'
        )) {
            return response()->json([
                'message' => 'This payment group is not an eSewa checkout.',
            ], 422);
        }

        if ($orders->contains(
            fn (Order $order) => $order->payment_status === 'paid'
        )) {
            return response()->json([
                'message' => 'This payment group has already been paid.',
            ], 409);
        }

        if ($orders->contains(
            fn (Order $order) => $order->payment_status !== 'pending'
        )) {
            return response()->json([
                'message' => 'This payment group is not eligible for payment.',
            ], 409);
        }

        $merchantCode = (string) config('services.esewa.merchant_code');
        $secretKey = (string) config('services.esewa.secret_key');
        $gatewayUrl = (string) config('services.esewa.url');

        if (
            $merchantCode === ''
            || $secretKey === ''
            || $gatewayUrl === ''
        ) {
            return response()->json([
                'message' => 'eSewa payment is not configured on the server.',
            ], 503);
        }

        $amount = round(
            (float) $orders->sum(
                fn (Order $order) => (float) $order->total_amount
            ),
            2
        );

        if ($amount <= 0) {
            return response()->json([
                'message' => 'The payment amount is invalid.',
            ], 422);
        }

        try {
            $transactionUuid = DB::transaction(function () use (
                $request,
                $groupId
            ) {
                $lockedOrders = Order::query()
                    ->where('user_id', $request->user()->id)
                    ->where('payment_group_id', $groupId)
                    ->orderBy('id')
                    ->lockForUpdate()
                    ->get();

                if ($lockedOrders->isEmpty()) {
                    throw new \RuntimeException('PAYMENT_GROUP_NOT_FOUND');
                }

                if ($lockedOrders->contains(
                    fn (Order $order) =>
                        $order->payment_method !== 'esewa'
                        || $order->payment_status !== 'pending'
                )) {
                    throw new \RuntimeException('PAYMENT_GROUP_NOT_PENDING');
                }

                $existing = $lockedOrders
                    ->pluck('payment_transaction_id')
                    ->filter()
                    ->unique()
                    ->values();

                if ($existing->count() > 1) {
                    throw new \RuntimeException(
                        'PAYMENT_GROUP_TRANSACTION_MISMATCH'
                    );
                }

                $uuid = $existing->first();

                if (! is_string($uuid) || $uuid === '') {
                    $uuid = (string) Str::uuid();

                    $anchor = $lockedOrders->first();
                    $anchor->payment_transaction_id = $uuid;
                    $anchor->save();
                }

                return $uuid;
            });
        } catch (\RuntimeException $e) {
            return response()->json([
                'message' => match ($e->getMessage()) {
                    'PAYMENT_GROUP_NOT_FOUND' =>
                        'Payment group not found.',
                    'PAYMENT_GROUP_TRANSACTION_MISMATCH' =>
                        'The payment group has inconsistent transaction data. Contact support.',
                    default =>
                        'This payment group is no longer eligible for payment.',
                },
            ], 409);
        }

        $signedFieldNames = 'total_amount,transaction_uuid,product_code';
        $totalAmount = number_format($amount, 2, '.', '');

        $message = 'total_amount=' . $totalAmount
            . ',transaction_uuid=' . $transactionUuid
            . ',product_code=' . $merchantCode;

        $signature = base64_encode(
            hash_hmac('sha256', $message, $secretKey, true)
        );

        return response()->json([
            'message' => 'eSewa payment is ready.',
            'gateway_url' => $gatewayUrl,
            'payment_group_id' => $groupId,
            'transaction_uuid' => $transactionUuid,
            'launch_url' => URL::temporarySignedRoute(
                'payments.esewa.hosted',
                now()->addMinutes(15),
                ['group' => $groupId]
            ),
            'amount' => $totalAmount,
            'product_code' => $merchantCode,
            'signed_field_names' => $signedFieldNames,
            'signature' => $signature,
            'form_fields' => [
                'amount' => $totalAmount,
                'tax_amount' => '0',
                'total_amount' => $totalAmount,
                'transaction_uuid' => $transactionUuid,
                'product_code' => $merchantCode,
                'product_service_charge' => '0',
                'product_delivery_charge' => '0',
                'signed_field_names' => $signedFieldNames,
                'signature' => $signature,
                'success_url' => route('api.payments.esewa.verify'),
                'failure_url' => route('api.payments.esewa.verify'),
            ],
        ]);
    }

    /**
     * Render a signed, short-lived eSewa auto-submit form for the Flutter app.
     * The signed URL avoids exposing the Laravel API bearer token in a browser URL.
     */
    public function hostedEsewaForm(Request $request, string $group)
    {
        $orders = Order::query()
            ->where('payment_group_id', $group)
            ->where('payment_method', 'esewa')
            ->where('payment_status', 'pending')
            ->orderBy('id')
            ->get();

        if ($orders->isEmpty()) {
            return redirect()
                ->route('home')
                ->with('error', 'This eSewa payment is no longer pending.');
        }

        $transactionUuid = (string) $orders
            ->pluck('payment_transaction_id')
            ->filter()
            ->first();

        if ($transactionUuid === '') {
            return redirect()
                ->route('home')
                ->with('error', 'The eSewa transaction is not initialized. Please retry checkout.');
        }

        $gatewayUrl = (string) config('services.esewa.url');
        $merchantCode = (string) config('services.esewa.merchant_code');
        $secretKey = (string) config('services.esewa.secret_key');

        if ($gatewayUrl === '' || $merchantCode === '' || $secretKey === '') {
            return redirect()
                ->route('home')
                ->with('error', 'eSewa is not configured on the server.');
        }

        $amount = number_format((float) $orders->sum(
            fn (Order $order) => (float) $order->total_amount
        ), 2, '.', '');

        $signedFieldNames = 'total_amount,transaction_uuid,product_code';
        $message = 'total_amount=' . $amount
            . ',transaction_uuid=' . $transactionUuid
            . ',product_code=' . $merchantCode;
        $signature = base64_encode(hash_hmac('sha256', $message, $secretKey, true));

        $firstOrder = $orders->first();
        session([
            'esewa_order_id' => $firstOrder->id,
            'esewa_payment_group_id' => $group,
            'esewa_transaction_uuid' => $transactionUuid,
        ]);

        $data = [
            'gateway_url' => $gatewayUrl,
            'amount' => $amount,
            'tax_amount' => '0',
            'total_amount' => $amount,
            'transaction_uuid' => $transactionUuid,
            'product_code' => $merchantCode,
            'product_service_charge' => '0',
            'product_delivery_charge' => '0',
            'success_url' => route('esewa.success'),
            'failure_url' => route('esewa.failure'),
            'signed_field_names' => $signedFieldNames,
            'signature' => $signature,
        ];

        return response()
            ->view('frontend.payments.esewa-redirect', compact('data'))
            ->header('Cache-Control', 'no-store, private');
    }

    /**
     * Verify payment with eSewa's server-side status API.
     *
     * The success email is sent only after the order group has been
     * successfully changed from pending to paid and the transaction commits.
     */
    public function verifyEsewa(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'payment_group_id' => ['required', 'string', 'max:64'],
            'transaction_uuid' => ['required', 'string', 'max:100'],
        ]);

        $userId = $request->user()->id;
        $groupId = $validated['payment_group_id'];
        $uuid = $validated['transaction_uuid'];

        $orders = Order::query()
            ->where('user_id', $userId)
            ->where('payment_group_id', $groupId)
            ->orderBy('id')
            ->get();

        if ($orders->isEmpty()) {
            return response()->json([
                'message' => 'Payment group not found.',
            ], 404);
        }

        if ($orders->contains(
            fn (Order $order) => $order->payment_method !== 'esewa'
        )) {
            return response()->json([
                'message' => 'This payment group is not an eSewa checkout.',
            ], 422);
        }

        $matchingTransaction = $orders->contains(
            fn (Order $order) =>
                is_string($order->payment_transaction_id)
                && hash_equals($order->payment_transaction_id, $uuid)
        );

        if (! $matchingTransaction) {
            return response()->json([
                'message' => 'The transaction does not match this payment group.',
            ], 422);
        }

        if ($orders->every(
            fn (Order $order) => $order->payment_status === 'paid'
        )) {
            return response()->json([
                'message' => 'Payment was already confirmed.',
                'payment_status' => 'paid',
                'payment_group_id' => $groupId,
            ]);
        }

        if ($orders->contains(
            fn (Order $order) => $order->payment_status !== 'pending'
        )) {
            return response()->json([
                'message' => 'This payment group is not eligible for verification.',
            ], 409);
        }

        $merchantCode = (string) config('services.esewa.merchant_code');
        $statusUrl = (string) config('services.esewa.status_url');

        if ($merchantCode === '' || $statusUrl === '') {
            return response()->json([
                'message' => 'eSewa status verification is not configured.',
            ], 503);
        }

        $amount = round(
            (float) $orders->sum(
                fn (Order $order) => (float) $order->total_amount
            ),
            2
        );

        try {
            $response = Http::acceptJson()
                ->timeout(15)
                ->get($statusUrl, [
                    'product_code' => $merchantCode,
                    'total_amount' => number_format($amount, 2, '.', ''),
                    'transaction_uuid' => $uuid,
                ]);
        } catch (\Throwable $e) {
            report($e);

            return response()->json([
                'message' => 'Unable to contact eSewa for verification. Please try again.',
                'payment_status' => 'pending',
            ], 502);
        }

        $body = $response->json();

        if (! $response->successful() || ! is_array($body)) {
            return response()->json([
                'message' => 'eSewa has not confirmed this payment.',
                'payment_status' => 'pending',
            ], 202);
        }

        $responseStatus = (string) ($body['status'] ?? '');
        $responseUuid = (string) (
            $body['transaction_uuid'] ?? $body['pid'] ?? ''
        );
        $responseCode = (string) (
            $body['product_code'] ?? $body['scd'] ?? ''
        );
        $responseAmount = $body['total_amount']
            ?? $body['totalAmount']
            ?? null;

        $verified = $responseStatus === 'COMPLETE'
            && $responseUuid !== ''
            && $responseCode !== ''
            && is_numeric($responseAmount)
            && hash_equals($uuid, $responseUuid)
            && hash_equals($merchantCode, $responseCode)
            && abs((float) $responseAmount - $amount) <= 0.009;

        if (! $verified) {
            return response()->json([
                'message' => 'eSewa has not confirmed this payment.',
                'payment_status' => 'pending',
            ], 202);
        }

        try {
            $newlyConfirmed = DB::transaction(function () use (
                $userId,
                $groupId,
                $uuid
            ) {
                $lockedOrders = Order::query()
                    ->where('user_id', $userId)
                    ->where('payment_group_id', $groupId)
                    ->orderBy('id')
                    ->lockForUpdate()
                    ->get();

                if ($lockedOrders->isEmpty()) {
                    throw new \RuntimeException('PAYMENT_GROUP_NOT_FOUND');
                }

                // Another verification request may already have confirmed it.
                if ($lockedOrders->every(
                    fn (Order $order) => $order->payment_status === 'paid'
                )) {
                    return false;
                }

                foreach ($lockedOrders as $order) {
                    if (
                        $order->payment_method !== 'esewa'
                        || $order->payment_status !== 'pending'
                    ) {
                        throw new \RuntimeException(
                            'PAYMENT_GROUP_NOT_PENDING'
                        );
                    }

                    /*
                     * The UUID may be stored on only the anchor order
                     * when the database has a unique constraint.
                     */
                    if (
                        $order->payment_transaction_id !== null
                        && (
                            ! is_string($order->payment_transaction_id)
                            || ! hash_equals(
                                $order->payment_transaction_id,
                                $uuid
                            )
                        )
                    ) {
                        throw new \RuntimeException(
                            'PAYMENT_GROUP_TRANSACTION_MISMATCH'
                        );
                    }
                }

                $inventory = app(OrderInventoryService::class);

                // Keep the entire group atomic: a failure on any order
                // rolls back every deduction and status change.
                foreach ($lockedOrders as $order) {
                    $inventory->deductForPayment($order);
                }

                // The inventory service skips cart deletion for Flutter orders
                // whose cart_removed_at marker is set, but still supports legacy
                // web orders that remove purchased quantities at payment time.
                foreach ($lockedOrders as $order) {
                    $inventory->removePurchasedCartItems($order);
                }

                foreach ($lockedOrders as $order) {
                    $order->payment_status = 'paid';
                    $order->order_status = 'confirmed';
                    $order->save();
                }

                return true;
            });
        } catch (\Throwable $e) {
            report($e);

            return response()->json([
                'message' => 'Payment was verified but the order update needs support review.',
            ], 409);
        }

        /*
         * Send after commit, never from inside the transaction.
         * A repeat verification does not send the email again.
         */
        if ($newlyConfirmed) {
            try {
                $paidOrders = Order::query()
                    ->with([
                        'user',
                        'dokan',
                        'shippingAddress',
                        'orderItems.product',
                        'orderItems.varient',
                    ])
                    ->where('user_id', $userId)
                    ->where('payment_group_id', $groupId)
                    ->orderBy('id')
                    ->get();

                $email = $paidOrders->first()?->user?->email;

                if (
                    $paidOrders->isNotEmpty()
                    && $paidOrders->every(
                        fn (Order $order) =>
                            $order->payment_status === 'paid'
                    )
                    && is_string($email)
                    && filter_var($email, FILTER_VALIDATE_EMAIL)
                ) {
                    Mail::to($email)->send(
                        new PaymentSuccessfulMail($paidOrders)
                    );
                } else {
                    Log::warning(
                        'eSewa payment-success email skipped: order group or customer email was not valid.',
                        ['payment_group_id' => $groupId]
                    );
                }
            } catch (\Throwable $e) {
                Log::error('eSewa payment-success email failed.', [
                    'payment_group_id' => $groupId,
                    'exception' => $e->getMessage(),
                ]);
            }
        }

        return response()->json([
            'message' => 'eSewa payment verified successfully.',
            'payment_status' => 'paid',
            'payment_group_id' => $groupId,
        ]);
    }

    /**
     * Upload bank-transfer proof for an authenticated payment group.
     * The receipt remains on the private local disk.
     */
    public function uploadBankReceipt(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'payment_group_id' => ['required', 'string', 'max:64'],
            'receipt' => [
                'required',
                'file',
                'mimes:jpg,jpeg,png,webp,pdf',
                'max:' . max(
                    1024,
                    (int) config(
                        'security.validation.payment_receipt_max_kb',
                        5120
                    )
                ),
            ],
        ]);

        $orders = Order::query()
            ->where('user_id', $request->user()->id)
            ->where('payment_group_id', $validated['payment_group_id'])
            ->orderBy('id')
            ->get();

        if ($orders->isEmpty()) {
            return response()->json([
                'message' => 'Payment group not found.',
            ], 404);
        }

        if ($orders->contains(
            fn (Order $order) => $order->payment_method !== 'bank'
        )) {
            return response()->json([
                'message' => 'This payment group is not a bank-transfer checkout.',
            ], 422);
        }

        if ($orders->contains(
            fn (Order $order) => $order->payment_status !== 'pending'
        )) {
            return response()->json([
                'message' => 'A receipt can only be uploaded for a pending payment.',
            ], 409);
        }

        $path = $validated['receipt']->store(
            'payment_receipts',
            ['disk' => 'local']
        );

        if (! is_string($path) || $path === '') {
            return response()->json([
                'message' => 'Unable to save the receipt. Please try again.',
            ], 500);
        }

        try {
            DB::transaction(function () use (
                $request,
                $validated,
                $path
            ) {
                $lockedOrders = Order::query()
                    ->where('user_id', $request->user()->id)
                    ->where(
                        'payment_group_id',
                        $validated['payment_group_id']
                    )
                    ->orderBy('id')
                    ->lockForUpdate()
                    ->get();

                if (
                    $lockedOrders->isEmpty()
                    || $lockedOrders->contains(
                        fn (Order $order) =>
                            $order->payment_method !== 'bank'
                            || $order->payment_status !== 'pending'
                    )
                ) {
                    throw new \RuntimeException(
                        'PAYMENT_GROUP_NOT_PENDING'
                    );
                }

                foreach ($lockedOrders as $order) {
                    if ($order->payment_receipt) {
                        Storage::disk('local')->delete(
                            $order->payment_receipt
                        );
                    }

                    $order->payment_receipt = $path;
                    $order->save();
                }
            });
        } catch (\Throwable $e) {
            Storage::disk('local')->delete($path);
            report($e);

            return response()->json([
                'message' => 'The receipt could not be attached to this payment group.',
            ], 409);
        }

        return response()->json([
            'message' => 'Receipt uploaded. Your payment is pending admin review.',
            'payment_group_id' => $validated['payment_group_id'],
            'payment_status' => 'pending',
        ], 201);
    }

    /**
     * Return the authenticated user's payment-group status.
     */
    public function groupStatus(
        Request $request,
        string $paymentGroupId
    ): JsonResponse {
        $orders = Order::query()
            ->where('user_id', $request->user()->id)
            ->where('payment_group_id', $paymentGroupId)
            ->orderBy('id')
            ->get([
                'id',
                'payment_group_id',
                'payment_method',
                'payment_status',
                'order_status',
                'total_amount',
                'payment_transaction_id',
                'payment_receipt',
                'tracking_number',
            ]);

        if ($orders->isEmpty()) {
            return response()->json([
                'message' => 'Payment group not found.',
            ], 404);
        }

        $statuses = $orders->pluck('payment_status')->unique()->values();

        $groupStatus = $statuses->count() === 1
            ? $statuses->first()
            : 'mixed';

        return response()->json([
            'payment_group_id' => $paymentGroupId,
            'payment_status' => $groupStatus,
            'orders' => $orders->map(
                static fn (Order $order) => [
                    'id' => $order->id,
                    'payment_method' => $order->payment_method,
                    'payment_status' => $order->payment_status,
                    'order_status' => $order->order_status,
                    'total_amount' => $order->total_amount,
                    'tracking_number' => $order->tracking_number,
                ]
            )->values(),
        ]);
    }
}