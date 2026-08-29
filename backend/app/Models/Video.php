<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Video extends Model
{
    /** @use HasFactory<\Database\Factories\VideoFactory> */
    use HasFactory;

    public const SOURCE_YOUTUBE = 'youtube';

    public const SOURCE_UPLOAD = 'upload';

    protected $fillable = [
        'title',
        'source_type',
        'youtube_url',
        'video_path',
        'thumbnail_path',
        'status',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
        ];
    }

    /**
     * Pulls the 11-character video ID out of whatever YouTube URL shape the
     * admin pasted in (watch?v=, youtu.be/, embed/, shorts/) — the frontend
     * needs this bare ID both to build a thumbnail (img.youtube.com/vi/...)
     * and to lazy-embed the player only once a viewer actually clicks play,
     * rather than loading every slide's iframe upfront.
     */
    protected function videoId(): Attribute
    {
        return Attribute::make(
            get: fn () => self::extractVideoId($this->youtube_url),
        );
    }

    /**
     * Uploaded videos have no auto-generated thumbnail the way YouTube
     * does — falls back to whatever poster image the admin uploaded
     * alongside it, or null (frontend shows a generic video placeholder).
     */
    protected function thumbnailUrl(): Attribute
    {
        return Attribute::make(
            get: function () {
                if ($this->source_type === self::SOURCE_UPLOAD) {
                    return $this->thumbnail_path ? Storage::disk('public')->url($this->thumbnail_path) : null;
                }

                return $this->video_id ? "https://img.youtube.com/vi/{$this->video_id}/hqdefault.jpg" : null;
            },
        );
    }

    protected function videoUrl(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->video_path ? Storage::disk('public')->url($this->video_path) : null,
        );
    }

    public static function extractVideoId(?string $url): ?string
    {
        if (blank($url)) {
            return null;
        }

        if (preg_match('/(?:youtu\.be\/|youtube\.com\/(?:watch\?v=|embed\/|shorts\/|v\/))([A-Za-z0-9_-]{11})/', $url, $matches)) {
            return $matches[1];
        }

        return null;
    }
}
