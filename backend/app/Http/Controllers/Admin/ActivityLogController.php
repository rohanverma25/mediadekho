<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\View\View;
use Spatie\Activitylog\Models\Activity;

class ActivityLogController extends Controller
{
    /**
     * Gated by the `staff` route middleware — an audit trail viewer isn't a
     * single-model resource, so it doesn't warrant its own Policy class.
     */
    public function index(): View
    {
        $activities = Activity::query()
            ->with('causer')
            ->latest()
            ->paginate(25);

        return view('admin.activity-log.index', compact('activities'));
    }
}
