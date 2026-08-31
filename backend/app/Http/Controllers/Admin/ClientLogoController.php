<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\ImageUploadHelper;
use App\Http\Controllers\Controller;
use App\Models\ClientLogo;
use App\Models\Industry;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ClientLogoController extends Controller
{
    private const IMAGE_DIRECTORY = 'client-logos';

    public function index(): View
    {
        $this->authorize('viewAny', ClientLogo::class);

        $industries = Industry::query()->where('status', 'active')->orderBy('title')->get();

        return view('admin.client-logos.index', compact('industries'));
    }

    /**
     * Flat listing as JSON for the client-side DataTable — the logo catalog
     * is small, so unlike Media Inventory this doesn't need server-side processing.
     */
    public function data(): JsonResponse
    {
        $this->authorize('viewAny', ClientLogo::class);

        $logos = ClientLogo::query()
            ->with('industry')
            ->orderBy('sort_order')
            ->get()
            ->map(fn (ClientLogo $logo) => [
                'id' => $logo->id,
                'name' => $logo->name,
                'industry' => $logo->industry?->title,
                'website_url' => $logo->website_url,
                'status' => $logo->status,
                'sort_order' => $logo->sort_order,
                'logo_url' => $logo->logo_url,
            ]);

        return response()->json(['data' => $logos]);
    }

    public function store(Request $request): JsonResponse
    {
        $this->authorize('create', ClientLogo::class);

        // The "None" option submits an empty string, not an absent field —
        // normalize before validation runs, since the `integer` rule
        // otherwise rejects "" outright (nullable only skips rules for an
        // actual null, not an empty string).
        $request->merge(['industry_id' => $request->filled('industry_id') ? $request->input('industry_id') : null]);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'industry_id' => ['nullable', 'integer', 'exists:industries,id'],
            'website_url' => ['nullable', 'url', 'max:255'],
            'logo' => ['required', 'image', 'mimes:jpg,jpeg,png,webp,svg', 'max:2048'],
            'status' => ['required', 'in:active,inactive'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ]);

        $data['logo'] = ImageUploadHelper::upload($request->file('logo'), self::IMAGE_DIRECTORY);

        $logo = ClientLogo::query()->create($data);

        return response()->json(['message' => 'Client logo created.', 'logo' => $logo], 201);
    }

    public function edit(ClientLogo $clientLogo): JsonResponse
    {
        $this->authorize('view', $clientLogo);

        return response()->json([
            'logo' => $clientLogo->toArray() + ['logo_url' => $clientLogo->logo_url],
        ]);
    }

    public function update(Request $request, ClientLogo $clientLogo): JsonResponse
    {
        $this->authorize('update', $clientLogo);

        $request->merge(['industry_id' => $request->filled('industry_id') ? $request->input('industry_id') : null]);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'industry_id' => ['nullable', 'integer', 'exists:industries,id'],
            'website_url' => ['nullable', 'url', 'max:255'],
            'logo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp,svg', 'max:2048'],
            'status' => ['required', 'in:active,inactive'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ]);

        if ($request->hasFile('logo')) {
            ImageUploadHelper::delete($clientLogo->logo);
            $data['logo'] = ImageUploadHelper::upload($request->file('logo'), self::IMAGE_DIRECTORY);
        }

        $clientLogo->update($data);

        return response()->json(['message' => 'Client logo updated.', 'logo' => $clientLogo]);
    }

    public function destroy(ClientLogo $clientLogo): JsonResponse
    {
        $this->authorize('delete', $clientLogo);

        ImageUploadHelper::delete($clientLogo->logo);
        $clientLogo->delete();

        return response()->json(['message' => 'Client logo deleted.']);
    }
}
