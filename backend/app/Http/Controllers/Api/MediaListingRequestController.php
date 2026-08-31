<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ImageUploadHelper;
use App\Http\Controllers\Controller;
use App\Models\MediaListingRequest;
use App\Services\NotificationMailer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MediaListingRequestController extends Controller
{
    private const IMAGE_DIRECTORY = 'media-listing-requests';

    private const MEDIA_KIT_DIRECTORY = 'media-listing-requests-kits';

    public function __construct(private readonly NotificationMailer $mailer)
    {
    }

    /**
     * Public "List Your Media" submission — a media owner/vendor pitching
     * their inventory to be listed on the platform. Open to guests, same
     * as Contact/Award Nomination/Job Application.
     */
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'company_name' => ['required', 'string', 'max:255'],
            'contact_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['required', 'string', 'max:30'],
            'media_title' => ['required', 'string', 'max:255'],
            'media_type' => ['nullable', 'string', 'max:255'],
            'location' => ['nullable', 'string', 'max:255'],
            'approximate_rate' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:5000'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'media_kit' => ['nullable', 'file', 'mimes:pdf,doc,docx,jpg,jpeg,png', 'max:10240'],
        ]);

        if ($request->hasFile('image')) {
            $data['image'] = ImageUploadHelper::upload($request->file('image'), self::IMAGE_DIRECTORY);
        }

        if ($request->hasFile('media_kit')) {
            $data['media_kit_original_name'] = $request->file('media_kit')->getClientOriginalName();
            $data['media_kit'] = ImageUploadHelper::upload($request->file('media_kit'), self::MEDIA_KIT_DIRECTORY);
        }

        $mediaListingRequest = MediaListingRequest::query()->create($data);

        $this->mailer->mediaListingRequest($mediaListingRequest);

        return response()->json([
            'message' => "Thanks! We've received your media details and our team will reach out shortly.",
        ], 201);
    }
}
