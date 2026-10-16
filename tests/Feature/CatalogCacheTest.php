<?php

use App\Services\CatalogCache;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

beforeEach(function () {
    Cache::flush();
});

test('remember reuses the cached result for the same key', function () {
    $catalogCache = app(CatalogCache::class);
    $callbackCalls = 0;

    $callback = function () use (&$callbackCalls) {
        $callbackCalls++;

        return ['products' => ['Product A']];
    };

    $first = $catalogCache->remember('products', $callback);
    $second = $catalogCache->remember('products', $callback);

    expect($first)->toBe(['products' => ['Product A']])
        ->and($second)->toBe($first)
        ->and($callbackCalls)->toBe(1);
});

test('invalidate causes the next request to rebuild the cached result', function () {
    $catalogCache = app(CatalogCache::class);

    $first = $catalogCache->remember(
        'products',
        fn () => ['version' => 1]
    );

    $catalogCache->invalidate();

    $second = $catalogCache->remember(
        'products',
        fn () => ['version' => 2]
    );

    expect($first)->toBe(['version' => 1])
        ->and($second)->toBe(['version' => 2]);
});

test('different cache keys maintain independent results', function () {
    $catalogCache = app(CatalogCache::class);

    $products = $catalogCache->remember(
        'products',
        fn () => ['type' => 'products']
    );

    $categories = $catalogCache->remember(
        'categories',
        fn () => ['type' => 'categories']
    );

    expect($products)->toBe(['type' => 'products'])
        ->and($categories)->toBe(['type' => 'categories']);
});

test('invalidateAfterCommit invalidates immediately outside a transaction', function () {
    $catalogCache = app(CatalogCache::class);

    $catalogCache->remember(
        'products',
        fn () => 'old'
    );

    $catalogCache->invalidateAfterCommit();

    $result = $catalogCache->remember(
        'products',
        fn () => 'new'
    );

    expect($result)->toBe('new');
});

test('invalidateAfterCommit waits until the transaction commits', function () {
    $catalogCache = app(CatalogCache::class);

    $catalogCache->remember(
        'products',
        fn () => 'old'
    );

    DB::beginTransaction();

    try {
        $catalogCache->invalidateAfterCommit();

        // The old cache remains available before the commit.
        $beforeCommit = $catalogCache->remember(
            'products',
            fn () => 'unexpected-before-commit'
        );

        expect($beforeCommit)->toBe('old');

        DB::commit();
    } catch (Throwable $exception) {
        if (DB::transactionLevel() > 0) {
            DB::rollBack();
        }

        throw $exception;
    }

    $afterCommit = $catalogCache->remember(
        'products',
        fn () => 'new'
    );

    expect($afterCommit)->toBe('new');
});
