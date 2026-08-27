<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Stat;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StatController extends Controller
{
    public function index(): View
    {
        $this->authorize('viewAny', Stat::class);

        return view('admin.stats.index');
    }

    /**
     * Flat listing as JSON for the client-side DataTable — the stats
     * catalog is small, so unlike Media Inventory this doesn't need
     * server-side processing.
     */
    public function data(): JsonResponse
    {
        $this->authorize('viewAny', Stat::class);

        $stats = Stat::query()
            ->orderBy('sort_order')
            ->get()
            ->map(fn (Stat $stat) => [
                'id' => $stat->id,
                'value' => $stat->value,
                'label' => $stat->label,
                'icon' => $stat->icon,
                'status' => $stat->status,
                'sort_order' => $stat->sort_order,
            ]);

        return response()->json(['data' => $stats]);
    }

    public function store(Request $request): JsonResponse
    {
        $this->authorize('create', Stat::class);

        $data = $request->validate([
            'value' => ['required', 'string', 'max:50'],
            'label' => ['required', 'string', 'max:255'],
            'icon' => ['nullable', 'string', 'max:100'],
            'status' => ['required', 'in:active,inactive'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ]);

        $stat = Stat::query()->create($data);

        return response()->json(['message' => 'Stat created.', 'stat' => $stat], 201);
    }

    public function edit(Stat $stat): JsonResponse
    {
        $this->authorize('view', $stat);

        return response()->json(['stat' => $stat]);
    }

    public function update(Request $request, Stat $stat): JsonResponse
    {
        $this->authorize('update', $stat);

        $data = $request->validate([
            'value' => ['required', 'string', 'max:50'],
            'label' => ['required', 'string', 'max:255'],
            'icon' => ['nullable', 'string', 'max:100'],
            'status' => ['required', 'in:active,inactive'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ]);

        $stat->update($data);

        return response()->json(['message' => 'Stat updated.', 'stat' => $stat]);
    }

    public function destroy(Stat $stat): JsonResponse
    {
        $this->authorize('delete', $stat);

        $stat->delete();

        return response()->json(['message' => 'Stat deleted.']);
    }
}
