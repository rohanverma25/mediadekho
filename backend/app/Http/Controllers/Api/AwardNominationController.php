<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\AwardNominationResource;
use App\Models\Award;
use App\Models\AwardNomination;
use App\Services\NotificationMailer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Validation\Rule;

class AwardNominationController extends Controller
{
    public function __construct(private readonly NotificationMailer $mailer)
    {
    }

    /**
     * Public nomination submission — open to guests (no auth required), but
     * if a valid Bearer token is present the nomination is linked to that
     * account so it shows up on their dashboard.
     */
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'award_id' => [
                'required',
                'integer',
                Rule::exists('awards', 'id')->where('type', 'upcoming')->where('status', 'active'),
            ],
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:30'],
            'company_name' => ['nullable', 'string', 'max:255'],
            'description' => ['required', 'string', 'max:5000'],
        ]);

        $data['user_id'] = $request->user('sanctum')?->id;

        $nomination = AwardNomination::query()->create($data);

        $this->mailer->awardNomination($nomination);

        return response()->json(['message' => 'Thanks! Your nomination has been submitted.'], 201);
    }

    /**
     * The authenticated user's own nomination history, shown on their
     * dashboard.
     */
    public function mine(Request $request): AnonymousResourceCollection
    {
        $nominations = AwardNomination::query()
            ->with('award:id,title,image')
            ->where('user_id', $request->user()->id)
            ->orderByDesc('created_at')
            ->get();

        return AwardNominationResource::collection($nominations);
    }
}
