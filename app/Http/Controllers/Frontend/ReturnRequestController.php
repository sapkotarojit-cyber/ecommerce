<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\ReturnRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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
            ->where('user_id', Auth::id())
            ->findOrFail($id);

        if ($order->returnRequests()->exists()) {
            return redirect()
                ->route('orders.show', $order->id)
                ->with(
                    'error',
                    'A return request already exists for this order.'
                );
        }

        /*
        |--------------------------------------------------------------------------
        | Allow cancelled orders or completed orders
        |--------------------------------------------------------------------------
        */

        if (
            ! in_array(
                $order->order_status,
                ['cancelled', 'completed']
            )
        ) {
            return redirect()
                ->route('orders.show', $order->id)
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
        $request->validate([
            'reason' =>
                'required|string|max:1000',
        ]);

        $order = Order::where(
            'user_id',
            Auth::id()
        )
            ->with('returnRequests')
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

        if (
            ! in_array(
                $order->order_status,
                ['cancelled', 'completed']
            )
        ) {
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

        ReturnRequest::create([
            'order_id' =>
                $order->id,

            'user_id' =>
                Auth::id(),

            'dokan_id' =>
                $order->dokan_id,

            'reason' =>
                $request->reason,

            'status' =>
                'requested',

            'refund_status' =>
                'pending',

            'refund_amount' =>
                $order->total_amount,

            'admin_note' =>
                null,
        ]);

        return redirect()
            ->route(
                'orders.show',
                $order->id
            )
            ->with(
                'success',
                'Your return/refund request has been submitted successfully.'
            );
    }

    /**
     * Display customer's return requests.
     */
    public function index()
    {
        $returnRequests =
            ReturnRequest::with([
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
        $returnRequest =
            ReturnRequest::with([
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