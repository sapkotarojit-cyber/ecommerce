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
            ->orderBy('is_default', 'desc')
            ->orderBy('created_at', 'desc')
            ->paginate(9);

        return view('shipping-address.index', compact('addresses'));
    }

    public function create()
    {
        return view('shipping-address.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'contact_no' => 'required|string|max:20',
            'full_address' => 'required|string|max:500',
            'is_default' => 'nullable|boolean',
        ]);

        $addresses = ShippingAddress::create([
            'user_id' => Auth::id(),
            'title' => $validated['title'],
            'contact_no' => $validated['contact_no'],
            'full_address' => $validated['full_address'],
            'is_default' => $request->has('is_default') && $request->is_default == 1,
        ]);

        if ($addresses->is_default) {
            ShippingAddress::where('user_id', Auth::id())
                ->where('id', '!=', $addresses->id)
                ->update(['is_default' => false]);
        }

        return redirect()
            ->route('shipping-address.index')
            ->with('success', 'Shipping address added successfully!');
    }

    public function quickStore(Request $request)
    {
        $validated = $request->validate([
            'title'        => 'required|string|max:255',
            'contact_no'   => 'required|string|max:20',
            'full_address' => 'required|string|max:500',
        ]);

        $hasAddresses = ShippingAddress::where('user_id', Auth::id())->exists();

        $address = ShippingAddress::create([
            'user_id'      => Auth::id(),
            'title'        => $validated['title'],
            'contact_no'   => $validated['contact_no'],
            'full_address' => $validated['full_address'],
            'is_default'   => !$hasAddresses,
        ]);

        return response()->json([
            'success' => true,
            'address' => $address,
        ]);
    }

    public function edit(ShippingAddress $addresses)
    {
        if ($addresses->user_id !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }

        return view('frontend.shipping-addresses.edit', compact('address'));
    }

    public function update(Request $request, ShippingAddress $addresses)
    {
        if ($addresses->user_id !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'contact_no' => 'required|string|max:20',
            'full_address' => 'required|string|max:500',
            'is_default' => 'nullable|boolean',
        ]);

        $addresses->update([
            'title' => $validated['title'],
            'contact_no' => $validated['contact_no'],
            'full_address' => $validated['full_address'],
            'is_default' => $request->has('is_default') && $request->is_default == 1,
        ]);

        if ($addresses->is_default) {
            ShippingAddress::where('user_id', Auth::id())
                ->where('id', '!=', $addresses->id)
                ->update(['is_default' => false]);
        }

        return redirect()
            ->route('shipping-address.index')
            ->with('success', 'Shipping address updated successfully!');
    }

    public function destroy(ShippingAddress $addresses)
    {
        if ($addresses->user_id !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }

        $addressCount = ShippingAddress::where('user_id', Auth::id())->count();
        if ($addressCount <= 1) {
            return redirect()
                ->route('shipping-address.index')
                ->with('error', 'You cannot delete your only shipping address. Add a new one first.');
        }

        $deletedDefault = $addresses->is_default;
        $addresses->delete();

        if ($deletedDefault) {
            $newDefault = ShippingAddress::where('user_id', Auth::id())->first();
            if ($newDefault) {
                $newDefault->update(['is_default' => true]);
            }
        }

        return redirect()
            ->route('shipping-address.index')
            ->with('success', 'Shipping address deleted successfully!');
    }

    public function setDefault(ShippingAddress $addresses)
    {
        if ($addresses->user_id !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }

        ShippingAddress::where('user_id', Auth::id())->update(['is_default' => false]);
        $addresses->update(['is_default' => true]);

        return redirect()
            ->route('shipping-address.index')
            ->with('success', 'Default shipping address updated successfully!');
    }
}
