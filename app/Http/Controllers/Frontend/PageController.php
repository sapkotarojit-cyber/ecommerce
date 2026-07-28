<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Mail\DokanRequestNotification;
use App\Models\Dokan;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class PageController extends Controller
{
    public function home()
    {
        $products = Product::latest()->get();
        return view('frontend.home', compact('products'));
    }

    public function products()
    {
        $products = Product::with(['dokan', 'varients'])->latest()->get();
        return view('product.index', compact('products'));
    }

    public function product($id)
    {
        $product = Product::with(['dokan', 'varients'])->findOrFail($id);
        return view('product.show', compact('product'));
    }

    public function dokan_registration()
    {
        return view('frontend.dokan');
    }

    public function dokan_registration_submit(Request $request)
    {
        $validated = $request->validate([
            'company_name' => 'required|string|max:255',
            'email' => 'required|email|unique:dokans,email',
            'reg_no' => 'required|string|max:255|unique:dokans,reg_no',
            'contact_number' => 'required|string|max:20',
            'logo' => 'required|image|mimes:jpeg,png,jpg,svg|max:2048',
            'terms' => 'required|accepted',
        ]);

         try {
        // Handle logo upload
        $file = $request->file('logo');
        $logoPath = null;
        if ($file) {
            $fileName = time() . '_' . preg_replace('/[^a-zA-Z0-9.]/', '', $file->getClientOriginalName());
            $file->move(public_path('storage/vendor-logos'), $fileName);
            $logoPath = 'vendor-logos/' . $fileName;
        }
             // Create dokan record
        $dokan = new Dokan();
        $dokan->user_id = Auth::id(); // ✅ Save user_id
        $dokan->company_name = $validated['company_name'];
        $dokan->email = $validated['email'];
        $dokan->reg_no = $validated['reg_no'];
        $dokan->contact_number = $validated['contact_number'];
        $dokan->logo = $logoPath;
        $dokan->status = Dokan::STATUS_PENDING;
        $dokan->save();


            // Send email notification to admin
            try {
                Mail::to(config('mail.from.address'))->send(new DokanRequestNotification($dokan));
            } catch (\Exception $e) {
                Log::error('Failed to send vendor registration email: ' . $e->getMessage());
            }

            return redirect()->route('dokan_registration')
                ->with('success', 'Your application has been submitted successfully! We will review it within 24-48 hours.');

        } catch (\Exception $e) {
            Log::error('Vendor registration error: ' . $e->getMessage());
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Something went wrong. Please try again.');
        }
    }
}
