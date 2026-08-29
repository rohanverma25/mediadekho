<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\ImageUploadHelper;
use App\Http\Controllers\Controller;
use App\Models\PageMeta;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PageMetaController extends Controller
{
    private const IMAGE_DIRECTORY = 'page-meta';

    /**
     * The fixed set of static pages — one small table, no DataTable/search
     * needed, so this is a plain listing rather than the usual admin.*.data
     * JSON endpoint pattern used by the larger content modules.
     */
    public function index(): View
    {
        $this->authorize('viewAny', PageMeta::class);

        $pageMetas = PageMeta::query()->orderBy('label')->get();

        return view('admin.page-meta.index', compact('pageMetas'));
    }

    public function edit(PageMeta $pageMeta): View
    {
        $this->authorize('view', $pageMeta);

        return view('admin.page-meta.edit', compact('pageMeta'));
    }

    public function update(Request $request, PageMeta $pageMeta): RedirectResponse
    {
        $this->authorize('update', $pageMeta);

        $data = $request->validate([
            'title' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:500'],
            'og_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ]);

        if ($request->hasFile('og_image')) {
            ImageUploadHelper::delete($pageMeta->og_image);
            $data['og_image'] = ImageUploadHelper::upload($request->file('og_image'), self::IMAGE_DIRECTORY);
        }

        $pageMeta->update($data);

        return redirect()->route('admin.page-meta.index')->with('success', "Meta tags updated for {$pageMeta->label}.");
    }
}
