<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\ImageUploadHelper;
use App\Http\Controllers\Controller;
use App\Models\Award;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class AwardController extends Controller
{
    private const IMAGE_DIRECTORY = 'awards';

    public function index(): View
    {
        $this->authorize('viewAny', Award::class);

        return view('admin.awards.index');
    }

    public function data(): JsonResponse
    {
        $this->authorize('viewAny', Award::class);

        $awards = Award::query()
            ->orderBy('type')
            ->orderBy('sort_order')
            ->get()
            ->map(fn (Award $award) => [
                'id' => $award->id,
                'title' => $award->title,
                'description_preview' => Str::limit(strip_tags($award->description ?? ''), 80),
                'type' => $award->type,
                'organization' => $award->organization,
                'event_date' => $award->event_date?->format('Y-m-d'),
                'status' => $award->status,
                'sort_order' => $award->sort_order,
                'image_url' => $award->image_url,
                'nominations_count' => $award->nominations()->count(),
            ]);

        return response()->json(['data' => $awards]);
    }

    public function store(Request $request): JsonResponse
    {
        $this->authorize('create', Award::class);

        $data = $this->validated($request);

        if ($request->hasFile('image')) {
            $data['image'] = ImageUploadHelper::upload($request->file('image'), self::IMAGE_DIRECTORY);
        }

        $award = Award::query()->create($data);

        return response()->json(['message' => 'Award saved.', 'award' => $award], 201);
    }

    public function edit(Award $award): JsonResponse
    {
        $this->authorize('view', $award);

        return response()->json([
            'award' => $award->toArray() + ['image_url' => $award->image_url],
        ]);
    }

    public function update(Request $request, Award $award): JsonResponse
    {
        $this->authorize('update', $award);

        $data = $this->validated($request);

        if ($request->hasFile('image')) {
            ImageUploadHelper::delete($award->image);
            $data['image'] = ImageUploadHelper::upload($request->file('image'), self::IMAGE_DIRECTORY);
        }

        $award->update($data);

        return response()->json(['message' => 'Award updated.', 'award' => $award]);
    }

    public function destroy(Award $award): JsonResponse
    {
        $this->authorize('delete', $award);

        ImageUploadHelper::delete($award->image);
        $award->delete();

        return response()->json(['message' => 'Award deleted.']);
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'type' => ['required', 'string', 'in:upcoming,past'],
            'organization' => ['nullable', 'string', 'max:255'],
            'event_date' => ['nullable', 'date'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'status' => ['required', 'string', 'in:active,inactive'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ]);
    }
}
