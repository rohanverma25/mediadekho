<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Setting extends Model
{
    protected $fillable = [
        'logo',
        'contact_phone',
        'contact_email',
        'contact_address',
        'footer_description',
        'facebook_url',
        'instagram_url',
        'linkedin_url',
        'youtube_url',
        'header_scripts',
        'footer_scripts',
        'privacy_policy',
        'terms_of_use',
        'about_us',
        'contact_emails',
        'contact_addresses',
        'map_embed_url',
        'razorpay_key_id',
        'razorpay_key_secret',
        'smtp_host',
        'smtp_port',
        'smtp_username',
        'smtp_password',
        'smtp_encryption',
        'mail_from_address',
        'mail_from_name',
        'notification_email',
    ];

    protected function casts(): array
    {
        return [
            'contact_emails' => 'array',
            'contact_addresses' => 'array',
            'razorpay_key_secret' => 'encrypted',
            'smtp_password' => 'encrypted',
        ];
    }

    protected function logoUrl(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->logo ? Storage::disk('public')->url($this->logo) : null,
        );
    }

    /**
     * There's only ever one settings row. The migration seeds it, but
     * firstOrCreate() is a defensive fallback in case that row was ever
     * deleted — the site should never be left with no settings record at all.
     */
    public static function current(): self
    {
        return static::query()->firstOrCreate([]);
    }
}
