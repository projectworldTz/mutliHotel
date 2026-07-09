<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HotelVideo extends Model
{
    use HasFactory;

    protected $fillable = ['hotel_id', 'title', 'source', 'path', 'url', 'sort_order'];

    protected $casts = [
        'sort_order' => 'integer',
    ];

    public function hotel()
    {
        return $this->belongsTo(Hotel::class);
    }

    public function isUpload(): bool
    {
        return $this->source === 'upload';
    }

    /**
     * Embed-ready URL for YouTube/Vimeo links pasted by the owner.
     * Uploads are already directly playable via `url` (native <video> tag), so they pass through unchanged.
     */
    public function getEmbedUrlAttribute(): string
    {
        if ($this->isUpload()) {
            return $this->url;
        }

        if (preg_match('~youtu\.be/([\w-]+)~', $this->url, $m)
            || preg_match('~youtube\.com/watch\?v=([\w-]+)~', $this->url, $m)
            || preg_match('~youtube\.com/embed/([\w-]+)~', $this->url, $m)
            || preg_match('~youtube\.com/shorts/([\w-]+)~', $this->url, $m)) {
            return "https://www.youtube.com/embed/{$m[1]}";
        }

        if (preg_match('~vimeo\.com/(\d+)~', $this->url, $m)) {
            return "https://player.vimeo.com/video/{$m[1]}";
        }

        return $this->url;
    }
}
