<?php

namespace App\Services;

use Closure;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CatalogCache
{
    private const VERSION_KEY = 'empire:catalog:cache-version:v1';

    private const PREFIX = 'empire:catalog:v1:';

    private const TTL_SECONDS = 30;

    public function remember(string $key, Closure $callback): mixed
    {
        $version = Cache::rememberForever(
            self::VERSION_KEY,
            fn (): string => (string) Str::uuid()
        );

        return Cache::remember(
            self::PREFIX . $version . ':' . $key,
            now()->addSeconds(self::TTL_SECONDS),
            $callback
        );
    }

    public function invalidate(): void
    {
        Cache::forever(
            self::VERSION_KEY,
            (string) Str::uuid()
        );
    }

    public function invalidateAfterCommit(): void
    {
        if (DB::transactionLevel() > 0) {
            DB::afterCommit(function (): void {
                $this->invalidate();
            });

            return;
        }

        $this->invalidate();
    }
}
