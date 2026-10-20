<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\OrderService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OrderController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | ORDER LIST
    |--------------------------------------------------------------------------
    |
    | GET /api/orders
    |
    */

    public function index(Request $request): JsonResponse
    {
        /*
        |--------------------------------------------------------------------------
        | Strict query validation
        |--------------------------------------------------------------------------
        |
        | Only search, page and per_page are accepted.
        |
        */
        $validated = $this->validateStrict(
            $request,
            [
                'search' => [
                    'sometimes',
                    'nullable',
                    'string',
                    'max:' . config(
                        'security.validation.strings.search_max',
                        100
                    ),
                    'regex:/^[A-Za-z0-9._#\-\s]+$/',
                ],

                'page' => [
                    'sometimes',
                    'integer',
                    'min:1',
                    'max:' . config(
                        'security.validation.pagination.max_page',
                        1000
                    ),
                ],

                'per_page' => [
                    'sometimes',
                    'integer',
                    'min:1',
                    'max:50',
                ],
            ]
        );

        $search = trim(
            (string) ($validated['search'] ?? '')
        );

        $perPage = (int) (
            $validated['per_page'] ?? 10
        );

        $page = (int) (
            $validated['page'] ?? 1
        );

        $orders = Order::query()
            ->with([
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
            // Hide legacy failed online checkout attempts; confirmed cancellations remain visible.
            ->where(function ($query) {
                $query->whereNotIn('payment_method', ['esewa', 'bank'])
                    ->orWhere('payment_status', '!=', 'failed')
                    ->orWhere('order_status', '!=', 'cancelled')
                    ->orWhereNotNull('payment_receipt');
            })
            ->when(
                $search !== '',
                function ($query) use ($search) {

                    /*
                    |--------------------------------------------------------------------------
                    | Escape LIKE wildcard characters
                    |--------------------------------------------------------------------------
                    */

                    $escapedSearch = addcslashes(
                        $search,
                        '%_\\'
                    );

                    $query->where(function ($q) use (
                        $escapedSearch,
                        $search
                    ) {
                        $q->where(
                            'tracking_number',
                            'like',
                            '%' . $escapedSearch . '%'
                        );

                        if (ctype_digit($search)) {
                            $q->orWhere(
                                'id',
                                (int) $search
                            );
                        }
                    });
                }
            )
            ->latest()
            ->paginate(
                $perPage,
                ['*'],
                'page',
                $page
            );

        return response()->json([
            'message' =>
                'Orders retrieved successfully.',

            'data' =>
                $orders->items(),

            'meta' => [
                'current_page' =>
                    $orders->currentPage(),

                'last_page' =>
                    $orders->lastPage(),

                'per_page' =>
                    $orders->perPage(),

                'total' =>
                    $orders->total(),
            ],
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | ORDER DETAIL
    |--------------------------------------------------------------------------
    |
    | GET /api/orders/{id}
    |
    */

    public function show(int $id): JsonResponse
    {
        if ($id < 1) {
            return response()->json([
                'message' =>
                    'Invalid order ID.',
            ], 422);
        }

        $order = Order::query()
            ->with([
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
            ->find($id);

        if (! $order) {
            return response()->json([
                'message' =>
                    'Order not found.',
            ], 404);
        }

        return response()->json([
            'message' =>
                'Order retrieved successfully.',

            'data' =>
                $order,
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | CANCEL ORDER
    |--------------------------------------------------------------------------
    |
    | POST /api/orders/{id}/cancel
    |
    */

    public function cancel(
        Request $request,
        int $id
    ): JsonResponse {
        /*
        |--------------------------------------------------------------------------
        | Strict request validation
        |--------------------------------------------------------------------------
        */

        $validated = $this->validateStrict(
            $request,
            [
                'reason' => [
                    'required',
                    'string',
                    'min:5',
                    'max:1000',
                ],
            ]
        );

        if ($id < 1) {
            return response()->json([
                'message' =>
                    'Invalid order ID.',
            ], 422);
        }

        try {
            $order = app(OrderService::class)
                ->cancelOrder(
                    $id,
                    trim($validated['reason'])
                );

            return response()->json([
                'message' =>
                    'Order cancelled successfully.',

                'data' =>
                    $order,
            ]);

        } catch (\RuntimeException $e) {

            if (
                $e->getMessage() ===
                'ORDER_NOT_FOUND'
            ) {
                return response()->json([
                    'message' =>
                        'Order not found.',
                ], 404);
            }

            if (
                $e->getMessage() ===
                'ORDER_CANNOT_BE_CANCELLED'
            ) {
                return response()->json([
                    'message' =>
                        'This order cannot be cancelled at its current status.',
                ], 409);
            }

            report($e);

            return response()->json([
                'message' =>
                    'Unable to cancel the order.',
            ], 500);

        } catch (\Throwable $e) {

            report($e);

            return response()->json([
                'message' =>
                    'Unable to cancel the order.',
            ], 500);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | TRACK ORDER
    |--------------------------------------------------------------------------
    |
    | GET /api/orders/{id}/tracking
    |
    */


public function tracking(string $id): JsonResponse
{
    $identifier = trim($id);

    if ($identifier === '' || strlen($identifier) > 100) {
        return response()->json([
            'message' => 'Invalid tracking identifier.',
        ], 422);
    }

    $query = Order::query()
        ->where('user_id', Auth::id());

    // Try the tracking number first, ignoring case and surrounding spaces.
    $order = (clone $query)
        ->whereRaw(
            'LOWER(TRIM(tracking_number)) = ?',
            [strtolower($identifier)]
        )
        ->first();

    // If no tracking number matched, allow a numeric order ID.
    if (!$order && ctype_digit($identifier)) {
        $order = (clone $query)->find((int) $identifier);
    }

    if (!$order) {
        return response()->json([
            'message' => 'Order or tracking number not found.',
        ], 404);
    }

    return response()->json([
        'message' => 'Order tracking retrieved successfully.',
        'data' => [
            'order_id' => $order->id,
            'tracking_number' => $order->tracking_number,
            'order_status' => $order->order_status,
            'payment_status' => $order->payment_status,
            'payment_method' => $order->payment_method,
            'created_at' => $order->created_at,
            'updated_at' => $order->updated_at,
        ],
    ]);
}

/*
    |--------------------------------------------------------------------------
    | RETURN / CANCELLATION STATUS
    |--------------------------------------------------------------------------
    |
    | GET /api/orders/{id}/return-status
    |
    */

    public function returnStatus(int $id): JsonResponse
    {
        if ($id < 1) {
            return response()->json([
                'message' =>
                    'Invalid order ID.',
            ], 422);
        }

        $order = Order::query()
            ->where(
                'user_id',
                Auth::id()
            )
            ->find($id);

        if (! $order) {
            return response()->json([
                'message' =>
                    'Order not found.',
            ], 404);
        }

        $returnRequest = $order
            ->returnRequests()
            ->latest('id')
            ->first();

        if (! $returnRequest) {
            return response()->json([
                'message' =>
                    'No return or cancellation request exists for this order.',

                'data' => [
                    'order_id' =>
                        $order->id,

                    'order_status' =>
                        $order->order_status,

                    'payment_status' =>
                        $order->payment_status,

                    'has_request' =>
                        false,

                    'return_request' =>
                        null,
                ],
            ]);
        }

        return response()->json([
            'message' =>
                'Return status retrieved successfully.',

            'data' => [
                'order_id' =>
                    $order->id,

                'order_status' =>
                    $order->order_status,

                'payment_status' =>
                    $order->payment_status,

                'has_request' =>
                    true,

                'return_request' => [
                    'id' =>
                        $returnRequest->id,

                    'order_id' =>
                        $returnRequest->order_id,

                    'reason' =>
                        $returnRequest->reason,

                    'status' =>
                        $returnRequest->status,

                    'refund_status' =>
                        $returnRequest->refund_status,

                    'refund_amount' =>
                        $returnRequest->refund_amount,

                    'admin_note' =>
                        $returnRequest->admin_note,

                    'created_at' =>
                        $returnRequest->created_at,

                    'updated_at' =>
                        $returnRequest->updated_at,
                ],
            ],
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | CREATE RETURN REQUEST
    |--------------------------------------------------------------------------
    |
    | POST /api/orders/{id}/return-request
    |
    */

    public function createReturnRequest(
        Request $request,
        int $id
    ): JsonResponse {
        /*
        |--------------------------------------------------------------------------
        | Strict request validation
        |--------------------------------------------------------------------------
        */

        $validated = $this->validateStrict(
            $request,
            [
                'reason' => [
                    'required',
                    'string',
                    'min:5',
                    'max:1000',
                ],
            ]
        );

        if ($id < 1) {
            return response()->json([
                'message' =>
                    'Invalid order ID.',
            ], 422);
        }

        $order = Order::query()
            ->where(
                'user_id',
                Auth::id()
            )
            ->find($id);

        if (! $order) {
            return response()->json([
                'message' =>
                    'Order not found.',
            ], 404);
        }

        /*
        |--------------------------------------------------------------------------
        | Check order status
        |--------------------------------------------------------------------------
        */

        $orderStatus = strtolower(
            (string) $order->order_status
        );

        if (! in_array(
            $orderStatus,
            [
                'completed',
                'delivered',
            ],
            true
        )) {
            return response()->json([
                'message' =>
                    'This order is not eligible for a return request.',
            ], 409);
        }

        /*
        |--------------------------------------------------------------------------
        | Prevent duplicate active return requests
        |--------------------------------------------------------------------------
        */

        $existingRequest = $order
            ->returnRequests()
            ->whereIn(
                'status',
                [
                    'pending',
                    'approved',
                    'processing',
                ]
            )
            ->exists();

        if ($existingRequest) {
            return response()->json([
                'message' =>
                    'A return request already exists for this order.',
            ], 409);
        }

        try {

            $returnRequest = $order
                ->returnRequests()
                ->create([
                    'reason' =>
                        trim($validated['reason']),

                    'status' =>
                        'pending',

                    'refund_status' =>
                        'pending',

                    'refund_amount' =>
                        $order->total_amount,

                    'admin_note' =>
                        null,
                ]);

            return response()->json([
                'message' =>
                    'Return request submitted successfully.',

                'data' => [
                    'id' =>
                        $returnRequest->id,

                    'order_id' =>
                        $returnRequest->order_id,

                    'reason' =>
                        $returnRequest->reason,

                    'status' =>
                        $returnRequest->status,

                    'refund_status' =>
                        $returnRequest->refund_status,

                    'refund_amount' =>
                        $returnRequest->refund_amount,

                    'admin_note' =>
                        $returnRequest->admin_note,

                    'created_at' =>
                        $returnRequest->created_at,

                    'updated_at' =>
                        $returnRequest->updated_at,
                ],
            ], 201);

        } catch (\Throwable $e) {

            report($e);

            return response()->json([
                'message' =>
                    'Unable to create the return request.',
            ], 500);
        }
    }
}