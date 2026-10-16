<?php

namespace App\Observers;

use App\Services\CatalogCache;
use Illuminate\Contracts\Events\ShouldHandleEventsAfterCommit;

class CatalogCacheObserver implements ShouldHandleEventsAfterCommit
{
    public function __construct(
        private readonly CatalogCache $catalogCache
    ) {
    }

    public function saved(object $model): void
    {
        $this->catalogCache->invalidate();
    }

    public function deleted(object $model): void
    {
        $this->catalogCache->invalidate();
    }

    public function restored(object $model): void
    {
        $this->catalogCache->invalidate();
    }

    public function forceDeleted(object $model): void
    {
        $this->catalogCache->invalidate();
    }
}
