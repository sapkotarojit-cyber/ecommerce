<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password as PasswordRule;

class ProfileController extends Controller
{
    public function edit()
    {
        $user = Auth::user();

        return view(
            'profile.edit',
            compact('user')
        );
    }

    public function update(
        Request $request
    ) {
        $user = Auth::user();

        $validated =
            $this->validateStrict($request, [
                'name' => [
                    'required',
                    'string',
                    'min:2',
                    'max:100',
                    'regex:/^[\pL\pM\pN .\'-]+$/u',
                ],

                'email' => [
                    'required',
                    'string',
                    'email:rfc',
                    'max:254',
                    Rule::unique(
                        'users',
                        'email'
                    )->ignore($user->id),
                ],
            ]);

        $user->update([
            'name' =>
                trim($validated['name']),

            'email' =>
                strtolower(
                    trim(
                        $validated['email']
                    )
                ),
        ]);

        return redirect()
            ->route('profile.edit')
            ->with(
                'success',
                'Profile updated successfully.'
            );
    }

    public function settings()
    {
        $user = Auth::user();

        return view(
            'profile.settings',
            compact('user')
        );
    }

    public function updatePassword(
        Request $request
    ) {
        $validated =
            $this->validateStrict($request, [
                'current_password' => [
                    'required',
                    'string',
                    'max:128',
                    'current_password',
                ],

                'password' => [
                    'required',
                    'string',
                    'min:8',
                    'max:128',
                    'confirmed',
                    PasswordRule::defaults(),
                ],
            ]);

        $user = Auth::user();

        $user->update([
            'password' =>
                Hash::make(
                    $validated['password']
                ),
        ]);

        /*
        |--------------------------------------------------------------------------
        | Invalidate existing sessions after password change
        |--------------------------------------------------------------------------
        |
        | The current session remains active. Other sessions should ideally
        | be invalidated using a session/token strategy appropriate to the
        | application's authentication setup.
        |
        */

        return redirect()
            ->route('settings')
            ->with(
                'success',
                'Password updated successfully.'
            );
    }
}