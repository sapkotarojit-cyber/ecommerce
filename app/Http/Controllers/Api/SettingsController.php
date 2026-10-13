<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password as PasswordRule;

class SettingsController extends Controller
{
    /**
     * GET /api/settings
     *
     * Same account data used by the web profile/settings pages.
     */
    public function show(Request $request): JsonResponse
    {
        $user = $request->user();

        return response()->json([
            'success' => true,
            'user' => $this->userData($user),
        ]);
    }

    /**
     * PUT /api/settings
     *
     * Mirrors the web ProfileController::update() validation and update
     * behavior. The web controller is not changed.
     */
    public function update(Request $request): JsonResponse
    {
        $user = $request->user();

        $validated = $this->validateStrict($request, [
            'name' => [
                'bail',
                'required',
                'string',
                'min:2',
                'max:100',
                'regex:/^[\pL\pM\pN .\'-]+$/u',
            ],

            'email' => [
                'bail',
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

        $name = trim($validated['name']);
        $email = strtolower(trim($validated['email']));

        /*
         * Keep email uniqueness behavior safe even on databases whose
         * comparison collation is case-sensitive.
         */
        $emailExists = \App\Models\User::query()
            ->whereRaw('LOWER(email) = ?', [$email])
            ->where('id', '!=', $user->id)
            ->exists();

        if ($emailExists) {
            return response()->json([
                'success' => false,
                'message' => 'That email address is already in use.',
                'errors' => [
                    'email' => [
                        'That email address is already in use.',
                    ],
                ],
            ], 422);
        }

        $user->update([
            'name' => $name,
            'email' => $email,
        ]);

        $user->refresh();

        return response()->json([
            'success' => true,
            'message' => 'Profile updated successfully.',
            'user' => $this->userData($user),
        ]);
    }

    /**
     * PUT /api/settings/password
     *
     * Mirrors the web ProfileController::updatePassword() behavior:
     * - current password validated by Laravel's current_password rule
     * - new password uses the same password policy
     * - current session/token remains active after changing the password
     */
    public function updatePassword(Request $request): JsonResponse
    {
        $validated = $this->validateStrict($request, [
            'current_password' => [
                'bail',
                'required',
                'string',
                'max:128',
                'current_password',
            ],

            'password' => [
                'bail',
                'required',
                'string',
                'min:8',
                'max:128',
                'confirmed',
                PasswordRule::defaults(),
            ],

            'password_confirmation' => [
                'required',
                'string',
                'max:128',
            ],
        ]);

        $user = $request->user();

        $user->update([
            'password' => Hash::make(
                $validated['password']
            ),
        ]);

        $user->refresh();

        return response()->json([
            'success' => true,
            'message' => 'Password updated successfully.',
        ]);
    }

    private function userData($user): array
    {
        return [
            'id' => (int) $user->id,
            'name' => (string) ($user->name ?? ''),
            'email' => (string) ($user->email ?? ''),
            'email_verified_at' =>
                $user->email_verified_at?->toISOString(),
        ];
    }
}