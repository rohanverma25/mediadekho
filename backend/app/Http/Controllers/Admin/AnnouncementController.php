<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class AnnouncementController extends Controller
{
    public function index(): View
    {
        $this->authorize('viewAny', Announcement::class);

        return view('admin.announcements.index');
    }

    /**
     * Flat listing as JSON for the client-side DataTable — the announcement
     * list is small, so unlike Media Inventory this doesn't need server-side processing.
     */
    public function data(): JsonResponse
    {
        $this->authorize('viewAny', Announcement::class);

        $announcements = Announcement::query()
            ->orderBy('sort_order')
            ->orderByDesc('event_date')
            ->get()
            ->map(fn (Announcement $announcement) => [
                'id' => $announcement->id,
                'title' => $announcement->title,
                'message_preview' => Str::limit(strip_tags($announcement->message), 80),
                'event_date' => $announcement->event_date?->format('Y-m-d'),
                'status' => $announcement->status,
                'sort_order' => $announcement->sort_order,
            ]);

        return response()->json(['data' => $announcements]);
    }

    public function store(Request $request): JsonResponse
    {
        $this->authorize('create', Announcement::class);

        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'message' => ['required', 'string'],
            'event_date' => ['nullable', 'date'],
            'status' => ['required', 'in:active,inactive'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ]);

        $announcement = Announcement::query()->create($data);

        return response()->json(['message' => 'Announcement created.', 'announcement' => $announcement], 201);
    }

    public function edit(Announcement $announcement): JsonResponse
    {
        $this->authorize('view', $announcement);

        return response()->json(['announcement' => $announcement]);
    }

    public function update(Request $request, Announcement $announcement): JsonResponse
    {
        $this->authorize('update', $announcement);

        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'message' => ['required', 'string'],
            'event_date' => ['nullable', 'date'],
            'status' => ['required', 'in:active,inactive'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ]);

        $announcement->update($data);

        return response()->json(['message' => 'Announcement updated.', 'announcement' => $announcement]);
    }

    public function destroy(Announcement $announcement): JsonResponse
    {
        $this->authorize('delete', $announcement);

        $announcement->delete();

        return response()->json(['message' => 'Announcement deleted.']);
    }
}
