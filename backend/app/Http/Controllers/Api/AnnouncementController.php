<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\AnnouncementResource;
use App\Models\Announcement;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class AnnouncementController extends Controller
{
    /**
     * Active announcements for the customer dashboard. An event announcement
     * drops off automatically once its date has passed — general
     * announcements (no event_date) stay until deactivated.
     */
    public function index(): AnonymousResourceCollection
    {
        $announcements = Announcement::query()
            ->where('status', 'active')
            ->where(function ($query) {
                $query->whereNull('event_date')->orWhereDate('event_date', '>=', now()->toDateString());
            })
            ->orderBy('sort_order')
            ->orderBy('event_date')
            ->get();

        return AnnouncementResource::collection($announcements);
    }
}
