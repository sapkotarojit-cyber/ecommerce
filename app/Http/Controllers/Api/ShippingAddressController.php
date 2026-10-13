<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ShippingAddress;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ShippingAddressController extends Controller
{
    public function index(): JsonResponse
    {
        $addresses = ShippingAddress::query()
            ->where('user_id', (int) Auth::id())
            ->orderByDesc('is_default_shipping')
            ->orderByDesc('id')
            ->get()
            ->map(fn (ShippingAddress $address) => $this->addressPayload($address))
            ->values()
            ->all();

        return response()->json(['success' => true, 'addresses' => $addresses]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $this->validateStrict($request, [
            'name' => ['bail', 'required', 'string', 'min:2', 'max:100', 'regex:/^[\pL\pM\pN .\'-]+$/u'],
            'phone' => ['bail', 'required', 'string', 'min:7', 'max:20', 'regex:/^[0-9+()\-.\s]+$/'],
            'region' => ['bail', 'required', 'string', 'min:2', 'max:100'],
            'address' => ['bail', 'required', 'string', 'min:5', 'max:500'],
            'landmark' => ['sometimes', 'nullable', 'string', 'max:200'],
            'address_type' => ['bail', 'required', 'string', 'max:20', 'in:home,office,work,other'],
        ]);

        $address = ShippingAddress::create([
            'user_id' => (int) Auth::id(),
            'name' => trim($validated['name']),
            'phone' => trim($validated['phone']),
            'region' => trim($validated['region']),
            'address' => trim($validated['address']),
            'landmark' => isset($validated['landmark']) && $validated['landmark'] !== null && trim((string) $validated['landmark']) !== ''
                ? trim((string) $validated['landmark'])
                : null,
            'address_type' => strtolower(trim($validated['address_type'])),
            'is_default_shipping' => false,
            'is_default_billing' => false,
        ]);

        $address->refresh();

        return response()->json([
            'success' => true,
            'message' => 'Shipping address created successfully.',
            'address' => $this->addressPayload($address),
        ], 201);
    }

    public function destroy(int $address): JsonResponse
    {
        $shippingAddress = ShippingAddress::query()
            ->whereKey($address)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        $shippingAddress->delete();

        return response()->json(['success' => true, 'message' => 'Shipping address deleted successfully.']);
    }

    public function setDefaultShipping(int $address): JsonResponse
    {
        $userId = (int) Auth::id();

        $shippingAddress = ShippingAddress::query()
            ->whereKey($address)
            ->where('user_id', $userId)
            ->firstOrFail();

        ShippingAddress::query()->where('user_id', $userId)->update(['is_default_shipping' => false]);
        $shippingAddress->update(['is_default_shipping' => true]);
        $shippingAddress->refresh();

        return response()->json([
            'success' => true,
            'message' => 'Default shipping address updated successfully.',
            'address' => $this->addressPayload($shippingAddress),
        ]);
    }

    private function addressPayload(ShippingAddress $address): array
    {
        return [
            'id' => (int) $address->id,
            'user_id' => (int) $address->user_id,
            'name' => (string) ($address->name ?? ''),
            'phone' => (string) ($address->phone ?? ''),
            'region' => (string) ($address->region ?? ''),
            'address' => (string) ($address->address ?? ''),
            'landmark' => $address->landmark !== null ? (string) $address->landmark : null,
            'address_type' => (string) ($address->address_type ?? 'home'),
            'is_default_shipping' => (bool) $address->is_default_shipping,
            'is_default_billing' => (bool) $address->is_default_billing,
            'created_at' => $address->created_at?->toISOString(),
            'updated_at' => $address->updated_at?->toISOString(),
        ];
    }
}
