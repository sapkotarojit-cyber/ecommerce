<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\ShippingAddress;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class ShippingAddressController extends Controller
{
    public function index()
    {
        $addresses =
            ShippingAddress::query()
                ->where(
                    'user_id',
                    Auth::id()
                )
                ->orderByDesc(
                    'is_default_shipping'
                )
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
        $validated =
            $request->validate([
                'name' => [
                    'required',
                    'string',
                    'min:2',
                    'max:100',
                    'regex:/^[\pL\pM\pN .\'-]+$/u',
                ],

                'phone' => [
                    'required',
                    'string',
                    'min:7',
                    'max:20',
                    'regex:/^[0-9+\-\s()]+$/',
                ],

                'region' => [
                    'required',
                    'string',
                    'min:2',
                    'max:255',
                ],

                'address' => [
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

        $address =
            ShippingAddress::create([
                'user_id' =>
                    Auth::id(),

                'name' =>
                    trim($validated['name']),

                'phone' =>
                    trim($validated['phone']),

                'region' =>
                    trim($validated['region']),

                'address' =>
                    trim($validated['address']),

                'landmark' =>
                    isset(
                        $validated['landmark']
                    )
                        ? trim(
                            $validated['landmark']
                        )
                        : null,

                'address_type' =>
                    $validated[
                        'address_type'
                    ],

                'is_default_shipping' =>
                    (bool) (
                        $validated[
                            'is_default_shipping'
                        ] ?? false
                    ),

                'is_default_billing' =>
                    (bool) (
                        $validated[
                            'is_default_billing'
                        ] ?? false
                    ),
            ]);

        /*
        |--------------------------------------------------------------------------
        | Maintain one default shipping address
        |--------------------------------------------------------------------------
        */

        if (
            $address->is_default_shipping
        ) {
            ShippingAddress::query()
                ->where(
                    'user_id',
                    Auth::id()
                )
                ->whereKeyNot(
                    $address->id
                )
                ->update([
                    'is_default_shipping' =>
                        false,
                ]);
        }

        /*
        |--------------------------------------------------------------------------
        | First address becomes default
        |--------------------------------------------------------------------------
        */

        if (
            !ShippingAddress::query()
                ->where(
                    'user_id',
                    Auth::id()
                )
                ->where(
                    'is_default_shipping',
                    true
                )
                ->exists()
        ) {
            $address->update([
                'is_default_shipping' =>
                    true,
            ]);
        }

        return redirect()
            ->route(
                'shipping-address.index'
            )
            ->with(
                'success',
                'Shipping address added successfully!'
            );
    }

    public function quickStore(
        Request $request
    ) {
        $validated =
            $request->validate([
                'name' => [
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
                    'required',
                    'string',
                    'min:7',
                    'max:20',
                    'regex:/^[0-9+\-\s()]+$/',
                ],

                'region' => [
                    'required',
                    'string',
                    'min:2',
                    'max:255',
                ],

                'address' => [
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

        $hasAddress =
            ShippingAddress::query()
                ->where(
                    'user_id',
                    Auth::id()
                )
                ->exists();

        $address =
            ShippingAddress::create([
                'user_id' =>
                    Auth::id(),

                'name' =>
                    trim($validated['name']),

                'address_type' =>
                    $validated[
                        'address_type'
                    ],

                'phone' =>
                    trim($validated['phone']),

                'region' =>
                    trim($validated['region']),

                'address' =>
                    trim($validated['address']),

                'landmark' =>
                    isset(
                        $validated['landmark']
                    )
                        ? trim(
                            $validated['landmark']
                        )
                        : null,

                'is_default_shipping' =>
                    !$hasAddress,

                'is_default_billing' =>
                    false,
            ]);

        return response()->json([
            'success' => true,
            'message' =>
                'Address added successfully!',
            'address' => $address,
        ]);
    }

    public function edit(
        ShippingAddress $address
    ) {
        $this->authorizeAddress(
            $address
        );

        return view(
            'frontend.shipping-address.edit',
            compact('address')
        );
    }

    public function update(
        Request $request,
        ShippingAddress $address
    ) {
        $this->authorizeAddress(
            $address
        );

        $validated =
            $request->validate([
                'name' => [
                    'required',
                    'string',
                    'min:2',
                    'max:100',
                    'regex:/^[\pL\pM\pN .\'-]+$/u',
                ],

                'phone' => [
                    'required',
                    'string',
                    'min:7',
                    'max:20',
                    'regex:/^[0-9+\-\s()]+$/',
                ],

                'region' => [
                    'required',
                    'string',
                    'min:2',
                    'max:255',
                ],

                'address' => [
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

        $address->update([
            'name' =>
                trim($validated['name']),

            'phone' =>
                trim($validated['phone']),

            'region' =>
                trim($validated['region']),

            'address' =>
                trim($validated['address']),

            'landmark' =>
                isset(
                    $validated['landmark']
                )
                    ? trim(
                        $validated['landmark']
                    )
                    : null,

            'address_type' =>
                $validated[
                    'address_type'
                ],

            'is_default_shipping' =>
                (bool) (
                    $validated[
                        'is_default_shipping'
                    ] ?? false
                ),

            'is_default_billing' =>
                (bool) (
                    $validated[
                        'is_default_billing'
                    ] ?? false
                ),
        ]);

        if (
            $address->is_default_shipping
        ) {
            $this->removeOtherShippingDefaults(
                $address
            );
        }

        return redirect()
            ->route(
                'shipping-address.index'
            )
            ->with(
                'success',
                'Shipping address updated successfully!'
            );
    }

    public function destroy(
        ShippingAddress $address
    ) {
        $this->authorizeAddress(
            $address
        );

        $count =
            ShippingAddress::query()
                ->where(
                    'user_id',
                    Auth::id()
                )
                ->count();

        if ($count <= 1) {
            return redirect()
                ->route(
                    'shipping-address.index'
                )
                ->with(
                    'error',
                    'You cannot delete your only shipping address.'
                );
        }

        $wasDefault =
            (bool)
            $address->is_default_shipping;

        $address->delete();

        if ($wasDefault) {
            $newDefault =
                ShippingAddress::query()
                    ->where(
                        'user_id',
                        Auth::id()
                    )
                    ->latest()
                    ->first();

            if ($newDefault) {
                $newDefault->update([
                    'is_default_shipping' =>
                        true,
                ]);
            }
        }

        return redirect()
            ->route(
                'shipping-address.index'
            )
            ->with(
                'success',
                'Shipping address deleted successfully!'
            );
    }

    public function setDefault(
        ShippingAddress $address
    ) {
        $this->authorizeAddress(
            $address
        );

        ShippingAddress::query()
            ->where(
                'user_id',
                Auth::id()
            )
            ->update([
                'is_default_shipping' =>
                    false,
            ]);

        $address->update([
            'is_default_shipping' =>
                true,
        ]);

        return redirect()
            ->route(
                'shipping-address.index'
            )
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

    private function removeOtherShippingDefaults(
        ShippingAddress $address
    ): void {
       ShippingAddress::query()
    ->where('user_id', Auth::id())
    ->where('id', '!=', $address->id)
    ->update([
        'is_default_shipping' => false,
    ]);
    }
}