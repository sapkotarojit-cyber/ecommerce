<?php

namespace App\Http\Controllers\Vendor;

use Illuminate\Http\Request;

class OrderController
{
    public function update(Request $request, $id)
{
    $request->validate([
        'order_status' => 'required|string',
        'payment_status' => 'required|string',
    ]);

    $order = Order::findOrFail($id);

    // Update fields
    $order->update([
        'order_status' => $request->order_status,
        'payment_status' => $request->payment_status,
    ]);

    return redirect()->back()->with('success', 'Order status updated successfully!');
}
}
