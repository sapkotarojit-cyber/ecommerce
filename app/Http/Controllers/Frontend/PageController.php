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
    /**
     * Home page
     */
    public function home()
    {
        return view('frontend.home', [
            'products' => Product::latest()->get(),
        ]);
    }

    /**
     * Support page
     */
    public function support()
    {
        return view('frontend.support');
    }

    /**
     * About page
     */
    public function about()
    {
        return view('frontend.about');
    }

    /**
     * Product listing
     */
    public function products(Request $request)
    {
        /*
        |--------------------------------------------------------------------------
        | Strict input validation
        |--------------------------------------------------------------------------
        */
        $validated = $this->validateStrict($request, [
            'category' => [
                'nullable',
                'string',
                'max:100',
                'regex:/^[A-Za-z0-9_\- ]+$/',
            ],

            'min_price' => [
                'nullable',
                'numeric',
                'min:0',
                'max:999999999.99',
            ],

            'max_price' => [
                'nullable',
                'numeric',
                'min:0',
                'max:999999999.99',
                'gte:min_price',
            ],

            'sort' => [
                'nullable',
                'string',
                'in:price_asc,price_desc',
            ],
        ]);

        $query = Product::with([
            'dokan',
            'varients',
        ]);

        /*
        |--------------------------------------------------------------------------
        | Category filter
        |--------------------------------------------------------------------------
        */
        if (!empty($validated['category'])) {

            $value = $validated['category'];

            $query->where(function ($q) use ($value) {

                /*
                 * Keep compatibility with the existing legacy category
                 * column if it exists in the products table.
                 */
                $q->where('category', $value);

                /*
                 * Numeric category IDs
                 */
                if (ctype_digit($value)) {
                    $q->orWhere('category_id', (int) $value);
                }

                /*
                 * Category relationship
                 */
                $q->orWhereHas('category', function ($cat) use ($value) {
                    $cat->where('slug', $value)
                        ->orWhere('name', $value);

                    if (ctype_digit($value)) {
                        $cat->orWhere('id', (int) $value);
                    }
                });
            });
        }

        /*
        |--------------------------------------------------------------------------
        | Price filter
        |--------------------------------------------------------------------------
        */
        if (
            array_key_exists('min_price', $validated)
            || array_key_exists('max_price', $validated)
        ) {
            $minPrice = $validated['min_price'] ?? null;
            $maxPrice = $validated['max_price'] ?? null;

            $query->whereHas('varients', function ($q) use (
                $minPrice,
                $maxPrice
            ) {

                if ($minPrice !== null) {
                    $q->where('price', '>=', $minPrice);
                }

                if ($maxPrice !== null) {
                    $q->where('price', '<=', $maxPrice);
                }
            });
        }

        /*
        |--------------------------------------------------------------------------
        | Sorting
        |--------------------------------------------------------------------------
        */
        $sort = $validated['sort'] ?? null;

        if ($sort === 'price_asc') {

            $query->join(
                'product_varients',
                'products.id',
                '=',
                'product_varients.product_id'
            )
                ->orderBy('product_varients.price')
                ->select('products.*');

        } elseif ($sort === 'price_desc') {

            $query->join(
                'product_varients',
                'products.id',
                '=',
                'product_varients.product_id'
            )
                ->orderByDesc('product_varients.price')
                ->select('products.*');

        } else {

            $query->latest('products.created_at');
        }

        /*
        |--------------------------------------------------------------------------
        | Categories
        |--------------------------------------------------------------------------
        */
        $categories = class_exists(Category::class)
            ? Category::all()
            : Product::whereNotNull('category')
                ->where('category', '!=', '')
                ->distinct()
                ->pluck('category');

        /*
        |--------------------------------------------------------------------------
        | Paginated products
        |--------------------------------------------------------------------------
        */
        $products = $query
            ->paginate(12)
            ->withQueryString();

        return view(
            'frontend.product.index',
            compact('products', 'categories')
        );
    }

    /**
     * Product details
     */
    public function product($id)
    {
        /*
        |--------------------------------------------------------------------------
        | Strict product ID validation
        |--------------------------------------------------------------------------
        */
        if (!ctype_digit((string) $id) || (int) $id < 1) {
            abort(404);
        }

        $productId = (int) $id;

        $product = Product::with([
            'dokan',
            'varients',
        ])->findOrFail($productId);

        /*
        |--------------------------------------------------------------------------
        | Related products
        |--------------------------------------------------------------------------
        */
        $relatedProducts = Product::with([
            'dokan',
            'varients',
        ])
            ->where('id', '!=', $product->id)
            ->when(
                $product->category,
                fn ($q) => $q->where(
                    'category',
                    $product->category
                )
            )
            ->latest()
            ->take(4)
            ->get();

        return view(
            'frontend.product.show',
            compact(
                'product',
                'relatedProducts'
            )
        );
    }

    /**
     * Vendor registration page
     */
    public function dokan_registration()
    {
        return view('frontend.dokan');
    }

    /**
     * Vendor registration submit
     */
    public function dokan_registration_submit(Request $request)
    {
        /*
        |--------------------------------------------------------------------------
        | Strict vendor registration validation
        |--------------------------------------------------------------------------
        */
        $data = $this->validateStrict($request, [

            /*
             * Company / applicant information
             */
            'company_name' => [
                'required',
                'string',
                'min:2',
                'max:255',
            ],

            'name' => [
                'required',
                'string',
                'min:2',
                'max:255',
            ],

            'email' => [
                'required',
                'string',
                'email:rfc',
                'max:255',
            ],

            'reg_no' => [
                'required',
                'string',
                'min:1',
                'max:100',
                'regex:/^[A-Za-z0-9\-\/]+$/',
            ],

            'contact_number' => [
                'required',
                'string',
                'max:20',
                'regex:/^[0-9+\-\s()]+$/',
            ],

            /*
             * Business address
             */
            'business_location' => [
                'required',
                'string',
                'min:2',
                'max:255',
            ],

            'business_address' => [
                'required',
                'string',
                'min:5',
                'max:1000',
            ],

            /*
             * Business registration
             */
            'business_reg_no' => [
                'required',
                'string',
                'min:1',
                'max:100',
                'regex:/^[A-Za-z0-9\-\/]+$/',
            ],

            'pan_no' => [
                'required',
                'string',
                'min:1',
                'max:100',
                'regex:/^[A-Za-z0-9\-\/]+$/',
            ],

            /*
             * Business document
             */
            'business_document' => [
                'required',
                'file',
                'mimes:pdf,jpg,jpeg,png',
                'max:5120',
            ],

            /*
             * Bank information
             */
            'bank_name' => [
                'required',
                'string',
                'min:2',
                'max:255',
            ],

            'bank_account_name' => [
                'required',
                'string',
                'min:2',
                'max:255',
            ],

            'bank_account_number' => [
                'required',
                'string',
                'min:3',
                'max:100',
                'regex:/^[A-Za-z0-9\-]+$/',
            ],

            'bank_branch' => [
                'required',
                'string',
                'min:2',
                'max:255',
            ],

            /*
             * Bank document
             */
            'bank_document' => [
                'required',
                'file',
                'mimes:pdf,jpg,jpeg,png',
                'max:5120',
            ],

            /*
             * Vendor logo
             */
            'logo' => [
                'required',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:2048',
            ],

            /*
             * Terms
             */
            'terms' => [
                'required',
                'accepted',
            ],
        ]);

        try {

            /*
            |--------------------------------------------------------------------------
            | Normalize email
            |--------------------------------------------------------------------------
            */
            $email = strtolower(trim($data['email']));

            /*
            |--------------------------------------------------------------------------
            | Check existing vendor
            |--------------------------------------------------------------------------
            */
            $existing = Dokan::where('email', $email)->first();

            if (
                $existing
                && $existing->status !== Dokan::STATUS_REJECTED
            ) {
                return back()
                    ->withInput()
                    ->withErrors([
                        'email' => 'This email is already registered as a vendor.',
                    ]);
            }

            /*
            |--------------------------------------------------------------------------
            | Store uploaded business document
            |--------------------------------------------------------------------------
            */
            $data['business_document'] = $request
                ->file('business_document')
                ->store(
                    'vendor-documents/business',
                    'public'
                );

            /*
            |--------------------------------------------------------------------------
            | Store uploaded bank document
            |--------------------------------------------------------------------------
            */
            $data['bank_document'] = $request
                ->file('bank_document')
                ->store(
                    'vendor-documents/bank',
                    'public'
                );

            /*
            |--------------------------------------------------------------------------
            | Store vendor logo
            |--------------------------------------------------------------------------
            */
            $data['logo'] = $request
                ->file('logo')
                ->store(
                    'vendor-logos',
                    'public'
                );

            /*
            |--------------------------------------------------------------------------
            | Additional database values
            |--------------------------------------------------------------------------
            */
            $data['user_id'] = Auth::id();
            $data['email'] = $email;
            $data['status'] = Dokan::STATUS_PENDING;
            $data['password'] = null;
            $data['rejection_comment'] = null;

            /*
             * terms is only used for validation.
             * It should not be saved to the database.
             */
            unset($data['terms']);

            /*
            |--------------------------------------------------------------------------
            | Save vendor
            |--------------------------------------------------------------------------
            */
            if ($existing) {

                $existing->update($data);

                $dokan = $existing->fresh();

            } else {

                $dokan = Dokan::create($data);
            }

            /*
            |--------------------------------------------------------------------------
            | Send registration email
            |--------------------------------------------------------------------------
            */
            Mail::to($dokan->email)->send(
                new DokanApplicationReceived($dokan)
            );

            return back()->with(
                'success',
                'Vendor registration submitted successfully. A confirmation email has been sent to your email.'
            );

        } catch (\Throwable $e) {

            /*
            |--------------------------------------------------------------------------
            | Log error
            |--------------------------------------------------------------------------
            */
            Log::error(
                'Vendor registration failed',
                [
                    'email' => $data['email'] ?? null,
                    'user_id' => Auth::id(),
                    'error' => $e->getMessage(),
                ]
            );

            return back()
                ->withInput()
                ->with(
                    'error',
                    'Unable to submit vendor registration. Please try again.'
                );
        }
    }
}