<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Frontend\AuthController;
use App\Http\Controllers\Frontend\PageController;
use App\Http\Controllers\Frontend\ShippingAddressController;
use App\Http\Controllers\Frontend\CartController;
use App\Http\Controllers\Frontend\OrderController;
use App\Http\Controllers\Frontend\CheckoutController;
use App\Http\Controllers\Frontend\ProfileController;
use App\Http\Controllers\Frontend\ReturnRequestController;

/*
|--------------------------------------------------------------------------
| Public
|--------------------------------------------------------------------------
*/

Route::get('/', [PageController::class, 'home'])->name('home');
Route::get('/about', [PageController::class, 'about'])->name('about');
Route::get('/dokan-registration', [PageController::class, 'dokan_registration'])->name('dokan_registration');
Route::post('/dokan-registration', [PageController::class, 'dokan_registration_submit'])->name('dokan_registration_submit');
Route::get('/products', [PageController::class, 'products'])->name('products');
Route::get('/product/{id}', [PageController::class, 'product'])->name('product');
Route::get('/support', [PageController::class, 'support'])->name('support');

Route::get('/cart', [CartController::class, 'index'])->name('cart.index');

/*
|--------------------------------------------------------------------------
| Authentication
|--------------------------------------------------------------------------
*/

Route::get('/auth/redirect', [AuthController::class, 'redirect'])->name('redirect');
Route::get('/auth/callback', [AuthController::class, 'callback'])->name('auth.callback');
Route::get('/auth/google/callback', [AuthController::class, 'callback'])->name('google.callback');
Route::get('/auth/auth0/redirect', [AuthController::class, 'auth0Redirect'])->name('auth0.redirect');
Route::get('/auth/auth0/callback', [AuthController::class, 'auth0Callback'])->name('auth0.callback');

Route::post('/vendor/check-email', [AuthController::class, 'checkVendorEmail'])
    ->name('vendor.check_email');

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

Route::get('/forgot-password', [AuthController::class, 'showForgotPasswordForm'])->name('password.request');
Route::post('/forgot-password', [AuthController::class, 'sendResetLink'])->name('password.email');
Route::get('/reset-password/{token}', [AuthController::class, 'showResetPasswordForm'])->name('password.reset');
Route::post('/reset-password', [AuthController::class, 'resetPassword'])->name('password.update');

Route::get('/verify-email', [AuthController::class, 'showVerifyForm'])->name('verify.show');
Route::post('/verify-email', [AuthController::class, 'verifyCode'])
    ->middleware('throttle:5,1')
    ->name('verify.submit');

Route::post('/resend-verification-code', [AuthController::class, 'resendCode'])
    ->middleware('throttle:3,1')
    ->name('verify.resend');

/*
|--------------------------------------------------------------------------
| Authenticated
|--------------------------------------------------------------------------
*/

Route::middleware('auth')->group(function () {

    Route::get('/logout', [AuthController::class, 'logout'])->name('logout');

    /*
    | Profile
    */
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::get('/settings', [ProfileController::class, 'settings'])->name('settings');
    Route::put('/settings/password', [ProfileController::class, 'updatePassword'])
        ->name('settings.password');

    /*
    | Shipping Address
    */
    Route::prefix('shipping-address')->name('shipping-address.')->group(function () {
        Route::get('/', [ShippingAddressController::class, 'index'])->name('index');
        Route::get('/create', [ShippingAddressController::class, 'create'])->name('create');
        Route::post('/', [ShippingAddressController::class, 'store'])->name('store');
        Route::post('/quick-store', [ShippingAddressController::class, 'quickStore'])->name('quick-store');

        Route::get('/{address}/edit', [ShippingAddressController::class, 'edit'])->name('edit');
        Route::patch('/{address}', [ShippingAddressController::class, 'update'])->name('update');
        Route::delete('/{address}', [ShippingAddressController::class, 'destroy'])->name('destroy');
        Route::patch('/{address}/set-default', [ShippingAddressController::class, 'setDefault'])
            ->name('set-default');
    });

    /*
    | Cart
    */
    Route::prefix('cart')->name('cart.')->group(function () {
        Route::post('/add', [CartController::class, 'add'])->name('add');
        Route::post('/buy-now', [CartController::class, 'buyNow'])->name('buy-now');
        Route::post('/clear', [CartController::class, 'clear'])->name('clear');
        Route::get('/count', [CartController::class, 'count'])->name('count');

        Route::match(['post', 'patch'], '/{id}', [CartController::class, 'update'])
            ->name('update');

        Route::delete('/{id}', [CartController::class, 'destroy'])
            ->name('destroy');
    });

    /*
    | Orders
    */
    Route::prefix('orders')->name('orders.')->group(function () {

        Route::get('/', [OrderController::class, 'index'])->name('index');

        Route::get('/checkout', [CheckoutController::class, 'checkout'])
            ->name('checkout');

        Route::post('/cart/checkout-selected', [CheckoutController::class, 'postCheckoutSelected'])
            ->name('cart.checkout.selected');

        Route::post('/', [CheckoutController::class, 'store'])
            ->name('store');

        Route::get('/esewa/pay/{order}', [OrderController::class, 'esewaPay'])
            ->name('esewa.pay');

        Route::get('/bank-transfer/pay/{order}', [OrderController::class, 'bankPaymentPage'])
            ->name('bank.pay');

        Route::get('/track', [OrderController::class, 'trackForm'])
            ->name('track');

        Route::post('/track', [OrderController::class, 'trackResult'])
            ->name('track.submit');

        Route::get('/{id}/return', [ReturnRequestController::class, 'create'])
            ->name('returns.create');

        Route::post('/{id}/return', [ReturnRequestController::class, 'store'])
            ->name('returns.store');

        Route::get('/{id}/invoice', [OrderController::class, 'invoice'])
            ->name('invoice');

        Route::post('/{id}/cancel', [OrderController::class, 'cancel'])
            ->name('cancel');

        Route::get('/{id}', [OrderController::class, 'show'])
            ->name('show');
    });

    /*
    | Return Requests
    */
    Route::get('/my-return-requests', [ReturnRequestController::class, 'index'])
        ->name('returns.index');

    Route::get('/my-return-requests/{id}', [ReturnRequestController::class, 'show'])
        ->name('returns.show');
});

/*
|--------------------------------------------------------------------------
| eSewa Callback
|--------------------------------------------------------------------------
*/

Route::match(['get', 'post'], '/payment/esewa/success', [OrderController::class, 'esewaSuccess'])
    ->name('esewa.success')
    ->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class);

Route::get('/payment/esewa/failure', [OrderController::class, 'esewaFailure'])
    ->name('esewa.failure');

/*
|--------------------------------------------------------------------------
| Bank Payment Callback
|--------------------------------------------------------------------------
*/

Route::match(['get', 'post'], '/payment/bank/success', [OrderController::class, 'bankSuccess'])
    ->name('bank.success')
    ->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class);

Route::match(['get', 'post'], '/payment/bank/failure', [OrderController::class, 'bankFailure'])
    ->name('bank.failure');