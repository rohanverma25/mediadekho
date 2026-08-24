<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\ImageUploadHelper;
use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SettingController extends Controller
{
    private const LOGO_DIRECTORY = 'settings';

    public function index(): View
    {
        $setting = Setting::current();
        $this->authorize('view', $setting);

        return view('admin.settings.index', compact('setting'));
    }

    public function update(Request $request): RedirectResponse
    {
        $setting = Setting::current();
        $this->authorize('update', $setting);

        $data = $request->validate([
            'contact_phone' => ['nullable', 'string', 'max:30'],
            'contact_email' => ['nullable', 'email', 'max:255'],
            'contact_address' => ['nullable', 'string', 'max:1000'],
            'contact_emails' => ['nullable', 'array'],
            'contact_emails.*.title' => ['nullable', 'string', 'max:100'],
            'contact_emails.*.email' => ['nullable', 'email', 'max:255'],
            'contact_addresses' => ['nullable', 'array'],
            'contact_addresses.*.title' => ['nullable', 'string', 'max:100'],
            'contact_addresses.*.address' => ['nullable', 'string', 'max:1000'],
            'map_embed_url' => ['nullable', 'string', 'max:2000'],
            'footer_description' => ['nullable', 'string', 'max:2000'],
            'facebook_url' => ['nullable', 'url', 'max:255'],
            'twitter_url' => ['nullable', 'url', 'max:255'],
            'linkedin_url' => ['nullable', 'url', 'max:255'],
            'youtube_url' => ['nullable', 'url', 'max:255'],
            'header_scripts' => ['nullable', 'string'],
            'footer_scripts' => ['nullable', 'string'],
            'privacy_policy' => ['nullable', 'string'],
            'terms_of_use' => ['nullable', 'string'],
            'logo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp,svg', 'max:2048'],
            'razorpay_key_id' => ['nullable', 'string', 'max:255'],
            'razorpay_key_secret' => ['nullable', 'string', 'max:255'],
        ]);

        // The secret field is deliberately never re-displayed once saved
        // (see the Blade view) — a blank submission means "leave it
        // unchanged", not "clear it out".
        if (! filled($data['razorpay_key_secret'] ?? null)) {
            unset($data['razorpay_key_secret']);
        }

        // Repeater rows the admin added but left blank (or only partially
        // filled) shouldn't be persisted as junk entries — keep only rows
        // that actually carry their essential value, defaulting the title.
        $data['contact_emails'] = collect($data['contact_emails'] ?? [])
            ->filter(fn ($row) => filled($row['email'] ?? null))
            ->map(fn ($row) => ['title' => $row['title'] ?: 'Email', 'email' => $row['email']])
            ->values()->all();

        $data['contact_addresses'] = collect($data['contact_addresses'] ?? [])
            ->filter(fn ($row) => filled($row['address'] ?? null))
            ->map(fn ($row) => ['title' => $row['title'] ?: 'Office', 'address' => $row['address']])
            ->values()->all();

        if ($request->hasFile('logo')) {
            ImageUploadHelper::delete($setting->logo);
            $data['logo'] = ImageUploadHelper::upload($request->file('logo'), self::LOGO_DIRECTORY);
        }

        $setting->update($data);

        return redirect()->route('admin.settings.index')->with('success', 'Settings updated.');
    }
}
