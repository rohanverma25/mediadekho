<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ContactLeadResource;
use App\Models\ContactLead;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ContactLeadController extends Controller
{
    /**
     * Public "Contact Us" form submission. Open to guests by design (no
     * auth required to reach a sales team), so it's rate-limited at the
     * route level rather than gated behind a policy. If a valid Bearer
     * token is present the lead is linked to that account so it shows up
     * on the customer's "My Enquiries" page — same optional-attach pattern
     * AwardNominationController::store already uses.
     */
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:30'],
            'company_name' => ['nullable', 'string', 'max:255'],
            'location' => ['nullable', 'string', 'max:255'],
            'subject' => ['nullable', 'string', 'max:255'],
            'description' => ['required', 'string', 'max:5000'],
        ]);

        $data['user_id'] = $request->user('sanctum')?->id;

        ContactLead::query()->create($data);

        return response()->json(['message' => "Thanks! We've received your message and will get back to you shortly."], 201);
    }

    /**
     * The authenticated user's own enquiry history, shown on their
     * dashboard's "My Enquiries" page.
     */
    public function mine(Request $request): AnonymousResourceCollection
    {
        $leads = ContactLead::query()
            ->where('user_id', $request->user()->id)
            ->orderByDesc('created_at')
            ->get();

        return ContactLeadResource::collection($leads);
    }
}
