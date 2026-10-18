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
            ->latest()
            ->paginate(9);

        return view(
            'frontend.shipping-address.index',
            compact('addresses')
        );
    }

    public function create()
    {
        return view(
            'frontend.shipping-address.create'
        );
    }

    public function store(Request $request)
    {
        $validated = $this->validateStrict($request, [
            'name' => [
                'bail',
                'required',
                'string',
                'min:2',
                'max:100',
                'regex:/^[\pL\pM\pN .\'-]+$/u',
            ],

            'phone' => [
                'bail',
                'required',
                'string',
                'min:7',
                'max:20',
                'regex:/^[0-9+\-\s()]+$/',
            ],

            'region' => [
                'bail',
                'required',
                'string',
                'min:2',
                'max:255',
            ],

            'address' => [
                'bail',
                'required',
                'string',
                'min:5',
                'max:500',
            ],

            'landmark' => [
                'nullable',
                'string',
                'max:255',
            ],

            'address_type' => [
                'required',
                'string',
                Rule::in([
                    'Home',
                    'Office',
                ]),
            ],

            'is_default_shipping' => [
                'sometimes',
                'boolean',
            ],

            'is_default_billing' => [
                'sometimes',
                'boolean',
            ],
        ]);

        $address = DB::transaction(function () use ($validated) {

            /*
             * Lock the authenticated user row.
             *
             * This serializes address/default mutations for the
             * same user and prevents two concurrent requests from
             * creating conflicting defaults.
             */
            User::query()
                ->whereKey(Auth::id())
                ->lockForUpdate()
                ->firstOrFail();

            $normalized = [
                'name' => trim($validated['name']),
                'phone' => trim($validated['phone']),
                'region' => trim($validated['region']),
                'address' => trim($validated['address']),
                'landmark' => trim((string) ($validated['landmark'] ?? '')),
                'address_type' => $validated['address_type'],
            ];

            // Repeated submits of the same address are idempotent.
            $existing = ShippingAddress::query()
                ->where('user_id', Auth::id())
                ->where('name', $normalized['name'])
                ->where('phone', $normalized['phone'])
                ->where('region', $normalized['region'])
                ->where('address', $normalized['address'])
                ->where('address_type', $normalized['address_type'])
                ->whereRaw("COALESCE(landmark, '') = ?", [$normalized['landmark']])
                ->first();

            if ($existing) {
                $existing->setAttribute('_was_existing', true);
                return $existing;
            }

            $makeDefault = (bool) (
                $validated['is_default_shipping'] ?? false
            );

            if ($makeDefault) {
                ShippingAddress::query()
                    ->where('user_id', Auth::id())
                    ->update([
                        'is_default_shipping' => false,
                    ]);
            }

            $address = ShippingAddress::create([
                'user_id' => Auth::id(),

                'name' => $normalized['name'],

                'phone' => $normalized['phone'],

                'region' => $normalized['region'],

                'address' => $normalized['address'],

                'landmark' => $normalized['landmark'] !== ''
                    ? $normalized['landmark']
                    : null,

                'address_type' => $validated['address_type'],

                'is_default_shipping' => $makeDefault,

                'is_default_billing' => (bool) (
                    $validated['is_default_billing'] ?? false
                ),
            ]);

            /*
             * If this is the user's first address, it becomes
             * the default shipping address automatically.
             */
            if (
                !ShippingAddress::query()
                    ->where('user_id', Auth::id())
                    ->where('is_default_shipping', true)
                    ->exists()
            ) {
                $address->update([
                    'is_default_shipping' => true,
                ]);
            }

            return $address;
        });

        return redirect()
            ->route('shipping-address.index')
            ->with(
                'success',
                'Shipping address has been added successfully.'
            );
    }

    public function quickStore(Request $request)
    {
        $validated = $this->validateStrict($request, [
            'name' => [
                'bail',
                'required',
                'string',
                'min:2',
                'max:100',
                'regex:/^[\pL\pM\pN .\'-]+$/u',
            ],

            'address_type' => [
                'required',
                'string',
                Rule::in([
                    'Home',
                    'Office',
                ]),
            ],

            'phone' => [
                'bail',
                'required',
                'string',
                'min:7',
                'max:20',
                'regex:/^[0-9+\-\s()]+$/',
            ],

            'region' => [
                'bail',
                'required',
                'string',
                'min:2',
                'max:255',
            ],

            'address' => [
                'bail',
                'required',
                'string',
                'min:5',
                'max:500',
            ],

            'landmark' => [
                'nullable',
                'string',
                'max:255',
            ],
        ]);

        $address = DB::transaction(function () use ($validated) {

            User::query()
                ->whereKey(Auth::id())
                ->lockForUpdate()
                ->firstOrFail();

            $normalized = [
                'name' => trim($validated['name']),
                'address_type' => $validated['address_type'],
                'phone' => trim($validated['phone']),
                'region' => trim($validated['region']),
                'address' => trim($validated['address']),
                'landmark' => trim((string) ($validated['landmark'] ?? '')),
            ];

            $existing = ShippingAddress::query()
                ->where('user_id', Auth::id())
                ->where('name', $normalized['name'])
                ->where('address_type', $normalized['address_type'])
                ->where('phone', $normalized['phone'])
                ->where('region', $normalized['region'])
                ->where('address', $normalized['address'])
                ->whereRaw("COALESCE(landmark, '') = ?", [$normalized['landmark']])
                ->first();

            if ($existing) {
                $existing->setAttribute('_was_existing', true);
                return $existing;
            }

            $hasAddress = ShippingAddress::query()
                ->where('user_id', Auth::id())
                ->exists();

            return ShippingAddress::create([
                'user_id' => Auth::id(),
                'name' => $normalized['name'],
                'address_type' => $normalized['address_type'],
                'phone' => $normalized['phone'],
                'region' => $normalized['region'],
                'address' => $normalized['address'],
                'landmark' => $normalized['landmark'] !== ''
                    ? $normalized['landmark']
                    : null,
                'is_default_shipping' => !$hasAddress,
                'is_default_billing' => false,
            ]);
        });

        return response()->json([
            'success' => true,
            'message' => 'Shipping address has been added successfully.',
            'created' => ! (bool) $address->getAttribute('_was_existing'),
            'address' => $address,
        ]);
    }

    public function edit(ShippingAddress $address)
    {
        $this->authorizeAddress($address);

        return view(
            'frontend.shipping-address.edit',
            compact('address')
        );
    }

    public function update(
        Request $request,
        ShippingAddress $address
    ) {
        $this->authorizeAddress($address);

        $validated = $this->validateStrict($request, [
            'name' => [
                'bail',
                'required',
                'string',
                'min:2',
                'max:100',
                'regex:/^[\pL\pM\pN .\'-]+$/u',
            ],

            'phone' => [
                'bail',
                'required',
                'string',
                'min:7',
                'max:20',
                'regex:/^[0-9+\-\s()]+$/',
            ],

            'region' => [
                'bail',
                'required',
                'string',
                'min:2',
                'max:255',
            ],

            'address' => [
                'bail',
                'required',
                'string',
                'min:5',
                'max:500',
            ],

            'landmark' => [
                'nullable',
                'string',
                'max:255',
            ],

            'address_type' => [
                'required',
                'string',
                Rule::in([
                    'Home',
                    'Office',
                ]),
            ],

            'is_default_shipping' => [
                'sometimes',
                'boolean',
            ],

            'is_default_billing' => [
                'sometimes',
                'boolean',
            ],
        ]);

        DB::transaction(function () use (
            $validated,
            $address
        ) {
            User::query()
                ->whereKey(Auth::id())
                ->lockForUpdate()
                ->firstOrFail();

            /*
             * Re-read the address inside the transaction after
             * locking the user's address mutations.
             */
            $lockedAddress = ShippingAddress::query()
                ->where('user_id', Auth::id())
                ->whereKey($address->id)
                ->lockForUpdate()
                ->firstOrFail();

            $makeDefault = (bool) (
                $validated['is_default_shipping'] ?? false
            );

            if ($makeDefault) {
                ShippingAddress::query()
                    ->where('user_id', Auth::id())
                    ->whereKeyNot($lockedAddress->id)
                    ->update([
                        'is_default_shipping' => false,
                    ]);
            }

            $lockedAddress->update([
                'name' =>
                    trim($validated['name']),

                'phone' =>
                    trim($validated['phone']),

                'region' =>
                    trim($validated['region']),

                'address' =>
                    trim($validated['address']),

                'landmark' =>
                    isset($validated['landmark'])
                        ? trim($validated['landmark'])
                        : null,

                'address_type' =>
                    $validated['address_type'],

                'is_default_shipping' =>
                    $makeDefault,

                'is_default_billing' =>
                    (bool) (
                        $validated['is_default_billing']
                        ?? false
                    ),
            ]);

            /*
             * Never leave the user without a default shipping
             * address if this address was the only default.
             */
            if (
                !ShippingAddress::query()
                    ->where('user_id', Auth::id())
                    ->where('is_default_shipping', true)
                    ->exists()
            ) {
                $lockedAddress->update([
                    'is_default_shipping' => true,
                ]);
            }
        });

        return redirect()
            ->route('shipping-address.index')
            ->with(
                'success',
                'Shipping address updated successfully!'
            );
    }

    public function destroy(
        ShippingAddress $address
    ) {
        $this->authorizeAddress($address);

        DB::transaction(function () use ($address) {

            User::query()
                ->whereKey(Auth::id())
                ->lockForUpdate()
                ->firstOrFail();

            $lockedAddress = ShippingAddress::query()
                ->where('user_id', Auth::id())
                ->whereKey($address->id)
                ->lockForUpdate()
                ->firstOrFail();

            $count = ShippingAddress::query()
                ->where('user_id', Auth::id())
                ->count();

            if ($count <= 1) {
                throw new \RuntimeException(
                    'You cannot delete your only shipping address.'
                );
            }

            $wasDefault =
                (bool) $lockedAddress->is_default_shipping;

            $lockedAddress->delete();

            if ($wasDefault) {
                $newDefault = ShippingAddress::query()
                    ->where('user_id', Auth::id())
                    ->latest()
                    ->first();

                if ($newDefault) {
                    $newDefault->update([
                        'is_default_shipping' => true,
                    ]);
                }
            }
        });

        return redirect()
            ->route('shipping-address.index')
            ->with(
                'success',
                'Shipping address deleted successfully!'
            );
    }

    public function setDefault(
        ShippingAddress $address
    ) {
        $this->authorizeAddress($address);

        DB::transaction(function () use ($address) {

            User::query()
                ->whereKey(Auth::id())
                ->lockForUpdate()
                ->firstOrFail();

            $lockedAddress = ShippingAddress::query()
                ->where('user_id', Auth::id())
                ->whereKey($address->id)
                ->lockForUpdate()
                ->firstOrFail();

            ShippingAddress::query()
                ->where('user_id', Auth::id())
                ->update([
                    'is_default_shipping' => false,
                ]);

            $lockedAddress->update([
                'is_default_shipping' => true,
            ]);
        });

        return redirect()
            ->route('shipping-address.index')
            ->with(
                'success',
                'Default shipping address updated successfully!'
            );
    }

    private function authorizeAddress(
        ShippingAddress $address
    ): void {
        abort_unless(
            (int) $address->user_id ===
            (int) Auth::id(),
            403
        );
    }
}