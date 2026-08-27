<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Video;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class VideoController extends Controller
{
    public function index(): View
    {
        $this->authorize('viewAny', Video::class);

        return view('admin.videos.index');
    }

    /**
     * Flat listing as JSON for the client-side DataTable — the video
     * catalog is small, so unlike Media Inventory this doesn't need
     * server-side processing.
     */
    public function data(): JsonResponse
    {
        $this->authorize('viewAny', Video::class);

        $videos = Video::query()
            ->orderBy('sort_order')
            ->get()
            ->map(fn (Video $video) => [
                'id' => $video->id,
                'title' => $video->title,
                'youtube_url' => $video->youtube_url,
                'thumbnail_url' => $video->thumbnail_url,
                'status' => $video->status,
                'sort_order' => $video->sort_order,
            ]);

        return response()->json(['data' => $videos]);
    }

    public function store(Request $request): JsonResponse
    {
        $this->authorize('create', Video::class);

        $data = $this->validated($request);

        $video = Video::query()->create($data);

        return response()->json(['message' => 'Video created.', 'video' => $video], 201);
    }

    public function edit(Video $video): JsonResponse
    {
        $this->authorize('view', $video);

        return response()->json(['video' => $video]);
    }

    public function update(Request $request, Video $video): JsonResponse
    {
        $this->authorize('update', $video);

        $data = $this->validated($request);

        $video->update($data);

        return response()->json(['message' => 'Video updated.', 'video' => $video]);
    }

    public function destroy(Video $video): JsonResponse
    {
        $this->authorize('delete', $video);

        $video->delete();

        return response()->json(['message' => 'Video deleted.']);
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'youtube_url' => [
                'required',
                'string',
                'max:255',
                'url',
                function (string $attribute, mixed $value, \Closure $fail): void {
                    if (! Video::extractVideoId($value)) {
                        $fail('Enter a valid YouTube video URL (e.g. https://www.youtube.com/watch?v=... or https://youtu.be/...).');
                    }
                },
            ],
            'status' => ['required', 'in:active,inactive'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ]);
    }
}
