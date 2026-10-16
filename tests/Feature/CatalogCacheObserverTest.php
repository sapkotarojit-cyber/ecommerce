<?php

use App\Models\Category;
use App\Models\Dokan;
use App\Models\Product;
use App\Services\CatalogCache;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;

uses(RefreshDatabase::class);

beforeEach(function () {
    Cache::flush();
});

function createCatalogCacheTestSeller(): Dokan
{
    return Dokan::create([
        'email' => 'cache-seller-' . uniqid() . '@example.com',
        'password' => 'TestPassword123!',
        'company_name' => 'Catalog Cache Test Seller',
        'logo' => 'test-logo.png',
        'reg_no' => 'REG-' . strtoupper(bin2hex(random_bytes(5))),
        'contact_number' => '9800000000',
        'status' => Dokan::STATUS_APPROVED,
    ]);
}

test('creating a product invalidates the catalog cache', function () {
    $seller = createCatalogCacheTestSeller();
    $catalogCache = app(CatalogCache::class);

    $catalogCache->remember('products', fn () => 'stale');

    Product::create([
        'dokan_id' => $seller->id,
        'title' => 'Cache Observer Test Product',
        'description' => 'Product used to test cache invalidation.',
    ]);

    $result = $catalogCache->remember(
        'products',
        fn () => 'rebuilt'
    );

    expect($result)->toBe('rebuilt');
});

test('updating a product invalidates the catalog cache', function () {
    $seller = createCatalogCacheTestSeller();

    $product = Product::create([
        'dokan_id' => $seller->id,
        'title' => 'Original Cache Test Product',
        'description' => 'Original description.',
    ]);

    $catalogCache = app(CatalogCache::class);
    $catalogCache->remember('products', fn () => 'stale');

    $product->update([
        'title' => 'Updated Cache Test Product',
    ]);

    $result = $catalogCache->remember(
        'products',
        fn () => 'rebuilt'
    );

    expect($result)->toBe('rebuilt');
});

test('creating a category invalidates the catalog cache', function () {
    $catalogCache = app(CatalogCache::class);
    $catalogCache->remember('categories', fn () => 'stale');

    Category::create([
        'name' => 'Cache Test Category',
        'slug' => 'cache-test-' . bin2hex(random_bytes(5)),
        'is_active' => true,
    ]);

    $result = $catalogCache->remember(
        'categories',
        fn () => 'rebuilt'
    );

    expect($result)->toBe('rebuilt');
});

test('updating a category invalidates the catalog cache', function () {
    $category = Category::create([
        'name' => 'Original Cache Category',
        'slug' => 'original-cache-' . bin2hex(random_bytes(5)),
        'is_active' => true,
    ]);

    $catalogCache = app(CatalogCache::class);
    $catalogCache->remember('categories', fn () => 'stale');

    $category->update([
        'name' => 'Updated Cache Category',
    ]);

    $result = $catalogCache->remember(
        'categories',
        fn () => 'rebuilt'
    );

    expect($result)->toBe('rebuilt');
});
