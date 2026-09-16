<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Mail\DokanApplicationReceived;
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
            $query->where('category', $request->input('category'));
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

        // Paginate results
        $products = $query->paginate(12)->withQueryString();

        return view('frontend.product.index', compact('products'));
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
        // Validate registration form
        $validated = $request->validate([
            'company_name' => 'required|string|max:255',
            'email' => 'required|email|unique:dokans,email',
            'reg_no' => 'required|string|max:255|unique:dokans,reg_no',
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
            | Create Vendor Application
            |--------------------------------------------------------------------------
            */

            $dokan = new Dokan();

            $dokan->user_id = Auth::id();
            $dokan->company_name = $validated['company_name'];
            $dokan->email = $validated['email'];
            $dokan->reg_no = $validated['reg_no'];
            $dokan->contact_number = $validated['contact_number'];
            $dokan->logo = $logoPath;
            $dokan->status = Dokan::STATUS_PENDING;

           $dokan->save();

/*
|--------------------------------------------------------------------------
| Send Application Notification to Admin
|--------------------------------------------------------------------------
*/

Mail::to([
    'empireinnovation2025@gmail.com',
    $dokan->email,
])->send(new DokanApplicationReceived($dokan));

return redirect()
    ->back()
    ->with('success', 'Vendor registration submitted successfully.');
        } catch (\Throwable $exception) {
            Log::error('Vendor registration failed.', [
                'exception' => $exception,
            ]);

            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Unable to submit vendor registration.');
        }
    }
}

           
