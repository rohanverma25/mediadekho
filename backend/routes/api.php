<?php

use App\Http\Controllers\Api\AnnouncementController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\AwardController;
use App\Http\Controllers\Api\AwardNominationController;
use App\Http\Controllers\Api\BlogController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\ClientLogoController;
use App\Http\Controllers\Api\ContactLeadController;
use App\Http\Controllers\Api\FaqController;
use App\Http\Controllers\Api\JobApplicationController;
use App\Http\Controllers\Api\JobController;
use App\Http\Controllers\Api\MediaCategoryController;
use App\Http\Controllers\Api\MediaInventoryController;
use App\Http\Controllers\Api\NewsController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\ProfileController;
use App\Http\Controllers\Api\SettingController;
use Illuminate\Support\Facades\Route;

Route::apiResource('categories', CategoryController::class);

Route::post('register', [AuthController::class, 'register'])->middleware('throttle:10,1');
Route::post('login', [AuthController::class, 'login'])->middleware('throttle:10,1');
Route::post('contact', [ContactLeadController::class, 'store'])->middleware('throttle:10,1');

// Open to guests, but the controller links the nomination to the caller's
// account when a valid Bearer token is present (see AwardNominationController::store).
Route::post('award-nominations', [AwardNominationController::class, 'store'])->middleware('throttle:10,1');

// Same pattern — open to guests, linked to the account when authenticated.
Route::post('job-applications', [JobApplicationController::class, 'store'])->middleware('throttle:10,1');

Route::middleware('auth:sanctum')->group(function () {
    Route::post('logout', [AuthController::class, 'logout']);
    Route::get('me', [AuthController::class, 'me']);

    Route::get('my/award-nominations', [AwardNominationController::class, 'mine']);

    Route::post('orders', [OrderController::class, 'store']);
    Route::get('orders', [OrderController::class, 'index']);
    Route::get('orders/{order:order_number}', [OrderController::class, 'show']);
    Route::post('orders/{order:order_number}/verify', [OrderController::class, 'verify']);

    Route::get('my/contact-leads', [ContactLeadController::class, 'mine']);
    Route::put('profile', [ProfileController::class, 'update']);
});

// Public browsing — guests get Retail pricing, a valid Bearer token resolves
// the caller's real tier (see MediaInventoryResource / PricingService).
// Rate-limited since these are the search/detail endpoints exposed to the
// open marketplace, not gated behind auth.
Route::middleware('throttle:60,1')->group(function () {
    Route::get('media-categories', [MediaCategoryController::class, 'index']);
    Route::get('media-categories/{category}', [MediaCategoryController::class, 'show']);

    Route::get('client-logos', [ClientLogoController::class, 'index']);

    Route::get('news', [NewsController::class, 'index']);

    Route::get('awards', [AwardController::class, 'index']);

    Route::get('jobs', [JobController::class, 'index']);

    Route::get('announcements', [AnnouncementController::class, 'index']);

    Route::get('faqs', [FaqController::class, 'index']);

    Route::get('blogs', [BlogController::class, 'index']);
    Route::get('blogs/{blog:slug}', [BlogController::class, 'show']);

    Route::get('settings', [SettingController::class, 'index']);

    Route::get('media-inventory', [MediaInventoryController::class, 'index']);
    Route::get('media-inventory/{inventory:slug}', [MediaInventoryController::class, 'show']);
    Route::get('media-inventory/{inventory:slug}/price', [MediaInventoryController::class, 'price']);
});

// Staff-only management API (Sanctum token + Policy-gated).
Route::middleware('auth:sanctum')->group(function () {
    Route::post('media-inventory', [MediaInventoryController::class, 'store']);
    Route::put('media-inventory/{inventory}', [MediaInventoryController::class, 'update']);
    Route::delete('media-inventory/{inventory}', [MediaInventoryController::class, 'destroy']);
    Route::post('media-inventory/{inventory}/price', [MediaInventoryController::class, 'storePrice']);
});
