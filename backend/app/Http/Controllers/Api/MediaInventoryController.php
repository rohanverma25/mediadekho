<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\MediaInventory\StoreMediaInventoryPriceRequest;
use App\Http\Requests\MediaInventory\StoreMediaInventoryRequest;
use App\Http\Requests\MediaInventory\UpdateMediaInventoryRequest;
use App\Http\Resources\MediaInventoryResource;
use App\Models\MediaInventory;
use App\Repositories\Contracts\MediaInventoryRepositoryInterface;
use App\Services\MediaInventoryService;
use App\Services\PricingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Gate;

class MediaInventoryController extends Controller
{
    public function __construct(
        private readonly MediaInventoryRepositoryInterface $repository,
        private readonly MediaInventoryService $service,
        private readonly PricingService $pricing,
    ) {
    }

    /**
     * Search/list API. Guests and customer roles only ever see published
     * inventory — staff (inventory.view) can also pull drafts/archived via
     * the `status` filter for back-office tooling.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $filters = $request->only(['category_id', 'subcategory_id', 'frequency_id', 'language_id', 'date_from', 'date_to', 'search']);

        if (! $request->user('sanctum')?->can('inventory.view')) {
            $filters['status'] = 'published';
        } else {
            $filters['status'] = $request->input('status');
        }

        $inventory = $this->repository->paginate($filters, (int) $request->input('per_page', 15));
        $inventory->load(['images' => fn ($q) => $q->where('is_cover', true), 'keyInsights']);

        return MediaInventoryResource::collection($inventory);
    }

    public function show(Request $request, MediaInventory $inventory): MediaInventoryResource
    {
        // No `auth:sanctum` middleware guards this route (guests must be able
        // to browse), so authorization has to explicitly check the
        // sanctum-resolved user rather than $this->authorize()'s default
        // guard, which would be empty for a pure API/token client.
        Gate::forUser($request->user('sanctum'))->authorize('view', $inventory);

        $inventory->load(['category', 'subcategory', 'frequency', 'language', 'images', 'price', 'keyInsights', 'faqs']);

        return new MediaInventoryResource($inventory);
    }

    /**
     * Dedicated pricing endpoint, distinct from the price already embedded
     * in show() — explicit per the spec's API section. Still routed through
     * PricingService::priceForUser(), so it carries the same guarantee: the
     * tier is derived from the authenticated token holder, never from the
     * request itself.
     */
    public function price(Request $request, MediaInventory $inventory): JsonResponse
    {
        Gate::forUser($request->user('sanctum'))->authorize('view', $inventory);

        return response()->json($this->pricing->priceForUser($inventory, $request->user('sanctum')));
    }

    public function store(StoreMediaInventoryRequest $request): JsonResponse
    {
        $data = $request->safe()->except(['gallery', 'documents', 'image', 'key_insights']);
        $data['created_by'] = $request->user()->id;

        $inventory = $this->service->create(
            $data,
            $request->file('image'),
            $request->file('gallery', []),
            $request->file('documents', []),
            $request->input('key_insights', []),
        );

        return (new MediaInventoryResource($inventory->load(['category', 'images'])))
            ->response()
            ->setStatusCode(201);
    }

    public function update(UpdateMediaInventoryRequest $request, MediaInventory $inventory): MediaInventoryResource
    {
        $data = $request->safe()->except(['gallery', 'documents', 'image', 'key_insights']);

        $inventory = $this->service->update(
            $inventory,
            $data,
            $request->file('image'),
            $request->file('gallery', []),
            $request->file('documents', []),
            $request->input('key_insights', []),
        );

        return new MediaInventoryResource($inventory->load(['category', 'images']));
    }

    public function destroy(MediaInventory $inventory): JsonResponse
    {
        $this->authorize('delete', $inventory);

        $this->service->delete($inventory);

        return response()->json(null, 204);
    }

    public function storePrice(StoreMediaInventoryPriceRequest $request, MediaInventory $inventory): JsonResponse
    {
        $this->service->setPrice($inventory, $request->validated());

        return response()->json([
            'message' => 'Pricing saved.',
            'breakdown' => $this->pricing->adminBreakdown($inventory->price()->firstOrFail()),
        ]);
    }
}
