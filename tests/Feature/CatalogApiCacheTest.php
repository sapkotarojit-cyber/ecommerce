<?php

use App\Models\Category;
use App\Models\Dokan;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;

uses(RefreshDatabase::class);

beforeEach(function () {
    Cache::flush();
});

function createCatalogApiTestSeller(): Dokan
{
    return Dokan::create([
        'email' => 'catalog-api-' . uniqid() . '@example.com',
        'password' => 'TestPassword123!',
        'company_name' => 'Catalog API Test Seller',
        'logo' => 'test-logo.png',
        'reg_no' => 'REG-' . strtoupper(bin2hex(random_bytes(5))),
        'contact_number' => '9800000000',
        'status' => Dokan::STATUS_APPROVED,
    ]);
}

function createCatalogApiTestProduct(Dokan $seller, array $overrides = []): Product
{
    return Product::create(array_merge([
        'dokan_id' => $seller->id,
        'title' => 'Catalog API Test Product',
        'description' => 'Product created for catalog API tests.',
    ], $overrides));
}

test('product listing returns the expected JSON structure', function () {
    $seller = createCatalogApiTestSeller();

    $product = createCatalogApiTestProduct($seller);

    $this->getJson('/api/products')
        ->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonPath('total', 1)
        ->assertJsonPath('data.0.id', $product->id)
        ->assertJsonPath('data.0.title', $product->title)
        ->assertJsonPath('data.0.dokan_id', $seller->id)
        ->assertJsonStructure([
            'success',
            'current_page',
            'data',
            'first_page_url',
            'last_page',
            'per_page',
            'total',
        ]);
});

test('repeated product listing requests return consistent cached data', function () {
    $seller = createCatalogApiTestSeller();

    createCatalogApiTestProduct($seller, [
        'title' => 'Cached Product',
    ]);

    $first = $this->getJson('/api/products')
        ->assertOk()
        ->json();

    $second = $this->getJson('/api/products')
        ->assertOk()
        ->json();

    expect($second)->toBe($first);
});

test('product detail returns the expected JSON structure', function () {
    $seller = createCatalogApiTestSeller();

    $product = createCatalogApiTestProduct($seller);

    $this->getJson('/api/products/' . $product->id)
        ->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonPath('data.id', $product->id)
        ->assertJsonPath('data.title', $product->title)
        ->assertJsonStructure([
            'success',
            'data' => [
                'id',
                'title',
                'description',
                'dokan_id',
                'category',
                'dokan',
                'varients',
            ],
        ]);
});

test('missing product detail returns 404', function () {
    $this->getJson('/api/products/999999')
        ->assertNotFound()
        ->assertJsonPath('success', false)
        ->assertJsonPath('message', 'Product not found.');
});

test('product listing returns updated data after a product update', function () {
    $seller = createCatalogApiTestSeller();

    $product = createCatalogApiTestProduct($seller, [
        'title' => 'Original Product Title',
    ]);

    $this->getJson('/api/products')
        ->assertOk()
        ->assertJsonFragment([
            'title' => 'Original Product Title',
        ]);

    $product->update([
        'title' => 'Updated Product Title',
    ]);

    $this->getJson('/api/products')
        ->assertOk()
        ->assertJsonFragment([
            'title' => 'Updated Product Title',
        ])
        ->assertJsonMissing([
            'title' => 'Original Product Title',
        ]);
});

test('product detail returns updated data after a product update', function () {
    $seller = createCatalogApiTestSeller();

    $product = createCatalogApiTestProduct($seller, [
        'title' => 'Original Detail Title',
    ]);

    $this->getJson('/api/products/' . $product->id)
        ->assertOk()
        ->assertJsonPath('data.title', 'Original Detail Title');

    $product->update([
        'title' => 'Updated Detail Title',
    ]);

    $this->getJson('/api/products/' . $product->id)
        ->assertOk()
        ->assertJsonPath('data.title', 'Updated Detail Title');
});

test('category endpoint returns active categories only', function () {
    $active = Category::create([
        'name' => 'Active Test Category',
        'slug' => 'active-test-' . bin2hex(random_bytes(5)),
        'is_active' => true,
    ]);

    Category::create([
        'name' => 'Inactive Test Category',
        'slug' => 'inactive-test-' . bin2hex(random_bytes(5)),
        'is_active' => false,
    ]);

    $this->getJson('/api/categories')
        ->assertOk()
        ->assertJsonPath('data.0.id', $active->id)
        ->assertJsonFragment([
            'id' => $active->id,
            'name' => $active->name,
            'is_active' => true,
        ])
        ->assertJsonMissing([
            'name' => 'Inactive Test Category',
        ]);
});

test('category endpoint returns newly created categories after cache invalidation', function () {
    $this->getJson('/api/categories')
        ->assertOk()
        ->assertJsonMissing([
            'name' => 'Newly Created Cache Category',
        ]);

    $category = Category::create([
        'name' => 'Newly Created Cache Category',
        'slug' => 'new-cache-' . bin2hex(random_bytes(5)),
        'is_active' => true,
    ]);

    $this->getJson('/api/categories')
        ->assertOk()
        ->assertJsonFragment([
            'id' => $category->id,
            'name' => 'Newly Created Cache Category',
            'is_active' => true,
        ]);
});
