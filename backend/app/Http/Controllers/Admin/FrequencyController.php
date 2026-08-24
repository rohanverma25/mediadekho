<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Frequency;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class FrequencyController extends Controller
{
    /**
     * Gated by the `staff` route middleware — a single-field lookup list
     * doesn't warrant its own Policy class.
     */
    public function index(): View
    {
        return view('admin.frequencies.index');
    }

    public function data(): JsonResponse
    {
        $frequencies = Frequency::query()
            ->orderBy('name')
            ->get(['id', 'name']);

        return response()->json(['data' => $frequencies]);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:frequencies,name'],
        ]);

        $frequency = Frequency::query()->create($data);

        return response()->json(['message' => 'Frequency created.', 'frequency' => $frequency], 201);
    }

    public function edit(Frequency $frequency): JsonResponse
    {
        return response()->json(['frequency' => $frequency]);
    }

    public function update(Request $request, Frequency $frequency): JsonResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('frequencies', 'name')->ignore($frequency->id)],
        ]);

        $frequency->update($data);

        return response()->json(['message' => 'Frequency updated.', 'frequency' => $frequency]);
    }

    public function destroy(Frequency $frequency): JsonResponse
    {
        $frequency->delete();

        return response()->json(['message' => 'Frequency deleted.']);
    }
}
