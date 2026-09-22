<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Mail\DokanApplicationReceived;
use App\Mail\DokanRequestNotification;
use App\Models\Category;
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

    public function support()
    {
        return view('frontend.support');
    }

    public function about()
    {
        return view('frontend.about');
    }

    public function products(Request $request)
    {
        $query = Product::with(['dokan', 'varients']);

        // Filter by Category
        if ($request->filled('category')) {
            $catValue = $request->input('category');

            $query->where(function ($q) use ($catValue) {
                // 1. Direct column match (e.g. if category column stores string name/slug or exact integer ID)
                $q->where('category', $catValue);

                // 2. Foreign key column match (e.g. if category_id column exists on products)
                if (is_numeric($catValue)) {
                    $q->orWhere('category_id', $catValue);
                }

                // 3. Category relationship match (slug or ID via Category model)
                if (class_exists('App\Models\Category')) {
                    $q->orWhereHas('category', function ($catQuery) use ($catValue) {
                        $catQuery->where('slug', $catValue)
                            ->orWhere('id', $catValue)
                            ->orWhere('name', $catValue);
                    });
                }
            });
        }

        // Filter by Price Range
        if ($request->filled('min_price') || $request->filled('max_price')) {
            $query->whereHas('varients', function ($q) use ($request) {

                if ($request->filled('min_price')) {
                    $q->where('price', '>=', $request->input('min_price'));
                }

                if ($request->filled('max_price')) {
                    $q->where('price', '<=', $request->input('max_price'));
                }
            });
        }

        // Apply Sorting
        switch ($request->input('sort')) {

            case 'price_asc':

                $query->whereHas('varients')
                    ->join(
                        'product_varients',
                        'products.id',
                        '=',
                        'product_varients.product_id'
                    )
                    ->orderBy('product_varients.price', 'asc')
                    ->select('products.*');

                break;

            case 'price_desc':

                $query->whereHas('varients')
                    ->join(
                        'product_varients',
                        'products.id',
                        '=',
                        'product_varients.product_id'
                    )
                    ->orderBy('product_varients.price', 'desc')
                    ->select('products.*');

                break;

            case 'newest':

            default:

                $query->latest('products.created_at');

                break;
        }

        // Fetch categories dynamically
        if (class_exists('App\Models\Category')) {
            $categories = Category::all();
        } else {
            $categories = Product::whereNotNull('category')
                ->where('category', '!=', '')
                ->distinct()
                ->pluck('category');
        }

        // Paginate results
        $products = $query->paginate(12)->withQueryString();

        return view('frontend.product.index', compact('products', 'categories'));
    }

    public function product($id)
    {
        $product = Product::with([
            'dokan',
            'varients'
        ])->findOrFail($id);

        return view('frontend.product.show', compact('product'));
    }

    public function dokan_registration()
    {
        return view('frontend.dokan');
    }

    public function dokan_registration_submit(Request $request)
{
    /*
    |--------------------------------------------------------------------------
    | Check Existing Vendor Application
    |--------------------------------------------------------------------------
    */

    $email = strtolower(trim($request->email));
    $regNo = trim($request->reg_no);

    $emailVendor = Dokan::where('email', $email)->first();
    $regNoVendor = Dokan::where('reg_no', $regNo)->first();

    /*
    |--------------------------------------------------------------------------
    | Prevent conflicts with different vendor records
    |--------------------------------------------------------------------------
    */

    if (
        $emailVendor &&
        $regNoVendor &&
        $emailVendor->id !== $regNoVendor->id
    ) {
        return redirect()
            ->back()
            ->withInput()
            ->withErrors([
                'email' => 'This email and registration number belong to different vendor applications.',
                'reg_no' => 'This registration number is already associated with another vendor.',
            ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Find existing vendor
    |--------------------------------------------------------------------------
    */

    $existingDokan = $emailVendor ?? $regNoVendor;

    /*
    |--------------------------------------------------------------------------
    | Pending / Approved vendors cannot register again
    |--------------------------------------------------------------------------
    */

    if ($existingDokan) {

        if ($existingDokan->status !== Dokan::STATUS_REJECTED) {

            return redirect()
                ->back()
                ->withInput()
                ->withErrors([
                    'email' => 'This email is already registered as a vendor.',
                    'reg_no' => 'This registration number is already registered.',
                ]);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Validate Registration
    |--------------------------------------------------------------------------
    */

    $validated = $request->validate([
        'company_name' => 'required|string|max:255',
        'email' => 'required|email',
        'reg_no' => 'required|string|max:255',
        'contact_number' => 'required|string|max:20',
        'logo' => 'required|image|mimes:jpeg,png,jpg,svg|max:2048',
        'terms' => 'required|accepted',
    ]);

    try {

        /*
        |--------------------------------------------------------------------------
        | Handle Logo Upload
        |--------------------------------------------------------------------------
        */

        $file = $request->file('logo');

        $logoPath = null;

        if ($file) {

            $fileName = time() . '_' .
                preg_replace(
                    '/[^a-zA-Z0-9.]/',
                    '',
                    $file->getClientOriginalName()
                );

            $file->move(
                public_path('storage/vendor-logos'),
                $fileName
            );

            $logoPath = 'vendor-logos/' . $fileName;
        }

        /*
        |--------------------------------------------------------------------------
        | Re-apply using existing rejected application
        |--------------------------------------------------------------------------
        */

        if ($existingDokan) {

            $existingDokan->user_id = Auth::id();
            $existingDokan->company_name = $validated['company_name'];
            $existingDokan->email = $email;
            $existingDokan->reg_no = $regNo;
            $existingDokan->contact_number = $validated['contact_number'];

            if ($logoPath) {
                $existingDokan->logo = $logoPath;
            }

            // Reset rejected application
            $existingDokan->status = Dokan::STATUS_PENDING;
            $existingDokan->rejection_comment = null;

            // Make sure old password cannot be used
            $existingDokan->password = null;

            $existingDokan->save();

            $dokan = $existingDokan;

        } else {

            /*
            |--------------------------------------------------------------------------
            | Create completely new vendor application
            |--------------------------------------------------------------------------
            */

            $dokan = new Dokan();

            $dokan->user_id = Auth::id();
            $dokan->company_name = $validated['company_name'];
            $dokan->email = $email;
            $dokan->reg_no = $regNo;
            $dokan->contact_number = $validated['contact_number'];
            $dokan->logo = $logoPath;
            $dokan->status = Dokan::STATUS_PENDING;

            $dokan->save();
        }

        /*
        |--------------------------------------------------------------------------
        | Send Application Notification
        |--------------------------------------------------------------------------
        */

        Mail::to([
            'empireinnovation2025@gmail.com',
            $dokan->email,
        ])->send(
            new DokanApplicationReceived($dokan)
        );

        /*
        |--------------------------------------------------------------------------
        | Success
        |--------------------------------------------------------------------------
        */

        return redirect()
            ->back()
            ->with(
                'success',
                'Vendor registration submitted successfully.'
            );

    } catch (\Throwable $exception) {

        Log::error('Vendor registration failed.', [
            'exception' => $exception,
        ]);

        return redirect()
            ->back()
            ->withInput()
            ->with(
                'error',
                'Unable to submit vendor registration.'
            );
    }
}
    }
