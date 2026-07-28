<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\Dokan;
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
     * Display a listing of user's orders
     */
    public function index()
    {
        $orders = Order::where('user_id', Auth::id())
            ->with(['dokan', 'shipping_address', 'order_items.product', 'order_items.varient'])
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('orders.index', compact('orders'));
    }

    /**
     * Show the order checkout page
     */
    public function checkout()
    {
        // Get user's cart items
        $cartItems = Cart::where('user_id', Auth::id())
            ->with(['product', 'varient', 'dokan'])
            ->get();

        if ($cartItems->isEmpty()) {
            return redirect()->route('cart.index')->with('error', 'Your cart is empty.');
        }

        // Get user's shipping addresses
        $addresses = ShippingAddress::where('user_id', Auth::id())->get();

        if ($addresses->isEmpty()) {
            return redirect()->route('shipping-address.create')
                ->with('error', 'Please add a shipping address first.');
        }

        // Calculate totals by vendor
        $vendorTotals = [];
        $totalAmount = 0;

        foreach ($cartItems as $item) {
            $vendorId = $item->dokan_id;
            $price = $item->varient->price;
            $discount = $item->varient->discount ?? 0;
            $finalPrice = $price - ($price * $discount / 100);
            $itemTotal = $finalPrice * $item->qty;

            if (!isset($vendorTotals[$vendorId])) {
                $vendorTotals[$vendorId] = [
                    'dokan' => $item->dokan,
                    'items' => [],
                    'subtotal' => 0,
                ];
            }

            $vendorTotals[$vendorId]['items'][] = $item;
            $vendorTotals[$vendorId]['subtotal'] += $itemTotal;
            $totalAmount += $itemTotal;
        }

        return view('orders.checkout', compact('cartItems', 'addresses', 'vendorTotals', 'totalAmount'));
    }

    /**
     * Store a newly created order
     */
    public function store(Request $request)
    {
        $request->validate([
            'shipping_address_id' => 'required|exists:shipping_address,id',
            'payment_method' => 'required|in:cod,kalti',
        ]);

        // Verify shipping address belongs to user
        $shippingAddress = ShippingAddress::where('id', $request->shipping_address_id)
            ->where('user_id', Auth::id())
            ->first();

        if (!$shippingAddress) {
            return back()->with('error', 'Invalid shipping address.');
        }

        // Get cart items
        $cartItems = Cart::where('user_id', Auth::id())
            ->with(['product', 'varient', 'dokan'])
            ->get();

        if ($cartItems->isEmpty()) {
            return redirect()->route('cart.index')->with('error', 'Your cart is empty.');
        }

        DB::beginTransaction();

        try {
            // Group items by vendor
            $vendorGroups = $cartItems->groupBy('dokan_id');

            foreach ($vendorGroups as $vendorId => $items) {
                $vendorTotal = 0;

                foreach ($items as $item) {
                    // Check stock availability
                    $varient = ProductVarient::find($item->varient_id);
                    if (!$varient || $varient->qty < $item->qty) {
                        throw new \Exception("Insufficient stock for {$item->product->title} - {$item->varient->title}");
                    }

                    // Calculate price
                    $price = $item->varient->price;
                    $discount = $item->varient->discount ?? 0;
                    $finalPrice = $price - ($price * $discount / 100);
                    $vendorTotal += $finalPrice * $item->qty;
                }

                // Create order
                $order = Order::create([
                    'user_id' => Auth::id(),
                    'dokan_id' => $vendorId,
                    'shipping_address_id' => $shippingAddress->id,
                    'total_amount' => $vendorTotal,
                    'status' => 'pending',
                    'payment_method' => $request->payment_method,
                    'payment_status' => $request->payment_method === 'cod' ? 'pending' : 'pending',
                ]);

                // Create order items
                foreach ($items as $item) {
                    $price = $item->varient->price;
                    $discount = $item->varient->discount ?? 0;
                    $finalPrice = $price - ($price * $discount / 100);

                    OrderItem::create([
                        'order_id' => $order->id,
                        'product_id' => $item->product_id,
                        'varient_id' => $item->varient_id,
                        'qty' => $item->qty,
                        'amount' => $finalPrice,
                    ]);

                    // Update stock
                    $varient = ProductVarient::find($item->varient_id);
                    if ($varient) {
                        $varient->qty -= $item->qty;
                        $varient->save();
                    }
                }

                // Clear cart items for this vendor
                Cart::where('user_id', Auth::id())
                    ->where('dokan_id', $vendorId)
                    ->delete();
            }

            DB::commit();

            return redirect()->route('orders.index')
                ->with('success', 'Order placed successfully!');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Order creation error: ' . $e->getMessage());
            return back()->with('error', 'Failed to place order: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified order
     */
    public function show($id)
    {
        $order = Order::where('id', $id)
            ->where('user_id', Auth::id())
            ->with(['dokan', 'shipping_address', 'order_items.product', 'order_items.varient'])
            ->firstOrFail();

        return view('orders.show', compact('order'));
    }

    /**
     * Cancel an order
     */
    public function cancel($id)
    {
        $order = Order::where('id', $id)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        // Check if order can be cancelled
        if (!in_array($order->status, ['pending', 'processing'])) {
            return back()->with('error', 'This order cannot be cancelled.');
        }

        DB::beginTransaction();

        try {
            // Restore stock
            foreach ($order->order_items as $item) {
                $varient = ProductVarient::find($item->varient_id);
                if ($varient) {
                    $varient->qty += $item->qty;
                    $varient->save();
                }
            }

            $order->status = 'cancelled';
            $order->save();

            DB::commit();

            return redirect()->route('orders.index')
                ->with('success', 'Order cancelled successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Order cancellation error: ' . $e->getMessage());
            return back()->with('error', 'Failed to cancel order.');
        }
    }

    /**
     * Update order status (for vendor/admin)
     */
    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:pending,processing,shipped,delivered,cancelled',
        ]);

        $order = Order::findOrFail($id);

        // Check if user is vendor or admin
        // You can add authorization logic here

        $order->status = $request->status;
        $order->save();

        return back()->with('success', 'Order status updated successfully.');
    }

    /**
     * Get order invoice
     */
    public function invoice($id)
    {
        $order = Order::where('id', $id)
            ->where('user_id', Auth::id())
            ->with(['dokan', 'shipping_address', 'order_items.product', 'order_items.varient'])
            ->firstOrFail();

        return view('orders.invoice', compact('order'));
    }

    /**
     * Get order by vendor (for vendor dashboard)
     */
    public function vendorOrders()
    {
        // Get the vendor's dokan
        $dokan = Dokan::where('user_id', Auth::id())->first();

        if (!$dokan) {
            return redirect()->route('home')->with('error', 'You are not a vendor.');
        }

        $orders = Order::where('dokan_id', $dokan->id)
            ->with(['user', 'shipping_address', 'order_items.product'])
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('orders.vendor-index', compact('orders'));
    }

    /**
     * Update payment status
     */
    public function updatePaymentStatus(Request $request, $id)
    {
        $request->validate([
            'payment_status' => 'required|in:pending,paid,failed',
        ]);

        $order = Order::findOrFail($id);

        // Check authorization (vendor or admin only)
        // Add your logic here

        $order->payment_status = $request->payment_status;
        $order->save();

        return back()->with('success', 'Payment status updated successfully.');
    }
}