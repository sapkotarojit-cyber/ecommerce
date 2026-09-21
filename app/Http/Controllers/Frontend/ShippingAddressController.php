<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\ShippingAddress;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ShippingAddressController extends Controller
{
    public function index()
    {
        $addresses = ShippingAddress::where('user_id', Auth::id())
            ->orderBy('is_default_shipping', 'desc') // 👈 Fixed column name here
            ->orderBy('created_at', 'desc')
            ->paginate(9);

        return view('frontend.shipping-address.index', compact('addresses'));
    }

    public function create()
    {
        return view('frontend.shipping-address.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'region' => 'required|string|max:255',
            'address' => 'required|string|max:500',
            'landmark' => 'nullable|string|max:255',
            'address_type' => 'required|in:Home,Office',
            'is_default_shipping' => 'nullable|boolean',
            'is_default_billing' => 'nullable|boolean',
        ]);

        $address = ShippingAddress::create([
            'user_id' => Auth::id(),
            'name' => $validated['name'],
            'phone' => $validated['phone'],
            'region' => $validated['region'],
            'address' => $validated['address'],
            'landmark' => $validated['landmark'] ?? null,
            'address_type' => $validated['address_type'],
            'is_default_shipping' => $request->has('is_default_shipping') && $request->is_default_shipping == 1,
            'is_default_billing' => $request->has('is_default_billing') && $request->is_default_billing == 1,
        ]);

        if ($address->is_default_shipping) {
            ShippingAddress::where('user_id', Auth::id())
                ->where('id', '!=', $address->id)
                ->update(['is_default_shipping' => false]);
        }

        return redirect()
            ->route('shipping-address.index')
            ->with('success', 'Shipping address added successfully!');
    }


public function quickStore(Request $request)
{
    $request->validate([
        'name' => 'required|string|max:255',
        'address_type' => 'required|string|max:50',
        'phone' => 'required|string|max:20',
        'region' => 'required|string|max:255',
        'address' => 'required|string',
    ]);

    $address = ShippingAddress::create([
        'user_id' => Auth::id(),
        'name' => $request->name,
        'address_type' => $request->address_type,
        'phone' => $request->phone,
        'region' => $request->region,
        'address' => $request->address,
        'is_default_shipping' => ShippingAddress::where('user_id', Auth::id())->doesntExist(), // Makes default if it's the first one
    ]);

    return response()->json([
        'success' => true,
        'message' => 'Address added successfully!',
        'address' => $address
    ]);
}

    public function edit(ShippingAddress $address)
    {
        if ($address->user_id !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }

        return view('frontend.shipping-address.edit', compact('address'));
    }

    public function update(Request $request, ShippingAddress $address)
    {
        if ($address->user_id !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'region' => 'required|string|max:255',
            'address' => 'required|string|max:500',
            'landmark' => 'nullable|string|max:255',
            'address_type' => 'required|in:Home,Office',
            'is_default_shipping' => 'nullable|boolean',
            'is_default_billing' => 'nullable|boolean',
        ]);

        $address->update([
            'name' => $validated['name'],
            'phone' => $validated['phone'],
            'region' => $validated['region'],
            'address' => $validated['address'],
            'landmark' => $validated['landmark'] ?? null,
            'address_type' => $validated['address_type'],
            'is_default_shipping' => $request->has('is_default_shipping') && $request->is_default_shipping == 1,
            'is_default_billing' => $request->has('is_default_billing') && $request->is_default_billing == 1,
        ]);

        if ($address->is_default_shipping) {
            ShippingAddress::where('user_id', Auth::id())
                ->where('id', '!=', $address->id)
                ->update(['is_default_shipping' => false]);
        }

        return redirect()
            ->route('shipping-address.index')
            ->with('success', 'Shipping address updated successfully!');
    }

    public function destroy(ShippingAddress $address)
    {
        if ($address->user_id !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }

        $addressCount = ShippingAddress::where('user_id', Auth::id())->count();
        if ($addressCount <= 1) {
            return redirect()
                ->route('shipping-address.index')
                ->with('error', 'You cannot delete your only shipping address. Add a new one first.');
        }

        $deletedDefault = $address->is_default_shipping;
        $address->delete();

        if ($deletedDefault) {
            $newDefault = ShippingAddress::where('user_id', Auth::id())->first();
            if ($newDefault) {
                $newDefault->update(['is_default_shipping' => true]);
            }
        }

        return redirect()
            ->route('shipping-address.index')
            ->with('success', 'Shipping address deleted successfully!');
    }

    public function setDefault(ShippingAddress $address)
    {
        if ($address->user_id !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }

        ShippingAddress::where('user_id', Auth::id())->update(['is_default_shipping' => false]);
        $address->update(['is_default_shipping' => true]);

        return redirect()
            ->route('shipping-address.index')
            ->with('success', 'Default shipping address updated successfully!');
    }
}