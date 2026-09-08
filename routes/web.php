<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Frontend\AuthController;
use App\Http\Controllers\Frontend\PageController;
use App\Http\Controllers\Frontend\ShippingAddressController;
use App\Http\Controllers\Frontend\CartController;
use App\Http\Controllers\Frontend\OrderController;
use App\Http\Controllers\Vendor\AuthController as VendorAuthController;
use App\Http\Controllers\Vendor\DashboardController;
use App\Http\Controllers\Vendor\ProductController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// ============================================
/// Public Routes
Route::get('/', [PageController::class, 'home'])->name('home');
Route::get('/about', [PageController::class, 'about'])->name('about'); // Added About Us Route
Route::get('dokan-registration', [PageController::class, 'dokan_registration'])->name('dokan_registration');
Route::post('dokan-registration', [PageController::class, 'dokan_registration_submit'])->name('dokan_registration_submit');
Route::get('/products', [PageController::class, 'products'])->name('products');
Route::get('/product/{id}', [PageController::class, 'product'])->name('product');
Route::get('/support', [PageController::class, 'support'])->name('support');



// Public Track Order Routes
Route::get('/track-order', [OrderController::class, 'trackForm'])->name('orders.track');
Route::post('/track-order', [OrderController::class, 'trackResult'])->name('orders.track.submit');

// Public Cart View
Route::get('/cart', [CartController::class, 'index'])->name('cart.index');

// ============================================
// GUEST ROUTES (Unauthenticated)
// ============================================
Route::middleware('unauth')->group(function () {
    // Login Routes
    Route::get('/login', [AuthController::class, 'login'])->name('login');
    Route::post('/login', [AuthController::class, 'loginSubmit'])->name('login.submit');

    // Registration Routes
    Route::get('/register', [AuthController::class, 'register'])->name('register');
    Route::post('/register', [AuthController::class, 'registerSubmit'])->name('register.submit');

    // Socialite Routes
    Route::get('/auth/redirect', [AuthController::class, 'redirect'])->name('redirect');
    Route::get('/auth/callback', [AuthController::class, 'callback'])->name('auth.callback');
});

// ============================================
// AUTHENTICATED ROUTES
// ============================================
Route::middleware('auth')->group(function () {
    Route::get('/logout', [AuthController::class, 'logout'])->name('logout');

    // Shipping Address Routes
    Route::prefix('shipping-address')->name('shipping-address.')->group(function () {
        Route::get('/', [ShippingAddressController::class, 'index'])->name('index');
        Route::get('/create', [ShippingAddressController::class, 'create'])->name('create');
        Route::post('/', [ShippingAddressController::class, 'store'])->name('store');
        Route::get('/{address}/edit', [ShippingAddressController::class, 'edit'])->name('edit');
        Route::patch('/{address}', [ShippingAddressController::class, 'update'])->name('update');
        Route::delete('/{address}', [ShippingAddressController::class, 'destroy'])->name('destroy');
        Route::patch('/{address}/set-default', [ShippingAddressController::class, 'setDefault'])->name('set-default');
    });

    // Authenticated Cart Actions
    Route::prefix('cart')->name('cart.')->group(function () {
        Route::post('/add', [CartController::class, 'add'])->name('add');
        Route::patch('/{id}', [CartController::class, 'update'])->name('update');
        Route::delete('/{id}', [CartController::class, 'destroy'])->name('destroy');
        Route::delete('/clear', [CartController::class, 'clear'])->name('clear');
        Route::get('/count', [CartController::class, 'count'])->name('count');
    });

    // Order Routes
    Route::prefix('orders')->name('orders.')->group(function () {
        Route::get('/', [OrderController::class, 'index'])->name('index');
        Route::get('/checkout', [OrderController::class, 'checkout'])->name('checkout');
        Route::post('/store', [OrderController::class, 'store'])->name('store');
        Route::get('/{id}', [OrderController::class, 'show'])->name('show');
        Route::post('/{id}/cancel', [OrderController::class, 'cancel'])->name('cancel');
        Route::get('/{id}/invoice', [OrderController::class, 'invoice'])->name('invoice');
    });
});