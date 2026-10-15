<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Mail\DokanApplicationReceived;
use App\Models\Dokan;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

class VendorRegistrationController extends Controller
{
    /**
     * Submit a vendor application from the authenticated Flutter app.
     */
    public function store(Request $request): JsonResponse
    {
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'message' => 'Authentication is required.',
                'code' => 'UNAUTHENTICATED',
            ], 401);
        }

        if (!$user->email_verified_at) {
            return response()->json([
                'message' => 'Please verify your email before submitting a vendor application.',
                'code' => 'EMAIL_VERIFICATION_REQUIRED',
            ], 403);
        }

            $data = $this->validateStrict($request, [
            'company_name' => ['required', 'string', 'min:2', 'max:255'],
            'name' => ['required', 'string', 'min:2', 'max:255'],
            'email' => ['required', 'string', 'email:rfc', 'max:255'],
            'reg_no' => ['required', 'string', 'min:1', 'max:100', 'regex:/^[A-Za-z0-9\-\/]+$/'],
            'contact_number' => ['required', 'string', 'max:20', 'regex:/^[0-9+\-\s()]+$/'],
            'business_location' => ['required', 'string', 'min:2', 'max:255'],
            'business_address' => ['required', 'string', 'min:5', 'max:1000'],
            'business_reg_no' => ['required', 'string', 'min:1', 'max:100', 'regex:/^[A-Za-z0-9\-\/]+$/'],
            'pan_no' => ['required', 'string', 'min:1', 'max:100', 'regex:/^[A-Za-z0-9\-\/]+$/'],
            'business_document' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
            'bank_name' => ['required', 'string', 'min:2', 'max:255'],
            'bank_account_name' => ['required', 'string', 'min:2', 'max:255'],
            'bank_account_number' => ['required', 'string', 'min:3', 'max:100', 'regex:/^[A-Za-z0-9\-]+$/'],
            'bank_branch' => ['required', 'string', 'min:2', 'max:255'],
            'bank_document' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
            'logo' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'terms' => ['required', 'accepted'],
        ]);

        $email = strtolower(trim($data['email']));
        $accountEmail = strtolower(trim($user->email));
        $userId = $user->id;

        if ($email !== $accountEmail) {
            return response()->json([
                'message' => 'Your vendor email must match your verified account email.',
                'errors' => [
                    'email' => [
                        'Your vendor email must match your verified account email.',
                    ],
                ],
            ], 422);
        }

        $storedPaths = [];

        try {
            $existing = Dokan::where('email', $email)->first();

            if ($existing && $existing->status !== Dokan::STATUS_REJECTED) {
                return response()->json([
                    'message' => 'This email is already registered as a vendor.',
                    'errors' => [
                        'email' => [
                            'This email is already registered as a vendor.',
                        ],
                    ],
                ], 422);
            }

            $businessDocument = $request
                ->file('business_document')
                ->store('vendor-documents/business', 'public');

            $storedPaths[] = $businessDocument;

            $bankDocument = $request
                ->file('bank_document')
                ->store('vendor-documents/bank', 'public');

            $storedPaths[] = $bankDocument;

            $logo = $request
                ->file('logo')
                ->store('vendor-logos', 'public');

            $storedPaths[] = $logo;

            $vendorData = [
                'company_name' => $data['company_name'],
                'name' => $data['name'],
                'email' => $email,
                'reg_no' => $data['reg_no'],
                'contact_number' => $data['contact_number'],
                'business_location' => $data['business_location'],
                'business_address' => $data['business_address'],
                'business_reg_no' => $data['business_reg_no'],
                'pan_no' => $data['pan_no'],
                'business_document' => $businessDocument,
                'bank_name' => $data['bank_name'],
                'bank_account_name' => $data['bank_account_name'],
                'bank_account_number' => $data['bank_account_number'],
                'bank_branch' => $data['bank_branch'],
                'bank_document' => $bankDocument,
                'logo' => $logo,
                'user_id' => $userId,
                'status' => Dokan::STATUS_PENDING,
                'password' => null,
                'rejection_comment' => null,
            ];

            $dokan = DB::transaction(function () use (
                $existing,
                $vendorData
            ) {
                if ($existing) {
                    $existing->update($vendorData);

                    return $existing->fresh();
                }

                return Dokan::create($vendorData);
            });

            // The application is saved even if the notification email fails.
            try {
                Mail::to($dokan->email)->send(
                    new DokanApplicationReceived($dokan)
                );
            } catch (\Throwable $mailError) {
                Log::error('Vendor confirmation email failed', [
                    'vendor_id' => $dokan->id,
                    'error' => $mailError->getMessage(),
                ]);
            }

            return response()->json([
                'message' => 'Vendor registration submitted successfully.',
                'vendor' => [
                    'id' => $dokan->id,
                    'company_name' => $dokan->company_name,
                    'email' => $dokan->email,
                    'status' => $dokan->status,
                ],
            ], 201);
        } catch (\Throwable $e) {
            foreach ($storedPaths as $path) {
                Storage::disk('public')->delete($path);
            }

            Log::error('Vendor API registration failed', [
                'email' => $email,
                'user_id' => $userId,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'message' => 'Unable to submit vendor registration. Please try again.',
            ], 500);
        }
    }
}
