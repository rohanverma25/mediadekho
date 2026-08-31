<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MediaListingRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MediaListingRequestController extends Controller
{
    public function index(): View
    {
        $this->authorize('viewAny', MediaListingRequest::class);

        return view('admin.media-listing-requests.index');
    }

    public function data(): JsonResponse
    {
        $this->authorize('viewAny', MediaListingRequest::class);

        $requests = MediaListingRequest::query()
            ->orderByDesc('created_at')
            ->get()
            ->map(fn (MediaListingRequest $request) => [
                'id' => $request->id,
                'company_name' => $request->company_name,
                'contact_name' => $request->contact_name,
                'email' => $request->email,
                'phone' => $request->phone,
                'media_title' => $request->media_title,
                'media_type' => $request->media_type,
                'location' => $request->location,
                'approximate_rate' => $request->approximate_rate,
                'description' => $request->description,
                'image_url' => $request->image_url,
                'media_kit_url' => $request->media_kit_url,
                'media_kit_original_name' => $request->media_kit_original_name,
                'status' => $request->status,
                'created_at' => $request->created_at->format('Y-m-d H:i'),
            ]);

        return response()->json(['data' => $requests]);
    }

    /**
     * Staff only ever change a request's triage status here — the
     * submitted details themselves are never editable.
     */
    public function update(Request $request, MediaListingRequest $mediaListingRequest): JsonResponse
    {
        $this->authorize('update', $mediaListingRequest);

        $data = $request->validate([
            'status' => ['required', 'string', 'in:new,contacted,listed,rejected'],
        ]);

        $mediaListingRequest->update($data);

        return response()->json(['message' => 'Request updated.', 'mediaListingRequest' => $mediaListingRequest]);
    }

    public function destroy(MediaListingRequest $mediaListingRequest): JsonResponse
    {
        $this->authorize('delete', $mediaListingRequest);

        $mediaListingRequest->delete();

        return response()->json(['message' => 'Request deleted.']);
    }
}
