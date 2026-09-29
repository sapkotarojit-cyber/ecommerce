<?php

use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

uses(RefreshDatabase::class);

test('customer cannot view another customers order', function () {
    $owner = User::factory()->create();
    $other = User::factory()->create();

    $dokanId = DB::table('dokans')->insertGetId([
        'email' => 'vendor-' . uniqid() . '@example.com',
        'password' => bcrypt('password'),
        'company_name' => 'Security Test Vendor',
        'logo' => 'logo.png',
        'reg_no' => 'REG-' . uniqid(),
        'contact_number' => '9800000000',
        'status' => 'approved',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $addressId = DB::table('shipping_addresses')->insertGetId([
        'user_id' => $owner->id,
        'name' => 'Owner',
        'phone' => '9800000000',
        'region' => 'Kathmandu',
        'address' => 'Test address',
        'address_type' => 'Home',
        'is_default_shipping' => true,
        'is_default_billing' => true,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $order = Order::create([
        'user_id' => $owner->id,
        'dokan_id' => $dokanId,
        'shipping_address_id' => $addressId,
        'total_amount' => 100,
        'status' => 'pending',
        'order_status' => 'pending',
        'payment_method' => 'cod',
        'payment_status' => 'pending',
    ]);

    $response = $this->actingAs($other)->get(
        route('orders.show', $order->id)
    );

    $response->assertNotFound();
});