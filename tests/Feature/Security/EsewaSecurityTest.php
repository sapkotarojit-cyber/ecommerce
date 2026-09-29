<?php

use App\Models\Dokan;
use App\Models\Order;
use App\Models\ShippingAddress;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;

uses(RefreshDatabase::class);

function esewaTestPayload(array $overrides = []): array
{
    $payload = array_merge([
        'transaction_code' => '0000000000000001',
        'status' => 'COMPLETE',
        'total_amount' => '100.00',
        'transaction_uuid' => 'ORD-TEST-UUID-123',
        'product_code' => 'EPAYTEST',
        'signed_field_names' => 'total_amount,transaction_uuid,product_code',
    ], $overrides);

    $secret = 'test-secret-key';

    $message = collect(explode(',', $payload['signed_field_names']))
        ->map(fn ($field) => $field . '=' . $payload[$field])
        ->implode(',');

    $payload['signature'] = base64_encode(
        hash_hmac('sha256', $message, $secret, true)
    );

    return $payload;
}

function createPendingEsewaOrder(User $user, array $overrides = []): Order
{
    $dokan = Dokan::create([
        'email' => 'test-' . uniqid() . '@example.com',
        'password' => 'password',
        'company_name' => 'Test Dokan',
        'logo' => 'test-logo.png',
        'reg_no' => 'REG-' . uniqid(),
        'contact_number' => '9800000000',
        'status' => 'approved',
    ]);

    $address = ShippingAddress::create([
        'user_id' => $user->id,
        'name' => 'Test User',
        'phone' => '9800000000',
        'region' => 'Kathmandu',
        'address' => 'Test Address',
        'landmark' => null,
        'address_type' => 'Home',
        'is_default_shipping' => true,
        'is_default_billing' => true,
    ]);

    return Order::create(array_merge([
        'user_id' => $user->id,
        'dokan_id' => $dokan->id,
        'shipping_address_id' => $address->id,
        'total_amount' => 100.00,
        'status' => 'pending',
        'order_status' => 'pending',
        'payment_method' => 'esewa',
        'payment_status' => 'pending',
        'tracking_number' => 'ORD-TEST-' . uniqid(),
        'payment_transaction_id' => 'ORD-TEST-UUID-123',
        'payment_group_id' => 'TEST-GROUP-123',
    ], $overrides));
}

test('esewa callback rejects a transaction uuid that does not match the payment session', function () {
    $user = User::factory()->create();

    $order = createPendingEsewaOrder($user);

    config([
        'services.esewa.secret_key' => 'test-secret-key',
        'services.esewa.merchant_code' => 'EPAYTEST',
    ]);

    session([
        'esewa_order_id' => $order->id,
        'esewa_payment_group_id' => 'TEST-GROUP-123',
        'esewa_transaction_uuid' => 'ORD-DIFFERENT-UUID',
    ]);

    $payload = esewaTestPayload([
        'transaction_uuid' => 'ORD-TEST-UUID-123',
    ]);

    $response = $this
        ->actingAs($user)
        ->post('/payment/esewa/success', $payload);

    $response
        ->assertRedirect(route('orders.index'))
        ->assertSessionHas('error');

    expect($order->fresh()->payment_status)->toBe('pending');
});

test('esewa callback rejects a transaction uuid that does not match the order', function () {
    $user = User::factory()->create();

    $order = createPendingEsewaOrder($user, [
        'payment_transaction_id' => 'ORD-DATABASE-UUID',
    ]);

    config([
        'services.esewa.secret_key' => 'test-secret-key',
        'services.esewa.merchant_code' => 'EPAYTEST',
    ]);

    session([
        'esewa_order_id' => $order->id,
        'esewa_payment_group_id' => 'TEST-GROUP-123',
        'esewa_transaction_uuid' => 'ORD-TEST-UUID-123',
    ]);

    $payload = esewaTestPayload();

    $response = $this
        ->actingAs($user)
        ->post('/payment/esewa/success', $payload);

    $response
        ->assertRedirect(route('orders.index'))
        ->assertSessionHas('error');

    expect($order->fresh()->payment_status)->toBe('pending');
});

