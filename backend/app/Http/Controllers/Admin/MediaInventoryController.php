<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\MediaInventory\StoreMediaInventoryPriceRequest;
use App\Http\Requests\MediaInventory\StoreMediaInventoryRequest;
use App\Http\Requests\MediaInventory\UpdateMediaInventoryRequest;
use App\Helpers\ImageUploadHelper;
use App\Models\Frequency;
use App\Models\Language;
use App\Models\MediaCategory;
use App\Models\MediaInventory;
use App\Models\MediaInventoryFile;
use App\Models\MediaInventoryImage;
use App\Services\MediaInventoryImportExportService;
use App\Services\MediaInventoryService;
use App\Services\PricingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Spatie\Activitylog\Models\Activity;
use Symfony\Component\HttpFoundation\StreamedResponse;

class MediaInventoryController extends Controller
{
    public function __construct(
        private readonly MediaInventoryService $service,
        private readonly PricingService $pricing,
        private readonly MediaInventoryImportExportService $importExport,
    ) {
    }

    public function index(): View
    {
        $this->authorize('viewAny', MediaInventory::class);

        $categories = MediaCategory::query()->whereNull('parent_id')->where('status', 'active')->orderBy('name')->get();
        $frequencies = Frequency::query()->orderBy('name')->get();
        $languages = Language::query()->orderBy('name')->get();

        return view('admin.media-inventory.index', compact('categories', 'frequencies', 'languages'));
    }

    /**
     * The Category/Subcategory selects only offer active options — but when
     * editing an inventory item already assigned to a category that's since
     * gone inactive, that option has to stay in the list anyway, or the
     * dropdown would silently reassign it to something else on save.
     *
     * @return array{categories: \Illuminate\Support\Collection<int, MediaCategory>, frequencies: \Illuminate\Support\Collection<int, Frequency>, languages: \Illuminate\Support\Collection<int, Language>}
     */
    private function formLookups(?MediaInventory $inventory = null): array
    {
        $categories = MediaCategory::query()
            ->whereNull('parent_id')
            ->where('status', 'active')
            ->with(['children' => fn ($q) => $q->where('status', 'active')])
            ->orderBy('name')
            ->get();

        if ($inventory?->category_id && ! $categories->contains('id', $inventory->category_id)) {
            $currentCategory = MediaCategory::query()->with('children')->find($inventory->category_id);
            if ($currentCategory) {
                $categories->push($currentCategory);
            }
        }

        if ($inventory?->subcategory_id) {
            $categories->each(function (MediaCategory $category) use ($inventory) {
                if ($category->id !== $inventory->category_id || $category->children->contains('id', $inventory->subcategory_id)) {
                    return;
                }

                $currentSubcategory = MediaCategory::query()->find($inventory->subcategory_id);
                if ($currentSubcategory) {
                    $category->children->push($currentSubcategory);
                }
            });
        }

        return [
            'categories' => $categories,
            'frequencies' => Frequency::query()->orderBy('name')->get(),
            'languages' => Language::query()->orderBy('name')->get(),
        ];
    }

