<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MaintenanceRequest extends Model
{
    const STATUS_PENDING     = 'pending';
    const STATUS_IN_PROGRESS = 'in_progress';
    const STATUS_RESOLVED    = 'resolved';

    const PRIORITY_NORMAL = 'normal';
    const PRIORITY_HIGH   = 'high';
    const PRIORITY_URGENT = 'urgent';

    protected $fillable = [
        'hotel_id', 'room_id', 'booking_id', 'reported_by', 'assigned_to',
        'category', 'description', 'status', 'priority',
        'resolution_notes', 'resolved_at',
    ];

    protected $casts = [
        'resolved_at' => 'datetime',
    ];

    // ── Relationships ─────────────────────────────────────────────────────────

    public function hotel(): BelongsTo   { return $this->belongsTo(Hotel::class); }
    public function room(): BelongsTo    { return $this->belongsTo(Room::class); }
    public function booking(): BelongsTo { return $this->belongsTo(Booking::class); }
    public function reporter(): BelongsTo { return $this->belongsTo(User::class, 'reported_by'); }
    public function assignee(): BelongsTo { return $this->belongsTo(User::class, 'assigned_to'); }

    // ── Scopes ────────────────────────────────────────────────────────────────

    public function scopeForHotel($query, int $hotelId) { return $query->where('hotel_id', $hotelId); }
    public function scopePending($query)     { return $query->where('status', self::STATUS_PENDING); }
    public function scopeInProgress($query)  { return $query->where('status', self::STATUS_IN_PROGRESS); }
    public function scopeResolved($query)    { return $query->where('status', self::STATUS_RESOLVED); }

    // ── Accessors ─────────────────────────────────────────────────────────────

    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_PENDING     => 'amber',
            self::STATUS_IN_PROGRESS => 'blue',
            self::STATUS_RESOLVED    => 'emerald',
            default                  => 'slate',
        };
    }

    public function getPriorityColorAttribute(): string
    {
        return match ($this->priority) {
            self::PRIORITY_URGENT => 'rose',
            self::PRIORITY_HIGH   => 'amber',
            default               => 'slate',
        };
    }

    // ── Status transitions ────────────────────────────────────────────────────

    public function markInProgress(User $user): void
    {
        $this->update(['status' => self::STATUS_IN_PROGRESS, 'assigned_to' => $user->id]);
    }

    public function markResolved(?string $notes = null): void
    {
        $this->update([
            'status'           => self::STATUS_RESOLVED,
            'resolved_at'      => now(),
            'resolution_notes' => $notes,
        ]);
    }
}
