<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\CheckoutController;
use App\Http\Controllers\Api\ShippingAddressController;
use App\Http\Controllers\Api\CartController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\SettingsController;
use App\Http\Controllers\Api\VendorRegistrationController;
use App\Http\Controllers\Api\PaymentController;

/*
|--------------------------------------------------------------------------
| Public API
|--------------------------------------------------------------------------
*/

Route::middleware('adaptive.rate:public')->group(function () {
    Route::get('/products', [ProductController::class, 'index'])
        ->name('api.products.index');

    Route::get('/products/{id}', [ProductController::class, 'show'])
        ->whereNumber('id')
        ->name('api.products.show');

    Route::get('/categories', [CategoryController::class, 'index'])
        ->name('api.categories.index');

    /*
    |--------------------------------------------------------------------------
    | Public Bank Payment Instructions
    |--------------------------------------------------------------------------
    |
    | This endpoint only returns bank transfer instructions.
    | Receipt uploads and payment operations remain authenticated.
    |
    */

    Route::get('/payments/bank-details', [
        PaymentController::class,
        'bankDetails',
    ])->name('api.payments.bank-details');
});

/*
|--------------------------------------------------------------------------
| Authentication
|--------------------------------------------------------------------------
*/

Route::post('/google/mobile', [AuthController::class, 'googleMobile'])
    ->middleware('adaptive.rate:auth')
    ->name('google.mobile');

Route::post('/register', [AuthController::class, 'register'])
    ->middleware('adaptive.rate:registration')
    ->name('api.register');

Route::post('/login', [AuthController::class, 'login'])
    ->middleware('adaptive.rate:auth')
    ->name('api.login');

Route::post('/forgot-password', [AuthController::class, 'forgotPassword'])
    ->middleware('adaptive.rate:password_reset')
    ->name('api.forgot-password');

Route::post('/verify-password-reset-code', [AuthController::class, 'verifyPasswordResetCode'])
    ->middleware('adaptive.rate:password_reset')
    ->name('api.verify-password-reset-code');

Route::post('/reset-password', [AuthController::class, 'resetPassword'])
    ->middleware('adaptive.rate:password_reset')
    ->name('api.reset-password');

Route::get('/google/redirect', [AuthController::class, 'googleRedirect'])
    ->middleware('adaptive.rate:sensitive')
    ->name('api.google.redirect');

Route::get('/google/callback', [AuthController::class, 'googleCallback'])
    ->middleware('adaptive.rate:sensitive')
    ->name('api.google.callback');

Route::post('/verify-email', [AuthController::class, 'verifyEmail'])
    ->middleware('adaptive.rate:verification')
    ->name('api.verify-email');

Route::post('/resend-verification-code', [AuthController::class, 'resendVerificationCode'])
    ->middleware('adaptive.rate:verification')
    ->name('api.resend-verification-code');

/*
|--------------------------------------------------------------------------
| Authenticated API
|--------------------------------------------------------------------------
*/

