<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Mail\DokanApplicationReceived;
use App\Models\Category;
use App\Models\Dokan;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class PageController extends Controller
{
    public function home()
    {
        return view('frontend.home', [
            'products' => Product::latest()->get()
        ]);
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

        if ($request->filled('category')) {
            $value = $request->category;

            $query->where(function ($q) use ($value) {
                $q->where('category', $value);

                if (is_numeric($value)) {
                    $q->orWhere('category_id', $value);
                }

                $q->orWhereHas('category', function ($cat) use ($value) {
                    $cat->where('slug', $value)
                        ->orWhere('id', $value)
                        ->orWhere('name', $value);
                });
            });
        }

        if ($request->filled('min_price') || $request->filled('max_price')) {
            $query->whereHas('varients', function ($q) use ($request) {
                if ($request->filled('min_price')) {
                    $q->where('price', '>=', $request->min_price);
                }

                if ($request->filled('max_price')) {
                    $q->where('price', '<=', $request->max_price);
                }
            });
        }

        if ($request->sort === 'price_asc') {
            $query->join(
                'product_varients',
                'products.id',
                '=',
                'product_varients.product_id'
            )->orderBy('product_varients.price')
             ->select('products.*');

        } elseif ($request->sort === 'price_desc') {
            $query->join(
                'product_varients',
                'products.id',
                '=',
                'product_varients.product_id'
            )->orderByDesc('product_varients.price')
             ->select('products.*');

        } else {
            $query->latest('products.created_at');
        }

        $categories = class_exists(Category::class)
            ? Category::all()
            : Product::whereNotNull('category')
                ->where('category', '!=', '')
                ->distinct()
                ->pluck('category');

        $products = $query->paginate(12)->withQueryString();

        return view('frontend.product.index', compact('products', 'categories'));
    }

    public function product($id)
    {
        $product = Product::with(['dokan', 'varients'])->findOrFail($id);

        $relatedProducts = Product::with(['dokan', 'varients'])
            ->where('id', '!=', $product->id)
            ->when(
                $product->category,
                fn ($q) => $q->where('category', $product->category)
            )
            ->latest()
            ->take(4)
            ->get();

        return view(
            'frontend.product.show',
            compact('product', 'relatedProducts')
        );
    }

    public function dokan_registration()
    {
        return view('frontend.dokan');
    }

    public function dokan_registration_submit(Request $request)
    {
        $data = $request->validate([
            'company_name'        => 'required|string|max:255',
            'name'                => 'required|string|max:255',
            'email'               => 'required|email|max:255',
            'reg_no'              => 'required|string|max:255',
            'contact_number'      => 'required|string|max:20',

            'business_location'   => 'required|string|max:255',
            'business_address'    => 'required|string|max:1000',

            'business_reg_no'     => 'required|string|max:255',
            'pan_no'              => 'required|string|max:255',
            'business_document'   => 'required|file|mimes:pdf,jpg,jpeg,png|max:5120',

            'bank_name'           => 'required|string|max:255',
            'bank_account_name'   => 'required|string|max:255',
            'bank_account_number' => 'required|string|max:255',
            'bank_branch'         => 'required|string|max:255',
            'bank_document'       => 'required|file|mimes:pdf,jpg,jpeg,png|max:5120',

            'logo'                => 'required|image|mimes:jpg,jpeg,png,webp|max:2048',

            'terms'               => 'required|accepted',
        ]);

        try {
            $email = strtolower(trim($data['email']));

            /*
             * Check existing vendor
             */
            $existing = Dokan::where('email', $email)->first();

            if ($existing && $existing->status !== Dokan::STATUS_REJECTED) {
                return back()
                    ->withInput()
                    ->withErrors([
                        'email' => 'This email is already registered as a vendor.'
                    ]);
            }

            /*
             * Store uploaded files
             */
            $data['business_document'] = $request
                ->file('business_document')
                ->store('vendor-documents/business', 'public');

            $data['bank_document'] = $request
                ->file('bank_document')
                ->store('vendor-documents/bank', 'public');

            $data['logo'] = $request
                ->file('logo')
                ->store('vendor-logos', 'public');

            /*
             * Additional database values
             */
            $data['user_id'] = Auth::id();
            $data['email'] = $email;
            $data['status'] = Dokan::STATUS_PENDING;
            $data['password'] = null;
            $data['rejection_comment'] = null;

            // terms is only for validation, not database
            unset($data['terms']);

            /*
             * Save vendor
             */
            if ($existing) {
                $existing->update($data);
                $dokan = $existing->fresh();
            } else {
                $dokan = Dokan::create($data);
            }

            /*
             * Send registration email
             */
            Mail::to($dokan->email)->send(
                new DokanApplicationReceived($dokan)
            );

            return back()->with(
                'success',
                'Vendor registration submitted successfully. A confirmation email has been sent to your email.'
            );

        } catch (\Throwable $e) {

            Log::error('Vendor registration failed', [
                'email' => $request->email,
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
            ]);

            return back()
                ->withInput()
                ->with(
                    'error',
                    'Unable to submit vendor registration. Please try again.'
                );
        }
    }
}