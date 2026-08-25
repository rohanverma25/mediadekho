<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ImageUploadHelper;
use App\Http\Controllers\Controller;
use App\Models\JobApplication;
use App\Services\NotificationMailer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class JobApplicationController extends Controller
{
    private const RESUME_DIRECTORY = 'resumes';

    public function __construct(private readonly NotificationMailer $mailer)
    {
    }

    /**
     * Public job application submission — open to guests (no auth
     * required), but if a valid Bearer token is present the application is
     * linked to that account.
     */
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'job_id' => [
                'required',
                'integer',
                Rule::exists('jobs_board', 'id')->where('status', 'active'),
            ],
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:30'],
            'resume' => ['nullable', 'file', 'mimes:pdf,doc,docx', 'max:5120'],
            'cover_letter' => ['nullable', 'string', 'max:5000'],
        ]);

        if ($request->hasFile('resume')) {
            $data['resume_original_name'] = $request->file('resume')->getClientOriginalName();
            $data['resume'] = ImageUploadHelper::upload($request->file('resume'), self::RESUME_DIRECTORY);
        }

        $data['user_id'] = $request->user('sanctum')?->id;

        $application = JobApplication::query()->create($data);

        $this->mailer->jobApplication($application);

        return response()->json(['message' => "Thanks for applying! We'll review your application and get back to you."], 201);
    }
}