    /**
     * Server-side DataTables endpoint. This is the listing that has to hold
     * up at 100k+ rows, so filtering/searching/pagination all happen in SQL
     * — nothing is ever loaded into PHP beyond the current page.
     */
    public function data(Request $request): JsonResponse
    {
        $this->authorize('viewAny', MediaInventory::class);

        $query = MediaInventory::query()->with(['category', 'subcategory', 'frequency', 'language', 'price', 'creator', 'images' => fn ($q) => $q->where('is_cover', true)]);

        $query->when($request->filled('category_id'), fn ($q) => $q->where('category_id', $request->input('category_id')))
            ->when($request->filled('frequency_id'), fn ($q) => $q->where('frequency_id', $request->input('frequency_id')))
            ->when($request->filled('language_id'), fn ($q) => $q->where('language_id', $request->input('language_id')))
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->input('status')))
            ->when($request->filled('date_from'), fn ($q) => $q->whereDate('created_at', '>=', $request->input('date_from')))
            ->when($request->filled('date_to'), fn ($q) => $q->whereDate('created_at', '<=', $request->input('date_to')));

        $recordsTotal = MediaInventory::query()->count();
        $recordsFiltered = (clone $query)->count();

        if ($search = $request->input('search.value')) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('short_description', 'like', "%{$search}%");
            });
            $recordsFiltered = (clone $query)->count();
        }

        // Sortable columns map to native media_inventory columns only —
        // sorting by related columns (category name, price tiers, creator)
        // would require joins and isn't exposed in the UI's column headers.
        $sortableColumns = ['title', null, null, null, null, null, null, null, 'status', null];
        $orderColumnIndex = (int) $request->input('order.0.column', 0);
        $orderDirection = $request->input('order.0.dir', 'desc') === 'asc' ? 'asc' : 'desc';
        $sortColumn = $sortableColumns[$orderColumnIndex] ?? null;

        $query->orderBy($sortColumn ?? 'created_at', $sortColumn ? $orderDirection : 'desc');

        $length = (int) $request->input('length', 15);
        $start = (int) $request->input('start', 0);

        $records = $query->skip($start)->take($length > 0 ? $length : 15)->get();

        $data = $records->map(function (MediaInventory $item) {
            $price = $item->price;

            return [
                'id' => $item->id,
                'cover_image_url' => $item->image_url ?? $item->images->first()?->url,
                'title' => $item->title,
                'category' => $item->category?->name,
                'frequency' => $item->frequency?->name,
                'language' => $item->language?->name,
                'retail_price' => $price?->retail_price,
                'b2c_price' => $price?->b2c_price,
                'b2b_price' => $price?->b2b_price,
                'status' => $item->status,
                'created_by' => $item->creator?->name,
            ];
        });

        return response()->json([
            'draw' => (int) $request->input('draw', 1),
            'recordsTotal' => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'data' => $data,
        ]);
    }

    public function create(): View
    {
        $this->authorize('create', MediaInventory::class);

        return view('admin.media-inventory.create', $this->formLookups());
    }

    public function store(StoreMediaInventoryRequest $request): RedirectResponse
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

        return redirect()
            ->route('admin.media-inventory.edit', $inventory)
            ->with('success', 'Inventory created. Now set up its pricing below.');
    }

    /**
     * Read-only detail page: media info, gallery, documents, pricing table,
     * availability, and audit history — distinct from edit(), which is the
     * form for changing any of it.
     */
    public function show(MediaInventory $inventory): View
    {
        $this->authorize('view', $inventory);

        $inventory->load(['category', 'subcategory', 'frequency', 'language', 'price', 'images', 'files', 'keyInsights', 'availabilitySlots', 'creator']);
        $priceBreakdown = $inventory->price ? $this->pricing->adminBreakdown($inventory->price) : null;
        $activities = Activity::query()
            ->forSubject($inventory)
            ->with('causer')
            ->latest()
            ->get();

        return view('admin.media-inventory.show', compact('inventory', 'priceBreakdown', 'activities'));
    }

    public function edit(MediaInventory $inventory): View
    {
        $this->authorize('view', $inventory);

        $inventory->load(['category', 'subcategory', 'frequency', 'language', 'price', 'images', 'files', 'keyInsights', 'availabilitySlots']);
        $canManagePrice = request()->user()->can('managePrice', $inventory);
        $priceBreakdown = $inventory->price ? $this->pricing->adminBreakdown($inventory->price) : null;

        return view('admin.media-inventory.edit', $this->formLookups($inventory) + compact('inventory', 'canManagePrice', 'priceBreakdown'));
    }

    public function update(UpdateMediaInventoryRequest $request, MediaInventory $inventory): RedirectResponse
    {
        $data = $request->safe()->except(['gallery', 'documents', 'image', 'key_insights']);

        $this->service->update(
            $inventory,
            $data,
            $request->file('image'),
            $request->file('gallery', []),
            $request->file('documents', []),
            $request->input('key_insights', []),
        );

        return redirect()
            ->route('admin.media-inventory.edit', $inventory)
            ->with('success', 'Inventory updated.');
    }

    public function destroy(MediaInventory $inventory): RedirectResponse
    {
        $this->authorize('delete', $inventory);

        $this->service->delete($inventory);

        return redirect()->route('admin.media-inventory.index')->with('success', 'Inventory deleted.');
    }

    public function storePrice(StoreMediaInventoryPriceRequest $request, MediaInventory $inventory): RedirectResponse
    {
        $this->service->setPrice($inventory, $request->validated());

        return redirect()
            ->route('admin.media-inventory.edit', $inventory)
            ->with('success', 'Pricing saved.');
    }

    public function destroyImage(MediaInventory $inventory, MediaInventoryImage $image): JsonResponse
    {
        $this->authorize('update', $inventory);

        ImageUploadHelper::delete($image->path);
        $image->delete();

        if (! $inventory->images()->where('is_cover', true)->exists()) {
            $inventory->images()->orderBy('sort_order')->first()?->update(['is_cover' => true]);
        }

        return response()->json(['message' => 'Image removed.']);
    }

    public function destroyFile(MediaInventory $inventory, MediaInventoryFile $file): JsonResponse
    {
        $this->authorize('update', $inventory);

        ImageUploadHelper::delete($file->path);
        $file->delete();

        return response()->json(['message' => 'Document removed.']);
    }

    public function export(Request $request): StreamedResponse
    {
        $this->authorize('export', MediaInventory::class);

        return $this->importExport->exportCsv($request->only(['category_id', 'frequency_id', 'language_id', 'status']));
    }

    public function import(Request $request): RedirectResponse
    {
        $this->authorize('import', MediaInventory::class);

        $request->validate([
            'file' => ['required', 'file', 'mimes:csv,txt', 'max:10240'],
        ]);

        try {
            $result = $this->importExport->importCsv($request->file('file'), $request->user());
        } catch (\InvalidArgumentException $e) {
            return back()->with('error', $e->getMessage());
        }

        $message = "Import complete — {$result['created']} created, {$result['updated']} updated.";
        if ($result['failed']) {
            $message .= ' '.count($result['failed']).' row(s) failed.';
        }

        return back()->with($result['failed'] ? 'error' : 'success', $message);
    }
}
