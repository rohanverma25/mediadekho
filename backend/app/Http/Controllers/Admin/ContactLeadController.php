<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ContactLead;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ContactLeadController extends Controller
{
    public function index(): View
    {
        $this->authorize('viewAny', ContactLead::class);

        return view('admin.leads.index');
    }

    public function data(): JsonResponse
    {
        $this->authorize('viewAny', ContactLead::class);

        $leads = ContactLead::query()
            ->with('user:id,name')
            ->orderByDesc('created_at')
            ->get()
            ->map(fn (ContactLead $lead) => [
                'id' => $lead->id,
                'name' => $lead->name,
                'email' => $lead->email,
                'phone' => $lead->phone,
                'company_name' => $lead->company_name,
                'location' => $lead->location,
                'subject' => $lead->subject,
                'description' => $lead->description,
                'status' => $lead->status,
                'customer_name' => $lead->user?->name,
                'created_at' => $lead->created_at->format('Y-m-d H:i'),
            ]);

        return response()->json(['data' => $leads]);
    }

    /**
     * Staff only ever change a lead's triage status here (new/contacted/
     * closed) — the submitted details themselves are never editable, since
     * they're the customer's own words.
     */
    public function update(Request $request, ContactLead $lead): JsonResponse
    {
        $this->authorize('update', $lead);

        $data = $request->validate([
            'status' => ['required', 'string', 'in:new,contacted,closed'],
        ]);

        $lead->update($data);

        return response()->json(['message' => 'Lead updated.', 'lead' => $lead]);
    }

    public function destroy(ContactLead $lead): JsonResponse
    {
        $this->authorize('delete', $lead);

        $lead->delete();

        return response()->json(['message' => 'Lead deleted.']);
    }
}
