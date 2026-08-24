<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MediaCategory;
use App\Models\MediaInventory;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $stats = [
            'total_inventory' => MediaInventory::query()->count(),
            'published_inventory' => MediaInventory::query()->where('status', 'published')->count(),
            'draft_inventory' => MediaInventory::query()->where('status', 'draft')->count(),
            'total_categories' => MediaCategory::query()->whereNull('parent_id')->count(),
        ];

        $recentInventory = MediaInventory::query()
            ->with(['category', 'frequency', 'creator'])
            ->latest()
            ->limit(8)
            ->get();

        return view('admin.dashboard', compact('stats', 'recentInventory'));
    }
}
