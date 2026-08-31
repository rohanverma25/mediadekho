<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\MagazineResource;
use App\Models\Magazine;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class MagazineController extends Controller
{
    /**
     * Active magazines for the public reader listing.
     */
    public function index(): AnonymousResourceCollection
    {
        $magazines = Magazine::query()
            ->where('status', 'active')
            ->orderBy('sort_order')
            ->orderByDesc('published_at')
            ->get();

        return MagazineResource::collection($magazines);
    }

    /**
     * No `auth:sanctum` middleware guards this route (guests must be able to
     * read magazines), so authorization explicitly checks the
     * sanctum-resolved user rather than $this->authorize()'s default guard.
     */
    public function show(Request $request, Magazine $magazine): MagazineResource
    {
        Gate::forUser($request->user('sanctum'))->authorize('view', $magazine);

        return new MagazineResource($magazine);
    }

    /**
     * Streams the raw PDF bytes through this app's own API route rather
     * than the static /uploads/... URL the `public` disk otherwise
     * resolves to. The in-browser reader (react-pdf/pdfjs-dist) loads the
     * file via a JS `fetch()` to render it to canvas — a cross-origin
     * fetch, unlike a plain `<iframe>`/download navigation, which the
     * static file host has no CORS headers for. Every /api/* route
     * already gets Laravel's default wildcard CORS treatment, so routing
     * through here sidesteps the problem without loosening anything on
     * the static file host itself.
     */
    public function pdf(Request $request, Magazine $magazine): StreamedResponse
    {
        Gate::forUser($request->user('sanctum'))->authorize('view', $magazine);

        return Storage::disk('public')->response($magazine->pdf_file, $magazine->slug.'.pdf', [
            'Content-Type' => 'application/pdf',
        ]);
    }
}
