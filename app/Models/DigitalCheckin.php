<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DigitalCheckin extends Model
{
    protected $fillable = [
        'booking_id', 'id_document_path', 'estimated_arrival_time', 'preferences',
        'submitted_at', 'verified_at', 'verified_by',
    ];

    protected $casts = [
        'submitted_at' => 'datetime',
        'verified_at'  => 'datetime',
    ];

    public function booking(): BelongsTo { return $this->belongsTo(Booking::class); }
    public function verifier(): BelongsTo { return $this->belongsTo(User::class, 'verified_by'); }

    public function isVerified(): bool
    {
        return $this->verified_at !== null;
    }

    public function getIdDocumentUrlAttribute(): ?string
    {
        return $this->id_document_path
            ? \Illuminate\Support\Facades\Storage::disk('public')->url($this->id_document_path)
            : null;
    }
}
