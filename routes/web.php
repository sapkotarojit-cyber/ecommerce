<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Frontend\AuthController;
use App\Http\Controllers\Frontend\PageController;
use App\Http\Controllers\Frontend\ShippingAddressController;
use App\Http\Controllers\Frontend\CartController;
use App\Http\Controllers\Frontend\OrderController;
use App\Http\Controllers\Frontend\ProfileController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

// ============================================
// PUBLIC ROUTES (No auth required)
// ============================================
Route::get('/', [PageController::class, 'home'])->name('home');
Route::get('/about', [PageController::class, 'about'])->name('about');
Route::get('dokan-registration', [PageController::class, 'dokan_registration'])->name('dokan_registration');
Route::post('dokan-registration', [PageController::class, 'dokan_registration_submit'])->name('dokan_registration_submit');
Route::get('/products', [PageController::class, 'products'])->name('products');
Route::get('/product/{id}', [PageController::class, 'product'])->name('product');
Route::get('/support', [PageController::class, 'support'])->name('support');

// Public Cart View
Route::get('/cart', [CartController::class, 'index'])->name('cart.index');

// OAuth routes
Route::get('/auth/redirect', [AuthController::class, 'redirect'])->name('redirect');
Route::get('/auth/callback', [AuthController::class, 'callback'])->name('auth.callback');
Route::get('/auth/google/callback', [AuthController::class, 'callback'])->name('google.callback');
Route::get('/auth/auth0/redirect', [AuthController::class, 'auth0Redirect'])->name('auth0.redirect');
Route::get('/auth/auth0/callback', [AuthController::class, 'auth0Callback'])->name('auth0.callback');

Route::post('/vendor/check-email', [AuthController::class, 'checkVendorEmail'])->name('vendor.check_email');

// ============================================
// GUEST ROUTES (Unauthenticated)
// ============================================
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'login'])->name('login');
    Route::post('/login', [AuthController::class, 'loginSubmit'])
        ->middleware('throttle:5,1')
        ->name('login.submit');

    Route::get('/register', [AuthController::class, 'register'])->name('register');
    Route::post('/register', [AuthController::class, 'registerSubmit'])
        ->middleware('throttle:3,1')
        ->name('register.submit');
});

// ============================================
// FORGOT & RESET PASSWORD ROUTES
// ============================================
Route::get('/forgot-password', [AuthController::class, 'showForgotPasswordForm'])->name('password.request');
Route::post('/forgot-password', [AuthController::class, 'sendResetLink'])->name('password.email');
Route::get('/reset-password/{token}', [AuthController::class, 'showResetPasswordForm'])->name('password.reset');
Route::post('/reset-password', [AuthController::class, 'resetPassword'])->name('password.update');

// ============================================
// VERIFICATION ROUTES
// ============================================
Route::get('/verify-email', [AuthController::class, 'showVerifyForm'])->name('verify.show');
Route::post('/verify-email', [AuthController::class, 'verifyCode'])
    ->middleware('throttle:5,1')
    ->name('verify.submit');
Route::post('/resend-verification-code', [AuthController::class, 'resendCode'])
    ->middleware('throttle:3,1')
    ->name('verify.resend');

// ============================================
// AUTHENTICATED ROUTES (Requires Login)
// ============================================
Route::middleware('auth')->group(function () {
    Route::get('/logout', [AuthController::class, 'logout'])->name('logout');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    
    Route::get('/settings', [ProfileController::class, 'settings'])->name('settings');
    Route::put('/settings/password', [ProfileController::class, 'updatePassword'])->name('settings.password');

    // Shipping Address Routes (Parameter fixed to {address})
    Route::prefix('shipping-address')->name('shipping-address.')->group(function () {
        Route::get('/', [ShippingAddressController::class, 'index'])->name('index');
        Route::get('/create', [ShippingAddressController::class, 'create'])->name('create');
        Route::post('/', [ShippingAddressController::class, 'store'])->name('store');
        Route::get('/{address}/edit', [ShippingAddressController::class, 'edit'])->name('edit');
        Route::patch('/{address}', [ShippingAddressController::class, 'update'])->name('update');
        Route::delete('/{address}', [ShippingAddressController::class, 'destroy'])->name('destroy');
        Route::patch('/{address}/set-default', [ShippingAddressController::class, 'setDefault'])->name('set-default');
        Route::post('/quick-store', [ShippingAddressController::class, 'quickStore'])->name('quick-store');
        Route::post('/shipping-address/quick-store', [ShippingAddressController::class, 'quickStore'])->name('shipping-address.quick-store');
    });

    // Authenticated Cart Actions
    Route::prefix('cart')->name('cart.')->group(function () {
        Route::post('/add', [CartController::class, 'add'])->name('add');
        Route::post('/buy-now', [CartController::class, 'buyNow'])->name('buy-now');
        Route::post('/clear', [CartController::class, 'clear'])->name('clear');
        Route::get('/count', [CartController::class, 'count'])->name('count');
        Route::match(['post', 'patch'], '/{id}', [CartController::class, 'update'])->name('update');
        Route::delete('/{id}', [CartController::class, 'destroy'])->name('destroy');
    });

    // Order & Dashboard Routes (Protected)
    Route::prefix('orders')->name('orders.')->group(function () {
        Route::get('/', [OrderController::class, 'index'])->name('index');
        Route::get('/checkout', [OrderController::class, 'checkout'])->name('checkout');
        Route::post('/', [OrderController::class, 'store'])->name('store'); 
        Route::get('/{id}', [OrderController::class, 'show'])->name('show');
        Route::post('/{id}/cancel', [OrderController::class, 'cancel'])->name('cancel');
        Route::get('/{id}/invoice', [OrderController::class, 'invoice'])->name('invoice');
        Route::post('/cart/checkout-selected', [OrderController::class, 'postCheckoutSelected'])->name('cart.checkout.selected');
        Route::get('/checkout', [OrderController::class, 'checkout'])->name('checkout');
    });

    // Track Order Routes (Protected - Requires Login)
    Route::get('/track-order', [OrderController::class, 'trackForm'])->name('orders.track');
    Route::post('/track-order', [OrderController::class, 'trackResult'])->name('orders.track.submit');

    Route::get('/bank-transfer/pay', [OrderController::class, 'bankPaymentPage'])->name('bank.pay');
});

// ============================================
// PAYMENT GATEWAY CALLBACK ROUTES
// ============================================
Route::prefix('payment/esewa')->name('esewa.')->group(function () {
    Route::match(['get', 'post'], '/success', [OrderController::class, 'esewaSuccess'])->name('success')->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class);
    Route::get('/failure', [OrderController::class, 'esewaFailure'])->name('failure');
});

Route::match(['get', 'post'], '/payment/bank/success', [OrderController::class, 'bankSuccess'])->name('bank.success')->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class);
Route::match(['get', 'post'], '/payment/bank/failure', [OrderController::class, 'bankFailure'])->name('bank.failure');