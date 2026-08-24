<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AwardNomination;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AwardNominationController extends Controller
{
    public function index(): View
    {
        $this->authorize('viewAny', AwardNomination::class);

        return view('admin.award-nominations.index');
    }

    public function data(): JsonResponse
    {
        $this->authorize('viewAny', AwardNomination::class);

        $nominations = AwardNomination::query()
            ->with(['award:id,title', 'user:id,name'])
            ->orderByDesc('created_at')
            ->get()
            ->map(fn (AwardNomination $nomination) => [
                'id' => $nomination->id,
                'award_title' => $nomination->award?->title,
                'name' => $nomination->name,
                'email' => $nomination->email,
                'phone' => $nomination->phone,
                'company_name' => $nomination->company_name,
                'description' => $nomination->description,
                'submitted_by' => $nomination->user?->name,
                'status' => $nomination->status,
                'created_at' => $nomination->created_at->format('Y-m-d H:i'),
            ]);

        return response()->json(['data' => $nominations]);
    }

    /**
     * Staff only ever change a nomination's triage status here — the
     * submitted details themselves are never editable.
     */
    public function update(Request $request, AwardNomination $nomination): JsonResponse
    {
        $this->authorize('update', $nomination);

        $data = $request->validate([
            'status' => ['required', 'string', 'in:new,shortlisted,rejected'],
        ]);

        $nomination->update($data);

        return response()->json(['message' => 'Nomination updated.', 'nomination' => $nomination]);
    }

    public function destroy(AwardNomination $nomination): JsonResponse
    {
        $this->authorize('delete', $nomination);

        $nomination->delete();

        return response()->json(['message' => 'Nomination deleted.']);
    }
}
