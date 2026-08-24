<?php

namespace App\Providers;

use App\Repositories\Contracts\MediaCategoryRepositoryInterface;
use App\Repositories\Contracts\MediaInventoryRepositoryInterface;
use App\Repositories\Eloquent\MediaCategoryRepository;
use App\Repositories\Eloquent\MediaInventoryRepository;
use Illuminate\Support\ServiceProvider;

class RepositoryServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(MediaCategoryRepositoryInterface::class, MediaCategoryRepository::class);
        $this->app->bind(MediaInventoryRepositoryInterface::class, MediaInventoryRepository::class);
    }
}
