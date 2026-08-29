<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\ImageUploadHelper;
use App\Http\Controllers\Controller;
use App\Models\Video;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class VideoController extends Controller
{
    private const VIDEO_DIRECTORY = 'videos';

    private const THUMBNAIL_DIRECTORY = 'video-thumbnails';

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
                'source_type' => $video->source_type,
                'youtube_url' => $video->youtube_url,
                'video_url' => $video->video_url,
                'thumbnail_url' => $video->thumbnail_url,
                'status' => $video->status,
                'sort_order' => $video->sort_order,
            ]);

        return response()->json(['data' => $videos]);
    }

    public function store(Request $request): JsonResponse
    {
        $this->authorize('create', Video::class);

        $data = $this->validated($request, null);

        if ($data['source_type'] === Video::SOURCE_UPLOAD) {
            $data['video_path'] = ImageUploadHelper::upload($request->file('video_file'), self::VIDEO_DIRECTORY);
            $data['youtube_url'] = '';
        } else {
            $data['video_path'] = null;
        }

        if ($request->hasFile('thumbnail_file')) {
            $data['thumbnail_path'] = ImageUploadHelper::upload($request->file('thumbnail_file'), self::THUMBNAIL_DIRECTORY);
        }

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

        $data = $this->validated($request, $video);

        if ($data['source_type'] === Video::SOURCE_UPLOAD) {
            if ($request->hasFile('video_file')) {
                ImageUploadHelper::delete($video->video_path);
                $data['video_path'] = ImageUploadHelper::upload($request->file('video_file'), self::VIDEO_DIRECTORY);
            } else {
                $data['video_path'] = $video->video_path;
            }
            $data['youtube_url'] = '';
        } else {
            ImageUploadHelper::delete($video->video_path);
            $data['video_path'] = null;
        }

        if ($request->hasFile('thumbnail_file')) {
            ImageUploadHelper::delete($video->thumbnail_path);
            $data['thumbnail_path'] = ImageUploadHelper::upload($request->file('thumbnail_file'), self::THUMBNAIL_DIRECTORY);
        }

        $video->update($data);

        return response()->json(['message' => 'Video updated.', 'video' => $video]);
    }

    public function destroy(Video $video): JsonResponse
    {
        $this->authorize('delete', $video);

        ImageUploadHelper::delete($video->video_path);
        ImageUploadHelper::delete($video->thumbnail_path);
        $video->delete();

        return response()->json(['message' => 'Video deleted.']);
    }

    /**
     * Videos come from one of two mutually exclusive sources — a pasted
     * YouTube link, or a directly uploaded file — so the required fields
     * branch on `source_type` rather than all being independently optional.
     * $video is null on create (file is always required there); on update
     * it's only required when switching to/staying on "upload" without
     * already having a stored file.
     */
    private function validated(Request $request, ?Video $video): array
    {
        $videoFileRequired = $request->input('source_type') === Video::SOURCE_UPLOAD
            && ! $request->hasFile('video_file')
            && ! $video?->video_path;

        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'source_type' => ['required', 'in:'.Video::SOURCE_YOUTUBE.','.Video::SOURCE_UPLOAD],
            'youtube_url' => [
                Rule::requiredIf($request->input('source_type') === Video::SOURCE_YOUTUBE),
                'nullable',
                'string',
                'max:255',
                'url',
                function (string $attribute, mixed $value, \Closure $fail): void {
                    if ($value && ! Video::extractVideoId($value)) {
                        $fail('Enter a valid YouTube video URL (e.g. https://www.youtube.com/watch?v=... or https://youtu.be/...).');
                    }
                },
            ],
            'video_file' => [
                Rule::requiredIf($videoFileRequired),
                'nullable',
                'file',
                'mimes:mp4,webm,mov,ogg',
                'max:40000',
            ],
            'thumbnail_file' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'status' => ['required', 'in:active,inactive'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ]);

        return collect($data)->except(['video_file', 'thumbnail_file'])->all();
    }
}