test('esewa callback rejects an invalid signature', function () {
    $user = User::factory()->create();

    $order = createPendingEsewaOrder($user);

    config([
        'services.esewa.secret_key' => 'test-secret-key',
        'services.esewa.merchant_code' => 'EPAYTEST',
    ]);

    session([
        'esewa_order_id' => $order->id,
        'esewa_payment_group_id' => 'TEST-GROUP-123',
        'esewa_transaction_uuid' => 'ORD-TEST-UUID-123',
    ]);

    $payload = esewaTestPayload([
        'signature' => 'INVALID-SIGNATURE',
    ]);

    $response = $this
        ->actingAs($user)
        ->post('/payment/esewa/success', $payload);

    $response
        ->assertRedirect(route('orders.index'))
        ->assertSessionHas('error');

    expect($order->fresh()->payment_status)->toBe('pending');
});

test('esewa callback rejects an amount that does not match the order', function () {
    $user = User::factory()->create();

    $order = createPendingEsewaOrder($user);

    config([
        'services.esewa.secret_key' => 'test-secret-key',
        'services.esewa.merchant_code' => 'EPAYTEST',
    ]);

    session([
        'esewa_order_id' => $order->id,
        'esewa_payment_group_id' => 'TEST-GROUP-123',
        'esewa_transaction_uuid' => 'ORD-TEST-UUID-123',
    ]);

    $payload = esewaTestPayload([
        'total_amount' => '999.00',
    ]);

    $response = $this
        ->actingAs($user)
        ->post('/payment/esewa/success', $payload);

    $response
        ->assertRedirect(route('orders.index'))
        ->assertSessionHas('error');

    expect($order->fresh()->payment_status)->toBe('pending');
});

test('esewa callback rejects an unexpected merchant code', function () {
    $user = User::factory()->create();

    $order = createPendingEsewaOrder($user);

    config([
        'services.esewa.secret_key' => 'test-secret-key',
        'services.esewa.merchant_code' => 'REAL-MERCHANT',
    ]);

    session([
        'esewa_order_id' => $order->id,
        'esewa_payment_group_id' => 'TEST-GROUP-123',
        'esewa_transaction_uuid' => 'ORD-TEST-UUID-123',
    ]);

    $payload = esewaTestPayload([
        'product_code' => 'EPAYTEST',
    ]);

    $response = $this
        ->actingAs($user)
        ->post('/payment/esewa/success', $payload);

    $response
        ->assertRedirect(route('orders.index'))
        ->assertSessionHas('error');

    expect($order->fresh()->payment_status)->toBe('pending');
});

test('esewa callback does not mark payment paid when gateway verification fails', function () {
    $user = User::factory()->create();

    $order = createPendingEsewaOrder($user);

    config([
        'services.esewa.secret_key' => 'test-secret-key',
        'services.esewa.merchant_code' => 'EPAYTEST',
        'services.esewa.status_url' => 'https://example.test/esewa/status',
    ]);

    session([
        'esewa_order_id' => $order->id,
        'esewa_payment_group_id' => 'TEST-GROUP-123',
        'esewa_transaction_uuid' => 'ORD-TEST-UUID-123',
    ]);

    Http::fake([
        'https://example.test/esewa/status*' => Http::response([
            'status' => 'FAILED',
            'transaction_uuid' => 'ORD-TEST-UUID-123',
            'product_code' => 'EPAYTEST',
            'total_amount' => '100.00',
        ], 200),
    ]);

    $payload = esewaTestPayload();

    $response = $this
        ->actingAs($user)
        ->post('/payment/esewa/success', $payload);

    $response
        ->assertRedirect(route('orders.index'))
        ->assertSessionHas('error');

    expect($order->fresh()->payment_status)->toBe('pending');
});
