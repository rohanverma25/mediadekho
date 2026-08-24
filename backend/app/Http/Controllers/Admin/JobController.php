<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Job;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class JobController extends Controller
{
    public function index(): View
    {
        $this->authorize('viewAny', Job::class);

        return view('admin.jobs.index');
    }

    public function data(): JsonResponse
    {
        $this->authorize('viewAny', Job::class);

        $jobs = Job::query()
            ->orderBy('sort_order')
            ->get()
            ->map(fn (Job $job) => [
                'id' => $job->id,
                'title' => $job->title,
                'description_preview' => Str::limit(strip_tags($job->description ?? ''), 80),
                'department' => $job->department,
                'location' => $job->location,
                'type' => $job->type,
                'status' => $job->status,
                'sort_order' => $job->sort_order,
                'applications_count' => $job->applications()->count(),
            ]);

        return response()->json(['data' => $jobs]);
    }

    public function store(Request $request): JsonResponse
    {
        $this->authorize('create', Job::class);

        $job = Job::query()->create($this->validated($request));

        return response()->json(['message' => 'Job saved.', 'job' => $job], 201);
    }

    public function edit(Job $job): JsonResponse
    {
        $this->authorize('view', $job);

        return response()->json(['job' => $job]);
    }

    public function update(Request $request, Job $job): JsonResponse
    {
        $this->authorize('update', $job);

        $job->update($this->validated($request));

        return response()->json(['message' => 'Job updated.', 'job' => $job]);
    }

    public function destroy(Job $job): JsonResponse
    {
        $this->authorize('delete', $job);

        $job->delete();

        return response()->json(['message' => 'Job deleted.']);
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'department' => ['nullable', 'string', 'max:255'],
            'location' => ['nullable', 'string', 'max:255'],
            'type' => ['required', 'string', 'in:full-time,part-time,internship,contract'],
            'status' => ['required', 'string', 'in:active,inactive'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ]);
    }
}
