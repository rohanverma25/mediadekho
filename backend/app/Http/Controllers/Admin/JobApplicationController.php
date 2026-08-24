<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\JobApplication;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class JobApplicationController extends Controller
{
    public function index(): View
    {
        $this->authorize('viewAny', JobApplication::class);

        return view('admin.job-applications.index');
    }

    public function data(): JsonResponse
    {
        $this->authorize('viewAny', JobApplication::class);

        $applications = JobApplication::query()
            ->with(['job:id,title', 'user:id,name'])
            ->orderByDesc('created_at')
            ->get()
            ->map(fn (JobApplication $application) => [
                'id' => $application->id,
                'job_title' => $application->job?->title,
                'name' => $application->name,
                'email' => $application->email,
                'phone' => $application->phone,
                'resume_url' => $application->resume_url,
                'resume_original_name' => $application->resume_original_name,
                'cover_letter' => $application->cover_letter,
                'submitted_by' => $application->user?->name,
                'status' => $application->status,
                'created_at' => $application->created_at->format('Y-m-d H:i'),
            ]);

        return response()->json(['data' => $applications]);
    }

    /**
     * Staff only ever change an application's triage status here — the
     * submitted details themselves are never editable.
     */
    public function update(Request $request, JobApplication $application): JsonResponse
    {
        $this->authorize('update', $application);

        $data = $request->validate([
            'status' => ['required', 'string', 'in:new,shortlisted,rejected'],
        ]);

        $application->update($data);

        return response()->json(['message' => 'Application updated.', 'application' => $application]);
    }

    public function destroy(JobApplication $application): JsonResponse
    {
        $this->authorize('delete', $application);

        $application->delete();

        return response()->json(['message' => 'Application deleted.']);
    }
}
