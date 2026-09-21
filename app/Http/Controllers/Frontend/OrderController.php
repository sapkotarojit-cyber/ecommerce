<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\ShippingAddress;
use App\Models\ProductVarient; 
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class OrderController extends Controller
{
    /**
     * Display a listing of the logged-in user's orders.
     */
    public function index(Request $request)
    {
        $search = $request->input('search');

        $orders = Order::with(['order_items.product', 'order_items.varient.product', 'shipping_address', 'dokan'])
            ->where('user_id', Auth::id())
            ->when($search, function ($query, $search) {
                return $query->where(function ($q) use ($search) {
                    $q->where('tracking_number', 'like', "%{$search}%")
                      ->orWhere('id', 'like', "%{$search}%");
                });
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('frontend.orders.index', compact('orders', 'search'));
    }

    /**
     * Show checkout page with user's cart items and shipping addresses.
     */
    public function checkout()
    {
        $cartItems = Cart::where('user_id', Auth::id())
            ->with(['product', 'varient', 'dokan'])
            ->get();

        if ($cartItems->isEmpty()) {
            return redirect()->route('cart.index')->with('error', 'Your cart is empty.');
        }

        $addresses = ShippingAddress::where('user_id', Auth::id())->get();

        $vendorTotal = [];
        $grandTotal = 0;

        foreach ($cartItems as $item) {
            $price = $item->varient->price ?? 0;
            $discount = $item->varient->discount ?? 0;
            $finalPrice = $price - ($price * $discount / 100);
            $itemTotal = $finalPrice * $item->qty;

            $dokanId = $item->dokan_id ?? 0;

            if (!isset($vendorTotal[$dokanId])) {
                $vendorTotal[$dokanId] = [
                    'dokan' => $item->dokan,
                    'subtotal' => 0,
                    'items' => []
                ];
            }

            $vendorTotal[$dokanId]['subtotal'] += $itemTotal;
            $vendorTotal[$dokanId]['items'][] = $item;
            $grandTotal += $itemTotal;
        }

        return view('frontend.orders.checkout', compact('cartItems', 'addresses', 'vendorTotal', 'grandTotal'));
    }

    /**
     * Store order placement and handle payment routing (COD, eSewa, Bank Transfer).
     */
    public function store(Request $request)
    {
        $request->validate([
            'shipping_address_id' => 'required|exists:shipping_addresses,id',
            'payment_method' => 'required|string',
        ]);

        $paymentMethod = strtolower(trim($request->payment_method));

        // 1. eSewa Gateway Routing
        $esewaMethods = ['esewa', 'online', 'card'];
        if (in_array($paymentMethod, $esewaMethods)) {
            DB::beginTransaction();

            try {
                $cartItems = Cart::where('user_id', Auth::id())
                    ->with(['product', 'varient'])
                    ->get();

                if ($cartItems->isEmpty()) {
                    DB::rollBack();
                    return redirect()->route('cart.index')->with('error', 'Your cart is empty.');
                }

                $groupedByVendor = $cartItems->groupBy('dokan_id');
                $masterTracking = 'ORD-' . strtoupper(uniqid());
                $grandTotalAmount = 0;

                foreach ($groupedByVendor as $dokanId => $items) {
                    $totalAmount = $items->sum(function ($item) {
                        $price = $item->varient->price ?? 0;
                        $discount = $item->varient->discount ?? 0;
                        $finalPrice = $price - ($price * $discount / 100);
                        return $finalPrice * $item->qty;
                    });

                    $grandTotalAmount += $totalAmount;

                    $order = Order::create([
                        'user_id' => Auth::id(),
                        'dokan_id' => $dokanId ?: null,
                        'shipping_address_id' => $request->shipping_address_id,
                        'tracking_number' => $masterTracking . '-' . ($dokanId ?: 'main'),
                        'total_amount' => $totalAmount,
                        'payment_method' => $request->payment_method,
                        'payment_status' => 'pending',
                        'order_status' => 'pending',
                    ]);

                    foreach ($items as $item) {
                        $price = $item->varient->price ?? 0;
                        $discount = $item->varient->discount ?? 0;
                        $finalPrice = $price - ($price * $discount / 100);

                        OrderItem::create([
                            'order_id' => $order->id,
                            'product_id' => $item->product_id,
                            'varient_id' => $item->varient_id,         
                            'qty' => $item->qty,          
                            'amount' => $finalPrice * $item->qty,      
                        ]);
                    }
                }

                session()->put('pending_gateway_order', [
                    'user_id' => Auth::id(),
                    'master_tracking' => $masterTracking,
                ]);

                Cart::where('user_id', Auth::id())->delete();
                DB::commit();

                return $this->initiateEsewaPayment($masterTracking, $grandTotalAmount);

            } catch (\Exception $e) {
                DB::rollBack();
                return redirect()->back()->with('error', 'Failed to initialize eSewa payment order: ' . $e->getMessage());
            }
        }

        // 2. Bank Transfer / Net Banking Routing
        $bankMethods = ['bank', 'net_banking', 'bank_transfer', 'direct_bank'];
        if (in_array($paymentMethod, $bankMethods)) {
            DB::beginTransaction();

            try {
                $cartItems = Cart::where('user_id', Auth::id())
                    ->with(['product', 'varient'])
                    ->get();

                if ($cartItems->isEmpty()) {
                    DB::rollBack();
                    return redirect()->route('cart.index')->with('error', 'Your cart is empty.');
                }

                $groupedByVendor = $cartItems->groupBy('dokan_id');
                $masterTracking = 'ORD-' . strtoupper(uniqid());
                $grandTotalAmount = 0;

                foreach ($groupedByVendor as $dokanId => $items) {
                    $totalAmount = $items->sum(function ($item) {
                        $price = $item->varient->price ?? 0;
                        $discount = $item->varient->discount ?? 0;
                        $finalPrice = $price - ($price * $discount / 100);
                        return $finalPrice * $item->qty;
                    });

                    $grandTotalAmount += $totalAmount;

                    $order = Order::create([
                        'user_id' => Auth::id(),
                        'dokan_id' => $dokanId ?: null,
                        'shipping_address_id' => $request->shipping_address_id,
                        'tracking_number' => $masterTracking . '-' . ($dokanId ?: 'main'),
                        'total_amount' => $totalAmount,
                        'payment_method' => $request->payment_method,
                        'payment_status' => 'pending', 
                        'order_status' => 'pending',   
                    ]);

                    foreach ($items as $item) {
                        $price = $item->varient->price ?? 0;
                        $discount = $item->varient->discount ?? 0;
                        $finalPrice = $price - ($price * $discount / 100);

                        // Fixed: Added 'qty' and 'amount' to resolve the 1364 Field 'qty' doesn't have a default value error
                        OrderItem::create([
                            'order_id' => $order->id,
                            'product_id' => $item->product_id,
                            'varient_id' => $item->varient_id,         
                            'qty' => $item->qty,          
                            'amount' => $finalPrice * $item->qty,      
                        ]);
                    }
                }

                session()->put('pending_gateway_order', [
                    'user_id' => Auth::id(),
                    'master_tracking' => $masterTracking,
                    'total_amount' => $grandTotalAmount,
                ]);

                Cart::where('user_id', Auth::id())->delete();
                DB::commit();

                return redirect()->route('bank.pay');

            } catch (\Exception $e) {
                DB::rollBack();
                return redirect()->back()->with('error', 'Failed to place bank order: ' . $e->getMessage());
            }
        }

        // 3. Cash on Delivery (COD) / Direct Order Creation Block
        DB::beginTransaction();

        try {
            $cartItems = Cart::where('user_id', Auth::id())
                ->with(['product', 'varient'])
                ->get();

            if ($cartItems->isEmpty()) {
                DB::rollBack();
                return redirect()->route('cart.index')->with('error', 'Your cart is empty.');
            }

            foreach ($cartItems as $item) {
                if ($item->varient && $item->varient->qty < $item->qty) {
                    throw new \Exception("Insufficient stock for '{$item->product->name}'. Only {$item->varient->qty} left.");
                }
            }

            $groupedByVendor = $cartItems->groupBy('dokan_id');
            $masterTracking = 'ORD-' . strtoupper(uniqid());

            foreach ($groupedByVendor as $dokanId => $items) {
                $totalAmount = $items->sum(function ($item) {
                    $price = $item->varient->price ?? 0;
                    $discount = $item->varient->discount ?? 0;
                    $finalPrice = $price - ($price * $discount / 100);
                    return $finalPrice * $item->qty;
                });

                $order = Order::create([
                    'user_id' => Auth::id(),
                    'dokan_id' => $dokanId ?: null,
                    'shipping_address_id' => $request->shipping_address_id,
                    'tracking_number' => $masterTracking . '-' . ($dokanId ?: 'main'),
                    'total_amount' => $totalAmount,
                    'payment_method' => $request->payment_method,
                    'payment_status' => 'pending',
                    'order_status' => 'pending',
                ]);

                foreach ($items as $item) {
                    $price = $item->varient->price ?? 0;
                    $discount = $item->varient->discount ?? 0;
                    $finalPrice = $price - ($price * $discount / 100);

                    OrderItem::create([
                        'order_id' => $order->id,
                        'product_id' => $item->product_id,
                        'varient_id' => $item->varient_id,         
                        'qty' => $item->qty,          
                        'amount' => $finalPrice * $item->qty,      
                    ]);

                    if ($item->varient) {
                        $item->varient->decrement('qty', $item->qty);
                    }
                }
            }

            Cart::where('user_id', Auth::id())->delete();
            DB::commit();

            return redirect()->route('orders.index')->with('success', 'Order placed successfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Failed to place order: ' . $e->getMessage());
        }
    }

    public function bankPaymentPage()
    {
        $pendingOrderData = session()->get('pending_gateway_order');

        if (!$pendingOrderData) {
            return redirect()->route('cart.index')->with('error', 'Session expired or no pending bank transfer order found.');
        }

        $totalAmount = $pendingOrderData['total_amount'] ?? 0;
        $masterTracking = $pendingOrderData['master_tracking'] ?? '';

        return view('frontend.payments.bank-pay', compact('totalAmount', 'masterTracking'));
    }

    protected function initiateEsewaPayment($masterTracking, $totalAmount)
    {
        $transactionUuid = $masterTracking . '-' . time();
        $productCode = config('services.esewa.merchant_code', 'EPAYTEST');
        $secretKey = config('services.esewa.secret_key', '8gBm/:&EnhH.1/q');

        $signatureString = "total_amount={$totalAmount},transaction_uuid={$transactionUuid},product_code={$productCode}";
        $signature = base64_encode(hash_hmac('sha256', $signatureString, $secretKey, true));

        $data = [
            'amount' => $totalAmount,
            'tax_amount' => 0,
            'total_amount' => $totalAmount,
            'transaction_uuid' => $transactionUuid,
            'product_code' => $productCode,
            'product_service_charge' => 0,
            'product_delivery_charge' => 0,
            'success_url' => route('esewa.success'),
            'failure_url' => route('esewa.failure'),
            'signed_field_names' => 'total_amount,transaction_uuid,product_code',
            'signature' => $signature,
            'gateway_url' => config('services.esewa.url', 'https://rc-epay.esewa.com.np/api/epay/main/v2/form')
        ];

        return view('frontend.payments.esewa-redirect', compact('data'));
    }

    public function esewaSuccess(Request $request)
    {
        $encodedData = $request->input('data');

        if (!$encodedData) {
            return redirect()->route('orders.index')->with('error', 'Invalid payment response from eSewa.');
        }

        $decodedData = json_decode(base64_decode($encodedData), true);

        if (isset($decodedData['status']) && $decodedData['status'] === 'COMPLETE') {
            $pendingOrderData = session()->get('pending_gateway_order');

            if (!$pendingOrderData) {
                return redirect()->route('orders.index')->with('error', 'Session expired or order session data not found.');
            }

            $masterTracking = $pendingOrderData['master_tracking'];
            $orders = Order::with('order_items.varient')->where('tracking_number', 'like', $masterTracking . '%')->get();

            foreach($orders as $order) {
                foreach($order->order_items as $item) {
                    if ($item->varient) {
                        $item->varient->decrement('qty', $item->qty);
                    }
                }
            }

            $updatedCount = Order::where('tracking_number', 'like', $masterTracking . '%')
                ->update([
                    'payment_status' => 'paid',
                    'order_status' => 'processing'
                ]);

            if ($updatedCount > 0) {
                session()->forget('pending_gateway_order');
                return redirect()->route('orders.index')->with('success', 'Payment verified and order placed successfully via eSewa!');
            }

            return redirect()->route('orders.index')->with('error', 'No matching pending orders found for this transaction.');
        }

        return redirect()->route('orders.index')->with('error', 'Payment verification failed.');
    }

    public function bankSuccess(Request $request)
    {
        $request->validate([
            'payment_receipt' => 'required|file|mimes:jpeg,png,jpg,pdf|max:2048',
            'terms' => 'required|accepted',
        ]);

        $pendingOrderData = session()->get('pending_gateway_order');

        if (!$pendingOrderData) {
            return redirect()->route('orders.index')->with('error', 'Session expired or order session data not found.');
        }

        $masterTracking = $pendingOrderData['master_tracking'];

        if ($request->hasFile('payment_receipt')) {
            $file = $request->file('payment_receipt');
            $filename = 'receipt_' . time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $receiptPath = $file->storeAs('payment_receipts', $filename, 'public');
        }

        $orders = Order::with('order_items.varient')->where('tracking_number', 'like', $masterTracking . '%')->get();

        foreach($orders as $order) {
            foreach($order->order_items as $item) {
                if ($item->varient) {
                    $item->varient->decrement('qty', $item->qty);
                }
            }
        }

        $updatedCount = Order::where('tracking_number', 'like', $masterTracking . '%')
            ->update([
                'payment_status' => 'pending', 
                'order_status' => 'pending',   
            ]);

        if ($updatedCount > 0) {
            session()->forget('pending_gateway_order');
            return redirect()->route('orders.index')->with('success', 'Bank transfer receipt submitted successfully! Awaiting verification.');
        }

        return redirect()->route('orders.index')->with('error', 'No matching pending orders found for this bank transfer.');
    }

    public function esewaFailure(Request $request)
    {
        $pendingOrderData = session()->get('pending_gateway_order');

        if ($pendingOrderData && isset($pendingOrderData['master_tracking'])) {
            Order::where('tracking_number', 'like', $pendingOrderData['master_tracking'] . '%')->delete();
        }

        session()->forget('pending_gateway_order');
        return redirect()->route('cart.index')->with('error', 'eSewa payment was cancelled or failed.');
    }

    public function bankFailure(Request $request)
    {
        $pendingOrderData = session()->get('pending_gateway_order');

        if ($pendingOrderData && isset($pendingOrderData['master_tracking'])) {
            Order::where('tracking_number', 'like', $pendingOrderData['master_tracking'] . '%')->delete();
        }

        session()->forget('pending_gateway_order');
        return redirect()->route('cart.index')->with('error', 'Bank payment was cancelled or failed.');
    }

    public function show($id)
    {
        $order = Order::with(['order_items.product', 'order_items.varient.product', 'shipping_address', 'dokan'])
            ->where('user_id', Auth::id())
            ->findOrFail($id);

        return view('frontend.orders.show', compact('order'));
    }

    public function cancel($id)
    {
        $order = Order::where('user_id', Auth::id())->findOrFail($id);

        if ($order->order_status !== 'pending') {
            return redirect()->back()->with('error', 'Only pending orders can be cancelled.');
        }

        $order->update(['order_status' => 'cancelled']);

        return redirect()->back()->with('success', 'Order cancelled successfully.');
    }

    public function invoice($id)
    {
        $order = Order::with(['order_items.product', 'order_items.varient.product', 'shipping_address', 'dokan'])
            ->where('user_id', Auth::id())
            ->findOrFail($id);

        return view('frontend.orders.invoice', compact('order'));
    }

    public function trackForm()
    {
        return view('frontend.orders.track');
    }

    public function trackResult(Request $request)
    {
        $request->validate([
            'tracking_number' => 'required|string',
        ]);

        $query = $request->input('tracking_number');

        $order = Order::with([
                'order_items.product', 
                'order_items.varient.product', 
                'shipping_address', 
                'dokan'
            ])
            ->where('tracking_number', $query)
            ->orWhere('id', $query)
            ->first();

        if (!$order) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'No order found with that tracking number or ID.');
        }

        return view('frontend.orders.track', compact('order'));
    }
}