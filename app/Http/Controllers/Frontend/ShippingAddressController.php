<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\ShippingAddress;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class ShippingAddressController extends Controller
{
    public function index()
    {
        $addresses = ShippingAddress::query()
            ->where('user_id', Auth::id())
            ->orderByDesc('is_default_shipping')
            ->latest('id')
            ->paginate(9);

        return view('frontend.shipping-address.index', compact('addresses'));
    }

    public function create()
    {
        return view('frontend.shipping-address.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate($this->addressRules(true));

        $address = DB::transaction(function () use ($validated) {
            $userId = (int) Auth::id();
            User::query()->whereKey($userId)->lockForUpdate()->firstOrFail();

            $normalized = $this->normalizeAddress($validated);
            $existing = $this->findDuplicate($userId, $normalized);
            if ($existing) {
                return $existing;
            }

            $hasAddress = ShippingAddress::query()->where('user_id', $userId)->exists();
            $makeDefault = (bool) ($validated['is_default_shipping'] ?? false) || !$hasAddress;

            if ($makeDefault) {
                ShippingAddress::query()->where('user_id', $userId)
                    ->update(['is_default_shipping' => false]);
            }

            return ShippingAddress::create([
                'user_id' => $userId,
                ...$normalized,
                'is_default_shipping' => $makeDefault,
                'is_default_billing' => (bool) ($validated['is_default_billing'] ?? false),
            ]);
        });

        return redirect()->route('shipping-address.index')
            ->with('success', 'Shipping address has been added successfully.');
    }

    public function quickStore(Request $request)
    {
        $validated = $request->validate($this->addressRules(false));

        $address = DB::transaction(function () use ($validated) {
            $userId = (int) Auth::id();
            User::query()->whereKey($userId)->lockForUpdate()->firstOrFail();
            $normalized = $this->normalizeAddress($validated);

            $existing = $this->findDuplicate($userId, $normalized);
            if ($existing) {
                $existing->setAttribute('_was_existing', true);
                return $existing;
            }

            $hasAddress = ShippingAddress::query()->where('user_id', $userId)->exists();
            return ShippingAddress::create([
                'user_id' => $userId,
                ...$normalized,
                'is_default_shipping' => !$hasAddress,
                'is_default_billing' => false,
            ]);
        });

        return response()->json([
            'success' => true,
            'message' => 'Shipping address has been added successfully.',
            'created' => !(bool) $address->getAttribute('_was_existing'),
            'address' => $address,
        ]);
    }

    public function edit(ShippingAddress $address)
    {
        $this->authorizeAddress($address);
        return view('frontend.shipping-address.edit', compact('address'));
    }

    public function update(Request $request, ShippingAddress $address)
    {
        $this->authorizeAddress($address);
        $validated = $request->validate($this->addressRules(true));

        DB::transaction(function () use ($validated, $address) {
            $userId = (int) Auth::id();
            User::query()->whereKey($userId)->lockForUpdate()->firstOrFail();
            $locked = ShippingAddress::query()->where('user_id', $userId)
                ->whereKey($address->id)->lockForUpdate()->firstOrFail();

            $makeDefault = (bool) ($validated['is_default_shipping'] ?? false);
            if ($makeDefault) {
                ShippingAddress::query()->where('user_id', $userId)
                    ->where('id', '!=', $locked->id)
                    ->update(['is_default_shipping' => false]);
            }

            $locked->update([
                ...$this->normalizeAddress($validated),
                'is_default_shipping' => $makeDefault,
                'is_default_billing' => (bool) ($validated['is_default_billing'] ?? false),
            ]);

            if (!ShippingAddress::query()->where('user_id', $userId)->where('is_default_shipping', true)->exists()) {
                $locked->update(['is_default_shipping' => true]);
            }
        });

        return redirect()->route('shipping-address.index')
            ->with('success', 'Shipping address updated successfully!');
    }

    public function destroy(ShippingAddress $address)
    {
        $this->authorizeAddress($address);

        DB::transaction(function () use ($address) {
            $userId = (int) Auth::id();
            User::query()->whereKey($userId)->lockForUpdate()->firstOrFail();
            $locked = ShippingAddress::query()->where('user_id', $userId)
                ->whereKey($address->id)->lockForUpdate()->firstOrFail();
            $wasDefault = (bool) $locked->is_default_shipping;
            $locked->delete(); // Deleting the last address is allowed.

            if ($wasDefault) {
                ShippingAddress::query()->where('user_id', $userId)
                    ->update(['is_default_shipping' => false]);
                $newDefault = ShippingAddress::query()->where('user_id', $userId)
                    ->latest('id')->first();
                if ($newDefault) {
                    $newDefault->update(['is_default_shipping' => true]);
                }
            }
        });

        return redirect()->route('shipping-address.index')
            ->with('success', 'Shipping address deleted successfully!');
    }

    public function setDefault(ShippingAddress $address)
    {
        $this->authorizeAddress($address);
        DB::transaction(function () use ($address) {
            $userId = (int) Auth::id();
            User::query()->whereKey($userId)->lockForUpdate()->firstOrFail();
            $locked = ShippingAddress::query()->where('user_id', $userId)
                ->whereKey($address->id)->lockForUpdate()->firstOrFail();
            ShippingAddress::query()->where('user_id', $userId)
                ->update(['is_default_shipping' => false]);
            $locked->update(['is_default_shipping' => true]);
        });

        return redirect()->route('shipping-address.index')
            ->with('success', 'Default shipping address updated successfully!');
    }

    private function addressRules(bool $includeDefaults): array
    {
        $rules = [
            'name' => ['bail', 'required', 'string', 'min:2', 'max:100', 'regex:/^[\pL\pM\pN .\'-]+$/u'],
            'phone' => ['bail', 'required', 'string', 'min:7', 'max:20', 'regex:/^[0-9+\-\s()]+$/'],
            'region' => ['bail', 'required', 'string', 'min:2', 'max:255'],
            'address' => ['bail', 'required', 'string', 'min:5', 'max:500'],
            'landmark' => ['nullable', 'string', 'max:255'],
            'address_type' => ['required', 'string', Rule::in(['Home', 'Office'])],
        ];
        if ($includeDefaults) {
            $rules['is_default_shipping'] = ['sometimes', 'boolean'];
            $rules['is_default_billing'] = ['sometimes', 'boolean'];
        }
        return $rules;
    }

    private function normalizeAddress(array $data): array
    {
        $landmark = trim((string) ($data['landmark'] ?? ''));
        return [
            'name' => trim($data['name']),
            'phone' => trim($data['phone']),
            'region' => trim($data['region']),
            'address' => trim($data['address']),
            'landmark' => $landmark !== '' ? $landmark : null,
            'address_type' => $data['address_type'],
        ];
    }

    private function findDuplicate(int $userId, array $data): ?ShippingAddress
    {
        return ShippingAddress::query()->where('user_id', $userId)
            ->where('name', $data['name'])
            ->where('phone', $data['phone'])
            ->where('region', $data['region'])
            ->where('address', $data['address'])
            ->where('address_type', $data['address_type'])
            ->whereRaw("COALESCE(landmark, '') = ?", [$data['landmark'] ?? ''])
            ->first();
    }

    private function authorizeAddress(ShippingAddress $address): void
    {
        abort_unless((int) $address->user_id === (int) Auth::id(), 403);
    }
}