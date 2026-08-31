<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\ImageUploadHelper;
use App\Http\Controllers\Controller;
use App\Models\Magazine;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MagazineController extends Controller
{
    private const COVER_DIRECTORY = 'magazines-covers';

    private const PDF_DIRECTORY = 'magazines';

    public function index(): View
    {
        $this->authorize('viewAny', Magazine::class);

        return view('admin.magazines.index');
    }

    public function data(): JsonResponse
    {
        $this->authorize('viewAny', Magazine::class);

        $magazines = Magazine::query()
            ->orderBy('sort_order')
            ->orderByDesc('created_at')
            ->get()
            ->map(fn (Magazine $magazine) => [
                'id' => $magazine->id,
                'title' => $magazine->title,
                'slug' => $magazine->slug,
                'cover_image_url' => $magazine->cover_image_url,
                'published_at' => $magazine->published_at?->format('Y-m-d'),
                'status' => $magazine->status,
                'sort_order' => $magazine->sort_order,
            ]);

        return response()->json(['data' => $magazines]);
    }

    public function store(Request $request): JsonResponse
    {
        $this->authorize('create', Magazine::class);

        $data = $this->validated($request, requirePdf: true);

        if ($request->hasFile('cover_image')) {
            $data['cover_image'] = ImageUploadHelper::upload($request->file('cover_image'), self::COVER_DIRECTORY);
        }

        if ($request->hasFile('pdf_file')) {
            $data['pdf_file'] = ImageUploadHelper::upload($request->file('pdf_file'), self::PDF_DIRECTORY);
        }

        $magazine = Magazine::query()->create($data);

        return response()->json(['message' => 'Magazine created.', 'magazine' => $magazine], 201);
    }

    public function edit(Magazine $magazine): JsonResponse
    {
        $this->authorize('view', $magazine);

        return response()->json([
            'magazine' => $magazine->toArray() + ['cover_image_url' => $magazine->cover_image_url, 'pdf_url' => $magazine->pdf_url],
        ]);
    }

    public function update(Request $request, Magazine $magazine): JsonResponse
    {
        $this->authorize('update', $magazine);

        $data = $this->validated($request, requirePdf: false);

        if ($request->hasFile('cover_image')) {
            ImageUploadHelper::delete($magazine->cover_image);
            $data['cover_image'] = ImageUploadHelper::upload($request->file('cover_image'), self::COVER_DIRECTORY);
        }

        if ($request->hasFile('pdf_file')) {
            ImageUploadHelper::delete($magazine->pdf_file);
            $data['pdf_file'] = ImageUploadHelper::upload($request->file('pdf_file'), self::PDF_DIRECTORY);
        }

        $magazine->update($data);

        return response()->json(['message' => 'Magazine updated.', 'magazine' => $magazine]);
    }

    public function destroy(Magazine $magazine): JsonResponse
    {
        $this->authorize('delete', $magazine);

        ImageUploadHelper::delete($magazine->cover_image);
        ImageUploadHelper::delete($magazine->pdf_file);
        $magazine->delete();

        return response()->json(['message' => 'Magazine deleted.']);
    }

    /**
     * The PDF is required on create (there's nothing to read otherwise) but
     * optional on update — an admin editing just the title/description
     * shouldn't be forced to re-upload the same file.
     */
    private function validated(Request $request, bool $requirePdf): array
    {
        return $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:500'],
            'cover_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'pdf_file' => [$requirePdf ? 'required' : 'nullable', 'file', 'mimes:pdf', 'max:40000'],
            'published_at' => ['nullable', 'date'],
            'status' => ['required', 'string', 'in:active,inactive'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ]);
    }
}
