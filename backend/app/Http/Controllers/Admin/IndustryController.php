<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\ImageUploadHelper;
use App\Http\Controllers\Controller;
use App\Models\Industry;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class IndustryController extends Controller
{
    private const IMAGE_DIRECTORY = 'industries';

    public function index(): View
    {
        $this->authorize('viewAny', Industry::class);

        return view('admin.industries.index');
    }

    /**
     * Flat listing as JSON for the client-side DataTable — the industry
     * catalog is small, so unlike Media Inventory this doesn't need
     * server-side processing.
     */
    public function data(): JsonResponse
    {
        $this->authorize('viewAny', Industry::class);

        $industries = Industry::query()
            ->orderBy('sort_order')
            ->get()
            ->map(fn (Industry $industry) => [
                'id' => $industry->id,
                'title' => $industry->title,
                'status' => $industry->status,
                'sort_order' => $industry->sort_order,
                'image_url' => $industry->image_url,
            ]);

        return response()->json(['data' => $industries]);
    }

    public function store(Request $request): JsonResponse
    {
        $this->authorize('create', Industry::class);

        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'image' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'status' => ['required', 'in:active,inactive'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ]);

        $data['image'] = ImageUploadHelper::upload($request->file('image'), self::IMAGE_DIRECTORY);

        $industry = Industry::query()->create($data);

        return response()->json(['message' => 'Industry created.', 'industry' => $industry], 201);
    }

    public function edit(Industry $industry): JsonResponse
    {
        $this->authorize('view', $industry);

        return response()->json([
            'industry' => $industry->toArray() + ['image_url' => $industry->image_url],
        ]);
    }

    public function update(Request $request, Industry $industry): JsonResponse
    {
        $this->authorize('update', $industry);

        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'status' => ['required', 'in:active,inactive'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ]);

        if ($request->hasFile('image')) {
            ImageUploadHelper::delete($industry->image);
            $data['image'] = ImageUploadHelper::upload($request->file('image'), self::IMAGE_DIRECTORY);
        }

        $industry->update($data);

        return response()->json(['message' => 'Industry updated.', 'industry' => $industry]);
    }

    public function destroy(Industry $industry): JsonResponse
    {
        $this->authorize('delete', $industry);

        ImageUploadHelper::delete($industry->image);
        $industry->delete();

        return response()->json(['message' => 'Industry deleted.']);
    }
}
