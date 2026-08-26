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

        $this->configurePublicDiskUrl();
    }

    /**
     * config/filesystems.php's `public` disk URL is normally a static
     * `APP_URL`-based value — every uploaded image/file URL (logo, category
     * images, media inventory photos, resumes, ...) inherits it via
     * Storage::disk('public')->url(). Left as-is, that means every one of
     * those breaks the moment .env's APP_URL doesn't exactly match wherever
     * the app is actually being served (a different domain, or the same
     * domain under a different subfolder — exactly what happened deploying
     * this app under /mediadekho/backend/).
     *
     * Overriding it here from the real incoming request — scheme, host, and
     * getBaseUrl() (the front controller's own folder, correctly resolved
     * whether Laravel sits at the domain root or several subfolders deep,
     * and regardless of whether "/public" happens to still be visible in
     * that path on this particular request) — makes every existing
     * accessor correct on any server, including a fresh one, with zero
     * per-deployment config or code changes. Since uploads live directly
     * under public/uploads (see config/filesystems.php), appending
     * "/uploads" to whatever getBaseUrl() resolves to always lands on the
     * real folder — no symlink, no separate "is it actually there" check
     * needed. Console commands (artisan, tests, queue workers) have no
     * real HTTP request to read, so they're left on the .env fallback,
     * which is fine since nothing there serves URLs to a browser.
     */
    private function configurePublicDiskUrl(): void
    {
        if ($this->app->runningInConsole()) {
            return;
        }

        // An explicit ASSET_URL in .env always wins — it's a deliberate,
        // known-correct override (config/filesystems.php already applied
        // it at boot), so there's nothing to auto-detect here.
        if (env('ASSET_URL')) {
            return;
        }

        $request = $this->app['request'];
        $root = rtrim($request->getSchemeAndHttpHost().$request->getBaseUrl(), '/');

        config(['filesystems.disks.public.url' => $root.'/uploads']);
    }
}
