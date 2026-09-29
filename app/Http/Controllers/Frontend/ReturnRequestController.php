<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\ReturnRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ReturnRequestController extends Controller
{
    /**
     * Display customer's return request form.
     */
    public function create($id)
    {
        $order = Order::with([
            'order_items.product',
            'order_items.varient',
            'shipping_address',
            'dokan',
            'returnRequests',
        ])
            ->where(
                'user_id',
                Auth::id()
            )
            ->findOrFail($id);

        if ($order->returnRequests()->exists()) {
            return redirect()
                ->route(
                    'orders.show',
                    $order->id
                )
                ->with(
                    'error',
                    'A return request already exists for this order.'
                );
        }

        if (! in_array(
            $order->order_status,
            [
                'cancelled',
                'completed',
            ],
            true
        )) {
            return redirect()
                ->route(
                    'orders.show',
                    $order->id
                )
                ->with(
                    'error',
                    'This order cannot be returned.'
                );
        }

        return view(
            'frontend.orders.return',
            compact('order')
        );
    }

    /**
     * Store customer return request.
     */
    public function store(
        Request $request,
        $id
    ) {
        $validated = $request->validate([
            'reason' => [
                'required',
                'string',
                'min:10',
                'max:1000',
            ],
        ]);

        try {
            DB::transaction(function () use (
                $id,
                $validated,
                &$returnRequest
            ) {
                $order = Order::where(
                    'user_id',
                    Auth::id()
                )
                    ->lockForUpdate()
                    ->findOrFail($id);

                if (! in_array(
                    $order->order_status,
                    [
                        'cancelled',
                        'completed',
                    ],
                    true
                )) {
                    throw new \RuntimeException(
                        'This order cannot be returned.'
                    );
                }

                if ($order->returnRequests()->exists()) {
                    throw new \RuntimeException(
                        'A return request already exists for this order.'
                    );
                }

                $returnRequest = ReturnRequest::create([
                    'order_id' => $order->id,
                    'user_id' => Auth::id(),
                    'dokan_id' => $order->dokan_id,
                    'reason' => trim($validated['reason']),
                    'status' => 'requested',
                    'refund_status' => 'pending',
                    'refund_amount' => $order->total_amount,
                    'admin_note' => null,
                ]);
            });

            return redirect()
                ->route(
                    'orders.show',
                    $returnRequest->order_id
                )
                ->with(
                    'success',
                    'Your return/refund request has been submitted successfully.'
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

    /**
     * Display customer's return requests.
     */
    public function index()
    {
        $returnRequests = ReturnRequest::with([
            'order',
            'dokan',
        ])
            ->where(
                'user_id',
                Auth::id()
            )
            ->latest()
            ->paginate(10);

        return view(
            'frontend.returns.index',
            compact('returnRequests')
        );
    }

    /**
     * Display one return request.
     */
    public function show($id)
    {
        $returnRequest = ReturnRequest::with([
            'order.order_items.product',
            'order.order_items.varient',
            'dokan',
        ])
            ->where(
                'user_id',
                Auth::id()
            )
            ->findOrFail($id);

        return view(
            'frontend.returns.show',
            compact('returnRequest')
        );
    }
}