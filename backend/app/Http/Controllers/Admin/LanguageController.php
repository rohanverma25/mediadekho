<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Language;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class LanguageController extends Controller
{
    /**
     * Gated by the `staff` route middleware — a single-field lookup list
     * doesn't warrant its own Policy class.
     */
    public function index(): View
    {
        return view('admin.languages.index');
    }

    public function data(): JsonResponse
    {
        $languages = Language::query()
            ->orderBy('name')
            ->get(['id', 'name']);

        return response()->json(['data' => $languages]);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:languages,name'],
        ]);

        $language = Language::query()->create($data);

        return response()->json(['message' => 'Language created.', 'language' => $language], 201);
    }

    public function edit(Language $language): JsonResponse
    {
        return response()->json(['language' => $language]);
    }

    public function update(Request $request, Language $language): JsonResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('languages', 'name')->ignore($language->id)],
        ]);

        $language->update($data);

        return response()->json(['message' => 'Language updated.', 'language' => $language]);
    }

    public function destroy(Language $language): JsonResponse
    {
        $language->delete();

        return response()->json(['message' => 'Language deleted.']);
    }
}
