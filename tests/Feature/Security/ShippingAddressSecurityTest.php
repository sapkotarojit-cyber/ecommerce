<?php

use App\Models\ShippingAddress;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('shipping address creation rejects unexpected top-level fields', function () {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->withHeader('Accept', 'application/json')
        ->post('/shipping-address', [
            'name' => 'Security Test',
            'phone' => '9812345678',
            'region' => 'Kathmandu',
            'address' => 'Kathmandu',
            'address_type' => 'Home',
            'unexpected' => 'must-be-rejected',
        ]);

    $response->assertStatus(422);
    $response->assertJsonValidationErrors('request');

    expect(
        ShippingAddress::where('user_id', $user->id)->count()
    )->toBe(0);
});

test('quick shipping address creation rejects unexpected top-level fields', function () {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->withHeader('Accept', 'application/json')
        ->post('/shipping-address/quick-store', [
            'name' => 'Security Test',
            'phone' => '9812345678',
            'region' => 'Kathmandu',
            'address' => 'Kathmandu',
            'address_type' => 'Home',
            'unexpected' => 'must-be-rejected',
        ]);

    $response->assertStatus(422);
    $response->assertJsonValidationErrors('request');

    expect(
        ShippingAddress::where('user_id', $user->id)->count()
    )->toBe(0);
});

test('shipping address update rejects unexpected top-level fields', function () {
    $user = User::factory()->create();

    $address = ShippingAddress::create([
        'user_id' => $user->id,
        'name' => 'Existing User',
        'phone' => '9812345678',
        'region' => 'Kathmandu',
        'address' => 'Kathmandu',
        'address_type' => 'Home',
        'is_default_shipping' => true,
        'is_default_billing' => false,
    ]);

    $response = $this
        ->actingAs($user)
        ->withHeader('Accept', 'application/json')
        ->patch("/shipping-address/{$address->id}", [
            'name' => 'Updated User',
            'phone' => '9812345678',
            'region' => 'Kathmandu',
            'address' => 'Updated Kathmandu',
            'address_type' => 'Home',
            'unexpected' => 'must-be-rejected',
        ]);

    $response->assertStatus(422);
    $response->assertJsonValidationErrors('request');

    expect(
        $address->fresh()->name
    )->toBe('Existing User');
});

test('user cannot update another users shipping address', function () {
    $owner = User::factory()->create();
    $attacker = User::factory()->create();

    $address = ShippingAddress::create([
        'user_id' => $owner->id,
        'name' => 'Owner',
        'phone' => '9812345678',
        'region' => 'Kathmandu',
        'address' => 'Owner Address',
        'address_type' => 'Home',
        'is_default_shipping' => true,
        'is_default_billing' => false,
    ]);

    $response = $this
        ->actingAs($attacker)
        ->patch("/shipping-address/{$address->id}", [
            'name' => 'Attacker',
            'phone' => '9812345678',
            'region' => 'Kathmandu',
            'address' => 'Attacker Address',
            'address_type' => 'Home',
        ]);

    $response->assertForbidden();

    expect(
        $address->fresh()->name
    )->toBe('Owner');
});