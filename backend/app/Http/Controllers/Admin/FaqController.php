<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Faq;
use App\Models\MediaCategory;
use App\Models\MediaInventory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class FaqController extends Controller
{
    public function index(): View
    {
        $this->authorize('viewAny', Faq::class);

        $categories = MediaCategory::query()->whereNull('parent_id')->orderBy('name')->get(['id', 'name']);
        $inventories = MediaInventory::query()->orderBy('title')->get(['id', 'title']);

        return view('admin.faqs.index', compact('categories', 'inventories'));
    }

    public function data(): JsonResponse
    {
        $this->authorize('viewAny', Faq::class);

        $faqs = Faq::query()
            ->with(['category:id,name', 'inventory:id,title'])
            ->orderBy('sort_order')
            ->get()
            ->map(fn (Faq $faq) => [
                'id' => $faq->id,
                'question' => $faq->question,
                'answer_preview' => Str::limit(strip_tags($faq->answer), 80),
                'linked_type' => $faq->category_id ? 'category' : ($faq->inventory_id ? 'inventory' : null),
                'linked_id' => $faq->category_id ?? $faq->inventory_id,
                'linked_label' => $faq->category?->name ?? $faq->inventory?->title,
                'status' => $faq->status,
                'sort_order' => $faq->sort_order,
            ]);

        return response()->json(['data' => $faqs]);
    }

    public function store(Request $request): JsonResponse
    {
        $this->authorize('create', Faq::class);

        $data = $this->validated($request);

        $faq = Faq::query()->create($data);

        return response()->json(['message' => 'FAQ created.', 'faq' => $faq], 201);
    }

    public function edit(Faq $faq): JsonResponse
    {
        $this->authorize('view', $faq);

        return response()->json([
            'faq' => $faq->toArray() + [
                'link_type' => $faq->category_id ? 'category' : ($faq->inventory_id ? 'inventory' : ''),
                'link_id' => $faq->category_id ?? $faq->inventory_id,
            ],
        ]);
    }

    public function update(Request $request, Faq $faq): JsonResponse
    {
        $this->authorize('update', $faq);

        $data = $this->validated($request);

        $faq->update($data);

        return response()->json(['message' => 'FAQ updated.', 'faq' => $faq]);
    }

    public function destroy(Faq $faq): JsonResponse
    {
        $this->authorize('delete', $faq);

        $faq->delete();

        return response()->json(['message' => 'FAQ deleted.']);
    }

    /**
     * A FAQ optionally links to exactly one Category OR one Media Inventory
     * item, chosen via a "Link To" type selector — never both. `link_type`/
     * `link_id` are the form's input pair; they get resolved into the
     * mutually-exclusive category_id/inventory_id columns before saving.
     */
    private function validated(Request $request): array
    {
        $linkType = $request->input('link_type');

        $validated = $request->validate([
            'question' => ['required', 'string', 'max:500'],
            'answer' => ['required', 'string'],
            'link_type' => ['nullable', 'string', 'in:category,inventory'],
            'link_id' => [
                'nullable',
                'integer',
                'required_if:link_type,category',
                'required_if:link_type,inventory',
                Rule::when($linkType === 'category', ['exists:media_categories,id']),
                Rule::when($linkType === 'inventory', ['exists:media_inventory,id']),
            ],
            'status' => ['required', 'string', 'in:active,inactive'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ]);

        $data = [
            'question' => $validated['question'],
            'answer' => $validated['answer'],
            'status' => $validated['status'],
            'sort_order' => $validated['sort_order'] ?? 0,
            'category_id' => $linkType === 'category' ? $validated['link_id'] : null,
            'inventory_id' => $linkType === 'inventory' ? $validated['link_id'] : null,
        ];

        return $data;
    }
}