Route::middleware('auth:sanctum')->group(function () {

    /*
    |--------------------------------------------------------------------------
    | Authentication
    |--------------------------------------------------------------------------
    */

    Route::post('/logout', [AuthController::class, 'logout'])
        ->middleware('adaptive.rate:sensitive')
        ->name('api.logout');

    Route::get('/me', [AuthController::class, 'me'])
        ->name('api.me');

    /*
    |--------------------------------------------------------------------------
    | Settings
    |--------------------------------------------------------------------------
    */

    Route::prefix('settings')
        ->name('api.settings.')
        ->group(function () {
            Route::get('/', [SettingsController::class, 'show'])
                ->name('show');

            Route::put('/', [SettingsController::class, 'update'])
                ->middleware('adaptive.rate:sensitive')
                ->name('update');

            Route::put('/password', [SettingsController::class, 'updatePassword'])
                ->middleware('adaptive.rate:sensitive')
                ->name('password');
        });

    /*
    |--------------------------------------------------------------------------
    | Cart
    |--------------------------------------------------------------------------
    */

    Route::prefix('cart')
        ->name('api.cart.')
        ->group(function () {
            Route::get('/', [CartController::class, 'index'])
                ->name('index');

            Route::post('/add', [CartController::class, 'add'])
                ->middleware('adaptive.rate:sensitive')
                ->name('add');

            Route::post('/buy-now', [CartController::class, 'buyNow'])
                ->middleware('adaptive.rate:sensitive')
                ->name('buy-now');

            Route::post('/clear', [CartController::class, 'clear'])
                ->middleware('adaptive.rate:sensitive')
                ->name('clear');

            Route::get('/count', [CartController::class, 'count'])
                ->name('count');

            Route::patch('/{id}', [CartController::class, 'update'])
                ->whereNumber('id')
                ->middleware('adaptive.rate:sensitive')
                ->name('update');

            Route::delete('/{id}', [CartController::class, 'destroy'])
                ->whereNumber('id')
                ->middleware('adaptive.rate:sensitive')
                ->name('destroy');
        });

    /*
    |--------------------------------------------------------------------------
    | Shipping Addresses
    |--------------------------------------------------------------------------
    */

    Route::prefix('shipping-addresses')
        ->name('api.shipping-addresses.')
        ->group(function () {
            Route::get('/', [ShippingAddressController::class, 'index'])
                ->name('index');

            Route::post('/', [ShippingAddressController::class, 'store'])
                ->middleware('adaptive.rate:sensitive')
                ->name('store');

            Route::delete('/{address}', [ShippingAddressController::class, 'destroy'])
                ->whereNumber('address')
                ->middleware('adaptive.rate:sensitive')
                ->name('destroy');

            Route::put(
                '/{address}/set-default-shipping',
                [ShippingAddressController::class, 'setDefaultShipping']
            )
                ->whereNumber('address')
                ->middleware('adaptive.rate:sensitive')
                ->name('set-default-shipping');
        });

    /*
    |--------------------------------------------------------------------------
    | Checkout
    |--------------------------------------------------------------------------
    */

    Route::post('/checkout', [CheckoutController::class, 'store'])
        ->middleware('adaptive.rate:sensitive')
        ->name('api.checkout');

    /*
    |--------------------------------------------------------------------------
    | Vendor Registration
    |--------------------------------------------------------------------------
    */

    Route::post('/vendor-registration', [
        VendorRegistrationController::class,
        'store',
    ])
        ->middleware('adaptive.rate:sensitive')
        ->name('api.vendor-registration.store');

    /*
    |--------------------------------------------------------------------------
    | Flutter Payment API
    |--------------------------------------------------------------------------
    |
    | Only bank instructions are public. These payment operations require
    | a valid Sanctum-authenticated user.
    |
    */

    Route::prefix('payments')
        ->name('api.payments.')
        ->group(function () {

            // Initiate eSewa payment for an existing checkout payment group.
            Route::post('/esewa/initiate', [
                PaymentController::class,
                'initiateEsewa',
            ])
                ->middleware('adaptive.rate:sensitive')
                ->name('esewa.initiate');

            // Verify the transaction with eSewa's server.
            Route::post('/esewa/verify', [
                PaymentController::class,
                'verifyEsewa',
            ])
                ->middleware('adaptive.rate:sensitive')
                ->name('esewa.verify');

            // Upload a bank-transfer receipt for the authenticated user's group.
            Route::post('/bank/receipt', [
                PaymentController::class,
                'uploadBankReceipt',
            ])
                ->middleware('adaptive.rate:sensitive')
                ->name('bank.receipt');

            // Retrieve payment status for the authenticated user's group.
            Route::get('/groups/{paymentGroupId}', [
                PaymentController::class,
                'groupStatus',
            ])
                ->name('groups.status');
        });

    /*
    |--------------------------------------------------------------------------
    | Orders
    |--------------------------------------------------------------------------
    */

    Route::prefix('orders')
        ->name('api.orders.')
        ->group(function () {
            Route::get('/', [OrderController::class, 'index'])
                ->name('index');

            Route::get('/{id}', [OrderController::class, 'show'])
                ->whereNumber('id')
                ->name('show');

            Route::post('/{id}/return-request', [
                OrderController::class,
                'createReturnRequest',
            ])
                ->whereNumber('id')
                ->middleware('adaptive.rate:sensitive')
                ->name('return-request');

            Route::post('/{id}/cancel', [OrderController::class, 'cancel'])
                ->whereNumber('id')
                ->middleware('adaptive.rate:sensitive')
                ->name('cancel');

            Route::get('/{id}/tracking', [OrderController::class, 'tracking'])
                ->whereNumber('id')
                ->name('tracking');

            Route::get('/{id}/return-status', [
                OrderController::class,
                'returnStatus',
            ])
                ->whereNumber('id')
                ->name('return-status');
        });
});
