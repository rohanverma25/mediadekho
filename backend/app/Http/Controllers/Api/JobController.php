<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\JobResource;
use App\Models\Job;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class JobController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        $jobs = Job::query()
            ->where('status', 'active')
            ->orderBy('sort_order')
            ->get();

        return JobResource::collection($jobs);
    }
}
