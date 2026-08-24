<?php

namespace App\Providers;

use App\Models\Blog;
use App\Models\MediaCategory;
use App\Models\MediaInventory;
use App\Observers\BlogObserver;
use App\Observers\MediaCategoryObserver;
use App\Observers\MediaInventoryObserver;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        MediaCategory::observe(MediaCategoryObserver::class);
        MediaInventory::observe(MediaInventoryObserver::class);
        Blog::observe(BlogObserver::class);
    }
}
