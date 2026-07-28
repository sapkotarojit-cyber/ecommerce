<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Frontend\AuthController;
use App\Http\Controllers\Frontend\PageController;
use App\Http\Controllers\Frontend\ShippingAddressController;
use App\Http\Controllers\Frontend\CartController;
use App\Http\Controllers\Frontend\OrderController;
use App\Http\Controllers\Vendor\AuthController as VendorAuthController;  // ✅ ADD THIS
use App\Http\Controllers\Vendor\DashboardController;
use App\Http\Controllers\Vendor\ProductController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// ============================================
// PUBLIC ROUTES
// ============================================
Route::get('/', [PageController::class, 'home'])->name('home');
Route::get('dokan-registration', [PageController::class, 'dokan_registration'])->name('dokan_registration');
Route::post('dokan-registration', [PageController::class, 'dokan_registration_submit'])->name('dokan_registration_submit');
Route::get('/products', [PageController::class, 'products'])->name('products');
Route::get('/product/{id}', [PageController::class, 'product'])->name('product');

// ============================================
// GUEST ROUTES (Unauthenticated)
// ============================================
Route::middleware('unauth')->group(function () {
    Route::get('/login', [AuthController::class, 'login'])->name('login');
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

    // CART ROUTES
    Route::prefix('cart')->name('cart.')->group(function () {
        Route::get('/', [CartController::class, 'index'])->name('index');
        Route::post('/add', [CartController::class, 'add'])->name('add');
        Route::patch('/{id}', [CartController::class, 'update'])->name('update');
        Route::delete('/{id}', [CartController::class, 'destroy'])->name('destroy');
        Route::delete('/clear', [CartController::class, 'clear'])->name('clear');
        Route::get('/count', [CartController::class, 'count'])->name('count');
    });

    // ORDER ROUTES
    Route::prefix('orders')->name('orders.')->group(function () {
        Route::get('/', [OrderController::class, 'index'])->name('index');
        Route::get('/checkout', [OrderController::class, 'checkout'])->name('checkout');
        Route::post('/store', [OrderController::class, 'store'])->name('store');
        Route::get('/{id}', [OrderController::class, 'show'])->name('show');
        Route::post('/{id}/cancel', [OrderController::class, 'cancel'])->name('cancel');
        Route::get('/{id}/invoice', [OrderController::class, 'invoice'])->name('invoice');
    });
});

// // ============================================
// // VENDOR ROUTES - ✅ FIXED
// // ============================================

// // Public vendor routes (no auth required)
// Route::prefix('vendor')->name('vendor.')->group(function () {
//     Route::get('/login', [VendorAuthController::class, 'showLoginForm'])->name('login');
//     Route::post('/login', [VendorAuthController::class, 'login'])->name('login.submit');
// });

// // Protected vendor routes (requires dokan auth)
// Route::middleware('auth:dokan')->prefix('dokan')->name('vendor.')->group(function () {
//     Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
//     Route::post('/logout', [VendorAuthController::class, 'logout'])->name('logout');

//     // // Products
//     // Route::resource('products', ProductController::class);

//     // Orders
//     Route::get('/orders', [OrderController::class, 'index'])->name('orders.index');
//     Route::get('/orders/{id}', [OrderController::class, 'show'])->name('orders.show');
//     Route::patch('/orders/{id}/status', [OrderController::class, 'updateStatus'])->name('orders.update-status');
// });
