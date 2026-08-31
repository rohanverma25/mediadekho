<?php

use App\Http\Controllers\Admin\ActivityLogController;
use App\Http\Controllers\Admin\AnnouncementController;
use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\AwardController;
use App\Http\Controllers\Admin\AwardNominationController;
use App\Http\Controllers\Admin\BlogController;
use App\Http\Controllers\Admin\MagazineController;
use App\Http\Controllers\Admin\ClientLogoController;
use App\Http\Controllers\Admin\ContactLeadController;
use App\Http\Controllers\Admin\CustomerController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\FaqController;
use App\Http\Controllers\Admin\FrequencyController;
use App\Http\Controllers\Admin\IndustryController;
use App\Http\Controllers\Admin\JobApplicationController;
use App\Http\Controllers\Admin\MediaListingRequestController;
use App\Http\Controllers\Admin\JobController;
use App\Http\Controllers\Admin\LanguageController;
use App\Http\Controllers\Admin\MediaCategoryController;
use App\Http\Controllers\Admin\MediaInventoryController;
use App\Http\Controllers\Admin\MediaSubCategoryController;
use App\Http\Controllers\Admin\NewsController;
use App\Http\Controllers\Admin\OrderController;
use App\Http\Controllers\Admin\PageMetaController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\StaffUserController;
use App\Http\Controllers\Admin\StatController;
use App\Http\Controllers\Admin\VideoController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::prefix('admin')->name('admin.')->group(function () {
    Route::get('login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('login', [AuthController::class, 'login'])->name('login.attempt');

    Route::middleware(['auth', 'staff'])->group(function () {
        Route::post('logout', [AuthController::class, 'logout'])->name('logout');

        Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

        Route::get('media-categories', [MediaCategoryController::class, 'index'])->name('categories.index');
        Route::get('media-categories/data', [MediaCategoryController::class, 'data'])->name('categories.data');
        Route::post('media-categories', [MediaCategoryController::class, 'store'])->name('categories.store');
        Route::get('media-categories/{category}/edit', [MediaCategoryController::class, 'edit'])->name('categories.edit');
        Route::put('media-categories/{category}', [MediaCategoryController::class, 'update'])->name('categories.update');
        Route::delete('media-categories/{category}', [MediaCategoryController::class, 'destroy'])->name('categories.destroy');

        Route::get('media-subcategories', [MediaSubCategoryController::class, 'index'])->name('subcategories.index');
        Route::get('media-subcategories/data', [MediaSubCategoryController::class, 'data'])->name('subcategories.data');
        Route::post('media-subcategories', [MediaSubCategoryController::class, 'store'])->name('subcategories.store');
        Route::get('media-subcategories/{subcategory}/edit', [MediaSubCategoryController::class, 'edit'])->name('subcategories.edit');
        Route::put('media-subcategories/{subcategory}', [MediaSubCategoryController::class, 'update'])->name('subcategories.update');
        Route::delete('media-subcategories/{subcategory}', [MediaSubCategoryController::class, 'destroy'])->name('subcategories.destroy');

        Route::get('media-inventory', [MediaInventoryController::class, 'index'])->name('media-inventory.index');
        Route::get('media-inventory/data', [MediaInventoryController::class, 'data'])->name('media-inventory.data');
        Route::get('media-inventory/create', [MediaInventoryController::class, 'create'])->name('media-inventory.create');
        Route::get('media-inventory/export', [MediaInventoryController::class, 'export'])->name('media-inventory.export');
        Route::post('media-inventory/import', [MediaInventoryController::class, 'import'])->name('media-inventory.import');
        Route::post('media-inventory', [MediaInventoryController::class, 'store'])->name('media-inventory.store');
        Route::get('media-inventory/{inventory}', [MediaInventoryController::class, 'show'])->name('media-inventory.show');
        Route::get('media-inventory/{inventory}/edit', [MediaInventoryController::class, 'edit'])->name('media-inventory.edit');
        Route::put('media-inventory/{inventory}', [MediaInventoryController::class, 'update'])->name('media-inventory.update');
        Route::delete('media-inventory/{inventory}', [MediaInventoryController::class, 'destroy'])->name('media-inventory.destroy');
        Route::post('media-inventory/{inventory}/price', [MediaInventoryController::class, 'storePrice'])->name('media-inventory.price.store');
        Route::delete('media-inventory/{inventory}/images/{image}', [MediaInventoryController::class, 'destroyImage'])->name('media-inventory.images.destroy');
        Route::delete('media-inventory/{inventory}/files/{file}', [MediaInventoryController::class, 'destroyFile'])->name('media-inventory.files.destroy');

        Route::get('frequencies', [FrequencyController::class, 'index'])->name('frequencies.index');
        Route::get('frequencies/data', [FrequencyController::class, 'data'])->name('frequencies.data');
        Route::post('frequencies', [FrequencyController::class, 'store'])->name('frequencies.store');
        Route::get('frequencies/{frequency}/edit', [FrequencyController::class, 'edit'])->name('frequencies.edit');
        Route::put('frequencies/{frequency}', [FrequencyController::class, 'update'])->name('frequencies.update');
        Route::delete('frequencies/{frequency}', [FrequencyController::class, 'destroy'])->name('frequencies.destroy');

        Route::get('languages', [LanguageController::class, 'index'])->name('languages.index');
        Route::get('languages/data', [LanguageController::class, 'data'])->name('languages.data');
        Route::post('languages', [LanguageController::class, 'store'])->name('languages.store');
        Route::get('languages/{language}/edit', [LanguageController::class, 'edit'])->name('languages.edit');
        Route::put('languages/{language}', [LanguageController::class, 'update'])->name('languages.update');
        Route::delete('languages/{language}', [LanguageController::class, 'destroy'])->name('languages.destroy');

        Route::get('faqs', [FaqController::class, 'index'])->name('faqs.index');
        Route::get('faqs/data', [FaqController::class, 'data'])->name('faqs.data');
        Route::post('faqs', [FaqController::class, 'store'])->name('faqs.store');
        Route::get('faqs/{faq}/edit', [FaqController::class, 'edit'])->name('faqs.edit');
        Route::put('faqs/{faq}', [FaqController::class, 'update'])->name('faqs.update');
        Route::delete('faqs/{faq}', [FaqController::class, 'destroy'])->name('faqs.destroy');

        Route::get('blogs', [BlogController::class, 'index'])->name('blogs.index');
        Route::get('blogs/data', [BlogController::class, 'data'])->name('blogs.data');
        Route::post('blogs', [BlogController::class, 'store'])->name('blogs.store');
        Route::get('blogs/{blog}/edit', [BlogController::class, 'edit'])->name('blogs.edit');
        Route::put('blogs/{blog}', [BlogController::class, 'update'])->name('blogs.update');
        Route::delete('blogs/{blog}', [BlogController::class, 'destroy'])->name('blogs.destroy');

        Route::get('magazines', [MagazineController::class, 'index'])->name('magazines.index');
        Route::get('magazines/data', [MagazineController::class, 'data'])->name('magazines.data');
        Route::post('magazines', [MagazineController::class, 'store'])->name('magazines.store');
        Route::get('magazines/{magazine}/edit', [MagazineController::class, 'edit'])->name('magazines.edit');
        Route::put('magazines/{magazine}', [MagazineController::class, 'update'])->name('magazines.update');
        Route::delete('magazines/{magazine}', [MagazineController::class, 'destroy'])->name('magazines.destroy');

        Route::get('news', [NewsController::class, 'index'])->name('news.index');
        Route::get('news/data', [NewsController::class, 'data'])->name('news.data');
        Route::post('news', [NewsController::class, 'store'])->name('news.store');
        Route::get('news/{news}/edit', [NewsController::class, 'edit'])->name('news.edit');
        Route::put('news/{news}', [NewsController::class, 'update'])->name('news.update');
        Route::delete('news/{news}', [NewsController::class, 'destroy'])->name('news.destroy');

        Route::get('leads', [ContactLeadController::class, 'index'])->name('leads.index');
        Route::get('leads/data', [ContactLeadController::class, 'data'])->name('leads.data');
        Route::put('leads/{lead}', [ContactLeadController::class, 'update'])->name('leads.update');
        Route::delete('leads/{lead}', [ContactLeadController::class, 'destroy'])->name('leads.destroy');

        Route::get('orders', [OrderController::class, 'index'])->name('orders.index');
        Route::get('orders/data', [OrderController::class, 'data'])->name('orders.data');
        Route::get('orders/{order}', [OrderController::class, 'show'])->name('orders.show');
        Route::put('orders/{order}', [OrderController::class, 'update'])->name('orders.update');

        Route::get('customers', [CustomerController::class, 'index'])->name('customers.index');
        Route::get('customers/data', [CustomerController::class, 'data'])->name('customers.data');
        Route::put('customers/{customer}/approve', [CustomerController::class, 'approve'])->name('customers.approve');
        Route::put('customers/{customer}/reject', [CustomerController::class, 'reject'])->name('customers.reject');

        Route::get('awards', [AwardController::class, 'index'])->name('awards.index');
        Route::get('awards/data', [AwardController::class, 'data'])->name('awards.data');
        Route::post('awards', [AwardController::class, 'store'])->name('awards.store');
        Route::get('awards/{award}/edit', [AwardController::class, 'edit'])->name('awards.edit');
        Route::put('awards/{award}', [AwardController::class, 'update'])->name('awards.update');
        Route::delete('awards/{award}', [AwardController::class, 'destroy'])->name('awards.destroy');

        Route::get('award-nominations', [AwardNominationController::class, 'index'])->name('award-nominations.index');
        Route::get('award-nominations/data', [AwardNominationController::class, 'data'])->name('award-nominations.data');
        Route::put('award-nominations/{nomination}', [AwardNominationController::class, 'update'])->name('award-nominations.update');
        Route::delete('award-nominations/{nomination}', [AwardNominationController::class, 'destroy'])->name('award-nominations.destroy');

        Route::get('jobs', [JobController::class, 'index'])->name('jobs.index');
        Route::get('jobs/data', [JobController::class, 'data'])->name('jobs.data');
        Route::post('jobs', [JobController::class, 'store'])->name('jobs.store');
        Route::get('jobs/{job}/edit', [JobController::class, 'edit'])->name('jobs.edit');
        Route::put('jobs/{job}', [JobController::class, 'update'])->name('jobs.update');
        Route::delete('jobs/{job}', [JobController::class, 'destroy'])->name('jobs.destroy');

        Route::get('job-applications', [JobApplicationController::class, 'index'])->name('job-applications.index');
        Route::get('job-applications/data', [JobApplicationController::class, 'data'])->name('job-applications.data');
        Route::put('job-applications/{application}', [JobApplicationController::class, 'update'])->name('job-applications.update');
        Route::delete('job-applications/{application}', [JobApplicationController::class, 'destroy'])->name('job-applications.destroy');

        Route::get('media-listing-requests', [MediaListingRequestController::class, 'index'])->name('media-listing-requests.index');
        Route::get('media-listing-requests/data', [MediaListingRequestController::class, 'data'])->name('media-listing-requests.data');
        Route::put('media-listing-requests/{mediaListingRequest}', [MediaListingRequestController::class, 'update'])->name('media-listing-requests.update');
        Route::delete('media-listing-requests/{mediaListingRequest}', [MediaListingRequestController::class, 'destroy'])->name('media-listing-requests.destroy');

        Route::get('activity-log', [ActivityLogController::class, 'index'])->name('activity-log.index');

        Route::get('client-logos', [ClientLogoController::class, 'index'])->name('client-logos.index');
        Route::get('client-logos/data', [ClientLogoController::class, 'data'])->name('client-logos.data');
        Route::post('client-logos', [ClientLogoController::class, 'store'])->name('client-logos.store');
        Route::get('client-logos/{clientLogo}/edit', [ClientLogoController::class, 'edit'])->name('client-logos.edit');
        Route::put('client-logos/{clientLogo}', [ClientLogoController::class, 'update'])->name('client-logos.update');
        Route::delete('client-logos/{clientLogo}', [ClientLogoController::class, 'destroy'])->name('client-logos.destroy');

        Route::get('industries', [IndustryController::class, 'index'])->name('industries.index');
        Route::get('industries/data', [IndustryController::class, 'data'])->name('industries.data');
        Route::post('industries', [IndustryController::class, 'store'])->name('industries.store');
        Route::get('industries/{industry}/edit', [IndustryController::class, 'edit'])->name('industries.edit');
        Route::put('industries/{industry}', [IndustryController::class, 'update'])->name('industries.update');
        Route::delete('industries/{industry}', [IndustryController::class, 'destroy'])->name('industries.destroy');

        Route::get('stats', [StatController::class, 'index'])->name('stats.index');
        Route::get('stats/data', [StatController::class, 'data'])->name('stats.data');
        Route::post('stats', [StatController::class, 'store'])->name('stats.store');
        Route::get('stats/{stat}/edit', [StatController::class, 'edit'])->name('stats.edit');
        Route::put('stats/{stat}', [StatController::class, 'update'])->name('stats.update');
        Route::delete('stats/{stat}', [StatController::class, 'destroy'])->name('stats.destroy');

        Route::get('videos', [VideoController::class, 'index'])->name('videos.index');
        Route::get('videos/data', [VideoController::class, 'data'])->name('videos.data');
        Route::post('videos', [VideoController::class, 'store'])->name('videos.store');
        Route::get('videos/{video}/edit', [VideoController::class, 'edit'])->name('videos.edit');
        Route::put('videos/{video}', [VideoController::class, 'update'])->name('videos.update');
        Route::delete('videos/{video}', [VideoController::class, 'destroy'])->name('videos.destroy');

        Route::get('announcements', [AnnouncementController::class, 'index'])->name('announcements.index');
        Route::get('announcements/data', [AnnouncementController::class, 'data'])->name('announcements.data');
        Route::post('announcements', [AnnouncementController::class, 'store'])->name('announcements.store');
        Route::get('announcements/{announcement}/edit', [AnnouncementController::class, 'edit'])->name('announcements.edit');
        Route::put('announcements/{announcement}', [AnnouncementController::class, 'update'])->name('announcements.update');
        Route::delete('announcements/{announcement}', [AnnouncementController::class, 'destroy'])->name('announcements.destroy');

        Route::get('page-meta', [PageMetaController::class, 'index'])->name('page-meta.index');
        Route::get('page-meta/{pageMeta}/edit', [PageMetaController::class, 'edit'])->name('page-meta.edit');
        Route::put('page-meta/{pageMeta}', [PageMetaController::class, 'update'])->name('page-meta.update');

        Route::get('settings', [SettingController::class, 'index'])->name('settings.index');
        Route::put('settings', [SettingController::class, 'update'])->name('settings.update');

        Route::get('roles', [RoleController::class, 'index'])->name('roles.index');
        Route::get('roles/data', [RoleController::class, 'data'])->name('roles.data');
        Route::get('roles/create', [RoleController::class, 'create'])->name('roles.create');
        Route::post('roles', [RoleController::class, 'store'])->name('roles.store');
        Route::get('roles/{role}/edit', [RoleController::class, 'edit'])->name('roles.edit');
        Route::put('roles/{role}', [RoleController::class, 'update'])->name('roles.update');
        Route::delete('roles/{role}', [RoleController::class, 'destroy'])->name('roles.destroy');

        Route::get('staff-users', [StaffUserController::class, 'index'])->name('staff-users.index');
        Route::get('staff-users/data', [StaffUserController::class, 'data'])->name('staff-users.data');
        Route::post('staff-users', [StaffUserController::class, 'store'])->name('staff-users.store');
        Route::get('staff-users/{staffUser}/edit', [StaffUserController::class, 'edit'])->name('staff-users.edit');
        Route::put('staff-users/{staffUser}', [StaffUserController::class, 'update'])->name('staff-users.update');
        Route::delete('staff-users/{staffUser}', [StaffUserController::class, 'destroy'])->name('staff-users.destroy');
    });
});
